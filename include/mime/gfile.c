#include <stdio.h>
#include <assert.h>
#include <fcntl.h>
#include <sys/types.h>
#include <sys/stat.h>
#include <stdlib.h>
#include <unistd.h>

#include "gfile.h"

/* get a file into a memory buffer */
int gfile ( char *fname, char **res, int *resSiz )
{
  struct stat sb;
  char *buf;
  int fd;
  size_t siz;

  if ( stat ( fname, &sb ) < 0 ) {
    *res = NULL;
    *resSiz = 0;
    return 0;
  }

  buf = (char *)malloc( sb.st_size + 1 );
  assert(NULL!=buf);

  fd = open ( fname, O_RDONLY );
  if ( fd < 0 ) {
    fprintf(stderr,
	    "gfile: can't open file <%s>\n",
	   fname);
    exit(0);
  }
  siz = read ( fd, buf, sb.st_size );
  if ( siz != sb.st_size ) {
    fprintf(stderr,
	    "gfile: can't read file <%s>\n",
	   fname);
    exit(0);
  }
  buf[sb.st_size] = 0;
  *res = buf;
  *resSiz = sb.st_size;

  close(fd);

  /* get rid of ^m's */
  {
    char *src;
    char *dst;

    src = buf;
    dst = buf;
    while ( *src != 0 ) {
      if ( src[0] == '\r' && src[1] == '\n' ) {
	*dst++ = '\n';
	src += 2;
	*resSiz -= 1;
	continue;
      }
      *dst++ = *src++;
    }
    *dst = 0;
  }

  return 1;
}
