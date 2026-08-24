# IA 1 — Water Sort Puzzle Solver

> **Licenciatura em Engenharia Informática e Computação (LEIC @ FEUP)**  
> **Unidade Curricular:** Inteligência Artificial (IA)  
> **Autor(es):** Luís Martins e Grupo de IA

---

## 📌 Sobre o Projeto

Este projeto consiste num resolvedor (*solver*) inteligente e interativo para o jogo **Water Sort Puzzle**. O jogo desafia o jogador a organizar líquidos de diferentes cores em frascos de ensaio até que cada frasco contenha apenas uma única cor.

O objetivo do projeto foi aplicar e avaliar a eficiência de múltiplos algoritmos de pesquisa no espaço de estados, comparando o tempo de execução, memória utilizada e o número de movimentos necessários para atingir o estado objetivo.

---

## 🛠️ Tecnologias e Algoritmos

- **Linguagem:** Python 3
- **Interface Gráfica:** Tkinter
- **Algoritmos de Pesquisa Não Informada:**
  - Pesquisa em Largura (*Breadth-First Search* - BFS)
  - Pesquisa em Profundidade (*Depth-First Search* - DFS)
  - Pesquisa de Custo Uniforme (*Uniform Cost Search* - UCS)
- **Algoritmos de Pesquisa Informada (Heurísticas):**
  - Pesquisa $A^*$ (*A-Star Search*)
  - Pesquisa Gulosa (*Greedy Best-First Search*)

---

## ✨ Funcionalidades Principais

- **Visualização Gráfica Interativa:** Interface para selecionar tabuleiros, jogar manualmente ou ativar a resolução automática por IA.
- **Seleção de Algoritmo:** Permite escolher qualquer um dos algoritmos de pesquisa e visualizar a animação passo a passo da solução encontrada.
- **Deteção de Estados Repetidos:** Otimização do espaço de estados evitando ciclos e movimentos inválidos.

---

## 📁 Estrutura do Repositório

- `main.py` — Ponto de entrada da aplicação gráfica Tkinter.
- `modes/` — Implementação dos algoritmos de pesquisa e cálculo de heurísticas.
- `rules.py` — Regras de transição de estado e validação de movimentos do puzzle.
- `levels/` — Definição dos níveis de teste do jogo.
- `ui/` — Componentes e menus da interface gráfica.

---

## 🚀 Como Executar

1. Certificar que o Python 3 está instalado:
   ```bash
   python main.py
   ```
