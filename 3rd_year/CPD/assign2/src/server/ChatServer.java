package server;

import auth.UserStore;
import room.RoomManager;
import session.SessionManager;

import java.io.IOException;
import java.nio.file.Path;
import java.nio.file.Paths;
import javax.net.ssl.SSLServerSocket;
import javax.net.ssl.SSLServerSocketFactory;
import javax.net.ssl.SSLSocket;

public final class ChatServer {
    private static final int DEFAULT_PORT = 12345;
    private static final long HOUSEKEEPING_INTERVAL_MS = 30_000L;

    private ChatServer() {
    }

    public static void main(String[] args) throws IOException {
        int port = args.length > 0 ? Integer.parseInt(args[0]) : DEFAULT_PORT;
        Path usersFile = args.length > 1 ? Paths.get(args[1]) : Paths.get("data", "users.txt");

        UserStore userStore = new UserStore(usersFile);
        SessionManager sessionManager = new SessionManager();
        RoomManager roomManager = new RoomManager();

        startHousekeeping(sessionManager);

        try (SSLServerSocket serverSocket = createSecureServerSocket(port)) {
            System.out.println("Secure chat server listening on port " + port);
            System.out.println("User database: " + usersFile.toAbsolutePath());

            while (true) {
                SSLSocket socket = (SSLSocket) serverSocket.accept();
                Thread.startVirtualThread(new ClientHandler(socket, roomManager, userStore, sessionManager));
            }
        }
    }

    private static SSLServerSocket createSecureServerSocket(int port) throws IOException {
        SSLServerSocketFactory factory = (SSLServerSocketFactory) SSLServerSocketFactory.getDefault();
        SSLServerSocket serverSocket = (SSLServerSocket) factory.createServerSocket(port);
        serverSocket.setEnabledProtocols(enabledTlsProtocols(serverSocket.getSupportedProtocols()));
        serverSocket.setNeedClientAuth(false);
        return serverSocket;
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

    private static void startHousekeeping(SessionManager sessionManager) {
        Thread.startVirtualThread(() -> {
            while (true) {
                try {
                    Thread.sleep(HOUSEKEEPING_INTERVAL_MS);
                } catch (InterruptedException e) {
                    Thread.currentThread().interrupt();
                    return;
                }
                try {
                    int expired = sessionManager.expireOldSessions().size();
                    if (expired > 0) {
                        System.out.println("Expired " + expired + " stale session(s).");
                    }
                } catch (RuntimeException e) {
                    System.err.println("Housekeeping error: " + e.getMessage());
                }
            }
        });
    }
}
