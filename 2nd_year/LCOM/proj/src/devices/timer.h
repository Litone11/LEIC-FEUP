/**
 * @file timer.h
 * @brief Timer interface for configuring and handling the i8254 timer on Minix.
 * 
 * Provides functions to set timer frequency, subscribe/unsubscribe interrupts,
 * handle timer ticks, query and display configuration, and track timed updates.
 * 
 * @author Grupo 2leic13_5
 * @version 0.1
 * @date 2025-05-30
 */

#ifndef TIMER_H
#define TIMER_H

#include <lcom/lcf.h>
#include <stdint.h>
#include <lcom/timer.h>

/**
 * @brief Sets the frequency of a given timer.
 *
 * @param timer Timer to configure (0, 1, or 2).
 * @param freq Desired frequency.
 * @return 0 on success, non-zero otherwise.
 */
int (timer_set_frequency)(uint8_t timer, uint32_t freq);

/**
 * @brief Subscribes timer interrupts.
 *
 * @param bit_no Pointer to bit number to be set.
 * @return 0 on success, non-zero otherwise.
 */
int (timer_subscribe_int)(uint8_t *bit_no);

/**
 * @brief Unsubscribes timer interrupts.
 *
 * @return 0 on success, non-zero otherwise.
 */
int (timer_unsubscribe_int)();

/**
 * @brief Timer interrupt handler.
 */
void (timer_int_handler)();

/**
 * @brief Gets the configuration of a timer.
 *
 * @param timer Timer to query (0, 1, or 2).
 * @param st Pointer to store the status byte.
 * @return 0 on success, non-zero otherwise.
 */
int (timer_get_conf)(uint8_t timer, uint8_t *st);

/**
 * @brief Displays the configuration of a timer.
 *
 * @param timer Timer to display.
 * @param st Status byte.
 * @param field Field to interpret.
 * @return 0 on success, non-zero otherwise.
 */
int (timer_display_conf)(uint8_t timer, uint8_t st, enum timer_status_field field);

/**
 * @brief Increments the timer update counter.
 * 
 * This function updates the global `timer_update` variable that may be used
 * to track timed events or elapsed time.
 * 
 * @param update Value to increment the timer update counter.
 */
void (timer_increment_update)();

#endif // TIMER_H

