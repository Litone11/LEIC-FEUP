#include <lcom/lcf.h>
#include <stdint.h>


int(change_graph_mode)(uint16_t mode);
int (map_memory)(uint16_t mode);
int (vg_draw_hline)(uint16_t x, uint16_t y, uint16_t len, uint32_t color);
int (vg_draw_rectangle)(uint16_t x, uint16_t y, uint16_t width, uint16_t height, uint32_t color);
int (show_xpm)(xpm_map_t xpm, uint16_t x, uint16_t y);
