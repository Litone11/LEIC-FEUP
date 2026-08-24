package ai;

import java.io.IOException;
import java.net.URI;
import java.net.http.HttpClient;
import java.net.http.HttpRequest;
import java.net.http.HttpResponse;
import java.time.Duration;

public final class OllamaModel {
    private static final String BASE_URL = "http://localhost:11434";
    private static final String MODEL = System.getenv().getOrDefault("OLLAMA_MODEL", "llama3");
    private static final HttpClient CLIENT = HttpClient.newBuilder()
            .connectTimeout(Duration.ofSeconds(10))
            .build();

    private OllamaModel() {
    }

    public static String ollamaAnswer(String question) {
        String body = "{\"model\":\"" + escapeJson(MODEL)
                + "\",\"prompt\":\"" + escapeJson(question)
                + "\",\"stream\":false}";

        HttpRequest request = HttpRequest.newBuilder()
                .uri(URI.create(BASE_URL + "/api/generate"))
                .timeout(Duration.ofMinutes(5))
                .header("Content-Type", "application/json")
                .POST(HttpRequest.BodyPublishers.ofString(body))
                .build();

        try {
            HttpResponse<String> response = CLIENT.send(request, HttpResponse.BodyHandlers.ofString());
            if (response.statusCode() != 200) {
                throw new IllegalStateException("Ollama returned HTTP " + response.statusCode()
                        + ": " + jsonString(response.body(), "error"));
            }
            return jsonString(response.body(), "response");
        } catch (IOException e) {
            throw new IllegalStateException("cannot connect to Ollama at " + BASE_URL, e);
        } catch (InterruptedException e) {
            Thread.currentThread().interrupt();
            throw new IllegalStateException("Ollama request was interrupted", e);
        }
    }

    private static String escapeJson(String value) {
        StringBuilder escaped = new StringBuilder();
        for (int i = 0; i < value.length(); i++) {
            char character = value.charAt(i);
            switch (character) {
                case '"' -> escaped.append("\\\"");
                case '\\' -> escaped.append("\\\\");
                case '\b' -> escaped.append("\\b");
                case '\f' -> escaped.append("\\f");
                case '\n' -> escaped.append("\\n");
                case '\r' -> escaped.append("\\r");
                case '\t' -> escaped.append("\\t");
                default -> {
                    if (character < 0x20) {
                        escaped.append(String.format("\\u%04x", (int) character));
                    } else {
                        escaped.append(character);
                    }
                }
            }
        }
        return escaped.toString();
    }

    private static String jsonString(String json, String field) {
        int keyIndex = json.indexOf("\"" + field + "\"");
        if (keyIndex < 0) {
            throw new IllegalStateException("Ollama response does not contain " + field);
        }
        int valueStart = json.indexOf('"', json.indexOf(':', keyIndex) + 1);
        if (valueStart < 0) {
            throw new IllegalStateException("Ollama response contains an invalid " + field);
        }

        StringBuilder value = new StringBuilder();
        for (int i = valueStart + 1; i < json.length(); i++) {
            char character = json.charAt(i);
            if (character == '"') {
                return value.toString();
            }
            if (character != '\\') {
                value.append(character);
                continue;
            }

            if (++i >= json.length()) {
                break;
            }
            character = json.charAt(i);
            switch (character) {
                case '"', '\\', '/' -> value.append(character);
                case 'b' -> value.append('\b');
                case 'f' -> value.append('\f');
                case 'n' -> value.append('\n');
                case 'r' -> value.append('\r');
                case 't' -> value.append('\t');
                case 'u' -> {
                    if (i + 4 >= json.length()) {
                        throw new IllegalStateException("Ollama response contains invalid JSON");
                    }
                    String digits = json.substring(i + 1, i + 5);
                    value.append((char) Integer.parseInt(digits, 16));
                    i += 4;
                }
                default -> throw new IllegalStateException("Ollama response contains invalid JSON");
            }
        }
        throw new IllegalStateException("Ollama response contains invalid JSON");
    }
}
