# Jogo/Aplicação em SO Minix (Low-Level C)

> **Licenciatura em Engenharia Informática e Computação (LEIC @ FEUP)**  
> **Unidade Curricular:** Laboratório de Computadores (LCOM)  
> **Autor(es):** Luís Martins, Henrique Gonçalves, Bárbara Ribeiro, Tomás Silva

---

## 📌 Sobre o Projeto

Este projeto consistiu no desenvolvimento de uma aplicação/jogo interativo em linguagem **C**, a correr diretamente sobre o sistema operativo em tempo real **Minix**. 

A aplicação interage diretamente com o hardware através de *device drivers* desenvolvidos de raiz na disciplina, manipulando interrupções, controladores e memória sem recorrer a bibliotecas de alto nível.

---

## 🛠️ Periféricos de Hardware Controlados

- **Timer (i8254):** Gestão de tempo, framerate de animação e temporizadores.
- **Teclado (KBC i8042):** Leitura de scancodes de teclas via interrupções.
- **Rato (PS/2):** Processamento de pacotes de dados do rato e deteção de movimento/cliques.
- **Placa Gráfica (VBE - VESA BIOS Extensions):** Renderização direta no buffer de vídeo em modo gráfico.
- **RTC (Real-Time Clock):** Leitura de data e hora do sistema para persistência e relógio do jogo.
- **Porta de Série (UART 16550):** Comunicação multiplayer via transmissão de dados de série.

---

## ✨ Funcionalidades do Jogo

- **Interface Gráfica Baseada em XPMs:** Sprites e fundos desenhados pixel a pixel no buffer de vídeo.
- **Deteção de Colisões:** Algoritmos de colisão em tempo real entre entidades do jogo.
- **Modo Multiplayer:** Suporte a comunicação entre duas máquinas via cabo de série.

---

## 📁 Estrutura do Repositório

- `proj/src/` — Código fonte principal do projeto:
  - `devices/` — Drivers de periféricos (Timer, KBC, Mouse, VBE, RTC, Serial).
  - `entities/` — Entidades e elementos interativos.
  - `game/` — Lógica do jogo e máquinas de estados.
  - `xpm/` — Imagens e elementos visuais em formato XPM.
- `lab0` a `lab5` — Laboratórios práticos preparatórios.

---

## 🚀 Como Compilar e Executar no Minix

1. Dentro do ambiente Minix, navegar para a pasta do projeto:
   ```bash
   cd proj/src
   ```
2. Compilar utilizando o Makefile:
   ```bash
   make
   ```
3. Executar o programa:
   ```bash
   lcom_run proj
   ```
