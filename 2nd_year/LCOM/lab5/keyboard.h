#ifndef _LCOM_KEYBOARD_H_
#define _LCOM_KEYBOARD_H_

#include <lcom/lcf.h>

#include <minix/sysutil.h>
#include <lcom/lcf.h>
#include "i8042.h"

int (keyboard_subscribe_interrupts)(uint8_t *bit_no);
int (keyboard_unsubscribe_interrupts)();

int (enableInterrupt)();
int (checking_scancode)();

#endif
