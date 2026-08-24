#ifndef _XPM_H_
#define _XPM_H_

/**
 * @file xpm.h
 * @brief Utilities for handling and drawing XPM images and animations.
 *
 * Declares the XPM structure and functions to load, draw, and animate XPM images.
 * Used for rendering static and animated sprites using the LCF graphics system.
 *
 * This module supports splitting a spritesheet into frames for animation playback.
 *
 * @author Grupo 2leic13_5
 * @version 0.1
 * @date 2025-05-30
 */

#include <lcom/lcf.h>
#include <stdlib.h>

/**
 * @brief Structure representing an XPM image with position and pixel data.
 */
typedef struct {
    int x, y;
    int width, height;
    uint32_t *map;
} XPM;

/**
 * @brief Creates an XPM image object from a raw XPM map.
 *
 * Allocates and loads the XPM into memory, storing its position and dimensions.
 *
 * @param xpm Raw XPM data (char* array).
 * @param x X position to place the image.
 * @param y Y position to place the image.
 * @return Pointer to an XPM struct or NULL on failure.
 */
XPM *create_xpm(xpm_map_t xpm, int x, int y);

/**
 * @brief Draws the entire XPM image at its position.
 *
 * @param xpm Pointer to an XPM object to render.
 */
void draw_xpm(XPM *xpm);

/**
 * @brief Draws a specific frame from an XPM sprite sheet animation.
 *
 * Splits the XPM into a grid of frames and draws the selected frame.
 *
 * @param xpm Pointer to the XPM sprite sheet.
 * @param n Frame index to draw (row-major order).
 * @param width_total Total number of frames per row.
 * @param height_total Total number of frame rows.
 */
void draw_animations(XPM *xpm, int n, int width_total, int height_total);

#endif /* _XPM_H_ */
