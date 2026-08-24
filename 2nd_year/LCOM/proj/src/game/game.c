//
// Created by Luís Martins on 27/05/2025.
//

#include "game.h"
#include"entities/score.h"
#include "devices/keyboard.h"
#include "menu_state.h"
#include "menu.h"
#include "devices/video.h"
#include "devices/mouse.h"
#include "entities/entities.h"
#include "devices/timer.h"

GameState current_state = STATE_MENU;
bool exit_game = false;
bool menu = true;
bool info_menu = false;
static Player player;
extern int counter;
int bullet_cooldown = 0;
bool flag = false;



void game_init() {
    current_state = STATE_MENU;
    exit_game = false;
    menu = true;
    info_menu = false;
    player_init(&player);
    bullet_init();
    enemy_init();
}





void game_update() {
    switch (current_state) {
        case STATE_MENU:
            menu_state_start();
            break;
        case STATE_INFO:
            info_state_start();
            break;
        case STATE_PLAYING:
            if(player.active == false) {
                if(flag){
                    current_state = STATE_MENU;
                    menu = true;
                    info_menu = false;
                    break;
                }
                flag = true;
            }
            player_update(&player);
            bullet_update();
            enemy_update();
            player_draw(&player);
            bullet_draw();
            enemy_draw();
            score_draw();
            if (processLeftClick() && bullet_cooldown >= 10) {
                bullet_fire(player.x + player.width / 2, player.y);
                bullet_cooldown = 0;
            }
            check_collisions(&player);
            bullet_cooldown++;
            break;
        case STATE_EXIT:
            exit_game = true;
        break;
    }
}


