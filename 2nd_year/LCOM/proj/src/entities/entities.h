/**
 * @file entities.h
 * @brief Entity management for player, bullets, enemies, and collisions.
 *
 * Declares structures and functions to handle player initialization and movement,
 * bullet firing and updates, enemy spawning and movement, and collision detection.
 *
 * Used in the main game loop to manage interactions and gameplay logic.
 *
 * @author Grupo 2leic13_5
 * @version 0.1
 * @date 2025-05-30
 */

#ifndef ENTITIES_H
#define ENTITIES_H

#include <stdbool.h>
#include <lcom/lcf.h>
#include "../devices/video.h"
#include "xpm/pixmap.h"
#include <stdlib.h>
#include "../devices/timer.h"
#include "score.h"

/**
 * @brief Structure representing the player entity.
 */
typedef struct {
    int x, y;
    int width, height;
    int speed;
    bool active;
} Player;

/**
 * @brief Structure representing a bullet entity.
 */
typedef struct {
    int x, y;
    int speed;
} Bullet;

/**
 * @brief Structure representing an enemy entity.
 */
typedef struct {
    int x, y;
    int width, height;
    int speed;
} Enemy;

// Player

/**
 * @brief Initializes the player entity with default values.
 * @param player Pointer to the player to initialize.
 */
void player_init(Player *player);

/**
 * @brief Updates the player's position based on input.
 * @param player Pointer to the player to update.
 */
void player_update(Player *player);

/**
 * @brief Renders the player on screen.
 * @param player Pointer to the player to draw.
 */
void player_draw(const Player *player);

// Bullet

/**
 * @brief Initializes the bullet list.
 */
void bullet_init();

/**
 * @brief Updates the positions of all active bullets.
 */
void bullet_update();

/**
 * @brief Renders all active bullets on screen.
 */
void bullet_draw();

/**
 * @brief Fires a bullet from the specified position.
 * @param x X position to fire from.
 * @param y Y position to fire from.
 */
void bullet_fire(int x, int y);

// Enemy

/**
 * @brief Initializes the enemy list.
 */
void enemy_init();

/**
 * @brief Spawns new enemies and updates their positions.
 */
void enemy_update();

/**
 * @brief Renders all enemies on screen.
 */
void enemy_draw();

// Collision

/**
 * @brief Checks and handles collisions between bullets, enemies, and the player.
 * @param player Pointer to the player for collision detection.
 */
void check_collisions(Player *player);

#endif

