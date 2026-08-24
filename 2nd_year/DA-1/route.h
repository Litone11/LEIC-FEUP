#pragma once
#include <string>
#include <vector>
#include <set>
#include <tuple>
#include "graph.h"

/**
 * @brief Computes the shortest path using Dijkstra's algorithm.
 *
 * Finds the shortest path from a source node to a destination node in the graph.
 *
 * @param g The graph in which to find the shortest path.
 * @param source The starting node.
 * @param dest The destination node.
 * @return A vector of node identifiers representing the shortest path.
 *
 * @note Time Complexity: O((E + V) * log V), where E is the number of edges and V is the number of vertices. This complexity arises from the priority queue used in Dijkstra's algorithm.
 */
std::vector<std::string> dijkstraShortestPath(Graph& g, const std::string& source, const std::string& dest);

/**
 * @brief Finds an alternative route to the main shortest path.
 *
 * Attempts to find a different route between the source and destination while avoiding the given main path.
 *
 * @param g The graph in which to find the route.
 * @param source The starting node.
 * @param dest The destination node.
 * @param mainPath The primary shortest path to avoid.
 * @return A vector of node identifiers representing the alternative route.
 *
 * @note Time Complexity: O((E + V) * log V), where E is the number of edges and V is the number of vertices. This is because the algorithm uses a modified Dijkstra's approach.
 */
std::vector<std::string> findAlternativeRoute(Graph& g, const std::string& source, const std::string& dest, const std::vector<std::string>& mainPath);

/**
 * @brief Computes a shortest path with restrictions.
 *
 * Finds the shortest path from source to destination while avoiding specific nodes and edges.
 *
 * @param g The graph in which to find the path.
 * @param source The starting node.
 * @param dest The destination node.
 * @param avoidNodes A set of nodes to avoid.
 * @param avoidSegments A set of edges to avoid.
 * @return A vector of node identifiers representing the restricted shortest path.
 *
 * @note Time Complexity: O((E + V) * log V), where E is the number of edges and V is the number of vertices. This complexity arises due to the priority queue and the node/segment avoidance checks.
 */
std::vector<std::string> dijkstraRestricted(Graph& g, const std::string& source, const std::string& dest,
    const std::set<std::string>& avoidNodes,
    const std::set<std::pair<std::string, std::string>>& avoidSegments);

/**
 * @brief Computes the total driving time for a given path.
 *
 * Sums the driving times of all edges along the given path.
 *
 * @param g The graph containing the path.
 * @param path The sequence of nodes forming the path.
 * @return The total driving time.
 *
 * @note Time Complexity: O(n), where n is the number of segments in the path, as each segment is checked once.
 */
int calculateDrivingTime(Graph& g, const std::vector<std::string>& path);

/**
 * @brief Computes the total walking time for a given path.
 *
 * Sums the walking times of all edges along the given path.
 *
 * @param g The graph containing the path.
 * @param path The sequence of nodes forming the path.
 * @return The total walking time.
 *
 * @note Time Complexity: O(n), where n is the number of segments in the path, as each segment is checked once.
 */
int calculateWalkingTime(Graph& g, const std::vector<std::string>& path);

/**
 * @brief Computes the total length of a given path.
 *
 * Calculates the number of nodes or segments in the path.
 *
 * @param g The graph containing the path.
 * @param path The sequence of nodes forming the path.
 * @return The total path length.
 */
int calculatePathLength(Graph& g, const std::vector<std::string>& path);

/**
 * @brief Finds an eco-friendly route balancing walking and driving.
 *
 * Computes a route that minimizes driving while considering a maximum walking time and avoiding specific nodes and segments.
 *
 * @param g The graph in which to find the route.
 * @param source The starting node.
 * @param dest The destination node.
 * @param maxWalkTime The maximum allowable walking time.
 * @param avoidNodes A set of nodes to avoid.
 * @param avoidSegments A set of edges to avoid.
 * @param message A reference string to store messages about the route calculation.
 * @return A tuple containing the walking path, a message, and the driving path.
 *
 * @note Time Complexity: O((E + V) * log V + P * (E + V)), where E is the number of edges, V is the number of vertices, and P is the number of parking candidates. The complexity comes from running Dijkstra's algorithm multiple times for each parking candidate.
 */
std::tuple<std::vector<std::string>, std::string, std::vector<std::string>> findEcoRoute(
    Graph& g,
    const std::string& source,
    const std::string& dest,
    int maxWalkTime,
    const std::set<std::string>& avoidNodes,
    const std::set<std::pair<std::string, std::string>>& avoidSegments,
    std::string& message);
