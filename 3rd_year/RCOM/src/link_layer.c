// Link layer protocol implementation (Stop-and-Wait HDLC-like)

#include "link_layer.h"
#include "serial_port.h"

#include <errno.h>
#include <signal.h>
#include <stdint.h>
#include <stdio.h>
#include <string.h>
#include <unistd.h>

// MISC
#define _POSIX_SOURCE 1 // POSIX compliant source

// HDLC framing/constants
#define FLAG 0x7E
#define ESC 0x7D
#define ESC_MASK 0x20

#define A_SND 0x03 // commands from sender
#define A_RCV 0x01 // responses from receiver

#define C_SET  0x03
#define C_UA   0x07
#define C_DISC 0x0B
#define C_RR(nr)  ((unsigned char)(0x05 | ((nr) ? 0x80 : 0x00)))
#define C_REJ(nr) ((unsigned char)(0x01 | ((nr) ? 0x80 : 0x00)))
#define C_I(ns)   ((unsigned char)(((ns) ? 0x40 : 0x00)))

#define MAX_FRAME_DATA (MAX_PAYLOAD_SIZE)
#define MAX_FRAME_SIZE (2 /*flags*/ + 2 /*A C*/ + 1 /*BCC1*/ + 2*MAX_FRAME_DATA + 1 /*BCC2*/ + 16)

static LinkLayer g_params;
static int g_is_open = 0;
static int g_tx_ns = 0;      // next I-frame sequence to send
static int g_rx_expected = 0; // expected I-frame sequence to receive

static volatile sig_atomic_t g_timedout = 0;
static void alarm_handler(int signo)
{
    (void)signo;
    g_timedout = 1;
}

static unsigned char bcc(const unsigned char *buf, int len)
{
    unsigned char x = 0x00;
    for (int i = 0; i < len; ++i)
        x ^= buf[i];
    return x;
}

// Statistics
static unsigned long st_tx_i_sent = 0;
static unsigned long st_tx_i_retx = 0;
static unsigned long st_rx_i_ok = 0;
static unsigned long st_rx_dup = 0;
static unsigned long st_bcc1_err = 0;
static unsigned long st_bcc2_err = 0;
static unsigned long st_timeouts = 0;
static unsigned long st_rr_sent = 0;
static unsigned long st_rej_sent = 0;
static unsigned long st_rr_recv = 0;
static unsigned long st_rej_recv = 0;
static unsigned long st_su_sent = 0;
static unsigned long st_su_recv = 0;

static int stuff(const unsigned char *in, int inlen, unsigned char *out)
{
    int j = 0;
    for (int i = 0; i < inlen; ++i)
    {
        unsigned char b = in[i];
        if (b == FLAG || b == ESC)
        {
            out[j++] = ESC;
            out[j++] = b ^ ESC_MASK;
        }
        else
        {
            out[j++] = b;
        }
    }
    return j;
}

static int write_exact(const unsigned char *buf, int n)
{
    int total = 0;
    while (total < n)
    {
        int w = writeBytesSerialPort(buf + total, n - total);
        if (w < 0) return -1;
        total += w;
    }
    return total;
}

// Send one full frame already constructed in 'frame' with length 'len'
static int send_frame(const unsigned char *frame, int len)
{
    return write_exact(frame, len) < 0 ? -1 : 0;
}

// Build and send supervision frame: FLAG A C BCC1 FLAG
static int send_su(unsigned char A, unsigned char C)
{
    unsigned char fr[5];
    fr[0] = FLAG;
    fr[1] = A;
    fr[2] = C;
    fr[3] = (unsigned char)(A ^ C);
    fr[4] = FLAG;
    int rc = send_frame(fr, 5);
    if (rc == 0) st_su_sent++;
    return rc;
}

// Build and send I-frame with stuffing
static int send_i(unsigned char A, int ns, const unsigned char *data, int len)
{
    unsigned char hdr[3];
    hdr[0] = A;
    hdr[1] = C_I(ns);
    hdr[2] = (unsigned char)(hdr[0] ^ hdr[1]);

    unsigned char bcc2 = bcc(data, len);

    // Compose to stuff: A C BCC1 DATA BCC2
    unsigned char raw[3 + MAX_FRAME_DATA + 1];
    memcpy(raw, hdr, 3);
    memcpy(raw + 3, data, len);
    raw[3 + len] = bcc2;

    unsigned char body[2 * (3 + MAX_FRAME_DATA + 1)];
    int body_len = stuff(raw, 3 + len + 1, body);

    unsigned char frame[2 + sizeof(body)];
    int idx = 0;
    frame[idx++] = FLAG;
    memcpy(&frame[idx], body, body_len);
    idx += body_len;
    frame[idx++] = FLAG;

    return send_frame(frame, idx);
}

// Read next frame (with destuffing). Returns:
//  -1 on error, -2 on timeout, otherwise frame length in 'out' (destuffed payload excluding flags)
// 'out' contains: A C BCC1 [DATA ...] [BCC2]
static int read_frame(unsigned char *out, int outcap, int with_timeout)
{
    enum {ST_START, ST_FLAG, ST_BODY, ST_END} st = ST_START;
    unsigned char buf[MAX_FRAME_SIZE];
    int pos = 0;

    g_timedout = 0;
    if (with_timeout && g_params.timeout > 0)
        alarm(g_params.timeout);

    while (1)
    {
        unsigned char b;
        int r = readByteSerialPort(&b);
        if (r < 0)
        {
            if (g_timedout)
            {
                alarm(0);
                return -2; // timeout
            }
            continue; // spurious
        }

        switch (st)
        {
        case ST_START:
            if (b == FLAG)
                st = ST_FLAG;
            break;
        case ST_FLAG:
            if (b == FLAG)
            {
                // stay
                break;
            }
            if (b == ESC)
            {
                // not expected here; reset
                st = ST_START;
                pos = 0;
                break;
            }
            buf[pos++] = b;
            st = ST_BODY;
            break;
        case ST_BODY:
            if (b == ESC)
            {
                // read next and destuff
                unsigned char nb;
                int r2 = readByteSerialPort(&nb);
                if (r2 < 0)
                {
                    if (g_timedout)
                    {
                        alarm(0);
                        return -2;
                    }
                    continue;
                }
                buf[pos++] = nb ^ ESC_MASK;
            }
            else if (b == FLAG)
            {
                // end frame
                alarm(0);
                // copy to out
                if (pos > outcap)
                    return -1;
                memcpy(out, buf, pos);
                return pos;
            }
            else
            {
                buf[pos++] = b;
                if (pos >= (int)sizeof(buf))
                {
                    // overflow, reset
                    st = ST_START;
                    pos = 0;
                }
            }
            break;
        case ST_END:
            break;
        }
    }
}

static int expect_su(unsigned char expectA, unsigned char expectC, int with_timeout)
{
    unsigned char fr[MAX_FRAME_SIZE];
    int n = read_frame(fr, sizeof(fr), with_timeout);
    if (n == -2) return -2; // timeout
    if (n < 3) return -1;   // too short

    unsigned char A = fr[0];
    unsigned char C = fr[1];
    unsigned char BCC1 = fr[2];
    if ((unsigned char)(A ^ C) != BCC1)
    {
        st_bcc1_err++;
        return -1;
    }
    if (A != expectA || C != expectC)
        return -1;
    st_su_recv++;
    return 0;
}

//////////////////////////////////////////////
// LLOPEN
//////////////////////////////////////////////
int llopen(LinkLayer connectionParameters)
{
    g_params = connectionParameters;

    if (openSerialPort(g_params.serialPort, g_params.baudRate) < 0)
        return -1;

    // setup alarm handler once
    struct sigaction sa;
    memset(&sa, 0, sizeof(sa));
    sa.sa_handler = alarm_handler;
    sigaction(SIGALRM, &sa, NULL);

    g_tx_ns = 0;
    g_rx_expected = 0;

    if (g_params.role == LlTx)
    {
        // Send SET and wait for UA with retries
        for (int attempt = 0; attempt <= g_params.nRetransmissions; ++attempt)
        {
            if (send_su(A_SND, C_SET) < 0)
                return -1;

            int r = expect_su(A_RCV, C_UA, 1);
            if (r == 0)
            {
                g_is_open = 1;
                return 0;
            }
            if (r == -2) // timeout
                continue; // retry
            // got something else -> keep waiting until timeout then retry
        }
        return -1;
    }
    else // LlRx
    {
        // Wait for SET, then reply UA
        while (1)
        {
            int r = expect_su(A_SND, C_SET, 0);
            if (r == 0)
            {
                if (send_su(A_RCV, C_UA) < 0)
                    return -1;
                g_is_open = 1;
                return 0;
            }
            // otherwise keep listening
        }
    }
}

//////////////////////////////////////////////
// LLWRITE
//////////////////////////////////////////////
int llwrite(const unsigned char *buf, int bufSize)
{
    if (!g_is_open) return -1;
    if (bufSize < 0 || bufSize > MAX_FRAME_DATA) return -1;

    int attempts = 0;
    while (attempts <= g_params.nRetransmissions)
    {
        if (send_i(A_SND, g_tx_ns, buf, bufSize) < 0)
            return -1;
        if (attempts == 0) st_tx_i_sent++; else st_tx_i_retx++;

        // Wait for RR/REJ with timeout
        unsigned char fr[MAX_FRAME_SIZE];
        int n = read_frame(fr, sizeof(fr), 1);
        if (n == -2)
        {
            st_timeouts++;
            attempts++;
            continue; // timeout -> retransmit
        }
        if (n < 3)
        {
            attempts++;
            continue;
        }
        unsigned char A = fr[0];
        unsigned char C = fr[1];
        unsigned char BCC1 = fr[2];
        if ((unsigned char)(A ^ C) != BCC1)
        {
            attempts++;
            continue;
        }
        // Accept only responses from receiver address
        if (A != A_RCV)
        {
            // ignore and keep waiting within timeout; but our read_frame returns once; just retry
            attempts++;
            continue;
        }

        int ack_nr = (C & 0x80) ? 1 : 0;
        if ((C & 0xF) == 0x5) // RR
        {
            st_rr_recv++;
            if (ack_nr == ((g_tx_ns ^ 1) & 0x1))
            {
                g_tx_ns ^= 1;
                return bufSize;
            }
            // unexpected RR; retry
        }
        else if ((C & 0xF) == 0x1) // REJ
        {
            st_rej_recv++;
            // retransmit
        }
        attempts++;
    }
    return -1;
}

//////////////////////////////////////////////
// LLREAD
//////////////////////////////////////////////
int llread(unsigned char *packet)
{
    if (!g_is_open) return -1;

    while (1)
    {
        unsigned char fr[MAX_FRAME_SIZE];
        int n = read_frame(fr, sizeof(fr), 0);
        if (n < 3)
            continue;

        unsigned char A = fr[0];
        unsigned char C = fr[1];
        unsigned char BCC1 = fr[2];
        if ((unsigned char)(A ^ C) != BCC1)
        {
            st_bcc1_err++;
            continue;
        }

        // I-frame?
        if ((C & 0xC0) == 0x00 || (C & 0xC0) == 0x40)
        {
            int ns = (C & 0x40) ? 1 : 0;
            // data starts at fr[3], last byte is BCC2
            if (n < 4) continue;
            int dlen = n - 4; // excluding A C BCC1 and BCC2
            unsigned char bcc2_rcv = fr[n - 1];
            unsigned char bcc2_calc = bcc(fr + 3, dlen);
            if (bcc2_rcv != bcc2_calc)
            {
                // send REJ for expected frame
                st_bcc2_err++;
                if (send_su(A_RCV, C_REJ(g_rx_expected)) == 0) st_rej_sent++;
                continue;
            }

            if (ns != g_rx_expected)
            {
                // duplicate, resend RR
                st_rx_dup++;
                if (send_su(A_RCV, C_RR(g_rx_expected)) == 0) st_rr_sent++;
                continue;
            }

            // deliver and ACK next expected
            memcpy(packet, fr + 3, dlen);
            g_rx_expected ^= 1;
            if (send_su(A_RCV, C_RR(g_rx_expected)) == 0) st_rr_sent++;
            st_rx_i_ok++;
            return dlen;
        }
        else if (C == C_DISC)
        {
            // peer is closing; respond according to role in llclose, but if received here just acknowledge path
            // For simplicity, reply DISC here so llclose caller can proceed
            if (send_su(A_RCV, C_DISC) == 0) st_su_sent++;
            st_su_recv++;
            continue;
        }
        else
        {
            // other supervision; ignore
        }
    }
}

//////////////////////////////////////////////
// LLCLOSE
//////////////////////////////////////////////
int llclose()
{
    if (!g_is_open) return -1;

    if (g_params.role == LlTx)
    {
        // Send DISC, wait DISC, send UA
        for (int attempt = 0; attempt <= g_params.nRetransmissions; ++attempt)
        {
            if (send_su(A_SND, C_DISC) < 0)
                break;
            int r = expect_su(A_RCV, C_DISC, 1);
            if (r == 0)
            {
                (void)send_su(A_RCV, C_UA);
                g_is_open = 0;
                // Print statistics
                printf("Link-layer stats:\n");
                printf("  I sent: %lu (retx: %lu)\n", st_tx_i_sent, st_tx_i_retx);
                printf("  I received OK: %lu, duplicates: %lu\n", st_rx_i_ok, st_rx_dup);
                printf("  Supervision sent: %lu, received: %lu\n", st_su_sent, st_su_recv);
                printf("  RR sent: %lu, REJ sent: %lu, RR recv: %lu, REJ recv: %lu\n", st_rr_sent, st_rej_sent, st_rr_recv, st_rej_recv);
                printf("  Timeouts: %lu, BCC1 errors: %lu, BCC2 errors: %lu\n", st_timeouts, st_bcc1_err, st_bcc2_err);
                return closeSerialPort();
            }
            if (r == -2) continue; // timeout -> retry
        }
        g_is_open = 0;
        printf("Link-layer stats:\n");
        printf("  I sent: %lu (retx: %lu)\n", st_tx_i_sent, st_tx_i_retx);
        printf("  I received OK: %lu, duplicates: %lu\n", st_rx_i_ok, st_rx_dup);
        printf("  Supervision sent: %lu, received: %lu\n", st_su_sent, st_su_recv);
        printf("  RR sent: %lu, REJ sent: %lu, RR recv: %lu, REJ recv: %lu\n", st_rr_sent, st_rej_sent, st_rr_recv, st_rej_recv);
        printf("  Timeouts: %lu, BCC1 errors: %lu, BCC2 errors: %lu\n", st_timeouts, st_bcc1_err, st_bcc2_err);
        return closeSerialPort();
    }
    else // LlRx
    {
        // Wait DISC, reply DISC, wait UA
        while (1)
        {
            int r = expect_su(A_SND, C_DISC, 0);
            if (r == 0)
            {
                (void)send_su(A_RCV, C_DISC);
                (void)expect_su(A_RCV, C_UA, 1);
                g_is_open = 0;
                printf("Link-layer stats:\n");
                printf("  I sent: %lu (retx: %lu)\n", st_tx_i_sent, st_tx_i_retx);
                printf("  I received OK: %lu, duplicates: %lu\n", st_rx_i_ok, st_rx_dup);
                printf("  Supervision sent: %lu, received: %lu\n", st_su_sent, st_su_recv);
                printf("  RR sent: %lu, REJ sent: %lu, RR recv: %lu, REJ recv: %lu\n", st_rr_sent, st_rej_sent, st_rr_recv, st_rej_recv);
                printf("  Timeouts: %lu, BCC1 errors: %lu, BCC2 errors: %lu\n", st_timeouts, st_bcc1_err, st_bcc2_err);
                return closeSerialPort();
            }
        }
    }
}
