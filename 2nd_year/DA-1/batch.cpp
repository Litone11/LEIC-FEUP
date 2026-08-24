#include "batch.h"
#include "parser.h"
#include "route.h"
#include <fstream>
#include <sstream>
#include <iostream>
#include <algorithm>

using namespace std;

// Variáveis globais que vêm do main

/**
 * @brief Processes a batch file containing various routing operations.
 *
 * Reads a batch file and performs operations such as finding the best route, an alternative route, restricted routes, and eco-friendly routes.
 * The results are written to an output file.
 *
 * @param g The graph representing the locations and edges.
 * @param inputPath The path to the input batch file.
 * @param outputPath The path to the output file where results will be written.
 *
 * @note Time Complexity: O(n * m), where n is the number of locations and m is the number of operations in the batch file.
 */
extern vector<Location> locations;
extern vector<Edge> edges;

void processBatchFile(Graph& g, const string& inputPath, const string& outputPath) {
    // Abrir ficheiros de input e output
    ifstream input(inputPath);
    ofstream output(outputPath);
    if (!input.is_open() || !output.is_open()) {
        cerr << "Erro ao abrir ficheiros." << endl;
        return;
    }

    // Variáveis de controlo de dados lidos do input
    string line;
    string mode;
    int sourceId = -1, destId = -1, includeNodeId = -1, maxWalkTime = -1;
    set<int> avoidNodeIds;
    set<pair<int, int>> avoidSegmentIds;

    // Leitura do ficheiro linha a linha
    while (getline(input, line)) {
        if (line.find("Mode:") == 0)
            mode = line.substr(5);  // Modo de operação (driving, restricted, walking)
        else if (line.find("Source:") == 0)
            sourceId = stoi(line.substr(7)); // ID origem
        else if (line.find("Destination:") == 0)
            destId = stoi(line.substr(12)); // ID destino
        else if (line.find("IncludeNode:") == 0 && line.size() > 12)
            includeNodeId = stoi(line.substr(12)); // Nó obrigatório
        else if (line.find("MaxWalkTime:") == 0 && line.size() > 12)
            maxWalkTime = stoi(line.substr(12)); // Tempo máximo a pé
        else if (line.find("AvoidNodes:") == 0 && line.size() > 11) {
            // Leitura de nós a evitar
            stringstream ss(line.substr(11));
            string id;
            while (getline(ss, id, ',')) {
                if (!id.empty()) avoidNodeIds.insert(stoi(id));
            }
        } else if (line.find("AvoidSegments:") == 0 && line.size() > 14) {
            // Leitura de segmentos a evitar (pares de IDs no formato (id1,id2))
            string segmentsStr = line.substr(14);
            stringstream ss(segmentsStr);
            string pairStr;
            while (getline(ss, pairStr, ')')) {
                size_t open = pairStr.find('(');
                if (open != string::npos) {
                    pairStr = pairStr.substr(open + 1);
                    stringstream pairStream(pairStr);
                    string id1Str, id2Str;
                    if (getline(pairStream, id1Str, ',') && getline(pairStream, id2Str)) {
                        int id1 = stoi(id1Str), id2 = stoi(id2Str);
                        avoidSegmentIds.insert({id1, id2});
                        avoidSegmentIds.insert({id2, id1}); // guardar também inverso
                    }
                }
            }
        }
    }

    // Converte os IDs para códigos
    string sourceCode = getCodeById(locations, sourceId);
    string destCode = getCodeById(locations, destId);
    string includeCode = (includeNodeId != -1) ? getCodeById(locations, includeNodeId) : "";

    // Converte IDs de nós a evitar para códigos
    set<string> avoidCodes;
    for (int id : avoidNodeIds)
        avoidCodes.insert(getCodeById(locations, id));

    // Converte segmentos proibidos para códigos
    set<pair<string, string>> avoidSegmentCodes;
    for (const auto& [id1, id2] : avoidSegmentIds) {
        avoidSegmentCodes.insert({getCodeById(locations, id1), getCodeById(locations, id2)});
    }

    // Escreve os dados base no output
    output << "Source:" << sourceId << "\nDestination:" << destId << "\n";

    //  Funcionalidade 1 e 2: Melhor rota e rota alternativa
    if (mode == "driving") {
        auto path = dijkstraShortestPath(g, sourceCode, destCode);
        auto alt = findAlternativeRoute(g, sourceCode, destCode, path);
        int t1 = calculateDrivingTime(g, path);
        int t2 = calculateDrivingTime(g, alt);

        // Melhor rota
        output << "BestDrivingRoute:";
        if (path.empty()) output << "none\n";
        else {
            for (size_t i = 0; i < path.size(); ++i) {
                output << getIdByCode(locations, path[i]);
                if (i < path.size() - 1) output << ",";
            }
            output << "(" << t1 << ")\n";
        }

        // Alternativa
        output << "AlternativeDrivingRoute:";
        if (alt.empty()) output << "none\n";
        else {
            for (size_t i = 0; i < alt.size(); ++i) {
                output << getIdByCode(locations, alt[i]);
                if (i < alt.size() - 1) output << ",";
            }
            output << "(" << t2 << ")\n";
        }

    //  Rota com restrições
    } else if (mode == "driving-restricted") {
        vector<string> path;
        if (!includeCode.empty()) {
            // Se houver nó obrigatório, divide o percurso em duas partes
            auto p1 = dijkstraRestricted(g, sourceCode, includeCode, avoidCodes, avoidSegmentCodes);
            auto p2 = dijkstraRestricted(g, includeCode, destCode, avoidCodes, avoidSegmentCodes);
            if (!p1.empty() && !p2.empty()) {
                p1.pop_back(); // evita duplicação
                path = p1;
                path.insert(path.end(), p2.begin(), p2.end());
            }
        } else {
            // Caso contrário faz o caminho direto com restrições
            path = dijkstraRestricted(g, sourceCode, destCode, avoidCodes, avoidSegmentCodes);
        }

        // Escreve resultado
        output << "RestrictedDrivingRoute:";
        if (path.empty()) output << "none\n";
        else {
            for (size_t i = 0; i < path.size(); ++i) {
                output << getIdByCode(locations, path[i]);
                if (i < path.size() - 1) output << ",";
            }
            output << "(" << calculateDrivingTime(g, path) << ")\n";
        }

    //  Eco-route
    } else if (mode == "driving-walking") {
        string message;

        // tenta encontrar melhor parque com base em critérios
        auto [drivePath, parking, walkPath] = findEcoRoute(g, sourceCode, destCode, maxWalkTime, avoidCodes, avoidSegmentCodes, message);

        // se falhar
        if (drivePath.empty() || walkPath.empty()) {
            output << "DrivingRoute:none\nParkingNode:none\nWalkingRoute:none\nTotalTime:\n";
            output << "Message:" << message << "\n";
        } else {
            // caso nao falhe
            int driveTime = calculateDrivingTime(g, drivePath);
            int walkTime = calculateWalkingTime(g, walkPath);

            output << "DrivingRoute:";
            for (size_t i = 0; i < drivePath.size(); ++i) {
                output << getIdByCode(locations, drivePath[i]);
                if (i < drivePath.size() - 1) output << ",";
            }
            output << "(" << driveTime << ")\n";

            output << "ParkingNode:" << getIdByCode(locations, parking) << "\n";

            output << "WalkingRoute:";
            for (size_t i = 0; i < walkPath.size(); ++i) {
                output << getIdByCode(locations, walkPath[i]);
                if (i < walkPath.size() - 1) output << ",";
            }
            output << "(" << walkTime << ")\n";

            output << "TotalTime:" << (driveTime + walkTime) << "\n";
        }
    }
}
