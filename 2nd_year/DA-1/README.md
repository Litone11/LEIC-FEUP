# Sistema de Gestão de Abastecimento de Água

> **Licenciatura em Engenharia Informática e Computação (LEIC @ FEUP)**  
> **Unidade Curricular:** Desenho de Algoritmos (DA)  
> **Autor(es):** Luís Martins

---

## 📌 Sobre o Projeto

Este projeto foca-se na conceção e implementação de um sistema de análise e otimização para a rede nacional de abastecimento de água. A rede é modelada como um grafo dirigido ponderado, onde os vértices representam reservatórios, estações de bombagem e cidades (pontos de entrega), e as arestas representam as tubagens de transporte.

O sistema calcula o fluxo máximo de água até aos municípios, deteta pontos de estrangulamento (*bottlenecks*) e simula o impacto de falhas na infraestrutura (desativação de reservatórios ou tubagens).

---

## 🛠️ Tecnologias e Algoritmos

- **Linguagem:** C++ (C++17)
- **Estrutura de Dados:** Grafos Dirigidos e Ponderados (*Adjacency List*)
- **Algoritmos Implementados:**
  - **Fluxo Máximo:** Algoritmo de *Edmonds-Karp* (baseado em BFS sobre grafos residuais).
  - **Análise de Resiliência:** Simulação do impacto da remoção de nós/arestas e recalcular do balanço hídrico.
  - **Métricas:** Determinação de défices de abastecimento por cidade.

---

## ✨ Funcionalidades Principais

- **Cálculo de Fluxo Máximo:** Determinação da quantidade máxima de água que pode ser entregue a cada cidade.
- **Avaliação de Cobertura:** Identificação de municípios com défice de água face às suas necessidades.
- **Simulação de Falhas de Infraestrutura:**
  - Impacto da inativação de um reservatório específico.
  - Impacto de falhas em estações de bombagem.
  - Teste de ruturas em tubagens chave da rede.

---

## 📁 Estrutura do Repositório

- `main.cpp` — Interface de linha de comandos (CLI) e menu interativo.
- `graph.cpp` / `graph.h` — Implementação da classe de Grafo, Vértices, Arestas e algoritmo de Edmonds-Karp.
- `parser.cpp` / `parser.h` — Leitor de datasets em formato CSV (Reservatórios, Estações, Cidades, Tubagens).
- `route.cpp` / `route.h` — Métodos de análise de rotas e balanço hídrico.

---

## 🚀 Como Executar

1. Compilar o código fonte em C++:
   ```bash
   g++ -std=c++17 *.cpp -o water_supply
   ```
2. Executar o programa:
   ```bash
   ./water_supply
   ```
