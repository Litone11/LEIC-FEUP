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
import java.util.ArrayList;
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
 * Dependency-free integration smoke test for the chat server.
 *
 * Compile from assign2 with:
 *   javac --release 21 -d out $(find src -name '*.java') test/IntegrationTests.java
 *
 * Run from assign2 with:
 *   java -cp out IntegrationTests
 *
 * Optional Bot response test, slower and requires Ollama/model availability:
 *   RUN_OLLAMA_TEST=true java -cp out IntegrationTests
 */
public final class IntegrationTests {
    private static final String HOST = "127.0.0.1";
    private static final Duration SERVER_START_TIMEOUT = Duration.ofSeconds(5);
    private static final Duration FRAME_TIMEOUT = Duration.ofSeconds(3);
    private static final Duration RESUME_TIMEOUT = Duration.ofSeconds(5);
    private static final Duration OLLAMA_TIMEOUT = Duration.ofSeconds(60);

    private int passed;
    private int failed;
    private TlsFixture tlsFixture;

    public static void main(String[] args) {
        int exitCode = new IntegrationTests().run();
        if (exitCode != 0) {
            System.exit(exitCode);
        }
    }

    private int run() {
        Path tempDir = null;
        Path usersFile = null;
        Path persistentUsersFile = null;
        Process server = null;

        try {
            tempDir = Files.createTempDirectory("cpd-chat-smoke-");
            usersFile = tempDir.resolve("users.txt");
            persistentUsersFile = tempDir.resolve("persistent-users.txt");
            tlsFixture = createTlsFixture(tempDir);
            int port = findFreePort();

            Path persistenceFile = persistentUsersFile;
            runGroup("user-file persistence across server restart", () -> runUserPersistenceFlow(persistenceFile));

            server = startServer(port, usersFile);
            Process runningServer = server;
            boolean serverReady = runGroup("server startup", () -> {
                waitForServer(runningServer, port);
                pass("server starts");
            });

            if (serverReady) {
                boolean chatOk = runGroup("core chat flow", () -> runChatFlow(port));
                boolean aiRoomOk = false;

                if (chatOk) {
                    runGroup("negative auth/protocol cases", () -> runNegativeFlow(port));
                    runGroup("concurrent messaging flow", () -> runConcurrentMessagingFlow(port));
                    aiRoomOk = runGroup("AI room creation flow", () -> runAiRoomCreationFlow(port));
                } else {
                    skip("negative, concurrent, and AI room tests because core chat flow failed");
                }

                if (aiRoomOk && Boolean.parseBoolean(System.getenv().getOrDefault("RUN_OLLAMA_TEST", "false"))) {
                    runGroup("optional Ollama Bot response test", () -> runOptionalOllamaFlow(port));
                } else if (aiRoomOk) {
                    skip("optional Ollama Bot response test (set RUN_OLLAMA_TEST=true)");
                }
            }
        } catch (Throwable e) {
            fail("test setup", e);
        } finally {
            if (server != null) {
                try {
                    stopServer(server);
                } catch (InterruptedException e) {
                    Thread.currentThread().interrupt();
                    fail("server cleanup", e);
                }
            }
            if (usersFile != null) {
                deleteIfExists(usersFile);
            }
            if (persistentUsersFile != null) {
                deleteIfExists(persistentUsersFile);
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
            System.out.println("[PASS] all " + passed + " smoke checks passed");
            return 0;
        }
        System.out.println("[FAIL] " + failed + " smoke group(s) failed; " + passed + " check(s) passed");
        return 1;
    }

    private void runUserPersistenceFlow(Path usersFile) throws Exception {
        int firstPort = findFreePort();
        Process firstServer = startServer(firstPort, usersFile);
        try {
            waitForServer(firstServer, firstPort);
            try (TestClient client = new TestClient("persistence-register", firstPort)) {
                register(client, "persistedUser", "persisted-pass");
                pass("user registration is written to the users file");
            }
        } finally {
            stopServer(firstServer);
        }

        int secondPort = findFreePort();
        Process secondServer = startServer(secondPort, usersFile);
        try {
            waitForServer(secondServer, secondPort);
            try (TestClient client = new TestClient("persistence-login", secondPort)) {
                login(client, "persistedUser", "persisted-pass");
                pass("persisted user can log in after server restart");
            }
        } finally {
            stopServer(secondServer);
        }
    }

    private void runChatFlow(int port) throws Exception {
        String aliceToken;

        try (TestClient alice = new TestClient("alice", port);
             TestClient bob = new TestClient("bob", port)) {

            register(alice, "alice", "alice-pass");
            pass("alice registers");

            register(bob, "bob", "bob-pass");
            pass("bob registers");

            aliceToken = login(alice, "alice", "alice-pass");
            pass("alice logs in and receives a token");

            login(bob, "bob", "bob-pass");
            pass("bob logs in");

            alice.send(Protocol.Command.CREATE, "Library");
            alice.waitFor(Protocol.Command.CREATED, frame -> arg(frame, 0).equals("Library"), FRAME_TIMEOUT);
            pass("alice creates room Library");

            alice.send(Protocol.Command.JOIN, "Library");
            alice.waitFor(Protocol.Command.JOINED, frame -> arg(frame, 0).equals("Library"), FRAME_TIMEOUT);
            pass("alice joins Library");

            bob.send(Protocol.Command.JOIN, "Library");
            bob.waitFor(Protocol.Command.JOINED, frame -> arg(frame, 0).equals("Library"), FRAME_TIMEOUT);
            alice.waitFor(Protocol.Command.INFO, frame -> arg(frame, 1).contains("bob enters"), FRAME_TIMEOUT);
            pass("bob joins Library and alice sees the enter event");

            alice.send(Protocol.Command.MSG, "Hi Bob");
            bob.waitFor(Protocol.Command.MESSAGE, message("Library", "alice", "Hi Bob"), FRAME_TIMEOUT);
            pass("bob receives alice message");

            alice.close();
            Thread.sleep(500);

            bob.send(Protocol.Command.MSG, "Message while Alice is disconnected");
            bob.waitFor(Protocol.Command.MESSAGE,
                    message("Library", "bob", "Message while Alice is disconnected"),
                    FRAME_TIMEOUT);
            pass("bob can keep chatting while alice is disconnected");

            try (TestClient aliceResumed = resumeClient("alice-resume", port, aliceToken)) {
                aliceResumed.waitFor(Protocol.Command.MESSAGE,
                        message("Library", "bob", "Message while Alice is disconnected"),
                        FRAME_TIMEOUT);
                aliceResumed.waitFor(Protocol.Command.JOINED, frame -> arg(frame, 0).equals("Library"), FRAME_TIMEOUT);
                pass("alice resumes with token and receives missed room message");

                aliceResumed.send(Protocol.Command.MSG, "I am back");
                bob.waitFor(Protocol.Command.MESSAGE, message("Library", "alice", "I am back"), FRAME_TIMEOUT);
                pass("bob receives alice message after token resume");
            }
        }

        try (TestClient bob = loginFresh(port, "bob2", "bob", "bob-pass")) {
            bob.send(Protocol.Command.JOIN, "Library");
            bob.waitFor(Protocol.Command.JOINED, frame -> arg(frame, 0).equals("Library"), FRAME_TIMEOUT);
            pass("existing rooms remain available after reconnect test");
        }
    }

    private void runNegativeFlow(int port) throws Exception {
        try (TestClient client = new TestClient("duplicate-register", port)) {
            client.send(Protocol.Command.REGISTER, "alice", "another-pass");
            client.waitFor(Protocol.Command.AUTH_FAIL,
                    frame -> arg(frame, 0).equals("Username already taken."),
                    FRAME_TIMEOUT);
            pass("duplicate registration is rejected");
        }

        try (TestClient client = new TestClient("bad-login", port)) {
            client.send(Protocol.Command.LOGIN, "alice", "wrong-pass");
            client.waitFor(Protocol.Command.AUTH_FAIL,
                    frame -> arg(frame, 0).equals("Invalid credentials."),
                    FRAME_TIMEOUT);
            pass("wrong password is rejected");
        }

        try (TestClient client = new TestClient("bad-resume", port)) {
            client.send(Protocol.Command.RESUME, "not-a-real-token");
            client.waitFor(Protocol.Command.AUTH_FAIL,
                    frame -> arg(frame, 0).equals("Unknown or expired token."),
                    FRAME_TIMEOUT);
            pass("invalid resume token is rejected");
        }

        try (TestClient alice = loginFresh(port, "negative-alice", "alice", "alice-pass")) {
            alice.send(Protocol.Command.MSG, "message before joining");
            alice.waitFor(Protocol.Command.ERROR,
                    frame -> arg(frame, 0).equals("Join a room before sending messages."),
                    FRAME_TIMEOUT);
            pass("message before joining a room is rejected");

            alice.send(Protocol.Command.CREATE, "Library");
            alice.waitFor(Protocol.Command.ERROR,
                    frame -> arg(frame, 0).equals("Room already exists."),
                    FRAME_TIMEOUT);
            pass("duplicate room creation is rejected");

            alice.send(Protocol.Command.CREATE_AI, "AI-Without-Prompt", "");
            alice.waitFor(Protocol.Command.ERROR,
                    frame -> arg(frame, 0).equals("AI room requires a non-empty prompt."),
                    FRAME_TIMEOUT);
            pass("AI room creation with empty prompt is rejected");
        }
    }

    private void runConcurrentMessagingFlow(int port) throws Exception {
        final int senderCount = 6;
        List<TestClient> clients = new ArrayList<>();

        try {
            TestClient watcher = loginFresh(port, "concurrent-watcher", "alice", "alice-pass");
            clients.add(watcher);

            watcher.send(Protocol.Command.CREATE, "Concurrent");
            watcher.waitFor(Protocol.Command.CREATED, frame -> arg(frame, 0).equals("Concurrent"), FRAME_TIMEOUT);

            watcher.send(Protocol.Command.JOIN, "Concurrent");
            watcher.waitFor(Protocol.Command.JOINED, frame -> arg(frame, 0).equals("Concurrent"), FRAME_TIMEOUT);

            for (int i = 0; i < senderCount; i++) {
                String username = "sender" + i;
                TestClient sender = new TestClient("concurrent-" + username, port);
                clients.add(sender);

                register(sender, username, "sender-pass");
                login(sender, username, "sender-pass");
                sender.send(Protocol.Command.JOIN, "Concurrent");
                sender.waitFor(Protocol.Command.JOINED, frame -> arg(frame, 0).equals("Concurrent"), FRAME_TIMEOUT);
            }
            pass("concurrent senders register and join a shared room");

            Throwable[] sendFailures = new Throwable[senderCount];
            List<Thread> sendThreads = new ArrayList<>();

            for (int i = 0; i < senderCount; i++) {
                int index = i;
                TestClient sender = clients.get(i + 1);
                Thread thread = Thread.startVirtualThread(() -> {
                    try {
                        sender.send(Protocol.Command.MSG, "concurrent message " + index);
                    } catch (Throwable e) {
                        sendFailures[index] = e;
                    }
                });
                sendThreads.add(thread);
            }

            for (Thread thread : sendThreads) {
                thread.join();
            }
            for (Throwable failure : sendFailures) {
                if (failure != null) {
                    throw new AssertionError("concurrent send failed: " + failure.getMessage());
                }
            }

            for (int i = 0; i < senderCount; i++) {
                watcher.waitFor(Protocol.Command.MESSAGE,
                        message("Concurrent", "sender" + i, "concurrent message " + i),
                        FRAME_TIMEOUT);
            }
            pass("watcher receives all concurrent sender messages");
        } finally {
            for (int i = clients.size() - 1; i >= 0; i--) {
                clients.get(i).close();
            }
        }
    }

    private void runAiRoomCreationFlow(int port) throws Exception {
        try (TestClient alice = loginFresh(port, "alice-ai", "alice", "alice-pass")) {
            alice.send(Protocol.Command.CREATE_AI, "AI-Smoke", "Reply briefly to messages.");
            alice.waitFor(Protocol.Command.CREATED, frame -> arg(frame, 0).equals("AI-Smoke"), FRAME_TIMEOUT);
            pass("AI room can be created with a prompt");

            alice.send(Protocol.Command.LIST);
            alice.waitFor(Protocol.Command.ROOMS, frame -> arg(frame, 0).contains("AI-Smoke"), FRAME_TIMEOUT);
            pass("AI room appears in room list");
        }
    }

    private void runOptionalOllamaFlow(int port) throws Exception {
        try (TestClient alice = loginFresh(port, "alice-ollama", "alice", "alice-pass")) {
            alice.send(Protocol.Command.JOIN, "AI-Smoke");
            alice.waitFor(Protocol.Command.JOINED, frame -> arg(frame, 0).equals("AI-Smoke"), FRAME_TIMEOUT);

            alice.send(Protocol.Command.MSG, "Say OK.");
            Protocol.Frame botResponse = alice.waitFor(Protocol.Command.MESSAGE, frame ->
                            arg(frame, 0).equals("AI-Smoke") && arg(frame, 1).equals("Bot"),
                    OLLAMA_TIMEOUT);
            assertSuccessfulBotResponse(arg(botResponse, 2));
            pass("Bot writes a response in an AI room");
        }
    }

    private void assertSuccessfulBotResponse(String text) {
        if (text.isBlank()) {
            throw new AssertionError("Ollama test failed: Bot returned an empty response");
        }
        if (text.startsWith("Unable to obtain a response from the local LLM:")) {
            throw new AssertionError("Ollama test failed: " + text);
        }
    }

    private void register(TestClient client, String username, String password) throws IOException {
        client.send(Protocol.Command.REGISTER, username, password);
        client.waitFor(Protocol.Command.OK, frame -> arg(frame, 0).contains("Registered"), FRAME_TIMEOUT);
    }

    private String login(TestClient client, String username, String password) throws IOException {
        client.send(Protocol.Command.LOGIN, username, password);
        String token = arg(client.waitFor(Protocol.Command.TOKEN, frame -> !arg(frame, 0).isBlank(), FRAME_TIMEOUT), 0);
        client.waitFor(Protocol.Command.AUTH_OK, frame -> arg(frame, 0).equals(username), FRAME_TIMEOUT);
        client.waitFor(Protocol.Command.ROOMS, frame -> arg(frame, 0).contains("Lobby"), FRAME_TIMEOUT);
        return token;
    }

    private TestClient loginFresh(int port, String label, String username, String password) throws IOException {
        TestClient client = new TestClient(label, port);
        try {
            login(client, username, password);
            return client;
        } catch (IOException | RuntimeException e) {
            client.close();
            throw e;
        }
    }

    private TestClient resumeClient(String label, int port, String token) throws IOException, InterruptedException {
        long deadline = System.nanoTime() + RESUME_TIMEOUT.toNanos();
        IOException lastFailure = null;

        while (System.nanoTime() < deadline) {
            TestClient client = new TestClient(label, port);
            try {
                client.send(Protocol.Command.RESUME, token);
                Protocol.Frame frame = client.waitForAny(List.of(
                        Protocol.Command.AUTH_OK,
                        Protocol.Command.AUTH_FAIL
                ), RESUME_TIMEOUT);

                if (frame.command() == Protocol.Command.AUTH_OK) {
                    return client;
                }
                if (!arg(frame, 0).equals("Session already active elsewhere.")) {
                    throw new AssertionError("resume failed: " + arg(frame, 0));
                }
            } catch (IOException e) {
                lastFailure = e;
            }

            client.close();
            Thread.sleep(100);
        }

        if (lastFailure != null) {
            throw lastFailure;
        }
        throw new AssertionError("resume did not become available within " + RESUME_TIMEOUT);
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
                "-alias", "smoke",
                "-keyalg", "RSA",
                "-keysize", "2048",
                "-validity", "1",
                "-storetype", "PKCS12",
                "-keystore", keyStore.toString(),
                "-storepass", password,
                "-keypass", password,
                "-dname", "CN=localhost, OU=Smoke Tests, O=CPD, L=Porto, ST=Porto, C=PT",
                "-ext", "san=dns:localhost,ip:127.0.0.1"
        );
        builder.redirectErrorStream(true);

        Process process = builder.start();
        boolean finished = process.waitFor(10, TimeUnit.SECONDS);
        if (!finished) {
            process.destroyForcibly();
            process.waitFor(3, TimeUnit.SECONDS);
            throw new IOException("keytool did not finish while creating smoke-test keystore");
        }

        String output = new String(process.getInputStream().readAllBytes(), StandardCharsets.UTF_8).trim();
        if (process.exitValue() != 0) {
            throw new IOException("keytool failed while creating smoke-test keystore: " + output);
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
            // Best-effort cleanup for temporary smoke-test files.
        }
    }

    private void pass(String name) {
        passed++;
        System.out.println("[PASS] " + name);
    }

    private void skip(String name) {
        System.out.println("[SKIP] " + name);
    }

    private boolean runGroup(String name, CheckedRunnable action) {
        try {
            action.run();
            return true;
        } catch (Throwable e) {
            fail(name, e);
            return false;
        }
    }

    private void fail(String name, Throwable e) {
        failed++;
        if (e instanceof InterruptedException) {
            Thread.currentThread().interrupt();
        }
        System.out.println("[FAIL] " + name + ": " + cleanMessage(e));
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

    @FunctionalInterface
    private interface CheckedRunnable {
        void run() throws Exception;
    }

    private final class TestClient implements AutoCloseable {
        private final String label;
        private final SSLSocket socket;
        private final BufferedReader in;
        private final PrintWriter out;
        private final Deque<Protocol.Frame> buffered = new ArrayDeque<>();

        private TestClient(String label, int port) throws IOException {
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

        private Protocol.Frame waitFor(Protocol.Command command,
                                       Predicate<Protocol.Frame> predicate,
                                       Duration timeout) throws IOException {
            return waitFor(frame -> frame.command() == command && predicate.test(frame), timeout,
                    "expected " + command);
        }

        private Protocol.Frame waitForAny(List<Protocol.Command> commands, Duration timeout) throws IOException {
            return waitFor(frame -> commands.contains(frame.command()), timeout,
                    "expected one of " + commands);
        }

        private Protocol.Frame waitFor(Predicate<Protocol.Frame> predicate,
                                       Duration timeout,
                                       String description) throws IOException {
            long deadline = System.nanoTime() + timeout.toNanos();
            List<Protocol.Frame> skipped = new ArrayList<>();

            try {
                while (System.nanoTime() < deadline) {
                    Protocol.Frame frame = nextFrame(deadline);
                    if (predicate.test(frame)) {
                        restoreSkipped(skipped);
                        return frame;
                    }
                    skipped.add(frame);
                }
            } catch (SocketTimeoutException e) {
                // Report a clearer assertion below.
            }

            restoreSkipped(skipped);
            throw new AssertionError(label + " timed out: " + description + ", buffered=" + buffered);
        }

        private Protocol.Frame nextFrame(long deadline) throws IOException {
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

        private void restoreSkipped(List<Protocol.Frame> skipped) {
            for (int i = skipped.size() - 1; i >= 0; i--) {
                buffered.addFirst(skipped.get(i));
            }
        }

        @Override
        public void close() {
            try {
                socket.close();
            } catch (IOException ignored) {
                // Closing an already broken test socket is fine.
            }
        }
    }
}
