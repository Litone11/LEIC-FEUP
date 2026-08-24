#!/usr/bin/env python3
"""Generate per-exercise spreadsheets and charts from benchmark CSV data."""

from __future__ import annotations

import argparse
import csv
import math
import zipfile
from collections import defaultdict
from datetime import datetime, timezone
from pathlib import Path
from xml.sax.saxutils import escape

REQUIRED_HEADERS = [
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

NUMERIC_FIELDS = [
    "time_s",
    "gflops",
    "speedup",
    "efficiency",
    "cycles",
    "instructions",
    "ipc",
    "cache_references",
    "cache_misses",
    "branches",
    "branch_misses",
]

RAW_HEADERS = [
    "lang",
    "mode",
    "n",
    "block",
    "threads",
    "rep",
    "time_s",
    "gflops",
    "speedup",
    "efficiency",
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
METRIC_LABELS = {
    "time_s": "Execution Time (s)",
    "gflops": "Performance (GFlop/s)",
    "speedup": "Speedup",
    "efficiency": "Efficiency",
}
MODE_METADATA = {
    1: {"version": "V1", "variant": "serial", "label": "V1 Serial"},
    2: {"version": "V2", "variant": "serial", "label": "V2 Serial"},
    3: {"version": "V3", "variant": "block", "label": "Blocked"},
    4: {"version": "V1", "variant": "parallel", "label": "V1 Parallel"},
    5: {"version": "V1", "variant": "alternative", "label": "V1 Alternative"},
    6: {"version": "V2", "variant": "parallel", "label": "V2 Parallel"},
    7: {"version": "V2", "variant": "alternative", "label": "V2 Alternative"},
    8: {"version": "V2", "variant": "simd", "label": "V2 SIMD"},
    9: {"version": "V2", "variant": "collapse2", "label": "V2 Collapse(2)"},
}
MODE_ORDER = {1: 0, 4: 1, 5: 2, 2: 3, 6: 4, 7: 5, 8: 6, 9: 7, 3: 8}
SERIAL_REFERENCE_MODE = {4: 1, 5: 1, 6: 2, 7: 2, 8: 2, 9: 2}
PART1_CHART_SPECS = [
    ("time_s", METRIC_LABELS["time_s"]),
    ("gflops", METRIC_LABELS["gflops"]),
]

EXERCISES = [
    {
        "id": 1,
        "kind": "part1",
        "include_modes": {1},
        "title": "Exercise 1",
        "description": "Baseline matrix multiplication (mode 1).",
        "group_fields": ["lang", "n"],
        "series_field": "lang",
        "x_field": "n",
        "x_label": "Matrix size (n)",
        "chart_specs": PART1_CHART_SPECS,
    },
    {
        "id": 2,
        "kind": "part1",
        "include_modes": {2},
        "title": "Exercise 2",
        "description": "Line-oriented matrix multiplication (mode 2).",
        "group_fields": ["lang", "n"],
        "series_field": "lang",
        "x_field": "n",
        "x_label": "Matrix size (n)",
        "chart_specs": PART1_CHART_SPECS,
    },
    {
        "id": 3,
        "kind": "part1",
        "include_modes": {3},
        "title": "Exercise 3",
        "description": "Blocked matrix multiplication (mode 3).",
        "group_fields": ["block", "n"],
        "series_field": "block",
        "x_field": "n",
        "x_label": "Matrix size (n)",
        "chart_specs": PART1_CHART_SPECS,
    },
    {
        "id": 4,
        "kind": "part2_ex4",
        "include_modes": {1, 2, 4, 5, 6, 7},
        "title": "Exercise 4",
        "description": "Parallel V1 and V2 comparison with 4 threads.",
        "group_fields": ["mode", "mode_label", "n", "threads"],
        "series_field": "mode",
        "x_field": "n",
        "x_label": "Matrix size (n)",
        "chart_specs": [
            ("gflops", METRIC_LABELS["gflops"]),
            ("speedup", METRIC_LABELS["speedup"]),
            ("efficiency", METRIC_LABELS["efficiency"]),
        ],
    },
    {
        "id": 5,
        "kind": "part2_ex5",
        "include_modes": {2, 6, 8, 9},
        "fixed_n": 8192,
        "title": "Exercise 5",
        "description": "V2 scaling and directive comparison at n = 8192.",
        "group_fields": ["mode", "mode_label", "threads"],
        "series_field": "mode",
        "x_field": "threads",
        "x_label": "Threads",
        "chart_specs": [
            ("gflops", METRIC_LABELS["gflops"]),
            ("speedup", METRIC_LABELS["speedup"]),
        ],
        "chart_min_x": 1,
    },
]

# Faz o parsing dos argumentos da linha de comando para escolher CSV e pasta de saida.
# Como funciona: descreve os argumentos aceites, le a linha de comando e devolve os valores validados.
# Vantagem: explicita bem a interface do script. Desvantagem: e pouco flexivel fora desse formato.
def parse_args() -> argparse.Namespace:
    parser = argparse.ArgumentParser(
        description="Create per-exercise CSV/XLSX reports and SVG charts."
    )
    parser.add_argument("--csv", required=True, help="Input CSV file with all results.")
    parser.add_argument(
        "--out-dir",
        required=True,
        help="Results directory where excel/tables/graphs folders will be created.",
    )
    return parser.parse_args()

# Converte texto para float e usa None quando o valor nao existe.
# Como funciona: remove espacos, trata "NA" como vazio logico e converte o resto para numero.
# Vantagem: uniformiza a leitura numerica. Desvantagem: depende da convencao "NA".
def parse_float(value: str) -> float | None:
    text = value.strip()
    if not text or text == "NA":
        return None
    return float(text)

# Le o CSV, valida o schema esperado e converte campos para tipos uteis.
# Como funciona: verifica os cabecalhos, percorre o CSV e transforma cada linha num dicionario tipado.
# Vantagem: apanha erros de estrutura cedo. Desvantagem: guarda tudo em memoria.
def parse_rows(csv_path: Path) -> list[dict[str, object]]:
    with csv_path.open(newline="", encoding="utf-8") as handle:
        reader = csv.DictReader(handle)
        fieldnames = reader.fieldnames or []
        missing = [header for header in REQUIRED_HEADERS if header not in fieldnames]
        if missing:
            raise ValueError(f"Missing expected CSV columns: {', '.join(missing)}")

        rows = []
        for row in reader:
            parsed = {
                "lang": row["lang"].strip(),
                "mode": int(row["mode"]),
                "n": int(row["n"]),
                "block": int(row["block"]),
                "threads": int((row.get("threads") or "0").strip() or "0"),
                "rep": int(row["rep"]),
            }
            for field in NUMERIC_FIELDS:
                parsed[field] = parse_float(row.get(field, "NA"))
            rows.append(parsed)
    return rows

# Enriquece cada linha com metadados, speedup e eficiencia quando faltam no CSV.
# Como funciona: primeiro guarda tempos seriais de referencia e depois calcula valores derivados para cada linha.
# Vantagem: prepara dados prontos para relatorio. Desvantagem: assume referencias seriais especificas.
def enrich_rows(rows: list[dict[str, object]]) -> list[dict[str, object]]:
    refs: dict[tuple[int, int, int], float] = {}

    for row in rows:
        mode = int(row["mode"])
        time_s = row["time_s"]
        metadata = MODE_METADATA.get(mode, {})
        row["version"] = metadata.get("version", "NA")
        row["variant"] = metadata.get("variant", "NA")
        row["mode_label"] = metadata.get("label", f"Mode {mode}")

        if mode in (1, 2) and time_s is not None:
            refs[(mode, int(row["n"]), int(row["rep"]))] = float(time_s)

    for row in rows:
        mode = int(row["mode"])
        threads = int(row["threads"])
        time_s = row["time_s"]

        if row["speedup"] is None and mode in SERIAL_REFERENCE_MODE and time_s is not None:
            ref_mode = SERIAL_REFERENCE_MODE[mode]
            ref_time = refs.get((ref_mode, int(row["n"]), int(row["rep"])))
            if ref_time is not None and float(time_s) > 0:
                row["speedup"] = ref_time / float(time_s)

        if row["efficiency"] is None and row["speedup"] is not None and threads > 0:
            row["efficiency"] = float(row["speedup"]) / threads

    return rows

# Calcula a media aritmetica de uma lista de floats.
# Como funciona: soma todos os valores e divide pelo total, devolvendo None para listas vazias.
# Vantagem: simples e reutilizavel. Desvantagem: nao mostra dispersao dos resultados.
def average(values: list[float]) -> float | None:
    if not values:
        return None
    return sum(values) / len(values)

# Formata valores para CSV/XLSX, tratando casos invalidos de forma consistente.
# Como funciona: verifica o tipo do valor e decide se escreve numero, texto, "NA" ou valor arredondado.
# Vantagem: a saida fica homogenea. Desvantagem: arredonda e simplifica valores originais.
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

# Define a ordenacao das linhas brutas de cada exercicio.
# Como funciona: devolve uma tupla com prioridades diferentes conforme o exercicio e o tipo de dados.
# Vantagem: torna os ficheiros mais legiveis. Desvantagem: depende de prioridades hardcoded.
def raw_sort_key(exercise_id: int, row: dict[str, object]) -> tuple[int, ...]:
    if exercise_id in (1, 2):
        return (LANG_ORDER.get(str(row["lang"]), 99), int(row["n"]), int(row["rep"]))
    if exercise_id == 3:
        return (int(row["block"]), int(row["n"]), int(row["rep"]))
    if exercise_id == 4:
        return (
            MODE_ORDER.get(int(row["mode"]), 99),
            int(row["n"]),
            int(row["threads"]),
            int(row["rep"]),
        )
    if exercise_id == 5:
        return (
            MODE_ORDER.get(int(row["mode"]), 99),
            int(row["threads"]),
            int(row["rep"]),
        )
    return (int(row["mode"]), int(row["n"]), int(row["rep"]))

# Define a ordenacao das linhas agregadas no resumo.
# Como funciona: gera a chave de sort adequada para que o resumo fique numa ordem logica para leitura.
# Vantagem: produz tabelas estaveis. Desvantagem: precisa ser atualizada se surgirem novos modos.
def summary_sort_key(exercise_id: int, row: dict[str, object]) -> tuple[int, ...]:
    if exercise_id in (1, 2):
        return (LANG_ORDER.get(str(row["lang"]), 99), int(row["n"]))
    if exercise_id == 3:
        return (int(row["block"]), int(row["n"]))
    if exercise_id == 4:
        return (
            MODE_ORDER.get(int(row["mode"]), 99),
            int(row["n"]),
            int(row["threads"]),
        )
    if exercise_id == 5:
        return (MODE_ORDER.get(int(row["mode"]), 99), int(row["threads"]))
    return (int(row["n"]),)

# Decide se ha dados suficientes para gerar um determinado exercicio.
# Como funciona: observa os modos presentes no dataset e aplica regras para saber se cada exercicio faz sentido.
# Vantagem: evita relatorios vazios ou incoerentes. Desvantagem: as regras ficam especificas deste trabalho.
def should_generate_exercise(
    rows: list[dict[str, object]], exercise: dict[str, object]
) -> bool:
    modes = {int(row["mode"]) for row in rows}
    kind = str(exercise["kind"])

    if kind == "part1":
        return not any(mode >= 4 for mode in modes) and any(
            mode in modes for mode in exercise["include_modes"]
        )
    if kind == "part2_ex4":
        return any(mode in modes for mode in (4, 5, 7))
    if kind == "part2_ex5":
        return any(mode in modes for mode in (8, 9))
    return False

# Filtra apenas as linhas que pertencem ao exercicio atual.
# Como funciona: seleciona os modos permitidos e, quando preciso, aplica filtros adicionais como um n fixo.
# Vantagem: isola a selecao num ponto unico. Desvantagem: aplica filtros fixos, como n=8192 no exercicio 5.
def select_exercise_rows(
    rows: list[dict[str, object]], exercise: dict[str, object]
) -> list[dict[str, object]]:
    include_modes = set(exercise["include_modes"])
    selected = [row for row in rows if int(row["mode"]) in include_modes]

    if exercise["kind"] == "part2_ex5":
        fixed_n = int(exercise["fixed_n"])
        selected = [row for row in selected if int(row["n"]) == fixed_n]

    return selected

# Agrupa dados por chave logica e calcula medias para o resumo final.
# Como funciona: cria grupos com base nos campos do exercicio e calcula os campos medios para cada grupo.
# Vantagem: reduz volume e facilita comparacoes. Desvantagem: perde detalhe de cada repeticao.
def build_summary(
    rows: list[dict[str, object]], exercise: dict[str, object]
) -> tuple[list[str], list[dict[str, object]]]:
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

    headers = group_fields + ["samples"] + [f"avg_{field}" for field in NUMERIC_FIELDS]
    return headers, summary_rows

# Escreve uma tabela simples em CSV com os cabecalhos pedidos.
# Como funciona: abre o ficheiro, escreve a linha de cabecalhos e depois serializa cada linha na ordem pedida.
# Vantagem: reutilizavel para bruto e resumo. Desvantagem: nao inclui formatacao rica.
def write_csv(path: Path, headers: list[str], rows: list[dict[str, object]]) -> None:
    with path.open("w", newline="", encoding="utf-8") as handle:
        writer = csv.writer(handle)
        writer.writerow(headers)
        for row in rows:
            writer.writerow([format_output(row.get(header)) for header in headers])

# Converte um indice numerico para a letra da coluna equivalente no Excel.
# Como funciona: faz divisao sucessiva em base 26 para construir a referencia alfabetica.
# Vantagem: evita dependencia externa para referencias de celulas. Desvantagem: lida so com essa convencao.
def column_letter(index: int) -> str:
    label = ""
    current = index + 1
    while current > 0:
        current, remainder = divmod(current - 1, 26)
        label = chr(65 + remainder) + label
    return label

# Monta o XML de uma worksheet XLSX a partir de uma tabela 2D.
# Como funciona: calcula larguras, percorre linhas/celulas e concatena manualmente o XML da sheet.
# Vantagem: gera folhas Excel sem bibliotecas extra. Desvantagem: manter XML manual e propenso a erro.
def worksheet_xml(rows: list[list[object]]) -> str:
    column_widths = [0] * max((len(row) for row in rows), default=0)
    for row in rows:
        for index, value in enumerate(row):
            column_widths[index] = max(column_widths[index], len(str(value)))

    cols_xml = []
    for index, width in enumerate(column_widths, start=1):
        adjusted = min(max(width + 2, 10), 40)
        cols_xml.append(
            f'<col min="{index}" max="{index}" width="{adjusted}" customWidth="1"/>'
        )

    row_chunks = []
    for row_index, row in enumerate(rows, start=1):
        cell_chunks = []
        for col_index, value in enumerate(row):
            cell_ref = f"{column_letter(col_index)}{row_index}"
            if isinstance(value, (int, float)) and not isinstance(value, bool):
                cell_chunks.append(f'<c r="{cell_ref}"><v>{value}</v></c>')
            else:
                text = escape(str(value))
                cell_chunks.append(
                    f'<c r="{cell_ref}" t="inlineStr"><is><t xml:space="preserve">{text}</t></is></c>'
                )
        row_chunks.append(f'<row r="{row_index}">{"".join(cell_chunks)}</row>')

    end_col = column_letter(len(column_widths) - 1) if column_widths else "A"
    end_row = max(len(rows), 1)
    dimension = f"A1:{end_col}{end_row}"

    return (
        '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
        f"<dimension ref=\"{dimension}\"/>"
        "<sheetViews><sheetView workbookViewId=\"0\"/></sheetViews>"
        "<sheetFormatPr defaultRowHeight=\"15\"/>"
        f"<cols>{''.join(cols_xml)}</cols>"
        f"<sheetData>{''.join(row_chunks)}</sheetData>"
        "</worksheet>"
    )

# Empacota varias folhas e cria um ficheiro XLSX minimo mas funcional.
# Como funciona: gera os XMLs obrigatorios do formato XLSX e escreve tudo num zip com a estrutura correta.
# Vantagem: produz relatorios Excel autonomos. Desvantagem: codigo verboso e de manutencao delicada.
def write_xlsx(workbook_path: Path, sheets: list[tuple[str, list[list[object]]]]) -> None:
    timestamp = datetime.now(timezone.utc).replace(microsecond=0).isoformat().replace(
        "+00:00", "Z"
    )

    content_types = [
        '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>',
        '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">',
        '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>',
        '<Default Extension="xml" ContentType="application/xml"/>',
        '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>',
        '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>',
        '<Override PartName="/docProps/core.xml" ContentType="application/vnd.openxmlformats-package.core-properties+xml"/>',
        '<Override PartName="/docProps/app.xml" ContentType="application/vnd.openxmlformats-officedocument.extended-properties+xml"/>',
    ]
    for index in range(1, len(sheets) + 1):
        content_types.append(
            f'<Override PartName="/xl/worksheets/sheet{index}.xml" '
            'ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
        )
    content_types.append("</Types>")

    workbook_xml = [
        '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>',
        '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" '
        'xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">',
        "<sheets>",
    ]
    for index, (name, _) in enumerate(sheets, start=1):
        workbook_xml.append(
            f'<sheet name="{escape(name)}" sheetId="{index}" r:id="rId{index}"/>'
        )
    workbook_xml.append("</sheets></workbook>")

    workbook_rels = [
        '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>',
        '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">',
    ]
    for index in range(1, len(sheets) + 1):
        workbook_rels.append(
            f'<Relationship Id="rId{index}" '
            'Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" '
            f'Target="worksheets/sheet{index}.xml"/>'
        )
    workbook_rels.append(
        f'<Relationship Id="rId{len(sheets) + 1}" '
        'Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" '
        'Target="styles.xml"/>'
    )
    workbook_rels.append("</Relationships>")

    package_rels = (
        '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
        '<Relationship Id="rId1" '
        'Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" '
        'Target="xl/workbook.xml"/>'
        '<Relationship Id="rId2" '
        'Type="http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties" '
        'Target="docProps/core.xml"/>'
        '<Relationship Id="rId3" '
        'Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/extended-properties" '
        'Target="docProps/app.xml"/>'
        "</Relationships>"
    )

    styles_xml = (
        '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
        "<fonts count=\"1\"><font><sz val=\"11\"/><name val=\"Calibri\"/><family val=\"2\"/></font></fonts>"
        "<fills count=\"2\"><fill><patternFill patternType=\"none\"/></fill><fill><patternFill patternType=\"gray125\"/></fill></fills>"
        "<borders count=\"1\"><border><left/><right/><top/><bottom/><diagonal/></border></borders>"
        "<cellStyleXfs count=\"1\"><xf numFmtId=\"0\" fontId=\"0\" fillId=\"0\" borderId=\"0\"/></cellStyleXfs>"
        "<cellXfs count=\"1\"><xf numFmtId=\"0\" fontId=\"0\" fillId=\"0\" borderId=\"0\" xfId=\"0\"/></cellXfs>"
        "<cellStyles count=\"1\"><cellStyle name=\"Normal\" xfId=\"0\" builtinId=\"0\"/></cellStyles>"
        "</styleSheet>"
    )

    app_titles = "".join(f"<vt:lpstr>{escape(name)}</vt:lpstr>" for name, _ in sheets)
    app_xml = (
        '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        '<Properties xmlns="http://schemas.openxmlformats.org/officeDocument/2006/extended-properties" '
        'xmlns:vt="http://schemas.openxmlformats.org/officeDocument/2006/docPropsVTypes">'
        "<Application>Codex</Application>"
        "<HeadingPairs><vt:vector size=\"2\" baseType=\"variant\">"
        "<vt:variant><vt:lpstr>Worksheets</vt:lpstr></vt:variant>"
        f"<vt:variant><vt:i4>{len(sheets)}</vt:i4></vt:variant>"
        "</vt:vector></HeadingPairs>"
        f"<TitlesOfParts><vt:vector size=\"{len(sheets)}\" baseType=\"lpstr\">{app_titles}</vt:vector></TitlesOfParts>"
        "</Properties>"
    )

    core_xml = (
        '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        '<cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties" '
        'xmlns:dc="http://purl.org/dc/elements/1.1/" '
        'xmlns:dcterms="http://purl.org/dc/terms/" '
        'xmlns:dcmitype="http://purl.org/dc/dcmitype/" '
        'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">'
        "<dc:creator>Codex</dc:creator>"
        "<cp:lastModifiedBy>Codex</cp:lastModifiedBy>"
        f'<dcterms:created xsi:type="dcterms:W3CDTF">{timestamp}</dcterms:created>'
        f'<dcterms:modified xsi:type="dcterms:W3CDTF">{timestamp}</dcterms:modified>'
        "</cp:coreProperties>"
    )

    with zipfile.ZipFile(workbook_path, "w", compression=zipfile.ZIP_DEFLATED) as workbook:
        workbook.writestr("[Content_Types].xml", "".join(content_types))
        workbook.writestr("_rels/.rels", package_rels)
        workbook.writestr("docProps/app.xml", app_xml)
        workbook.writestr("docProps/core.xml", core_xml)
        workbook.writestr("xl/workbook.xml", "".join(workbook_xml))
        workbook.writestr("xl/_rels/workbook.xml.rels", "".join(workbook_rels))
        workbook.writestr("xl/styles.xml", styles_xml)
        for index, (_, rows) in enumerate(sheets, start=1):
            workbook.writestr(f"xl/worksheets/sheet{index}.xml", worksheet_xml(rows))

# Organiza pontos agregados em series prontas a desenhar nos graficos.
# Como funciona: separa os pontos por serie, filtra valores invalidos e ordena cada linha de dados.
# Vantagem: separa dados da fase de renderizacao. Desvantagem: descarta pontos incompletos.
def build_chart_series(
    summary_rows: list[dict[str, object]], exercise: dict[str, object], metric: str
) -> list[dict[str, object]]:
    series_field = str(exercise["series_field"])
    x_field = str(exercise["x_field"])
    min_x = int(exercise.get("chart_min_x", 0))
    series_points: dict[object, list[tuple[int, float]]] = defaultdict(list)

    for row in summary_rows:
        value = row.get(f"avg_{metric}")
        if value is None:
            continue
        x_value = int(row[x_field])
        if x_value < min_x:
            continue
        series_points[row[series_field]].append((x_value, float(value)))

    ordered_keys = sorted(
        series_points,
        key=lambda key: LANG_ORDER.get(str(key), 99)
        if series_field == "lang"
        else MODE_ORDER.get(int(key), 99)
        if series_field == "mode"
        else int(key),
    )

    series = []
    for key in ordered_keys:
        points = sorted(series_points[key], key=lambda point: point[0])
        if not points:
            continue
        label = LANG_LABELS.get(str(key), str(key))
        if series_field == "mode":
            label = MODE_METADATA.get(int(key), {}).get("label", f"Mode {key}")
        if series_field == "block":
            label = f"Block {key}"
        series.append({"label": label, "points": points})
    return series

# Traduz o nome interno da metrica para uma etiqueta amigavel.
# Como funciona: consulta o dicionario de etiquetas e devolve o texto correspondente a cada metrica.
# Vantagem: centraliza os textos apresentados. Desvantagem: exige atualizacao manual do dicionario.
def metric_label(metric: str) -> str:
    return METRIC_LABELS.get(metric, metric)

# Desenha um grafico SVG de linhas com eixos, legenda e varias series.
# Como funciona: calcula dimensoes e escalas, desenha eixos/linhas/pontos e grava o SVG final em disco.
# Vantagem: nao depende de matplotlib ou semelhantes. Desvantagem: qualquer ajuste visual exige editar XML manual.
def write_line_chart_svg(
    path: Path, title: str, metric: str, series: list[dict[str, object]], x_label: str
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

    # Converte o valor do eixo X para coordenadas no SVG.
    # Como funciona: interpola a posicao do valor entre os limites minimos e maximos do eixo horizontal.
    # Vantagem: concentra a escala horizontal. Desvantagem: trabalha apenas com escala linear.
    def scale_x(value: float) -> float:
        if x_max == x_min:
            return left + plot_width / 2
        return left + ((value - x_min) / (x_max - x_min)) * plot_width

    # Converte o valor do eixo Y para coordenadas no SVG.
    # Como funciona: calcula a altura relativa do valor e inverte o eixo para o sistema de coordenadas do SVG.
    # Vantagem: garante consistencia vertical. Desvantagem: nao suporta outros tipos de escala.
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
        f'<text x="{left + plot_width / 2}" y="{height - 24}" text-anchor="middle" font-size="14" font-family="Arial, sans-serif" fill="#111827">{escape(x_label)}</text>'
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

# Converte um conjunto de dicionarios numa tabela 2D pronta para Excel.
# Como funciona: cria a primeira linha com cabecalhos e converte cada dicionario numa linha ordenada.
# Vantagem: simplifica a criacao das sheets. Desvantagem: achata tipos e perde alguma semantica.
def rows_to_sheet(headers: list[str], rows: list[dict[str, object]]) -> list[list[object]]:
    # Ajusta cada valor para um formato seguro antes de entrar na sheet.
    # Como funciona: troca None/NaN por texto e arredonda floats antes de os inserir na tabela.
    # Vantagem: evita NaN/infinito no XLSX. Desvantagem: tambem arredonda os floats.
    def normalize(value: object) -> object:
        if value is None:
            return "NA"
        if isinstance(value, float):
            if not math.isfinite(value):
                return "NA"
            return round(value, 6)
        return value

    table = [headers]
    for row in rows:
        table.append([normalize(row.get(header)) for header in headers])
    return table

# Cria a folha de overview com caminhos e contagens importantes do exercicio.
# Como funciona: monta uma pequena tabela com metadados, ficheiros gerados e numero de linhas processadas.
# Vantagem: resume rapidamente o que foi gerado. Desvantagem: adiciona outra representacao para manter.
def build_overview_rows(
    exercise: dict[str, object],
    source_csv: Path,
    raw_csv: Path,
    summary_csv: Path,
    graph_paths: dict[str, Path],
    raw_count: int,
    summary_count: int,
) -> list[list[object]]:
    rows = [
        ["Field", "Value"],
        ["Exercise", exercise["title"]],
        ["Description", exercise["description"]],
        ["Source CSV", str(source_csv)],
        ["Raw CSV", str(raw_csv)],
        ["Summary CSV", str(summary_csv)],
        ["Raw rows", raw_count],
        ["Summary rows", summary_count],
    ]
    for metric, label in exercise["chart_specs"]:
        rows.append([f"{label} chart", str(graph_paths[metric])])
    return rows

# Coordena a geracao completa de CSVs, XLSX e graficos para cada exercicio.
# Como funciona: percorre os exercicios, filtra dados, gera resumo, escreve tabelas e cria os graficos e workbooks.
# Vantagem: automatiza todo o pipeline de relatorio. Desvantagem: concentra muitas etapas numa unica funcao.
def main() -> None:
    args = parse_args()
    csv_path = Path(args.csv).resolve()
    out_dir = Path(args.out_dir).resolve()

    rows = enrich_rows(parse_rows(csv_path))

    excel_dir = out_dir / "excel"
    tables_dir = out_dir / "tables"
    graphs_dir = out_dir / "graphs"

    excel_dir.mkdir(parents=True, exist_ok=True)
    tables_dir.mkdir(parents=True, exist_ok=True)
    graphs_dir.mkdir(parents=True, exist_ok=True)

    generated = []

    for exercise in EXERCISES:
        if not should_generate_exercise(rows, exercise):
            continue

        exercise_rows = select_exercise_rows(rows, exercise)
        exercise_rows.sort(key=lambda row: raw_sort_key(int(exercise["id"]), row))
        if not exercise_rows:
            continue

        summary_headers, summary_rows = build_summary(exercise_rows, exercise)

        raw_csv = tables_dir / f"exercise_{exercise['id']}_raw.csv"
        summary_csv = tables_dir / f"exercise_{exercise['id']}_summary.csv"
        workbook_path = excel_dir / f"exercise_{exercise['id']}.xlsx"

        write_csv(raw_csv, RAW_HEADERS, exercise_rows)
        write_csv(summary_csv, summary_headers, summary_rows)

        graph_paths: dict[str, Path] = {}
        for metric, _ in exercise["chart_specs"]:
            chart_series = build_chart_series(summary_rows, exercise, metric)
            graph_path = graphs_dir / f"exercise_{exercise['id']}_{metric}.svg"
            graph_paths[metric] = graph_path
            if chart_series:
                write_line_chart_svg(
                    graph_path,
                    f"{exercise['title']} - {metric_label(metric)}",
                    metric,
                    chart_series,
                    str(exercise["x_label"]),
                )

        workbook_sheets = [
            (
                "overview",
                build_overview_rows(
                    exercise,
                    csv_path,
                    raw_csv,
                    summary_csv,
                    graph_paths,
                    len(exercise_rows),
                    len(summary_rows),
                ),
            ),
            ("raw_data", rows_to_sheet(RAW_HEADERS, exercise_rows)),
            ("summary", rows_to_sheet(summary_headers, summary_rows)),
        ]
        write_xlsx(workbook_path, workbook_sheets)

        generated.append(workbook_path)

    if not generated:
        raise SystemExit("No exercise data found in the CSV.")

    print(f"[report] generated {len(generated)} Excel files in {excel_dir}")
    print(f"[report] CSV tables: {tables_dir}")
    print(f"[report] SVG charts: {graphs_dir}")


if __name__ == "__main__":
    main()
