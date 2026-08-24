/**
 * @file KBC.h
 * @brief Keyboard Controller (KBC) interface header for handling communication with the i8042 controller.
 * 
 * Provides function declarations for reading and writing to the KBC,
 * including interaction with both keyboard and mouse through the i8042 controller.
 * 
 * @author Grupo 2leic13_5
 * @version 0.1
 * @date 2025-05-30
 * @copyright Copyright (c) 2025
 */
#ifndef _LCOM_KBC_H_
#define _LCOM_KBC_H_

#include <minix/sysutil.h>
#include "i8042.h"
#include <lcom/lcf.h>

/**
 * @brief Keyboard interrupt handler.
 * 
 * Reads the scancode from the KBC output buffer and stores it for further processing.
 */
void (kbc_ih)(void);

/**
 * @brief To print the scancodes
 * 
 * Prints the scancodes via printf
 * Provided via the LCF -- no need to implement it
 * 
 * @param make Whether this is a make or a break code
 * @param size Size in bytes of the scancode
 * @param bytes Array with size elements, with the scancode bytes
 * 
 * @return Return 0 upon success and non-zero otherwise
 */

/**
 * @brief Reads the status register from the KBC.
 * 
 * @param status Pointer to store the status byte read from the KBC.
 * @return 0 on success, non-zero otherwise.
 */
int (read_KBC_status)(uint8_t* status);

/**
 * @brief Reads data from the KBC output buffer.
 * 
 * @param port The port to read from (usually 0x60).
 * @param output Pointer to store the byte read.
 * @param mouse Boolean flag indicating if the read is for the mouse (1) or keyboard (0).
 * @return 0 on success, non-zero otherwise.
 */
int (read_KBC_output)(uint8_t port, uint8_t *output, int mouse);

/**
 * @brief Writes a command byte to the KBC.
 * 
 * @param port The port to write to (usually 0x64 for commands).
 * @param commandByte The command byte to write.
 * @param mouse Boolean flag indicating if the command is for the mouse (1) or keyboard (0).
 * @return 0 on success, non-zero otherwise.
 */
int (write_KBC_command)(uint8_t port, uint8_t commandByte, int mouse);

#endif /* _LCOM_KBC_H_ */
