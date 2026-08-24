# RCOM — Protocolo de Ligação de Dados e Aplicação (RS-232)

Este projeto consiste na implementação de um protocolo de transmissão de ficheiros através de uma porta de série (RS-232), desenvolvido para a Unidade Curricular de Redes de Computadores (RCOM) da LEIC @ FEUP.

## 📁 Estrutura do Projeto

- `src/`: Código fonte contendo a implementação da camada de ligação de dados (framing, byte stuffing, stop-and-wait ARQ) e da camada de aplicação.
- `bin/`: Executáveis compilados.
- `cable/`: Programa de simulação de cabo virtual com injeção de ruído e desconexão.
- `Makefile`: Script para compilação e execução automatizada de testes.
- `penguin.gif`: Ficheiro de teste para envio através da porta de série.

## 🚀 Como Executar

1. Compilar o projeto e o simulador de cabo virtual:
   ```bash
   make
   ```
2. Executar o simulador de cabo virtual (requer `socat`):
   ```bash
   sudo make run_cable
   ```
3. Noutro terminal, iniciar o recetor:
   ```bash
   make run_rx
   ```
4. Noutro terminal, iniciar o emissor:
   ```bash
   make run_tx
   ```
5. Verificar se o ficheiro recebido coincide com o ficheiro enviado:
   ```bash
   make check_files
   ```
