<?php
/**
 * Save the updated activity information to the database
 *
 * @todo: potential security risk in pulling some of these variables from the submit
 *        should eventually do a select to get the variables if we are going
 *        to post a followup
 *
 * $Id: edit-2.php,v 1.16 2004/06/12 15:43:51 braverock Exp $
 */

//include required files
require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();

$return_url = $_POST['return_url'];
$activity_id = $_POST['activity_id'];
$activity_type_id = $_POST['activity_type_id'];
$contact_id = $_POST['contact_id'];
$activity_title = $_POST['activity_title'];
$activity_description = $_POST['activity_description'];
$scheduled_at = $_POST['scheduled_at'];
$ends_at = $_POST['ends_at'];
$activity_status = $_POST['activity_status'];
$current_activity_status = $_POST['current_activity_status'];
$user_id    = $_POST['user_id'];
$followup   = $_POST['followup'];
$on_what_table = $_POST['on_what_table'];
$on_what_id = $_POST['on_what_id'];
$company_id = $_POST['company_id'];
$email_to = $_POST['email_to'];
$table_name = $_POST['table_name'];
$table_status_id = $_POST['table_status_id'];
$probability = $_POST['probability'];

//mark this activity as completed if follow up is to be scheduled
if ($followup) { $activity_status = 'c'; }

//set scheduled_at to today if it is empty
if (!$scheduled_at) {
    $scheduled_at = date('Y-m-d');
}

// set ends_at to scheduled_at if it is empty
if (!$ends_at) {
    $ends_at = $scheduled_at;
}

// set the correct activity status flag
$activity_status = ($activity_status == 'on') ? 'c' : 'o';
//mark this activity as completed if follow up is to be scheduled
if ($followup) { $activity_status = 'c'; }



$contact_id = ($contact_id > 0) ? $contact_id : 'NULL';

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

// if it's closed but wasn't before, update the closed_at timestamp
$completed_at = ($activity_status == 'c') && ($current_activity_status != 'c') ? time() : 'NULL';

$sql = "SELECT * FROM activities WHERE activity_id = " . $activity_id;
$rst = $con->execute($sql);
//$con->debug = 1;

//initialize array for updating db
$rec = array();
$rec['activity_type_id'] = $activity_type_id;
$rec['contact_id'] = $contact_id;
$rec['activity_title'] = $activity_title;
$rec['activity_description'] = $activity_description;
$rec['user_id'] = $user_id;
$rec['scheduled_at'] = strtotime($scheduled_at);
$rec['ends_at'] = strtotime($ends_at);
$rec['completed_at'] = $completed_at;
$rec['activity_status'] = $activity_status;

$upd = $con->GetUpdateSQL($rst, $rec, $forceUpdate=false, $magicq=get_magic_quotes_gpc());
$rst = $con->execute($upd);

if($on_what_table == 'opportunities' and strlen ($probability)) {
    $sql = "update opportunities set
        probability = $probability
        where opportunity_id = $on_what_id";

    $prob_rst= $con->execute($sql);
    if (!$prob_rst) { db_error_handler ($con, $sql); }
}

add_audit_item($con, $session_user_id, 'updated', 'activities', $activity_id, 1);


//get sort_order field
$sql = "select sort_order from " . strtolower($table_name) . "_statuses where " . strtolower($table_name) ."_status_id=$table_status_id";
$rst = $con->SelectLimit($sql, 1, 0);
$rst = $con->execute($sql);
if ($rst) {
    $sort_order = $rst->fields['sort_order'];
    $rst->close();
}

//get current username
$sql = "select username from users where user_id = $user_id";
$rst = $con->SelectLimit($sql, 1, 0);
if ($rst) { $username = $rst->fields['username']; }
$rst->close();

//get current company name and phone
$sql = "select company_name, phone from companies where company_id = $company_id";
$rst = $con->SelectLimit($sql, 1, 0);
if ($rst) {
    $company_name = $rst->fields['company_name'];
    $company_phone = $rst->fields['phone'];
    $rst->close();
}

$sql = "select activity_type_pretty_name from activity_types where activity_type_id = $activity_type_id";
$rst = $con->SelectLimit($sql, 1, 0);
if ($rst) {
    $activity_type = $rst->fields['activity_type_pretty_name'];
    $rst->close();
}


/* this saves case/opportunity status changes to the database when they are changed in one.php */
$table_name = strtolower($table_name);
if ($table_name != "attached to") {
    $sql = "select * from $on_what_table where ".$table_name."_id=$on_what_id";
    $rst = $con->execute($sql);

    $old_sort_order = $rst->fields['sort_order'];
    $old_status = $rst->fields[$table_name . '_status_id'];

    //check if there are open activities left
    $sql = "select * from activities
             where on_what_status='$old_status'
             and on_what_table='$on_what_table'
             and on_what_id=$on_what_id
             and contact_id=$contact_id
             and company_id=$company_id
             and activity_status='o'
             and activity_record_status='a'";

    $rst = $con->execute($sql);
    if ($rst) {
        $activity_return_id = $rst->fields['activity_id'];
    } else {
        db_error_handler ($con, $sql);
    }

    //check if there are open activities from this status
    // if no more activities are open, advance status
    $no_update = true;
    if ($rst->rowcount() == 0) {
        if ($old_status != $table_status_id) {
            $no_update = false;
        } else {
            $no_update = false;
            $sort_order++;
            $sql = "select * from " . $table_name . "_statuses
                where sort_order=$sort_order
                and " . $table_name . "_status_record_status='a'";
            $rst = $con->execute($sql);
            $table_status_id = $rst->fields[$table_name . '_status_id'];
        }
    }

    // check for status change
    if ($old_status != $table_status_id){
        //if there is only one field, the result set is empty (no old activities)
        //  otherwise prompt the user
        if ($no_update) {
            $return_url = "/activities/one.php?msg=no_change&activity_id=$activity_return_id";
        }

        //update if there are no open activities
        if (!$no_update) {
            $sql = "update " . $on_what_table . "
                set " . $table_name . "_status_id=$table_status_id
                where " . $table_name . "_id=$on_what_id";

            $con->execute($sql);
            $on_what_table_template = $table_name .  "_statuses";
            $on_what_id_template = $table_status_id;

            //include the workflow-activities.php page to actually make the update
            require_once("workflow-activities.php");
        }
    } //end if to check for status change

}


//get data for generated email
$sql = "SELECT " . $con->Concat("first_names", "' '", "last_name") . " AS contact_name, work_phone FROM contacts WHERE contact_id = $contact_id";
$rst = $con->SelectLimit($sql, 1, 0);
if ($rst) {
    $contact_name = $rst->fields['contact_name'];
    $contact_phone = $rst->fields['work_phone'];
    $rst->close();
}

if ($activity_status == 'o') {
    $activity_status_long = "Open";
}

if ($activity_status == 'c') {
    $activity_status_long = "Closed";
}

$email_message="
This is an UPDATED $app_title activity:

$http_site_root/activities/one.php?return_url=/companies/one.php?company_id=$company_id&activity_id=$activity_id

Title: $activity_title
Description: $activity_description

Contact: $contact_name
Contact Phone: $contact_phone

Company: $company_name
Company Phone: $company_phone

User: $username
Type: $activity_type
Status: $activity_status_long
Scheduled At: $scheduled_at
Ends At: $ends_at

";

$con->close();

if ($email_to) {
    mail($email_to, "$app_title: $activity_title", $email_message);
}

if ($followup) {
    header ('Location: '.$http_site_root."/activities/new-2.php?user_id=$session_user_id&activity_type_id=$activity_type_id&on_what_id=$on_what_id&contact_id=$contact_id&on_what_table=$on_what_table&company_id=$company_id&user_id=$user_id&activity_title=".htmlspecialchars( 'Follow-up ' . $activity_title ) .  "&company_id=$company_id&activity_status=o&return_url=$return_url&followup=true" );
} else {
    header("Location: " . $http_site_root . $return_url);
}

/**
 * $Log: edit-2.php,v $
 * Revision 1.16  2004/06/12 15:43:51  braverock
 * - changed all timestamps to work properly with ADODB getinsertsql/getupdatesql
 *
 * Revision 1.15  2004/06/11 21:18:39  introspectshun
 * - Now use ADODB GetInsertSQL and GetUpdateSQL functions.
 *
 * Revision 1.14  2004/06/11 16:18:15  braverock
 * - added more checking around activity_edit_id
 *
 * Revision 1.13  2004/06/10 20:30:07  braverock
 * - added ability to edit probability on linked opportunity
 *   - code contributed by Neil Roberts
 *
 * Revision 1.12  2004/06/03 16:11:00  braverock
 * - add functionality to support workflow and activity templates
 *   - functionality contributed by Brad Marshall
 *
 * Revision 1.12  2004/05/21 15:27:02  bmarshall
 * - added functionality to save opportunity/case status changes
 *
 * Revision 1.11  2004/05/10 13:04:15  maulani
 * - add session_check
 * - add level to audit_trail
 *
 * Revision 1.10  2004/05/07 16:17:10  braverock
 * - remove trailing whitespace added by Glenn's editor
 *
 * Revision 1.9  2004/05/07 16:15:48  braverock
 * - fixed multiple bugs with date-time formatting in activities
 * - correctly use dbtimestamp() date() and strtotime() fns
 * - add support for $default_followup_time config var
 *   - fixes SF bug  949779 reported by miguel Gonçalves (mig77)
 *
 * Revision 1.8  2004/04/27 16:29:34  gpowers
 * added support for activity emails.
 *
 * Revision 1.7  2004/04/27 15:18:04  gpowers
 * added support for activity times
 * added audit item
 *
 * Revision 1.6  2004/04/26 01:54:45  braverock
 * add ability to schedule a followup activity based on the current activity
 *
 * Revision 1.5  2004/03/17 21:36:48  braverock
 * -fixed strlen bug
 *
 * Revision 1.4  2004/03/15 14:51:27  braverock
 * - fix ends-at display bug
 * - make sure both scheduled_at and ends_at have legal values
 * - add phpdoc
 *
 */
?>