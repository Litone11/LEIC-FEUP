#include <lcom/lcf.h>
#include <lcom/timer.h>

#include <stdint.h>

#include "i8254.h"

int (timer_set_frequency)(uint8_t timer, uint32_t freq) {
	if(timer < 0 || timer > 2) return 1;
	if(freq > TIMER_FREQ) return 1;
	uint8_t controlWord;
	//obter a info atual do timer 
	if(timer_get_conf(timer, &controlWord) != 0) return 1;
	// (modo 3)  ativar o LSB e MSB e manter o resto igual
	controlWord = (controlWord & 0x0F) | TIMER_LSB_MSB; 
	uint32_t initVal = TIMER_FREQ / freq;
  	uint8_t MSB, LSB;
  	util_get_MSB(initVal, &MSB);
  	util_get_LSB(initVal, &LSB);

	//escolher o timer para atualizar a control word (0x40, 0x41, 0x42)
	uint8_t timerselected;      
	switch (timer) {
			case 0: controlWord |= TIMER_SEL0; timerselected = TIMER_0; break;
			case 1: controlWord |= TIMER_SEL1; timerselected = TIMER_1; break;
			case 2: controlWord |= TIMER_SEL2; timerselected = TIMER_2; break;
			default: return 1;

	}

	//escrever a nossa control word
	if(sys_outb(TIMER_CTRL, controlWord) != 0) return 1;

	//por os valores no registo correspondente
	if(sys_outb(timerselected, LSB) != 0) return 1;
	if(sys_outb(timerselected, MSB) != 0) return 1;
	return 0;
}

int hook_id; //= 0; // timer use 
int counter = 0;
int timer_update = 1; //timer update in seconds

int (timer_subscribe_int)(uint8_t *bit_no) {
    if( bit_no == NULL) return 1; 
		hook_id =  *bit_no;
  	*bit_no = BIT(*bit_no);       
		//subscrever as interrupções
  	if (sys_irqsetpolicy(TIMER0_IRQ, IRQ_REENABLE, &hook_id) != 0) return 1; 
  	return 0;
}

int (timer_unsubscribe_int)() {
	//Parar de subscrever as interrupções
  	if (sys_irqrmpolicy(&hook_id) != 0) return 1;
  	return 0;
}

void (timer_int_handler)() {
	counter++;
}

int (timer_get_conf)(uint8_t timer, uint8_t *st) {
	if(st == NULL) return 1;
	if(timer < 0 || timer > 2) return 1; 
  	uint8_t read_back_command = TIMER_RB_CMD | TIMER_RB_COUNT_ | TIMER_RB_SEL(timer);//Criar read back command
  	if(sys_outb(TIMER_CTRL, read_back_command) != 0) return 1; //Escrever o read back command
	//Ler a configuração do timer(TIMER_0 + timer)
  	if(util_sys_inb(TIMER_0 + timer, st) != 0) return 1;
  	return 0;
}

int (timer_display_conf)(uint8_t timer, uint8_t st, enum timer_status_field field) {
	union timer_status_field_val conf;
	switch (field)
	{
	case tsf_all:
		conf.byte = st;
		break;

	case tsf_initial:
		st = st >> 4;
		st = st & 0x03;
		if(st == 0x01) conf.in_mode = LSB_only;
		else if(st == 0x02) conf.in_mode = MSB_only;
		else if(st == 0x03) conf.in_mode = MSB_after_LSB;
		else conf.in_mode = INVAL_val;	
		break;
	
	case tsf_mode:
		st = st >> 1;
		st = st & 0x07;
		if (st == 0x06) conf.count_mode = 2;
		else if (st == 0x07) conf.count_mode = 3;
		else conf.count_mode = st;
		break;
	
	case tsf_base:
		st = st & 0x01;
		conf.bcd = (st==1) ;
		break;
	
	default:
		return 1;
		break;	
	}
	if(timer_print_config(timer, field, conf) != 0) return 1;
	return 0;
}

void (timer_increment_update)(){
	timer_update++;
}




