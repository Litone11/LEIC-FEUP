#ifndef _LCOM_I8042_H_
#define _LCOM_I8042_H_

#define IRQ_KEYBOARD    1
#define IRQ_MOUSE      12



#define KBC_STATUS_REG  0x64
#define KBC_IN_CMD      0x64
#define KBC_OUT_CMD     0x60
#define KBC_READ_CMD    0x20
#define KBC_WRITE_CMD   0x60

#define ESC_BREAKCODE   0x81
#define FIRST_TWO_BYTES 0xE0

// read status KBC
#define ST_OUT_FULL_BUFFER BIT(0)
#define ST_IN_FULL_BUFFER BIT(1)
#define ST_INHIBIT_FLAG BIT(4)
#define ST_MOUSE_DATA BIT(5)
#define ST_TIME_OUT_ERROR BIT(6)
#define ST_PARITY_ERROR BIT(7)


#define MOUSE_LB_PACKET BIT(0)
#define MOUSE_RB_PACKET BIT(1)
#define MOUSE_MB_PACKET BIT(2)
#define MOUSE_X_DELTA BIT(4)
#define MOUSE_Y_DELTA BIT(5)
#define MOUSE_X_OV BIT(6)
#define MOUSE_Y_OV BIT(7)

#define MOUSE_REQUEST     0xD4
#define SUCESSS_BYTE      0xFA
#define DISABLE_MOUSE     0xF5
#define ENABLE_MOUSE      0xF4

#define LB_MOUSE_PACKET BIT(0)
#define RB_MOUSE_PACKET BIT(1)
#define MB_MOUSE_PACKET BIT(2)
#define MSB_X_DELTA BIT(4)
#define MSB_Y_DELTA BIT(5)
#define X_OV_MOUSE_PACKET BIT(6)
#define Y_OV_MOUSE_PACKET BIT(7)





#define IS_BREAK_CODE(code) (code & BIT(7))


#define DELAY_US    20000



#define ESC_BREAKCODE 0x81
#endif /* _LCOM_I8042_H_ */
