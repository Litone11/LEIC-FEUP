# Conversor SVG para PNG em C++

> **Licenciatura em Engenharia Informática e Computação (LEIC @ FEUP)**  
> **Unidade Curricular:** Programação I (PROG)  
> **Autor(es):** Luís Martins, Henrique Gonçalves, Santiago Ferreira

---

## 📌 Sobre o Projeto

Este projeto foi desenvolvido no âmbito da unidade curricular de Programação I. O objetivo consistiu na criação de um leitor e conversor de ficheiros de imagem vetorial no formato **SVG (Scalable Vector Graphics)** para imagens matriciais no formato **PNG (Portable Network Graphics)** em C++.

O programa faz o parsing das especificações XML do SVG, extrai elementos geométricos, aplica transformações afins complexas e renderiza o resultado final pixel a pixel.

---

## 🛠️ Tecnologias e Ferramentas

- **Linguagem:** C++ (C++17)
- **Bibliotecas:** PNG Rendering Library (libpng / RGB image buffer)
- **Conceitos:** Programação Orientada a Objetos (POO), Árvores de Elementos, Transformações Matriciais 2D, Gestão Dinâmica de Memória.

---

## ✨ Funcionalidades Principais

- **Leitura de Primitivas Geométricas:**
  - Suporte a retângulos, círculos, elipses, linhas e polígonos.
  - Extração de propriedades de cor de preenchimento (*fill*) e contorno (*stroke*).
- **Transformações Afins 2D:**
  - Aplicação de transformações de translação (`translate`), rotação (`rotate`) e escala (`scale`).
  - Cálculo matricial de coordenadas de pixéis para renderização exata.
- **Estruturação de Grupos e Reutilização:**
  - Suporte a grupos de elementos `<g>` mantendo hierarquias e transformações combinadas.
  - Reutilização de elementos com a tag `<use>`.

---

## 📁 Estrutura do Repositório

- `src/` — Implementação das classes de elementos SVG, matrizes de transformação e renderizador PNG.
- `include/` — Ficheiros de cabeçalho (`.hpp`).
- `test/` / `samples/` — Ficheiros de teste em formato SVG e saídas esperadas em PNG.

---

## 🚀 Como Executar

1. Compilar o projeto com um compilador C++17:
   ```bash
   g++ -std=c++17 src/*.cpp -o svg2png
   ```
2. Executar passando o ficheiro SVG como argumento:
   ```bash
   ./svg2png exemplo.svg imagem_saida.png
   ```
