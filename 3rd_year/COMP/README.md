# Compilador de Sub-Java para Jasmin Bytecode

> **Licenciatura em Engenharia Informática e Computação (LEIC @ FEUP)**  
> **Unidade Curricular:** Compiladores (COMP)  
> **Autor(es):** Luís Martins, Guilherme Oliveira, Rafael Cunha, Maria Leonor Beirão

---

## 📌 Sobre o Projeto

Este projeto consistiu no desenvolvimento de um compilador completo para uma linguagem de programação orientada a objetos (subconjunto de Java / Java--).

O compilador efetua todo o pipeline tradicional de compilação: desde a análise léxica e sintática até à verificação semântica, construção da Árvore de Sintaxe Abstrata (AST), otimização de código e geração final de código executável em **Jasmin Bytecode** direcionado à máquina virtual Java (JVM).

---

## 🛠️ Tecnologias e Ferramentas

- **Linguagem do Compilador:** Java (Java 17)
- **Gerador de Parsers:** ANTLR4
- **Target / Assembler:** Jasmin (Java Bytecode Assembler)
- **Build System:** Gradle

---

## ⚙️ Fases do Compilador

1. **Análise Léxica e Sintática (CP1):**
   - Reconhecimento de tokens e gramática utilizando ANTLR4.
   - Tratamento de erros sintáticos e construção de regras gramaticais.
2. **Análise Semântica e AST (CP2):**
   - Construção da Árvore de Sintaxe Abstrata (*Abstract Syntax Tree*).
   - Tabela de Símbolos (*Symbol Table*) para variáveis, métodos e classes.
   - Verificação de tipos (*Type Checking*), verificação de âmbito (*Scope*) e compatibilidade de operandos.
3. **Geração de Código Bytecode (CP3):**
   - Tradução da representação intermédia para instruções de stack-machine em Jasmin bytecode.
   - Gestão de registos locais e otimização de instruções de salto e operações aritméticas.

---

## 📁 Estrutura do Repositório

- `src/` — Código fonte do compilador (Gramáticas ANTLR, Visitadores de AST, Gerador de Jasmin).
- `test/` — Suíte de testes unitários e de integração desenvolvidos pela equipa.
- `test-public/` — Casos de teste públicos de validação.

---

## 🚀 Como Executar

1. Compilar o projeto com o Gradle:
   ```bash
   ./gradlew build
   ```
2. Executar o compilador sobre um ficheiro fonte `.jmm`:
   ```bash
   java -jar build/libs/comp.jar exemplo.jmm -o exemplo.j
   ```
3. Assemble do código Jasmin para um ficheiro `.class` executável:
   ```bash
   java -jar libs/jasmin.jar exemplo.j
   java exemplo
   ```
