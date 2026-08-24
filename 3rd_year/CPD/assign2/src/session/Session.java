package session;

import room.ChatRoom;
import room.UserRoomMember;

import java.time.Instant;
import java.util.ArrayDeque;
import java.util.Deque;
import java.util.concurrent.locks.ReentrantLock;
import java.util.function.Consumer;

public final class Session {
    private static final int MAX_PENDING_FRAMES = 50;

    private final String username;
    private final String token;
    private final Instant createdAt;
    private final Deque<String> pendingQueue = new ArrayDeque<>();
    private final ReentrantLock lock = new ReentrantLock();

    private UserRoomMember member;
    private ChatRoom currentRoom;
    private Consumer<String> liveSink;
    private Instant disconnectedAt;
    private boolean connected;

    public Session(String username, String token) {
        this.username = username;
        this.token = token;
        this.createdAt = Instant.now();
        this.connected = false;
    }

    public String username() {
        return username;
    }

    public String token() {
        return token;
    }

    public Instant createdAt() {
        return createdAt;
    }

    public UserRoomMember member() {
        lock.lock();
        try {
            return member;
        } finally {
            lock.unlock();
        }
    }

    public void setMember(UserRoomMember member) {
        lock.lock();
        try {
            this.member = member;
        } finally {
            lock.unlock();
        }
    }

    public ChatRoom currentRoom() {
        lock.lock();
        try {
            return currentRoom;
        } finally {
            lock.unlock();
        }
    }

    public void setCurrentRoom(ChatRoom room) {
        lock.lock();
        try {
            this.currentRoom = room;
        } finally {
            lock.unlock();
        }
    }

    public Instant disconnectedAt() {
        lock.lock();
        try {
            return disconnectedAt;
        } finally {
            lock.unlock();
        }
    }

    public boolean isConnected() {
        lock.lock();
        try {
            return connected;
        } finally {
            lock.unlock();
        }
    }

    /**
     * Attach a live writer (called when a ClientHandler binds to this session).
     * Drains anything queued while disconnected and replays it through the new sink.
     */
    public boolean attach(Consumer<String> sink) {
        lock.lock();
        try {
            if (connected) {
                return false;
            }

            this.liveSink = sink;
            this.connected = true;
            this.disconnectedAt = null;

            while (!pendingQueue.isEmpty()) {
                sink.accept(pendingQueue.pollFirst());
            }

            return true;
        } finally {
            lock.unlock();
        }
    }

    /**
     * Detach the current writer (called when the socket dies). The session stays
     * alive, but subsequent deliveries go to the pending queue until reattach.
     */
    public void detach() {
        lock.lock();
        try {
            this.liveSink = null;
            this.connected = false;
            this.disconnectedAt = Instant.now();
        } finally {
            lock.unlock();
        }
    }

    /**
     * Deliver a frame to the session: writes through if connected, otherwise queues.
     */
    public void deliver(String frame) {
        lock.lock();
        try {
            if (connected && liveSink != null) {
                liveSink.accept(frame);
            } else {
                if (pendingQueue.size() >= MAX_PENDING_FRAMES) {
                    pendingQueue.removeFirst();
                }
                pendingQueue.addLast(frame);
            }
        } finally {
            lock.unlock();
        }
    }
}
