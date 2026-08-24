/**
 * @file main.cpp
 * @brief Command-line interface for testing multiple knapsack algorithms.
 * 
 * Allows users to choose between brute-force, dynamic programming,
 * greedy approximation, and ILP-style approaches to solve instances
 * of the 0/1 knapsack problem. Reads dataset files and reports results
 * including selected pallets, profit, and execution time.
 * 
 * @author João Taveira e Luís Martins
 * @date 2025-05-20
 */

#include <iostream>
#include <vector>
#include <string>
#include <chrono>
#include <algorithm>
#include "reader.h"
#include "Pallet.h"
#include "algorithms.h"

/**
 * @brief Displays the algorithm selection menu.
 *
 * Lists available algorithmic options for solving the knapsack problem.
 */
void showMenu();

/**
 * @brief Main function to drive the knapsack algorithm selection and execution.
 *
 * Repeatedly prompts the user to choose an algorithm and dataset, reads data files,
 * and runs the selected algorithm. Outputs results and timing to the console.
 * 
 * @return int Returns 0 upon successful program termination.
 */
int main() {
    std::string datasetId;
    int choice = -1;

    while (choice != 0) {
        showMenu();
        std::cin >> choice;

        if (choice == 0) {
            std::cout << "Leaving program...\n";
            break;
        }

        std::cout << "Chose the dataset number: ";
        std::cin >> datasetId;
        if (datasetId.size() == 1) datasetId = "0" + datasetId;

        std::string truckFile = "../data/TruckAndPallets_" + datasetId + ".csv";
        std::string palletFile = "../data/Pallets_" + datasetId + ".csv";

        int capacity, numPallets;
        std::vector<Pallet> pallets;

        if (!loadTruckData(truckFile, capacity, numPallets)) {
            std::cerr << "Error loading File: " << truckFile << "\n";
            continue;
        }

        if (!loadPallets(palletFile, pallets)) {
            std::cerr << "Error loading File: " << palletFile << "\n";
            continue;
        }

        int result = 0;
        std::chrono::duration<double, std::milli> duration;
        std::string algorithmName;

        switch (choice) {
            case 1: {
                algorithmName = "Brute Force";
                auto start = std::chrono::high_resolution_clock::now();
                result = KBruteForce(capacity, pallets);
                auto end = std::chrono::high_resolution_clock::now();
                duration = end - start;
                break;
            }
            case 2: {
                algorithmName = "Dynamic Programming";
                auto start = std::chrono::high_resolution_clock::now();
                result = KDynamic(capacity, pallets);
                auto end = std::chrono::high_resolution_clock::now();
                duration = end - start;
                break;
            }
            case 3: {
                algorithmName = "Greedy Approximation";
                auto start = std::chrono::high_resolution_clock::now();
                result = KProxy(capacity, pallets);
                auto end = std::chrono::high_resolution_clock::now();
                duration = end - start;
                break;
            }
            case 4: {
                algorithmName = "Integer Linear Programming";
                auto start = std::chrono::high_resolution_clock::now();
                ILPResult ilpResult = solveILP(pallets, capacity);
                auto end = std::chrono::high_resolution_clock::now();
                duration = end - start;

                result = ilpResult.totalProfit;
                std::cout << "Paletes selecionadas (ID | Value | Weight):\n";
                for (const int& id : ilpResult.selectedPallets) {
                    auto it = std::find_if(pallets.begin(), pallets.end(), [id](const Pallet& p) {
                        return p.id == id;
                    });
                    if (it != pallets.end()) {
                        std::cout << it->id << " | " << it->profit << " | " << it->weight << "\n";
                    }
                }
                std::cout << "Peso total: " << ilpResult.totalWeight << "\n";

                break;
            }
            default:
                std::cerr << "Invalid option.\n";
                continue;
        }

        std::cout << "Algorithm: " << algorithmName << "\n";
        std::cout << "Max profit: " << result << "\n";
        std::cout << "Execution time: " << duration.count() << " ms\n\n";
    }

    return 0;
}

void showMenu() {
    std::cout << "===== Knapstack =====\n";
    std::cout << "Chose an option:\n";
    std::cout << "  1 - Brute Force\n";
    std::cout << "  2 - Dynamic Programming\n";
    std::cout << "  3 - Approximation (Greedy Method)\n";
    std::cout << "  4 - Integer Linear Programming\n";
    std::cout << "  0 - Leave\n";
    std::cout << "Option: ";
}
