#ifndef _LCOM_KBC_H_
#define _LCOM_KBC_H_

#include <minix/sysutil.h>
#include "i8042.h"
#include <lcom/lcf.h>

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

int (read_KBC_status)(uint8_t* status);

int (read_KBC_output)(uint8_t port, uint8_t *output, int mouse);

int (write_KBC_command)(uint8_t port, uint8_t commandByte, int mouse);

#endif /* _LCOM_KBC_H_ */
