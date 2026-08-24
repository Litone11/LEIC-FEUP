#include "score.h"
#include <stdio.h>
#include <stdlib.h>

static int current_score = 0;

void inc_score(uint32_t points){
    current_score += points;
    if (current_score < 0) {
        current_score = 0; 
    }
}

void reset_score(){
    current_score = 0;
}

uint32_t get_score(){
    return current_score;
}

void score_draw(){
    vg_draw_xpm((xpm_map_t)score_frame, 0, 0); 
    char score_str[20];
    snprintf(score_str, sizeof(score_str), "SCORE: %d", current_score);
    draw_text(score_str, 23, 28, 10); 
}
