#ifndef BATCH_HPP
#define BATCH_HPP

#include <string>
#include "graph.h"

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
void processBatchFile(Graph& g, const std::string& inputPath, const std::string& outputPath);

#endif
