#!/bin/sh

PONAME=xrms.po
MONAME=xrms.mo

# **
# ** This script compiles locale PO files
# **
# ** Usage:   compilepo <locale id>
# ** Example: compilepo es_ES
# **
# ** Philipe Mingo <mingo@rotedic.com>
# ** Konstantin Riabitsev <icon@duke.edu>
# **
# **  $Id: compilepo.sh,v 1.3 2004/08/21 20:12:39 johnfawcett Exp $

if [ -z "$1" ]; then
 echo "USAGE: compilepo [localename]"
 exit 1
fi

WORKDIR=../locale
LOCALEDIR=$WORKDIR/$1/LC_MESSAGES

if [ ! -d $LOCALEDIR ]; then
 # lessee if it's been renamed.
 DCOUNT=`find $WORKDIR/ -name $1* | wc -l` 
 if [ $DCOUNT -eq 1 ]; then 
  # aha
  LOCALEDIR=`find $WORKDIR/ -name $1*`
 elif [ $DCOUNT -gt 1 ]; then
  # err out
  echo "More than one locale matching this name found:"
  find $WORKDIR/ -name $1*
  echo "You have to be more specific."
  exit 1
 fi
fi

POFILE=$LOCALEDIR/$PONAME
MOFILE=$LOCALEDIR/$MONAME

echo "Compiling $POFILE"
msgfmt -vvv -o $MOFILE $POFILE

