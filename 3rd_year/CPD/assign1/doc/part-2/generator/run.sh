#!/usr/bin/env bash
set -euo pipefail

# Script para correr tudo na Parte 1:
# - compila o mult.cpp com -O2
# - corre os tamanhos pedidos no enunciado
# - apanha tempo + GFlop/s + counters do perf quando disponivel
# - guarda uma linha por execucao em CSV (C++ e Java)

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
RUN_PART1=1
RUN_EX4=0
RUN_EX5=0
OUT_DIR=""

while [[ $# -gt 0 ]]; do
    case "$1" in
        --ex4)
            RUN_PART1=0
            RUN_EX4=1
            RUN_EX5=0
            shift
            ;;
        --ex5)
            RUN_PART1=0
            RUN_EX4=0
            RUN_EX5=1
            shift
            ;;
        --part1)
            RUN_PART1=1
            RUN_EX4=0
            RUN_EX5=0
            shift
            ;;
        --help|-h)
            echo "Usage: ./run.sh [--part1 | --ex4 | --ex5] [OUT_DIR]"
            exit 0
            ;;
        *)
            if [[ -z "$OUT_DIR" ]]; then
                OUT_DIR="$1"
                shift
            else
                echo "Error: unexpected argument '$1'"
                echo "Usage: ./run.sh [--part1 | --ex4 | --ex5] [OUT_DIR]"
                exit 1
            fi
            ;;
    esac
done

OUT_DIR="${OUT_DIR:-"$ROOT_DIR/results"}"
if [[ "$RUN_EX4" -eq 1 ]]; then
    CSV_FILE="$OUT_DIR/results_ex4.csv"
elif [[ "$RUN_EX5" -eq 1 ]]; then
    CSV_FILE="$OUT_DIR/results_ex5.csv"
else
    CSV_FILE="$OUT_DIR/results_part1.csv"
fi
RAW_DIR="$OUT_DIR/raw"
OS_NAME="$(uname -s)"
REPORT_SCRIPT="$ROOT_DIR/generate_reports.py"

# Variaveis "rapidas" para controlar comportamento sem mexer no codigo
REPEATS="${REPEATS:-1}"
BUILD_BIN="${BUILD_BIN:-1}"
KEEP_RAW="${KEEP_RAW:-1}"
RUN_JAVA="${RUN_JAVA:-1}"

# Tamanhos pedidos no trabalho
SMALL_SIZES="${SMALL_SIZES:-1024 1536 2048 2560 3072}"
LARGE_SIZES="${LARGE_SIZES:-4096 6144 8192 10240}"
BLOCK_SIZES="${BLOCK_SIZES:-128 256 512}"
EX4_THREADS="${EX4_THREADS:-4}"
EX5_SIZE="${EX5_SIZE:-8192}"
EX5_THREADS="${EX5_THREADS:-4 8 12 16 20 24}"

# Eventos base do perf (podem ser trocados via env)
EVENTS="${EVENTS:-cycles,instructions,cache-references,cache-misses,branches,branch-misses}"

# Confirmar dependencias antes de arrancar
if command -v perf >/dev/null 2>&1 && perf stat --no-big-num -x, true >/dev/null 2>&1; then
    PERF_AVAILABLE=1
else
    PERF_AVAILABLE=0
fi

if command -v g++ >/dev/null 2>&1; then
    CXX_BIN="g++"
elif command -v clang++ >/dev/null 2>&1; then
    CXX_BIN="clang++"
elif command -v c++ >/dev/null 2>&1; then
    CXX_BIN="c++"
else
    echo "Error: no C++ compiler found (tried g++, clang++, c++)."
    exit 1
fi

JAVA_AVAILABLE=0
if command -v javac >/dev/null 2>&1 && command -v java >/dev/null 2>&1 && \
   javac -version >/dev/null 2>&1 && java -version >/dev/null 2>&1; then
    JAVA_AVAILABLE=1
fi

if [[ "$PERF_AVAILABLE" -eq 1 ]]; then
    echo "[info] perf found, hardware counters will be collected."
else
    echo "[warn] perf unavailable on $OS_NAME. Vou correr sem hardware counters; CSV tera NA nessas colunas."
fi

# Criar pasta de output
mkdir -p "$OUT_DIR"
if [[ "$KEEP_RAW" -eq 1 ]]; then
    mkdir -p "$RAW_DIR"
fi

# Compilar so se estiver ativo
if [[ "$BUILD_BIN" -eq 1 ]]; then
    echo "[build] $CXX_BIN -O2 -fopenmp mult.cpp -o mult"
    "$CXX_BIN" -O2 -fopenmp "$ROOT_DIR/mult.cpp" -o "$ROOT_DIR/mult"
    if [[ "$RUN_JAVA" -eq 1 && "$JAVA_AVAILABLE" -eq 1 ]]; then
        echo "[build] javac mult.java"
        javac "$ROOT_DIR/mult.java"
    fi
fi

# Vai ao ficheiro do perf e tira o valor do counter que pedimos.
# Se nao encontrar, devolve NA.
counter_from_perf() {
    local perf_file="$1"
    local regex="$2"
    LC_ALL=C awk -F, -v pat="$regex" '
        $3 ~ pat {
            gsub(/^[[:space:]]+|[[:space:]]+$/, "", $1);
            found=1;
            total += $1;
        }
        END {
            if (!found) {
                print "NA"
            } else {
                printf "%.0f", total
            }
        }
    ' "$perf_file"
}

# Helper simples para validar numero
is_number() {
    [[ "$1" =~ ^[0-9]+([.][0-9]+)?$ ]]
}

# Tira o valor numerico do "Time:" mesmo quando o programa mistura prompt e output na mesma linha.
extract_time_s() {
    local run_file="$1"
    sed -nE 's/.*Time:[[:space:]]*([0-9]+([.][0-9]+)?).*/\1/p' "$run_file" | head -n 1
}

# Formula pedida: 2*n^3 / tempo / 1e9
compute_gflops() {
    local n="$1"
    local t="$2"
    LC_ALL=C awk -v n="$n" -v t="$t" '
        BEGIN {
            if (t <= 0) {
                print "NA";
            } else {
                printf "%.6f", (2*n*n*n)/(t*1e9);
            }
        }
    '
}

# IPC = instructions / cycles
compute_ipc() {
    local instructions="$1"
    local cycles="$2"
    LC_ALL=C awk -v i="$instructions" -v c="$cycles" '
        BEGIN {
            if (c <= 0) {
                print "NA";
            } else {
                printf "%.6f", i/c;
            }
        }
    '
}

# Corre um caso (mode, tamanho, block, threads, repeticao)
# e escreve uma linha no CSV final.
run_cpp_case() {
    local mode="$1"
    local n="$2"
    local block="$3"
    local threads="$4"
    local rep="$5"

    local base="m${mode}_n${n}_b${block}_t${threads}_r${rep}"
    local run_out
    local perf_out

    # Guardar logs em ficheiros (ou temporarios, se KEEP_RAW=0)
    if [[ "$KEEP_RAW" -eq 1 ]]; then
        run_out="$RAW_DIR/${base}.out"
        perf_out="$RAW_DIR/${base}.perf"
    else
        run_out="$(mktemp)"
        perf_out="$(mktemp)"
    fi

    # O programa e interativo, por isso enviamos o input com printf.
    # Se perf nao existir (ex.: macOS), corremos na mesma e deixamos os counters como NA.
    if [[ "$PERF_AVAILABLE" -eq 1 ]]; then
        if [[ "$mode" -eq 3 ]]; then
            printf "3\n%s\n%s\n0\n" "$n" "$block" | \
                perf stat --no-big-num -x, -e "$EVENTS" -- "$ROOT_DIR/mult" >"$run_out" 2>"$perf_out"
        elif [[ "$mode" -ge 4 && "$mode" -le 9 ]]; then
            printf "%s\n%s\n%s\n0\n" "$mode" "$n" "$threads" | \
                perf stat --no-big-num -x, -e "$EVENTS" -- "$ROOT_DIR/mult" >"$run_out" 2>"$perf_out"
        else
            printf "%s\n%s\n0\n" "$mode" "$n" | \
                perf stat --no-big-num -x, -e "$EVENTS" -- "$ROOT_DIR/mult" >"$run_out" 2>"$perf_out"
        fi
    else
        if [[ "$mode" -eq 3 ]]; then
            printf "3\n%s\n%s\n0\n" "$n" "$block" | "$ROOT_DIR/mult" >"$run_out" 2>"$perf_out"
        elif [[ "$mode" -ge 4 && "$mode" -le 9 ]]; then
            printf "%s\n%s\n%s\n0\n" "$mode" "$n" "$threads" | "$ROOT_DIR/mult" >"$run_out" 2>"$perf_out"
        else
            printf "%s\n%s\n0\n" "$mode" "$n" | "$ROOT_DIR/mult" >"$run_out" 2>"$perf_out"
        fi
    fi

    # Ir buscar o tempo que o programa imprime
    local time_s
    time_s="$(extract_time_s "$run_out")"
    if [[ -z "${time_s:-}" ]]; then
        time_s="NA"
    fi

    # Calcular GFlop/s so se o tempo vier valido
    local gflops="NA"
    if is_number "$time_s"; then
        gflops="$(compute_gflops "$n" "$time_s")"
    fi

    # Ler counters do perf, se existirem
    local cycles instructions cache_ref cache_miss branches branch_miss ipc
    if [[ "$PERF_AVAILABLE" -eq 1 ]]; then
        cycles="$(counter_from_perf "$perf_out" '(^|/)cycles(/|$|:)')"
        instructions="$(counter_from_perf "$perf_out" '(^|/)instructions(/|$|:)')"
        cache_ref="$(counter_from_perf "$perf_out" '(^|/)cache-references(/|$|:)')"
        cache_miss="$(counter_from_perf "$perf_out" '(^|/)cache-misses(/|$|:)')"
        branches="$(counter_from_perf "$perf_out" '(^|/)branches(/|$|:)')"
        branch_miss="$(counter_from_perf "$perf_out" '(^|/)branch-misses(/|$|:)')"
    else
        cycles="NA"
        instructions="NA"
        cache_ref="NA"
        cache_miss="NA"
        branches="NA"
        branch_miss="NA"
    fi

    # IPC final (se der para calcular)
    ipc="NA"
    if is_number "$instructions" && is_number "$cycles"; then
        ipc="$(compute_ipc "$instructions" "$cycles")"
    fi

    # Linha final no CSV
    echo "cpp,${mode},${n},${block},${threads},${rep},${time_s},${gflops},NA,NA,${cycles},${instructions},${ipc},${cache_ref},${cache_miss},${branches},${branch_miss}" >> "$CSV_FILE"

    # Limpar temporarios se nao for para guardar logs
    if [[ "$KEEP_RAW" -ne 1 ]]; then
        rm -f "$run_out" "$perf_out"
    fi
}

# Corre um caso de Java (sem perf, so tempo + GFlop/s)
run_java_case() {
    local mode="$1"
    local n="$2"
    local rep="$3"

    local base="java_m${mode}_n${n}_r${rep}"
    local run_out
    local err_out

    if [[ "$KEEP_RAW" -eq 1 ]]; then
        run_out="$RAW_DIR/${base}.out"
        err_out="$RAW_DIR/${base}.err"
    else
        run_out="$(mktemp)"
        err_out="$(mktemp)"
    fi

    printf "%s\n%s\n0\n" "$mode" "$n" | java -cp "$ROOT_DIR" mult >"$run_out" 2>"$err_out"

    local time_s
    time_s="$(extract_time_s "$run_out")"
    if [[ -z "${time_s:-}" ]]; then
        time_s="NA"
    fi

    local gflops="NA"
    if is_number "$time_s"; then
        gflops="$(compute_gflops "$n" "$time_s")"
    fi

    echo "java,${mode},${n},0,0,${rep},${time_s},${gflops},NA,NA,NA,NA,NA,NA,NA,NA,NA" >> "$CSV_FILE"

    if [[ "$KEEP_RAW" -ne 1 ]]; then
        rm -f "$run_out" "$err_out"
    fi
}

# Header do CSV
echo "lang,mode,n,block,threads,rep,time_s,gflops,speedup,efficiency,cycles,instructions,ipc,cache_references,cache_misses,branches,branch_misses" > "$CSV_FILE"

# Parte 1
if [[ "$RUN_PART1" -eq 1 ]]; then
    # Parte 1.1 e 1.2 (V1 e V2) nos tamanhos pequenos
    echo "[run] V1 and V2, sizes: $SMALL_SIZES"
    for n in $SMALL_SIZES; do
        for rep in $(seq 1 "$REPEATS"); do
            echo "  - mode=1 n=$n rep=$rep"
            run_cpp_case 1 "$n" 0 0 "$rep"
            echo "  - mode=2 n=$n rep=$rep"
            run_cpp_case 2 "$n" 0 0 "$rep"
        done
    done

    # Parte 1.2 extra em C++ (V2) nos tamanhos grandes
    echo "[run] V2 large sizes: $LARGE_SIZES"
    for n in $LARGE_SIZES; do
        for rep in $(seq 1 "$REPEATS"); do
            echo "  - mode=2 n=$n rep=$rep"
            run_cpp_case 2 "$n" 0 0 "$rep"
        done
    done

    # Parte 1.3 (V3 block) para cada block size
    echo "[run] V3 blocked, sizes: $LARGE_SIZES, blocks: $BLOCK_SIZES"
    for b in $BLOCK_SIZES; do
        for n in $LARGE_SIZES; do
            for rep in $(seq 1 "$REPEATS"); do
                echo "  - mode=3 n=$n block=$b rep=$rep"
                run_cpp_case 3 "$n" "$b" 0 "$rep"
            done
        done
    done

    # Parte Java (V1 e V2), so tamanhos pequenos
    if [[ "$RUN_JAVA" -eq 1 ]]; then
        if [[ "$JAVA_AVAILABLE" -eq 1 ]]; then
            echo "[run] Java V1 and V2, sizes: $SMALL_SIZES"
            for n in $SMALL_SIZES; do
                for rep in $(seq 1 "$REPEATS"); do
                    echo "  - java mode=1 n=$n rep=$rep"
                    run_java_case 1 "$n" "$rep"
                    echo "  - java mode=2 n=$n rep=$rep"
                    run_java_case 2 "$n" "$rep"
                done
            done
        else
            echo "[warn] RUN_JAVA=1 mas o runtime/toolchain Java nao esta disponivel. Vou saltar Java."
        fi
    fi
fi

# Parte 2.1 -> exercise_4
# V1 e V2 paralelos, com 4 threads, para os tamanhos pequenos.
# Recolhe tambem as referencias seriais para o mesmo conjunto de tamanhos.
if [[ "$RUN_EX4" -eq 1 ]]; then
    echo "[run] Exercise 4 serial refs, sizes: $SMALL_SIZES"
    for n in $SMALL_SIZES; do
        for rep in $(seq 1 "$REPEATS"); do
            echo "  - mode=1 n=$n rep=$rep"
            run_cpp_case 1 "$n" 0 0 "$rep"
            echo "  - mode=2 n=$n rep=$rep"
            run_cpp_case 2 "$n" 0 0 "$rep"
        done
    done

    echo "[run] Exercise 4 parallel, sizes: $SMALL_SIZES, threads: $EX4_THREADS"
    for n in $SMALL_SIZES; do
        for rep in $(seq 1 "$REPEATS"); do
            echo "  - mode=4 n=$n threads=$EX4_THREADS rep=$rep"
            run_cpp_case 4 "$n" 0 "$EX4_THREADS" "$rep"
            echo "  - mode=5 n=$n threads=$EX4_THREADS rep=$rep"
            run_cpp_case 5 "$n" 0 "$EX4_THREADS" "$rep"
            echo "  - mode=6 n=$n threads=$EX4_THREADS rep=$rep"
            run_cpp_case 6 "$n" 0 "$EX4_THREADS" "$rep"
            echo "  - mode=7 n=$n threads=$EX4_THREADS rep=$rep"
            run_cpp_case 7 "$n" 0 "$EX4_THREADS" "$rep"
        done
    done
fi

# Parte 2.2 -> exercise_5
# So V2, em n=8192, com varios numeros de threads.
# Recolhe tambem o resultado serial de mode=2 para o mesmo tamanho.
if [[ "$RUN_EX5" -eq 1 ]]; then
    echo "[run] Exercise 5 serial ref, size: $EX5_SIZE"
    for rep in $(seq 1 "$REPEATS"); do
        echo "  - mode=2 n=$EX5_SIZE rep=$rep"
        run_cpp_case 2 "$EX5_SIZE" 0 0 "$rep"
    done

    echo "[run] Exercise 5, size: $EX5_SIZE, threads: $EX5_THREADS"
    for threads in $EX5_THREADS; do
        for rep in $(seq 1 "$REPEATS"); do
            echo "  - mode=6 n=$EX5_SIZE threads=$threads rep=$rep"
            run_cpp_case 6 "$EX5_SIZE" 0 "$threads" "$rep"
            echo "  - mode=8 n=$EX5_SIZE threads=$threads rep=$rep"
            run_cpp_case 8 "$EX5_SIZE" 0 "$threads" "$rep"
            echo "  - mode=9 n=$EX5_SIZE threads=$threads rep=$rep"
            run_cpp_case 9 "$EX5_SIZE" 0 "$threads" "$rep"
        done
    done
fi

echo
if [[ -f "$REPORT_SCRIPT" ]]; then
    echo "[report] Generating per-exercise Excel files and charts"
    python3 "$REPORT_SCRIPT" --csv "$CSV_FILE" --out-dir "$OUT_DIR"
fi

echo
echo "Done."
echo "CSV: $CSV_FILE"
if [[ "$KEEP_RAW" -eq 1 ]]; then
    echo "Raw logs: $RAW_DIR"
fi
if [[ -f "$REPORT_SCRIPT" ]]; then
    echo "Excel files: $OUT_DIR/excel"
    echo "Charts: $OUT_DIR/graphs"
fi
