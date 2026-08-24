package auth;

import java.io.IOException;
import java.nio.charset.StandardCharsets;
import java.nio.file.Files;
import java.nio.file.Path;
import java.nio.file.StandardOpenOption;
import java.util.HashMap;
import java.util.Map;
import java.util.concurrent.locks.ReentrantReadWriteLock;

public final class UserStore {
    private static final String SEPARATOR = ":";

    private final Path file;
    private final Map<String, Record> users = new HashMap<>();
    private final ReentrantReadWriteLock lock = new ReentrantReadWriteLock();

    private record Record(String saltB64, String hashB64) {
    }

    public UserStore(Path file) throws IOException {
        this.file = file;
        if (file.getParent() != null) {
            Files.createDirectories(file.getParent());
        }
        if (!Files.exists(file)) {
            Files.createFile(file);
        }
        load();
    }

    public boolean register(String username, String password) throws IOException {
        validate(username, password);
        PasswordHasher.Hashed hashed = PasswordHasher.hash(password);

        lock.writeLock().lock();
        try {
            if (users.containsKey(username)) {
                return false;
            }
            users.put(username, new Record(hashed.saltB64(), hashed.hashB64()));
            appendLine(username + SEPARATOR + hashed.saltB64() + SEPARATOR + hashed.hashB64());
            return true;
        } finally {
            lock.writeLock().unlock();
        }
    }

    public boolean verify(String username, String password) {
        if (username == null || password == null) {
            return false;
        }
        lock.readLock().lock();
        try {
            Record record = users.get(username);
            if (record == null) {
                return false;
            }
            return PasswordHasher.verify(password, record.saltB64(), record.hashB64());
        } finally {
            lock.readLock().unlock();
        }
    }

    public boolean exists(String username) {
        lock.readLock().lock();
        try {
            return users.containsKey(username);
        } finally {
            lock.readLock().unlock();
        }
    }

    private void validate(String username, String password) {
        if (username == null || username.isBlank()) {
            throw new IllegalArgumentException("username cannot be empty");
        }
        if (username.contains(SEPARATOR) || username.contains("\t") || username.contains("\n")) {
            throw new IllegalArgumentException("username contains invalid characters");
        }
        if (password == null || password.length() < 4) {
            throw new IllegalArgumentException("password too short (min 4 chars)");
        }
    }

    private void load() throws IOException {
        for (String line : Files.readAllLines(file, StandardCharsets.UTF_8)) {
            if (line.isBlank()) {
                continue;
            }
            String[] parts = line.split(SEPARATOR, 3);
            if (parts.length != 3) {
                continue;
            }
            users.put(parts[0], new Record(parts[1], parts[2]));
        }
    }

    private void appendLine(String line) throws IOException {
        Files.writeString(
                file,
                line + System.lineSeparator(),
                StandardCharsets.UTF_8,
                StandardOpenOption.APPEND
        );
    }
}
