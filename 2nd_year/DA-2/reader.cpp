/**
 * @file reader.cpp
 * @brief Handles reading of truck and pallet data from CSV files.
 *
 * Implements functions to parse CSV files and extract truck capacity,
 * number of pallets, and a list of pallet objects.
 * 
 * @author Luís Martins e João Taveira
 * @date 2025-05-20
 */

#include "reader.h"
#include <fstream>
#include <sstream>
#include <iostream>

/**
 * @brief Loads truck capacity and pallet count from a CSV file.
 *
 * Reads the second line of the file and parses the capacity and number of pallets.
 *
 * @param filename Path to the truck data CSV file.
 * @param capacity Reference to store the truck's capacity.
 * @param numPallets Reference to store the number of pallets.
 * @return true if file is successfully read, false otherwise.
 */
bool loadTruckData(const std::string& filename, int& capacity, int& numPallets) {
    std::ifstream file(filename);
    if (!file.is_open()) {
        std::cerr << "Erro ao abrir ficheiro: " << filename << "\n";
        return false;
    }

    std::string line;
    std::getline(file, line); // header
    std::getline(file, line); // data

    std::stringstream ss(line);
    std::string token;

    std::getline(ss, token, ',');
    capacity = std::stoi(token);

    std::getline(ss, token);
    numPallets = std::stoi(token);

    return true;
}

/**
 * @brief Loads pallet data from a CSV file into a vector of Pallet objects.
 *
 * Parses each line of the file to extract pallet ID, weight, and profit,
 * and stores the data in the given vector.
 *
 * @param filename Path to the pallets data CSV file.
 * @param pallets Reference to a vector where the parsed pallets will be stored.
 * @return true if file is successfully read, false otherwise.
 */
bool loadPallets(const std::string& filename, std::vector<Pallet>& pallets) {
    std::ifstream file(filename);
    if (!file.is_open()) {
        std::cerr << "Erro ao abrir ficheiro: " << filename << "\n";
        return false;
    }

    std::string line;
    std::getline(file, line); // header

    while (std::getline(file, line)) {
        std::stringstream ss(line);
        std::string token;
        Pallet p;

        std::getline(ss, token, ',');
        p.id = std::stoi(token);

        std::getline(ss, token, ',');
        p.weight = std::stoi(token);

        std::getline(ss, token);
        p.profit = std::stoi(token);

        pallets.push_back(p);
    }

    return true;
}
