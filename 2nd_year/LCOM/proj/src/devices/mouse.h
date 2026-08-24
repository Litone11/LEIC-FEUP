/**
 * @file mouse.h
 * @brief Mouse driver interface for handling mouse interrupts and data packets.
 * 
 * Provides function declarations for configuring the mouse, reading/writing through the KBC,
 * subscribing to mouse interrupts, interpreting packets, and updating cursor position.
 * 
 * @author Grupo 2leic13_5
 * @version 0.1
 * @date 2025-05-30
 */

#ifndef _MOUSE_
#define _MOUSE_

#include <minix/sysutil.h>
#include "i8042.h"
#include <lcom/lcf.h>
#include <stdint.h>
#include <stdio.h>

extern int x;
extern int y;

/**
 * @brief Sends a command to the mouse.
 * 
 * @param controlWord The command byte to be sent to the mouse.
 * @return 0 on success, 1 on failure.
 */
int (mouse_config)(uint8_t controlWord);

/**
 * @brief Writes a byte to the KBC command or argument port.
 * 
 * @param port The port to write to.
 * @param controlWord The byte to send.
 * @return 0 on success, 1 on failure.
 */
int (KBCWrite)(uint8_t port, uint8_t controlWord);

/**
 * @brief Reads a byte from the KBC output buffer.
 * 
 * @param port The port to read from.
 * @param output Pointer to store the byte read.
 * @return 0 on success, 1 on failure.
 */
int (KBCRead)(uint8_t port, uint32_t *output);

/**
 * @brief Subscribes mouse interrupts.
 * 
 * @param bit_no Pointer to store the bit mask for the mouse IRQ.
 * @return 0 on success, 1 on failure.
 */
int (mouse_subscribe_int)(uint8_t *bit_no);

/**
 * @brief Mouse interrupt handler.
 * 
 * Reads a byte from the KBC related to mouse activity.
 */
void (mouse_ih)();

/**
 * @brief Unsubscribes mouse interrupts.
 * 
 * @return 0 on success, 1 on failure.
 */
int (mouse_unsubscribe)();

/**
 * @brief Synchronizes mouse packet bytes.
 * 
 * Fills the mouse packet buffer with the 3 bytes received from the mouse.
 * @return 0 when synchronization proceeds.
 */
int (mouse_sync_bytes)();

/**
 * @brief Parses the 3-byte mouse packet into a structured format.
 * 
 * Interprets button states and movement deltas.
 * @return 0 on success.
 */
int (mouse_bytes_to_packet)();

/**
 * @brief Updates global cursor position using the mouse packet data.
 */
void updateMouseLocation();

/**
 * @brief Checks if the left mouse button is currently pressed.
 * 
 * @return true if pressed, false otherwise.
 */
bool processLeftClick();

#endif /* _MOUSE_ */
