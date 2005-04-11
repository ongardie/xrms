CTI / Asterisk Out Dial XRMS Plugin v0.1
uses the Asterisk Open Source Soft PBX from:
http://www.asterisk.org/

copyright 2004 Glenn Powers <glenn@net127.com>
Licensed Under the Open Software License v. 2.0

*** THIS PLUGIN IS UNDER ACTIVE DEVELOPMENT ***

YOU *MUST* INSTALL THE TABLE IN cti-call-queue.sql to your database
BEFORE you activate this plugin.  If you don't, you'll get a Javascript
error every second on every page.

The screen pop function uses the JPSpan library. (It's one of those
cool Ajax things you've been hearing about.)

The Cisco 7960 config writer currently provides only basic functionality.
Specifically, you can't edit a config file with XRMS once you create it.

XRMS Voice Mail Plugin v0.1
for use with the Asterisk Open Source PBX http://www.asterisk.org/

see voicemail-install.txt

The XRMS username is used to look up the correct extension in
/etc/asterisk/voicemail.conf If the XRMS username is not in voicemail.conf,
this plugin will not show any messages.  The *last* match is used.
This is a hack. Patches welcomed.

