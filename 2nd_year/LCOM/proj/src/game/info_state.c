#include "info_state.h"



extern uint8_t scancode;
extern bool menu;
extern bool info_menu;
void info_state_init() {
    menu = false;
    info_menu = true;
}

void info_state_update() {
    if (scancode == BREAK_ESC) exit_game = true;   // tecla ESC
}

void info_state_draw() {
    vg_draw_rectangle(0,0,1152,864, 0x000001);
    vg_draw_xpm((xpm_map_t)title, 400, 100);
    vg_draw_xpm((xpm_map_t)info, 200, 200);
    vg_draw_xpm((xpm_map_t)exit_button, 475, 750);
}


void info_state_start() {
    info_state_init();
    info_state_draw();
}
