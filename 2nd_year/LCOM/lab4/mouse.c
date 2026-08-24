#include "mouse.h"
#include "KBC.h"

int hook_id_mouse = 2;
uint8_t mouse_byte = 0;
struct packet mouse_packet;
int byte_counter = 0;

void (mouse_ih)(){
  read_KBC_output(KBC_OUT_CMD, &mouse_byte, 1);
}

void (mouse_packets)(){
  mouse_packet.bytes[byte_counter] = mouse_byte;
  byte_counter++;
  if(byte_counter == 1){
    mouse_packet.lb = mouse_byte & MOUSE_LB_PACKET;
    mouse_packet.rb = mouse_byte & MOUSE_RB_PACKET;
    mouse_packet.mb = mouse_byte & MOUSE_MB_PACKET;
    mouse_packet.x_ov = mouse_byte & MOUSE_X_OV;
    mouse_packet.y_ov = mouse_byte & MOUSE_Y_OV;
  }
  else if (byte_counter == 2){
    mouse_packet.delta_x = mouse_byte;
    mouse_packet.delta_x = (mouse_packet.bytes[0] & MSB_X_DELTA) ? (mouse_packet.bytes[1] | 0xFF00) : mouse_byte;
  }
  else {
    mouse_packet.delta_y = mouse_byte;
    mouse_packet.delta_y = (mouse_packet.bytes[0] & MSB_Y_DELTA) ? (mouse_packet.bytes[2] | 0xFF00) : mouse_byte;
    byte_counter = 0;
  }  
  
}

int (mouse_subscribe_int) (uint8_t *bit_no){
    if (bit_no == NULL) return 1;
    *bit_no = BIT(hook_id_mouse);
    return sys_irqsetpolicy(IRQ_MOUSE, IRQ_REENABLE | IRQ_EXCLUSIVE, &hook_id_mouse);
}

int (mouse_unsubscribe_int) (){
  return sys_irqrmpolicy(&hook_id_mouse);
}

int (write_to_mouse)(uint8_t commandByte){
  int k = 0;
  while (k < 10) {
    if (write_KBC_command(KBC_IN_CMD, MOUSE_REQUEST, 1) != 0) return 1;
    if (write_KBC_command(KBC_WRITE_CMD, commandByte, 1) != 0) return 1;
    uint8_t readBackCommand;
    tickdelay(micros_to_ticks(DELAY_US));
    if (util_sys_inb(KBC_OUT_CMD, &readBackCommand) != 0) return 1; 
    if (readBackCommand == SUCESSS_BYTE) return 0;
    tickdelay(micros_to_ticks(DELAY_US));
    k++;
  }
  return 1;
}
