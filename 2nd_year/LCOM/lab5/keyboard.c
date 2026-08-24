#include "keyboard.h"
#include "KBC.h"


uint8_t scancode = 0;
int hook_id_keyboard = 1;
uint8_t scancodes[2] ;
bool is_second_byte = false;


int (keyboard_subscribe_interrupts)(uint8_t *bit_no) {
    if (bit_no == NULL) return 1;
    *bit_no = BIT(hook_id_keyboard);
    return sys_irqsetpolicy(IRQ_KEYBOARD, IRQ_REENABLE | IRQ_EXCLUSIVE, &hook_id_keyboard);
}

int (keyboard_unsubscribe_interrupts)() {
    return sys_irqrmpolicy(&hook_id_keyboard);
}

int (enableInterrupt)(){
  uint8_t status;
  if (write_KBC_command(KBC_IN_CMD, KBC_READ_CMD) != 0) return 1;    
  if (read_KBC_output(KBC_OUT_CMD, &status, 0) != 0) return 1;
  if (write_KBC_command(KBC_IN_CMD, KBC_WRITE_CMD) != 0) return 1;   
  return (write_KBC_command(KBC_IN_CMD,status| BIT(0)) != 0);

  //Read Use KBC command 0x20, which must be written to 0x64
  //▶ But the value of the “command byte” must be read from
  //0x60
  //Write Use KBC command 0x60, which must be written to 0x64
  //▶ But the new value of the “command byte” must be
  //written to 0x60
}

int (checking_scancode)(){
    if(is_second_byte){
        scancodes[1] = scancode;
        is_second_byte = false;
        return 2;
    }
    else if(scancode != FIRST_TWO_BYTES){
        scancodes[0] = scancode;
        is_second_byte = false;
        return 1;
    }
    else{
        scancodes[0] = scancode;
        is_second_byte = true;
        return 0;
    }
}
