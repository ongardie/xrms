#!/bin/sh
# **
# ** This script regenerates main POT file
# **
# **  $Id: xgetpo.sh,v 1.1 2004/06/25 14:35:39 braverock Exp $
# **
# ** Modified from Squirrelmail for XRMS by Brian Peterson
 
XGETTEXT_OPTIONS="--keyword=_ --default-domain=xrms --add-location --output=locale/xrms.pot --language=PHP -s "

cp xrms.pot xrms.pot.bak


cd ../
xgettext ${XGETTEXT_OPTIONS} notes/*.php 
xgettext ${XGETTEXT_OPTIONS} -j files/*.php 
xgettext ${XGETTEXT_OPTIONS} -j activities/*.php
xgettext ${XGETTEXT_OPTIONS} -j campaigns/*.php 
xgettext ${XGETTEXT_OPTIONS} -j cases/*.php
xgettext ${XGETTEXT_OPTIONS} -j companies/*.php
xgettext ${XGETTEXT_OPTIONS} -j contacts/*.php 
xgettext ${XGETTEXT_OPTIONS} -j email/*.php  
xgettext ${XGETTEXT_OPTIONS} -j opportunities/*.php  
xgettext ${XGETTEXT_OPTIONS} -j private/*.php 
xgettext ${XGETTEXT_OPTIONS} -j reports/*.php 
xgettext ${XGETTEXT_OPTIONS} -j include/*.php 
xgettext ${XGETTEXT_OPTIONS} -j install/*.php
xgettext ${XGETTEXT_OPTIONS} -j admin/*.php 


# no php files in doc yet
#  	 plugins/*.php \ 
#        doc/*.php \
#        doc/user/*.php \

# need special sections for admin and plugins
# don't forget to add these later
# don't forget to add new directories as they are needed
       
#cd locale
