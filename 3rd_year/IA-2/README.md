# StockSmart — Previsão de Procura com Machine Learning

> **Licenciatura em Engenharia Informática e Computação (LEIC @ FEUP)**  
> **Unidade Curricular:** Inteligência Artificial (IA)  
> **Autor(es):** Luís Martins e Grupo de IA

---

## 📌 Sobre o Projeto

O **StockSmart** é uma aplicação web desenhada para estimar e prever a procura de produtos no setor de vestuário e retalho com base em dados históricos de vendas e modelos de **Machine Learning**.

O sistema treina modelos preditivos para antecipar a necessidade de stock, ajudando a evitar quebras de inventário ou acumulação excessiva de produtos.

---

## 🛠️ Tecnologias Utilizadas

- **Linguagem:** Python (Python 3.10+)
- **Framework Web:** Streamlit
- **Machine Learning & Ciência de Dados:** Scikit-Learn, Pandas, NumPy
- **Visualização de Dados:** Matplotlib, Seaborn

---

## ✨ Funcionalidades Principais

- **Dashboard Interativo em Streamlit:** Interface web para previsão em tempo real com base nos parâmetros do utilizador.
- **Pipeline de Treino de Modelos:** Scripts para geração de datasets sintéticos e treino de modelos de regressão/previsão.
- **Avaliação de Desempenho:** Cálculo automático de métricas de erro ($MSE$, $MAE$, $R^2$) e geração de gráficos de avaliação.

---

## 📁 Estrutura do Repositório

- `app.py` / `main.py` — Aplicação web Streamlit.
- `data/` — Datasets CSV (`dados_loja.csv`) e script gerador (`gerar_dados.py`).
- `model/` — Scripts de treino (`treinar_modelo.py`), modelo treinado (`modelo.pkl`), métricas (`metricas.json`) e gráficos.

---

## 🚀 Como Executar

1. Criar e ativar um ambiente virtual Python:
   ```bash
   python -m venv .venv
   source .venv/bin/activate
   ```
2. Instalar as dependências:
   ```bash
   pip install -r requirements.txt
   ```
3. Treinar o modelo de Machine Learning (opcional):
   ```bash
   python model/treinar_modelo.py
   ```
4. Iniciar a aplicação web Streamlit:
   ```bash
   streamlit run app.py
   ```
