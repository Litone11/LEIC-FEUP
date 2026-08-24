#include <iostream>
#include <limits>
#include "parser.h"
#include "route.h"
#include "graph.h"
#include "batch.h"
#include <sstream>

using namespace std;

// Vetores globais para guardar locais e segmentos carregados dos ficheiros CSV

/**
 * @brief Stores the locations loaded from the CSV file.
 *
 * Each location represents a node in the graph.
 */
vector<Location> locations;

/**
 * @brief Stores the edges loaded from the CSV file.
 *
 * Each edge represents a connection between two locations, including
 * driving and walking time.
 */
vector<Edge> edges;

/**
 * @brief Main graph instance used for route calculations.
 *
 * This graph is built using the loaded locations and edges.
 */
Graph g;

/**
 * @brief Displays the main menu options for the user.
 *
 * This function prints the available options, allowing the user to
 * select different types of route calculations or batch processing.
 *
 * @note Time Complexity: O(1), as printing the menu is a constant-time operation.
 */
void showMainMenu() {
    cout << "\n==========================";
    cout << "\n=== Escolha uma opcao: ===\n";
    cout << "==========================\n";
    cout << "1. Calcular rota mais rápida\n";
    cout << "2. Calcular segunda rota mais rápida independente\n";
    cout << "3. Calcular rota com exclusão de pontos/segmentos\n";
    cout << "4. Calcular rota eco-friendly \n";
    cout << "5. Executar modo batch (input.txt → output.txt)\n";
    cout << "6. Sair\n";
    cout << "==========================\n";
}

/**
 * @brief Processes the selected option from the main menu.
 *
 * Based on the user's input, this function calls the appropriate
 * route calculation function or exits the application.
 *
 * @param option The menu option selected by the user.
 *        - 1: Calculate the fastest route.
 *        - 2: Calculate an independent second-fastest route.
 *        - 3: Calculate a route with restricted nodes/segments.
 *        - 4: Calculate an eco-friendly route.
 *        - 5: Execute batch processing from input.txt.
 *        - 6: Exit the application.
 *
 * @note Time Complexity: O(n), where n is the number of menu options, as it involves checking the selected option and performing corresponding actions.
 */
void handleOption(int option) {
    string src, dst;
    switch (option) {
        case 1: {
            cout << "ID de origem: ";
            cin >> src;
            cout << "ID de destino: ";
            cin >> dst;
            string code1 = getCodeById(locations, stoi(src));
            string code2 = getCodeById(locations, stoi(dst));
            auto path = dijkstraShortestPath(g, code1, code2);
            int time = calculateDrivingTime(g, path);
            if (path.empty()) {
                cout << "Rota impossível.\n";
            } else {
                cout << "Rota mais rápida: ";
                for (size_t i = 0; i < path.size(); ++i) {
                    cout << getIdByCode(locations, path[i]);
                    if (i < path.size() - 1) cout << ",";
                }
                cout << " (" << time << ")\n";
            }
            break;
        }
        // Outras opções seguem o mesmo estilo...
        case 5:
            processBatchFile(g, "input.txt", "output.txt");
            cout << "Batch processado. Verifica o ficheiro output.txt\n";
            break;

        case 6:
            cout << "A sair da aplicação. Obrigado!\n";
            break;

        default:
            cout << "Opção inválida. Tente novamente.\n";
    }
}

/**
 * @brief Entry point of the program.
 *
 * Loads location and edge data from CSV files, initializes the graph,
 * and enters the main menu loop where the user can choose an option.
 *
 * @return int Returns 0 upon successful execution.
 *
 * @note Time Complexity: O(n), where n is the number of locations and edges, as it involves parsing the CSV files and building the graph.
 */
int main() {
    // Carrega os dados dos ficheiros CSV
    locations = parseLocations("Locations.csv");
    edges = parseDistances("Distances.csv");

    // Constrói o grafo com os dados carregados no graph.cpp
    for (const auto& e : edges) {
        g.addEdge(e.from, e.to, e.drivingTime, e.walkingTime);
    }

    // Mostra dados iniciais (nº de locations e de segmentos)
    cout << "=== Dados Analisados: ===\n";
    cout << "Locais: " << locations.size() << endl;
    cout << "Segmentos: " << edges.size() << endl;

    // Loop principal do menu
    int option = 0;
    while (option != 6) {
        showMainMenu();
        cin >> option;

        if (cin.fail()) {
            cin.clear();
            cin.ignore(numeric_limits<streamsize>::max(), '\n');
            cout << "Entrada inválida. Insira um número.\n";
        } else {
            handleOption(option);
        }
    }

    return 0;
}