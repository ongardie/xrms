<?php
/**
 * activites/new-2.php - This page inserts a new activity into the database
 *
 * This may be called from many places in the XRMS interface, because the activities
 * may be linked from contacts, companies, cases, opportunities, or mailto links.
 *
 * This page needs to first grab submitted parameters for the activity, or set default
 * values if no value is submitted.
 *
 * Recently changed to use the getGlobalVar utility funtion so that $_GET parameters
 * could be used with mailto links.
 *
 * $Id: new-2.php,v 1.10 2004/05/07 16:15:48 braverock Exp $
 */

//where do we include from
require_once('../include-locations.inc');

//get required common files
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

//check to make sure we are logged on
$session_user_id = session_check();


//now pull all the required variables from $_GET or $_POST
getGlobalVar($return_url , 'return_url');
//need check in here for missing return_url, set to calling page

getGlobalVar($activity_type_id , 'activity_type_id');
getGlobalVar($on_what_table , 'on_what_table');
getGlobalVar($on_what_id , 'on_what_id');
getGlobalVar($activity_title , 'activity_title');
getGlobalVar($activity_description , 'activity_description');
getGlobalVar($activity_status , 'activity_status');
getGlobalVar($scheduled_at , 'scheduled_at');
getGlobalVar($ends_at , 'ends_at');
getGlobalVar($company_id , 'company_id');
getGlobalVar($contact_id , 'contact_id');
getGlobalVar($user_id    , 'user_id');
getGlobalVar($email , 'email');
getGlobalVar($followup , 'followup');

//set defaults if we didn't get values
$user_id = (strlen($user_id) > 0) ? $user_id : $session_user_id;
$activity_status = (strlen($activity_status) > 0) ? $activity_status : "o";
$activity_title = (strlen($activity_title) > 0) ? $activity_title : "[none]";
$activity_description = (strlen($activity_description) > 0) ? $activity_description : "[none]";
$on_what_table = (strlen($on_what_table) > 0) ? $on_what_table : '';
$on_what_id = ($on_what_id > 0) ? $on_what_id : 0;
$company_id = ($company_id > 0) ? $company_id : 0;
$contact_id = ($contact_id > 0) ? $contact_id : 0;

//make our database connection
$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
//$con->debug = 1;

if (!$scheduled_at) {
    $scheduled_at = date('Y-m-d H:i:s');
}

if ($followup) {
    if ($default_followup_time) {
        $scheduled_at = date('Y-m-d', strtotime($default_followup_time) ) ;
    } else {
        $scheduled_at = date('Y-m-d', strtotime('+1 week') );
    }
}

if (!$ends_at) {
    $ends_at = $scheduled_at;
}

// define our query
$sql = "insert into activities
        set
        activity_type_id  = $activity_type_id,
        user_id = $user_id,
        company_id = $company_id,
        contact_id = $contact_id,
        on_what_id = $on_what_id,
        entered_by = $session_user_id,
        on_what_table = ". $con->qstr($on_what_table, get_magic_quotes_gpc()) . ',
        activity_title = '. $con->qstr($activity_title, get_magic_quotes_gpc()) . ',
        activity_description = '. $con->qstr($activity_note, get_magic_quotes_gpc()) . ',
        entered_at = '. $con->DBTimeStamp(mktime()) .',
        scheduled_at = ' . $con->DBTimeStamp(date ('Y-m-d H:i:s', strtotime($scheduled_at))) . ',
        ends_at = ' . $con->DBTimeStamp(date ('Y-m-d H:i:s', strtotime($ends_at))) . ',
        activity_status = ' . $con->qstr($activity_status, get_magic_quotes_gpc());

//insert it already
$con->execute($sql);

$activity_id = $con->insert_id();
add_audit_item($con, $session_user_id, 'created', 'activities', $activity_id);

//close the connection
$con->close();

//if this is a mailto link, try to open the user's default mail application
if ($email) {
    header ("Location: mailto:$email");
}

//now send them back where they came from
if ($activities_default_behavior == "Fast") {
    header("Location: " . $http_site_root . $return_url);
} else {  //If Long activities are the default, send them to edit the activity
    header("Location: " . $http_site_root . "/activities/one.php?return_url=" . $return_url . "&activity_id=" . $activity_id);
}

/**
 *$Log: new-2.php,v $
 *Revision 1.10  2004/05/07 16:15:48  braverock
 *- fixed multiple bugs with date-time formatting in activities
 *- correctly use dbtimestamp() date() and strtotime() fns
 *- add support for $default_followup_time config var
 *  - fixes SF bug  949779 reported by miguel Gonçalves (mig77)
 *
 *Revision 1.9  2004/05/04 15:13:21  maulani
 *- Database connection object was called before being created.  Reorganized
 *  code to prevent fatal crash.
 *
 *Revision 1.8  2004/04/27 15:17:08  gpowers
 *- added support for activity times
 *- added support passing ends_at (defaults to scheduled_at)
 *- added audit item
 *
 *Revision 1.7  2004/04/26 01:54:45  braverock
 *- add ability to schedule a followup activity based on the current activity
 *
 *Revision 1.6  2004/02/10 16:19:34  maulani
 *- Make default activity creation behavior configurable
 *
 *Revision 1.5  2004/02/06 22:47:36  maulani
 *- Use ends_at to determine if activity is overdue
 *
 *Revision 1.4  2004/01/26 19:26:32  braverock
 *- modified to use getGlobalVar fn
 *- modified to allow for mailto $email link
 *- added phpdoc
 *
 */
?>