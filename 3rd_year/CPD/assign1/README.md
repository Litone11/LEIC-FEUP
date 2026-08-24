# CPD Project 1

Ficheiros principais:


- `src/cpp/mult.cpp` - implementação C++ das versões seriais e paralelas
- `src/java/mult.java` - implementação Java das versões seriais 1 e 2
- `results/part*/generator/run.sh` - script de recolha de resultados
- `results/part*/generator/generate_reports.py` - geração de tabelas e gráficos

## Compilar manualmente

```bash
g++ -O2 -fopenmp mult.cpp -o mult
javac mult.java
```

## Executar manualmente

- C++: `./mult`
- Java: `java mult`

Menus do `mult.cpp`:

- `1` - Multiplication
- `2` - Line Multiplication
- `3` - Block Multiplication
- `4` - Parallel Multiplication
- `5` - Parallel Multiplication - Alternative Solution
- `6` - Parallel Line Multiplication
- `7` - Parallel Line Multiplication - Alternative Solution
- `8` - Parallel Line Multiplication SIMD
- `9` - Parallel Line Multiplication Collapse(2)

## Recolha automática

Parte 1:

```bash
./run.sh
```

Parte 2:

```bash
./run.sh --ex4 ou --ex5
```

O script:

- compila `mult.cpp` com `-O2 -fopenmp`
- compila `mult.java`
- corre os casos pedidos
- recolhe tempo, GFlop/s e counters do `perf`
- gera tabelas e gráficos


## Outputs gerados

Parte 1:

Tabelas:

- `results/part-1/tables/results.csv`

Gráficos:

- `results/part-1/graphs/exercise_1_time_s.svg`
- `results/part-1/graphs/exercise_1_gflops.svg`
- `results/part-1/graphs/exercise_2_time_s.svg`
- `results/part-1/graphs/exercise_2_gflops.svg`
- `results/part-1/graphs/exercise_3_time_s.svg`
- `results/part-1/graphs/exercise_3_gflops.svg`

Parte 2:

Tabelas:

- `results/part-2/tables/exercise_4_raw.csv`
- `results/part-2/tables/exercise_4_summary.csv`
- `results/part-2/tables/exercise_5_raw.csv`
- `results/part-2/tables/exercise_5_summary.csv`

Gráficos:

- `results/part-2/graphs/exercise_4_gflops.svg`
- `results/part-2/graphs/exercise_4_speedup.svg`
- `results/part-2/graphs/exercise_4_efficiency.svg`
- `results/part-2/graphs/exercise_5_gflops.svg`
- `results/part-2/graphs/exercise_5_speedup.svg`


## Análise Parte 1

### Exercício 1

- A versão base `V1` foi claramente a mais lenta e a menos eficiente da Parte 1.
- Em C++, o desempenho desceu de cerca de `0.60 GFlop/s` em `1024` para `0.37 GFlop/s` em `3072`, enquanto o tempo subiu de `3.578 s` para `157.582 s`.
- Em Java apareceu a mesma tendência: cerca de `0.72 GFlop/s` em `1024` e `0.40 GFlop/s` em `3072`, com tempos entre `3 s` e `145 s`.
- Isto mostra que a ordem de ciclos usada em `V1` tem fraca localidade de memória e não aproveita bem a cache.
- Os counters de C++ reforçam essa leitura: o `IPC` ficou muito baixo, aproximadamente entre `0.29` e `0.48`, o que indica um processador frequentemente à espera de dados.

### Exercício 2

- A versão `V2` melhorou muito o comportamento do algoritmo apenas pela reordenação dos ciclos.
- Em C++, o desempenho passou para cerca de `7.13 GFlop/s` em `1024` e manteve-se bastante estável perto de `5 GFlop/s` até `10240`.
- Face a `V1`, o ganho em C++ ficou aproximadamente entre `11.9x` e `13.9x`, e em Java entre `10.4x` e `14.0x` nos tamanhos comparáveis.
- Em Java, o ponto `1024` ficou com `0.000 s` e `GFlop/s = NA`, o que deve ser lido com cuidado, porque o código usa `System.currentTimeMillis()` e a resolução do relógio não chega para execuções tão curtas.
- Em C++, os counters mostram uma melhoria muito forte: os `cache misses` ficaram aproximadamente entre `130x` e `253x` abaixo de `V1`, e o `IPC` subiu cerca de `13x` a `16x`.
- Isto confirma que o principal ganho de `V2` vem do acesso muito mais favorável à memória, e não de uma mudança no número de operações.

### Exercício 3

- A versão com blocos `V3` voltou a melhorar o desempenho para os tamanhos grandes (`4096` a `10240`).
- Todos os tamanhos de bloco testados (`128`, `256` e `512`) ficaram acima de `V2`, o que mostra que o blocking compensa quando a matriz já não cabe tão bem nas caches.
- O melhor compromisso global foi `block = 512`, com média de cerca de `6.42 GFlop/s`, acima de `block = 128` (`6.22 GFlop/s`) e `block = 256` (`5.93 GFlop/s`).
- O bloco `512` foi o melhor em `6144`, `8192` e `10240`, enquanto em `4096` o melhor valor apareceu com `block = 128`.
- Comparando com `V2`, o ganho de `V3` ficou aproximadamente entre `10%` e `33%`, com o melhor caso em `6144` usando `block = 512`.
- O `IPC` também subiu ligeiramente face a `V2` na maioria dos casos, o que sugere melhor aproveitamento do processador quando o trabalho é reorganizado por blocos.

### Conclusões

- O maior salto de desempenho da Parte 1 veio da mudança de ordem dos acessos à memória: `V2` foi muito melhor do que `V1` em ambas as linguagens.
- Para matrizes grandes, o blocking de `V3` melhorou ainda mais os resultados e passou a ser a melhor versão serial testada.
- Nos resultados desta máquina, `block = 512` foi o parâmetro mais forte para `V3`, embora `128` ainda seja competitivo em tamanhos menores dentro do conjunto testado.
- C++ e Java seguiram a mesma tendência geral, o que reforça que o fator decisivo aqui é a organização do algoritmo e dos acessos à memória.
- Ainda assim, os resultados de Java devem ser lidos com alguma prudência nos casos muito curtos, porque a medição com `System.currentTimeMillis()` tem resolução limitada.

## Análise Parte 2

### Exercício 4

- A versão serial `V2` foi claramente superior a `V1` em todos os tamanhos testados.
- Em média, `V1` serial ficou aproximadamente entre `0.22` e `0.92 GFlop/s`, enquanto `V2` serial ficou entre `3.40` e `4.98 GFlop/s`.
- Isto confirma que a ordem de acesso usada em `V2` é muito mais favorável à cache do que a de `V1`.
- As versões paralelas base (`mode 4` para `V1` e `mode 6` para `V2`) melhoraram o desempenho face às referências seriais, mas com comportamentos diferentes.
- Em `V1`, a versão paralela base apresentou speedup aproximadamente entre `1.56` e `3.77`, com eficiência entre `0.39` e `0.94`.
- Em `V2`, o comportamento foi mais estável: o speedup ficou aproximadamente entre `1.59` e `2.18`, e a eficiência entre `0.40` e `0.55`.
- Isto mostra um ganho real com 4 threads, mas ainda longe da escalabilidade ideal.
- As versões alternativas (`mode 5` e `mode 7`) foram claramente piores do que as versões base, e também piores do que as versões seriais em praticamente todos os casos.
- Em `V1 Alternative`, o speedup ficou aproximadamente entre `0.10` e `1.15`, enquanto em `V2 Alternative` ficou entre `0.06` e `0.24`.
- Isto confirma que a colocação da diretiva de paralelização numa zona errada do ciclo introduz overhead e sincronização suficientes para destruir o ganho do paralelismo.
- Em `V1 Parallel` (`mode 4`) os resultados ficaram mais irregulares do que em `V2`, com speedups muito variáveis entre tamanhos.
- Depois da correção das repetições, os resultados ficaram mais credíveis e deixaram de mostrar ganhos claramente acima do razoável para 4 threads.
- Por isso, `V2 Parallel` continua a ser a comparação mais robusta para avaliar o efeito do paralelismo no `Exercício 4`.

### Exercício 5

- Considerando apenas `V2`, o aumento do número de threads melhorou o desempenho global, mas com ganhos decrescentes à medida que o número de threads subiu.
- A versão paralela base (`mode 6`) passou de cerca de `7.84 GFlop/s` com `4` threads para cerca de `10.95 GFlop/s` com `20` threads, com uma ligeira quebra em `24` threads.
- O speedup de `mode 6` ficou aproximadamente entre `2.05` e `2.87`, mostrando que há ganho real, mas longe de uma escalabilidade linear.
- A variante com `SIMD` (`mode 8`) foi a melhor em praticamente todos os testes.
- O seu desempenho subiu de cerca de `10.57 GFlop/s` com `4` threads para cerca de `12.20 GFlop/s` com `24` threads, com speedup máximo próximo de `3.19`.
- Isto era esperado porque combina paralelismo por threads com vetorização do ciclo mais interno.
- A variante com `collapse(2)` (`mode 9`) foi de longe a pior.
- Mesmo com `24` threads, ficou apenas em cerca de `2.15 GFlop/s`, abaixo da própria referência serial (`3.82 GFlop/s`).
- O speedup de `mode 9` ficou sempre abaixo de `1`, aproximadamente entre `0.31` e `0.56`, o que significa que esta abordagem não compensa.
- O motivo principal é o uso de `atomic` nas atualizações de `C(i,j)`, o que introduz forte sincronização e tráfego de coerência de cache.
- A eficiência desce em todas as variantes quando o número de threads aumenta, o que mostra que os ganhos adicionais por thread vão diminuindo.
- Os gráficos de `GFlop/s` e `Speedup` em `Exercício 5` têm praticamente a mesma forma porque o tamanho da matriz é fixo (`8192`), logo ambas as métricas dependem essencialmente do tempo de execução.

### Conclusões

- O algoritmo `V2` é melhor do que `V1`, tanto em serial como em paralelo, porque a sua ordem de acesso aos dados é mais favorável à cache.
- Paralelizar um algoritmo já eficiente em memória produz melhores resultados do que paralelizar diretamente um algoritmo com pior localidade.
- A melhor abordagem da Parte 2 foi `V2` com `SIMD`, que apresentou o maior `GFlop/s` e o melhor speedup.
- A versão paralela base de `V2` também foi eficaz, mas com ganhos moderados e eficiência decrescente com mais threads.
- As versões alternativas e a versão com `collapse(2)` mostraram que uma escolha errada da estratégia OpenMP pode introduzir overhead suficiente para anular o benefício do paralelismo.
- Os resultados de `V1 Parallel` devem ainda ser analisados com alguma prudência, porque continuam a mostrar mais variação do que `V2`, embora agora estejam mais consistentes do que antes da correção das repetições.
- Mais threads não garantem ganhos proporcionais, e a eficiência diminui quando o overhead de sincronização e de memória passa a dominar.
