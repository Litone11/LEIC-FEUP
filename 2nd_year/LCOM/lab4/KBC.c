#include <lcom/lcf.h>
#include "KBC.h"

uint8_t scancode;

void (kbc_ih)() {
    if (read_KBC_output(KBC_OUT_CMD, &scancode, 0) != 0){ 
        printf("Error: Could not read scancode in ih!\n");
    }

}

int (read_KBC_status)(uint8_t* status){
    return util_sys_inb(KBC_STATUS_REG, status); //lê direto do registo
}

int (read_KBC_output)(uint8_t port, uint8_t *output, int mouse){
    uint8_t status;
    int k = 0;

    while (k<10)
    {
        /* code */
        if (read_KBC_status(&status) != 0 || status & ST_TIME_OUT_ERROR || status & ST_PARITY_ERROR){
            printf("Error: Error reading KBC status!\n");
            return 1; //lê o registo de status
        }
        if(status & ST_OUT_FULL_BUFFER){
            if ((mouse && (status & ST_MOUSE_DATA)) || (!mouse && !(status & ST_MOUSE_DATA))){
                if (util_sys_inb(port, output) != 0) {
                    printf("Error: Could not read KBC output!\n");
                }
            }
            return 0; //lê o registo de output 
        }

        tickdelay(micros_to_ticks(DELAY_US)); //delay de 20ms
        k++;
    }
        printf("Error: Output buffer is empty!\n");
        return 1;
    }


int (write_KBC_command)(uint8_t port, uint8_t commandByte, int mouse){
    uint8_t status;
    int k = 0;
    while (k<10)
    {
            /* code */    
        if (read_KBC_status(&status) != 0){
            printf("Error: Error reading KBC status!\n");
            return 1; //lê o registo de status
        }
        if(!(status & ST_IN_FULL_BUFFER)){
            if (sys_outb(port, commandByte) != 0) {
                printf("Error: Could not write KBC input!\n");
                return 1; //lê o registo de output
            }
            return 0;
        }
   
        k++;
        tickdelay(micros_to_ticks(DELAY_US)); //delay de 20ms
    }
        return 1;
}
