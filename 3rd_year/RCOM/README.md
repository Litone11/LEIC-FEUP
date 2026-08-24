# Protocolo de Transmissão por Porta de Série (RS-232)

> **Licenciatura em Engenharia Informática e Computação (LEIC @ FEUP)**  
> **Unidade Curricular:** Redes de Computadores (RCOM)  
> **Autor(es):** Luís Martins e Grupo RCOM

---

## 📌 Sobre o Projeto

Este projeto consistiu na implementação em C de um protocolo de transmissão de ficheiros fiável através de uma comunicação ponto a ponto sobre porta de série (**RS-232**).

O software implementa a arquitetura por camadas: a **Camada de Ligação de Dados** (*Data Link Layer*) responsável pelo enquadramento (*framing*), controlo de erros e controlo de fluxo, e a **Camada de Aplicação** (*Application Layer*) responsável pela fragmentação e reconstituição de ficheiros.

---

## 🛠️ Tecnologias e Mecanismos de Redes

- **Linguagem:** C (C11)
- **APIs do Sistema:** Linux Serial Port API (`termios.h`, `/dev/ttyS*`)
- **Mecanismos da Camada de Ligação de Dados:**
  - **Framing & Byte Stuffing:** Delimitação de tramas com a flag `0x7E` e mecanismo de *stuffing* de caracteres de controlo.
  - **Controlo de Erros (ARQ):** Protocolo *Stop-and-Wait* com tramas $SET$, $UA$, $DISC$, $RR$ (*Receiver Ready*) e $REJ$ (*Reject*).
  - **Timeouts e Retransmissões:** Gestão de alarmes no Linux para retransmissão de tramas perdidas ou corrompidas.

---

## ✨ Funcionalidades Principais

- **Transmissão Robusta de Ficheiros:** Envio de ficheiros de qualquer tipo (ex: imagens `.gif`, ficheiros de texto) com garantia de entrega.
- **Simulador de Cabo Virtual:** Suporte para testes com injeção de ruído, perda de pacotes e desligamento temporário do cabo.
- **Verificação de Integridade:** Validação byte a byte do ficheiro recebido face ao original.

---

## 📁 Estrutura do Repositório

- `src/` — Código fonte principal:
  - `link_layer.c` / `.h` — Implementação da camada de ligação de dados.
  - `application_layer.c` / `.h` — Implementação da camada de aplicação.
  - `serial_port.c` / `.h` — Interface de baixo nível com a porta de série Linux.
- `cable/` — Código do programa de simulação de cabo virtual.
- `bin/` — Binários compilados.

---

## 🚀 Como Executar

1. Compilar o projeto e o simulador de cabo virtual:
   ```bash
   make
   ```
2. Iniciar o cabo virtual num terminal (requer `socat`):
   ```bash
   sudo make run_cable
   ```
3. Iniciar o recetor noutro terminal:
   ```bash
   make run_rx
   ```
4. Iniciar o emissor noutro terminal:
   ```bash
   make run_tx
   ```
5. Validar a integridade do ficheiro transmitido:
   ```bash
   make check_files
   ```
