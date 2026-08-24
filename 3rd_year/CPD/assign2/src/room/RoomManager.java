package room;

import java.util.ArrayList;
import java.util.Collections;
import java.util.HashMap;
import java.util.List;
import java.util.Map;
import java.util.concurrent.locks.ReentrantReadWriteLock;

public final class RoomManager {
    private final Map<String, ChatRoom> rooms = new HashMap<>();
    private final ReentrantReadWriteLock lock = new ReentrantReadWriteLock();

    public RoomManager() {
        rooms.put("Lobby", new ChatRoom("Lobby"));
        rooms.put("Help", new ChatRoom("Help"));
    }

    public ChatRoom createRoom(String name) {
        String normalized = normalizeRoomName(name);

        lock.writeLock().lock();
        try {
            ChatRoom room = createAndRegisterRoom(normalized);
            rooms.put(normalized, room);
            return room;
        } finally {
            lock.writeLock().unlock();
        }
    }

    public ChatRoom createAIRoom(String name, String prompt) {
        if (prompt == null || prompt.isBlank()) {
            throw new IllegalArgumentException("AI room requires a non-empty prompt.");
        }
        String normalized = normalizeRoomName(name);

        lock.writeLock().lock();
        try {
            ChatRoom room = createAndRegisterRoom(normalized, true);

            AIRoomMember bot = new AIRoomMember(room, prompt);
            room.join(bot);

            return room;
        } finally {
            lock.writeLock().unlock();
        }
    }

    private ChatRoom createAndRegisterRoom(String normalized) throws IllegalArgumentException {
        return createAndRegisterRoom(normalized, false);
    }

    private ChatRoom createAndRegisterRoom(String normalized, boolean preserveFullHistory)
            throws IllegalArgumentException {
        if (rooms.containsKey(normalized)) {
            throw new IllegalArgumentException("Room already exists.");
        }
        ChatRoom room = new ChatRoom(normalized, preserveFullHistory);
        rooms.put(normalized, room);
        return room;
    }

    public ChatRoom findRoom(String name) {
        String normalized = normalizeRoomName(name);

        lock.readLock().lock();
        try {
            ChatRoom room = rooms.get(normalized);
            if (room == null) {
                throw new IllegalArgumentException("Room does not exist. Create it first.");
            }
            return room;
        } finally {
            lock.readLock().unlock();
        }
    }


    public List<String> listRoomNames() {
        lock.readLock().lock();
        try {
            List<String> names = new ArrayList<>(rooms.keySet());
            Collections.sort(names);
            return names;
        } finally {
            lock.readLock().unlock();
        }
    }

    private String normalizeRoomName(String name) {
        if (name == null || name.isBlank()) {
            throw new IllegalArgumentException("room name cannot be empty");
        }

        return name.trim();
    }
}
