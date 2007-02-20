Case Hours Plugin for XRMS (v1.0)

copyright 2007 The XRMS Development Team
Licensed under the same license as XRMS. See http://www.xrms.org for more information.

This plugin was developed by Francis Crossen and sponsored by Concept Info, Dublin,
Ireland, http://www.conceptinfo.ie.

This plugin is used to track the number of hours spent on a case.

The plugin requires no installation - simply enable it in the Plugin Administration
section of XRMS.

The first time it is used, the plugin will automatically create three activity types
as follows (assuming they don't already exist). You should make sure you don't have
activity types in your system with these names and descriptions.

Short Name  Full Name          Full Plural Name   Display HTML       Score Adjustment
'H-B'       'Hours - Billable' 'Hours - Billable' 'Hours - Billable' 0
'H-C'       'Hours - Contract' 'Hours - Contract' 'Hours - Contract' 0
'H-I'	      'Hours - Internal' 'Hours - Internal' 'Hours - Internal' 0

To enter hours, create an activity of the correct type (i.e. one of the three types
above). Enter the start time ('Scheduled Start') and the finish time ('Scheduled End')
and mark the activity as 'Complete'. You MUST choose a 'Resolution Type' for the
activity in order to be able to total the hours spent on all activities of that
activity type.

The hours are displayed on the Case Details screen.

Note: If there are one or more 'Hours' activities marked as 'Complete' WITHOUT a
'Resolution Type' entered, you will see an error (x NULLS, where 'x' is is the number
of activities without a 'Resolution Type' set). To correct this, make sure the 'Resolution
Type' is set for all activities of that type. A mouseover the NULLs message will
give a more helpful message.

If you like this plugin, send an email to joe@conceptinfo.ie and tell him what you
think - he'll be chuffed! It was all his idea and I wouldn't have developed it otherwise -
I have no need for this functionality in my office.

Regards,
Francis Crossen <fcrossen@users.sourceforge.net>
