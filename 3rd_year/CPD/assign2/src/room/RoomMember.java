package room;

import protocol.Protocol;

public interface RoomMember {
    String username();

    void send(Protocol.Command command, String... args);

    void sendRaw(String line);
}
