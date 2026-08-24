#include "menu_state.h"
#include "devices/keyboard.h"
#include "devices/video.h"
#include "xpm/pixmap.h"

static bool start_game = false;
static bool exit_game = false;
extern uint8_t scancode;

void menu_state_init() {
    start_game = false;
    exit_game = false;
}

void menu_state_update() {
    if (scancode == 0x02) start_game = true;
    if (scancode == 0x81) exit_game = true; 
}

void menu_state_draw() {
    vg_draw_rectangle(0,0,1152,864, 0x000001);
    vg_draw_xpm((xpm_map_t)title, 400, 150);
    vg_draw_xpm((xpm_map_t)start_button, 500, 350);
    vg_draw_xpm((xpm_map_t)info_button, 500, 500);
    vg_draw_xpm((xpm_map_t)exit_button, 500, 650);


}

void menu_state_start(){
    menu_state_init();
    menu_state_draw();
}
