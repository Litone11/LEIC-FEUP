package room;

import java.time.Instant;

public record Message(String username, String text, Instant createdAt) {
    public Message(String username, String text) {
        this(username, text, Instant.now());
    }
}
