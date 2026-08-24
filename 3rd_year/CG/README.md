# Computação Gráfica 3D (WebGL / Three.js)

> **Licenciatura em Engenharia Informática e Computação (LEIC @ FEUP)**  
> **Unidade Curricular:** Computação Gráfica (CG)  
> **Autor(es):** Luís Martins, Rafael Cunha, João Sousa

---

## 📌 Sobre o Projeto

Este projeto compreende o desenvolvimento de uma aplicação gráfica 3D interativa no navegador, explorando os fundamentos da síntese de imagem, modelação tridimensional, iluminação, mapeamento de texturas e animação em tempo real.

O projeto foi construído utilizando **WebGL** e a biblioteca **CGF (FEUP Computer Graphics Framework)**, acompanhado por um conjunto de trabalhos práticos evolutivos (`tp1` a `tp5`).

---

## 🛠️ Tecnologias e Conceitos

- **Linguagem:** JavaScript (ES6+), HTML5, CSS3
- **Graphics API:** WebGL, CGF (FEUP Computer Graphics Framework), Three.js / dat.gui
- **Conceitos 3D:**
  - Modelação de Geometria e Malhas de Polígonos (*Meshes*).
  - Transformações Geométricas 3D (Translação, Rotação, Escala).
  - Iluminação (Modelos de Gouraud e Phong).
  - Mapeamento de Texturas 2D e Coordenadas UV.
  - Shaders customizados em GLSL.

---

## ✨ Trabalhos Práticos e Progresso

- `tp1/` — Construção de primitivas 2D/3D simples e transformações básicas (`MyDiamond`, `MyTriangle`).
- `tp2/` — Modelação de objetos 3D complexos (cubos, pirâmides, tanques) e transformações hierárquicas.
- `tp3/` — Aplicação de iluminação, normais de superfícies e materiais reflexivos.
- `tp4/` — Mapeamento de texturas e aplicação de materiais realistas sobre superfícies 3D.
- `tp5/` — Desenvolvimento de Shaders em GLSL (vertex e fragment shaders) e efeitos visuais.
- `project/` — Cena 3D interativa final com controlo de câmara e iluminação dinâmica.

---

## 🚀 Como Executar

1. Abrir o servidor web local na pasta raiz do projeto:
   ```bash
   npx serve .
   ```
2. Abrir o navegador no endereço indicado (ex: `http://localhost:3000/tp1/index.html` ou `http://localhost:3000/project/index.html`).