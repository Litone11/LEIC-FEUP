/**
 * @file keyboard.h
 * @brief Functions and definitions for handling keyboard interrupts and scancodes.
 * 
 * Provides declarations for subscribing/unsubscribing keyboard interrupts,
 * enabling keyboard interrupt handling via the KBC, and interpreting scancodes.
 * 
 * @author Grupo 2leic13_5
 * @version 0.1
 * @date 2025-05-30
 */

#ifndef _LCOM_KEYBOARD_H_
#define _LCOM_KEYBOARD_H_

#include <lcom/lcf.h>
#include <minix/sysutil.h>
#include "i8042.h"

/**
 * @brief Latest scancode read from the keyboard interrupt handler.
 */
extern uint8_t scancode;

/**
 * @brief Subscribes keyboard interrupts.
 * 
 * Sets the policy to receive interrupts from the keyboard.
 * 
 * @param bit_no Pointer to store the bit number to be set in the interrupt mask.
 * @return 0 on success, 1 on failure.
 */
int (keyboard_subscribe_interrupts)(uint8_t *bit_no);

/**
 * @brief Unsubscribes keyboard interrupts.
 * 
 * Removes the policy to stop receiving keyboard interrupts.
 * 
 * @return 0 on success, 1 on failure.
 */
int (keyboard_unsubscribe_interrupts)();

/**
 * @brief Enables keyboard interrupts in the KBC.
 * 
 * Reads the current command byte, sets the interrupt-enable bit, and writes it back.
 * 
 * @return 0 on success, 1 on failure.
 */
int (enableInterrupt)();

/**
 * @brief Interprets the current scancode.
 * 
 * Determines if the current scancode is a single-byte or part of a multi-byte sequence.
 * Stores the scancode in a buffer accordingly.
 * 
 * @return 
 * - 2 if a two-byte scancode has been fully read,
 * - 1 if a single-byte scancode has been read,
 * - 0 if the first byte of a two-byte scancode has been read.
 */
int (checking_scancode)();

#endif /* _LCOM_KEYBOARD_H_ */
