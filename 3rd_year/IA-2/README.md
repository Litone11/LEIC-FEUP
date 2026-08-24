# StockSmart - Previsao de Procura

Aplicacao Streamlit para prever a procura de produtos de uma loja de roupa com base em dados historicos e num modelo de Machine Learning.

## Requisitos

- Python 3.10 ou superior
- `pip`

## Instalar o projeto

Na raiz do projeto, cria e ativa um ambiente virtual:

```bash
python -m venv .venv
source .venv/bin/activate
```

Instala as dependencias:

```bash
pip install -r requirements.txt
```

## Gerar os dados

O projeto ja inclui `data/dados_loja.csv`. Para regenerar o dataset:

```bash
python data/gerar_dados.py
```

## Treinar o modelo

O projeto ja inclui `model/modelo.pkl`, `model/metricas.json` e `model/avaliacao_modelo.png`. Para treinar novamente:

```bash
python model/treinar_modelo.py
```

Este comando atualiza:

- `model/modelo.pkl`
- `model/metricas.json`
- `model/avaliacao_modelo.png`

## Executar a aplicacao

```bash
streamlit run app/app.py
```

Depois abre o endereco indicado no terminal, normalmente:

```text
http://localhost:8501
```

## Fluxo recomendado

Para correr tudo do zero:

```bash
python -m venv .venv
source .venv/bin/activate
pip install -r requirements.txt
python data/gerar_dados.py
python model/treinar_modelo.py
streamlit run app/app.py
```

