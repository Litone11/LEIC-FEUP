# Processador da Linguagem SCRIM (Manipulação de Imagens)

> **Licenciatura em Engenharia Informática e Computação (LEIC @ FEUP)**  
> **Unidade Curricular:** Programação II (PROG)  
> **Autor(es):** Luís Martins

---

## 📌 Sobre o Projeto

Este projeto desenvolve um interpretador e processador para a linguagem **SCRIM**, uma DSL (*Domain-Specific Language*) desenhada para automatizar a manipulação e aplicação de filtros em imagens digitais no formato PNG.

O motor lê scripts de comandos `.scrim`, executa operações sequenciais de processamento de imagem sobre matrizes de cores/pixéis e gera o resultado visual correspondente.

---

## 🛠️ Tecnologias e Ferramentas

- **Linguagem:** C++ (C++17)
- **Sistema de Build:** CMake
- **Ferramentas de Análise:** AddressSanitizer (`-fsanitize=address`) e UndefinedBehaviorSanitizer
- **Bibliotecas:** PNG I/O Wrapper

---

## ✨ Funcionalidades Principais

- **Parser de Comandos (`ScrimParser`):**
  - Leitura e validação sintática de ficheiros de instrução `.scrim`.
  - Suporte a comandos de carregamento, gravação, alteração de dimensão e aplicação de filtros.
- **Operações de Imagem:**
  - Redimensionamento, recorte e rotação de matrizes de pixéis.
  - Manipulação de canais de cor RGB e transparência Alpha.
  - Filtros de imagem e mistura de pixéis.
- **Motor de Testes Integrado (`Tester` & `RunScrim`):**
  - Execução automatizada de testes com comparação entre imagens geradas e resultados esperados.

---

## 📁 Estrutura do Repositório

- `src/` — Implementação do parser, comandos e estruturas de imagem.
- `include/` — Cabeçalhos das classes (`Color.hpp`, `Image.hpp`, `ScrimParser.hpp`, etc.).
- `main/` — Pontos de entrada executáveis (`RunScrim.cpp`, `Tester.cpp`).
- `scrims/` — Exemplos de scripts de teste `.scrim`.

---

## 🚀 Como Executar

1. Gerar o sistema de build com o CMake:
   ```bash
   mkdir build && cd build
   cmake ..
   make
   ```
2. Executar um script SCRIM:
   ```bash
   ./runscrim ../scrims/exemplo.scrim
   ```
3. Executar os testes automatizados:
   ```bash
   ./tester
   ```
