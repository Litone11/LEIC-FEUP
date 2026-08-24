#include "route.h"
#include "parser.h"
#include <queue>
#include <map>
#include <set>
#include <climits>
#include <algorithm>
#include <iostream>

using namespace std;
extern vector<Location> locations; // Acede à lista global de locais

/**
 * @brief Computes the shortest path using Dijkstra's algorithm.
 *
 * @param g The graph representing the locations and edges.
 * @param source The starting location for the path.
 * @param dest The destination location for the path.
 * @return A vector of strings representing the locations in the shortest path, or an empty vector if no path exists.
 *
 * @note Time Complexity: O((E + V) * log V), where E is the number of edges and V is the number of vertices. This complexity arises from the priority queue used in Dijkstra's algorithm.
 */
vector<string> dijkstraShortestPath(Graph& g, const string& source, const string& dest) {
    auto adj = g.getAdjacencyList();
    map<string, int> dist;
    map<string, string> prev;
    set<string> visited;

    for (const auto& node : adj)
        dist[node.first] = INT_MAX;

    dist[source] = 0;
    priority_queue<pair<int, string>, vector<pair<int, string>>, greater<>> pq;
    pq.push({0, source});

    while (!pq.empty()) {
        auto [d, u] = pq.top(); pq.pop();
        if (visited.count(u)) continue;
        visited.insert(u);

        for (const auto& [v, edge] : adj[u]) {
            if (edge.drivingTime == -1) continue; // ignora se não há tempo de condução
            if (dist[v] > dist[u] + edge.drivingTime) {
                dist[v] = dist[u] + edge.drivingTime;
                prev[v] = u;
                pq.push({dist[v], v});
            }
        }
    }

    if (!prev.count(dest)) return {}; // caminho impossível

    vector<string> path;
    for (string at = dest; at != source; at = prev[at])
        path.push_back(at);
    path.push_back(source);
    reverse(path.begin(), path.end());
    return path;
}

/**
 * @brief Finds an alternative route that avoids the main path.
 *
 * @param g The graph representing the locations and edges.
 * @param source The starting location for the path.
 * @param dest The destination location for the path.
 * @param mainPath The main path to avoid.
 * @return A vector of strings representing the alternative route, or an empty vector if no route exists.
 *
 * @note Time Complexity: O((E + V) * log V), where E is the number of edges and V is the number of vertices. This is because the algorithm uses a modified Dijkstra's approach.
 */
vector<string> findAlternativeRoute(Graph& g, const string& source, const string& dest, const vector<string>& mainPath) {
    auto adj = g.getAdjacencyList();

    // Evita todos os nós intermédios da rota principal
    set<string> forbiddenNodes(mainPath.begin() + 1, mainPath.end() - 1);

    // Evita os segmentos da rota principal (bidirecional)
    set<pair<string, string>> forbiddenEdges;
    for (size_t i = 0; i < mainPath.size() - 1; ++i) {
        forbiddenEdges.insert({mainPath[i], mainPath[i+1]});
        forbiddenEdges.insert({mainPath[i+1], mainPath[i]});
    }

    map<string, int> dist;
    map<string, string> prev;
    set<string> visited;

    for (const auto& node : adj)
        dist[node.first] = INT_MAX;

    dist[source] = 0;
    priority_queue<pair<int, string>, vector<pair<int, string>>, greater<>> pq;
    pq.push({0, source});

    while (!pq.empty()) {
        auto [d, u] = pq.top(); pq.pop();
        if (visited.count(u)) continue;
        visited.insert(u);

        for (const auto& [v, edge] : adj[u]) {
            if (edge.drivingTime == -1) continue;
            if (forbiddenNodes.count(v)) continue;
            if (forbiddenEdges.count({u, v}) || forbiddenEdges.count({v, u})) continue;

            if (dist[v] > dist[u] + edge.drivingTime) {
                dist[v] = dist[u] + edge.drivingTime;
                prev[v] = u;
                pq.push({dist[v], v});
            }
        }
    }

    if (!prev.count(dest)) return {};

    vector<string> path;
    for (string at = dest; at != source; at = prev[at])
        path.push_back(at);
    path.push_back(source);
    reverse(path.begin(), path.end());
    return path;
}

/**
 * @brief Calculates the total driving time for a given path.
 *
 * @param g The graph representing the locations and edges.
 * @param path A vector of strings representing the path for which driving time is to be calculated.
 * @return The total driving time in minutes, or -1 if any segment of the path is invalid.
 *
 * @note Time Complexity: O(n), where n is the number of segments in the path, as each segment is checked once.
 */
int calculateDrivingTime(Graph& g, const vector<string>& path) {
    if (path.size() < 2) return 0;

    int total = 0;
    auto adj = g.getAdjacencyList();

    for (size_t i = 0; i < path.size() - 1; ++i) {
        bool found = false;
        for (const auto& [v, edge] : adj[path[i]]) {
            if (v == path[i+1]) {
                if (edge.drivingTime == -1) return -1;
                total += edge.drivingTime;
                found = true;
                break;
            }
        }
        if (!found) return -1;
    }

    return total;
}

/**
 * @brief Calculates the total walking time for a given path.
 *
 * @param g The graph representing the locations and edges.
 * @param path A vector of strings representing the path for which walking time is to be calculated.
 * @return The total walking time in minutes, or -1 if any segment of the path is invalid.
 *
 * @note Time Complexity: O(n), where n is the number of segments in the path, as each segment is checked once.
 */
int calculateWalkingTime(Graph& g, const vector<string>& path) {
    if (path.size() < 2) return 0;

    int total = 0;
    auto adj = g.getAdjacencyList();

    for (size_t i = 0; i < path.size() - 1; ++i) {
        bool found = false;
        for (const auto& [v, edge] : adj[path[i]]) {
            if (v == path[i+1]) {
                if (edge.walkingTime == -1) return -1;
                total += edge.walkingTime;
                found = true;
                break;
            }
        }
        if (!found) return -1;
    }

    return total;
}

/**
 * @brief Computes a restricted route avoiding specified nodes and segments.
 *
 * @param g The graph representing the locations and edges.
 * @param source The starting location for the path.
 * @param dest The destination location for the path.
 * @param avoidNodes A set of nodes to avoid in the route.
 * @param avoidSegments A set of segments to avoid in the route.
 * @return A vector of strings representing the restricted route, or an empty vector if no route exists.
 *
 * @note Time Complexity: O((E + V) * log V), where E is the number of edges and V is the number of vertices. This complexity arises due to the priority queue and the node/segment avoidance checks.
 */
vector<string> dijkstraRestricted(Graph& g, const string& source, const string& dest,
                                  const set<string>& avoidNodes,
                                  const set<pair<string, string>>& avoidSegments) {
    auto adj = g.getAdjacencyList();
    map<string, int> dist;
    map<string, string> prev;
    set<string> visited;

    for (const auto& node : adj)
        dist[node.first] = INT_MAX;

    dist[source] = 0;
    priority_queue<pair<int, string>, vector<pair<int, string>>, greater<>> pq;
    pq.push({0, source});

    while (!pq.empty()) {
        auto [d, u] = pq.top(); pq.pop();
        if (visited.count(u)) continue;
        visited.insert(u);

        for (const auto& [v, edge] : adj[u]) {
            if (edge.drivingTime == -1) continue;
            if (avoidNodes.count(v)) continue;
            if (avoidSegments.count({u, v}) || avoidSegments.count({v, u})) continue;

            if (dist[v] > dist[u] + edge.drivingTime) {
                dist[v] = dist[u] + edge.drivingTime;
                prev[v] = u;
                pq.push({dist[v], v});
            }
        }
    }

    if (!prev.count(dest)) return {};

    vector<string> path;
    for (string at = dest; at != source; at = prev[at])
        path.push_back(at);
    path.push_back(source);
    reverse(path.begin(), path.end());
    return path;
}

/**
 * @brief Finds an eco-friendly route that minimizes driving and walking time.
 *
 * @param g The graph representing the locations and edges.
 * @param source The starting location for the route.
 * @param dest The destination location for the route.
 * @param maxWalkTime The maximum allowable walking time.
 * @param avoidNodes A set of nodes to avoid in the route.
 * @param avoidSegments A set of segments to avoid in the route.
 * @param message A reference to a string for outputting messages about the route findings.
 * @return A tuple containing the driving path, the best parking location, and the walking path.
 *
 * @note Time Complexity: O((E + V) * log V + P * (E + V)), where E is the number of edges, V is the number of vertices, and P is the number of parking candidates. The complexity comes from running Dijkstra's algorithm multiple times for each parking candidate.
 */
tuple<vector<string>, string, vector<string>> findEcoRoute(
    Graph& g,
    const string& source,
    const string& dest,
    int maxWalkTime,
    const set<string>& avoidNodes,
    const set<pair<string, string>>& avoidSegments,
    string& message)
{
    auto adj = g.getAdjacencyList();
    vector<string> parkingCandidates;

    // Recolher todos os locais com parque
    for (const auto& loc : locations) {
        if (loc.hasParking) {
            parkingCandidates.push_back(loc.code);
        }
    }

    if (parkingCandidates.empty()) {
        message = "No parking nodes available.";
        return {{}, "", {}};
    }

    string bestPark = "";
    int bestTotal = INT_MAX;
    int bestWalkTime = -1;
    vector<string> bestDrivePath, bestWalkPath;

    for (const auto& park : parkingCandidates) {
        if (avoidNodes.count(park)) continue;

        auto drivePath = dijkstraRestricted(g, source, park, avoidNodes, avoidSegments);
        auto walkPath = dijkstraRestricted(g, park, dest, avoidNodes, avoidSegments);

        if (drivePath.empty() || walkPath.empty()) continue;

        int driveTime = calculateDrivingTime(g, drivePath);
        int walkTime = calculateWalkingTime(g, walkPath);

        if (walkTime > maxWalkTime) continue;

        int total = driveTime + walkTime;

        // Escolher a rota com menor tempo total (desempate: maior caminhada)
        if (total < bestTotal || (total == bestTotal && walkTime > bestWalkTime)) {
            bestTotal = total;
            bestWalkTime = walkTime;
            bestPark = park;
            bestDrivePath = drivePath;
            bestWalkPath = walkPath;
        }
    }

    if (bestDrivePath.empty() || bestWalkPath.empty()) {
        message = "No viable eco route found.";
        return {{}, "", {}};
    }

    message = "Eco route found.";
    return {bestDrivePath, bestPark, bestWalkPath};
}
