


#define IRQ_KEYBOARD    1


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





#define IS_BREAK_CODE(code) (code & BIT(7))


#define DELAY_US    20000



#define ESC_BREAKCODE 0x81
