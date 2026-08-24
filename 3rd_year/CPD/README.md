# Computação Paralela e Distribuída (CPD)

> **Licenciatura em Engenharia Informática e Computação (LEIC @ FEUP)**  
> **Unidade Curricular:** Computação Paralela e Distribuída (CPD)  
> **Autor(es):** Luís Martins, Santiago Ferreira, Yago Alba

---

## 📌 Sobre o Projeto

Este repositório reúne os projetos práticos desenvolvidos na unidade curricular de **Computação Paralela e Distribuída (CPD)**. O objetivo principal consistiu no estudo, análise de desempenho e paralelização de algoritmos computacionalmente intensivos.

O trabalho focou-se em tirar partido da hierarquia de memória (*CPU caches*) e em modelos de programação paralela de memória partilhada (**OpenMP**) e memória distribuída (**MPI**).

---

## 🛠️ Tecnologias e Ferramentas

- **Linguagem:** C / C++ (C++17)
- **Programação Paralela:** OpenMP (Multi-threading), MPI (Message Passing Interface)
- **Análise de Desempenho:** PAPI (Performance Application Programming Interface), Perf, Valgrind / KCachegrind
- **Compiladores:** GCC, MPICC

---

## 📁 Trabalhos Práticos

### 1. Trabalho 1 (`assign1/`) — Otimização de Multiplicação de Matrizes
- Estudo e otimização de acessos à memória (efeito da *Cache Miss* na multiplicação de matrizes).
- Comparativo entre algoritmo ingénuo $O(N^3)$, versão baseada em blocos (*tiling*) e paralelização com OpenMP threads.

### 2. Trabalho 2 (`assign2/`) — Computação Distribuída com MPI
- Paralelização de algoritmos de larga escala em ambientes distribuídos.
- Comunicação entre processos via trocas de mensagens (*point-to-point* e *collective communications* em MPI).

---

## 🚀 Como Executar

### Trabalho 1 (OpenMP):
```bash
cd assign1
make
./matrix_mult
```

### Trabalho 2 (MPI):
```bash
cd assign2
mpicc -O3 main.c -o mpi_app
mpirun -np 4 ./mpi_app
```
