/**
 * @file Pallet.h
 * @brief Definition of the Pallet structure used in knapsack algorithms.
 *
 * Represents an item (pallet) with a unique ID, weight, and profit.
 * Provides getter methods for all member variables.
 *
 * @author Luís Martins e João Taveira
 * @date 2025-05-20
 */

#ifndef PALLET_HPP
#define PALLET_HPP

/**
 * @brief Represents a pallet with an ID, weight, and profit.
 *
 * Used as the fundamental data unit in all knapsack-related algorithms.
 */
struct Pallet {
    int id;
    int weight;
    int profit;

    /**
     * @brief Returns the ID of the pallet.
     * @return Pallet ID.
     */
    int getID() const { return id; }

    /**
     * @brief Returns the weight of the pallet.
     * @return Pallet weight.
     */
    int getWeight() const { return weight; }

    /**
     * @brief Returns the profit of the pallet.
     * @return Pallet profit.
     */
    int getProfit() const { return profit; }
};

#endif
