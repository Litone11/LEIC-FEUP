import streamlit as st
import pandas as pd
import numpy as np
import pickle
import json
import plotly.express as px
import plotly.graph_objects as go
from pathlib import Path

BASE = Path(__file__).parent.parent

st.set_page_config(
    page_title="StockSmart — Previsão de Procura",
    layout="wide",
)

st.markdown("""
<style>
    :root {
        --accent: #2563eb;
        --accent-soft: #dbeafe;
        --text-main: #172033;
        --text-muted: #64748b;
        --surface: #ffffff;
        --surface-soft: #f8fafc;
        --border: #e2e8f0;
    }

    .stApp {
        background: var(--surface-soft);
        color: var(--text-main);
    }

    .main .block-container {
        padding-top: 2rem;
        padding-bottom: 3rem;
    }

    section[data-testid="stSidebar"] {
        background: var(--surface);
        border-right: 1px solid var(--border);
    }

    section[data-testid="stSidebar"] [data-testid="stMarkdownContainer"] p,
    section[data-testid="stSidebar"] label,
    section[data-testid="stSidebar"] span {
        color: var(--text-muted);
    }

    .main-header {
        font-size: 2rem;
        font-weight: 700;
        color: var(--text-main);
        margin-bottom: 0.2rem;
        letter-spacing: 0;
    }

    div[data-testid="stMetric"] {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 8px;
        padding: 12px 16px;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
    }

    div[data-testid="stMetric"] label {
        font-size: 0.8rem;
        color: var(--text-muted);
    }

    div[data-testid="stMetricValue"] {
        color: var(--text-main);
    }

    .stButton > button,
    .stDownloadButton > button {
        border-radius: 8px;
        border: 1px solid var(--accent);
        background: var(--accent);
        color: #ffffff;
        font-weight: 600;
    }

    .stButton > button:hover,
    .stDownloadButton > button:hover {
        border-color: #1d4ed8;
        background: #1d4ed8;
        color: #ffffff;
    }

    div[data-baseweb="select"] > div,
    div[data-baseweb="input"] > div,
    div[data-baseweb="slider"] {
        border-radius: 8px;
    }

    div[data-baseweb="select"],
    div[data-baseweb="select"] *,
    ul[data-testid="stVirtualDropdown"] li {
        cursor: pointer !important;
    }

    hr {
        border-color: var(--border);
    }
</style>
""", unsafe_allow_html=True)

ESTACOES_ORDER = ["Primavera", "Verão", "Outono", "Inverno"]
FEATURE_COLS   = ["categoria_enc", "estacao_enc", "preco", "desconto",
                   "tendencia", "margem", "historico_vendas"]
FEATURE_LABELS = {
    "categoria_enc":    "Categoria",
    "estacao_enc":      "Estação",
    "preco":            "Preço (€)",
    "desconto":         "Desconto (%)",
    "tendencia":        "Tendência",
    "margem":           "Margem (%)",
    "historico_vendas": "Histórico Vendas",
}

CHART_COLORS = {
    "accent": "#2563eb",
    "accent_soft": "#bfdbfe",
    "green": "#22c55e",
    "amber": "#f59e0b",
    "rose": "#fb7185",
    "slate": "#64748b",
    "grid": "#e2e8f0",
}

SEASON_COLORS = ["#86efac", "#fcd34d", "#fb7185", "#93c5fd"]
CONTINUOUS_SCALE = ["#f8fafc", "#dbeafe", "#93c5fd", "#2563eb"]
WARM_SCALE = ["#fff7ed", "#fed7aa", "#fdba74", "#f97316"]
HEATMAP_SCALE = ["#f8fafc", "#dbeafe", "#bfdbfe", "#60a5fa", "#2563eb"]

px.defaults.template = "plotly_white"
px.defaults.color_discrete_sequence = [
    CHART_COLORS["accent"],
    CHART_COLORS["green"],
    CHART_COLORS["amber"],
    CHART_COLORS["rose"],
    CHART_COLORS["slate"],
]


@st.cache_resource
def load_model():
    with open(BASE / "model/modelo.pkl", "rb") as f:
        return pickle.load(f)


@st.cache_data
def load_data():
    return pd.read_csv(BASE / "data/dados_loja.csv")


@st.cache_data
def load_metrics():
    p = BASE / "model/metricas.json"
    if p.exists():
        with open(p) as f:
            return json.load(f)
    return {}


artifacts = load_model()
model     = artifacts["model"]
le_cat    = artifacts["le_cat"]
le_est    = artifacts["le_est"]
df        = load_data()
metrics   = load_metrics()


def predict_with_interval(X_df, alpha=0.10):
    """90 % prediction interval derived from individual tree variance."""
    tree_preds = np.array([t.predict(X_df.values) for t in model.estimators_])
    mean = tree_preds.mean(axis=0)
    low  = np.maximum(1, np.quantile(tree_preds, alpha / 2, axis=0))
    high = np.quantile(tree_preds, 1 - alpha / 2, axis=0)
    return mean, low, high


def build_input(categoria, estacao, preco, desconto, tendencia_val, margem, historico_vendas):
    return pd.DataFrame(
        [[le_cat.transform([categoria])[0], le_est.transform([estacao])[0],
          preco, desconto, tendencia_val, margem, historico_vendas]],
        columns=FEATURE_COLS,
    )


# ── Sidebar ───────────────────────────────────────────────────────────────────
st.sidebar.image("https://img.icons8.com/color/96/clothes.png", width=80)
st.sidebar.title("StockSmart")
st.sidebar.caption("Previsão de Procura para Lojas de Roupa")
if metrics:
    st.sidebar.markdown(f"**Modelo:** R² = {metrics.get('r2', '—')}")
st.sidebar.markdown("---")
page = st.sidebar.radio("Navegar", ["Previsão", "Previsão em Lote", "Análise", "Sobre"])


# ── PAGE 1: Single prediction ─────────────────────────────────────────────────
if page == "Previsão":
    st.markdown('<p class="main-header">Previsão de Procura</p>', unsafe_allow_html=True)
    st.markdown("Preenche as características do produto para obter uma estimativa de unidades a encomendar.")

    col1, col2 = st.columns(2)

    with col1:
        st.subheader("Produto")
        categoria = st.selectbox("Categoria", sorted(le_cat.classes_))
        estacao   = st.selectbox("Estação", ESTACOES_ORDER)
        preco     = st.slider("Preço (€)", 10.0, 150.0, 39.99, 0.5)

    with col2:
        st.subheader("Condições Comerciais")
        desconto  = st.select_slider("Desconto (%)", options=[0, 10, 20, 30, 50], value=0)
        tendencia = st.radio("Artigo em tendência?", [("Não", 0), ("Sim", 1)],
                             format_func=lambda x: x[0])
        margem    = st.slider("Margem de lucro (%)", 20, 65, 35, 1) / 100

    default_hist = int(
        df[(df["categoria"] == categoria) & (df["estacao"] == estacao)]["historico_vendas"].mean()
    )
    with st.expander("Configurações Avançadas"):
        historico_vendas = st.number_input(
            "Vendas na época anterior (unidades)",
            min_value=1, max_value=150, value=default_hist,
            help="Unidades vendidas nesta categoria/estação na época anterior. Pré-preenchido com a média histórica.",
        )

    st.markdown("---")

    if st.button("Calcular Previsão", type="primary", use_container_width=True):
        X_in = build_input(categoria, estacao, preco, desconto, tendencia[1], margem, historico_vendas)
        mean_arr, low_arr, high_arr = predict_with_interval(X_in)

        pred    = int(round(mean_arr[0]))
        low_v   = int(round(low_arr[0]))
        high_v  = int(round(high_arr[0]))
        preco_f = preco * (1 - desconto / 100)

        c1, c2, c3 = st.columns(3)
        c1.metric("Unidades a Encomendar", f"{pred} un.")
        c2.metric("Investimento Estimado", f"€ {pred * preco_f:,.0f}")
        c3.metric("Preço Final ao Cliente", f"€ {preco_f:.2f}")

        st.info(
            f"**Intervalo de confiança (90%):** entre **{low_v}** e **{high_v}** unidades  "
            f"— calculado a partir da variância das {len(model.estimators_)} árvores do modelo."
        )

        st.subheader("Contexto Sazonal")
        seasonal = (
            df[df["categoria"] == categoria]
            .groupby("estacao")["unidades"].mean()
            .reindex(ESTACOES_ORDER)
            .reset_index()
        )
        seasonal.columns = ["Estação", "Média Histórica"]
        seasonal["Destaque"] = seasonal["Estação"].apply(
            lambda e: "Estação Selecionada" if e == estacao else "Outras Estações"
        )

        fig = px.bar(
            seasonal, x="Estação", y="Média Histórica",
            color="Destaque",
            color_discrete_map={"Estação Selecionada": CHART_COLORS["accent"], "Outras Estações": CHART_COLORS["accent_soft"]},
            title=f"Procura Histórica Média — {categoria}",
            labels={"Média Histórica": "Unidades"},
        )
        fig.add_hline(y=pred, line_dash="dash", line_color=CHART_COLORS["rose"],
                      annotation_text=f"Previsão: {pred} un.", annotation_position="top right")
        fig.add_hrect(y0=low_v, y1=high_v, fillcolor=CHART_COLORS["rose"], opacity=0.08,
                      line_width=0, annotation_text="IC 90%", annotation_position="top left")
        fig.update_layout(showlegend=True, xaxis_title="", height=360)
        st.plotly_chart(fig, use_container_width=True)


# ── PAGE 2: Batch prediction ──────────────────────────────────────────────────
elif page == "Previsão em Lote":
    st.markdown('<p class="main-header">Previsão em Lote</p>', unsafe_allow_html=True)
    st.markdown("Faz upload de um CSV com vários produtos para obter previsões em massa.")

    st.info(
        "**Colunas esperadas:** `categoria`, `estacao`, `preco`, `desconto`, `tendencia` (0/1), "
        "`margem` (decimal, ex: 0.35), `historico_vendas` (inteiro)."
    )

    template = pd.DataFrame({
        "categoria":        ["T-shirt", "Casaco", "Vestido"],
        "estacao":          ["Verão", "Inverno", "Primavera"],
        "preco":            [29.99, 89.99, 49.99],
        "desconto":         [0, 20, 10],
        "tendencia":        [1, 0, 1],
        "margem":           [0.35, 0.45, 0.40],
        "historico_vendas": [75, 60, 55],
    })
    st.download_button("Descarregar Template CSV", template.to_csv(index=False),
                       "template.csv", "text/csv")

    uploaded = st.file_uploader("Carregar ficheiro CSV", type="csv")

    if uploaded:
        try:
            inp = pd.read_csv(uploaded)
            st.subheader("Dados carregados")
            st.dataframe(inp, use_container_width=True)

            inp = inp.copy()
            inp["categoria_enc"] = le_cat.transform(inp["categoria"])
            inp["estacao_enc"]   = le_est.transform(inp["estacao"])
            X_batch = inp[FEATURE_COLS]

            mean_arr, low_arr, high_arr = predict_with_interval(X_batch)
            inp["previsao"]      = np.round(mean_arr).astype(int)
            inp["ic_baixo"]      = np.round(low_arr).astype(int)
            inp["ic_alto"]       = np.round(high_arr).astype(int)
            inp["investimento"]  = (inp["previsao"] * inp["preco"] * (1 - inp["desconto"] / 100)).round(2)

            result = inp[["categoria", "estacao", "preco", "desconto",
                           "previsao", "ic_baixo", "ic_alto", "investimento"]]

            st.subheader("Resultados")
            st.dataframe(result, use_container_width=True)

            st.download_button("Descarregar Resultados CSV", result.to_csv(index=False),
                               "previsoes.csv", "text/csv")

            fig = go.Figure()
            for i, row in result.iterrows():
                label = f"{row['categoria']} / {row['estacao']}"
                fig.add_trace(go.Bar(
                    name=label, x=[label], y=[row["previsao"]],
                    error_y=dict(type="data",
                                 array=[row["ic_alto"] - row["previsao"]],
                                 arrayminus=[row["previsao"] - row["ic_baixo"]]),
                ))
            fig.update_layout(title="Previsão por Produto (com IC 90%)",
                              yaxis_title="Unidades", showlegend=False, height=400)
            st.plotly_chart(fig, use_container_width=True)

        except Exception as e:
            st.error(f"Erro ao processar ficheiro: {e}")


# ── PAGE 3: Data analysis ─────────────────────────────────────────────────────
elif page == "Análise":
    st.markdown('<p class="main-header">Análise dos Dados Históricos</p>', unsafe_allow_html=True)

    c1, c2, c3 = st.columns(3)
    c1.metric("Total de Registos", f"{len(df):,}")
    c2.metric("Média de Unidades", f"{df['unidades'].mean():.1f}")
    c3.metric("Máximo Vendido", f"{df['unidades'].max()} un.")

    st.markdown("---")

    col_a, col_b = st.columns(2)

    with col_a:
        season_mean = (
            df.groupby("estacao")["unidades"].mean()
            .reindex(ESTACOES_ORDER).reset_index()
        )
        fig1 = px.bar(
            season_mean, x="estacao", y="unidades",
            color="estacao",
            color_discrete_sequence=SEASON_COLORS,
            title="Procura Média por Estação",
            labels={"estacao": "", "unidades": "Média de Unidades"},
        )
        fig1.update_layout(showlegend=False)
        st.plotly_chart(fig1, use_container_width=True)

    with col_b:
        cat_mean = df.groupby("categoria")["unidades"].mean().sort_values().reset_index()
        fig2 = px.bar(
            cat_mean, x="unidades", y="categoria", orientation="h",
            color="unidades", color_continuous_scale=CONTINUOUS_SCALE,
            title="Procura Média por Categoria",
            labels={"unidades": "Média de Unidades", "categoria": ""},
        )
        fig2.update_layout(coloraxis_showscale=False)
        st.plotly_chart(fig2, use_container_width=True)

    # Heatmap category × season
    pivot = (
        df.groupby(["categoria", "estacao"])["unidades"].mean()
        .reset_index()
        .pivot(index="categoria", columns="estacao", values="unidades")
    )
    pivot = pivot[ESTACOES_ORDER]
    fig3 = px.imshow(
        pivot, text_auto=".0f", color_continuous_scale=HEATMAP_SCALE,
        title="Procura Média — Categoria × Estação",
        labels={"x": "Estação", "y": "Categoria", "color": "Unidades"},
        aspect="auto",
    )
    st.plotly_chart(fig3, use_container_width=True)

    col_c, col_d = st.columns(2)

    with col_c:
        disc_mean = df.groupby("desconto")["unidades"].mean().reset_index()
        fig4 = px.bar(
            disc_mean, x="desconto", y="unidades",
            color="unidades", color_continuous_scale=WARM_SCALE,
            title="Efeito do Desconto na Procura",
            labels={"desconto": "Desconto (%)", "unidades": "Média de Unidades"},
        )
        fig4.update_layout(coloraxis_showscale=False)
        st.plotly_chart(fig4, use_container_width=True)

    with col_d:
        sample = df.sample(600, random_state=42)
        fig5 = px.scatter(
            sample, x="preco", y="unidades", color="categoria",
            opacity=0.55, title="Preço vs Procura (amostra)",
            labels={"preco": "Preço (€)", "unidades": "Unidades"},
        )
        fig5.update_traces(marker=dict(size=5))
        st.plotly_chart(fig5, use_container_width=True)

    # Feature importance
    imp = (
        pd.DataFrame({"feature": [FEATURE_LABELS[f] for f in FEATURE_COLS],
                      "importance": model.feature_importances_})
        .sort_values("importance")
    )
    fig6 = px.bar(
        imp, x="importance", y="feature", orientation="h",
        color="importance", color_continuous_scale=CONTINUOUS_SCALE,
        title="Importância das Variáveis no Modelo",
        labels={"importance": "Importância", "feature": ""},
    )
    fig6.update_layout(coloraxis_showscale=False)
    st.plotly_chart(fig6, use_container_width=True)


# ── PAGE 4: About ─────────────────────────────────────────────────────────────
elif page == "Sobre":
    st.markdown('<p class="main-header">Sobre o StockSmart</p>', unsafe_allow_html=True)

    st.markdown("""
    ## O Problema
    Uma loja de roupa enfrenta um desafio constante: **quanto stock encomendar** para cada produto,
    em cada estação? Encomendar demasiado imobiliza capital e gera saldos. Encomendar de menos
    significa vendas perdidas e clientes insatisfeitos.

    ## A Solução
    O **StockSmart** usa *machine learning* para prever a procura com base em:
    - Categoria do produto e estação do ano
    - Preço de venda e desconto aplicado
    - Se o artigo está em tendência nas redes sociais
    - Margem de lucro do produto
    - Histórico de vendas da época anterior
    """)

    if metrics:
        st.subheader("Desempenho do Modelo")
        c1, c2, c3, c4 = st.columns(4)
        c1.metric("MAE", f"{metrics['mae']} un.")
        c2.metric("RMSE", f"{metrics['rmse']} un.")
        c3.metric("R²", f"{metrics['r2']:.4f}")
        c4.metric("R² CV (5-fold)", f"{metrics['r2_cv']:.4f} ± {metrics['r2_cv_std']:.4f}")

        st.markdown(f"""
| Métrica | Valor | Interpretação |
|---------|-------|---------------|
| MAE | {metrics['mae']} un. | Erro médio absoluto na previsão |
| RMSE | {metrics['rmse']} un. | Raiz do erro quadrático médio |
| R² | {metrics['r2']:.3f} | Proporção da variância explicada pelo modelo |
| Treino | {metrics['n_train']} registos | Tamanho do conjunto de treino |
        """)

    eval_img = BASE / "model/avaliacao_modelo.png"
    if eval_img.exists():
        st.subheader("Gráficos de Avaliação")
        st.image(str(eval_img), use_container_width=True)

    st.markdown("""
    ## Impacto Esperado
    - Redução de 20–30% em excesso de stock
    - Menos rupturas de stock em períodos de pico
    - Melhor planeamento de encomendas e cash flow

    ---
    *Projeto desenvolvido para IST — Inteligência Artificial 2025/2026*
    """)
