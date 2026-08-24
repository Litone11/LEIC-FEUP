// IMPORTANT: you must include the following line in all your C files
#include <lcom/lcf.h>
#include <video.h>
#include <lcom/lab5.h>
#include <lcom/pixmap.h>

#include "keyboard.h"

#include <stdint.h>
#include <stdio.h>

#define XPM_INDEXED 0x105
extern uint8_t scancode;
// Any header files included below this line should have been created by you

int main(int argc, char *argv[]) {
  // sets the language of LCF messages (can be either EN-US or PT-PT)
  lcf_set_language("EN-US");

  // enables to log function invocations that are being "wrapped" by LCF
  // [comment this out if you don't want/need it]
  lcf_trace_calls("/home/lcom/labs/lab5/trace.txt");

  // enables to save the output of printf function calls on a file
  // [comment this out if you don't want/need it]
  lcf_log_output("/home/lcom/labs/lab5/output.txt");

  // handles control over to LCF
  // [LCF handles command line arguments and invokes the right function]
  if (lcf_start(argc, argv))
    return 1;

  // LCF clean up tasks
  // [must be the last statement before return]
  lcf_cleanup();

  return 0;
}

int (waiting_ESC_key)() {

  int ipc_status;
  message msg;
  uint8_t keyboard_mask;

  if (keyboard_subscribe_interrupts(&keyboard_mask) != 0) return 1;

  while (scancode != ESC_BREAKCODE){
    if (driver_receive(ANY, &msg, &ipc_status) != 0) { 
      printf("driver_receive failed");
      continue;
    }
    if (is_ipc_notify(ipc_status)) {
      switch (_ENDPOINT_P(msg.m_source)) {
        case HARDWARE: 
          if (msg.m_notify.interrupts & keyboard_mask) 
            kbc_ih();
            break;
        default:
          break; 
      }
    }
  }

  if (keyboard_unsubscribe_interrupts() != 0) return 1;
  return 0;
}

int(video_test_init)(uint16_t mode, uint8_t delay) {
  /* To be completed */
  if (change_graph_mode(mode) != 0) return 1;
  sleep(delay);
  return vg_exit();  
}



int(video_test_rectangle)(uint16_t mode, uint16_t x, uint16_t y, uint16_t width, uint16_t height, uint32_t color) {
  if (map_memory(mode) != 0) return 1;
  if (change_graph_mode(mode) != 0) return 1;
  if (vg_draw_rectangle(x, y, width, height, color) != 0) return 1;
  sleep(100);
  return vg_exit();
}

//int(video_test_pattern)(uint16_t mode, uint8_t no_rectangles, uint32_t first, uint8_t step) {
//  /* To be completed */
//  printf("%s(0x%03x, %u, 0x%08x, %d): under construction\n", __func__,
//         mode, no_rectangles, first, step);
//
//  return 1;
//}
//


int(video_test_xpm)(xpm_map_t xpm, uint16_t x, uint16_t y) {

  if(map_memory(XPM_INDEXED)!= 0) return 1;
  if(change_graph_mode(XPM_INDEXED)!= 0) return 1;
  if(show_xpm(xpm, x, y) != 0) return 1;

  if (waiting_ESC_key()) return 1;

  if (vg_exit() != 0) return 1;

  return 0;
}
//
//int(video_test_move)(xpm_map_t xpm, uint16_t xi, uint16_t yi, uint16_t xf, uint16_t yf,
//                     int16_t speed, uint8_t fr_rate) {
//  /* To be completed */
//  printf("%s(%8p, %u, %u, %u, %u, %d, %u): under construction\n",
//         __func__, xpm, xi, yi, xf, yf, speed, fr_rate);
//
//  return 1;
//}
//
//int(video_test_controller)() {
//  /* This year you do not need to implement this */
//  printf("%s(): under construction\n", __func__);
//
//  return 1;
//}
