#include <stdio.h>

#include "gfile.h"

char *to_nl ( char *c )
{
  while ( *c && *c != '\n' ) {
    c++;
  }
  if ( *c == '\n' ) {
    c++;
  }
  return c;
}

#ifdef DOC

// generate ez and andrew-insert
application/andrew-inset	ez
// generate applefile
application/applefile
// generate cmd
application/index.cmd

#endif // DOC

// generate php code
void g_code ( char *mtype, char *mext )
{
  printf("\t'%s' => '%s',\n",mext,mtype);
}

// gen from mime type
void g_h1 ( char *mtype )
{
  // extension
  char ext[256];
  int idx;

  idx = strlen(mtype) - 1;

  while ( mtype[idx] != '/' ) {
    if ( mtype[idx] == '.' ) {
      break;
    }
    idx--;
  }
  idx++;
  strcpy(ext,&mtype[idx]);

  g_code ( mtype, ext );
}

void sho ( char *x )
{
  printf("sho: ");
  while ( *x && *x != '\n' ) {
    printf("%c",*x);
    x++;
  }
  printf("\n");
}

// process file
void g_mime( void )
{
  char *res;
  int resSize;
  char *c;

  // mime type
  char type[256];
  char *src,*dst;
  int cnt;

  // extension
  char ext[256];

  // get the file
  gfile("mime.types",&res,&resSize);

  // process entire file
  c = res;
  while ( *c ) {

    if ( *c == ' ' || *c == '\t' ) {
      c++;
      continue;
    }
    if ( *c == '#' ) {
      c = to_nl ( c );
      continue;
    }
    if ( *c == '\n' ) {
      c = to_nl ( c );
      continue;
    }

    // get the mime type
    dst = type;
    while ( *c != ' ' && *c != '\t' && *c != '\n' && *c != 0 ) {
      *dst++ = *c++;
    }
    *dst = 0;

    //printf("type = <%s>\n",type);

    // generate entry from mime type
    g_h1 ( type );

    // now get extensions
    while ( *c != 0 && *c != '\n' ) {
      if ( *c == ' ' || *c == '\t' ) {
	c++;
	continue;
      }

      dst = ext;
      while ( *c != '\n' && *c != 0 && *c != ' ' && *c != '\t' ) {
	*dst++ = *c++;
      }
      *dst = 0;

      // generate entry
      g_code( type, ext );
    }
    if ( *c == '\n' ) c++;
  }
}

// main entry point
int main()
{
  g_mime();
  return 0;
}
