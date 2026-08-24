#ifndef PARSER_HPP
#define PARSER_HPP

#include <string>
#include <vector>

using namespace std;

/**
 * @struct Location
 * @brief Represents a location with an identifier, name, and code.
 *
 * Each location has a unique ID, a name, a code, and an indication of whether parking is available.
 */
struct Location {
    string name;
    int id;
    string code;
    bool hasParking;
};

/**
 * @struct Edge
 * @brief Represents a connection between two locations with travel times.
 *
 * An edge specifies the travel times (driving and walking) between two locations.
 * If driving is not possible, the driving time is set to -1.
 */
struct Edge {
    string from;
    string to;
    int drivingTime;   // -1 se não for possível conduzir
    int walkingTime;
};

/**
 * @brief Parses locations from a file.
 *
 * Reads a file and extracts location data into a vector.
 *
 * @param filename The path to the file containing location data.
 * @return A vector of parsed locations.
 *
 * @note Time Complexity: O(n), where n is the number of locations in the file, as it involves reading and parsing each line.
 */
vector<Location> parseLocations(const string& filename);

/**
 * @brief Parses distances from a file.
 *
 * Reads a file and extracts edge data representing travel times between locations.
 *
 * @param filename The path to the file containing distance data.
 * @return A vector of parsed edges.
 *
 * @note Time Complexity: O(m), where m is the number of edges in the file, as it involves reading and parsing each line.
 */
vector<Edge> parseDistances(const string& filename);

/**
 * @brief Retrieves a location code by ID.
 *
 * Searches a list of locations and returns the corresponding code for the given ID.
 *
 * @param locations The vector of locations.
 * @param id The ID of the location.
 * @return The corresponding location code, or an empty string if not found.
 *
 * @note Time Complexity: O(n), where n is the number of locations, as it involves searching through the vector.
 */
string getCodeById(const vector<Location>& locations, int id);

/**
 * @brief Retrieves a location ID by code.
 *
 * Searches a list of locations and returns the corresponding ID for the given code.
 *
 * @param locations The vector of locations.
 * @param code The code of the location.
 * @return The corresponding location ID, or -1 if not found.
 *
 * @note Time Complexity: O(n), where n is the number of locations, as it involves searching through the vector.
 */
int getIdByCode(const vector<Location>& locations, const string& code);

/**
 * @brief Cleans a location code.
 *
 * Removes unwanted characters or formatting from a location code.
 *
 * @param code The raw location code.
 * @return The cleaned location code.
 *
 * @note Time Complexity: O(k), where k is the length of the input code, as it involves processing each character of the code.
 */
string cleanCode(const string& code);

#endif
