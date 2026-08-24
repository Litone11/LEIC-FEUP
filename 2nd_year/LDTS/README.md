# The Invaders — Retro Arcade Game

> **Licenciatura em Engenharia Informática e Computação (LEIC @ FEUP)**  
> **Unidade Curricular:** Laboratório de Desenho e Teste de Software (LDTS)  
> **Autor(es):** Luís Martins e Grupo LDTS

---

## 📌 Sobre o Projeto

**The Invaders** é um jogo arcade retro inspirado em clássicos de *fixed shooter* como *Space Invaders* e *Pac-Man*. O jogador controla a nave/entidade na parte inferior do ecrã e spara contra as vagas de inimigos que se aproximam.

O foco principal do projeto foi a aplicação rigorosa de **Padrões de Desenho de Software (Design Patterns)**, arquitetura **MVC (Model-View-Controller)** e testes unitários automatizados.

---

## 🛠️ Tecnologias e Arquitetura

- **Linguagem:** Java (Java 17)
- **Interface Gráfica:** Lanterna GUI Library (Interface baseada em caracteres/Terminal)
- **Testes & Qualidade:** JUnit 5, Mockito (Mocking), Pitest (Testes de Mutação)
- **Design Patterns Aplicados:**
  - **MVC (Model-View-Controller):** Separação total de estado, representação visual e controlo.
  - **State Pattern:** Gestão dos menus do jogo (Menu Principal, Jogo, Instruções, Scoreboard).
  - **Factory Method:** Geração de temas visuais (Tema Space Invaders e Tema Pac-Man).

---

## ✨ Funcionalidades Principais

- **Múltiplos Temas Visuais:** Comutação dinâmica entre o tema *Space Invaders* e o tema *Pac-Man*.
- **Menus Interativos:** Menu Inicial, Seleção de Tema, Instruções e Tabela de Pontuações (*Scoreboard*).
- **Mecânica de Jogo Completa:** Movimento, disparos, colisões, pontuação e níveis de dificuldade progressivos.

---

## 📸 Screenshots

| Tema Space Invaders | Tema PAC-MAN |
| :---: | :---: |
| ![Space](docs/screenshots/space.gif) | ![Pac](docs/screenshots/pac.gif) |

---

## 🚀 Como Executar

1. Compilar o projeto com o Gradle:
   ```bash
   ./gradlew build
   ```
2. Executar o jogo:
   ```bash
   ./gradlew run
   ```
3. Executar os testes unitários e de mutação:
   ```bash
   ./gradlew test
   ./gradlew pitest
   ```