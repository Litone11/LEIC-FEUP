package room;

import protocol.Protocol;

import java.util.Objects;
import java.util.function.Consumer;

public final class UserRoomMember implements RoomMember {
    private final String username;
    private final Consumer<String> rawSender;

    public UserRoomMember(String username, Consumer<String> rawSender) {
        if (username == null || username.isBlank()) {
            throw new IllegalArgumentException("username cannot be empty");
        }

        this.username = username.trim();
        this.rawSender = Objects.requireNonNull(rawSender, "rawSender");
    }

    @Override
    public String username() {
        return username;
    }

    @Override
    public void send(Protocol.Command command, String... args) {
        sendRaw(Protocol.encode(command, args));
    }

    @Override
    public void sendRaw(String line) {
        rawSender.accept(line);
    }
}
