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
 * $Id: new-2.php,v 1.33 2005/01/09 14:54:02 braverock Exp $
 */

//where do we include from
require_once('../include-locations.inc');

//get required common files
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

//check to make sure we are logged on
$session_user_id = session_check();

// declare passed in variables
$arr_vars = array ( // local var name       // session variable name
                   'return_url'       => array ( 'return_url' , arr_vars_REQUEST_SESSION ),
                   'activity_type_id' => array ( 'activity_type_id' , arr_vars_REQUEST_SESSION ),
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
                   'opportunity_status_id'  => array ( 'opportunity_status_id' , arr_vars_REQUEST_SESSION ),
                   );

// get all passed in variables
arr_vars_get_request ( $arr_vars, true );

//mark completed if it is an email
if ($email) { $activity_status = 'c'; };

if (!$scheduled_at) {
    $scheduled_at = date('Y-m-d H:i:s');
}

if ($followup) {
    //set the time for the new activity if it isn't already set
    if (isset($default_followup_time) && $default_followup_time) {
        $scheduled_at = date('Y-m-d', strtotime($default_followup_time) ) ;
    } else {
        $scheduled_at = date('Y-m-d', strtotime('+1 week') );
    }
}

if (!$ends_at) {
    $ends_at = $scheduled_at;
}

// make sure ends_at is later than scheduled at
if ($scheduled_at > $ends_at) {
   //set $ends_at to = $scheduled_at
   $ends_at = $scheduled_at;
}

//make our database connection
$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
//$con->debug = 1;

//check to see if we need to associate with an opportunity or case
if ($associate_activities == true ) {
    if (($on_what_table=='contacts') or ($on_what_table=='')) {
        $opp_arr = array();
        $case_arr = array();
        $arr_count = 0;

        //get active case ids for this company
        $case_sql = "SELECT c.case_id
                     FROM cases c
                     LEFT JOIN case_statuses cs ON (c.case_status_id = cs.case_status_id)
                     WHERE c.company_id = $company_id
                       AND cs.status_open_indicator = 'o'
                       AND c.case_record_status='a'";
        $case_rst = $con->execute($case_sql);
        if ($case_rst) {
            if ($case_rst->RecordCount()>=1){
                while (!$case_rst->EOF) {
                    $case_arr[]=$case_rst->fields['case_id'];
                    $case_rst->movenext();
                    $arr_count++;
                }
            }
            $case_rst->close();
        } else {
            db_error_handler ($con,$case_sql);
        }

        //get active opportunity ids for this company
        $opp_sql = "SELECT o.opportunity_id
                    FROM opportunities o
                    LEFT JOIN opportunity_statuses os ON (o.opportunity_status_id = os.opportunity_status_id)
                    WHERE o.company_id = $company_id
                      AND os.status_open_indicator = 'o'
                      AND o.opportunity_record_status='a'";
        $opp_rst = $con->execute($opp_sql);
        if ($opp_rst) {
            if ($opp_rst->RecordCount()>=1){
                while (!$opp_rst->EOF) {
                    $opp_arr[]=$opp_rst->fields['opportunity_id'];
                    $opp_rst->movenext();
                    $arr_count++;
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
                $on_what_id    = $case_arr[0];
            }
            if (count($opp_arr)){
                //echo '<pre>'.print_r($opp_arr).'</pre>';
                $on_what_table = 'opportunities';
                $on_what_id    = $opp_arr[0];
            }
        }
    } //end empty on_what_table check
} // end associate code

//save to database

$rec['user_id']          = (strlen($user_id) > 0) ? $user_id : $session_user_id;
$rec['company_id']       = ($company_id > 0) ? $company_id : 0;
$rec['contact_id']       = ($contact_id > 0) ? $contact_id : 0;
$rec['entered_at']       = time();
$rec['entered_by']       = $session_user_id;
$rec['ends_at']          = strtotime($ends_at);
$rec['opportunity_status_id'] = $opportunity_status_id;

if(empty($opportunity_status_id)) {
    $rec['activity_type_id'] = ($activity_type_id > 0) ? $activity_type_id : 0;
    $rec['activity_status']  = (strlen($activity_status) > 0) ? $activity_status : "o";
    $rec['on_what_status']   = ($on_what_status > 0) ? $on_what_status : 0;
    $rec['activity_title']   = (strlen($activity_title) > 0) ? $activity_title : _("[none]");
    $rec['activity_description'] = (strlen($activity_description) > 0) ? $activity_description : "";
    $rec['on_what_table']    = (strlen($on_what_table) > 0) ? $on_what_table : '';
    $rec['on_what_id']       = ($on_what_id > 0) ? $on_what_id : 0;
    $rec['scheduled_at']     = strtotime($scheduled_at);
}
else {
    $rec['opportunity_status']  = "o";
    $rec['opportunity_title'] = (strlen($activity_title) > 0) ? $activity_title : _("[none]");
    $rec['last_modified_at'] = time();
    $rec['last_modified_by'] = $session_user_id;
}

if(empty($opportunity_status_id)) {
    $tbl = 'activities';
    $ins = $con->GetInsertSQL($tbl, $rec, get_magic_quotes_gpc());
    $con->execute($ins);

    $activity_id = $con->insert_id();
    add_audit_item($con, $session_user_id, 'created', 'activities', $activity_id, 1);
}
else {
    $tbl = 'opportunities';
    $ins = $con->GetInsertSQL($tbl, $rec, get_magic_quotes_gpc());
    $con->execute($ins);

    $opportunity_id = $con->insert_id();
    add_audit_item($con, $session_user_id, 'created', 'opportunities', $opportunity_id, 1);

    $on_what_table_template = "opportunity_statuses";
    $on_what_id_template = $opportunity_status_id;
    $on_what_table = "opportunities";
    $on_what_id = $opportunity_id;
    require $xrms_file_root . "/activities/workflow-activities.php";
}

//if this is a mailto link, try to open the user's default mail application
if ($email) {
    header ("Location: mailto:$email");
}

$activities_default_behavior = get_system_parameter($con, 'Activities Default Behavior');

//close the connection
$con->close();

//now send them back where they came from
if ($activity_status == 'c') {
    header("Location: " . $http_site_root . $return_url);
} elseif ($activities_default_behavior == "Fast") {
    header("Location: " . $http_site_root . $return_url);
} elseif ($activities_default_behavior == "Long") {  //If Long activities are the default, send them to edit the activity
    header("Location: " . $http_site_root . "/activities/one.php?return_url=" . $return_url . "&activity_id=" . $activity_id);
} elseif(!empty($opportunity_status_id)) {
    header("Location: " . $http_site_root . "/opportunities/one.php?return_url=" . $return_url . "&opportunity_id=" . $opportunity_id);
} else {  //If Long activities are the default, send them to edit the activity
    header("Location: " . $http_site_root . "/activities/one.php?return_url=" . $return_url . "&activity_id=" . $activity_id);
}

/**
 *$Log: new-2.php,v $
 *Revision 1.33  2005/01/09 14:54:02  braverock
 *- localize [none]
 *
 *Revision 1.32  2005/01/09 13:58:51  braverock
 *- use of single = instead of == in comparison for $associate_activities
 *  Solves SF bug 1098200 submitted by Fu22Ba55
 *
 *Revision 1.31  2005/01/09 00:01:39  braverock
 *- fixed Fast/Long processing
 *- re-enable opportunity status code accidentally commented out
 *
 *Revision 1.30  2005/01/07 03:40:54  braverock
 *- add status pop-up link
 *
 *Revision 1.29  2004/09/21 18:19:30  introspectshun
 *- Corrected mispelling of opportunities directory
 *
 *Revision 1.28  2004/09/17 20:04:29  neildogg
 *- Added optional auto creation of opportunity
 * - from contact screen along with auto
 * - launching activities on opportunity status
 *
 *Revision 1.27  2004/08/05 15:12:22  braverock
 *- remove obsolete comment
 *
 *Revision 1.26  2004/08/05 14:57:14  braverock
 *- update to use new arr_vars_get_request fn
 *  - resolves several bugs reported on SF
 *
 *Revision 1.25  2004/08/02 08:31:30  maulani
 *- Create Activities Default Behavior system parameter.  Replaces vars.php
 *  variable $activities_default_behavior
 *
 *Revision 1.24  2004/07/22 17:14:35  braverock
 *- fixed problem with arr_vars subsystem
 *
 *Revision 1.23  2004/07/20 14:07:47  cpsource
 *- Remove unused calls to getGlobalVar's
 *
 *Revision 1.22  2004/07/20 14:02:39  cpsource
 *- Beagle bites sqirrel - got rid of getGlobalVars and
 *    upgraded to arr_vars sub-system.
 *  Fixed bug whereby companies/one.php couldn't create
 *    activities.
 *
 *Revision 1.21  2004/07/14 22:58:17  introspectshun
 *- Altered LEFT JOINs to use standard ON syntax rather than USING
 *
 *Revision 1.20  2004/07/14 18:34:15  braverock
 *- fixed logic error that could result in assignment rather than comparison
 *
 *Revision 1.19  2004/07/13 20:52:33  braverock
 *- removed debug echo
 *
 *Revision 1.18  2004/07/13 19:52:37  braverock
 *- add ability to process activity association to open Opportunity/Case
 *  for unassociated activities based on $associate_activities global
 *
 *Revision 1.17  2004/07/10 13:51:18  braverock
 *- improved date handling error checking
 *
 *Revision 1.16  2004/07/07 21:27:37  introspectshun
 *- Now passes a table name instead of a recordset into GetInsertSQL
 *
 *Revision 1.15  2004/06/29 14:28:31  gpowers
 *- changed activity_description default to null
 *  - to save activity enter time
 *
 *Revision 1.14  2004/06/12 17:10:23  gpowers
 *- removed DBTimeStamp() calls for compatibility with GetInsertSQL() and
 *  GetUpdateSQL()
 *
 *Revision 1.13  2004/06/11 21:18:39  introspectshun
 *- Now use ADODB GetInsertSQL and GetUpdateSQL functions.
 *
 *Revision 1.12  2004/06/04 13:23:45  braverock
 *- fix date bug that would create invalid datestamp
 *- mark new emails from mailto link as completed
 *
 *Revision 1.11  2004/05/10 13:07:20  maulani
 *- Add level to audit trail
 *- Clean up audit trail text
 *
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