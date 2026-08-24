//
// Created by Luís Martins on 27/05/2025.
//

#include "font.h"
#include "devices/video.h"
// 5x7 XPMs for characters A-Z
static xpm_row_t const letter_A[] = {
    "5 7 2 1",
    ". c #FFFFFF", 
    "  c #000000",
    "  .  ", 
    " . . ", 
    ".   .", 
    ".....", 
    ".   .", 
    ".   .", 
    ".   ."
};
static xpm_row_t const letter_B[] = {
    "5 7 2 1", ". c #FFFFFF", "  c #000000",
    ".... ", ".   .", ".   .", ".... ", ".   .", ".   .", ".... "
};
static xpm_row_t const letter_C[] = {
    "5 7 2 1", ". c #FFFFFF", "  c #000000",
    " ... ", ".   .", ".    ", ".    ", ".    ", ".   .", " ... "
};
static xpm_row_t const letter_D[] = {
    "5 7 2 1", ". c #FFFFFF", "  c #000000",
    ".... ", ".   .", ".   .", ".   .", ".   .", ".   .", ".... "
};
static xpm_row_t const letter_E[] = {
    "5 7 2 1", ". c #FFFFFF", "  c #000000",
    ".....", ".    ", ".    ", ".....", ".    ", ".    ", "....."
};
static xpm_row_t const letter_F[] = {
    "5 7 2 1", ". c #FFFFFF", "  c #000000",
    ".....", ".    ", ".    ", ".....", ".    ", ".    ", ".    "
};
static xpm_row_t const letter_G[] = {
    "5 7 2 1", ". c #FFFFFF", "  c #000000",
    " ... ", ".   .", ".    ", ". ...", ".   .", ".   .", " ... "
};
static xpm_row_t const letter_H[] = {
    "5 7 2 1", ". c #FFFFFF", "  c #000000",
    ".   .", ".   .", ".   .", ".....", ".   .", ".   .", ".   ."
};
static xpm_row_t const letter_I[] = {
    "5 7 2 1", ". c #FFFFFF", "  c #000000",
    ".....", "  .  ", "  .  ", "  .  ", "  .  ", "  .  ", "....."
};
static xpm_row_t const letter_J[] = {
    "5 7 2 1", ". c #FFFFFF", "  c #000000",
    "  ...", "    .", "    .", "    .", ".   .", ".   .", " ... "
};
static xpm_row_t const letter_K[] = {
    "5 7 2 1", ". c #FFFFFF", "  c #000000",
    ".   .", ".  . ", ". .  ", "..   ", ". .  ", ".  . ", ".   ."
};
static xpm_row_t const letter_L[] = {
    "5 7 2 1", ". c #FFFFFF", "  c #000000",
    ".    ", ".    ", ".    ", ".    ", ".    ", ".    ", "....."
};
static xpm_row_t const letter_M[] = {
    "5 7 2 1", ". c #FFFFFF", "  c #000000",
    ".   .", ".. ..", ". . .", ".   .", ".   .", ".   .", ".   ."
};
static xpm_row_t const letter_N[] = {
    "5 7 2 1", ". c #FFFFFF", "  c #000000",
    ".   .", "..  .", ". . .", ".  ..", ".   .", ".   .", ".   ."
};
static xpm_row_t const letter_O[] = {
    "5 7 2 1", ". c #FFFFFF", "  c #000000",
    " ... ", ".   .", ".   .", ".   .", ".   .", ".   .", " ... "
};
static xpm_row_t const letter_P[] = {
    "5 7 2 1", ". c #FFFFFF", "  c #000000",
    ".... ", ".   .", ".   .", ".... ", ".    ", ".    ", ".    "
};
static xpm_row_t const letter_Q[] = {
    "5 7 2 1", ". c #FFFFFF", "  c #000000",
    " ... ", ".   .", ".   .", ".   .", ". . .", ".  . ", " .. ."
};
static xpm_row_t const letter_R[] = {
    "5 7 2 1", ". c #FFFFFF", "  c #000000",
    ".... ", ".   .", ".   .", ".... ", ". .  ", ".  . ", ".   ."
};
static xpm_row_t const letter_S[] = {
    "5 7 2 1", ". c #FFFFFF", "  c #000000",
    " ... ", ".   .", ".    ", " ... ", "    .", ".   .", " ... "
};
static xpm_row_t const letter_T[] = {
    "5 7 2 1", ". c #FFFFFF", "  c #000000",
    ".....", "  .  ", "  .  ", "  .  ", "  .  ", "  .  ", "  .  "
};
static xpm_row_t const letter_U[] = {
    "5 7 2 1", ". c #FFFFFF", "  c #000000",
    ".   .", ".   .", ".   .", ".   .", ".   .", ".   .", " ... "
};
static xpm_row_t const letter_V[] = {
    "5 7 2 1", ". c #FFFFFF", "  c #000000",
    ".   .", ".   .", ".   .", ".   .", ".   .", " . . ", "  .  "
};
static xpm_row_t const letter_W[] = {
    "5 7 2 1", ". c #FFFFFF", "  c #000000",
    ".   .", ".   .", ".   .", ".   .", ". . .", ". . .", " . . "
};
static xpm_row_t const letter_X[] = {
    "5 7 2 1", ". c #FFFFFF", "  c #000000",
    ".   .", ".   .", " . . ", "  .  ", " . . ", ".   .", ".   ."
};
static xpm_row_t const letter_Y[] = {
    "5 7 2 1", ". c #FFFFFF", "  c #000000",
    ".   .", ".   .", " . . ", "  .  ", "  .  ", "  .  ", "  .  "
};
static xpm_row_t const letter_Z[] = {
    "5 7 2 1", ". c #FFFFFF", "  c #000000",
    ".....", "    .", "   . ", "  .  ", " .   ", ".    ", "....."
};

// Digits 0-9
static xpm_row_t const digit_0[] = {
    "5 7 2 1", ". c #FFFFFF", "  c #000000",
    " ... ", ".   .", ".  ..", ". . .", "..  .", ".   .", " ... "
};
static xpm_row_t const digit_1[] = {
    "5 7 2 1", ". c #FFFFFF", "  c #000000",
    "  .  ", " ..  ", "  .  ", "  .  ", "  .  ", "  .  ", "....."
};
static xpm_row_t const digit_2[] = {
    "5 7 2 1", ". c #FFFFFF", "  c #000000",
    " ... ", ".   .", "    .", "   . ", "  .  ", " .   ", "....."
};
static xpm_row_t const digit_3[] = {
    "5 7 2 1", ". c #FFFFFF", "  c #000000",
    " ... ", ".   .", "    .", " ... ", "    .", ".   .", " ... "
};
static xpm_row_t const digit_4[] = {
    "5 7 2 1", ". c #FFFFFF", "  c #000000",
    "   . ", "  .. ", " . . ", ".  . ", ".....", "   . ", "   . "
};
static xpm_row_t const digit_5[] = {
    "5 7 2 1", ". c #FFFFFF", "  c #000000",
    ".....", ".    ", ".    ", ".... ", "    .", ".   .", " ... "
};
static xpm_row_t const digit_6[] = {
    "5 7 2 1", ". c #FFFFFF", "  c #000000",
    " ... ", ".   .", ".    ", ".... ", ".   .", ".   .", " ... "
};
static xpm_row_t const digit_7[] = {
    "5 7 2 1", ". c #FFFFFF", "  c #000000",
    ".....", "    .", "   . ", "  .  ", " .   ", " .   ", " .   "
};
static xpm_row_t const digit_8[] = {
    "5 7 2 1", ". c #FFFFFF", "  c #000000",
    " ... ", ".   .", ".   .", " ... ", ".   .", ".   .", " ... "
};
static xpm_row_t const digit_9[] = {
    "5 7 2 1", ". c #FFFFFF", "  c #000000",
    " ... ", ".   .", ".   .", " ... ", "    .", ".   .", " ... "
};

// Colon ':'
static xpm_row_t const colon_char[] = {
    "5 7 2 1", ". c #FFFFFF", "  c #000000",
    "     ", "  .  ", "     ", "     ", "     ", "  .  ", "     "
};

xpm_row_t const *char_A = letter_A;
xpm_row_t const *char_B = letter_B;
xpm_row_t const *char_C = letter_C;
xpm_row_t const *char_D = letter_D;
xpm_row_t const *char_E = letter_E;
xpm_row_t const *char_F = letter_F;
xpm_row_t const *char_G = letter_G;
xpm_row_t const *char_H = letter_H;
xpm_row_t const *char_I = letter_I;
xpm_row_t const *char_J = letter_J;
xpm_row_t const *char_K = letter_K;
xpm_row_t const *char_L = letter_L;
xpm_row_t const *char_M = letter_M;
xpm_row_t const *char_N = letter_N;
xpm_row_t const *char_O = letter_O;
xpm_row_t const *char_P = letter_P;
xpm_row_t const *char_Q = letter_Q;
xpm_row_t const *char_R = letter_R;
xpm_row_t const *char_S = letter_S;
xpm_row_t const *char_T = letter_T;
xpm_row_t const *char_U = letter_U;
xpm_row_t const *char_V = letter_V;
xpm_row_t const *char_W = letter_W;
xpm_row_t const *char_X = letter_X;
xpm_row_t const *char_Y = letter_Y;
xpm_row_t const *char_Z = letter_Z;

xpm_row_t const *char_0 = digit_0;
xpm_row_t const *char_1 = digit_1;
xpm_row_t const *char_2 = digit_2;
xpm_row_t const *char_3 = digit_3;
xpm_row_t const *char_4 = digit_4;
xpm_row_t const *char_5 = digit_5;
xpm_row_t const *char_6 = digit_6;
xpm_row_t const *char_7 = digit_7;
xpm_row_t const *char_8 = digit_8;
xpm_row_t const *char_9 = digit_9;
xpm_row_t const *char_colon = colon_char;

void draw_text(const char *str, int x, int y, int spacing) {
    for (size_t i = 0; str[i] != '\0'; ++i) {
        xpm_row_t const *letter = NULL;
        switch (str[i]) {
            case 'A': letter = char_A; break;
            case 'B': letter = char_B; break;
            case 'C': letter = char_C; break;
            case 'D': letter = char_D; break;
            case 'E': letter = char_E; break;
            case 'F': letter = char_F; break;
            case 'G': letter = char_G; break;
            case 'H': letter = char_H; break;
            case 'I': letter = char_I; break;
            case 'J': letter = char_J; break;
            case 'K': letter = char_K; break;
            case 'L': letter = char_L; break;
            case 'M': letter = char_M; break;
            case 'N': letter = char_N; break;
            case 'O': letter = char_O; break;
            case 'P': letter = char_P; break;
            case 'Q': letter = char_Q; break;
            case 'R': letter = char_R; break;
            case 'S': letter = char_S; break;
            case 'T': letter = char_T; break;
            case 'U': letter = char_U; break;
            case 'V': letter = char_V; break;
            case 'W': letter = char_W; break;
            case 'X': letter = char_X; break;
            case 'Y': letter = char_Y; break;
            case 'Z': letter = char_Z; break;
            case '0': letter = char_0; break;
            case '1': letter = char_1; break;
            case '2': letter = char_2; break;
            case '3': letter = char_3; break;
            case '4': letter = char_4; break;
            case '5': letter = char_5; break;
            case '6': letter = char_6; break;
            case '7': letter = char_7; break;
            case '8': letter = char_8; break;
            case '9': letter = char_9; break;
            case ':': letter = char_colon; break;
            default: continue;
        }
        if (letter) {
            vg_draw_xpm(letter, x, y);
            x += spacing;
        }
    }
}

