# Algoritmos de Otimização (Problema da Mochila / 0-1 Knapsack)

> **Licenciatura em Engenharia Informática e Computação (LEIC @ FEUP)**  
> **Unidade Curricular:** Desenho de Algoritmos (DA)  
> **Autor(es):** Luís Martins, João Taveira

---

## 📌 Sobre o Projeto

Este projeto aborda o **Problema da Mochila 0/1 (0-1 Knapsack Problem)**, aplicado à otimização da seleção e transporte de paletes e carga em contexto logístico. 

O objetivo principal consistiu em implementar e comparar empiricamente múltiplas abordagens algorítmicas — desde soluções exatas até aproximações heurísticas — analisando o *trade-off* entre tempo de execução e qualidade do resultado.

---

## 🛠️ Tecnologias e Algoritmos

- **Linguagem:** C++ (C++17)
- **Abordagens Algorítmicas:**
  - **Força Bruta (*Brute-Force Search*):** Pesquisa exaustiva de todas as combinações $2^N$.
  - **Programação Dinâmica (*Dynamic Programming*):** Solução exata pseudo-polinomial $O(N \times W)$.
  - **Algoritmos Gulosos (*Greedy Approximation*):** Heurística baseada no rácio valor/peso para grandes instâncias.
  - **Programação Linear Inteira (*ILP*):** Modelação matemática de otimização combinatória.

---

## ✨ Funcionalidades Principais

- **Comparativo de Desempenho:** Medição do tempo de execução em milissegundos para diferentes dimensões de datasets.
- **Seleção de Algoritmo via CLI:** Menu interativo para escolha da estratégia de otimização a aplicar.
- **Leitura de Datasets:** Suporte para carregamento dinâmico de instâncias de teste com múltiplos itens e capacidades de carga.

---

## 📁 Estrutura do Repositório

- `main.cpp` — Interface de utilizador via linha de comandos.
- `algorithms.cpp` / `algorithms.h` — Implementação dos algoritmos (Força Bruta, DP, Greedy, ILP).
- `reader.cpp` / `reader.h` — Parsing de ficheiros de dados.
- `Pallet.h` — Definição da estrutura de dados dos itens/paletes.

---

## 🚀 Como Executar

1. Compilar o projeto em C++:
   ```bash
   g++ -std=c++17 *.cpp -o knapsack_solver
   ```
2. Executar a aplicação:
   ```bash
   ./knapsack_solver
   ```
