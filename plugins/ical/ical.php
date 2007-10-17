<?php
/*
*
* iCal plugin
* 
* @author Randy Martinsen <randym56@hotmail.com>
*
* $Id: ical.php,v 1.1 2007/10/17 00:56:27 randym56 Exp $
*
* NOTE: Required modification of activities_widget.php v1.58 to show the button when this plugin is activated
*
*/

include_once('class.iCal.inc.php');
require_once('../../include-locations.inc'); //root of XRMS

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');
require_once($include_directory . 'classes/Pager/GUP_Pager.php');
require_once($include_directory . 'classes/Pager/Pager_Columns.php');

$session_user_id = session_check();
$msg = $_GET['msg'];

$con = get_xrms_dbconnection();
//$con->debug = 1;

$array_of_activities = array();

$sql=$_SESSION["search_sql"];

$rst = $con->execute($sql);

if ($rst) {
        while (!$rst->EOF) {
                array_push($array_of_activities, $rst->fields['activity_id']);
                $rst->movenext();
        }
}

if (is_array($array_of_activities))
    $imploded_activities = implode(',', $array_of_activities);
else
    echo _("WARNING: No array of activities!") . "<br>";

$select = "SELECT a.*, at.activity_type_pretty_name AS type, CONCAT(cont.first_names,' ',cont.last_name) AS contact, cont.email as contact_email, "
        . "CONCAT(u.first_names,' ',u.last_name) AS owner, u.user_id, u.email as owner_email, cp.case_priority_pretty_name, rt.resolution_short_name, " 
                . "c.company_name ";

    $from = "FROM activities a";

    $joins = "
    INNER JOIN activity_types at ON a.activity_type_id = at.activity_type_id
    LEFT OUTER JOIN contacts cont ON a.contact_id = cont.contact_id
    LEFT OUTER JOIN companies c ON a.company_id = c.company_id
    LEFT OUTER JOIN users u ON a.user_id = u.user_id
    LEFT OUTER JOIN activity_participants ON a.activity_id=activity_participants.activity_id
    LEFT OUTER JOIN contacts part_cont ON part_cont.contact_id=activity_participants.contact_id
    LEFT OUTER JOIN case_priorities cp ON a.activity_priority_id=cp.case_priority_id
    LEFT OUTER JOIN activity_resolution_types rt ON a.activity_resolution_type_id=rt.activity_resolution_type_id";

    $where = " WHERE a.activity_record_status = 'a' AND a.activity_id IN ($imploded_activities)";
        
$sql = "$select $from $joins $where";
        

$rst = $con->execute($sql);
if ($rst) {
        $iCal = (object) new iCal('', 1, ''); // (ProgrammID, Method (1 = Publish | 0 = Request), Download Directory)
    while (!$rst->EOF) {
                $activity_link = $http_site_root ."/activities/one.php?activity_id=".$rst->fields['activity_id'];
                $organizer = (array) array($rst->fields['owner'],$rst->fields['owner_email']);
                $attendees = (array) array($rst->fields['contact'],$rst->fields['contact_email']);
                $categories = (array) array('XRMS');
                $description = $rst->fields['contact']. " - " .$rst->fields['contact_email'] . "\n". $rst->fields['activity_description']."\n".$rst->fields['on_what_table'] . "\n" . $activity_link;
                $alarm = (array) array(
                          0, // Action: 0 = DISPLAY, 1 = EMAIL, (not supported: 2 = AUDIO, 3 = PROCEDURE)
                          30,  // Trigger: alarm before the event in minutes
                          '', // Title - Must leave blank for MS outlook
                          'Reminder', // Description - must be set to Reminder for MS Outlook
                          '', // Array (key = attendee name, value = e-mail, second value = role of the attendee [0 = CHAIR | 1 = REQ | 2 = OPT | 3 =NON])
                          0, // Duration between the alarms in minutes
                          0  // How often should the alarm be repeated
                          );

                $iCal->addEvent(
                                $organizer, // Organizer array('name', 'email')
                                strtotime($rst->fields['scheduled_at']), // Start Time (timestamp; for an allday event the startdate has to start at YYYY-mm-dd 00:00:00)
                                strtotime($rst->fields['ends_at']), // End Time (write 'allday' for an allday event instead of a timestamp)
                                '', // Location
                                0, // Transparancy (0 = OPAQUE | 1 = TRANSPARENT)
                                $categories, // Array with Strings
                                $description, // Description
                                $rst->fields['activity_title'] . " -> Contact: " . $rst->fields['contact'] . " -> Type: ".$rst->fields['type'], // Title
                                1, // Class (0 = PRIVATE | 1 = PUBLIC | 2 = CONFIDENTIAL)
                                $attendees, // Array (key = attendee name, value = e-mail, second value = role of the attendee [0 = CHAIR | 1 = REQ | 2 = OPT | 3 =NON])
                                $rst->fields['activity_priority_id'], // Priority = 0-9
                                0, // frequency: 0 = once, secoundly - yearly = 1-7
                                0, // recurrency end: ('' = forever | integer = number of times | timestring = explicit date)
                                0, // Interval for frequency (every 2,3,4 weeks...)
                                '', // Array with the number of the days the event accures (example: array(0,1,5) = Sunday, Monday, Friday
                                0, // Startday of the Week ( 0 = Sunday - 6 = Saturday)
                                '', // exeption dates: Array with timestamps of dates that should not be includes in the recurring event
                                $alarm,  // Sets the time in minutes an alarm appears before the event in the programm. no alarm if empty string or 0
                                1, // Status of the event (0 = TENTATIVE, 1 = CONFIRMED, 2 = CANCELLED)
                                '', // optional URL for that event
                                'en', // Language of the Strings (en = English)
                'XRMS' // Optional UID for this event
                           );
        $rst->movenext();
    }
        $iCal->outputFile('ics'); // output file as ics (xcs and rdf possible)
    $rst->close();
}
$con->close();

/**
 * $Log: ical.php,v $
 * Revision 1.1  2007/10/17 00:56:27  randym56
 * iCal creator - requires updates to activities-widget.php.
 *
 * Revision 1.0
 *
 */
?>
