// AI-generated tests!

import protocol.Protocol;

import java.io.BufferedReader;
import java.io.EOFException;
import java.io.IOException;
import java.io.InputStream;
import java.io.InputStreamReader;
import java.io.PrintWriter;
import java.net.InetSocketAddress;
import java.net.ServerSocket;
import java.net.SocketTimeoutException;
import java.nio.charset.StandardCharsets;
import java.nio.file.Files;
import java.nio.file.Path;
import java.security.GeneralSecurityException;
import java.security.KeyStore;
import java.time.Duration;
import java.util.ArrayDeque;
import java.util.Deque;
import java.util.List;
import java.util.Objects;
import java.util.concurrent.TimeUnit;
import java.util.function.Predicate;
import javax.net.ssl.SSLContext;
import javax.net.ssl.SSLParameters;
import javax.net.ssl.SSLSocket;
import javax.net.ssl.SSLSocketFactory;
import javax.net.ssl.TrustManagerFactory;

/**
 * Visual, transcript-style demos for features that are awkward to show with the
 * interactive terminal client.
 *
 * Compile from assign2 with:
 *   javac --release 21 -d out $(find src -name '*.java') test/IntegrationTests.java test/VisualDemoTests.java
 *
 * Run all demos:
 *   java -cp out VisualDemoTests
 *
 * Run one demo:
 *   java -cp out VisualDemoTests reconnect
 *   java -cp out VisualDemoTests slow-client
 */
public final class VisualDemoTests {
    private static final String HOST = "127.0.0.1";
    private static final Duration SERVER_START_TIMEOUT = Duration.ofSeconds(5);
    private static final Duration FRAME_TIMEOUT = Duration.ofSeconds(4);
    private static final Duration SLOW_CLIENT_TIMEOUT = Duration.ofSeconds(8);
    private static final int SLOW_FLOOD_MESSAGES = 250;
    private static final int SLOW_PAYLOAD_CHARS = 65_536;

    private int failed;
    private TlsFixture tlsFixture;

    public static void main(String[] args) {
        String mode = args.length == 0 ? "all" : args[0].trim().toLowerCase();
        int exitCode = new VisualDemoTests().run(mode);
        if (exitCode != 0) {
            System.exit(exitCode);
        }
    }

    private int run(String mode) {
        Path tempDir = null;
        Path usersFile = null;
        Process server = null;

        try {
            tempDir = Files.createTempDirectory("cpd-chat-visual-demo-");
            usersFile = tempDir.resolve("users.txt");
            tlsFixture = createTlsFixture(tempDir);
            int port = findFreePort();

            server = startServer(port, usersFile);
            waitForServer(server, port);

            demo("temporary server started on port " + port);

            switch (mode) {
                case "all" -> {
                    runDemo("automatic reconnect", () -> reconnectDemo(port));
                    runDemo("best-effort slow-client disconnect", () -> slowClientDemo(port));
                }
                case "reconnect" -> runDemo("automatic reconnect", () -> reconnectDemo(port));
                case "slow-client" -> runDemo("best-effort slow-client disconnect", () -> slowClientDemo(port));
                default -> {
                    fail("unknown mode: " + mode);
                    System.out.println("Usage: java -cp out VisualDemoTests [all|reconnect|slow-client]");
                }
            }
        } catch (Throwable e) {
            fail("setup failed: " + cleanMessage(e));
        } finally {
            if (server != null) {
                try {
                    stopServer(server);
                } catch (InterruptedException e) {
                    Thread.currentThread().interrupt();
                    fail("server cleanup interrupted");
                }
            }
            if (usersFile != null) {
                deleteIfExists(usersFile);
            }
            if (tlsFixture != null) {
                deleteIfExists(tlsFixture.keyStore());
                tlsFixture = null;
            }
            if (tempDir != null) {
                deleteIfExists(tempDir);
            }
        }

        if (failed == 0) {
            demo("visual demo finished");
            return 0;
        }
        System.out.println("[FAIL] visual demo finished with " + failed + " failure(s)");
        return 1;
    }

    private void reconnectDemo(int port) throws Exception {
        String token;

        try (DemoClient alice = new DemoClient("alice", port);
             DemoClient bob = new DemoClient("bob", port)) {
            demo("Alice connects, registers, logs in, and joins DemoReconnect.");
            alice.send(Protocol.Command.REGISTER, "visualAlice", "alice-pass");
            alice.waitForLogged("ALICE", Protocol.Command.OK,
                    frame -> arg(frame, 0).contains("Registered"), FRAME_TIMEOUT);

            alice.send(Protocol.Command.LOGIN, "visualAlice", "alice-pass");
            token = arg(alice.waitForLogged("ALICE", Protocol.Command.TOKEN,
                    frame -> !arg(frame, 0).isBlank(), FRAME_TIMEOUT), 0);
            alice.waitForLogged("ALICE", Protocol.Command.AUTH_OK,
                    frame -> arg(frame, 0).equals("visualAlice"), FRAME_TIMEOUT);
            alice.waitForLogged("ALICE", Protocol.Command.ROOMS,
                    frame -> arg(frame, 0).contains("Lobby"), FRAME_TIMEOUT);

            alice.send(Protocol.Command.CREATE, "DemoReconnect");
            alice.waitForLogged("ALICE", Protocol.Command.CREATED,
                    frame -> arg(frame, 0).equals("DemoReconnect"), FRAME_TIMEOUT);

            alice.send(Protocol.Command.JOIN, "DemoReconnect");
            alice.waitForLogged("ALICE", Protocol.Command.JOINED,
                    frame -> arg(frame, 0).equals("DemoReconnect"), FRAME_TIMEOUT);

            registerAndLoginSilently(bob, "visualBob", "bob-pass");
            bob.send(Protocol.Command.JOIN, "DemoReconnect");
            bob.waitFor(Protocol.Command.JOINED,
                    frame -> arg(frame, 0).equals("DemoReconnect"), FRAME_TIMEOUT);
            alice.waitForLogged("ALICE", Protocol.Command.INFO,
                    frame -> arg(frame, 1).contains("visualBob enters"), FRAME_TIMEOUT);

            demo("Simulating Alice's broken TCP connection by closing only her socket.");
            alice.close();
            Thread.sleep(500);

            demo("Bob keeps using the room while Alice is disconnected.");
            bob.send(Protocol.Command.MSG, "Are you still there?");
            bob.waitFor(Protocol.Command.MESSAGE,
                    message("DemoReconnect", "visualBob", "Are you still there?"),
                    FRAME_TIMEOUT);

            demo("Alice reconnects with her saved token, without sending her password again.");
        }

        try (DemoClient alice = new DemoClient("alice-resumed", port)) {
            alice.send(Protocol.Command.RESUME, token);
            alice.waitForLogged("ALICE", Protocol.Command.MESSAGE,
                    message("DemoReconnect", "visualBob", "Are you still there?"),
                    FRAME_TIMEOUT);
            alice.waitForLogged("ALICE", Protocol.Command.AUTH_OK,
                    frame -> arg(frame, 0).equals("visualAlice"), FRAME_TIMEOUT);
            alice.waitForLogged("ALICE", Protocol.Command.JOINED,
                    frame -> arg(frame, 0).equals("DemoReconnect"), FRAME_TIMEOUT);
            demo("Alice resumed the same room and received the missed message.");
        }
    }

    private void slowClientDemo(int port) throws Exception {
        try (DemoClient alice = new DemoClient("alice-slow-demo", port);
             DemoClient slow = new DemoClient("slow-client", port)) {
            demo("Alice is a normal client. Another synthetic client will join and then stop reading.");

            registerAndLoginSilently(alice, "slowDemoAlice", "alice-pass");
            alice.send(Protocol.Command.CREATE, "DemoSlowClient");
            alice.waitForLogged("ALICE", Protocol.Command.CREATED,
                    frame -> arg(frame, 0).equals("DemoSlowClient"), FRAME_TIMEOUT);
            alice.send(Protocol.Command.JOIN, "DemoSlowClient");
            alice.waitForLogged("ALICE", Protocol.Command.JOINED,
                    frame -> arg(frame, 0).equals("DemoSlowClient"), FRAME_TIMEOUT);

            registerAndLoginSilently(slow, "visualSlow", "slow-pass");
            slow.send(Protocol.Command.JOIN, "DemoSlowClient");
            slow.waitFor(Protocol.Command.JOINED,
                    frame -> arg(frame, 0).equals("DemoSlowClient"), FRAME_TIMEOUT);
            alice.waitForLogged("ALICE", Protocol.Command.INFO,
                    frame -> arg(frame, 1).contains("visualSlow enters"), FRAME_TIMEOUT);

            demo("The slow client now stops reading from its TCP socket.");
            CountingReader aliceReader = new CountingReader("ALICE", alice);
            Thread readerThread = Thread.startVirtualThread(aliceReader);

            String payload = "X".repeat(SLOW_PAYLOAD_CHARS);
            for (int i = 1; i <= SLOW_FLOOD_MESSAGES; i++) {
                alice.send(Protocol.Command.MSG, "flood-" + i + " " + payload);
                if (i % 50 == 0) {
                    demo("Alice sent " + i + " large messages and is still writing normally.");
                }
            }

            Thread.sleep(1000);
            demo("Alice remained active and received " + aliceReader.messageCount() + " echoed message(s).");
            demo("Now checking what happened to the client that was not reading.");

            SlowClientResult result = inspectSlowClient(slow);
            switch (result.kind()) {
                case ERROR_FRAME -> demo("Slow client received server error: " + result.detail());
                case CLOSED -> demo("Slow client socket was closed by the server.");
                case NOT_TRIGGERED -> warn(result.detail());
            }

            aliceReader.stop();
            alice.close();
            readerThread.join(1000);
        }
    }

    private SlowClientResult inspectSlowClient(DemoClient slow) throws IOException {
        long deadline = System.nanoTime() + SLOW_CLIENT_TIMEOUT.toNanos();
        int skippedMessages = 0;

        while (System.nanoTime() < deadline) {
            try {
                Protocol.Frame frame = slow.readFrame(deadline);
                if (frame.command() == Protocol.Command.ERROR) {
                    return new SlowClientResult(SlowClientResult.Kind.ERROR_FRAME, arg(frame, 0));
                }
                if (frame.command() == Protocol.Command.MESSAGE) {
                    skippedMessages++;
                    if (skippedMessages % 25 == 0) {
                        demo("Read past " + skippedMessages + " queued message(s) from the slow client socket.");
                    }
                }
            } catch (EOFException e) {
                return new SlowClientResult(SlowClientResult.Kind.CLOSED, "socket closed");
            } catch (IllegalArgumentException e) {
                return new SlowClientResult(SlowClientResult.Kind.CLOSED, "truncated protocol frame while socket closed");
            } catch (SocketTimeoutException e) {
                break;
            }
        }

        return new SlowClientResult(
                SlowClientResult.Kind.NOT_TRIGGERED,
                "Best-effort slow-client demo did not force a disconnect on this machine. "
                        + "TCP buffers may have absorbed the flood before the server queue filled."
        );
    }

    private void registerAndLoginSilently(DemoClient client, String username, String password) throws IOException {
        client.send(Protocol.Command.REGISTER, username, password);
        client.waitFor(Protocol.Command.OK, frame -> arg(frame, 0).contains("Registered"), FRAME_TIMEOUT);

        client.send(Protocol.Command.LOGIN, username, password);
        client.waitFor(Protocol.Command.TOKEN, frame -> !arg(frame, 0).isBlank(), FRAME_TIMEOUT);
        client.waitFor(Protocol.Command.AUTH_OK, frame -> arg(frame, 0).equals(username), FRAME_TIMEOUT);
        client.waitFor(Protocol.Command.ROOMS, frame -> arg(frame, 0).contains("Lobby"), FRAME_TIMEOUT);
    }

    private void runDemo(String name, DemoRunnable action) {
        System.out.println();
        System.out.println("=== " + name + " ===");
        try {
            action.run();
            System.out.println("[PASS] " + name);
        } catch (Throwable e) {
            fail(name + ": " + cleanMessage(e));
        }
    }

    private Predicate<Protocol.Frame> message(String room, String username, String text) {
        return frame -> arg(frame, 0).equals(room)
                && arg(frame, 1).equals(username)
                && arg(frame, 2).equals(text);
    }

    private Process startServer(int port, Path usersFile) throws IOException {
        TlsFixture tls = requireTlsFixture();
        String javaBin = Path.of(System.getProperty("java.home"), "bin", "java").toString();
        String classPath = System.getProperty("java.class.path");

        ProcessBuilder builder = new ProcessBuilder(
                javaBin,
                "-Djavax.net.ssl.keyStore=" + tls.keyStore(),
                "-Djavax.net.ssl.keyStorePassword=" + tls.password(),
                "-Djavax.net.ssl.keyStoreType=PKCS12",
                "-cp",
                classPath,
                "server.ChatServer",
                Integer.toString(port),
                usersFile.toString()
        );
        builder.redirectOutput(ProcessBuilder.Redirect.DISCARD);
        builder.redirectError(ProcessBuilder.Redirect.DISCARD);
        return builder.start();
    }

    private void waitForServer(Process server, int port) throws Exception {
        long deadline = System.nanoTime() + SERVER_START_TIMEOUT.toNanos();

        while (System.nanoTime() < deadline) {
            if (!server.isAlive()) {
                throw new AssertionError("server exited before accepting connections");
            }

            try (SSLSocket ignored = createClientSocket(port, 200)) {
                return;
            } catch (IOException e) {
                Thread.sleep(100);
            }
        }

        throw new AssertionError("server did not start within " + SERVER_START_TIMEOUT);
    }

    private TlsFixture createTlsFixture(Path tempDir) throws IOException, GeneralSecurityException, InterruptedException {
        Path keyStore = tempDir.resolve("server-keystore.p12");
        String password = "changeit";
        generateKeyStore(keyStore, password);

        KeyStore trustStore = KeyStore.getInstance("PKCS12");
        try (InputStream in = Files.newInputStream(keyStore)) {
            trustStore.load(in, password.toCharArray());
        }

        TrustManagerFactory trustManagers = TrustManagerFactory.getInstance(TrustManagerFactory.getDefaultAlgorithm());
        trustManagers.init(trustStore);

        SSLContext sslContext = SSLContext.getInstance("TLS");
        sslContext.init(null, trustManagers.getTrustManagers(), null);
        return new TlsFixture(keyStore, password, sslContext.getSocketFactory());
    }

    private void generateKeyStore(Path keyStore, String password) throws IOException, InterruptedException {
        String keytool = Path.of(System.getProperty("java.home"), "bin", keytoolName()).toString();
        ProcessBuilder builder = new ProcessBuilder(
                keytool,
                "-genkeypair",
                "-alias", "visual-demo",
                "-keyalg", "RSA",
                "-keysize", "2048",
                "-validity", "1",
                "-storetype", "PKCS12",
                "-keystore", keyStore.toString(),
                "-storepass", password,
                "-keypass", password,
                "-dname", "CN=localhost, OU=Visual Demo Tests, O=CPD, L=Porto, ST=Porto, C=PT",
                "-ext", "san=dns:localhost,ip:127.0.0.1"
        );
        builder.redirectErrorStream(true);

        Process process = builder.start();
        boolean finished = process.waitFor(10, TimeUnit.SECONDS);
        if (!finished) {
            process.destroyForcibly();
            process.waitFor(3, TimeUnit.SECONDS);
            throw new IOException("keytool did not finish while creating visual-demo keystore");
        }

        String output = new String(process.getInputStream().readAllBytes(), StandardCharsets.UTF_8).trim();
        if (process.exitValue() != 0) {
            throw new IOException("keytool failed while creating visual-demo keystore: " + output);
        }
    }

    private String keytoolName() {
        return System.getProperty("os.name", "").startsWith("Windows") ? "keytool.exe" : "keytool";
    }

    private SSLSocket createClientSocket(int port, int connectTimeoutMillis) throws IOException {
        SSLSocket socket = (SSLSocket) requireTlsFixture().clientSocketFactory().createSocket();
        try {
            socket.connect(new InetSocketAddress(HOST, port), connectTimeoutMillis);
            SSLParameters parameters = socket.getSSLParameters();
            parameters.setEndpointIdentificationAlgorithm("HTTPS");
            socket.setSSLParameters(parameters);
            socket.startHandshake();
            return socket;
        } catch (IOException e) {
            try {
                socket.close();
            } catch (IOException ignored) {
                // Connection setup already failed.
            }
            throw e;
        }
    }

    private TlsFixture requireTlsFixture() {
        if (tlsFixture == null) {
            throw new IllegalStateException("TLS fixture was not initialized");
        }
        return tlsFixture;
    }

    private record TlsFixture(Path keyStore, String password, SSLSocketFactory clientSocketFactory) {
    }

    private int findFreePort() throws IOException {
        try (ServerSocket socket = new ServerSocket(0)) {
            return socket.getLocalPort();
        }
    }

    private void stopServer(Process server) throws InterruptedException {
        server.destroy();
        if (!server.waitFor(3, TimeUnit.SECONDS)) {
            server.destroyForcibly();
            server.waitFor(3, TimeUnit.SECONDS);
        }
    }

    private void deleteIfExists(Path path) {
        try {
            Files.deleteIfExists(path);
        } catch (IOException ignored) {
            // Best-effort cleanup for temporary demo files.
        }
    }

    private void demo(String message) {
        System.out.println("[DEMO] " + message);
    }

    private void warn(String message) {
        System.out.println("[WARN] " + message);
    }

    private void fail(String message) {
        failed++;
        System.out.println("[FAIL] " + message);
    }

    private String cleanMessage(Throwable e) {
        String message = e.getMessage();
        if (message == null || message.isBlank()) {
            return e.getClass().getSimpleName();
        }
        if (e instanceof AssertionError) {
            return message;
        }
        return e.getClass().getSimpleName() + ": " + message;
    }

    private static String arg(Protocol.Frame frame, int index) {
        return index < frame.size() ? frame.arg(index) : "";
    }

    private static String viewLine(Protocol.Frame frame) {
        return switch (frame.command()) {
            case OK -> "[OK] " + arg(frame, 0);
            case ERROR -> "[ERROR] " + arg(frame, 0);
            case AUTH_OK -> "[AUTH_OK] " + arg(frame, 0);
            case AUTH_FAIL -> "[AUTH_FAIL] " + arg(frame, 0);
            case TOKEN -> "[TOKEN] <received>";
            case ROOMS -> "[ROOMS] " + arg(frame, 0);
            case CREATED -> "[CREATED] " + arg(frame, 0);
            case JOINED -> "[JOINED] " + arg(frame, 0);
            case LEFT -> "[LEFT] " + arg(frame, 0);
            case INFO -> "[INFO] " + arg(frame, 1);
            case MESSAGE -> arg(frame, 1) + ": " + summarize(arg(frame, 2));
            default -> "[" + frame.command() + "] " + frame.args();
        };
    }

    private static String summarize(String text) {
        int limit = 80;
        if (text.length() <= limit) {
            return text;
        }
        return text.substring(0, limit) + "... (" + text.length() + " chars)";
    }

    @FunctionalInterface
    private interface DemoRunnable {
        void run() throws Exception;
    }

    private record SlowClientResult(Kind kind, String detail) {
        private enum Kind {
            ERROR_FRAME,
            CLOSED,
            NOT_TRIGGERED
        }
    }

    private static final class CountingReader implements Runnable {
        private final String view;
        private final DemoClient client;
        private boolean running = true;
        private int messages;

        private CountingReader(String view, DemoClient client) {
            this.view = view;
            this.client = client;
        }

        @Override
        public void run() {
            while (isRunning()) {
                try {
                    Protocol.Frame frame = client.readFrame(System.nanoTime() + TimeUnit.MILLISECONDS.toNanos(500));
                    if (frame.command() == Protocol.Command.MESSAGE) {
                        int count = incrementMessages();
                        if (count == 1 || count % 50 == 0) {
                            System.out.println("[" + view + "] received " + count + " room message(s) so far");
                        }
                    } else if (frame.command() == Protocol.Command.ERROR) {
                        System.out.println("[" + view + "] " + viewLine(frame));
                    }
                } catch (SocketTimeoutException ignored) {
                    // Poll again unless stopped.
                } catch (EOFException e) {
                    return;
                } catch (IOException e) {
                    if (isRunning()) {
                        System.out.println("[" + view + "] reader stopped: " + e.getMessage());
                    }
                    return;
                }
            }
        }

        private synchronized int incrementMessages() {
            messages++;
            return messages;
        }

        private synchronized int messageCount() {
            return messages;
        }

        private synchronized boolean isRunning() {
            return running;
        }

        private synchronized void stop() {
            running = false;
        }
    }

    private final class DemoClient implements AutoCloseable {
        private final String label;
        private final SSLSocket socket;
        private final BufferedReader in;
        private final PrintWriter out;
        private final Deque<Protocol.Frame> buffered = new ArrayDeque<>();

        private DemoClient(String label, int port) throws IOException {
            this.label = Objects.requireNonNull(label, "label");
            this.socket = createClientSocket(port, 1_000);
            this.in = new BufferedReader(new InputStreamReader(socket.getInputStream(), StandardCharsets.UTF_8));
            this.out = new PrintWriter(socket.getOutputStream(), true, StandardCharsets.UTF_8);
        }

        private void send(Protocol.Command command, String... args) throws IOException {
            out.println(Protocol.encode(command, args));
            if (out.checkError()) {
                throw new IOException(label + " failed to write " + command);
            }
        }

        private Protocol.Frame waitForLogged(String view,
                                             Protocol.Command command,
                                             Predicate<Protocol.Frame> predicate,
                                             Duration timeout) throws IOException {
            return waitFor(frame -> {
                System.out.println("[" + view + "] " + viewLine(frame));
                return frame.command() == command && predicate.test(frame);
            }, timeout, "expected " + command);
        }

        private Protocol.Frame waitFor(Protocol.Command command,
                                       Predicate<Protocol.Frame> predicate,
                                       Duration timeout) throws IOException {
            return waitFor(frame -> frame.command() == command && predicate.test(frame), timeout,
                    "expected " + command);
        }

        private Protocol.Frame waitFor(Predicate<Protocol.Frame> predicate,
                                       Duration timeout,
                                       String description) throws IOException {
            long deadline = System.nanoTime() + timeout.toNanos();
            while (System.nanoTime() < deadline) {
                try {
                    Protocol.Frame frame = readFrame(deadline);
                    if (predicate.test(frame)) {
                        return frame;
                    }
                } catch (SocketTimeoutException e) {
                    break;
                }
            }
            throw new AssertionError(label + " timed out: " + description);
        }

        private Protocol.Frame readFrame(long deadline) throws IOException {
            if (!buffered.isEmpty()) {
                return buffered.removeFirst();
            }

            while (true) {
                long remainingNanos = deadline - System.nanoTime();
                if (remainingNanos <= 0) {
                    throw new SocketTimeoutException("deadline reached");
                }

                int timeoutMs = (int) Math.max(1, Math.min(250, TimeUnit.NANOSECONDS.toMillis(remainingNanos)));
                socket.setSoTimeout(timeoutMs);

                try {
                    String line = in.readLine();
                    if (line == null) {
                        throw new EOFException(label + " connection closed");
                    }
                    return Protocol.decode(line);
                } catch (SocketTimeoutException e) {
                    if (System.nanoTime() >= deadline) {
                        throw e;
                    }
                }
            }
        }

        @Override
        public void close() {
            try {
                socket.close();
            } catch (IOException ignored) {
                // Closing an already broken demo socket is fine.
            }
        }
    }
}
