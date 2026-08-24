// Application layer protocol implementation (START/END/DATA TLV)

#include "application_layer.h"
#include "link_layer.h"

#include <stdint.h>
#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <time.h>

// App-layer control codes
#define APP_START 0x02
#define APP_END   0x03
#define APP_DATA  0x01

// TLV types
#define T_FILE_SIZE 0x00
#define T_FILE_NAME 0x01

static void fill_link_params(LinkLayer *p, const char *serialPort, LinkLayerRole role, int baudRate, int nTries, int timeout)
{
    memset(p, 0, sizeof(*p));
    strncpy(p->serialPort, serialPort, sizeof(p->serialPort) - 1);
    p->role = role;
    p->baudRate = baudRate;
    p->nRetransmissions = nTries;
    p->timeout = timeout;
}

static int build_start_end(unsigned char *out, int outcap, unsigned char C, unsigned long long fsize, const char *fname)
{
    // encode file size as ASCII decimal to keep it simple
    char szbuf[32];
    snprintf(szbuf, sizeof(szbuf), "%llu", (unsigned long long)fsize);
    int szlen = (int)strlen(szbuf);
    int fnamelen = (int)strlen(fname);

    int pos = 0;
    if (pos + 1 > outcap) return -1;
    out[pos++] = C;
    if (pos + 2 + szlen > outcap) return -1; // T,L,V for size
    out[pos++] = T_FILE_SIZE;
    out[pos++] = (unsigned char)szlen;
    memcpy(out + pos, szbuf, szlen);
    pos += szlen;
    if (pos + 2 + fnamelen > outcap) return -1; // T,L,V for name
    out[pos++] = T_FILE_NAME;
    out[pos++] = (unsigned char)fnamelen;
    memcpy(out + pos, fname, fnamelen);
    pos += fnamelen;
    return pos;
}

static int build_data(unsigned char *out, int outcap, unsigned char seq, const unsigned char *data, int dlen)
{
    if (dlen > MAX_PAYLOAD_SIZE - 4) dlen = MAX_PAYLOAD_SIZE - 4; // reserve header space
    int pos = 0;
    if (pos + 1 > outcap) return -1;
    out[pos++] = APP_DATA; // C
    if (pos + 1 > outcap) return -1;
    out[pos++] = seq;       // N
    // L2 L1 (big-endian: L2=MSB, L1=LSB) common in these labs
    if (pos + 2 > outcap) return -1;
    out[pos++] = (unsigned char)((dlen >> 8) & 0xFF); // L2
    out[pos++] = (unsigned char)(dlen & 0xFF);        // L1
    if (pos + dlen > outcap) return -1;
    memcpy(out + pos, data, dlen);
    pos += dlen;
    return pos;
}

void applicationLayer(const char *serialPort, const char *role, int baudRate,
                      int nTries, int timeout, const char *filename)
{
    LinkLayer params;
    LinkLayerRole r = (strcmp(role, "tx") == 0) ? LlTx : LlRx;
    fill_link_params(&params, serialPort, r, baudRate, nTries, timeout);

    if (llopen(params) < 0)
    {
        fprintf(stderr, "ERROR: llopen failed\n");
        return;
    }

    if (r == LlTx)
    {
        FILE *f = fopen(filename, "rb");
        if (!f)
        {
            fprintf(stderr, "ERROR: could not open file '%s' for reading\n", filename);
            llclose();
            return;
        }

        if (fseek(f, 0, SEEK_END) != 0)
        {
            fprintf(stderr, "ERROR: fseek failed\n");
            fclose(f);
            llclose();
            return;
        }
        long fsize_long = ftell(f);
        if (fsize_long < 0)
        {
            fprintf(stderr, "ERROR: ftell failed\n");
            fclose(f);
            llclose();
            return;
        }
        rewind(f);
        unsigned long long fsize = (unsigned long long)fsize_long;

        unsigned char ctrl[MAX_PAYLOAD_SIZE];
        int clen = build_start_end(ctrl, sizeof(ctrl), APP_START, fsize, filename);
        if (clen < 0 || llwrite(ctrl, clen) < 0)
        {
            fprintf(stderr, "ERROR: failed to send START\n");
            fclose(f);
            llclose();
            return;
        }

        unsigned char seq = 0;
        unsigned char buf_raw[MAX_PAYLOAD_SIZE - 4];
        for (;;)
        {
            int n = (int)fread(buf_raw, 1, sizeof(buf_raw), f);
            if (n <= 0) break;
            unsigned char dpk[MAX_PAYLOAD_SIZE];
            int dlen = build_data(dpk, sizeof(dpk), seq, buf_raw, n);
            if (dlen < 0 || llwrite(dpk, dlen) < 0)
            {
                fprintf(stderr, "ERROR: failed to send DATA\n");
                fclose(f);
                llclose();
                return;
            }
            seq = (unsigned char)((seq + 1) % 256);
        }
        fclose(f);

        clen = build_start_end(ctrl, sizeof(ctrl), APP_END, fsize, filename);
        if (clen < 0 || llwrite(ctrl, clen) < 0)
        {
            fprintf(stderr, "ERROR: failed to send END\n");
            llclose();
            return;
        }

        if (llclose() < 0)
            fprintf(stderr, "WARN: llclose failed on TX\n");
    }
    else // LlRx
    {
        // Receive START
        unsigned char ctrl[MAX_PAYLOAD_SIZE];
        int n = llread(ctrl);
        if (n < 0 || n < 1 || ctrl[0] != APP_START)
        {
            fprintf(stderr, "ERROR: did not receive START\n");
            llclose();
            return;
        }

        unsigned long long fsize = 0ULL;
        char fname[256] = {0};
        // Parse TLVs after C
        for (int i = 1; i + 1 < n; )
        {
            unsigned char T = ctrl[i++];
            unsigned char L = ctrl[i++];
            if (i + L > n) break;
            if (T == T_FILE_SIZE)
            {
                char tmp[64] = {0};
                int len = (L < 63 ? L : 63);
                memcpy(tmp, &ctrl[i], len);
                fsize = strtoull(tmp, NULL, 10);
            }
            else if (T == T_FILE_NAME)
            {
                int len = (L < 255 ? L : 255);
                memcpy(fname, &ctrl[i], len);
                fname[len] = '\0';
            }
            i += L;
        }
        if (fname[0] == '\0')
        {
            // missing name in TLV, keep empty and rely on provided filename
        }

        FILE *f = fopen(filename, "wb");
        if (!f)
        {
            fprintf(stderr, "ERROR: could not open file '%s' for writing\n", filename);
            llclose();
            return;
        }

        unsigned long long received = 0ULL;
        int started = 0;
        struct timespec t0 = {0}, t1 = {0};
        for (;;)
        {
            unsigned char pkt[MAX_PAYLOAD_SIZE];
            int m = llread(pkt);
            if (m < 0)
            {
                fprintf(stderr, "ERROR: llread failed\n");
                fclose(f);
                llclose();
                return;
            }
            if (m < 1) continue;

            if (pkt[0] == APP_DATA)
            {
                if (m < 4) continue; // malformed
                unsigned char N = pkt[1]; (void)N; // not used for ordering at app level
                int L2 = pkt[2];
                int L1 = pkt[3];
                int dlen = (L2 << 8) | L1;
                if (4 + dlen > m) continue;
                if (!started) { clock_gettime(CLOCK_MONOTONIC, &t0); started = 1; }
                size_t w = fwrite(pkt + 4, 1, dlen, f);
                if ((int)w != dlen)
                {
                    fprintf(stderr, "ERROR: fwrite failed\n");
                    fclose(f);
                    llclose();
                    return;
                }
                received += (unsigned long long)dlen;
            }
            else if (pkt[0] == APP_END)
            {
                if (started) clock_gettime(CLOCK_MONOTONIC, &t1);
                break;
            }
            else if (pkt[0] == APP_START)
            {
                // ignore duplicate START
            }
        }
        fclose(f);
        printf("RX: received %llu/%llu bytes into '%s' (TX name: '%s')\n", received, fsize, filename, (fname[0]?fname:"<none>"));
        if (started)
        {
            double dt = (t1.tv_sec - t0.tv_sec) + (t1.tv_nsec - t0.tv_nsec) / 1e9;
            if (dt > 0)
            {
                double R = (received * 8.0) / dt; // bits/s
                double C = (double)baudRate;      // line capacity in bits/s
                double S = R / C;
                printf("Throughput: %.2f bit/s, Efficiency S=R/C=%.3f (C=%.0f)\n", R, S, C);
            }
        }

        if (llclose() < 0)
            fprintf(stderr, "WARN: llclose failed on RX\n");
    }
}
