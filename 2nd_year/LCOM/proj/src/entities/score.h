/**
 * @file score.h
 * @brief Score management module for the game.
 *
 * Provides functions for updating, resetting, retrieving, and drawing the game score.
 * Also declares the global score variable used throughout the game.
 *
 * @author Grupo 2leic13_5
 * @version 0.1
 * @date 2025-05-30
 */

#ifndef SCORE_H
#define SCORE_H
#include <stdint.h>
#include <lcom/lcf.h>
#include <stdint.h>
#include "../devices/i8042.h" 
#include "xpm/font.h"
#include "xpm/pixmap.h"
#include "../devices/video.h"
uint32_t score; 

/**
 * @brief Increments the current score by a specified amount.
 * @param points The number of points to add (can be negative).
 */
void inc_score(uint32_t points);

/**
 * @brief Resets the current score to zero.
 */
void reset_score();

/**
 * @brief Retrieves the current score.
 * @return The current score as a 32-bit unsigned integer.
 */
uint32_t get_score();

/**
 * @brief Renders the score on screen using text and background graphics.
 */
void score_draw();

#endif // SCORE_H

