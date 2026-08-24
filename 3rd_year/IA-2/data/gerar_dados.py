import numpy as np
import pandas as pd

np.random.seed(42)

CATEGORIAS = ["T-shirt", "Calças", "Vestido", "Casaco", "Saia", "Sweater"]
ESTACOES   = ["Primavera", "Verão", "Outono", "Inverno"]
N = 3000

BASE_DEMAND = {
    ("T-shirt",  "Primavera"): 60, ("T-shirt",  "Verão"): 90,  ("T-shirt",  "Outono"): 40, ("T-shirt",  "Inverno"): 20,
    ("Calças",   "Primavera"): 50, ("Calças",   "Verão"): 40,  ("Calças",   "Outono"): 55, ("Calças",   "Inverno"): 50,
    ("Vestido",  "Primavera"): 70, ("Vestido",  "Verão"): 85,  ("Vestido",  "Outono"): 30, ("Vestido",  "Inverno"): 15,
    ("Casaco",   "Primavera"): 20, ("Casaco",   "Verão"): 10,  ("Casaco",   "Outono"): 55, ("Casaco",   "Inverno"): 80,
    ("Saia",     "Primavera"): 55, ("Saia",     "Verão"): 70,  ("Saia",     "Outono"): 25, ("Saia",     "Inverno"): 10,
    ("Sweater",  "Primavera"): 25, ("Sweater",  "Verão"): 10,  ("Sweater",  "Outono"): 60, ("Sweater",  "Inverno"): 75,
}

# Each category has different demand volatility
CAT_VARIANCE = {
    "T-shirt": 6, "Calças": 5, "Vestido": 8, "Casaco": 7, "Saia": 7, "Sweater": 5,
}

rows = []
for _ in range(N):
    categoria = np.random.choice(CATEGORIAS)
    estacao   = np.random.choice(ESTACOES)
    preco     = round(np.random.uniform(10, 150), 2)
    desconto  = np.random.choice([0, 10, 20, 30, 50], p=[0.4, 0.25, 0.2, 0.1, 0.05])
    tendencia = np.random.choice([0, 1], p=[0.7, 0.3])
    # Profit margin as a fraction — higher margin = premium product
    margem    = round(np.random.uniform(0.20, 0.65), 2)

    base = BASE_DEMAND[(categoria, estacao)]

    # Previous season's sales: auto-correlated with current demand (realistic feature)
    hist_base = base * np.random.uniform(0.75, 1.25)
    historico_vendas = max(1, int(hist_base + np.random.normal(0, CAT_VARIANCE[categoria] * 1.2)))

    preco_effect     = -0.15 * (preco - 10) / 140
    desconto_effect  =  0.40 * desconto / 50
    tendencia_effect =  0.25 * tendencia
    # Higher margin → slight demand drop (premium positioning reduces volume)
    margem_effect    = -0.08 * (margem - 0.20) / 0.45
    # Previous season sales drive current season (demand autocorrelation)
    hist_effect      =  0.12 * (historico_vendas / base - 1)

    unidades = base * (1 + preco_effect + desconto_effect + tendencia_effect + margem_effect + hist_effect)
    unidades = max(1, int(unidades + np.random.normal(0, CAT_VARIANCE[categoria])))

    rows.append({
        "categoria":        categoria,
        "estacao":          estacao,
        "preco":            preco,
        "desconto":         desconto,
        "tendencia":        tendencia,
        "margem":           margem,
        "historico_vendas": historico_vendas,
        "unidades":         unidades,
    })

df = pd.DataFrame(rows)
df.to_csv("data/dados_loja.csv", index=False)
print(f"Dataset gerado: {len(df)} registos")
print(df.head(10))
print("\nEstatísticas:")
print(df["unidades"].describe())
