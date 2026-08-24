#include "graph.h"
#include <iostream>

/**
 * @brief Adds an edge between two nodes (bidirectional) with driving and walking times.
 *
 * This function creates an edge in both directions between the specified nodes
 * and associates the given driving and walking times with the edge.
 *
 * @param from The starting node of the edge.
 * @param to The ending node of the edge.
 * @param drivingTime The time taken to drive from 'from' to 'to'.
 * @param walkingTime The time taken to walk from 'from' to 'to'.
 *
 * @note Time Complexity: O(1), as adding an edge to the adjacency list is a constant-time operation.
 */
void Graph::addEdge(const string& from, const string& to, int drivingTime, int walkingTime) {
    EdgeData data = { drivingTime, walkingTime };     // Cria o objeto com os tempos
    adj[from].push_back({to, data});          // Adiciona a aresta do nó 'from' para 'to'
    adj[to].push_back({from, data});          // Adiciona a aresta inversa (grafo bidirecional)
}

/**
 * @brief Returns the adjacency list of the graph.
 *
 * This function provides access to the internal representation of the graph,
 * which is stored as a map of nodes to their respective edges.
 *
 * @return A constant reference to a map where each key is a node and the value
 *         is a vector of pairs representing the connected nodes and their edge data.
 *
 * @note Time Complexity: O(1), as accessing the adjacency list is a constant-time operation.
 */
const map<string, vector<pair<string, EdgeData>>>& Graph::getAdjacencyList() const {
    return adj;  // Devolve o mapa onde estão guardadas as ligações de cada nó
}
