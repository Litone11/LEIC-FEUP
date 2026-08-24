// IMPORTANT: you must include the following line in all your C files
#include <lcom/lcf.h>
#include <video.h>


int(change_graph_mode)(uint16_t mode) {
    reg86_t r;

    memset(&r, 0, sizeof(r));	/* zero the structure */

    r.ax = 0x4F02; // VBE call, function 02 -- set VBE mode
    r.bx = 1<<14|0x105; // set bit 14: linear framebuffer
    r.intno = 0x10;
    if( sys_int86(&r) != OK ) {
    printf("set_vbe_mode: sys_int86() failed \n");
      return 1;
    }
    return 0;
}

vbe_mode_info_t mode_info;
static uint8_t n_bytes_per_pixel;
static uint8_t *video_mem;

int (map_memory)(uint16_t mode){
    if (vbe_get_mode_info(mode, &mode_info) != 0) return 1;    

    struct minix_mem_range mr;
    n_bytes_per_pixel = (mode_info.BitsPerPixel + 7) / 8; /* Calculate bytes per pixel */
    unsigned int vram_base = mode_info.PhysBasePtr;  /* VRAM's physical addresss */
    unsigned int vram_size = mode_info.XResolution * mode_info.YResolution * n_bytes_per_pixel; /* VRAM's size, in bytes */
    int r;				    

    /* Use VBE function 0x01 to initialize vram_base and vram_size */
    /* Allow memory mapping */

    mr.mr_base = (phys_bytes) vram_base;	
    mr.mr_limit = mr.mr_base + vram_size;  

    if( OK != (r = sys_privctl(SELF, SYS_PRIV_ADD_MEM, &mr)))
       panic("sys_privctl (ADD_MEM) failed: %d\n", r);

    /* Map memory */

    video_mem = vm_map_phys(SELF, (void *)mr.mr_base, vram_size);

    if(video_mem == MAP_FAILED){
       panic("couldn't map video memory");
      return 1;
    }

    return 0;
}

int (vg_draw_pixel)(uint16_t x, uint16_t y, uint32_t color) {
  if(x > mode_info.XResolution || y > mode_info.YResolution) return 1;
  unsigned int index = (mode_info.XResolution * y + x) * n_bytes_per_pixel;
  if (memcpy(&video_mem[index], &color, n_bytes_per_pixel) == NULL) return 0;
  return 0;
}

int (vg_draw_hline)(uint16_t x, uint16_t y, uint16_t len, uint32_t color) {
  for (unsigned i = 0 ; i < len ; i++)   
    if (vg_draw_pixel(x+i, y, color) != 0) return 1;
  return 0;
}

int (vg_draw_rectangle)(uint16_t x, uint16_t y, uint16_t width, uint16_t height, uint32_t color) {
  for(unsigned i = 0; i < height ; i++)
    if (vg_draw_hline(x, y+i, width, color) != 0) {
      return 1;
    }
  return 0;
}

int (show_xpm)(xpm_map_t xpm, uint16_t x, uint16_t y){

  xpm_image_t img;
  uint8_t *colors = xpm_load(xpm, XPM_INDEXED, &img);
  if (colors == NULL) return 1;
  for (int h = 0 ; h < img.height ; h++) {
    for (int w = 0 ; w < img.width ; w++) {
      if (vg_draw_pixel(x + w, y + h, *colors)) return 1;
      colors++;
    }
  }
  return 0;
}




