/**
 * @file info_state.h
 * @brief Handles the "Info" state of the game, displaying information screen.
 * 
 * Declares functions to initialize, update, draw, and start the information screen state,
 * shown between gameplay and menu transitions.
 * 
 * @author Grupo 2leic13_5
 * @version 0.1
 * @date 2025-05-30
 */

#ifndef INFO_STATE_H
#define INFO_STATE_H

#include <stdbool.h>
#include <lcom/lcf.h>
#include "game.h" 
#include "devices/keyboard.h"
#include "devices/video.h"
#include "xpm/pixmap.h"

extern uint8_t scancode;

extern bool start_game;
extern bool exit_game;

/**
 * @brief Initializes the info state (flags, state tracking).
 */
void info_state_init();

/**
 * @brief Updates the info state, e.g., checks for ESC to exit.
 */
void info_state_update();

/**
 * @brief Draws the info screen including background, title, and content.
 */
void info_state_draw();

/**
 * @brief Starts the info state by initializing and drawing it.
 */
void info_state_start();

#endif
