#ifndef FONT_H
#define FONT_H

/**
 * @file font.h
 * @brief XPM font character declarations and text drawing utility.
 *
 * Provides external declarations for character XPMs (A-Z, 0-9, colon) and a utility function
 * to render text strings on screen using these XPM-based fonts.
 *
 * This module is useful for displaying HUD elements such as scores or labels
 * in a pixel-art style.
 *
 * @author Grupo 2leic13_5
 * @version 0.1
 * @date 2025-05-30
 */

#include <stddef.h>
#include <lcom/xpm.h>

// Declare external references to character XPMs
extern xpm_row_t const *char_A;
extern xpm_row_t const *char_B;
extern xpm_row_t const *char_C;
extern xpm_row_t const *char_D;
extern xpm_row_t const *char_E;
extern xpm_row_t const *char_F;
extern xpm_row_t const *char_G;
extern xpm_row_t const *char_H;
extern xpm_row_t const *char_I;
extern xpm_row_t const *char_J;
extern xpm_row_t const *char_K;
extern xpm_row_t const *char_L;
extern xpm_row_t const *char_M;
extern xpm_row_t const *char_N;
extern xpm_row_t const *char_O;
extern xpm_row_t const *char_P;
extern xpm_row_t const *char_Q;
extern xpm_row_t const *char_R;
extern xpm_row_t const *char_S;
extern xpm_row_t const *char_T;
extern xpm_row_t const *char_U;
extern xpm_row_t const *char_V;
extern xpm_row_t const *char_W;
extern xpm_row_t const *char_X;
extern xpm_row_t const *char_Y;
extern xpm_row_t const *char_Z;

extern xpm_row_t const *char_0;
extern xpm_row_t const *char_1;
extern xpm_row_t const *char_2;
extern xpm_row_t const *char_3;
extern xpm_row_t const *char_4;
extern xpm_row_t const *char_5;
extern xpm_row_t const *char_6;
extern xpm_row_t const *char_7;
extern xpm_row_t const *char_8;
extern xpm_row_t const *char_9;

extern xpm_row_t const *char_colon; // ':'

/**
 * @brief Draws a string of characters using XPM pixel-art font.
 *
 * Iterates through each character in the string and renders the corresponding
 * XPM character sprite at the given (x, y) coordinates with specified spacing.
 *
 * @param str Null-terminated string to render (A-Z, 0-9, ':').
 * @param x X coordinate where the string starts.
 * @param y Y coordinate where the string baseline is drawn.
 * @param spacing Horizontal spacing in pixels between characters.
 */
void draw_text(const char *str, int x, int y, int spacing);

#endif // FONT_H
