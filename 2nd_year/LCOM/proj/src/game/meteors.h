/**
 * @file meteors.h
 * @brief Handles meteor entity logic including spawning, updating, and removal.
 *
 * Declares functions and variables used to manage falling meteors on screen.
 * Supports timed spawning, downward movement, and memory cleanup.
 *
 * This module is used in gameplay to add obstacles dynamically.
 *
 * @author Grupo 2leic13_5
 * @version 0.1
 * @date 2025-05-30
 */

#ifndef _METEORS_H_
#define _METEORS_H_
#include <lcom/lcf.h>
#include"../devices/video.h"
#include "../alive/object.h"
#include "../pixmap.h"
#include "../game.h"

#define MAX_METEORS 20
extern vbe_mode_info_t mode_info;
extern Object *meteors[MAX_METEORS];
extern int meteor_count;

extern int create_rate;
extern int decrease_rate;
extern int min_create_rate;

/**
 * @brief Initializes the meteor system, resets all counters and meteors.
 */
void init_meteor_system();

/**
 * @brief Checks whether a new meteor should spawn at the current time.
 * @param current_time Global time counter used to control spawn rate.
 * @return 1 if it's time to spawn a meteor, 0 otherwise.
 */
int check_meteor_spawn(int current_time);

/**
 * @brief Spawns a new meteor object if space allows and updates spawn rate.
 */
void spawn_meteor();

/**
 * @brief Updates position of all active meteors and removes those off screen.
 */
void update_meteors();

/**
 * @brief Handles meteor lifecycle: checks spawn timing and updates all meteors.
 * @param current_time Global time counter for determining spawn events.
 */
void handle_meteors(int current_time);

#endif /* _METEORS_H_ */
