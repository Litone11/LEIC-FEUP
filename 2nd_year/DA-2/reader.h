/**
 * @file reader.h
 * @brief Function declarations for reading truck and pallet data from CSV files.
 *
 * Declares functions to load truck capacity and pallet information for use
 * in knapsack problem algorithms.
 * 
 * @author Luís Martins e João Taveira
 * @date 2025-05-20
 */
#ifndef DATA_LOADER_HPP
#define DATA_LOADER_HPP

#include "Pallet.h"
#include <vector>
#include <string>

/**
 * @brief Loads truck capacity and pallet count from a CSV file.
 * 
 * @param filename Path to the truck CSV file.
 * @param capacity Reference to store truck capacity.
 * @param numPallets Reference to store number of pallets.
 * @return true if file is successfully read, false otherwise.
 */
bool loadTruckData(const std::string& filename, int& capacity, int& numPallets);

/**
 * @brief Loads pallet data from a CSV file into a vector.
 * 
 * @param filename Path to the pallets CSV file.
 * @param pallets Reference to vector to store loaded pallets.
 * @return true if file is successfully read, false otherwise.
 */
bool loadPallets(const std::string& filename, std::vector<Pallet>& pallets);

#endif
