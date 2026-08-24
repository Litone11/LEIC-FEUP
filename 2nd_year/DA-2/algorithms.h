/**
 * @file algorithms.h
 * @brief Function declarations for solving the 0/1 Knapsack Problem using multiple approaches.
 *
 * This header declares all the algorithmic methods implemented to solve the knapsack problem,
 * including brute-force, dynamic programming, greedy approximation, and ILP-based solutions.
 * 
 * @author Luís Martins e João Taveira
 * @date 2025-05-20
 */

#ifndef ALGORITHMS_H
#define ALGORITHMS_H

#include <vector>
#include "Pallet.h"

/**
 * @brief Structure to hold the result of the ILP-based solution.
 *
 * Stores the IDs of selected pallets, their total profit, and total weight.
 */
struct ILPResult {
    std::vector<int> selectedPallets; // pallet IDs
    int totalProfit;
    int totalWeight;
};

/**
 * @brief Solves the knapsack problem using brute-force recursion.
 * 
 * @param capacity Maximum capacity of the truck.
 * @param pallets Vector of available pallets.
 * @return Maximum profit achievable.
 */
int KBruteForce(int capacity, const std::vector<Pallet>& pallets);

/**
 * @brief Solves the knapsack problem using dynamic programming.
 * 
 * @param capacity Maximum capacity of the truck.
 * @param pallets Vector of available pallets.
 * @return Maximum profit achievable.
 */
int KDynamic(int capacity, const std::vector<Pallet>& pallets);

/**
 * @brief Solves the knapsack problem using a greedy heuristic approximation.
 * 
 * @param capacity Maximum capacity of the truck.
 * @param pallets Vector of available pallets.
 * @return Approximate profit achieved.
 */
int KProxy(int capacity, const std::vector<Pallet>& pallets);

/**
 * @brief Solves the knapsack problem using a custom ILP-style branch-and-bound method.
 * 
 * @param pallets Vector of available pallets.
 * @param capacity Maximum capacity of the truck.
 * @return Struct containing selected pallet IDs, profit, and total weight.
 */
ILPResult solveILP(const std::vector<Pallet>& pallets, int capacity);


#endif
