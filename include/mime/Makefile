CC = gcc

.c.o:
	$(CC) -c $*.c

OBJS = gfile.o gmime.o

all: gmime

gmime: $(OBJS)
	$(CC) -o gmime $(OBJS)



