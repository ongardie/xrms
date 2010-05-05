<?php
/**
 * activites/new&followup.php - This script inserts a new activity into the
 * database and, if the correct parameters have been set, also inserts a
 * followup activity.  Adapted from activities/new-2.php.
 *
 * This may be called from many places in the XRMS interface, because the activities
 * may be linked from contacts, companies, cases, opportunities, or mailto links.
 *
 * This page needs to first grab submitted parameters for the activity, or set default
 * values if no value is submitted.
 *
 * Recently changed to use the getGlobalVar utility funtion so that $_GET parameters
 * could be used with mailto links.
 */

//where do we include from
require_once('../include-locations.inc');

//get required common files
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'utils-activities.php');
require_once($include_directory . 'utils-opportunities.php');
require_once($include_directory . 'utils-preferences.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');


//check to make sure we are logged on
$session_user_id = session_check('','Create');

// declare passed in variables
$arr_vars = array ( // local var name       // session variable name
                   'return_url'       => array ( 'return_url' , arr_vars_REQUEST_SESSION ),
                   'activity_type_id' => array ( 'activity_type_id' , arr_vars_REQUEST_SESSION ),
                   'activity_priority_id' => array ( 'activity_priority_id' , arr_vars_REQUEST_SESSION ),
                   'activity_resolution_type_id' => array ( 'activity_resolution_type_id' , arr_vars_REQUEST_SESSION ),
                   'resolution_description' => array ( 'resolution_description' , arr_vars_REQUEST_SESSION ),
                   'on_what_table'    => array ( 'on_what_table' , arr_vars_REQUEST_SESSION ),
                   'on_what_id'       => array ( 'on_what_id' , arr_vars_REQUEST_SESSION ),
                   'on_what_status'   => array ( 'on_what_status' , arr_vars_REQUEST_SESSION ),
                   'activity_title'   => array ( 'activity_title' , arr_vars_REQUEST_SESSION ),
                   'activity_description' => array ( 'activity_description' , arr_vars_REQUEST_SESSION ),
                   'activity_status'  => array ( 'activity_status' , arr_vars_REQUEST_SESSION ),
                   'scheduled_at'     => array ( 'scheduled_at' , arr_vars_REQUEST_SESSION ),
                   'ends_at'          => array ( 'ends_at' , arr_vars_REQUEST_SESSION ),
                   'company_id'       => array ( 'company_id' , arr_vars_REQUEST_SESSION ),
                   'contact_id'       => array ( 'contact_id' , arr_vars_REQUEST_SESSION ),
                   'user_id'          => array ( 'user_id' , arr_vars_REQUEST_SESSION ),
                   'email'            => array ( 'email' , arr_vars_REQUEST_SESSION ),
                   'followup'         => array ( 'followup' , arr_vars_REQUEST_SESSION ),
                   'new_and_followup'              => array ( 'new_and_followup' , arr_vars_REQUEST_SESSION ),
                   'followup_user_id'          => array ( 'followup_user_id' , arr_vars_REQUEST_SESSION ),
                   'followup_activity_type_id' => array ( 'followup_activity_type_id' , arr_vars_REQUEST_SESSION ),
                   'followup_contact_id'       => array ( 'followup_contact_id' , arr_vars_REQUEST_SESSION ),
                   'followup_scheduled_at'     => array ( 'followup_scheduled_at' , arr_vars_REQUEST_SESSION ),
                   'followup_ends_at'          => array ( 'followup_ends_at' , arr_vars_REQUEST_SESSION ),
                   'followup_transfer_notes'   => array ( 'followup_transfer_notes' , arr_vars_REQUEST_SESSION ),
                   'followup_from_id' => array ( 'followup_from_id' , arr_vars_REQUEST_SESSION ),
                   'thread_id'        => array ( 'thread_id' , arr_vars_REQUEST_SESSION ),
                   'address_id'  => array ( 'address_id' , arr_vars_REQUEST_SESSION ),
                   );

// Get all passed in variables
arr_vars_get_request ( $arr_vars, true );

// For testing only!  View passed-in values
//foreach ($arr_vars as $arr_var) echo "$arr_var[0] = ". ${$arr_var[0]} ."<br />";

// Mark completed if it is an email
if ($email) { $activity_status = 'c'; };

// @TODO: Move the default_activity_duration setting to the system preferences
// Default activty duration, in seconds
$default_activity_duration = 900;

// Convert all times to unix timestamps, perform sanity check on the values
// or set them, if not passed-in.  Remove all guesswork!
$scheduled_at = strtotime($scheduled_at);
$scheduled_at = $scheduled_at ? $scheduled_at : strtotime("now");

$ends_at = strtotime($ends_at);
// No activity lasts zero time!
$ends_at = ($ends_at && ($ends_at > $scheduled_at)) ? $ends_at : $scheduled_at + $default_activity_duration;

if ($new_and_followup) {

    // @TODO: Move the $default_followup_time setting to the system preferences.
    // It is validated in several places in XRMS yet it is defined nowhere.
    // It is a good idea, so we leave it and assume it is an offset in seconds.

    // Try to figure out the followup time.  We'll need that if a followup time is not set
    $followup_time = (isset($default_followup_time) && $default_followup_time) ?  $scheduled_at + $default_followup_time : $scheduled_at + 604800;

    $followup_scheduled_at = strtotime($followup_scheduled_at);
    $followup_scheduled_at = ($followup_scheduled_at && ($followup_scheduled_at > $scheduled_at)) ? $followup_scheduled_at : $followup_time;
    $followup_ends_at = strtotime($followup_ends_at);
    $followup_ends_at = ($followup_ends_at && ($followup_ends_at > $followup_scheduled_at)) ? $followup_ends_at : $followup_scheduled_at + $default_activity_duration;
}

//make our database connection
$con = get_xrms_dbconnection();
//$con->debug = 1;

// Get the format for date/time
$datetime_format = set_datetime_format($con, $session_user_id);

// Convert all dates back to the appropriate format.  Going back and forth to
// unix timestamps and the Y-m-d H:i:s is way too clumsy
// but that is how they are stored in the database...
$scheduled_at = date('Y-m-d H:i:s', $scheduled_at);
$ends_at = date('Y-m-d H:i:s', $ends_at);
if ($new_and_followup) {
    $followup_scheduled_at = date('Y-m-d H:i:s', $followup_scheduled_at);
    $followup_ends_at = date('Y-m-d H:i:s', $followup_ends_at);
}

//check to see if we need to associate with an opportunity or case
if ($associate_activities == true ) {
    if (($on_what_table=='contacts') or ($on_what_table=='') or ($on_what_table=='companies')) {
        $opp_arr = array();
        $case_arr = array();
        $arr_count = 0;

        //get active case ids for this company
        $case_sql = "SELECT c.case_id, c.division_id
                     FROM cases c
                     LEFT JOIN case_statuses cs ON (c.case_status_id = cs.case_status_id)
                     WHERE c.company_id = $company_id
                       AND cs.status_open_indicator = 'o'
                       AND c.case_record_status='a'";
        $case_rst = $con->execute($case_sql);
        if ($case_rst) {
            if ($case_rst->RecordCount()>=1){
                while (!$case_rst->EOF) {
                    $case_arr[]['case_id']=$case_rst->fields['case_id'];
                    $case_arr[]['division_id']=$case_rst->fields['division_id'];
                    $case_rst->movenext();
                    $arr_count++;
                    // There is no need to go through all the records
                    // since we are only interested in the data if there is only one
                    if ($arr_count > 1) break;
                }
            }
            $case_rst->close();
        } else {
            db_error_handler ($con,$case_sql);
        }

        //get active opportunity ids for this company
        $opp_sql = "SELECT o.opportunity_id, o.division_id
                    FROM opportunities o
                    LEFT JOIN opportunity_statuses os ON (o.opportunity_status_id = os.opportunity_status_id)
                    WHERE o.company_id = $company_id
                      AND os.status_open_indicator = 'o'
                      AND o.opportunity_record_status='a'";
        $opp_rst = $con->execute($opp_sql);
        if ($opp_rst) {
            if ($opp_rst->RecordCount()>=1){
                while (!$opp_rst->EOF) {
                    $opp_arr[]['opportunity_id']=$opp_rst->fields['opportunity_id'];
                    $opp_arr[]['division_id']=$opp_rst->fields['division_id'];
                    $opp_rst->movenext();
                    $arr_count++;
                    // There is no need to go through all the records
                    // since we are only interested in the data if there is only one
                    if ($arr_count > 1) break;
                }
            }
            $opp_rst->close();
            //echo '<br><pre>Opp Arr:'.print_r($opp_arr).'</pre>';
        } else {
            db_error_handler ($con,$opp_sql);
        }

        //we can only guess at the association if there is only one item to associate to
        if ($arr_count==1) {
            if (count($case_arr)){
                //echo '<pre>'.print_r($case_arr).'</pre>';
                $on_what_table = 'cases';
                $on_what_id    = $case_arr[0]['case_id'];
                $division_id   = $case_arr[0]['division_id'];
            }
            if (count($opp_arr)){
                //echo '<pre>'.print_r($opp_arr).'</pre>';
                $on_what_table = 'opportunities';
                $on_what_id    = $opp_arr[0]['opportunity_id'];
                $division_id   = $opp_arr[0]['division_id'];
            }
        }
    } //end empty on_what_table check
} // end associate code


// If we have not been able to set the division_id from cases or opportunities,
// we should try to set it from the contact record
if (!$division_id) {
    // If no division has been assigned to this activity, assume the division id of the contact, if any
    $tmp_sql = "SELECT division_id FROM contacts WHERE contact_id = $contact_id LIMIT 1";
    $tmp_rst = $con->execute($tmp_sql);
    if ($tmp_rst) {
        $division_id = $tmp_rst->fields['division_id'];
    } else {
        db_error_handler ($con, $tmp_sql);
    }
}


//set up the data record
$rec['user_id']          = (strlen($user_id) > 0) ? $user_id : $session_user_id;
$rec['company_id']       = ($company_id > 0) ? $company_id : 0;
$rec['division_id']      = ($division_id > 0) ? $division_id : 0;
$rec['contact_id']       = ($contact_id > 0) ? $contact_id : 0;
$rec['activity_type_id'] = ($activity_type_id > 0) ? $activity_type_id : 0;
$rec['activity_priority_id'] = ($activity_priority_id > 0) ? $activity_priority_id : 0;
$rec['activity_resolution_type_id'] = ($activity_resolution_type_id > 0) ? $activity_resolution_type_id : 0;
$rec['resolution_description'] = ($resolution_description > 0) ? $resolution_description : 0;
$rec['activity_status']  = (strlen($activity_status) > 0) ? $activity_status : "o";
$rec['on_what_status']   = ($on_what_status > 0) ? $on_what_status : 0;
$rec['activity_title']   = (strlen($activity_title) > 0) ? $activity_title : _("[none]");
$rec['activity_description'] = (strlen($activity_description) > 0) ? $activity_description : "";
$rec['on_what_table']    = (strlen($on_what_table) > 0) ? $on_what_table : '';
$rec['on_what_id']       = ($on_what_id > 0) ? $on_what_id : 0;
$rec['scheduled_at']     = $scheduled_at; //activity scheduled *start* time
$rec['ends_at']          = $ends_at;      //activity anticipated *end* time

// Set the thread_id, if possible
if($thread_id)
    $rec['thread_id']         = $thread_id;
elseif ($activity_id)
    $rec['thread_id']         = $activity_id;

if($followup_from_id) $rec['followup_from_id'] = $followup_from_id;
if($address_id) $rec['address_id']         = $address_id;

// Check to see if we need to convert the activity_description to HTML
if (get_user_preference($con, $user_id, "html_activity_notes") == 'y') {
    $rec['activity_description'] = nl2br($rec['activity_description']);
}

$magic_quotes=get_magic_quotes_gpc();
//add activity using API
$activity_id = add_activity($con, $rec, false, $magic_quotes);
if (!$activity_id) {
    $msg=urlencode(_("Failed to add activity"));
    header("Location: " . $http_site_root . $return_url."&msg=$msg");
    exit();
}

// If we could not set the thread_id before, we can certainly do it now
if (!$rec['thread_id']) {
    $tmp = $con->qstr($activity_id);
    $sql = "UPDATE activities SET thread_id = $tmp WHERE activity_id = $tmp";
    $rst = $con->execute($sql);
    if (!$rst) {
        db_error_handler($con, $ins);
        exit();
    }
}

// Insert the followup activity
if ($new_and_followup) {
    $followup_rec = $rec;
    if ($followup_user_id) $followup_rec['user_id'] = $followup_user_id;
    if ($followup_activity_type_id) $followup_rec['activity_type_id'] = $followup_activity_type_id;
    if ($followup_contact_id) $followup_rec['contact_id'] = $followup_contact_id;
    $followup_rec['scheduled_at'] = $followup_scheduled_at;
    $followup_rec['ends_at'] = $followup_ends_at;
    $followup_rec['activity_status'] = 'o';
    $followup_rec['activity_title'] = _('Follow-up') .' '. $activity_title;
    if (!$followup_transfer_notes) $followup_rec['activity_description'] = NULL;
    $followup_rec['thread_id'] = $activity_id;
    $followup_rec['followup_from_id'] = $activity_id;

    $followup_activity_id = add_activity($con, $followup_rec, false, $magic_quotes);
    if (!$followup_activity_id) {
        $msg=urlencode(_("Failed to add followup activity"));
        header("Location: " . $http_site_root . $return_url."&msg=$msg");
        exit();
    }
}

//if this is a mailto link, try to open the user's default mail application
if ($email) {
    header ("Location: mailto:$email");
}

$activities_default_behavior = get_system_parameter($con, 'Activities Default Behavior');

//close the connection
$con->close();

//set return location
if ($activity_status == 'c') { //now send them back where they came from
    header("Location: " . $http_site_root . $return_url);
} elseif ($activities_default_behavior == "Fast") {
    //If Fast activities are the default, send them back where they came from
    header("Location: " . $http_site_root . $return_url);
} else {
    //If Long activities are the default, send them to edit the activity
    //like elseif ($activities_default_behavior == "Long")
    header("Location: " . $http_site_root . "/activities/one.php?return_url=" . $return_url . "&activity_id=" . $activity_id);
}

?>