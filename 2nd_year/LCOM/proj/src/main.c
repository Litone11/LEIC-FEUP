/**
 * @file main.c
 * @brief Main file for the LCOM game project.
 *
 * Contains the program entry point, interrupt handling logic, and the game loop controller.
 * Manages the interaction between hardware devices (mouse, keyboard, timer) and game states.
 *
 * @author Grupo 2leic13_5
 * @version 0.1
 * @date 2025-05-30
 */
#include <lcom/lcf.h>
#include "devices/timer.h"
#include "devices/keyboard.h"
#include "devices/mouse.h"
#include "devices/video.h"
#include "game/game.h"
#include "xpm/pixmap.h"
#include "game/menu_state.h"

extern uint8_t scancode;
extern int counter;

extern struct packet mouse_packet;
extern uint8_t mouse_counter;
message msg;
extern int x;
extern int y;

extern GameState current_state;

/**
 * @brief Entry point of the program. Initializes LCF and delegates control.
 */
int main(int argc, char *argv[]) {
    lcf_set_language("EN-US");
    lcf_trace_calls("/home/lcom/labs/proj/doc/trace.txt");
    lcf_log_output("/home/lcom/labs/proj/doc/output.txt");
    if (lcf_start(argc, argv)) return 1;
    lcf_cleanup();
    return 0;
}

/**
 * @brief Handles interrupts during menu and info states.
 *
 * Processes mouse and keyboard input to handle menu navigation and state transitions.
 *
 * @param mouse_mask IRQ mask for mouse.
 * @param keyboard_mask IRQ mask for keyboard.
 */
void (menu_interrupts)(uint8_t mouse_mask, uint8_t keyboard_mask) {
  int16_t prev_mouse_x;
  int16_t prev_mouse_y;
  int ipc_status;
  while (current_state == STATE_MENU || current_state == STATE_INFO) {
    if (driver_receive(ANY, &msg, &ipc_status) != 0) continue;

    if (is_ipc_notify(ipc_status)) {
      switch (_ENDPOINT_P(msg.m_source)) {
        case HARDWARE:
          if (msg.m_notify.interrupts & mouse_mask) {
            mouse_ih();
            mouse_sync_bytes();
            if (mouse_counter == 3) {
              mouse_bytes_to_packet();
              prev_mouse_x = x;
              prev_mouse_y = y;
              updateMouseLocation();
              if (x != prev_mouse_x || y != prev_mouse_y) {
                vg_draw_rectangle(prev_mouse_x, prev_mouse_y, 12, 19, 0x0000001);
                game_update();
                vg_draw_xpm((xpm_map_t)cursor, x, y);
                buf_swap();
              }
              if (current_state == STATE_MENU) {
                if ((x > 500 && x < 700 && y > 350 && y < 410) && processLeftClick())
                  current_state = STATE_PLAYING;
                if ((x > 500 && x < 700 && y > 500 && y < 560) && processLeftClick())
                  current_state = STATE_INFO;
                if ((x > 500 && x < 700 && y > 650 && y < 710) && processLeftClick())
                  current_state = STATE_EXIT;
              } else if (current_state == STATE_INFO) {
                if ((x > 475 && x < 675 && y > 750 && y < 810) && processLeftClick())
                  current_state = STATE_MENU;
              }
              game_update();
              mouse_counter = 0;
            }
          }
          if (msg.m_notify.interrupts & keyboard_mask) {
            kbc_ih();
            if (current_state == STATE_MENU) {
              if (scancode == MAKE_1) current_state = STATE_PLAYING;
              if (scancode == MAKE_2) current_state = STATE_INFO;
              if (scancode == MAKE_3 || scancode == BREAK_ESC) current_state = STATE_EXIT;
            } else if (current_state == STATE_INFO) {
              if (scancode == MAKE_1 || scancode == BREAK_ESC) current_state = STATE_MENU;
            }
            game_update();
          }
          break;
      }
    }
  }
}

/**
 * @brief Handles interrupts during gameplay.
 *
 * Manages timer updates, keyboard events, and mouse input for the playing state.
 *
 * @param timer_mask IRQ mask for timer.
 * @param keyboard_mask IRQ mask for keyboard.
 * @param mouse_mask IRQ mask for mouse.
 */
void (game_interrupts)(uint8_t timer_mask, uint8_t keyboard_mask, uint8_t mouse_mask) {
  int ipc_status;
  while (current_state == STATE_PLAYING) {
    if (driver_receive(ANY, &msg, &ipc_status) != 0) {
      printf("driver_receive failed");
      continue;
    }
    if (is_ipc_notify(ipc_status)) {
      switch (_ENDPOINT_P(msg.m_source)) {
        case HARDWARE:
          if (msg.m_notify.interrupts & mouse_mask) {
            mouse_ih();
            mouse_sync_bytes();
            if (mouse_counter == 3) {
              mouse_counter = 0;
              mouse_bytes_to_packet();
            }
          }
          if (msg.m_notify.interrupts & keyboard_mask) {
            kbc_ih();
            if (scancode == BREAK_ESC) {
              current_state = STATE_MENU;
              game_update();
            }
          }
          if (msg.m_notify.interrupts & timer_mask) {
            timer_int_handler();
            if (current_state == STATE_PLAYING) {
              if (counter % 60 == 0) {
                timer_increment_update();
              }
              vg_draw_rectangle(0, 0, 1152, 864, 0x0000001);
              game_update();
              buf_swap();
            }
          }
          break;
      }
    }
  }
}

/**
 * @brief Subscribes interrupts for timer, keyboard, and mouse.
 * @return 0 on success, 1 on failure.
 */
uint8_t keyboard_mask;
uint8_t timer_mask = 0;
uint8_t mouse_mask;

int(subscrive_interrupts)() {
  if (timer_subscribe_int(&timer_mask) != 0) return 1;
  if (keyboard_subscribe_interrupts(&keyboard_mask) != 0) return 1;
  if (mouse_subscribe_int(&mouse_mask) != 0) return 1;
  return 0;
}

/**
 * @brief Unsubscribes interrupts for timer, keyboard, and mouse.
 * @return 0 on success, 1 on failure.
 */
int(unsubscrive_interrupts)() {
  if (keyboard_unsubscribe_interrupts() != 0) return 1;
  if (timer_unsubscribe_int() != 0) return 1;
  if (mouse_unsubscribe() != 0) return 1;
  return 0;
}

/**
 * @brief Configures mouse to stream mode and enable data reporting.
 * @return 0 on success, 1 on failure.
 */
int(conf_mouse)() {
  if (mouse_config(0xEA)) return 1;
  if (mouse_config(0xF4)) return 1;
  return 0;
}

/**
 * @brief Main game loop controlled by game state.
 *
 * Initializes video mode, configures devices, and handles state-specific loops
 * until STATE_EXIT is reached.
 *
 * @param argc Number of command line arguments.
 * @param argv Array of command line argument strings.
 * @return 0 on success, 1 on error.
 */
int (proj_main_loop)(int argc, char *argv[]) {
  if (map_memory(0x14C) != 0) return 1;
  if (change_graph_mode(0x14C) != 0) return 1;
  if (timer_set_frequency(0, 60)) return 1;

  conf_mouse();
  game_init();
  subscrive_interrupts();

  while (current_state != STATE_EXIT) {
    if (current_state == STATE_PLAYING) {
      game_interrupts(timer_mask, keyboard_mask, mouse_mask);
    } else {
      menu_interrupts(mouse_mask, keyboard_mask);
    }
  }

  unsubscrive_interrupts();
  if (vg_exit() != 0) return 1;

  return 0;
}
