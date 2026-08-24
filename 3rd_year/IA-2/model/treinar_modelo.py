import pandas as pd
import numpy as np
import pickle
import json
import matplotlib.pyplot as plt
import seaborn as sns
from sklearn.ensemble import RandomForestRegressor
from sklearn.model_selection import train_test_split, cross_val_score, RandomizedSearchCV
from sklearn.preprocessing import LabelEncoder
from sklearn.metrics import mean_absolute_error, mean_squared_error, r2_score

df = pd.read_csv("data/dados_loja.csv")

le_cat = LabelEncoder()
le_est = LabelEncoder()
df["categoria_enc"] = le_cat.fit_transform(df["categoria"])
df["estacao_enc"]   = le_est.fit_transform(df["estacao"])

FEATURES = ["categoria_enc", "estacao_enc", "preco", "desconto", "tendencia", "margem", "historico_vendas"]
TARGET   = "unidades"

X = df[FEATURES]
y = df[TARGET]

X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.2, random_state=42)

# Randomized hyperparameter search
param_dist = {
    "n_estimators":      [100, 200, 300, 500],
    "max_depth":         [8, 10, 15, 20, None],
    "min_samples_split": [2, 5, 10],
    "min_samples_leaf":  [1, 2, 4],
    "max_features":      ["sqrt", "log2", None],
}
print("A fazer hyperparameter search (pode demorar ~30 s)…")
search = RandomizedSearchCV(
    RandomForestRegressor(random_state=42, n_jobs=-1),
    param_dist, n_iter=30, cv=5, scoring="r2",
    random_state=42, n_jobs=-1, verbose=1,
)
search.fit(X_train, y_train)
model = search.best_estimator_
print(f"Melhores parâmetros: {search.best_params_}")

y_pred = model.predict(X_test)

mae  = mean_absolute_error(y_test, y_pred)
rmse = np.sqrt(mean_squared_error(y_test, y_pred))
r2   = r2_score(y_test, y_pred)
cv   = cross_val_score(model, X, y, cv=5, scoring="r2", n_jobs=-1)

print(f"\nMAE:  {mae:.2f} unidades")
print(f"RMSE: {rmse:.2f} unidades")
print(f"R²:   {r2:.4f}")
print(f"R² CV (5-fold): {cv.mean():.4f} ± {cv.std():.4f}")

# Persist metrics so the app can display them live
metrics = {
    "mae":         round(mae, 2),
    "rmse":        round(rmse, 2),
    "r2":          round(r2, 4),
    "r2_cv":       round(cv.mean(), 4),
    "r2_cv_std":   round(cv.std(), 4),
    "n_train":     len(X_train),
    "n_test":      len(X_test),
    "best_params": search.best_params_,
}
with open("model/metricas.json", "w") as f:
    json.dump(metrics, f, indent=2, default=str)
print("Métricas guardadas em model/metricas.json")

with open("model/modelo.pkl", "wb") as f:
    pickle.dump({"model": model, "le_cat": le_cat, "le_est": le_est}, f)
print("Modelo guardado em model/modelo.pkl")

# ── Evaluation plots (2×2) ────────────────────────────────────────────────────
fig, axes = plt.subplots(2, 2, figsize=(14, 10))
fig.suptitle("Avaliação do Modelo — StockSmart", fontsize=14, fontweight="bold")

# 1. Predicted vs Actual
ax = axes[0, 0]
ax.scatter(y_test, y_pred, alpha=0.3, color="steelblue", s=12)
lims = [min(y_test.min(), y_pred.min()), max(y_test.max(), y_pred.max())]
ax.plot(lims, lims, "r--", linewidth=1.5)
ax.set_xlabel("Unidades Reais")
ax.set_ylabel("Unidades Previstas")
ax.set_title(f"Previsto vs Real  (R²={r2:.3f})")

# 2. Residuals
ax = axes[0, 1]
residuals = y_pred - y_test
ax.scatter(y_pred, residuals, alpha=0.3, color="coral", s=12)
ax.axhline(0, color="black", linewidth=1)
ax.set_xlabel("Previsto")
ax.set_ylabel("Resíduo (Previsto − Real)")
ax.set_title("Gráfico de Resíduos")

# 3. Feature importance
ax = axes[1, 0]
feature_labels = {
    "categoria_enc":    "Categoria",
    "estacao_enc":      "Estação",
    "preco":            "Preço (€)",
    "desconto":         "Desconto (%)",
    "tendencia":        "Tendência",
    "margem":           "Margem (%)",
    "historico_vendas": "Histórico Vendas",
}
importances = pd.Series(model.feature_importances_, index=FEATURES).sort_values()
importances.index = [feature_labels[i] for i in importances.index]
importances.plot(kind="barh", ax=ax, color="steelblue")
ax.set_title("Importância das Variáveis")
ax.set_xlabel("Importância")

# 4. Mean demand heatmap
ax = axes[1, 1]
pivot = df.groupby(["categoria", "estacao"])["unidades"].mean().unstack()
pivot = pivot[["Primavera", "Verão", "Outono", "Inverno"]]
sns.heatmap(pivot, ax=ax, annot=True, fmt=".0f", cmap="YlOrRd", linewidths=0.5)
ax.set_title("Procura Média por Categoria e Estação")
ax.set_xlabel("Estação")
ax.set_ylabel("Categoria")

plt.tight_layout()
plt.savefig("model/avaliacao_modelo.png", dpi=150, bbox_inches="tight")
print("Gráficos guardados em model/avaliacao_modelo.png")
plt.show()
