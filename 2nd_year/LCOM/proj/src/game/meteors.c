#include "meteors.h"


#include "../alive/object.h"

// Variáveis globais
Object *meteors[MAX_METEORS];
int meteor_count = 0;

int create_rate = 120;      
int decrease_rate = 5;      
int min_create_rate = 30;   

extern int counter;

void init_meteor_system() {
    for (int i = 0; i < MAX_METEORS; i++) {
        meteors[i] = NULL;
    }
    meteor_count = 0;
    
    create_rate = 500;
    decrease_rate = 50;
    min_create_rate = 100;
}

int check_meteor_spawn(int current_time) {
    return (current_time % create_rate == 0);
}

void spawn_meteor() {
    if (meteor_count >= MAX_METEORS) return;
    
    int x = (counter * 73) % (mode_info.XResolution - 32);
    
    Object *new_meteor = create_object((xpm_map_t)meteor_double, x, -32);
    if (new_meteor == NULL) return;
    
    new_meteor->ys = 3;
    
    for (int i = 0; i < MAX_METEORS; i++) {
        if (meteors[i] == NULL) {
            meteors[i] = new_meteor;
            meteor_count++;
            
            create_rate = (create_rate - decrease_rate < min_create_rate) ? 
                          min_create_rate : create_rate - decrease_rate;
            break;
        }
    }
}

void update_meteors() {
    for (int i = 0; i < MAX_METEORS; i++) {
        if (meteors[i] != NULL) {
            move_object(meteors[i], 0, meteors[i]->ys);
            if (meteors[i]->xpm->y > mode_info.YResolution) {
                free(meteors[i]->xpm);
                free(meteors[i]);
                meteors[i] = NULL;
                meteor_count--;
            }
        }
    }
}

void handle_meteors(int current_time) {
    if (check_meteor_spawn(current_time)) {
        spawn_meteor();
    }
    update_meteors();
}
