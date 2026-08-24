# CPD Project 2

## Prerequisites

- JDK 21 (or later)
- Docker (if using AI rooms)

## Compiling

Run the following commands to create the `out` directory and compile the project into it:

```sh
mkdir -p out
javac --release 21 -d out $(find src -name '*.java')
```

## Running

Client/server communication uses TLS sockets (`javax.net.ssl`). Before running
the chat server, create a server keystore and a client truststore. The example
below creates a local development certificate for `localhost`:

```sh
mkdir -p certs

keytool -genkeypair \
  -alias chat-server \
  -keyalg RSA \
  -keysize 3072 \
  -validity 365 \
  -keystore certs/server.p12 \
  -storetype PKCS12 \
  -storepass changeit \
  -keypass changeit \
  -dname "CN=localhost" \
  -ext SAN=dns:localhost,ip:127.0.0.1

keytool -exportcert \
  -alias chat-server \
  -keystore certs/server.p12 \
  -storepass changeit \
  -rfc \
  -file certs/server.crt

keytool -importcert \
  -alias chat-server \
  -file certs/server.crt \
  -keystore certs/client-truststore.p12 \
  -storetype PKCS12 \
  -storepass changeit \
  -noprompt
```

### Server

In a terminal, start the server:

```sh
java \
  -Djavax.net.ssl.keyStore=certs/server.p12 \
  -Djavax.net.ssl.keyStorePassword=changeit \
  -cp out server.ChatServer
```

By default, the server listens on port `12345` and stores users in
`data/users.txt`. You may pass a different port and user database path:

```sh
java \
  -Djavax.net.ssl.keyStore=certs/server.p12 \
  -Djavax.net.ssl.keyStorePassword=changeit \
  -cp out server.ChatServer <port> <users-file>
```

### Client(s)

In another terminal, to start one or more clients:

```sh
java \
  -Djavax.net.ssl.trustStore=certs/client-truststore.p12 \
  -Djavax.net.ssl.trustStorePassword=changeit \
  -cp out client.ChatClient
```

By default, the client connects to `localhost:12345`. You may pass a different
host and port:

```sh
java \
  -Djavax.net.ssl.trustStore=certs/client-truststore.p12 \
  -Djavax.net.ssl.trustStorePassword=changeit \
  -cp out client.ChatClient <host> <port>
```

### Artificial Intelligence Integration

AI rooms require an Ollama server exposed on `localhost:11434`. Run it with Docker:

```sh
docker run -d --name ollama -p 11434:11434 -v ollama:/root/.ollama ollama/ollama
docker exec ollama ollama pull llama3
```

If the `ollama` container already exists, start it:

```sh
docker start ollama
```

Confirm that the model is available:

```sh
curl http://localhost:11434/api/tags
```

AI rooms use `llama3` by default. Other available models can be found in the
[Ollama model library](https://ollama.com/library). To use one, pull it and set
`OLLAMA_MODEL` when starting the server:

```sh
docker exec ollama ollama pull <model-name>
OLLAMA_MODEL=<model-name> java \
  -Djavax.net.ssl.keyStore=certs/server.p12 \
  -Djavax.net.ssl.keyStorePassword=changeit \
  -cp out server.ChatServer
```

## Testing and Demo

*Disclaimer: The testing and demo aspects described below were generated with AI usage, and not being part of the project's specification, only serve for us to validate our implementation and to aid in demonstrating said implementation's correctness.*

The `test` directory contains dependency-free Java tests for the main server and client flows, plus transcript-style demos for reconnect and slow-client handling.

In a terminal, compile the application and tests with:

```sh
javac --release 21 -d out $(find src -name '*.java') test/IntegrationTests.java test/VisualDemoTests.java
```

Run the integration smoke tests with:

```sh
java -cp out IntegrationTests
```

The optional Ollama response test is skipped by default because it requires an Ollama server and model to be available. To include it, start Ollama as described above and run:

```sh
RUN_OLLAMA_TEST=true java -cp out IntegrationTests
```

Run all visual demos with:

```sh
java -cp out VisualDemoTests
```

To run only one visual demo:

```sh
java -cp out VisualDemoTests reconnect
java -cp out VisualDemoTests slow-client
```
