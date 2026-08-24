# name of the program (Minix service)
PROG=proj

.PATH: ${.CURDIR}/devices
.PATH: ${.CURDIR}/entities
.PATH: ${.CURDIR}/xpm
.PATH: ${.CURDIR}/game



# source code files to be compiled
SRCS = main.c game.c video.c keyboard.c mouse.c timer.c utils.c menu_state.c KBC.c info_state.c entities.c font.c score.c

# additional compilation flags
CFLAGS += -pedantic -I .
CFLAGS += -D_LCOM_OPTIMIZED_
# list of library dependencies (for Lab 2, only LCF library)
DPADD += ${LIBLCF}
LDADD += -llcf

# include LCOM's makefile that does all the "heavy lifting"
.include <minix.lcom.mk>
