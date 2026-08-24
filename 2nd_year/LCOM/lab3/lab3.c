#include <lcom/lcf.h>

#include <lcom/lab3.h>

#include <stdbool.h>
#include <stdint.h>

#include "keyboard.h"
#include "timer.c"
#include "KBC.h"

extern uint32_t counter_TIMER;
extern uint8_t scancode;
int main(int argc, char *argv[]) {
  // sets the language of LCF messages (can be either EN-US or PT-PT)
  lcf_set_language("EN-US");

  // enables to log function invocations that are being "wrapped" by LCF
  // [comment this out if you don't want/need it]
  lcf_trace_calls("/home/lcom/labs/lab3/trace.txt");

  // enables to save the output of printf function calls on a file
  // [comment this out if you don't want/need it]
  lcf_log_output("/home/lcom/labs/lab3/output.txt");

  // handles control over to LCF
  // [LCF handles command line arguments and invokes the right function]
  if (lcf_start(argc, argv))
    return 1;

  // LCF clean up tasks
  // [must be the last statement before return]
  lcf_cleanup();

  return 0;
}

int(kbd_test_scan)() {
  int status_ipc;
  uint8_t irq_set;
  message mesg;

  if(keyboard_subscribe_interrupts(&irq_set) != 0) {
    printf("Error subscribing to keyboard interrupts\n");
    return 1;
  }
  
  while (scancode != ESC_BREAKCODE) {
    // Wait for a message
    if (driver_receive(ANY, &mesg, &status_ipc) != 0) {
      printf("Error receiving message\n");
      return 1;
    }

    // Check if the message is from the keyboard
    if (is_ipc_notify(status_ipc)) {
      switch (_ENDPOINT_P(mesg.m_source))
      {
      case HARDWARE:
        if(mesg.m_notify.interrupts & irq_set){
          kbc_ih();
		  int scan = checking_scancode();
		  if(scan){
          	kbd_print_scancode(!IS_BREAK_CODE(scancode), scan, &scancode);
		  }
        }
      
      	default:
        break;
      }
    }
  }
  if (keyboard_unsubscribe_interrupts() != 0) {
	printf("Error unsubscribing from keyboard interrupts\n");
	return 1;
  }

  return 0;
}


int(kbd_test_poll)() {
  /* To be completed by the students */
  printf("%s is not yet implemented!\n", __func__);

  return 1;
}

int(kbd_test_timed_scan)(uint8_t n) {
  /* To be completed by the students */
  printf("%s is not yet implemented!\n", __func__);

  return 1;
}
