#include "parser.h"
#include <fstream>
#include <sstream>
#include <iostream>
#include <algorithm>

/**
 * @brief Parses a CSV file containing location data.
 *
 * This function reads a file containing location data and returns a vector of `Location` objects.
 *
 * @param filename The path to the CSV file containing location data.
 * @return A vector of `Location` objects.
 *
 * @note Time Complexity: O(n), where n is the number of lines (locations) in the file. Each line is processed sequentially.
 */
vector<Location> parseLocations(const string& filename) {
    vector<Location> locations;
    ifstream file(filename);
    string line;

    getline(file, line); // ➤ Ignora a linha de cabeçalho

    //  Lê linha a linha os dados dos locais
    while (getline(file, line)) {
        stringstream ss(line);
        string name, idStr, code, parkingStr;

        //  Extrai os campos separados por vírgula
        getline(ss, name, ',');
        getline(ss, idStr, ',');
        getline(ss, code, ',');
        getline(ss, parkingStr, ',');

        Location loc;
        loc.name = name;
        loc.id = stoi(idStr);                         // ➤ Converte o ID para inteiro
        loc.code = code;
        parkingStr.erase(remove(parkingStr.begin(), parkingStr.end(), '\r'), parkingStr.end());
        parkingStr.erase(remove(parkingStr.begin(), parkingStr.end(), ' '), parkingStr.end());
        loc.hasParking = (parkingStr == "1");

        locations.push_back(loc);
    }

    return locations;
}

/**
 * @brief Parses a CSV file containing distance data.
 *
 * This function reads a file containing distance data and returns a vector of `Edge` objects.
 *
 * @param filename The path to the CSV file containing distance data.
 * @return A vector of `Edge` objects.
 *
 * @note Time Complexity: O(m), where m is the number of lines (edges) in the file. Each line is processed sequentially.
 */
vector<Edge> parseDistances(const string& filename) {
    vector<Edge> edges;
    ifstream file(filename);
    string line;

    getline(file, line); //  Ignora a linha de cabeçalho

    //  Lê cada linha e cria objetos Edge
    while (getline(file, line)) {
        stringstream ss(line);
        string from, to, drivingStr, walkingStr;

        getline(ss, from, ',');
        getline(ss, to, ',');
        getline(ss, drivingStr, ',');
        getline(ss, walkingStr, ',');

        Edge edge;
        edge.from = from;
        edge.to = to;
        edge.drivingTime = (drivingStr == "X") ? -1 : stoi(drivingStr);   // ➤ "X" indica que não há tempo de condução
        edge.walkingTime = (walkingStr == "X") ? -1 : stoi(walkingStr);   // ➤ "X" indica que não há tempo a pé

        edges.push_back(edge);
    }

    return edges;
}

/**
 * @brief Retrieves the code of a location by its ID.
 *
 * This function searches through a vector of `Location` objects and returns the corresponding code for a given ID.
 *
 * @param locations The vector of `Location` objects.
 * @param id The ID of the location to find.
 * @return The code of the location if found, otherwise an empty string.
 *
 * @note Time Complexity: O(n), where n is the number of locations in the vector, as each location is checked once.
 */
string getCodeById(const vector<Location>& locations, int id) {
    for (const auto& loc : locations) {
        if (loc.id == id) return loc.code;
    }
    return "";
}

/**
 * @brief Retrieves the ID of a location by its code.
 *
 * This function searches through a vector of `Location` objects and returns the corresponding ID for a given code.
 *
 * @param locations The vector of `Location` objects.
 * @param code The code of the location to find.
 * @return The ID of the location if found, otherwise -1.
 *
 * @note Time Complexity: O(n), where n is the number of locations in the vector, as each location is checked once.
 */
int getIdByCode(const vector<Location>& locations, const string& code) {
    for (const auto& loc : locations) {
        if (loc.code == code) return loc.id;
    }
    return -1;
}

/**
 * @brief Removes spaces from a location code.
 *
 * This function removes any spaces from a location code string.
 *
 * @param code The location code to clean.
 * @return The cleaned location code with no spaces.
 *
 * @note Time Complexity: O(k), where k is the length of the input string, as each character is processed.
 */
string cleanCode(const string& code) {
    string trimmed = code;
    trimmed.erase(remove(trimmed.begin(), trimmed.end(), ' '), trimmed.end());
    return trimmed;
}