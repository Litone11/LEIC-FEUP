
#include "entities.h"

extern uint8_t scancode;
// Bullets are now managed dynamically
static Bullet* bullets_list;
static int bullets_count = 0;
static Enemy* enemy_list = NULL;
static int enemy_count = 0;
extern int counter; 
extern int timer_update;

// PLAYER

void player_init(Player *player) {
    player->x = 626;
    player->y = 700; 
    player->width = 20;
    player->height = 20;
    player->speed = 10;
    player->active = true;
}

void player_update(Player *player) {
    if (!player->active) return;

    if (scancode == MAKE_A && player->x > 0 + player->width) {
        player->x -= player->speed;
    }
    else if (scancode == MAKE_D && player->x + player->width < 1152 - player->width) {
        player->x += player->speed;
    }
}

void player_draw(const Player *player) {
    if (player->active) {
        vg_draw_xpm((xpm_map_t)ship_2, player->x, player->y); 
    }
    else {
        vg_draw_xpm((xpm_map_t)explosion, player->x, player->y);
    }
}



// BULLET

void bullet_init() {
    bullets_list = malloc(sizeof(Bullet) * 15);
    bullets_count = 0;
}

void bullet_update() {
    int j = 0;
    for (int i = 0; i < bullets_count; i++) {
        
            bullets_list[i].y -= bullets_list[i].speed;
            if (bullets_list[i].y >= 0) {
                bullets_list[j++] = bullets_list[i];
            }
        
    }
    bullets_count = j;
}

void bullet_draw() {
    for (int i = 0; i < bullets_count; i++) {
        
        vg_draw_xpm((xpm_map_t)laser, bullets_list[i].x, bullets_list[i].y);
        
    }
}

void bullet_fire(int x, int y) {
    bullets_list[bullets_count].x = x;
    bullets_list[bullets_count].y = y;
    bullets_list[bullets_count].speed = 25;
    bullets_count++;
}

// ENEMY

void enemy_init() {
    enemy_list = NULL;
    enemy_count = 0;
}

void enemy_update() {
    if (counter % (25 - timer_update) == 0) { 
        Enemy* new_list = realloc(enemy_list, sizeof(Enemy) * (enemy_count + 1));
        if (!new_list) return;
        enemy_list = new_list;
        enemy_list[enemy_count].x = rand() % 864;
        enemy_list[enemy_count].y = 0;
        enemy_list[enemy_count].width = 20;
        enemy_list[enemy_count].height = 20;
        enemy_list[enemy_count].speed = 2 * (rand() % 5 + 1);
        enemy_count++;
    }

    int j = 0;
    for (int i = 0; i < enemy_count; i++) {
            enemy_list[i].y += enemy_list[i].speed;
            enemy_list[j++] = enemy_list[i];
        
    }
    enemy_count = j;
}

void enemy_draw() {
    for (int i = 0; i < enemy_count; i++) {
        vg_draw_xpm((xpm_map_t) meteor, enemy_list[i].x, enemy_list[i].y);
        
    }
}

// COLLISION
void check_collisions(Player *player) {
    for (int i = 0; i < bullets_count; i++) {
        for (int j = 0; j < enemy_count; j++) {

            int bx = bullets_list[i].x;
            int by = bullets_list[i].y;
            int ex = enemy_list[j].x;
            int ey = enemy_list[j].y;
            int ew = enemy_list[j].width;
            int eh = enemy_list[j].height;

            if (bx >= ex - ew && bx <= ex + ew &&
                by - 20 <= ey + eh) {
                for (int k = i; k < bullets_count - 1; k++) {
                    bullets_list[k] = bullets_list[k + 1];
                }
                bullets_count--;

                for (int k = j; k < enemy_count - 1; k++) {
                    enemy_list[k] = enemy_list[k + 1];
                }
                enemy_count--;
                inc_score(10 * timer_update);
            }
        }
    }

    for (int i = 0; i < enemy_count; i++) {
        int ex = enemy_list[i].x;
        int ey = enemy_list[i].y;
        int ew = enemy_list[i].width;
        int eh = enemy_list[i].height;

        if (((ex + ew >= player->x - player->width/2 && ex + ew <= player->x + player->width/2) ||
            (ex - ew <= player->x + player->width/2 && ex - ew >= player->x - player->width/2) ||
            (ex - ew >= player->x - player->width/2 && ex + ew <= player->x + player->width/2)) &&
            (player->y - player->height/2 <= ey + eh) && player->y + player->height/2 >= ey - eh) {
            player->active = false; // Player is hit
            vg_draw_xpm((xpm_map_t)explosion, player->x, player->y);
            return;
        }
        if(ey > 840){
            for (int k = i; k < enemy_count - 1; k++) {
                enemy_list[k] = enemy_list[k + 1];
            }
            enemy_count--;
        }
    }
}
