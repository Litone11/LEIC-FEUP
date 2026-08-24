#ifndef GRAPH_HPP
#define GRAPH_HPP

#include <string>
#include <vector>
#include <map>
#include <utility>

using namespace std;

/**
 * @class Graph
 * @brief Represents a directed graph with weighted edges.
 *
 * The Graph class stores nodes and edges, where each edge has a driving time and a walking time.
 */
struct EdgeData {
    int drivingTime;
    int walkingTime;
};

class Graph {
private:
    map<string, vector<pair<string, EdgeData>>> adj;

public:
    /**
     * @brief Adds an edge to the graph.
     *
     * Adds a directed edge from one node to another with specified driving and walking times.
     *
     * @param from The starting node of the edge.
     * @param to The destination node of the edge.
     * @param drivingTime The time required to drive from `from` to `to`.
     * @param walkingTime The time required to walk from `from` to `to`.
     */
    void addEdge(const string& from, const string& to, int drivingTime, int walkingTime);

    /**
     * @brief Retrieves the adjacency list of the graph.
     *
     * Provides a reference to the internal adjacency list structure, representing all nodes and edges.
     *
     * @return A const reference to the adjacency list map.
     */
    const map<string, vector<pair<string, EdgeData>>>& getAdjacencyList() const;

    /**
     * @brief Prints the graph structure.
     *
     * Outputs the graph's adjacency list to the console for visualization.
     *
     * @note Time Complexity: O(n), where n is the number of nodes in the graph, as each node and its edges are printed.
     */
    void printGraph() const;
};

#endif
