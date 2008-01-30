<?php
/**
 * Edit the details for a single Activity
 *
 * $Id: one.php,v 1.144 2008/01/30 21:17:31 gpowers Exp $
 */

//include required files
require_once('../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-preferences.php');
require_once($include_directory . 'utils-activities.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');
require_once($include_directory . 'confgoto.php');
require_once('../activities/activities-widget.php');

//get session
$session_user_id = session_check();

//get variables
getGlobalVar($activity_id, 'activity_id');
getGlobalVar($msg, 'msg');
getGlobalVar($return_url, 'return_url');
getGlobalVar($print_view, 'print_view');
$save_and_next = isset($_GET['save_and_next']) ? true : false;

//check, set required variables
if (!$return_url) $return_url = '/activities/some.php';
if (!$activity_id) {
    header("Location: " . $http_site_root . $return_url . '?msg=' . urlencode(_("Error: No Activity ID Specified")));
    exit;
}

//open database connection
$con = get_xrms_dbconnection();
//$con->debug = 1;

//update recent activities
update_recent_items($con, $session_user_id, "activities", $activity_id);
update_daylight_savings($con);

//get activity data
$activity_rst = get_activity($con,$activity_id,$show_deleted=false, $return_rst=true);

//get activity_recurrence_id
if ((!$activity_rst) || ($activity_rst->EOF)) {
    $msg = urlencode(_("Activity #") . $activity_id . " " . _("not found!"));
    header("Location: " . $http_site_root . $return_url . "?msg=" . $msg);
    exit;
}

//set recurrance id
$recurrance_sql = "SELECT activity_recurrence_id FROM activities_recurrence where activity_id='" . $activity_id . "'";
$recurrence_rst=$con->execute($recurrance_sql);
    if (!$recurrence_rst) db_error_handler($con, $recurrance_sql);
    if ($recurrence_rst->fields['activity_recurrence_id']) {
        $activity_rst->fields['activity_recurrence_id'] = $recurrence_rst->fields['activity_recurrence_id'];
    }



// Instantiating variables for each activity field, so that  fields
// are accessible to plugin code without an extra read from database.
foreach ($activity_rst->fields as $activity_field => $activity_field_value ) {
    $$activity_field = $activity_field_value;
}

//format dates and times
$entered_at = date($datetime_format, strtotime($activity_rst->fields['entered_at']));
$last_modified_at = date($datetime_format, strtotime($activity_rst->fields['last_modified_at']));
$scheduled_at = date($datetime_format, strtotime($activity_rst->fields['scheduled_at']));
$ends_at = date($datetime_format, strtotime($activity_rst->fields['ends_at']));

//close result set
$activity_rst->close();

//activity template
//this should be a system or user preference
require_once('templates/v1.99.php');


$con->close();

end_page();

/**
 * $Log: one.php,v $
 * Revision 1.144  2008/01/30 21:17:31  gpowers
 * - added support for activity templates
 * - large chunk of code moved to activities/templates/v1.99.php
 *
 * Revision 1.143  2007/12/10 22:34:32  gpowers
 * - added system preference for html activity notes (uses tinymce)
 * - move $con->close(); to end of file.
 *
 * Revision 1.142  2006/11/14 19:55:21  braverock
 * - special handling for unknown company
 *   based on patches by fcrossen
 *
 * Revision 1.141  2006/10/17 21:46:05  braverock
 * - added user email menu
 * - modified to correctly show only related activities belonging to the same company
 * - avoided bad display behaviour of the resolution description field content
 *    modified from 2006/07/31 patch by dbaudone
 *
 * Revision 1.140  2006/08/23 21:17:17  jnhayart
 * prevent error in javascrip if localisation need use quote
 * and syntax for localisation of java display
 *
 * Revision 1.139  2006/06/29 15:53:46  braverock
 * - remove extra whitespace from resolution_description field.
 *   - patch by Frederik Jervfors
 *
 * Revision 1.138  2006/06/21 15:51:25  jswalter
 *  - LOCATION list does not default to address selected for a given activity. This was corrected.
 *
 * Revision 1.137  2006/05/06 09:32:18  vanmer
 * - added passthrough for old status seperately from status in dropdown
 *
 * Revision 1.136  2006/05/02 00:40:16  vanmer
 * - moved recurrence check back into activities/one.php
 *
 * Revision 1.135  2006/05/01 19:32:24  braverock
 * - use get_activity API call
 *
 * Revision 1.134  2006/04/28 16:31:38  braverock
 * - use get_activity API call
 *   - move created,modified,completed by processing into API
 *   - standardize how variables are assigned from the result set
 *   - limit processing in this file to UI-directed items
 *
 * Revision 1.133  2006/01/19 22:20:32  daturaarutad
 * add Print View button which displays textarea as a static element
 *
 * Revision 1.132  2006/01/19 16:22:11  braverock
 * - add class="print" to the main form to aid in printing support
 *
 *
 * Revision 1.131  2006/01/10 08:47:01  gpowers
 * - added activity_content_top plugin hook
 * - added activity_sidebar_top plugin hook
 * - added limiting of opp. statuses by opp. types
 *
 * Revision 1.130  2006/01/02 21:23:18  vanmer
 * - changed to use centralized database connection function
 *
 * Revision 1.129  2005/12/18 02:56:23  vanmer
 * - changed to use gmt_offset fieldname
 *
 * Revision 1.128  2005/11/04 16:26:50  braverock
 * - clear ends_at time if scheduled and end times are the same
 *   and activity uncompleted
 * - rationalizes time for activities like phone calls
 *
 * Revision 1.127  2005/10/08 21:07:52  vanmer
 * - added hidden variables to track actions for participants subsystem
 *
 * Revision 1.126  2005/09/29 14:49:10  vanmer
 * - added lookup of activity template id, to provide to attachment sidebar
 *
 * Revision 1.125  2005/09/25 04:12:23  vanmer
 * - added ability to detach an activity from an on_what_table/on_what_id relationship using Detach button
 * - added case to check for $on_what_id before attempting to query for activity attachmetn
 * - added error handling on sql errors when querying for a name of the activity's attached entity
 *
 * Revision 1.124  2005/09/23 20:55:40  daturaarutad
 * add space after thread_id clause in extra_where
 *
 * Revision 1.123  2005/09/21 20:08:34  vanmer
 * - added menu for location of activity
 * - removed address table from query on activity, unused and overrides activity address_id
 *
 * Revision 1.122  2005/09/08 21:30:43  vanmer
 * - changed to have edit button reference correctly the activity rather than whatever $on_what_table and $on_what_id
 * happen to be
 *
 * Revision 1.121  2005/08/11 02:36:52  vanmer
 * - Added sidebar to control activity association
 * - moved button from main form to sidebar
 *
 * Revision 1.120  2005/08/02 22:00:43  ycreddy
 * Added Last Modified By and Last Modified At fields to the details
 *
 * Revision 1.119  2005/07/31 17:41:32  braverock
 * - make changes to improve functioning even if register_globals is 'on'
 *
 * Revision 1.118  2005/07/15 22:52:53  vanmer
 * - changed join to reflect activities without a company
 * - changed User field to be listed as Owner instead, to reflect standard field labels
 *
 * Revision 1.117  2005/07/08 14:49:57  braverock
 * - fix to properly handle saving activities that are not part of workflow activity templates
 * - trim description fields
 *
 * Revision 1.116  2005/07/08 14:40:25  braverock
 * - set textarea for resolution to be the same size as the other textarea's on the page
 *
 * Revision 1.115  2005/07/08 01:30:09  vanmer
 * - changed Change button into Change Attachment button
 * - changed to redirect and save instead of going immediately to change the attachment
 *
 * Revision 1.114  2005/07/08 01:18:56  braverock
 * - localize button strings
 *
 * Revision 1.113  2005/07/08 01:07:24  vanmer
 * - changed to try to show attached entity if possible
 *
 * Revision 1.112  2005/07/08 00:53:25  vanmer
 * - added change button to reconnect activity to another entity
 *
 * Revision 1.111  2005/07/07 20:54:49  vanmer
 * - changed return_url path from activities into sidebars
 *
 * Revision 1.110  2005/07/07 03:38:46  daturaarutad
 * updated to use new activities-widget functions
 *
 * Revision 1.109  2005/06/30 17:32:27  vanmer
 * - added javascript and needed ID's to form elements to allow resolution fields to be hidden before activity is
 * completed
 *
 * Revision 1.108  2005/06/30 04:39:44  vanmer
 * - added UI for resolution description, resolution types and activity priority
 *
 * Revision 1.107  2005/06/27 16:31:46  braverock
 * - add Entered By into main screen after many requests
 * - fix localization of several strings
 *
 * Revision 1.106  2005/06/08 17:36:28  daturaarutad
 * updated rst->activity_rst to fix broken page
 *
 * Revision 1.105  2005/06/08 15:31:24  braverock
 * - add activity_inline_edit hook
 *
 * Revision 1.104  2005/06/03 22:55:45  braverock
 * - change the logic for recurring activities pager to exclude on_what_table of
 *   companies or contacts
 *
 * Revision 1.103  2005/06/03 20:58:22  daturaarutad
 * moved recurrence configuration widget to its own page
 *
 * Revision 1.102  2005/06/03 12:53:59  braverock
 * - remove 'Switch Opportunity' contact switching, as this is confusing to users
 * - take out nbsp; tags from inside strings that are better combined for i18n
 *
 * Revision 1.101  2005/05/25 21:35:53  braverock
 * - improve color CSS style rendering on related activities pager
 *
 * Revision 1.100  2005/05/25 15:10:52  braverock
 * - changed to urlencode the string for localized error msg
 *
 * Revision 1.99  2005/05/25 14:55:32  braverock
 * - add error message and return if no activity_id is passed in
 *
 * Revision 1.98  2005/05/25 05:37:58  vanmer
 * - added output to display completed_by and completed_at when an activity is completed, next to the checked
 * completed box.
 *
 * Revision 1.97  2005/05/25 05:35:51  daturaarutad
 * added the activity recurrence sidebar
 *
 * Revision 1.96  2005/05/19 20:29:41  daturaarutad
 * added support for followup activities
 *
 * Revision 1.95  2005/05/19 13:20:43  maulani
 * - Remove trailing whitespace
 *
 * Revision 1.94  2005/05/10 21:31:43  braverock
 * - modify so selectable columns widget is rendered inside the <html> and <form> tags
 *
 * Revision 1.93  2005/05/05 23:01:02  braverock
 * - changed labels on Summary, Scheduled Start, Scheduled End columns in pager
 *   for consistency in naming conventions
 *
 * Revision 1.92  2005/05/05 21:38:31  daturaarutad
 * added Related Activities Pager and changed $_GET usage to getGlobalVar
 *
 * Revision 1.91  2005/05/04 14:33:37  braverock
 * - removed obsolete widget_label_right_166px CSS style, replaces w/ widget_label_right
 *
 * Revision 1.90  2005/05/04 14:30:40  braverock
 * - fix CSS style for 'Activity Notes'
 *
 * Revision 1.89  2005/05/04 14:27:28  braverock
 * - change Activity 'Title' to 'Summary' for consistency
 *
 * Revision 1.88  2005/05/04 13:39:50  braverock
 * - change 'Start' to 'Scheduled Start' for consistenct of activity start time labels
 * - change 'End' to 'Scheduled End' for consistenct of activity end time labels
 *
 * Revision 1.87  2005/04/28 15:31:35  braverock
 * - applied patch for clearing case/opp/campaign id on editing of activities
 *   patch supplied by Miguel Gon�alves (mig77)
 *
 * Revision 1.86  2005/04/20 21:26:27  braverock
 * - change $on_what_table to 'activities' before calling file sidebar
 *
 * Revision 1.85  2005/04/18 23:34:13  maulani
 * - participant sidebar include was stomping on $return_url variable.  Changed
 *   variable name to resolve conflict in activities/one.php
 *
 * Revision 1.84  2005/04/18 16:32:39  vanmer
 * - changed default behavior when clicking the delete button: used to redirect to arbitrary return_url (by default same page)
 * - now redirects back to /activities/some.php
 *
 * Revision 1.83  2005/04/15 07:48:21  vanmer
 * - added sidebar for display of activity participants
 *
 * Revision 1.82  2005/04/07 17:45:43  vanmer
 * - added NULL parameter to do_hook_function, to fulfill new requirement of passing a second parameter to do_hook_function
 *
 * Revision 1.81  2005/03/22 22:12:43  gpowers
 * - added activity_content_bottom plugin hook
 *
 * Revision 1.80  2005/03/21 14:45:42  maulani
 * - Display optional id and other info about activity.  Option controlled
 *   with system parameter.  ID is useful for developers tracking bugs in
 *   production.
 *
 * Revision 1.79  2005/03/21 14:38:31  maulani
 * - Having unassigned activities is now an option that can be set in
 *   system parameters.  Installations that do not need activity pools
 *   can require activities to have an assigned user.
 *
 * Revision 1.78  2005/03/21 13:40:51  maulani
 * - Remove redundant code by centralizing common user menu call
 *
 * Revision 1.77  2005/02/10 21:16:43  maulani
 * - Add audit trail entries
 *
 * Revision 1.76  2005/01/22 15:07:14  braverock
 * - add sort order to activity_types menu
 *
 * Revision 1.75  2005/01/10 21:43:32  vanmer
 * - added types so that status dropdown can operate properly when activity is attached to a case with case types
 *
 * Revision 1.74  2005/01/10 20:47:47  neildogg
 * - Changed to support new relationship sidebar variable requirement
 *
 * Revision 1.73  2005/01/09 18:08:57  vanmer
 * - moved definition activity_id to above session_check (for ACL)
 * - added default return_url if none is defined
 * - changed to use render_button functions for buttons instead of direct HTML
 *
 * Revision 1.72  2004/12/31 13:46:51  braverock
 * - localize 'Entered by'
 * - sort contact list by last_name, first_names
 *
 * Revision 1.71  2004/12/30 00:13:16  maulani
 * - Display Entered by User data in addition to assigned user
 *
 * Revision 1.70  2004/12/27 15:57:21  braverock
 * - localize "Switch Opportunity"
 *
 * Revision 1.69  2004/12/20 21:50:51  neildogg
 * - Updated to reflect new parameter passing
 *
 * Revision 1.68  2004/12/20 13:50:39  neildogg
 * Added ability to select an empty user (allows an activity pool)
 *
 * Revision 1.67  2004/12/01 18:12:42  vanmer
 * - altered relationship setup section to reference relationships that relate to activities
 *
 * Revision 1.66  2004/10/31 14:14:30  braverock
 * - fixed bug that overwrote table_name, breaking link w/ opportunities/cases
 * - moved sidebar code lower in page, resolved issues with overwriting values
 * - adjusted width of textareas to solve CSS layout problem in IE
 *
 * Revision 1.65  2004/10/08 19:30:43  gpowers
 * - added file attachment sidebar to activities
 *
 * Revision 1.64  2004/09/13 21:59:03  introspectshun
 * - Changed order of tables in main query.
 *   - MSSQL chokes on the JOIN otherwise.
 *
 * Revision 1.63  2004/09/02 23:20:21  maulani
 * - Reduce textarea width to fit on 1024 wide screen
 *
 * Revision 1.62  2004/08/26 14:41:31  neildogg
 * - Display nothing if no daylight savings in address
 *
 * Revision 1.61  2004/08/25 14:34:53  neildogg
 * - Displays local time
 *  - Change position as you see fit
 *
 * Revision 1.60  2004/08/19 20:43:51  neildogg
 * - Added jump to position in save and next
 *
 * Revision 1.59  2004/08/04 18:18:11  neildogg
 * - If you're going to change one textarea
 *  - for goodness sake, change the one below it
 *
 * Revision 1.58  2004/08/04 15:58:05  maulani
 * - Narrow textarea so it will fit on 1024 x 768 screen
 * - todo to make relative positioning so adjusts for larger screens.
 *
 * Revision 1.57  2004/08/04 15:31:12  neildogg
 * - Added more plugin support
 *
 * Revision 1.56  2004/07/30 10:03:10  cpsource
 * - Remove undefines
 *     contact_block
 *     relationship_link_rows
 *
 * Revision 1.55  2004/07/30 09:45:24  cpsource
 * - Place confGoTo setup later in startup sequence.
 *
 * Revision 1.54  2004/07/29 09:35:47  cpsource
 * - Seperate .js and .php for confGoTo for PHP V4 problems.
 *
 * Revision 1.53  2004/07/28 20:55:32  neildogg
 * - Added parenthesis around save and next numbers
 *
 * Revision 1.52  2004/07/28 19:24:21  cpsource
 * - Move confGoTo sub-system out into a seperate file for
 *   a more structured, and general implementation.
 *
 * Revision 1.51  2004/07/27 19:50:41  neildogg
 * - Major changes to browse functionality
 *  - Removal of sidebar for "browse" button
 *  - Removal of activity_type browse
 *  - Aesthetic modifications
 *  - Date in some.php is now mySQL curdate()
 *
 * Revision 1.50  2004/07/26 12:10:26  cpsource
 * - Fix bug whereby javascript is used to confirm the
 *   delete of an activity.
 *
 * Revision 1.49  2004/07/25 20:06:57  johnfawcett
 * - standardized delete button
 *
 * Revision 1.48  2004/07/25 16:15:25  johnfawcett
 * - unified page title
 *
 * Revision 1.47  2004/07/25 12:27:42  braverock
 * - remove lang file require_once, as it is no longer used
 *
 * Revision 1.46  2004/07/22 14:06:00  neildogg
 * - Errant commit, rollback to 1.44
 *
 * Revision 1.45  2004/07/22 13:58:27  neildogg
 * - Limit group saved-search functionality to admin
 *
 * Revision 1.44  2004/07/21 13:00:54  neildogg
 * - Rolling back previous erroneous commit to reactivate sidebars
 *  - Sidebar variables are declared in the sidebar requires
 *
 * Revision 1.43  2004/07/21 11:48:47  cpsource
 * - Stub out unused right sidebar.
 *
 * Revision 1.42  2004/07/20 16:50:00  neildogg
 * - Have to remove the hidden opportunity_description AGAIN
 *
 * Revision 1.41  2004/07/20 11:25:26  braverock
 * - removed second jscalendar_includes call
 *   - it is unecessary, and causes a stack overflow on IE 6
 *   - applies fix for SF bug 976476 suggested by cdeneve
 *
 * Revision 1.40  2004/07/19 21:19:52  neildogg
 * - Allow contact to be shifted with opportunity as well as activity
 *
 * Revision 1.39  2004/07/16 04:53:51  introspectshun
 * - Localized strings for i18n/translation support
 *
 * Revision 1.38  2004/07/15 22:57:20  cpsource
 * - Post $opportunity_description
 *
 * Revision 1.37  2004/07/14 22:10:49  neildogg
 * - Now uses $overall_id
 *
 * Revision 1.36  2004/07/14 18:30:37  neildogg
 * - I don't have a calender1.js file, correct if wrong
 *  - For some reason someone added a second opportunity_description, removed
 *  - Proper form naming, since form name=form was removed
 *
 * Revision 1.35  2004/07/14 15:22:17  cpsource
 * - Fixed various undefines, including:
 *     $opportunity_description
 *     $followup
 *     $saveandnext
 *     $table_status_id
 *     $probability
 *
 * Revision 1.34  2004/07/11 15:12:40  braverock
 * - Change 'Description' to 'Activity Notes' for consistency
 *
 * Revision 1.33  2004/07/09 19:41:02  neildogg
 * - Now matches normal description textarea width\n- Break before Insert button
 *
 * Revision 1.32  2004/07/09 15:50:56  neildogg
 * - Uses the new, generic relationship sidebar
 *
 * Revision 1.31  2004/07/08 12:27:05  braverock
 * - clean up formatting of probability syntax for less parser switching and easier readability
 *
 * Revision 1.30  2004/07/08 02:22:11  gpowers
 * - changed description textarea width to 90 (was 100)
 *   - this screen will now fit on a 1024x768 Windows XP display
 *     with MSIE v6.0 (Maximized)
 *
 * Revision 1.29  2004/07/07 22:23:18  neildogg
 * - Fixed lack of <?php in probability printing\n- Added logging formatting in opportunity notes
 *
 * Revision 1.28  2004/07/07 18:06:18  neildogg
 * - Added sticky opportunity description
 *
 * Revision 1.27  2004/07/02 18:09:19  neildogg
 * - Added contact-company sidebar to activities page as per new support in companies/company-sidebar.php.
 *
 * Revision 1.26  2004/06/25 03:12:41  braverock
 * - add error handling for missing variables
 *
 * Revision 1.25  2004/06/24 19:58:47  braverock
 * - committing enhancements to Save&Next functionality
 *   - patches submitted by Neil Roberts
 *
 * Revision 1.24  2004/06/13 09:15:07  braverock
 * - add Save & Next functionality
 *   - code contributed by Neil Roberts
 *
 * Revision 1.23  2004/06/11 21:20:11  introspectshun
 * - Now use ADODB Concat and Date functions.
 *
 * Revision 1.22  2004/06/10 20:30:07  braverock
 * - added ability to edit probability on linked opportunity
 *   - code contributed by Neil Roberts
 *
 * Revision 1.21  2004/06/04 15:57:24  gpowers
 * Applied Patch [ 965012 ] Calendar replacement By: miguel Gonçves - mig77
 * w/minor changes: changed includes to function, used complete php tags
 *
 * Revision 1.20  2004/06/03 17:29:04  gpowers
 * changed the order of the sidebars(contact,company) to match the order in
 * the form to the left (company,contact)
 *
 * Revision 1.19  2004/06/03 16:31:05  gpowers
 * my bad. they exist now.
 *
 * Revision 1.18  2004/06/03 16:29:58  gpowers
 * commented out the includes for the contact sidebar code and
 * the company sidebar code. these sidebars do not appear to exist.
 *
 * Revision 1.17  2004/06/03 16:11:00  braverock
 * - add functionality to support workflow and activity templates
 *   - functionality contributed by Brad Marshall
 *
 * Revision 1.16  2004/05/28 13:58:33  gpowers
 * removed "viewed" audit log entry. this is redundant, as this data is
 * already stored in httpd access logs.
 *
 * Revision 1.15  2004/05/27 20:36:12  gpowers
 * Added Support for Patch [ 951138 ] Export Activities vCALENDAR
 * Export one activity into the vCalendar format.
 *
 * Revision 1.14  2004/05/10 13:07:20  maulani
 * - Add level to audit trail
 * - Clean up audit trail text
 *
 * Revision 1.13  2004/05/07 16:15:48  braverock
 * - fixed multiple bugs with date-time formatting in activities
 * - correctly use dbtimestamp() date() and strtotime() fns
 * - add support for $default_followup_time config var
 *   - fixes SF bug  949779 reported by miguel Gon�alves (mig77)
 *
 * Revision 1.12  2004/04/27 16:42:07  gpowers
 * - fixed usertimestamp
 *
 * Revision 1.11  2004/04/27 16:28:39  gpowers
 * - added support for activity times.
 *   NOTE: usertimestamp doesn't appear to work. I don't know why.
 *   (The unformatted time works fine with MySQL, but may not with other DBs.)
 * - added support for activity emails.
 *
 * Revision 1.10  2004/04/26 01:54:45  braverock
 * - add ability to schedule a followup activity based on the current activity
 *
 * Revision 1.9  2004/04/19 22:21:15  maulani
 * - Correct javascript syntax
 *
 * Revision 1.8  2004/04/17 16:02:40  maulani
 * - Add CSS2 positioning
 *
 * Revision 1.7  2004/04/16 22:21:19  maulani
 * - Add CSS2 positioning
 *
 * Revision 1.6  2004/03/22 22:44:29  braverock
 * - add htmlspecialchars around activity_title and activity_description
 *   - fixes SF bug 921295
 *
 * Revision 1.5  2004/03/15 14:51:28  braverock
 * - fix ends-at display bug
 * - make sure both scheduled_at and ends_at have legal values
 * - add phpdoc
 */
?>
