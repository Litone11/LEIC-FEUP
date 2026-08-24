import java.util.Scanner;

public class mult {

    // Faz a multiplicacao classica com a ordem i-j-k, igual a uma baseline simples.
    // Como funciona: calcula cada C[i,j] somando os produtos entre a linha i de A e a coluna j de B.
    // Vantagem: codigo direto e facil de comparar. Desvantagem: acesso a memoria pouco eficiente.
    public static void onMult(int m_ar, int m_br) {
        long time1, time2;
        double temp;
        int i, j, k;

        double[] pha = new double[m_ar * m_ar];
        double[] phb = new double[m_ar * m_ar];
        double[] phc = new double[m_ar * m_ar];

        for (i = 0; i < m_ar; i++)
            for (j = 0; j < m_ar; j++)
                pha[i * m_ar + j] = 1.0;

        for (i = 0; i < m_br; i++)
            for (j = 0; j < m_br; j++)
                phb[i * m_br + j] = (double) (i + 1);

        time1 = System.currentTimeMillis();

        for (i = 0; i < m_ar; i++) {
            for (j = 0; j < m_br; j++) {
                temp = 0;
                for (k = 0; k < m_ar; k++) {
                    temp += pha[i * m_ar + k] * phb[k * m_br + j];
                }
                phc[i * m_ar + j] = temp;
            }
        }

        time2 = System.currentTimeMillis();
        System.out.printf("Time: %3.3f seconds\n", (double) (time2 - time1) / 1000.0);

        System.out.println("Result matrix: ");
        for (i = 0; i < 1; i++) {
            for (j = 0; j < Math.min(10, m_br); j++)
                System.out.print(phc[j] + " ");
        }
        System.out.println();
    }

    // Faz a multiplicacao linha a linha com a ordem i-k-j para melhorar localidade.
    // Como funciona: para cada linha i, usa cada elemento A[i,k] para atualizar de seguida todas as colunas j de C.
    // Vantagem: normalmente usa melhor a cache. Desvantagem: continua com custo O(n^3).
    public static void onMultLine(int m_ar, int m_br) {
        long time1, time2;
        int i, j, k;

        double[] pha = new double[m_ar * m_ar];
        double[] phb = new double[m_ar * m_ar];
        double[] phc = new double[m_ar * m_ar];

        for (i = 0; i < m_ar; i++)
            for (j = 0; j < m_ar; j++)
                pha[i * m_ar + j] = 1.0;

        for (i = 0; i < m_br; i++)
            for (j = 0; j < m_br; j++)
                phb[i * m_br + j] = (double) (i + 1);

        for (i = 0; i < m_ar; i++)
            for (j = 0; j < m_br; j++)
                phc[i * m_ar + j] = 0.0;

        time1 = System.currentTimeMillis();

        for (i = 0; i < m_ar; i++) {
            for (k = 0; k < m_ar; k++) {
                for (j = 0; j < m_br; j++) {
                    phc[i * m_ar + j] += pha[i * m_ar + k] * phb[k * m_br + j];
                }
            }
        }

        time2 = System.currentTimeMillis();
        System.out.printf("Time: %3.3f seconds\n", (double) (time2 - time1) / 1000.0);

        System.out.println("Result matrix: ");
        for (i = 0; i < 1; i++) {
            for (j = 0; j < Math.min(10, m_br); j++)
                System.out.print(phc[j] + " ");
        }
        System.out.println();
    }

    // Controla o menu, le a opcao do utilizador e chama a versao pretendida.
    // Como funciona: repete um ciclo de leitura da opcao, pede a dimensao e invoca a funcao correspondente.
    // Vantagem: centraliza a execucao do programa. Desvantagem: deixa a logica de interface acoplada ao calculo.
    public static void main(String[] args) {
        Scanner scanner = new Scanner(System.in);
        int lin, col;
        int op;

        do {
            System.out.println("\n1. Multiplication");
            System.out.println("2. Line Multiplication");
            System.out.println("0. Exit");
            System.out.print("Selection?: ");

            if (!scanner.hasNextInt()) {
                scanner.next();
                op = -1;
            } else {
                op = scanner.nextInt();
            }

            if (op == 0)
                break;

            if (op < 1 || op > 2) {
                System.out.println("Invalid selection");
                continue;
            }

            System.out.print("Dimensions: lins=cols ? ");
            lin = scanner.nextInt();
            col = lin;

            switch (op) {
                case 1:
                    onMult(lin, col);
                    break;
                case 2:
                    onMultLine(lin, col);
                    break;
            }

        } while (op != 0);

        scanner.close();
    }
}
