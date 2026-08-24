/**
 * @file video.h
 * @brief Video graphics interface using VBE and double buffering.
 * 
 * Declares functions for setting video mode, mapping memory,
 * drawing pixels, lines, rectangles, XPM images, and swapping buffers.
 * 
 * Uses VBE mode information and double buffer to render graphics in Minix.
 * 
 * @author Grupo 2leic13_5
 * @version 0.1
 * @date 2025-05-30
 */

#include <lcom/lcf.h>
#include <stdint.h>

/**
 * @brief Changes the graphics mode using VBE.
 * 
 * @param mode The VBE mode to set.
 * @return 0 on success, 1 on failure.
 */
int(change_graph_mode)(uint16_t mode);

/**
 * @brief Maps video memory and initializes double buffering.
 * 
 * @param mode The VBE mode used to calculate memory size.
 * @return 0 on success, 1 on failure.
 */
int (map_memory)(uint16_t mode);

/**
 * @brief Draws a single pixel to the back buffer.
 * 
 * @param x Horizontal coordinate.
 * @param y Vertical coordinate.
 * @param color Color value to set.
 * @return 0 on success, 1 if out of bounds.
 */
int (vg_draw_pixel)(uint16_t x, uint16_t y, uint32_t color);

/**
 * @brief Draws a horizontal line on the screen.
 * 
 * @param x Starting x-coordinate.
 * @param y Y-coordinate.
 * @param len Length of the line in pixels.
 * @param color Color to draw.
 * @return 0 on success, 1 on error.
 */
int (vg_draw_hline)(uint16_t x, uint16_t y, uint16_t len, uint32_t color);

/**
 * @brief Draws a filled rectangle on the screen.
 * 
 * @param x Top-left x-coordinate.
 * @param y Top-left y-coordinate.
 * @param width Width of the rectangle.
 * @param height Height of the rectangle.
 * @param color Fill color.
 * @return 0 on success, 1 on error.
 */
int (vg_draw_rectangle)(uint16_t x, uint16_t y, uint16_t width, uint16_t height, uint32_t color);

/**
 * @brief Draws an XPM image on the screen.
 * 
 * @param xpm The XPM map to draw.
 * @param x Top-left x-coordinate.
 * @param y Top-left y-coordinate.
 * @return 0 on success, 1 on failure.
 */
int (vg_draw_xpm)(xpm_map_t xpm, uint16_t x, uint16_t y);

/**
 * @brief Swaps the back buffer to the video memory (double buffering).
 * 
 * @return 0 on success.
 */
int (buf_swap)();
