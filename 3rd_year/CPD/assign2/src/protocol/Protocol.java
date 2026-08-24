package protocol;

import java.nio.charset.StandardCharsets;
import java.util.ArrayList;
import java.util.Base64;
import java.util.List;
import java.util.Locale;

/**
 * Wire contract (each frame = one line, COMMAND<TAB>arg1<TAB>arg2..., each arg Base64-URL).
 *
 * Client -> Server (unauthenticated):
 *   REGISTER <user> <pass>
 *   LOGIN    <user> <pass>
 *   RESUME   <token>
 *   QUIT
 *
 * Client -> Server (authenticated):
 *   LIST
 *   CREATE    <room>
 *   JOIN      <room>
 *   MSG       <text>
 *   CREATE_AI <room> <prompt>
 *   QUIT
 *
 * Server -> Client:
 *   OK        <info>
 *   ERROR     <reason>
 *   AUTH_OK   <username>
 *   AUTH_FAIL <reason>
 *   TOKEN     <hex>
 *   ROOMS     <comma-separated room names>
 *   CREATED   <room>
 *   JOINED    <room>
 *   LEFT      <room>
 *   MESSAGE   <room> <user> <text>
 *   INFO      <room> <text>
 */
public final class Protocol {
    private static final String SEPARATOR = "\t";

    private Protocol() {
    }

    public enum Command {
        // unauthenticated client -> server
        REGISTER,
        LOGIN,
        RESUME,
        // authenticated client -> server
        HELLO,
        LIST,
        CREATE,
        CREATE_AI,
        JOIN,
        MSG,
        QUIT,
        // server -> client
        OK,
        ERROR,
        AUTH_OK,
        AUTH_FAIL,
        TOKEN,
        ROOMS,
        LEFT,
        CREATED,
        JOINED,
        MESSAGE,
        INFO
    }

    public record Frame(Command command, List<String> args) {
        public String arg(int index) {
            return args.get(index);
        }

        public int size() {
            return args.size();
        }
    }

    public static String encode(Command command, String... args) {
        StringBuilder line = new StringBuilder();

        line.append(command.name());
        for (String arg : args) {
            line.append(SEPARATOR).append(encodeArg(arg == null ? "" : arg));
        }

        return line.toString();
    }

    public static Frame decode(String line) {
        if (line == null || line.isBlank()) {
            throw new IllegalArgumentException("empty protocol line");
        }

        String[] parts = line.split(SEPARATOR, -1);

        Command command = Command.valueOf(parts[0].trim().toUpperCase(Locale.ROOT));

        List<String> args = new ArrayList<>();
        for (int i = 1; i < parts.length; i++) {
            args.add(decodeArg(parts[i]));
        }

        return new Frame(command, List.copyOf(args));
    }

    private static String encodeArg(String arg) {
        return Base64.getUrlEncoder().withoutPadding().encodeToString(arg.getBytes(StandardCharsets.UTF_8));
    }

    private static String decodeArg(String arg) {
        if (arg.isEmpty()) {
            return "";
        }

        return new String(Base64.getUrlDecoder().decode(arg), StandardCharsets.UTF_8);
    }
}
