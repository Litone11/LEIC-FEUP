package room;

import ai.OllamaModel;
import protocol.Protocol;

public final class AIRoomMember implements RoomMember {
    private final ChatRoom room;
    private final String initialPrompt;

    public AIRoomMember(ChatRoom room, String initialPrompt) {
        this.room = room;
        this.initialPrompt = initialPrompt;
    }

    @Override
    public String username() {
        return "Bot";
    }

    @Override
    public void send(Protocol.Command command, String... args) {
        sendRaw(Protocol.encode(command, args));
    }

    @Override
    public void sendRaw(String line) {
        Protocol.Frame frame = Protocol.decode(line);

        if (frame.command() != Protocol.Command.MESSAGE) {
            return;
        }

        String author = frame.arg(1);

        if (author.equals(username())) {
            return;
        }

        String context = room.messageContext();
        String question = initialPrompt + "\n\nConversation:\n" + context;

        Thread.startVirtualThread(() -> {
            String answer;
            try {
                answer = OllamaModel.ollamaAnswer(question);
            } catch (RuntimeException e) {
                answer = "Unable to obtain a response from the local LLM: " + e.getMessage();
            }
            room.addUserMessage(username(), answer);
        });
    }
}
