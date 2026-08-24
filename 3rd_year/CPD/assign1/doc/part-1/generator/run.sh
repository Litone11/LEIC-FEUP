#!/usr/bin/env bash
set -euo pipefail

# Forcar ponto como separador decimal evita CSVs partidos em locales pt_PT.
export LC_NUMERIC=C

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
USER_HOME="$(getent passwd "$(id -un)" | cut -d: -f6)"
OS_NAME="$(uname -s)"
REPORT_SCRIPT="$ROOT_DIR/generate_results.py"
CPP_SRC="$ROOT_DIR/src/cpp/mult.cpp"
JAVA_SRC="$ROOT_DIR/src/java/mult.java"
BUILD_DIR="$ROOT_DIR/build"
CPP_BUILD_DIR="$BUILD_DIR/cpp"
JAVA_BUILD_DIR="$BUILD_DIR/java"
CPP_EXE="$CPP_BUILD_DIR/mult"
JAVA_STORE_ROOT="${JAVA_INSTALL_ROOT:-$USER_HOME/.local/share/cpd-proj1}"
DEFAULT_JAVA_HOME="$JAVA_STORE_ROOT/jdk"

REPEATS="${REPEATS:-1}"
BUILD_BIN="${BUILD_BIN:-1}"
RUN_CPP="${RUN_CPP:-1}"
RUN_JAVA="${RUN_JAVA:-1}"
RUN_REPORTS="${RUN_REPORTS:-1}"
REQUIRE_PERF="${REQUIRE_PERF:-0}"
REPORT_EVERY="${REPORT_EVERY:-1}"

SMALL_SIZES="${SMALL_SIZES:-1024 1536 2048 2560 3072}"
LARGE_SIZES="${LARGE_SIZES:-4096 6144 8192 10240}"
BLOCK_SIZES="${BLOCK_SIZES:-128 256 512}"
EVENTS="${EVENTS:-cycles,instructions,cache-references,cache-misses,branches,branch-misses}"

PERF_BIN="${PERF_BIN:-}"
PERF_STATUS="missing"
PERF_AVAILABLE=0
PERF_RUNTIME_WARNED=0

OUT_DIR=""
CSV_FILE=""
RESULT_ROWS=0

JAVA_HOME_CANDIDATE=""
JAVA_BIN=""
JAVAC_BIN=""
JAVA_AVAILABLE=0

usage() {
    cat <<EOF
Usage:
  $0                    Run benchmarks and generate charts
  $0 [out-dir]          Run benchmarks with a custom output directory
  $0 run [out-dir]      Same as above
  $0 setup-java         Install a local JDK outside the repository
  $0 enable-perf [--apply] [--persist]
                        Check or enable perf on Linux
  $0 help               Show this help
EOF
}

validate_report_every() {
    if ! [[ "$REPORT_EVERY" =~ ^[0-9]+$ ]] || [[ "$REPORT_EVERY" -lt 1 ]]; then
        echo "[error] REPORT_EVERY tem de ser um inteiro >= 1."
        exit 1
    fi
}

perf_try_run() {
    local perf_bin="$1"
    "$perf_bin" stat --no-big-num -x, true >/dev/null 2>&1
}

perf_detect_bin() {
    local candidate

    if [[ -n "$PERF_BIN" && -x "$PERF_BIN" ]]; then
        echo "$PERF_BIN"
        return 0
    fi

    while IFS= read -r candidate; do
        if [[ -x "$candidate" ]]; then
            echo "$candidate"
            return 0
        fi
    done < <(
        find /usr/lib -type f -name perf \
            \( -path '/usr/lib/linux-tools/*/perf' -o -path '/usr/lib/linux-hwe-*-tools-*/perf' \) \
            2>/dev/null | sort -Vr
    )

    if candidate="$(command -v perf 2>/dev/null)" && [[ -x "$candidate" ]]; then
        echo "$candidate"
        return 0
    fi

    return 1
}

detect_perf() {
    PERF_STATUS="missing"
    PERF_AVAILABLE=0

    if PERF_BIN="$(perf_detect_bin)"; then
        if perf_try_run "$PERF_BIN"; then
            PERF_AVAILABLE=1
            PERF_STATUS="ok"
        else
            PERF_STATUS="blocked"
        fi
    fi
}

detect_java_home() {
    local candidate

    if [[ -n "${JAVA_HOME:-}" && -x "${JAVA_HOME}/bin/java" && -x "${JAVA_HOME}/bin/javac" ]]; then
        echo "$JAVA_HOME"
        return 0
    fi

    for candidate in \
        "$DEFAULT_JAVA_HOME" \
        "$JAVA_STORE_ROOT"/jdk-* \
        "$USER_HOME/.local/jdks/jdk" \
        "$USER_HOME/.local/jdks"/jdk-*; do
        if [[ -x "$candidate/bin/java" && -x "$candidate/bin/javac" ]]; then
            echo "$candidate"
            return 0
        fi
    done

    return 1
}

detect_java() {
    JAVA_HOME_CANDIDATE=""
    JAVA_BIN=""
    JAVAC_BIN=""
    JAVA_AVAILABLE=0

    if JAVA_HOME_CANDIDATE="$(detect_java_home 2>/dev/null)"; then
        export JAVA_HOME="$JAVA_HOME_CANDIDATE"
        export PATH="$JAVA_HOME/bin:$PATH"
        JAVA_BIN="$JAVA_HOME/bin/java"
        JAVAC_BIN="$JAVA_HOME/bin/javac"
    elif command -v java >/dev/null 2>&1 && command -v javac >/dev/null 2>&1; then
        JAVA_BIN="$(command -v java)"
        JAVAC_BIN="$(command -v javac)"
    fi

    if [[ -n "$JAVA_BIN" && -n "$JAVAC_BIN" ]] && \
       "$JAVAC_BIN" -version >/dev/null 2>&1 && "$JAVA_BIN" -version >/dev/null 2>&1; then
        JAVA_AVAILABLE=1
    fi
}

print_runtime_info() {
    detect_perf
    detect_java

    if [[ "$PERF_AVAILABLE" -eq 1 ]]; then
        echo "[info] perf found at $PERF_BIN, hardware counters will be collected."
    else
        if [[ "$PERF_STATUS" == "blocked" ]]; then
            echo "[warn] perf found at $PERF_BIN but cannot collect counters with the current permissions/kernel settings."
            echo "[warn] Vou correr sem hardware counters; CSV tera NA nessas colunas."
            echo "[hint] Para desbloquear o perf neste Linux, corre $0 enable-perf --apply --persist"
        else
            echo "[warn] perf not found on $OS_NAME. Vou correr sem hardware counters; CSV tera NA nessas colunas."
        fi
    fi

    if [[ "$REQUIRE_PERF" -eq 1 && "$PERF_AVAILABLE" -ne 1 ]]; then
        echo "[error] REQUIRE_PERF=1 mas o perf nao esta utilizavel."
        if [[ "$PERF_STATUS" == "blocked" ]]; then
            echo "[error] Corre primeiro: $0 enable-perf --apply --persist"
        fi
        exit 2
    fi

    if [[ "$JAVA_AVAILABLE" -eq 1 ]]; then
        echo "[info] Java found at $JAVA_BIN"
    else
        echo "[warn] Java toolchain not found. Podes instalar um JDK local com $0 setup-java"
    fi
}

setup_java_local() {
    local arch archive tmp_dir

    arch="$(uname -m)"
    case "$arch" in
        x86_64) arch="x64" ;;
        aarch64|arm64) arch="aarch64" ;;
        *)
            echo "[error] Unsupported architecture: $arch"
            exit 1
            ;;
    esac

    local jdk_url="${JDK_URL:-https://api.adoptium.net/v3/binary/latest/21/ga/linux/${arch}/jdk/hotspot/normal/eclipse}"
    archive="$JAVA_STORE_ROOT/jdk.tar.gz"
    tmp_dir="$JAVA_STORE_ROOT/jdk.tmp"

    mkdir -p "$JAVA_STORE_ROOT"
    rm -rf "$tmp_dir"
    mkdir -p "$tmp_dir"

    echo "[download] $jdk_url"
    wget -O "$archive" "$jdk_url"

    echo "[extract] $archive"
    tar -xzf "$archive" -C "$tmp_dir" --strip-components=1

    rm -rf "$DEFAULT_JAVA_HOME"
    mv "$tmp_dir" "$DEFAULT_JAVA_HOME"
    rm -f "$archive"

    echo "[done] Local JDK installed in $DEFAULT_JAVA_HOME"
    echo "[hint] run.sh will detect it automatically."
}

enable_perf_linux() {
    local apply=0
    local persist=0
    local arg

    detect_perf

    for arg in "$@"; do
        case "$arg" in
            --apply) apply=1 ;;
            --persist) persist=1 ;;
            *)
                echo "[error] Unknown option: $arg"
                echo "Usage: $0 enable-perf [--apply] [--persist]"
                exit 1
                ;;
        esac
    done

    echo "[info] Current perf_event_paranoid: $(cat /proc/sys/kernel/perf_event_paranoid)"
    echo "[info] Testing perf binary: ${PERF_BIN:-/usr/bin/perf}"

    if [[ "$PERF_AVAILABLE" -eq 1 ]]; then
        echo "[ok] perf is already usable."
        return 0
    fi

    if [[ "$apply" -eq 1 ]]; then
        echo "[apply] Setting kernel.perf_event_paranoid=1"
        sudo sysctl -w kernel.perf_event_paranoid=1
    fi

    if [[ "$persist" -eq 1 ]]; then
        echo "[persist] Writing /etc/sysctl.d/99-perf.conf"
        echo 'kernel.perf_event_paranoid=1' | sudo tee /etc/sysctl.d/99-perf.conf >/dev/null
        sudo sysctl --system >/dev/null
    fi

    detect_perf
    if [[ "$apply" -eq 1 || "$persist" -eq 1 ]]; then
        echo "[verify] Current perf_event_paranoid: $(cat /proc/sys/kernel/perf_event_paranoid)"
        if [[ "$PERF_AVAILABLE" -eq 1 ]]; then
            echo "[ok] perf is now usable."
            return 0
        fi
        echo "[warn] perf is still blocked after applying the changes."
    fi

    cat <<EOF
[action] To unlock perf for your user, run:

$0 enable-perf --apply --persist

[optional] To make it persistent:

echo 'kernel.perf_event_paranoid=1' | sudo tee /etc/sysctl.d/99-perf.conf
sudo sysctl --system

[verify] Then test with:

${PERF_BIN:-/usr/bin/perf} stat -e task-clock --no-big-num -x, true
EOF
}

counter_from_perf() {
    local perf_file="$1"
    local regex="$2"
    awk -F, -v pat="$regex" '
        $3 ~ pat {
            gsub(/^[[:space:]]+|[[:space:]]+$/, "", $1);
            if ($1 ~ /^[0-9]+([.][0-9]+)?$/) {
                print $1;
            } else {
                print "NA";
            }
            found=1;
            exit
        }
        END {
            if (!found) print "NA"
        }
    ' "$perf_file"
}

is_number() {
    [[ "$1" =~ ^[0-9]+([.][0-9]+)?$ ]]
}

extract_time_s() {
    local run_file="$1"
    sed -nE 's/.*Time:[[:space:]]*([0-9]+([.][0-9]+)?).*/\1/p' "$run_file" | head -n 1
}

compute_gflops() {
    local n="$1"
    local t="$2"
    awk -v n="$n" -v t="$t" '
        BEGIN {
            if (t <= 0) {
                print "NA";
            } else {
                printf "%.6f", (2*n*n*n)/(t*1e9);
            }
        }
    '
}

compute_ipc() {
    local instructions="$1"
    local cycles="$2"
    awk -v i="$instructions" -v c="$cycles" '
        BEGIN {
            if (c <= 0) {
                print "NA";
            } else {
                printf "%.6f", i/c;
            }
        }
    '
}

generate_charts() {
    local quiet="${1:-0}"

    if [[ "$RUN_REPORTS" -ne 1 || ! -f "$REPORT_SCRIPT" ]]; then
        return 0
    fi

    if [[ "$quiet" -eq 1 ]]; then
        python3 "$REPORT_SCRIPT" --csv "$CSV_FILE" --out-dir "$OUT_DIR" --quiet
    else
        python3 "$REPORT_SCRIPT" --csv "$CSV_FILE" --out-dir "$OUT_DIR"
    fi
}

maybe_generate_charts() {
    ((RESULT_ROWS+=1))

    if (( RESULT_ROWS % REPORT_EVERY == 0 )); then
        generate_charts 1
    fi
}

run_cpp_program() {
    local mode="$1"
    local n="$2"
    local block="$3"
    local run_out="$4"
    local err_out="$5"

    if [[ "$mode" -eq 3 ]]; then
        printf "3\n%s\n%s\n0\n" "$n" "$block" | "$CPP_EXE" >"$run_out" 2>"$err_out"
    else
        printf "%s\n%s\n0\n" "$mode" "$n" | "$CPP_EXE" >"$run_out" 2>"$err_out"
    fi
}

run_cpp_program_with_perf() {
    local mode="$1"
    local n="$2"
    local block="$3"
    local run_out="$4"
    local perf_out="$5"

    if [[ "$mode" -eq 3 ]]; then
        printf "3\n%s\n%s\n0\n" "$n" "$block" | \
            "$PERF_BIN" stat --no-big-num -x, -e "$EVENTS" -- "$CPP_EXE" >"$run_out" 2>"$perf_out"
    else
        printf "%s\n%s\n0\n" "$mode" "$n" | \
            "$PERF_BIN" stat --no-big-num -x, -e "$EVENTS" -- "$CPP_EXE" >"$run_out" 2>"$perf_out"
    fi
}

run_cpp_case() {
    local mode="$1"
    local n="$2"
    local block="$3"
    local rep="$4"
    local run_out perf_out

    run_out="$(mktemp)"
    perf_out="$(mktemp)"

    local perf_used=0
    if [[ "$PERF_AVAILABLE" -eq 1 ]]; then
        if run_cpp_program_with_perf "$mode" "$n" "$block" "$run_out" "$perf_out"; then
            perf_used=1
        else
            if [[ "$PERF_RUNTIME_WARNED" -eq 0 ]]; then
                echo "[warn] perf falhou durante a recolha. Vou continuar sem hardware counters."
                PERF_RUNTIME_WARNED=1
            fi
            PERF_AVAILABLE=0
            run_cpp_program "$mode" "$n" "$block" "$run_out" "$perf_out"
        fi
    else
        run_cpp_program "$mode" "$n" "$block" "$run_out" "$perf_out"
    fi

    local time_s
    time_s="$(extract_time_s "$run_out")"
    if [[ -z "${time_s:-}" ]]; then
        time_s="NA"
    fi

    local gflops="NA"
    if is_number "$time_s"; then
        gflops="$(compute_gflops "$n" "$time_s")"
    fi

    local cycles instructions cache_ref cache_miss branches branch_miss ipc
    if [[ "$perf_used" -eq 1 ]]; then
        cycles="$(counter_from_perf "$perf_out" '^cycles($|:)')"
        instructions="$(counter_from_perf "$perf_out" '^instructions($|:)')"
        cache_ref="$(counter_from_perf "$perf_out" '^cache-references($|:)')"
        cache_miss="$(counter_from_perf "$perf_out" '^cache-misses($|:)')"
        branches="$(counter_from_perf "$perf_out" '^branches($|:)')"
        branch_miss="$(counter_from_perf "$perf_out" '^branch-misses($|:)')"
    else
        cycles="NA"
        instructions="NA"
        cache_ref="NA"
        cache_miss="NA"
        branches="NA"
        branch_miss="NA"
    fi

    ipc="NA"
    if is_number "$instructions" && is_number "$cycles"; then
        ipc="$(compute_ipc "$instructions" "$cycles")"
    fi

    echo "cpp,${mode},${n},${block},${rep},${time_s},${gflops},${cycles},${instructions},${ipc},${cache_ref},${cache_miss},${branches},${branch_miss}" >> "$CSV_FILE"
    maybe_generate_charts
    echo "    -> time=${time_s}s gflops=${gflops}"
    if [[ "$perf_used" -eq 1 ]]; then
        echo "    -> perf cycles=${cycles} instructions=${instructions} ipc=${ipc} cache-misses=${cache_miss} branches=${branches}"
    fi

    rm -f "$run_out" "$perf_out"
}

run_java_case() {
    local mode="$1"
    local n="$2"
    local rep="$3"
    local run_out err_out

    run_out="$(mktemp)"
    err_out="$(mktemp)"

    printf "%s\n%s\n0\n" "$mode" "$n" | "$JAVA_BIN" -cp "$JAVA_BUILD_DIR" mult >"$run_out" 2>"$err_out"

    local time_s
    time_s="$(extract_time_s "$run_out")"
    if [[ -z "${time_s:-}" ]]; then
        time_s="NA"
    fi

    local gflops="NA"
    if is_number "$time_s"; then
        gflops="$(compute_gflops "$n" "$time_s")"
    fi

    echo "java,${mode},${n},0,${rep},${time_s},${gflops},NA,NA,NA,NA,NA,NA,NA" >> "$CSV_FILE"
    maybe_generate_charts
    echo "    -> java time=${time_s}s gflops=${gflops}"

    rm -f "$run_out" "$err_out"
}

run_benchmarks() {
    local out_dir_arg="${1:-}"

    OUT_DIR="${out_dir_arg:-"$ROOT_DIR/results"}"
    CSV_FILE="$OUT_DIR/results.csv"
    RESULT_ROWS=0

    print_runtime_info
    validate_report_every

    local cxx_bin
    if command -v g++ >/dev/null 2>&1; then
        cxx_bin="g++"
    elif command -v clang++ >/dev/null 2>&1; then
        cxx_bin="clang++"
    elif command -v c++ >/dev/null 2>&1; then
        cxx_bin="c++"
    else
        echo "Error: no C++ compiler found (tried g++, clang++, c++)."
        exit 1
    fi

    mkdir -p "$OUT_DIR" "$CPP_BUILD_DIR" "$JAVA_BUILD_DIR"
    rm -f "$CSV_FILE"
    rm -rf "$OUT_DIR/raw" "$OUT_DIR/tables" "$OUT_DIR/excel" "$OUT_DIR/graphs"

    if [[ "$BUILD_BIN" -eq 1 ]]; then
        if [[ "$RUN_CPP" -eq 1 ]]; then
            echo "[build] $cxx_bin -O2 src/cpp/mult.cpp -o build/cpp/mult"
            "$cxx_bin" -O2 "$CPP_SRC" -o "$CPP_EXE"
        fi
        if [[ "$RUN_JAVA" -eq 1 && "$JAVA_AVAILABLE" -eq 1 ]]; then
            echo "[build] $JAVAC_BIN -d build/java src/java/mult.java"
            "$JAVAC_BIN" -d "$JAVA_BUILD_DIR" "$JAVA_SRC"
        fi
    fi

    echo "lang,mode,n,block,rep,time_s,gflops,cycles,instructions,ipc,cache_references,cache_misses,branches,branch_misses" > "$CSV_FILE"
    if [[ "$RUN_REPORTS" -eq 1 && -f "$REPORT_SCRIPT" ]]; then
        echo "[report] Live SVG charts enabled (every $REPORT_EVERY measurement(s))"
    fi

    if [[ "$RUN_CPP" -eq 1 ]]; then
        echo "[run] V1 and V2, sizes: $SMALL_SIZES"
        for n in $SMALL_SIZES; do
            for rep in $(seq 1 "$REPEATS"); do
                echo "  - mode=1 n=$n rep=$rep"
                run_cpp_case 1 "$n" 0 "$rep"
                echo "  - mode=2 n=$n rep=$rep"
                run_cpp_case 2 "$n" 0 "$rep"
            done
        done

        echo "[run] V2 large sizes: $LARGE_SIZES"
        for n in $LARGE_SIZES; do
            for rep in $(seq 1 "$REPEATS"); do
                echo "  - mode=2 n=$n rep=$rep"
                run_cpp_case 2 "$n" 0 "$rep"
            done
        done

        echo "[run] V3 blocked, sizes: $LARGE_SIZES, blocks: $BLOCK_SIZES"
        for b in $BLOCK_SIZES; do
            for n in $LARGE_SIZES; do
                for rep in $(seq 1 "$REPEATS"); do
                    echo "  - mode=3 n=$n block=$b rep=$rep"
                    run_cpp_case 3 "$n" "$b" "$rep"
                done
            done
        done
    else
        echo "[skip] RUN_CPP=0, skipping C++ benchmark collection."
    fi

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

    echo
    if [[ "$RUN_REPORTS" -eq 1 && -f "$REPORT_SCRIPT" ]]; then
        echo "[report] Generating final SVG charts"
        generate_charts 0
    fi

    echo
    echo "Done."
    echo "CSV: $CSV_FILE"
    if [[ "$RUN_REPORTS" -eq 1 && -f "$REPORT_SCRIPT" ]]; then
        echo "Charts: $OUT_DIR/graphs"
    fi
}

main() {
    local cmd="${1:-}"

    case "$cmd" in
        help|-h|--help)
            usage
            ;;
        setup-java)
            shift
            setup_java_local "$@"
            ;;
        enable-perf)
            shift
            enable_perf_linux "$@"
            ;;
        run)
            shift
            run_benchmarks "$@"
            ;;
        "")
            run_benchmarks
            ;;
        *)
            run_benchmarks "$@"
            ;;
    esac
}

main "$@"
