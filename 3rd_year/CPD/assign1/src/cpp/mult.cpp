#include <stdio.h>
#include <iostream>
#include <iomanip>
#include <time.h>
#include <cstdlib>
#include <omp.h>

using namespace std;

// clock_t e o tipo usado para medir tempo de CPU com a funcao clock()
#define SYSTEMTIME clock_t

// =============================================================================
// OnMult - Multiplicacao classica com ordem de ciclos i-j-k
// =============================================================================
// Para cada elemento C[i,j], percorre a linha i de A e a coluna j de B.
//
// Problema de cache:
//   - A[i,k] e acedido sequencialmente (linha) -> bom, esta em cache
//   - B[k,j] e acedido por COLUNA (salta N posicoes a cada iteracao de k)
//            -> mau! provoca muitos cache misses porque as colunas nao sao
//               contiguas em memoria (row-major storage)
//
// Exemplo com N=4:
//   C[0,0] = A[0,0]*B[0,0] + A[0,1]*B[1,0] + A[0,2]*B[2,0] + A[0,3]*B[3,0]
//            B[0,0] esta em mem[0], B[1,0] em mem[4], B[2,0] em mem[8]...
//            -> stride N entre acessos = cache miss a cada passo
// =============================================================================
void OnMult(int m_ar, int m_br)
{
    SYSTEMTIME Time1, Time2;

    char st[100];
    double temp;
    int i, j, k;

    double *pha, *phb, *phc;

    // Aloca as tres matrizes como arrays 1D em row-major
    // Elemento [i][j] esta na posicao i*N + j
    pha = (double *)malloc((m_ar * m_ar) * sizeof(double));
    phb = (double *)malloc((m_ar * m_ar) * sizeof(double));
    phc = (double *)malloc((m_ar * m_ar) * sizeof(double));

    // Inicializa A com tudo a 1.0
    for(i = 0; i < m_ar; i++)
        for(j = 0; j < m_ar; j++)
            pha[i*m_ar + j] = 1.0;

    // Inicializa B com B[i][j] = i+1 (linha 0 -> 1, linha 1 -> 2, ...)
    for(i = 0; i < m_br; i++)
        for(j = 0; j < m_br; j++)
            phb[i*m_br + j] = (double)(i + 1);

    Time1 = clock();

    // Ordem i -> j -> k: para cada C[i,j], acumula o produto escalar
    for(i = 0; i < m_ar; i++)
    {
        for(j = 0; j < m_br; j++)
        {
            temp = 0;
            for(k = 0; k < m_ar; k++)
            {
                // A[i,k]: acesso sequencial na linha i (bom para cache)
                // B[k,j]: acesso por coluna, salta m_br doubles (mau para cache)
                temp += pha[i*m_ar + k] * phb[k*m_br + j];
            }
            phc[i*m_ar + j] = temp;
        }
    }

    Time2 = clock();
    snprintf(st, sizeof(st), "Time: %3.3f seconds\n",
         (double)(Time2 - Time1) / CLOCKS_PER_SEC);
    cout << st;

    // Imprime os primeiros 10 elementos da primeira linha de C para validacao
    cout << "Result matrix: " << endl;
    for(i = 0; i < 1; i++)
    {
        for(j = 0; j < min(10, m_br); j++)
            cout << phc[j] << " ";
    }
    cout << endl;

    free(pha);
    free(phb);
    free(phc);
}


// =============================================================================
// OnMultLine - Multiplicacao com ordem de ciclos i-k-j (linha a linha)
// =============================================================================
// Troca a ordem dos ciclos j e k face a versao classica.
// Em vez de calcular C[i,j] por completo antes de avancar,
// acumula a contribuicao de cada k para toda a linha i de C.
//
// Vantagem de cache:
//   - A[i,k] e lido uma vez por k e reutilizado para todos os j -> bom
//   - B[k,j]: percorre a linha k de B sequencialmente (j varia rapido) -> bom!
//   - C[i,j]: tambem percorre a linha i sequencialmente -> bom!
//
// Todos os acessos sao agora sequenciais em memoria (stride 1) ->
// muito menos cache misses do que a versao i-j-k.
//
// Exemplo com N=4:
//   Para i=0, k=0:
//     C[0,0] += A[0,0]*B[0,0]
//     C[0,1] += A[0,0]*B[0,1]   <- B[0,0], B[0,1], B[0,2], B[0,3] sao contiguos
//     C[0,2] += A[0,0]*B[0,2]
//     C[0,3] += A[0,0]*B[0,3]
// =============================================================================
void OnMultLine(int m_ar, int m_br)
{
    SYSTEMTIME Time1, Time2;

    char st[100];
    int i, j, k;

    double *pha, *phb, *phc;

    pha = (double *)malloc((m_ar * m_ar) * sizeof(double));
    phb = (double *)malloc((m_ar * m_ar) * sizeof(double));
    phc = (double *)malloc((m_ar * m_ar) * sizeof(double));

    for(i = 0; i < m_ar; i++)
        for(j = 0; j < m_ar; j++)
            pha[i*m_ar + j] = 1.0;

    for(i = 0; i < m_br; i++)
        for(j = 0; j < m_br; j++)
            phb[i*m_br + j] = (double)(i + 1);

    // C tem de ser inicializado a 0 porque a escrita e feita com +=
    for(i = 0; i < m_ar; i++)
        for(j = 0; j < m_br; j++)
            phc[i*m_ar + j] = 0.0;

    Time1 = clock();

    // Ordem i -> k -> j: para cada linha i de C, acumula contributo de cada k
    for(i = 0; i < m_ar; i++)
    {
        for(k = 0; k < m_ar; k++)
        {
            // pha[i*m_ar + k] e um escalar fixo para este k
            // O ciclo de j percorre as linhas i de C e k de B sequencialmente
            for(j = 0; j < m_br; j++)
            {
                // A[i,k]: escalar constante neste k (lido uma vez, reutilizado)
                // B[k,j]: sequencial na linha k -> cache friendly
                // C[i,j]: sequencial na linha i -> cache friendly
                phc[i*m_ar + j] += pha[i*m_ar + k] * phb[k*m_br + j];
            }
        }
    }

    Time2 = clock();
    snprintf(st, sizeof(st), "Time: %3.3f seconds\n",
         (double)(Time2 - Time1) / CLOCKS_PER_SEC);
    cout << st;

    cout << "Result matrix: " << endl;
    for(i = 0; i < 1; i++)
    {
        for(j = 0; j < min(10, m_br); j++)
            cout << phc[j] << " ";
    }
    cout << endl;

    free(pha);
    free(phb);
    free(phc);
}


// =============================================================================
// OnMultBlock - Multiplicacao por blocos (cache blocking / tiling)
// =============================================================================
// Divide as matrizes em submatrizes de tamanho bkSize x bkSize e processa
// bloco a bloco. O objetivo e que os dados do bloco atual caibam na cache
// L1/L2 e sejam reutilizados antes de serem expulsos.
//
// Estrutura de ciclos:
//   - Ciclos externos (ii, kk, jj): selecionam o bloco atual
//   - Ciclos internos (i, k, j):   executam a multiplicacao dentro do bloco
//
// Porque funciona melhor que OnMultLine para matrizes grandes:
//   - Quando N e muito grande, mesmo a linha i de C pode nao caber em cache
//   - Com blocos, forcamos a reutilizacao de dados pequenos antes de avancar
//   - O bloco ideal tem dimensao sqrt(cache_size / 3) (3 matrizes em jogo)
//
// Exemplo: com N=1024 e bkSize=128, trabalhamos 128x128 de cada vez
//   -> 128*128*8 bytes = 128KB por bloco, potencialmente em L2
// =============================================================================
void OnMultBlock(int m_ar, int m_br, int bkSize)
{
    SYSTEMTIME Time1, Time2;

    char st[100];
    int i, j, k, ii, jj, kk;

    double *pha, *phb, *phc;

    pha = (double *)malloc((m_ar * m_ar) * sizeof(double));
    phb = (double *)malloc((m_ar * m_ar) * sizeof(double));
    phc = (double *)malloc((m_ar * m_ar) * sizeof(double));

    for(i = 0; i < m_ar; i++)
        for(j = 0; j < m_ar; j++)
            pha[i*m_ar + j] = 1.0;

    for(i = 0; i < m_br; i++)
        for(j = 0; j < m_br; j++)
            phb[i*m_br + j] = (double)(i + 1);

    for(i = 0; i < m_ar; i++)
        for(j = 0; j < m_br; j++)
            phc[i*m_ar + j] = 0.0;

    Time1 = clock();

    // Ciclos externos: iteram de bloco em bloco com passo bkSize
    for(ii = 0; ii < m_ar; ii += bkSize)
    {
        for(kk = 0; kk < m_ar; kk += bkSize)
        {
            for(jj = 0; jj < m_br; jj += bkSize)
            {
                // Ciclos internos: multiplicacao dentro do bloco atual
                // min() garante que nao saimos dos limites no ultimo bloco
                for(i = ii; i < min(ii + bkSize, m_ar); i++)
                {
                    for(k = kk; k < min(kk + bkSize, m_ar); k++)
                    {
                        for(j = jj; j < min(jj + bkSize, m_br); j++)
                        {
                            // Mesma operacao que OnMultLine, mas restrita ao bloco
                            phc[i*m_ar + j] += pha[i*m_ar + k] * phb[k*m_br + j];
                        }
                    }
                }
            }
        }
    }

    Time2 = clock();
    snprintf(st, sizeof(st), "Time: %3.3f seconds\n",
         (double)(Time2 - Time1) / CLOCKS_PER_SEC);
    cout << st;

    cout << "Result matrix: " << endl;
    for(i = 0; i < 1; i++)
    {
        for(j = 0; j < min(10, m_br); j++)
            cout << phc[j] << " ";
    }
    cout << endl;

    free(pha);
    free(phb);
    free(phc);
}

// =============================================================================
// OnMultParallel - Versao classica i-j-k paralela com OpenMP
// =============================================================================
// Paraleliza o ciclo externo (i) da versao classica.
// Cada thread processa um subconjunto de linhas de C de forma independente.
//
// Como o trabalho e dividido:
//   - omp_set_num_threads(numThreads): define quantos threads usar
//   - #pragma omp parallel: cria a equipa de threads
//   - #pragma omp for: divide as iteracoes de i entre os threads
//     (thread 0 faz linhas 0..N/T-1, thread 1 faz N/T..2N/T-1, etc.)
//
// Variaveis private (cada thread tem a sua copia):
//   - j, k: indices do ciclo interno, nao partilhados entre threads
//   - temp: acumulador local, nao pode ser partilhado
//
// Problema que persiste: o padrao de acesso a B continua a ser por coluna
// (stride N) -> os threads partilham a mesma cache e competem pelos mesmos
// dados dispersos em memoria.
//
// Medicao de tempo com omp_get_wtime() em vez de clock() porque clock()
// mede tempo de CPU somado de todos os threads, nao tempo real (wall clock).
// =============================================================================
void OnMultParallel(int m_ar, int m_br, int numThreads)
{
    double Time1, Time2;

    char st[100];
    double temp;
    int i, j, k;

    double *pha, *phb, *phc;

    pha = (double *)malloc((m_ar * m_ar) * sizeof(double));
    phb = (double *)malloc((m_ar * m_ar) * sizeof(double));
    phc = (double *)malloc((m_ar * m_ar) * sizeof(double));

    for(i = 0; i < m_ar; i++)
        for(j = 0; j < m_ar; j++)
            pha[i*m_ar + j] = 1.0;

    for(i = 0; i < m_br; i++)
        for(j = 0; j < m_br; j++)
            phb[i*m_br + j] = (double)(i + 1);

    omp_set_num_threads(numThreads);
    Time1 = omp_get_wtime(); // wall clock, nao CPU time

    // Regiao paralela: todos os threads executam este bloco
    #pragma omp parallel private(j, k, temp)
    {
        // Divide o ciclo de i (linhas de C) entre os threads
        #pragma omp for
        for(i = 0; i < m_ar; i++)
        {
            for(j = 0; j < m_br; j++)
            {
                temp = 0;
                for(k = 0; k < m_ar; k++)
                {
                    // A[i,k]: sequencial (bom) | B[k,j]: por coluna (mau)
                    temp += pha[i*m_ar + k] * phb[k*m_br + j];
                }
                phc[i*m_ar + j] = temp;
            }
        }
        // Barreira implicita no fim do omp for: todos os threads sincronizam aqui
    }

    Time2 = omp_get_wtime();
    snprintf(st, sizeof(st), "Time: %3.3f seconds\n",
         Time2 - Time1);
    cout << st;

    cout << "Result matrix: " << endl;
    for(i = 0; i < 1; i++)
    {
        for(j = 0; j < min(10, m_br); j++)
            cout << phc[j] << " ";
    }
    cout << endl;

    free(pha);
    free(phb);
    free(phc);
}

// =============================================================================
// OnMultParallelAlternative - Paralelismo no ciclo interno k com reduction
// =============================================================================
// Estrategia diferente: em vez de dividir linhas de C entre threads,
// divide o calculo de CADA elemento C[i,j] entre threads usando reduction.
//
// Como funciona:
//   - i e j sao private: cada thread percorre todos os pares (i,j)
//   - Para cada C[i,j], o ciclo de k e dividido entre threads
//   - reduction(+ : temp): cada thread acumula a sua parte da soma em
//     variaveis locais; no fim do omp for, os valores sao somados
//
// Problema desta abordagem:
//   - Para cada par (i,j), todos os threads sincronizam no fim do omp for
//   - Isso acontece N*N vezes -> enorme overhead de sincronizacao
//   - O trabalho por iteracao (uma multiplicacao) e minimo face ao custo
//     de sincronizar threads
//   - Resultado: tipicamente muito mais lento que OnMultParallel
//
// #pragma omp single: apenas um thread executa aquele bloco (os outros esperam)
//   - usado para inicializar temp e escrever phc sem condicoes de corrida
// =============================================================================
void OnMultParallelAlternative(int m_ar, int m_br, int numThreads)
{
    double Time1, Time2;

    char st[100];
    double temp;
    int i, j, k;

    double *pha, *phb, *phc;

    pha = (double *)malloc((m_ar * m_ar) * sizeof(double));
    phb = (double *)malloc((m_ar * m_ar) * sizeof(double));
    phc = (double *)malloc((m_ar * m_ar) * sizeof(double));

    for(i = 0; i < m_ar; i++)
        for(j = 0; j < m_ar; j++)
            pha[i*m_ar + j] = 1.0;

    for(i = 0; i < m_br; i++)
        for(j = 0; j < m_br; j++)
            phb[i*m_br + j] = (double)(i + 1);

    omp_set_num_threads(numThreads);
    Time1 = omp_get_wtime();

    // i e j sao private: todos os threads percorrem todos os pares (i,j)
    #pragma omp parallel private(i, j)
    {
        for(i = 0; i < m_ar; i++)
        {
            for(j = 0; j < m_br; j++)
            {
                // Apenas um thread inicializa temp (barreira implicita depois)
                #pragma omp single
                temp = 0;

                // Divide o ciclo de k entre threads; cada thread soma a sua parte
                // No fim, reduction combina as somas parciais em temp
                #pragma omp for reduction(+ : temp)
                for(k = 0; k < m_ar; k++)
                {
                    temp += pha[i*m_ar + k] * phb[k*m_br + j];
                }

                // Apenas um thread escreve o resultado final em C
                #pragma omp single
                phc[i*m_ar + j] = temp;
                // -> N*N sincronizacoes = bottleneck enorme
            }
        }
    }

    Time2 = omp_get_wtime();
    snprintf(st, sizeof(st), "Time: %3.3f seconds\n",
         Time2 - Time1);
    cout << st;

    cout << "Result matrix: " << endl;
    for(i = 0; i < 1; i++)
    {
        for(j = 0; j < min(10, m_br); j++)
            cout << phc[j] << " ";
    }
    cout << endl;

    free(pha);
    free(phb);
    free(phc);
}

// =============================================================================
// OnMultLineParallel - Versao i-k-j paralela por linhas (melhor combinacao)
// =============================================================================
// Combina as duas melhores ideias: ordem i-k-j (boa localidade de cache)
// com paralelismo por linhas (omp for no ciclo de i).
//
// Porque e a melhor variante paralela:
//   1. Cache: todos os acessos a B e C sao sequenciais (stride 1)
//   2. Paralelismo: threads independentes, sem conflitos de escrita em C
//      (thread X so escreve nas linhas de C que lhe foram atribuidas)
//   3. Overhead minimo: so uma sincronizacao no fim do omp for
//
// Comparacao com OnMultParallel:
//   - Mesma estrutura de paralelismo (omp for em i)
//   - Mas acesso a B e agora por linha (muito melhor para cache)
//   -> Tipicamente a variante mais rapida a single-node
// =============================================================================
void OnMultLineParallel(int m_ar, int m_br, int numThreads)
{
    double Time1, Time2;

    char st[100];
    int i, j, k;

    double *pha, *phb, *phc;

    pha = (double *)malloc((m_ar * m_ar) * sizeof(double));
    phb = (double *)malloc((m_ar * m_ar) * sizeof(double));
    phc = (double *)malloc((m_ar * m_ar) * sizeof(double));

    for(i = 0; i < m_ar; i++)
        for(j = 0; j < m_ar; j++)
            pha[i*m_ar + j] = 1.0;

    for(i = 0; i < m_br; i++)
        for(j = 0; j < m_br; j++)
            phb[i*m_br + j] = (double)(i + 1);

    for(i = 0; i < m_ar; i++)
        for(j = 0; j < m_br; j++)
            phc[i*m_ar + j] = 0.0;

    omp_set_num_threads(numThreads);
    Time1 = omp_get_wtime();

    // k e j sao private: cada thread tem os seus proprios indices internos
    #pragma omp parallel private(k, j)
    {
        // Divide as linhas de C entre threads (uma so sincronizacao no fim)
        #pragma omp for
        for(i = 0; i < m_ar; i++)
        {
            for(k = 0; k < m_ar; k++)
            {
                for(j = 0; j < m_br; j++)
                {
                    // A[i,k]: escalar constante para este k
                    // B[k,j]: sequencial em memoria (linha k) -> cache hit
                    // C[i,j]: sequencial em memoria (linha i) -> cache hit
                    phc[i*m_ar + j] += pha[i*m_ar + k] * phb[k*m_br + j];
                }
            }
        }
    }

    Time2 = omp_get_wtime();
    snprintf(st, sizeof(st), "Time: %3.3f seconds\n",
         Time2 - Time1);
    cout << st;

    cout << "Result matrix: " << endl;
    for(i = 0; i < 1; i++)
    {
        for(j = 0; j < min(10, m_br); j++)
            cout << phc[j] << " ";
    }
    cout << endl;

    free(pha);
    free(phb);
    free(phc);
}

// =============================================================================
// OnMultLineParallelAlternative - i-k-j com omp for no ciclo interno de j
// =============================================================================
// Mesma ordem i-k-j mas o paralelismo esta no ciclo INTERNO (j) em vez de i.
//
// Como funciona:
//   - i e k sao private: todos os threads percorrem todos os pares (i,k)
//   - O ciclo de j e dividido entre threads para cada par (i,k)
//
// Problema:
//   - O omp for dentro de dois ciclos sem barreiras causa N*N sincronizacoes
//     implicitas (barreira no fim de cada omp for)
//   - Cada thread faz pouquissimo trabalho por iteracao antes de sincronizar
//   - O overhead de fork/join repete-se N*N vezes
//
// Comparacao com OnMultLineParallel:
//   - Mesma logica matematica, mesmos acessos a memoria
//   - Mas N*N vezes mais sincronizacoes -> muito mais lento
// =============================================================================
void OnMultLineParallelAlternative(int m_ar, int m_br, int numThreads)
{
    double Time1, Time2;

    char st[100];
    int i, j, k;

    double *pha, *phb, *phc;

    pha = (double *)malloc((m_ar * m_ar) * sizeof(double));
    phb = (double *)malloc((m_ar * m_ar) * sizeof(double));
    phc = (double *)malloc((m_ar * m_ar) * sizeof(double));

    for(i = 0; i < m_ar; i++)
        for(j = 0; j < m_ar; j++)
            pha[i*m_ar + j] = 1.0;

    for(i = 0; i < m_br; i++)
        for(j = 0; j < m_br; j++)
            phb[i*m_br + j] = (double)(i + 1);

    for(i = 0; i < m_ar; i++)
        for(j = 0; j < m_br; j++)
            phc[i*m_ar + j] = 0.0;

    omp_set_num_threads(numThreads);
    Time1 = omp_get_wtime();

    // i e k sao private: cada thread percorre todos os pares (i,k)
    #pragma omp parallel private(i, k)
    {
        for(i = 0; i < m_ar; i++)
        {
            for(k = 0; k < m_ar; k++)
            {
                // omp for no ciclo INTERNO: divide j entre threads
                // Barreira implicita no fim -> sincronizacao N*N vezes!
                #pragma omp for
                for(j = 0; j < m_br; j++)
                {
                    phc[i*m_ar + j] += pha[i*m_ar + k] * phb[k*m_br + j];
                }
            }
        }
    }

    Time2 = omp_get_wtime();
    snprintf(st, sizeof(st), "Time: %3.3f seconds\n",
         Time2 - Time1);
    cout << st;

    cout << "Result matrix: " << endl;
    for(i = 0; i < 1; i++)
    {
        for(j = 0; j < min(10, m_br); j++)
            cout << phc[j] << " ";
    }
    cout << endl;

    free(pha);
    free(phb);
    free(phc);
}

// =============================================================================
// OnMultLineParallelSimd - i-k-j com threads (omp for) + vetorizacao (simd)
// =============================================================================
// Adiciona uma segunda camada de paralelismo: SIMD (Single Instruction Multiple Data).
//
// Dois niveis de paralelismo:
//   1. Thread-level: omp for divide as linhas i entre threads (como OnMultLineParallel)
//   2. Data-level:  omp simd instrui o compilador a vetorizar o ciclo de j
//                   usando instrucoes AVX/SSE que processam varios doubles de uma vez
//
// Como funciona o SIMD:
//   - Um registo AVX-256 pode conter 4 doubles
//   - Em vez de um produto por ciclo, faz 4 produtos em paralelo no mesmo ciclo de CPU
//   - O compilador gera instrucoes como vmulpd e vaddpd
//
// Requisitos para o SIMD funcionar bem:
//   - Os dados devem ser contiguos em memoria (j percorre linha de B e C -> sim)
//   - Sem dependencias entre iteracoes de j (cada j e independente -> sim)
//   - Idealmente alinhados em memoria (malloc pode nao garantir, mas funciona)
//
// Na pratica: o ganho depende do compilador e das flags de otimizacao usadas.
// =============================================================================
void OnMultLineParallelSimd(int m_ar, int m_br, int numThreads)
{
    double Time1, Time2;

    char st[100];
    int i, j, k;

    double *pha, *phb, *phc;

    pha = (double *)malloc((m_ar * m_ar) * sizeof(double));
    phb = (double *)malloc((m_ar * m_ar) * sizeof(double));
    phc = (double *)malloc((m_ar * m_ar) * sizeof(double));

    for(i = 0; i < m_ar; i++)
        for(j = 0; j < m_ar; j++)
            pha[i*m_ar + j] = 1.0;

    for(i = 0; i < m_br; i++)
        for(j = 0; j < m_br; j++)
            phb[i*m_br + j] = (double)(i + 1);

    for(i = 0; i < m_ar; i++)
        for(j = 0; j < m_br; j++)
            phc[i*m_ar + j] = 0.0;

    omp_set_num_threads(numThreads);
    Time1 = omp_get_wtime();

    #pragma omp parallel private(k, j)
    {
        // Nivel 1: paralelismo de threads - divide linhas i entre threads
        #pragma omp for
        for(i = 0; i < m_ar; i++)
        {
            for(k = 0; k < m_ar; k++)
            {
                // Nivel 2: paralelismo SIMD - vetoriza o ciclo de j
                // O compilador processa multiplos j simultaneamente com AVX/SSE
                #pragma omp simd
                for(j = 0; j < m_br; j++)
                {
                    // Esta operacao sera executada em "chunks" de 4 doubles
                    // com instrucoes vetoriais (se o hardware suportar AVX-256)
                    phc[i*m_ar + j] += pha[i*m_ar + k] * phb[k*m_br + j];
                }
            }
        }
    }

    Time2 = omp_get_wtime();
    snprintf(st, sizeof(st), "Time: %3.3f seconds\n",
         Time2 - Time1);
    cout << st;

    cout << "Result matrix: " << endl;
    for(i = 0; i < 1; i++)
    {
        for(j = 0; j < min(10, m_br); j++)
            cout << phc[j] << " ";
    }
    cout << endl;

    free(pha);
    free(phb);
    free(phc);
}

// =============================================================================
// OnMultLineParallelCollapse2 - i-k-j com collapse(2) e operacoes atomicas
// =============================================================================
// Usa collapse(2) para fundir os ciclos i e k num unico espaco de iteracoes,
// aumentando o numero de iteracoes disponivel para distribuir entre threads.
//
// Porque se usa collapse(2):
//   - Com omp for so em i: N iteracoes para T threads
//   - Com collapse(2) em i e k: N*N iteracoes para T threads
//   - Mais iteracoes = melhor balanceamento de carga em alguns casos
//
// Problema critico - condicao de corrida em C:
//   - Dois threads com o mesmo i mas k diferente escrevem em phc[i*m_ar + j]
//   - Sem protecao: race condition -> resultados errados
//   - Com #pragma omp atomic: garante atomicidade da operacao += em C
//
// Custo das operacoes atomicas:
//   - Cada atomic forca sincronizacao no bus de memoria para aquela posicao
//   - Ha N*N*N operacoes atomicas no total (uma por iteracao do ciclo de j)
//   - Threads com o mesmo i bloqueiam-se mutuamente ao escrever na mesma linha
//   -> Overhead enorme: tipicamente mais lento que todas as outras versoes
// =============================================================================
void OnMultLineParallelCollapse2(int m_ar, int m_br, int numThreads)
{
    double Time1, Time2;

    char st[100];
    int i, j, k;

    double *pha, *phb, *phc;

    pha = (double *)malloc((m_ar * m_ar) * sizeof(double));
    phb = (double *)malloc((m_ar * m_ar) * sizeof(double));
    phc = (double *)malloc((m_ar * m_ar) * sizeof(double));

    for(i = 0; i < m_ar; i++)
        for(j = 0; j < m_ar; j++)
            pha[i*m_ar + j] = 1.0;

    for(i = 0; i < m_br; i++)
        for(j = 0; j < m_br; j++)
            phb[i*m_br + j] = (double)(i + 1);

    for(i = 0; i < m_ar; i++)
        for(j = 0; j < m_br; j++)
            phc[i*m_ar + j] = 0.0;

    omp_set_num_threads(numThreads);
    Time1 = omp_get_wtime();

    // j e private para evitar que threads partilhem o indice do ciclo interno
    #pragma omp parallel private(j)
    {
        // collapse(2): funde os ciclos i e k -> N*N iteracoes distribuidas
        // Threads podem ter o mesmo i (linhas de C partilhadas entre threads)
        #pragma omp for collapse(2)
        for(i = 0; i < m_ar; i++)
        {
            for(k = 0; k < m_ar; k++)
            {
                for(j = 0; j < m_br; j++)
                {
                    // atomic e obrigatorio porque threads com mesmo i e j diferente
                    // (mas k diferente) escrevem na mesma posicao de C
                    // Custo: N^3 operacoes atomicas = bottleneck severo
                    #pragma omp atomic
                    phc[i*m_ar + j] += pha[i*m_ar + k] * phb[k*m_br + j];
                }
            }
        }
    }

    Time2 = omp_get_wtime();
    snprintf(st, sizeof(st), "Time: %3.3f seconds\n",
         Time2 - Time1);
    cout << st;

    cout << "Result matrix: " << endl;
    for(i = 0; i < 1; i++)
    {
        for(j = 0; j < min(10, m_br); j++)
            cout << phc[j] << " ";
    }
    cout << endl;

    free(pha);
    free(phb);
    free(phc);
}

// =============================================================================
// main - Menu interativo para selecionar e testar cada variante
// =============================================================================
// Le a opcao do utilizador em loop, pede os parametros necessarios
// (dimensao, tamanho de bloco ou numero de threads) e chama a funcao.
// =============================================================================
int main(int argc, char *argv[])
{
    int lin, col, blockSize, numThreads;
    int op;

    do {
        cout << endl << "1. Multiplication" << endl;
        cout << "2. Line Multiplication" << endl;
        cout << "3. Block Multiplication" << endl;
        cout << "4. Parallel Multiplication" << endl;
        cout << "5. Parallel Multiplication - Alternative Solution" << endl;
        cout << "6. Parallel Line Multiplication" << endl;
        cout << "7. Parallel Line Multiplication - Alternative Solution" << endl;
        cout << "8. Parallel Line Multiplication SIMD" << endl;
        cout << "9. Parallel Line Multiplication Collapse(2)" << endl;
        cout << "0. Exit" << endl;
        cout << "Selection?: ";
        cin >> op;

        if (op == 0)
            break;

        // Assumimos matrizes quadradas: lin == col
        cout << "Dimensions: lins=cols ? ";
        cin >> lin;
        col = lin;

        switch (op) {
            case 1:
                OnMult(lin, col);
                break;
            case 2:
                OnMultLine(lin, col);
                break;
            case 3:
                cout << "Block Size? ";
                cin >> blockSize;
                OnMultBlock(lin, col, blockSize);
                break;
            case 4:
                cout << "Threads? ";
                cin >> numThreads;
                OnMultParallel(lin, col, numThreads);
                break;
            case 5:
                cout << "Threads? ";
                cin >> numThreads;
                OnMultParallelAlternative(lin, col, numThreads);
                break;
            case 6:
                cout << "Threads? ";
                cin >> numThreads;
                OnMultLineParallel(lin, col, numThreads);
                break;
            case 7:
                cout << "Threads? ";
                cin >> numThreads;
                OnMultLineParallelAlternative(lin, col, numThreads);
                break;
            case 8:
                cout << "Threads? ";
                cin >> numThreads;
                OnMultLineParallelSimd(lin, col, numThreads);
                break;
            case 9:
                cout << "Threads? ";
                cin >> numThreads;
                OnMultLineParallelCollapse2(lin, col, numThreads);
                break;
        }

    } while (op != 0);

    return 0;
}
