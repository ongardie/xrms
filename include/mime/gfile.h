#ifndef __gfile_h__
#define __gfile_h__

#ifdef __cplusplus
extern "C" {
#endif

/* get a file into a memory buffer */
extern int gfile ( char *fname, char **res, int *resSiz );

#ifdef __cplusplus
};
#endif


#endif /* __gfile_h__ */
