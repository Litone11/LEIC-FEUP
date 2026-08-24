#!/usr/bin/env python3
"""Generate charts from benchmark CSV data."""

from __future__ import annotations

import argparse
import csv
import math
from collections import defaultdict
from html import escape
from pathlib import Path

NUMERIC_FIELDS = [
    "time_s",
    "gflops",
    "cycles",
    "instructions",
    "ipc",
    "cache_references",
    "cache_misses",
    "branches",
    "branch_misses",
]

CSV_HEADERS = [
    "lang",
    "mode",
    "n",
    "block",
    "rep",
    "time_s",
    "gflops",
    "cycles",
    "instructions",
    "ipc",
    "cache_references",
    "cache_misses",
    "branches",
    "branch_misses",
]

LANG_ORDER = {"cpp": 0, "java": 1}
LANG_LABELS = {"cpp": "C++", "java": "Java"}

EXERCISES = [
    {
        "id": 1,
        "mode": 1,
        "title": "Exercise 1",
        "group_fields": ["lang", "n"],
        "series_field": "lang",
    },
    {
        "id": 2,
        "mode": 2,
        "title": "Exercise 2",
        "group_fields": ["lang", "n"],
        "series_field": "lang",
    },
    {
        "id": 3,
        "mode": 3,
        "title": "Exercise 3",
        "group_fields": ["block", "n"],
        "series_field": "block",
    },
]

CHART_SPECS = [
    ("time_s", "Execution Time (s)"),
    ("gflops", "Performance (GFlop/s)"),
]

# Faz o parsing dos argumentos da linha de comando para localizar o CSV e a pasta de saida.
# Como funciona: define as flags esperadas, valida a presenca delas e devolve um objeto com os valores lidos.
# Vantagem: define uma interface clara do script. Desvantagem: obriga a usar flags fixas.
def parse_args() -> argparse.Namespace:
    parser = argparse.ArgumentParser(
        description="Create SVG charts from the benchmark CSV."
    )
    parser.add_argument("--csv", required=True, help="Input CSV file with all results.")
    parser.add_argument(
        "--out-dir",
        required=True,
        help="Results directory where the graphs folder will be created.",
    )
    parser.add_argument(
        "--quiet",
        action="store_true",
        help="Do not print the final generated graphs path.",
    )
    return parser.parse_args()

# Converte texto para float e trata campos vazios ou "NA" como ausencia de valor.
# Como funciona: limpa a string, verifica casos especiais e so depois tenta a conversao numerica.
# Vantagem: centraliza a limpeza numerica. Desvantagem: assume um formato de entrada muito especifico.
def parse_float(value: str) -> float | None:
    text = value.strip()
    if not text or text == "NA":
        return None
    return float(text)

# Le o CSV completo, valida as colunas esperadas e converte os tipos relevantes.
# Como funciona: percorre linha a linha, monta um dicionario tipado e acumula tudo numa lista.
# Vantagem: deteta erros cedo no ficheiro de origem. Desvantagem: carrega tudo em memoria.
def parse_rows(csv_path: Path) -> list[dict[str, object]]:
    with csv_path.open(newline="", encoding="utf-8") as handle:
        reader = csv.DictReader(handle)
        missing = [header for header in CSV_HEADERS if header not in (reader.fieldnames or [])]
        if missing:
            raise ValueError(f"Missing expected CSV columns: {', '.join(missing)}")

        rows = []
        for row in reader:
            parsed = {
                "lang": row["lang"].strip(),
                "mode": int(row["mode"]),
                "n": int(row["n"]),
                "block": int(row["block"]),
                "rep": int(row["rep"]),
            }
            for field in NUMERIC_FIELDS:
                parsed[field] = parse_float(row[field])
            rows.append(parsed)
    return rows

# Calcula a media aritmetica de uma lista de valores.
# Como funciona: soma todos os elementos e divide pelo numero total, devolvendo None se a lista estiver vazia.
# Vantagem: e simples e reutilizavel. Desvantagem: nao lida com pesos nem outliers.
def average(values: list[float]) -> float | None:
    if not values:
        return None
    return sum(values) / len(values)

# Normaliza valores para escrita em CSV/SVG, evitando infinitos e excesso de casas decimais.
# Como funciona: inspeciona o tipo do valor e aplica regras diferentes para None, inteiros, floats e texto.
# Vantagem: garante saida consistente. Desvantagem: perde alguma precisao original.
def format_output(value: object) -> object:
    if value is None:
        return "NA"
    if isinstance(value, bool):
        return str(value)
    if isinstance(value, int):
        return value
    if isinstance(value, float):
        if not math.isfinite(value):
            return "NA"
        if value.is_integer():
            return int(value)
        text = f"{value:.6f}".rstrip("0").rstrip(".")
        return text or "0"
    return str(value)

# Define a ordem das linhas resumidas para cada exercicio.
# Como funciona: devolve uma chave de ordenacao diferente consoante o exercicio e os campos mais relevantes.
# Vantagem: mantem tabelas previsiveis. Desvantagem: depende de regras codificadas manualmente.
def summary_sort_key(exercise_id: int, row: dict[str, object]) -> tuple[int, int]:
    if exercise_id in (1, 2):
        return (LANG_ORDER.get(str(row["lang"]), 99), int(row["n"]))
    return (int(row["block"]), int(row["n"]))

# Agrupa repeticoes equivalentes e calcula medias das metricas por grupo.
# Como funciona: cria grupos pela chave do exercicio, recolhe os valores de cada metrica e calcula medias por grupo.
# Vantagem: simplifica a analise final. Desvantagem: esconde a variabilidade entre execucoes.
def build_summary(
    rows: list[dict[str, object]], exercise: dict[str, object]
) -> list[dict[str, object]]:
    group_fields = list(exercise["group_fields"])
    grouped: dict[tuple[object, ...], list[dict[str, object]]] = defaultdict(list)

    for row in rows:
        key = tuple(row[field] for field in group_fields)
        grouped[key].append(row)

    summary_rows = []
    for key, items in grouped.items():
        summary_row = {field: value for field, value in zip(group_fields, key)}
        summary_row["samples"] = len(items)
        for field in NUMERIC_FIELDS:
            values = [float(item[field]) for item in items if item[field] is not None]
            summary_row[f"avg_{field}"] = average(values)
        summary_rows.append(summary_row)

    summary_rows.sort(key=lambda row: summary_sort_key(int(exercise["id"]), row))
    return summary_rows

# Organiza os pontos por serie para desenhar cada grafico.
# Como funciona: percorre o resumo, junta pares (x,y) por serie e ordena os pontos antes de os devolver.
# Vantagem: separa a preparacao dos dados da renderizacao. Desvantagem: ignora pontos sem valor.
def build_chart_series(
    summary_rows: list[dict[str, object]], exercise: dict[str, object], metric: str
) -> list[dict[str, object]]:
    series_field = str(exercise["series_field"])
    series_points: dict[object, list[tuple[int, float]]] = defaultdict(list)

    for row in summary_rows:
        value = row.get(f"avg_{metric}")
        if value is None:
            continue
        series_points[row[series_field]].append((int(row["n"]), float(value)))

    ordered_keys = sorted(
        series_points,
        key=lambda key: LANG_ORDER.get(str(key), 99)
        if series_field == "lang"
        else int(key),
    )

    series = []
    for key in ordered_keys:
        points = sorted(series_points[key], key=lambda point: point[0])
        if not points:
            continue
        label = LANG_LABELS.get(str(key), str(key))
        if series_field == "block":
            label = f"Block {key}"
        series.append({"label": label, "points": points})
    return series

# Traduz o identificador interno da metrica para um nome mais legivel.
# Como funciona: procura a metrica na lista de especificacoes e devolve a etiqueta associada.
# Vantagem: evita repetir textos no codigo. Desvantagem: requer manter o mapa atualizado.
def metric_label(metric: str) -> str:
    for key, label in CHART_SPECS:
        if key == metric:
            return label
    return metric

# Gera manualmente um grafico SVG de linhas com eixos, legenda e pontos.
# Como funciona: calcula escalas, desenha grelha/eixos/legenda e escreve o SVG final como texto.
# Vantagem: nao precisa de bibliotecas externas. Desvantagem: o layout e todo mantido a mao.
def write_line_chart_svg(
    path: Path, title: str, metric: str, series: list[dict[str, object]]
) -> None:
    width = 960
    height = 560
    left = 90
    right = 250
    top = 70
    bottom = 80
    plot_width = width - left - right
    plot_height = height - top - bottom
    colors = ["#0f766e", "#c2410c", "#2563eb", "#7c3aed", "#be185d", "#4d7c0f"]

    x_values = sorted({x for item in series for x, _ in item["points"]})
    y_values = [y for item in series for _, y in item["points"]]

    if not x_values or not y_values:
        return

    x_min = min(x_values)
    x_max = max(x_values)
    y_min = 0.0
    y_max = max(y_values)
    if math.isclose(y_max, y_min):
        y_max = y_min + 1.0
    else:
        y_max *= 1.1

    # Mapeia um valor do eixo X para a largura util do grafico.
    # Como funciona: converte a posicao relativa entre x_min e x_max numa coordenada horizontal.
    # Vantagem: encapsula a escala num sitio so. Desvantagem: suporta apenas escala linear.
    def scale_x(value: float) -> float:
        if x_max == x_min:
            return left + plot_width / 2
        return left + ((value - x_min) / (x_max - x_min)) * plot_width

    # Mapeia um valor do eixo Y para a altura util do grafico.
    # Como funciona: transforma o valor numerico numa coordenada vertical invertida para o sistema do SVG.
    # Vantagem: mantem a conversao consistente. Desvantagem: nao contempla escalas alternativas.
    def scale_y(value: float) -> float:
        return top + plot_height - ((value - y_min) / (y_max - y_min)) * plot_height

    svg = [
        f'<svg xmlns="http://www.w3.org/2000/svg" width="{width}" height="{height}" viewBox="0 0 {width} {height}">',
        '<rect width="100%" height="100%" fill="#ffffff"/>',
        f'<text x="{width / 2}" y="34" text-anchor="middle" font-size="24" font-family="Arial, sans-serif" fill="#111827">{escape(title)}</text>',
        f'<text x="{width / 2}" y="58" text-anchor="middle" font-size="14" font-family="Arial, sans-serif" fill="#4b5563">{escape(metric_label(metric))}</text>',
    ]

    tick_count = 6
    for index in range(tick_count):
        ratio = index / (tick_count - 1)
        value = y_min + (y_max - y_min) * ratio
        y = scale_y(value)
        svg.append(
            f'<line x1="{left}" y1="{y:.2f}" x2="{left + plot_width}" y2="{y:.2f}" stroke="#e5e7eb" stroke-width="1"/>'
        )
        svg.append(
            f'<text x="{left - 12}" y="{y + 4:.2f}" text-anchor="end" font-size="12" font-family="Arial, sans-serif" fill="#374151">{escape(str(format_output(value)))}</text>'
        )

    for x_value in x_values:
        x = scale_x(x_value)
        svg.append(
            f'<line x1="{x:.2f}" y1="{top}" x2="{x:.2f}" y2="{top + plot_height}" stroke="#f3f4f6" stroke-width="1"/>'
        )
        svg.append(
            f'<text x="{x:.2f}" y="{top + plot_height + 28}" text-anchor="middle" font-size="12" font-family="Arial, sans-serif" fill="#374151">{x_value}</text>'
        )

    svg.append(
        f'<line x1="{left}" y1="{top + plot_height}" x2="{left + plot_width}" y2="{top + plot_height}" stroke="#111827" stroke-width="1.5"/>'
    )
    svg.append(
        f'<line x1="{left}" y1="{top}" x2="{left}" y2="{top + plot_height}" stroke="#111827" stroke-width="1.5"/>'
    )
    svg.append(
        f'<text x="{left + plot_width / 2}" y="{height - 24}" text-anchor="middle" font-size="14" font-family="Arial, sans-serif" fill="#111827">Matrix size (n)</text>'
    )
    svg.append(
        f'<text x="28" y="{top + plot_height / 2}" text-anchor="middle" transform="rotate(-90 28 {top + plot_height / 2})" font-size="14" font-family="Arial, sans-serif" fill="#111827">{escape(metric_label(metric))}</text>'
    )

    legend_y = top + 18
    legend_x = left + plot_width + 32
    for index, item in enumerate(series):
        color = colors[index % len(colors)]
        points = item["points"]
        path_data = " ".join(
            (
                f"M {scale_x(points[0][0]):.2f} {scale_y(points[0][1]):.2f}",
                *[
                    f"L {scale_x(x):.2f} {scale_y(y):.2f}"
                    for x, y in points[1:]
                ],
            )
        )
        svg.append(
            f'<path d="{path_data}" fill="none" stroke="{color}" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>'
        )
        for x_value, y_value in points:
            svg.append(
                f'<circle cx="{scale_x(x_value):.2f}" cy="{scale_y(y_value):.2f}" r="4.5" fill="{color}" stroke="#ffffff" stroke-width="1.5"/>'
            )

        legend_item_y = legend_y + index * 28
        svg.append(
            f'<line x1="{legend_x}" y1="{legend_item_y}" x2="{legend_x + 24}" y2="{legend_item_y}" stroke="{color}" stroke-width="3" stroke-linecap="round"/>'
        )
        svg.append(
            f'<circle cx="{legend_x + 12}" cy="{legend_item_y}" r="4.5" fill="{color}" stroke="#ffffff" stroke-width="1.5"/>'
        )
        svg.append(
            f'<text x="{legend_x + 36}" y="{legend_item_y + 4}" font-size="13" font-family="Arial, sans-serif" fill="#111827">{escape(str(item["label"]))}</text>'
        )

    svg.append("</svg>")
    path.write_text("".join(svg), encoding="utf-8")

# Orquestra o fluxo completo: ler CSV, resumir dados e gerar os SVGs.
# Como funciona: chama as funcoes auxiliares pela ordem certa e grava os ficheiros finais na pasta de saida.
# Vantagem: deixa o script pronto a correr de ponta a ponta. Desvantagem: concentra muita responsabilidade num bloco.
def main() -> None:
    args = parse_args()
    csv_path = Path(args.csv).resolve()
    out_dir = Path(args.out_dir).resolve()

    rows = parse_rows(csv_path)
    graphs_dir = out_dir / "graphs"

    graphs_dir.mkdir(parents=True, exist_ok=True)
    for graph_path in graphs_dir.glob("exercise_*.svg"):
        graph_path.unlink()

    generated_graphs = 0

    for exercise in EXERCISES:
        exercise_rows = [row for row in rows if row["mode"] == exercise["mode"]]
        if not exercise_rows:
            continue

        summary_rows = build_summary(exercise_rows, exercise)

        for metric, _ in CHART_SPECS:
            chart_series = build_chart_series(summary_rows, exercise, metric)
            graph_path = graphs_dir / f"exercise_{exercise['id']}_{metric}.svg"
            if chart_series:
                write_line_chart_svg(
                    graph_path,
                    f"{exercise['title']} - {metric_label(metric)}",
                    metric,
                    chart_series,
                )
                generated_graphs += 1

    if generated_graphs == 0:
        raise SystemExit("No exercise data found in the CSV.")
    if not args.quiet:
        print(f"[report] SVG charts: {graphs_dir}")


if __name__ == "__main__":
    main()
