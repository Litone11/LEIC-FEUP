/**
 * @file menu_state.h
 * @brief Handles the "Menu" state of the game.
 * 
 * Declares functions for initializing, updating, drawing, and starting the main menu state.
 * This state is used as the entry point of the game, offering options such as start, info, and exit.
 * 
 * @author Grupo 2leic13_5
 * @version 0.1
 * @date 2025-05-30
 */

#ifndef MENU_STATE_H
#define MENU_STATE_H

#include <stdbool.h>

/**
 * @brief Initializes the menu state by resetting control flags.
 */
void menu_state_init();

/**
 * @brief Updates menu state based on keyboard input (e.g., key 1 to start, ESC to exit).
 */
void menu_state_update();

/**
 * @brief Draws the menu screen including background and buttons.
 */
void menu_state_draw();

/**
 * @brief Starts the menu state by initializing and rendering it.
 */
void menu_state_start();

#endif // MENU_STATE_H
