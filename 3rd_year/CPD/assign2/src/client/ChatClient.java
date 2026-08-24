package client;

import protocol.Protocol;

import java.io.BufferedReader;
import java.io.IOException;
import java.io.InputStreamReader;
import java.io.PrintWriter;
import java.nio.charset.StandardCharsets;
import java.util.Scanner;
import javax.net.ssl.SSLParameters;
import javax.net.ssl.SSLSocket;
import javax.net.ssl.SSLSocketFactory;

public final class ChatClient {
    private static final String DEFAULT_HOST = "localhost";
    private static final int DEFAULT_PORT = 12345;

    private static final long RECONNECT_DELAY_MS = 1000L;

    private static final String HELP_MESSAGE = "Commands: /help, /list, /create <room>, /create-ai <room> <prompt>, /join <room>, /quit. Type anything else to chat.";
    private static final String AUTH_MESSAGE = "Commands: /register <user> <pass>, /login <user> <pass>";

    private ChatClient() {
    }

    public static void main(String[] args) {
        String host = args.length > 0 ? args[0] : DEFAULT_HOST;
        int port = args.length > 1 ? Integer.parseInt(args[1]) : DEFAULT_PORT;

        ClientState state = new ClientState();

        Thread connection = Thread.startVirtualThread(() -> connectLoop(host, port, state));

        try (Scanner console = new Scanner(System.in)) {
            printLocal("AUTH", AUTH_MESSAGE);
            printLocal("HELP", HELP_MESSAGE);

            while (state.isRunning() && console.hasNextLine()) {
                String input = console.nextLine();
                if (input.equalsIgnoreCase("/quit")) {
                    state.send(Protocol.Command.QUIT);
                    state.stop();
                    break;

                } else if (input.equalsIgnoreCase("/help")) {
                    printLocal("AUTH", AUTH_MESSAGE);
                    printLocal("HELP", HELP_MESSAGE);

                } else if (input.startsWith("/register ")) {
                    String[] parts = input.trim().split("\\s+", 3);
                    if (parts.length != 3) {
                        printLocal("ERROR", "Usage: /register <user> <pass>");
                    } else {
                        state.send(Protocol.Command.REGISTER, parts[1], parts[2]);
                    }

                } else if (input.startsWith("/login ")) {
                    String[] parts = input.trim().split("\\s+", 3);
                    if (parts.length != 3) {
                        printLocal("ERROR", "Usage: /login <user> <pass>");
                    } else {
                        state.send(Protocol.Command.LOGIN, parts[1], parts[2]);
                    }

                } else if (input.equalsIgnoreCase("/list")) {
                    state.send(Protocol.Command.LIST);

                } else if (input.startsWith("/create ")) {
                    state.send(Protocol.Command.CREATE, input.substring("/create ".length()).trim());

                } else if (input.startsWith("/join ")) {
                    state.send(Protocol.Command.JOIN, input.substring("/join ".length()).trim());

                } else if (input.startsWith("/create-ai ")) {
                    String[] parts = input.trim().split("\\s+", 3);
                    if (parts.length != 3) {
                        printLocal("ERROR", "Usage: /create-ai <room> <prompt>");
                    } else {
                        state.send(Protocol.Command.CREATE_AI, parts[1], parts[2]);
                    }

                } else if (input.startsWith("/")) {
                    printLocal("ERROR", "Unknown command.");

                } else if (!input.isBlank()) {
                    state.send(Protocol.Command.MSG, input);
                }
            }

            state.stop();
            connection.join();
        } catch (InterruptedException e) {
            Thread.currentThread().interrupt();
            state.stop();
        }
    }

    private static void connectLoop(String host, int port, ClientState state) {
        while (state.isRunning()) {
            try (
                    SSLSocket socket = createSecureSocket(host, port);
                    BufferedReader in = new BufferedReader(new InputStreamReader(socket.getInputStream(), StandardCharsets.UTF_8));
                    PrintWriter out = new PrintWriter(socket.getOutputStream(), true, StandardCharsets.UTF_8)
            ) {
                state.attach(socket, out);
                readServer(in, state);
            } catch (IOException e) {
                if (state.isRunning()) {
                    printLocal("ERROR", "Connection failed: " + e.getMessage());
                }
            } finally {
                state.detach();
            }

            if (state.isRunning()) {
                sleepBeforeReconnect();
            }
        }
    }

    private static void sleepBeforeReconnect() {
        try {
            Thread.sleep(RECONNECT_DELAY_MS);
        } catch (InterruptedException e) {
            Thread.currentThread().interrupt();
        }
    }

    private static SSLSocket createSecureSocket(String host, int port) throws IOException {
        SSLSocketFactory factory = (SSLSocketFactory) SSLSocketFactory.getDefault();
        SSLSocket socket = (SSLSocket) factory.createSocket(host, port);
        try {
            socket.setEnabledProtocols(enabledTlsProtocols(socket.getSupportedProtocols()));

            SSLParameters parameters = socket.getSSLParameters();
            parameters.setEndpointIdentificationAlgorithm("HTTPS");
            socket.setSSLParameters(parameters);
            socket.startHandshake();
            return socket;
        } catch (IOException | RuntimeException e) {
            try {
                socket.close();
            } catch (IOException closeException) {
                e.addSuppressed(closeException);
            }
            throw e;
        }
    }

    private static String[] enabledTlsProtocols(String[] supportedProtocols) {
        boolean tls13 = false;
        boolean tls12 = false;
        for (String protocol : supportedProtocols) {
            if ("TLSv1.3".equals(protocol)) {
                tls13 = true;
            } else if ("TLSv1.2".equals(protocol)) {
                tls12 = true;
            }
        }
        if (tls13 && tls12) {
            return new String[]{"TLSv1.3", "TLSv1.2"};
        }
        if (tls13) {
            return new String[]{"TLSv1.3"};
        }
        if (tls12) {
            return new String[]{"TLSv1.2"};
        }
        throw new IllegalStateException("No supported secure TLS protocol found.");
    }

    private static void readServer(BufferedReader in, ClientState state) {
        try {
            String line;
            while ((line = in.readLine()) != null) {
                handleFrame(Protocol.decode(line), state);
            }
            if (state.isRunning()) {
                printLocal("INFO", "Disconnected from server.");
            }
        } catch (IOException e) {
            if (state.isRunning()) {
                printLocal("ERROR", "Disconnected from server: " + e.getMessage());
            }
        } catch (IllegalArgumentException e) {
            printLocal("ERROR", "Received invalid message from server.");
        }
    }

    private static void handleFrame(Protocol.Frame frame, ClientState state) {
        switch (frame.command()) {
            case OK -> System.out.println("[OK] " + arg(frame, 0));
            case ERROR -> System.out.println("[ERROR] " + arg(frame, 0));
            case AUTH_OK -> handleAuthOk(frame, state);
            case AUTH_FAIL -> handleAuthFail(frame, state);
            case TOKEN -> state.saveToken(arg(frame, 0));
            case ROOMS -> System.out.println("[ROOMS] " + arg(frame, 0));
            case LEFT -> System.out.println("[LEFT] " + arg(frame, 0));
            case CREATED -> System.out.println("[CREATED] " + arg(frame, 0));
            case JOINED -> System.out.println("[JOINED] " + arg(frame, 0));
            case MESSAGE -> System.out.println(arg(frame, 1) + ": " + arg(frame, 2));
            case INFO -> System.out.println("[INFO] " + arg(frame, 1));
            default -> System.out.println("[SERVER] " + frame.command() + " " + frame.args());
        }
    }

    private static void handleAuthOk(Protocol.Frame frame, ClientState state) {
        state.finishResumeAttempt();
        System.out.println("[AUTH_OK] " + arg(frame, 0));
    }

    private static void handleAuthFail(Protocol.Frame frame, ClientState state) {
        String reason = arg(frame, 0);
        if (!state.failResumeAttempt(reason)) {
            System.out.println("[AUTH_FAIL] " + reason);
            return;
        }

        String action = ClientState.SESSION_ACTIVE_ELSEWHERE.equals(reason)
                ? " Retrying resume."
                : " Log in again.";
        System.out.println("[AUTH_FAIL] " + reason + action);
    }

    private static String arg(Protocol.Frame frame, int index) {
        return index < frame.size() ? frame.arg(index) : "";
    }

    private static void printLocal(String label, String message) {
        System.out.println("{" + label + "} " + message);
    }

    private static final class ClientState {
        private static final String SESSION_ACTIVE_ELSEWHERE = "Session already active elsewhere.";

        private SSLSocket socket;
        private PrintWriter out;
        private String token;
        private boolean running = true;
        private boolean resumeAttempt;

        synchronized boolean isRunning() {
            return running;
        }

        synchronized void attach(SSLSocket socket, PrintWriter out) {
            this.socket = socket;
            this.out = out;
            if (token != null) {
                resumeAttempt = true;
                out.println(Protocol.encode(Protocol.Command.RESUME, token));
            }
        }

        synchronized void detach() {
            socket = null;
            out = null;
            resumeAttempt = false;
        }

        synchronized void saveToken(String token) {
            this.token = token;
        }

        synchronized void finishResumeAttempt() {
            resumeAttempt = false;
        }

        synchronized boolean failResumeAttempt(String reason) {
            if (!resumeAttempt) {
                return false;
            }
            resumeAttempt = false;
            if (SESSION_ACTIVE_ELSEWHERE.equals(reason)) {
                closeSocket();
            } else {
                token = null;
            }
            return true;
        }

        synchronized void send(Protocol.Command command, String... args) {
            if (out == null) {
                printLocal("INFO", "Not connected. Waiting for reconnection.");
                return;
            }
            out.println(Protocol.encode(command, args));
            if (out.checkError()) {
                closeSocket();
            }
        }

        synchronized void stop() {
            running = false;
            closeSocket();
        }

        private void closeSocket() {
            if (socket == null) {
                return;
            }
            try {
                socket.close();
            } catch (IOException ignored) {
                // connection unusable
            }
        }
    }
}
