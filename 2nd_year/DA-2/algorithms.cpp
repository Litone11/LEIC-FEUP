/**
 * @file algorithms.cpp
 * @author João Taveira, Luís Martins
 * @brief Implementation of four algorithmic approaches to solve the 0/1 Knapsack Problem
 * @version 1.0
 * @date 2025-05-20
 *
 * This file includes:
 * - Brute-force recursive solution
 * - Dynamic programming solution
 * - Greedy heuristic solution
 * - Integer Linear Programming (ILP) style branch-and-bound solution
 */
#include "Pallet.h"
#include "algorithms.h"
#include <vector>
#include <algorithm>
#include <iostream>
#include <chrono>


//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------

//case 1

/**
 * @brief Recursive brute-force solution to the 0/1 Knapsack Problem.
 *
 * Tries all combinations of including/excluding each pallet.
 * Optimizes for maximum profit and minimal pallet usage.
 *
 * @param pallets Vector of pallets.
 * @param index Current index of recursion.
 * @param remainingCapacity Remaining capacity of the truck.
 * @param currentProfit Accumulated profit.
 * @param currentSubset Current pallet IDs being considered.
 * @param bestProfit Reference to best profit found.
 * @param bestSubset Reference to best subset found.
 * @return Best profit found.
 */

int knapsackRecursive(const std::vector<Pallet>& pallets, int index, int remainingCapacity,
                      int currentProfit, std::vector<int>& currentSubset,
                      int& bestProfit, std::vector<int>& bestSubset) {

    if (index == pallets.size()) {
        if (currentProfit > bestProfit ||
            (currentProfit == bestProfit && currentSubset.size() < bestSubset.size())) {
            bestProfit = currentProfit;
            bestSubset = currentSubset;
        }
        return bestProfit;
    }

    knapsackRecursive(pallets, index + 1, remainingCapacity,
                      currentProfit, currentSubset, bestProfit, bestSubset);

    if (pallets[index].weight <= remainingCapacity) {
        currentSubset.push_back(pallets[index].id);
        knapsackRecursive(pallets, index + 1, remainingCapacity - pallets[index].weight,
                          currentProfit + pallets[index].profit, currentSubset, bestProfit, bestSubset);
        currentSubset.pop_back();
    }

    return bestProfit;
}

/**
 * @brief Wrapper for brute-force recursive knapsack solver.
 *
 * Initializes tracking variables and prints selected pallet IDs.
 *
 * @param capacity Max truck capacity.
 * @param pallets List of available pallets.
 * @return Maximum achievable profit.
 */
int KBruteForce(int capacity, const std::vector<Pallet>& pallets) {
    int bestProfit = 0;
    std::vector<int> bestSubset;
    std::vector<int> currentSubset;

    int finalProfit = knapsackRecursive(pallets, 0, capacity, 0, currentSubset, bestProfit, bestSubset);

    std::cout << "Selected Pallets (ID | Value | Weight):\n";
    for (int id : bestSubset) {
        auto it = std::find_if(pallets.begin(), pallets.end(), [id](const Pallet& p) {
            return p.id == id;
        });
        if (it != pallets.end()) {
            std::cout << it->id << " | " << it->profit << " | " << it->weight << "\n";
        }
    }


    return finalProfit;
}

//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------

//case 2

/**
 * @brief Dynamic programming solution to the 0/1 Knapsack Problem.
 *
 * Builds a DP table to compute optimal profit with subproblem reuse.
 * Also tracks the minimal number of pallets used to resolve ties.
 *
 * @param capacity Max truck capacity.
 * @param pallets List of available pallets.
 * @return Maximum achievable profit.
 */
int KDynamic(int capacity, const std::vector<Pallet>& pallets) {
    int n = pallets.size();

    // Tabela de lucro máximo
    std::vector<std::vector<int>> dp(n + 1, std::vector<int>(capacity + 1, 0));

    // Tabela auxiliar: número mínimo de paletes usadas para obter aquele lucro
    std::vector<std::vector<int>> count(n + 1, std::vector<int>(capacity + 1, 0));

    // Preenchimento da tabela
    for (int i = 1; i <= n; ++i) {
        for (int w = 0; w <= capacity; ++w) {
            int peso = pallets[i - 1].weight;
            int lucro = pallets[i - 1].profit;

            if (peso <= w) {
                int inclui = lucro + dp[i - 1][w - peso];
                int exclui = dp[i - 1][w];

                if (inclui > exclui) {
                    dp[i][w] = inclui;
                    count[i][w] = count[i - 1][w - peso] + 1;
                } else if (inclui < exclui) {
                    dp[i][w] = exclui;
                    count[i][w] = count[i - 1][w];
                } else {
                    // Empate de lucro: escolher o que usar menos paletes
                    int incluiCount = count[i - 1][w - peso] + 1;
                    int excluiCount = count[i - 1][w];
                    dp[i][w] = inclui;
                    count[i][w] = std::min(incluiCount, excluiCount);
                }
            } else {
                dp[i][w] = dp[i - 1][w];
                count[i][w] = count[i - 1][w];
            }
        }
    }

    // Reconstruir subconjunto ótimo (preferência por menos paletes)
    int w = capacity;
    std::vector<int> selectedIDs;

    for (int i = n; i > 0 && w > 0; --i) {
        if (dp[i][w] != dp[i - 1][w]) {
            selectedIDs.push_back(pallets[i - 1].id);
            w -= pallets[i - 1].weight;
        }
    }

    std::reverse(selectedIDs.begin(), selectedIDs.end());

    std::cout << "Selected Pallets (ID | Value | Weight):\n";
    for (int id : selectedIDs) {
        auto it = std::find_if(pallets.begin(), pallets.end(), [id](const Pallet& p) {
            return p.id == id;
        });
        if (it != pallets.end()) {
            std::cout << it->id << " | " << it->profit << " | " << it->weight << "\n";
        }
    }

    return dp[n][capacity];
}



//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------

//case 3

/**
 * @brief Greedy approximation solution to the 0/1 Knapsack Problem.
 *
 * Sorts pallets by profit-to-weight ratio and selects greedily.
 * Does not guarantee optimality but is fast for large instances.
 *
 * @param capacity Max truck capacity.
 * @param pallets List of available pallets.
 * @return Approximate total profit.
 */
int KProxy(int capacity, const std::vector<Pallet>& pallets) {
    std::vector<Pallet> sorted = pallets;

    // Ordenar por eficiência decrescente (lucro/peso)
    std::sort(sorted.begin(), sorted.end(), [](const Pallet& a, const Pallet& b) {
        double r1 = static_cast<double>(a.profit) / a.weight;
        double r2 = static_cast<double>(b.profit) / b.weight;
        return r1 > r2;
    });

    int totalProfit = 0;
    int totalWeight = 0;
    std::vector<int> selectedIDs;

    for (const auto& p : sorted) {
        if (totalWeight + p.weight <= capacity) {
            selectedIDs.push_back(p.id);
            totalWeight += p.weight;
            totalProfit += p.profit;
        }
    }

    std::cout << "Sellected Pallets (ID | Value | Weight):\n";
    for (int id : selectedIDs) {
        auto it = std::find_if(pallets.begin(), pallets.end(), [id](const Pallet& p) {
            return p.id == id;
        });
        if (it != pallets.end()) {
            std::cout << it->id << " | " << it->profit << " | " << it->weight << "\n";
        }
    }

    std::cout << "Peso total: " << totalWeight << " / Capacidade: " << capacity << "\n";


    return totalProfit;
}


//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------

//case 4


/**
 * @brief Branch and bound implementation for ILP-style solution.
 *
 * Recursively explores subsets of pallets and prunes unpromising paths.
 * Tracks best profit, smallest number of pallets, and lightest weight.
 *
 * @param pallets All available pallets.
 * @param idx Current index in recursion.
 * @param capacity Truck capacity.
 * @param currWeight Current weight used.
 * @param currProfit Current profit accumulated.
 * @param currSelection Currently selected pallet IDs.
 * @param bestSelection Best selection found.
 * @param bestProfit Best profit found.
 * @param bestWeight Best weight found (used to break ties).
 */
namespace {
    void branchAndBound(const std::vector<Pallet>& pallets, int idx, int capacity,
                        int currWeight, int currProfit,
                        std::vector<int>& currSelection,
                        std::vector<int>& bestSelection,
                        int& bestProfit, int& bestWeight) {

        if (pallets.empty()) return;

        if (idx >= static_cast<int>(pallets.size())) {
            if (currProfit > bestProfit ||
                (currProfit == bestProfit && (
                    currSelection.size() < bestSelection.size() ||
                    (currSelection.size() == bestSelection.size() && currWeight < bestWeight)
                ))) {
                bestProfit = currProfit;
                bestWeight = currWeight;
                bestSelection = currSelection;
            }
            return;
        }

        const Pallet& current = pallets[idx];
        if (currWeight + current.getWeight() <= capacity) {
            currSelection.push_back(current.getID());
            branchAndBound(pallets, idx + 1, capacity,
                           currWeight + current.getWeight(),
                           currProfit + current.getProfit(),
                           currSelection, bestSelection, bestProfit, bestWeight);
            currSelection.pop_back();
        }

        // Try excluding current pallet
        branchAndBound(pallets, idx + 1, capacity,
                       currWeight, currProfit,
                       currSelection, bestSelection, bestProfit, bestWeight);
    }
}

/**
 * @brief Solves the knapsack problem using ILP via branch and bound.
 *
 * Returns the best selection based on profit, pallet count, and total weight.
 *
 * @param pallets List of pallets.
 * @param capacity Truck capacity.
 * @return ILPResult containing selected pallets, total weight, and profit.
 */
ILPResult solveILP(const std::vector<Pallet>& pallets, int capacity) {
    ILPResult result;
    std::vector<int> currentSelection;
    int bestProfit = 0;
    int bestWeight = INT_MAX;

    auto start = std::chrono::high_resolution_clock::now();
    branchAndBound(pallets, 0, capacity, 0, 0, currentSelection, result.selectedPallets, bestProfit, bestWeight);
    auto end = std::chrono::high_resolution_clock::now();

    result.totalProfit = bestProfit;
    result.totalWeight = 0;
    for (int id : result.selectedPallets) {
        auto it = std::find_if(pallets.begin(), pallets.end(), [id](const Pallet& p) {
            return p.getID() == id;
        });
        if (it != pallets.end()) {
            result.totalWeight += it->getWeight();
        }
    }
    return result;
}

/**
 * @brief Validates and checks consistency of an ILP solution.
 *
 * Recomputes profit and weight and compares with stored values.
 *
 * @param result ILP result to validate.
 * @param pallets Pallets list used in original input.
 * @param capacity Max allowed truck weight.
 */
void validateILP(const ILPResult& result, const std::vector<Pallet>& pallets, int capacity);
