/**
 * @file game.h
 * @brief Game state controller and main loop handler.
 *
 * Declares the GameState enum and core functions for initializing and updating
 * the main game logic, including transitions between menu, info, playing, and exit states.
 * Used to manage the high-level game flow and central control loop.
 *
 * @author Grupo 2leic13_5
 * @version 0.1
 * @date 2025-05-30
 */

#ifndef GAME_H
#define GAME_H

#include <stdbool.h>
#include "devices/keyboard.h"
#include "menu_state.h"
#include "info_state.h"
#include "menu.h"
#include "devices/video.h"
#include "entities/entities.h"

/**
 * @brief Enum representing the current state of the game.
 */
typedef enum GameState {
    STATE_MENU,    /**< Menu state */
    STATE_INFO,    /**< Info screen state */
    STATE_PLAYING, /**< Main gameplay state */
    STATE_EXIT     /**< Exit state */
} GameState;

/**
 * @brief Global variable tracking the current game state.
 */
GameState current_state;

/**
 * @brief Initializes game components and sets initial game state.
 */
void game_init();

/**
 * @brief Updates game logic based on the current state (menu, info, playing, exit).
 */
void game_update();

#endif // GAME_H

