package server;

import auth.UserStore;
import protocol.Protocol;
import room.ChatRoom;
import room.RoomManager;
import room.UserRoomMember;
import session.Session;
import session.SessionManager;

import java.io.BufferedReader;
import java.io.IOException;
import java.io.InputStreamReader;
import java.io.PrintWriter;
import java.nio.charset.StandardCharsets;
import java.util.List;
import java.util.concurrent.locks.ReentrantLock;
import javax.net.ssl.SSLSocket;

public final class ClientHandler implements Runnable {
    private static final int OUTBOUND_QUEUE_LIMIT = 50;
    private static final long SLOW_CLIENT_NOTICE_GRACE_MS = 250L;

    private final SSLSocket socket;
    private final RoomManager roomManager;
    private final UserStore userStore;
    private final SessionManager sessionManager;
    private final OutboundQueue outboundQueue = new OutboundQueue(OUTBOUND_QUEUE_LIMIT);
    private final ReentrantLock disconnectLock = new ReentrantLock();

    private PrintWriter out;
    private Session session;
    private boolean slowDisconnectStarted;

    public ClientHandler(SSLSocket socket,
                         RoomManager roomManager,
                         UserStore userStore,
                         SessionManager sessionManager) {
        this.socket = socket;
        this.roomManager = roomManager;
        this.userStore = userStore;
        this.sessionManager = sessionManager;
    }

    @Override
    public void run() {
        Thread writerThread = null;
        try (
                socket;
                BufferedReader in = new BufferedReader(new InputStreamReader(socket.getInputStream(), StandardCharsets.UTF_8));
                PrintWriter writer = new PrintWriter(socket.getOutputStream(), true, StandardCharsets.UTF_8)
        ) {
            socket.startHandshake();
            out = writer;
            writerThread = Thread.startVirtualThread(this::writeOutbound);

            if (!authenticate(in)) {
                return;
            }

            sendRooms();

            String line;
            while ((line = in.readLine()) != null) {
                if (!handleAuthedLine(line)) {
                    break;
                }
            }
        } catch (IOException e) {
            System.err.println("Client connection error: " + e.getMessage());
        } finally {
            outboundQueue.close();
            if (writerThread != null) {
                writerThread.interrupt();
            }
            if (session != null) {
                sessionManager.disconnect(session);
            }
        }
    }

    // -------- queued send helpers --------

    private void send(Protocol.Command command, String... args) {
        enqueue(Protocol.encode(command, args));
    }

    private void enqueue(String line) {
        if (!outboundQueue.offer(line)) {
            disconnectSlowClient();
        }
    }

    private void writeOutbound() {
        try {
            String line;
            while ((line = outboundQueue.take()) != null) {
                if (!sendRawDirect(line)) {
                    closeSocket();
                    return;
                }
            }
        } catch (InterruptedException e) {
            Thread.currentThread().interrupt();
        }
    }

    private boolean sendRawDirect(String line) {
        PrintWriter writer = out;
        if (writer == null) {
            return false;
        }
        writer.println(line);
        return !writer.checkError();
    }

    private void disconnectSlowClient() {
        disconnectLock.lock();
        try {
            if (slowDisconnectStarted) {
                return;
            }
            slowDisconnectStarted = true;
        } finally {
            disconnectLock.unlock();
        }

        outboundQueue.closeAfter(Protocol.encode(
                Protocol.Command.ERROR,
                "Disconnected because this client is not reading fast enough. The client will try to reconnect automatically."
        ));

        Thread.startVirtualThread(() -> {
            try {
                Thread.sleep(SLOW_CLIENT_NOTICE_GRACE_MS);
            } catch (InterruptedException e) {
                Thread.currentThread().interrupt();
            } finally {
                closeSocket();
            }
        });
    }

    private void closeSocket() {
        try {
            socket.close();
        } catch (IOException ignored) {
            // connection unusable
        }
    }

    // -------- unauthenticated loop --------

    private boolean authenticate(BufferedReader in) throws IOException {
        String line;
        while ((line = in.readLine()) != null) {
            Protocol.Frame frame;
            try {
                frame = Protocol.decode(line);
            } catch (IllegalArgumentException e) {
                send(Protocol.Command.ERROR, "Invalid protocol message.");
                continue;
            }

            switch (frame.command()) {
                case REGISTER -> handleRegister(frame);
                case LOGIN -> {
                    if (handleLogin(frame)) return true;
                }
                case RESUME -> {
                    if (handleResume(frame)) return true;
                }
                case QUIT -> {
                    return false;
                }
                default -> send(Protocol.Command.ERROR, "Authenticate first (/login or /register).");
            }
        }
        return false;
    }

    private void handleRegister(Protocol.Frame frame) {
        if (frame.size() != 2) {
            send(Protocol.Command.ERROR, "REGISTER requires <user> <pass>.");
            return;
        }
        String username = frame.arg(0).trim();
        String password = frame.arg(1);
        try {
            boolean created = userStore.register(username, password);
            if (!created) {
                send(Protocol.Command.AUTH_FAIL, "Username already taken.");
                return;
            }
            send(Protocol.Command.OK, "Registered. You can now LOGIN.");
        } catch (IllegalArgumentException e) {
            send(Protocol.Command.AUTH_FAIL, e.getMessage());
        } catch (IOException e) {
            send(Protocol.Command.ERROR, "Could not persist user: " + e.getMessage());
        }
    }

    private boolean handleLogin(Protocol.Frame frame) {
        if (frame.size() != 2) {
            send(Protocol.Command.ERROR, "LOGIN requires <user> <pass>.");
            return false;
        }
        String username = frame.arg(0).trim();
        String password = frame.arg(1);
        if (!userStore.verify(username, password)) {
            send(Protocol.Command.AUTH_FAIL, "Invalid credentials.");
            return false;
        }

        session = sessionManager.createSession(username);
        UserRoomMember member = new UserRoomMember(username, session::deliver);
        session.setMember(member);
        session.attach(this::enqueue);

        send(Protocol.Command.TOKEN, session.token());
        send(Protocol.Command.AUTH_OK, username);
        return true;
    }

    private boolean handleResume(Protocol.Frame frame) {
        if (frame.size() != 1) {
            send(Protocol.Command.ERROR, "RESUME requires <token>.");
            return false;
        }
        Session resumed = sessionManager.resume(frame.arg(0));
        if (resumed == null) {
            send(Protocol.Command.AUTH_FAIL, "Unknown or expired token.");
            return false;
        }
        if (!resumed.attach(this::enqueue)) {
            send(Protocol.Command.AUTH_FAIL, "Session already active elsewhere.");
            return false;
        }

        session = resumed;
        send(Protocol.Command.AUTH_OK, session.username());

        // attach drains the pending backlog before announcing the room,
        // so the user catches up on missed messages first.
        ChatRoom current = session.currentRoom();
        if (current != null) {
            send(Protocol.Command.JOINED, current.name());
        }

        return true;
    }

    // -------- authenticated loop --------

    private void sendRooms() {
        List<String> names = roomManager.listRoomNames();
        send(Protocol.Command.ROOMS, String.join(", ", names));
    }

    private boolean handleAuthedLine(String line) {
        Protocol.Frame frame;
        try {
            frame = Protocol.decode(line);
        } catch (IllegalArgumentException e) {
            send(Protocol.Command.ERROR, "Invalid protocol message.");
            return true;
        }

        try {
            return switch (frame.command()) {
                case LIST -> {
                    sendRooms();
                    yield true;
                }
                case CREATE -> {
                    requireArgs(frame, 1, "CREATE requires a room name.");
                    createRoom(frame.arg(0));
                    yield true;
                }
                case JOIN -> {
                    requireArgs(frame, 1, "JOIN requires a room name.");
                    joinRoom(frame.arg(0));
                    yield true;
                }
                case MSG -> {
                    requireArgs(frame, 1, "MSG requires text.");
                    sendMessage(frame.arg(0));
                    yield true;
                }
                case CREATE_AI -> {
                    requireArgs(frame, 2, "CREATE_AI requires <room> <prompt>.");
                    createAiRoom(frame.arg(0), frame.arg(1));
                    yield true;
                }
                case QUIT -> false;
                default -> {
                    send(Protocol.Command.ERROR, "Command not allowed here.");
                    yield true;
                }
            };
        } catch (IllegalArgumentException e) {
            send(Protocol.Command.ERROR, e.getMessage());
            return true;
        }
    }

    private void requireArgs(Protocol.Frame frame, int expected, String error) {
        if (frame.size() != expected) {
            throw new IllegalArgumentException(error);
        }
    }

    private void createRoom(String roomName) {
        ChatRoom room = roomManager.createRoom(roomName);
        send(Protocol.Command.CREATED, room.name());
    }

    private void joinRoom(String roomName) {
        ChatRoom nextRoom = roomManager.findRoom(roomName);
        UserRoomMember member = session.member();

        ChatRoom previous = session.currentRoom();
        if (previous != null && previous != nextRoom) {
            previous.leave(member);
        }
        session.setCurrentRoom(nextRoom);
        nextRoom.join(member);
    }

    private void sendMessage(String text) {
        ChatRoom currentRoom = session.currentRoom();
        if (currentRoom == null) {
            send(Protocol.Command.ERROR, "Join a room before sending messages.");
            return;
        }
        if (text.isBlank()) {
            send(Protocol.Command.ERROR, "Message cannot be empty.");
            return;
        }
        currentRoom.addUserMessage(session.username(), text);
    }

    private void createAiRoom(String roomName, String prompt) {
        ChatRoom room = roomManager.createAIRoom(roomName, prompt);
        send(Protocol.Command.CREATED, room.name());
    }
}
