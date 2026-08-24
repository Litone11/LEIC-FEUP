package session;

import room.ChatRoom;

import java.security.SecureRandom;
import java.time.Duration;
import java.time.Instant;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.HexFormat;
import java.util.List;
import java.util.Map;
import java.util.concurrent.locks.ReentrantReadWriteLock;

public final class SessionManager {
    private static final Duration DEFAULT_TTL = Duration.ofMinutes(5);
    private static final int TOKEN_BYTES = 24;

    private final Map<String, Session> byToken = new HashMap<>();
    private final Map<String, Session> byUser = new HashMap<>();
    private final ReentrantReadWriteLock lock = new ReentrantReadWriteLock();
    private final SecureRandom rng = new SecureRandom();
    private final Duration ttl;

    public SessionManager() {
        this(DEFAULT_TTL);
    }

    public SessionManager(Duration ttl) {
        this.ttl = ttl;
    }

    public Session createSession(String username) {
        String token = newToken();
        Session session = new Session(username, token);

        lock.writeLock().lock();
        try {
            Session existing = byUser.get(username);
            if (existing != null) {
                byToken.remove(existing.token());
            }
            byUser.put(username, session);
            byToken.put(token, session);
        } finally {
            lock.writeLock().unlock();
        }
        return session;
    }

    public Session resume(String token) {
        if (token == null || token.isBlank()) {
            return null;
        }
        lock.readLock().lock();
        try {
            return byToken.get(token);
        } finally {
            lock.readLock().unlock();
        }
    }

    public void disconnect(Session session) {
        if (session == null) {
            return;
        }
        session.detach();
    }

    /**
     * Sweep sessions that have been disconnected longer than the TTL. For each one,
     * leave the current room (if any) and drop the session from the maps. Returns
     * the sessions that were expired so callers can run any extra cleanup.
     */
    public List<Session> expireOldSessions() {
        Instant cutoff = Instant.now().minus(ttl);
        List<Session> expired = new ArrayList<>();

        lock.writeLock().lock();
        try {
            for (Session s : new ArrayList<>(byToken.values())) {
                if (s.isConnected()) {
                    continue;
                }
                Instant disconnectedAt = s.disconnectedAt();
                if (disconnectedAt == null || disconnectedAt.isAfter(cutoff)) {
                    continue;
                }
                byToken.remove(s.token());
                byUser.remove(s.username());
                expired.add(s);
            }
        } finally {
            lock.writeLock().unlock();
        }

        for (Session s : expired) {
            ChatRoom room = s.currentRoom();
            if (room != null && s.member() != null) {
                room.leave(s.member());
            }
        }
        return expired;
    }

    private String newToken() {
        byte[] buf = new byte[TOKEN_BYTES];
        rng.nextBytes(buf);
        return HexFormat.of().formatHex(buf);
    }
}
