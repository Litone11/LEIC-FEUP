#include "xpm.h"
#include "../devices/video.h"



XPM *create_xpm(xpm_map_t xpm, int x, int y) {
    XPM *sp = (XPM *) malloc (sizeof(XPM));
    xpm_image_t img;
    if(sp == NULL) return NULL;
    sp->map = (uint32_t *)xpm_load(xpm, XPM_8_8_8_8, &img);
    if(sp->map == NULL) {
        free(sp);
        return NULL;
    }
    sp->width = img.width; 
    sp->height= img.height;
    sp->x = x;
    sp->y = y;
    return sp;
}

void draw_xpm(XPM *xpm){
   int c = 0;
   for (int i = 0 ; i < xpm->height ; i++) {
        for (int j = 0 ; j < xpm->width ; j++) {
            vg_draw_pixel(xpm->x + j, xpm->y + i, *(xpm->map + c));
            c++;
        }
   }
}

void draw_animations(XPM *xpm, int n, int width_total, int height_total){
    int w = xpm->width / width_total;
    int h = xpm->height / height_total;

    if(n < 0 || n >= (width_total * height_total)) {
        printf("Invalid animation frame number: %d\n", n);
        return;
    }

    int xpm1 = (n % width_total) * w;
    int xpm2 = (n / width_total) * h;

       for (int i = 0 ; i < h ; i++) {
        for (int j = 0 ; j < w ; j++) {
            int map_n = (xpm2 + i) * xpm->width + (xpm1 + j);
            vg_draw_pixel(xpm->x + j, xpm->y + i, xpm->map[map_n]);
        }
    }

}
