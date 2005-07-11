#!/bin/sh
# **
# ** This script regenerates main POT file
# **
# **  $Id/*.php xgetpo.sh,v 1.1 2004/06/25 14/*.php35/*.php39 braverock Exp $
# **
# ** Modified from Squirrelmail for XRMS by Brian Peterson

XGETTEXT_OPTIONS="--keyword=_ --default-domain=xrms --add-location --output=locale/xrms.pot --language=PHP -s "

cp xrms.pot xrms.pot.bak

cd ../
xgettext ${XGETTEXT_OPTIONS} activities/*.php
xgettext ${XGETTEXT_OPTIONS} -j admin/*.php
xgettext ${XGETTEXT_OPTIONS} -j admin/acl/*.php
xgettext ${XGETTEXT_OPTIONS} -j admin/account-statuses/*.php
xgettext ${XGETTEXT_OPTIONS} -j admin/activity-resolution-types/*.php
xgettext ${XGETTEXT_OPTIONS} -j admin/activity-templates/*.php
xgettext ${XGETTEXT_OPTIONS} -j admin/activity-types/*.php
xgettext ${XGETTEXT_OPTIONS} -j admin/campaign-statuses/*.php
xgettext ${XGETTEXT_OPTIONS} -j admin/campaign-types/*.php
xgettext ${XGETTEXT_OPTIONS} -j admin/case-priorities/*.php
xgettext ${XGETTEXT_OPTIONS} -j admin/case-statuses/*.php
xgettext ${XGETTEXT_OPTIONS} -j admin/case-types/*.php
xgettext ${XGETTEXT_OPTIONS} -j admin/categories/*.php
xgettext ${XGETTEXT_OPTIONS} -j admin/company-sources/*.php
xgettext ${XGETTEXT_OPTIONS} -j admin/company-types/*.php
xgettext ${XGETTEXT_OPTIONS} -j admin/country-address-format/*.php
xgettext ${XGETTEXT_OPTIONS} -j admin/crm-statuses/*.php
xgettext ${XGETTEXT_OPTIONS} -j admin/export/*.php
xgettext ${XGETTEXT_OPTIONS} -j admin/import/*.php
xgettext ${XGETTEXT_OPTIONS} -j admin/industries/*.php
xgettext ${XGETTEXT_OPTIONS} -j admin/opportunity-statuses/*.php
xgettext ${XGETTEXT_OPTIONS} -j admin/plugin/*.php
xgettext ${XGETTEXT_OPTIONS} -j admin/ratings/*.php
xgettext ${XGETTEXT_OPTIONS} -j admin/relationship-types/*.php
xgettext ${XGETTEXT_OPTIONS} -j admin/reports/*.php
xgettext ${XGETTEXT_OPTIONS} -j admin/salutations/*.php
xgettext ${XGETTEXT_OPTIONS} -j admin/system-parameters/*.php
xgettext ${XGETTEXT_OPTIONS} -j admin/users/*.php
xgettext ${XGETTEXT_OPTIONS} -j campaigns/*.php
xgettext ${XGETTEXT_OPTIONS} -j calendar/*.php
xgettext ${XGETTEXT_OPTIONS} -j cases/*.php
xgettext ${XGETTEXT_OPTIONS} -j companies/*.php
xgettext ${XGETTEXT_OPTIONS} -j contacts/*.php
xgettext ${XGETTEXT_OPTIONS} -j doc/*.php
xgettext ${XGETTEXT_OPTIONS} -j doc/developers/*.php
xgettext ${XGETTEXT_OPTIONS} -j doc/users/*.php
xgettext ${XGETTEXT_OPTIONS} -j email/*.php
xgettext ${XGETTEXT_OPTIONS} -j files/*.php
xgettext ${XGETTEXT_OPTIONS} -j files/storage/*.php
#xgettext ${XGETTEXT_OPTIONS} -j img/*.php
xgettext ${XGETTEXT_OPTIONS} -j include/*.php
xgettext ${XGETTEXT_OPTIONS} -j include/adodb/*.php
xgettext ${XGETTEXT_OPTIONS} -j include/classes/*.php
xgettext ${XGETTEXT_OPTIONS} -j include/classes/SMTPs/*.php
xgettext ${XGETTEXT_OPTIONS} -j include/classes/Pager/*.php
xgettext ${XGETTEXT_OPTIONS} -j include/classes/QuickForm/*.php
xgettext ${XGETTEXT_OPTIONS} -j include/classes/acl/*.php

# these directories shouldn't need to be scanned
#xgettext ${XGETTEXT_OPTIONS} -j include/adodb/contrib/*.php
#xgettext ${XGETTEXT_OPTIONS} -j include/adodb/cute_icons_for_site/*.php
#xgettext ${XGETTEXT_OPTIONS} -j include/adodb/datadict/*.php
#xgettext ${XGETTEXT_OPTIONS} -j include/adodb/docs/*.php
#xgettext ${XGETTEXT_OPTIONS} -j include/adodb/drivers/*.php
#xgettext ${XGETTEXT_OPTIONS} -j include/adodb/lang/*.php
#xgettext ${XGETTEXT_OPTIONS} -j include/adodb/perf/*.php
#xgettext ${XGETTEXT_OPTIONS} -j include/adodb/session/*.php
#xgettext ${XGETTEXT_OPTIONS} -j include/adodb/session/old/*.php
#xgettext ${XGETTEXT_OPTIONS} -j include/adodb/tests/*.php
#xgettext ${XGETTEXT_OPTIONS} -j include/adodb/xsl/*.php
#xgettext ${XGETTEXT_OPTIONS} -j include/decode/*.php
#xgettext ${XGETTEXT_OPTIONS} -j include/encode/*.php
#xgettext ${XGETTEXT_OPTIONS} -j include/lang/*.php

xgettext ${XGETTEXT_OPTIONS} -j install/*.php
xgettext ${XGETTEXT_OPTIONS} -j js/*.php
xgettext ${XGETTEXT_OPTIONS} -j js/jscalendar/*.php
#xgettext ${XGETTEXT_OPTIONS} -j js/jscalendar/lang/*.php
xgettext ${XGETTEXT_OPTIONS} -j locale/*.php
#xgettext ${XGETTEXT_OPTIONS} -j locale/de_DE/*.php
#xgettext ${XGETTEXT_OPTIONS} -j locale/en_US/*.php
#xgettext ${XGETTEXT_OPTIONS} -j locale/es_ES/*.php
#xgettext ${XGETTEXT_OPTIONS} -j locale/es_ES/LC_MESSAGES/*.php
#xgettext ${XGETTEXT_OPTIONS} -j locale/fr_FR/*.php
xgettext ${XGETTEXT_OPTIONS} -j notes/*.php
xgettext ${XGETTEXT_OPTIONS} -j opportunities/*.php
xgettext ${XGETTEXT_OPTIONS} -j plugins/*.php
#xgettext ${XGETTEXT_OPTIONS} -j plugins/censusfactsheet/*.php
xgettext ${XGETTEXT_OPTIONS} -j plugins/cti/*.php
xgettext ${XGETTEXT_OPTIONS} -j plugins/demo/*.php
xgettext ${XGETTEXT_OPTIONS} -j plugins/dunfinder/*.php
xgettext ${XGETTEXT_OPTIONS} -j plugins/idphoto/*.php
xgettext ${XGETTEXT_OPTIONS} -j plugins/info/*.php
#xgettext ${XGETTEXT_OPTIONS} -j plugins/journal/*.php
xgettext ${XGETTEXT_OPTIONS} -j plugins/mapquest/*.php
xgettext ${XGETTEXT_OPTIONS} -j plugins/mrtg/*.php
xgettext ${XGETTEXT_OPTIONS} -j plugins/opportunitynotes/*.php
xgettext ${XGETTEXT_OPTIONS} -j plugins/phone/*.php
xgettext ${XGETTEXT_OPTIONS} -j plugins/radtest/*.php
xgettext ${XGETTEXT_OPTIONS} -j plugins/vcard/*.php
#xgettext ${XGETTEXT_OPTIONS} -j plugins/webcalendar/*.php
xgettext ${XGETTEXT_OPTIONS} -j plugins/webform/*.php
xgettext ${XGETTEXT_OPTIONS} -j private/*.php
xgettext ${XGETTEXT_OPTIONS} -j relationships/*.php
xgettext ${XGETTEXT_OPTIONS} -j reports/*.php
xgettext ${XGETTEXT_OPTIONS} -j rss/*.php
xgettext ${XGETTEXT_OPTIONS} -j sql/*.php
xgettext ${XGETTEXT_OPTIONS} -j sql/mysql/*.php
xgettext ${XGETTEXT_OPTIONS} -j tmp/*.php

# don't forget to add new directories as they are needed
       
#cd locale
