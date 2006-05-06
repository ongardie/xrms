<?php
/**
 * Save the updated activity information to the database
 *
 * @todo  potential security risk in pulling some of these variables from the submit
 *        should eventually do a select to get the variables if we are going
 *        to post a followup
 *
 * $Id: edit-2.php,v 1.80 2006/05/06 09:28:14 vanmer Exp $
 */

//include required files
require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'utils-activities.php');
require_once($include_directory . 'utils-workflow.php');
require_once($include_directory . 'utils-cases.php');
require_once($include_directory . 'utils-opportunities.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');


// POST'ed in data
$arr_vars = array (
                   // posted data
                   'return_url' => arr_vars_POST ,
                   'activity_id' => arr_vars_POST ,
                   'activity_type_id' => arr_vars_POST ,
                   'activity_priority_id' => arr_vars_POST ,
                   'activity_resolution_type_id' => arr_vars_POST ,
                   'resolution_description' => arr_vars_POST ,
                   'contact_id' => arr_vars_POST ,
                   'activity_title' => arr_vars_POST ,
                   'activity_description' => arr_vars_POST ,
                   'scheduled_at' => arr_vars_POST ,
                   'ends_at' => arr_vars_POST ,
                   'activity_status' => arr_vars_POST ,
                   'current_activity_status' => arr_vars_POST ,
                   'user_id' => arr_vars_POST ,
                   'on_what_table' => arr_vars_POST ,
                   'on_what_id' => arr_vars_POST ,
                   'company_id' => arr_vars_POST ,
                   'email_to' => arr_vars_POST ,
                   'table_name' => arr_vars_POST ,
                   'table_status_id' => arr_vars_POST ,
                   'thread_id' => arr_vars_POST ,
                   'address_id' => arr_vars_POST ,
                   'followup_from_id' => arr_vars_POST ,

                   // optionally posted data
                   'opportunity_description' => arr_vars_POST_UNDEF ,
                   'probability' => arr_vars_POST_UNDEF ,
                   'followup' => arr_vars_POST_UNDEF ,
                   'recurrence' => arr_vars_POST_UNDEF ,
                   'change_attachment' => arr_vars_POST_UNDEF ,
                   'add_participant' => arr_vars_POST_UNDEF ,
                   'remove_participant' => arr_vars_POST_UNDEF ,
                   'mailmerge_participant' => arr_vars_POST_UNDEF ,
                   'saveandnext' => arr_vars_POST_UNDEF ,
                   'print_view' => arr_vars_POST_UNDEF ,
                   );

// get posted data
arr_vars_post_with_cmd ( $arr_vars );


$activity_on_what_id=$on_what_id;
$activity_on_what_table=$on_what_table;

$on_what_id=$activity_id;
$on_what_table='activities';

$session_user_id = session_check('','Update');

$on_what_id=$activity_on_what_id;
$on_what_table=$activity_on_what_table;

if (!$return_url) {
    $return_url='/activities/some.php';
}

$participant_return_url=urlencode("/activities/one.php?activity_id=$activity_id&return_url=$return_url");
if ($add_participant) {
    $return_url="/activities/new_activity_participant.php?activity_id=$activity_id&return_url=$participant_return_url";
} elseif ($remove_participant) {
    $return_url="/activities/new_activity_participant.php?activity_participant_action=deleteActivityParticipant&activity_participant_id=$remove_participant&return_url=$participant_return_url";
} elseif ($mailmerge_participant) {
    $return_url="/email/email.php?scope=contact_list&contact_list=$mailmerge_participant&return_url=$participant_return_url";
}

if ($change_attachment) {
    if ($change_attachment=='true') {
        $return_url="/activities/activity-reconnect.php?activity_id=$activity_id";
    } elseif ($change_attachment=='detach') {
        $return_msg=_("Successfully detached activity");
        $return_url="/activities/one.php?activity_id=$activity_id&msg=".urlencode($return_msg);
        $on_what_id=0;
        $on_what_table=NULL;
    }
}
// set the correct activity status flag
if ($activity_status == 'on') {
    $activity_status = 'c';
} else {
    //force a value for open activity, so the GetUpdateSQL will work
    $activity_status = 'o';
}

//mark this activity as completed if follow up is to be scheduled
if ($followup) {
    $activity_status = 'c';
    if(!$thread_id) {
        $thread_id = $activity_id;
    }
}

$scheduled_at = strtotime($scheduled_at);
//set scheduled_at to today if it is empty
if (!$scheduled_at) {
    $scheduled_at = strtotime(date('Y-m-d'));
}

// set ends_at to current time if it is empty
if (!$ends_at) {
    $ends_at = date('Y-m-d H:i:s');
}
$ends_at = strtotime($ends_at);

// make sure ends_at is later than scheduled at
if ($scheduled_at > $ends_at) {
   //set $ends_at to = $scheduled_at
   $ends_at = $scheduled_at;
}

$con = get_xrms_dbconnection();
//$con->debug = 1;

//get the existing activity record for use later in the script
$activity = get_activity($con, $activity_id);

$new_contact_id = ($contact_id > 0) ? $contact_id : 0;

if (!$contact_id OR $contact_id==0) {
    // set to the previous contact_id
    $contact_id=$activity['contact_id'];
}

// if it's closed but wasn't before, update the closed_at timestamp
$completed_at = ($activity_status == 'c') && ($current_activity_status != 'c') ? time() : 'NULL';
$completed_by= ($activity_status == 'c') && ($current_activity_status != 'c') ? $session_user_id : 'NULL';

//check to see if we need to associate with an opportunity or case
if ($associate_activities == true ) {
    if ((($on_what_table=='contacts') or ($on_what_table=='')) AND $company_id) {
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

$rec = array();
$rec['activity_type_id']     = $activity_type_id;
//use new contact ID here to update contact with newly set ID
$rec['contact_id']           = $new_contact_id;
$rec['activity_title']       = trim($activity_title);
$rec['activity_description'] = trim($activity_description);
if(empty($user_id)) {
    // If the user ID was empty
    // then we're going to assume that the current user has taken over the activity.
    $rec['user_id']          = $session_user_id;
}
else {
    $rec['user_id']          = $user_id;
}
$rec['last_modified_by']     = $session_user_id;
$rec['last_modified_at']     = time();
$rec['scheduled_at']         = $scheduled_at;
$rec['ends_at']              = $ends_at;
$rec['completed_at']         = $completed_at;
$rec['activity_status']      = $activity_status;
$rec['on_what_table']        = $on_what_table;
$rec['on_what_id']           = $on_what_id;
$rec['completed_by']         = $completed_by;
$rec['thread_id']            = $thread_id;
$rec['address_id']            = $address_id;
$rec['followup_from_id']     = $followup_from_id;
$rec['activity_priority_id'] = $activity_priority_id;
$rec['resolution_description'] = trim($resolution_description);
$rec['activity_resolution_type_id'] = $activity_resolution_type_id;

$magicq=get_magic_quotes_gpc();
$upd=update_activity($con, $rec, $activity_id, false, true, $magicq, $return_url); //, $table_status_id, $old_status
if ($upd['return_url']) $return_url=$upd['return_url'];

if ($upd['allow_status_change']) {
    getGlobalVar($old_status, 'old_status');
    if ($old_status AND $table_status_id) {
        if ($table_status_id != $old_status) {
            //make sure no workflow activities exist before changing status, even here
            $open_activities=get_open_workflow_activities_on_status_change($con, $on_what_table, $on_what_id, $table_status_id, $company_id, $contact_id);
            if ($open_activities){
                $first_activity=current($open_activities);
                $open_activity_id=$first_activity['activity_id'];
                $return_url="/activities/one.php?msg=no_change&activity_id=$open_activity_id&return_url=".urlencode($return_url);
            } else {
            //change case or opportunity status from activity edit, if workflow return allows it during update_activity
                $rec = array();
                $table_name=strtolower($table_name);
                $rec["{$table_name}_id"]=$on_what_id;
                $rec[$table_name . "_status_id"] = $table_status_id;
    //            print_r($rec);
                switch ($table_name) {
                    case 'case':
                        update_case($con, $rec, $on_what_id, false, $magicq);
                    break;
                    case 'opportunity':
                        update_opportunity($con, $rec, $on_what_id, false, $magicq);
                    break;
                }
            }
        }
    }
}

if($on_what_table == 'opportunities' and (strlen($opportunity_description)>0)) {
    //Update Opportunity Description
    $rec = array();
    $rec['opportunity_description'] = trim($opportunity_description);
    $rec['opportunity_id']=$on_what_id;
    update_opportunity($con, $rec, $on_what_id, false, $magicq);

}

if($on_what_table == 'opportunities' and (strlen($probability)>0)) {

    $rec = array();
    $rec['probability'] = $probability;
    $rec['opportunity_id']=$on_what_id;
    update_opportunity($con, $rec, $on_what_id, false, $magicq);

}

//get current username
if($user_id) {
    $sql = "select username from users where user_id = $user_id";
    $rst = $con->SelectLimit($sql, 1, 0);
    if ($rst) { $username = $rst->fields['username']; }
    $rst->close();
}

if ($company_id) {
    //get current company name and phone
    $sql = "select company_name, phone from companies where company_id = $company_id";
    $rst = $con->SelectLimit($sql, 1, 0);
    if ($rst) {
        $company_name = $rst->fields['company_name'];
        $company_phone = $rst->fields['phone'];
        $rst->close();
    }
}

$sql = "select activity_type_pretty_name from activity_types where activity_type_id = $activity_type_id";
$rst = $con->SelectLimit($sql, 1, 0);
if ($rst) {
    $activity_type = $rst->fields['activity_type_pretty_name'];
    $rst->close();
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
} elseif ($activity_status == 'c') {
    $activity_status_long = "Closed";
}
if ($company_id) {
    $email_return=urlencode('/companies/one.php?company_id='.$company_id);
} else {
    $email_return=urlencode('/activities/some.php');
}

$email_message="
This is an UPDATED $app_title activity:

$http_site_root/activities/one.php?return_url=$email_return&activity_id=$activity_id

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

    header ('Location: '.$http_site_root."/activities/new-2.php?user_id=$session_user_id&activity_type_id=$activity_type_id&on_what_id=$on_what_id&contact_id=$contact_id&on_what_table=$on_what_table&company_id=$company_id&user_id=$user_id&activity_title=".htmlspecialchars( _("Follow-up") . ' ' . $activity_title ) .  "&company_id=$company_id&activity_status=o&on_what_status=$old_status&return_url=$return_url&thread_id=$thread_id&followup_from_id=$activity_id&followup=true" );
} elseif($saveandnext) {
    header("Location: browse-next.php?activity_id=$activity_id");
} elseif($recurrence) {
    header("Location: recurrence_sidebar.php?activity_id=$activity_id");
} elseif($print_view) {
    header("Location: " . $http_site_root . $return_url . "&print_view=true");
} else {
    header("Location: " . $http_site_root . $return_url);
}

/**
 * $Log: edit-2.php,v $
 * Revision 1.80  2006/05/06 09:28:14  vanmer
 * - replaced direct sql in edit-2 to function calls where appropriate
 * - added functionality to change status on an entity when changed from activities/one.php, where appropriate
 * - pulled out all workflow-specific code into utils-workflow.php
 * - moved edit2 hook into update_activity API in utils-activities.php
 *
 * Revision 1.79  2006/04/29 01:45:17  vanmer
 * - changed to use workflow API to instantiate workflow activities
 * - changed to use cases/opportunities API to change status on cases/opportunities when last workflow activity is closed (API then instantiates new workflow
 * activities for new status)
 *
 * Revision 1.78  2006/04/05 00:53:10  vanmer
 * - pass magic quotes into activities API
 *
 * Revision 1.77  2006/01/19 22:20:44  daturaarutad
 * add handler for print_view button
 *
 * Revision 1.76  2006/01/02 21:23:18  vanmer
 * - changed to use centralized database connection function
 *
 * Revision 1.75  2005/11/04 16:27:52  braverock
 * - set ends_at time to current time if it is empty.
 * - rationalizes ends_at for activities like phone calls or long-overdue activities
 *
 * Revision 1.74  2005/10/08 21:08:49  vanmer
 * - changed participant subsystem to operate through activity edit-2, to allow changes on activity page to save
 * before moving to add/remove/mail participants on the activity
 *
 * Revision 1.73  2005/09/25 04:12:23  vanmer
 * - added ability to detach an activity from an on_what_table/on_what_id relationship using Detach button
 * - added case to check for $on_what_id before attempting to query for activity attachmetn
 * - added error handling on sql errors when querying for a name of the activity's attached entity
 *
 * Revision 1.72  2005/09/21 20:07:23  vanmer
 * - added address_id to allow location to be set or edited for an activity
 *
 * Revision 1.71  2005/07/15 22:49:58  vanmer
 * - changed to allow activities without a company_id to be saved
 *
 * Revision 1.70  2005/07/08 14:49:57  braverock
 * - fix to properly handle saving activities that are not part of workflow activity templates
 * - trim description fields
 *
 * Revision 1.69  2005/07/08 01:28:26  vanmer
 * - changed action to redirect to change attachment after saving all other settings
 *
 * Revision 1.68  2005/07/06 23:40:36  vanmer
 * - updated to check for activities at the same sort order, and add new activities if there are none left at htis
 * sort order
 * - only run above check when current activity has been completed
 *
 * Revision 1.67  2005/07/01 21:17:56  daturaarutad
 * set the thread_id to activity_id if this activity is the parent of another
 *
 * Revision 1.66  2005/07/01 20:54:52  daturaarutad
 * add thread_id and followup_from_id to record before saving
 *
 * Revision 1.65  2005/06/30 04:41:13  vanmer
 * - changed to allow contact_id to be switched within API
 * - changed to allow API to handle participants
 * - changed to allow API to handle the audit_item
 * - added needed fields for resolutions and priorities on activities
 *
 * Revision 1.64  2005/06/29 18:49:21  vanmer
 * - changed to use API for updating record instead of using getUpdateSQL directly
 * - changed param passed to hook function to pass the old record and the new array of records, instead of result of
 * update statement and new array of records
 *
 * Revision 1.63  2005/06/08 12:49:34  braverock
 * - rearrange when we get the old activity details for update
 * - fix bug where 'NULL' setting could cause problems with associate_activities functionality
 *
 * Revision 1.62  2005/06/05 17:18:59  braverock
 * - add standardized new/edit hooks
 *
 * Revision 1.61  2005/06/03 22:56:58  daturaarutad
 * added recurrence action handling
 *
 * Revision 1.60  2005/06/03 12:54:23  braverock
 * - remove 'Switch Opportunity' contact switching, as this is confusing to users
 *
 * Revision 1.59  2005/06/02 20:17:49  braverock
 * - change user to current user if user/owner field is blank on submit
 *
 * Revision 1.58  2005/05/25 05:37:00  vanmer
 * - added update of completed_by field when completing an activity
 *
 * Revision 1.57  2005/05/19 20:29:17  daturaarutad
 * added support for followup activities
 *
 * Revision 1.56  2005/05/19 13:20:43  maulani
 * - Remove trailing whitespace
 *
 * Revision 1.55  2005/05/18 21:47:27  vanmer
 * - added workflow tracking when changing status of entity as activity completes
 *
 * Revision 1.54  2005/04/15 07:50:00  vanmer
 * - added handling of change to contact, update activity_participants contact as well
 *
 * Revision 1.53  2005/02/10 14:29:29  maulani
 * - Add last modified timestamp and user fields to activities
 *
 * Revision 1.52  2005/01/13 18:40:09  vanmer
 * - ensure that if parameters are already set for return_url that msg is simply appended
 *
 * Revision 1.51  2005/01/12 21:00:02  vanmer
 * - altered to redirect to different URLS when next activity id is not available
 * - altered to use type comparison for different statuses
 *
 * Revision 1.50  2005/01/10 21:45:07  vanmer
 * - updates to allow workflow hooks to properly find the next possible status, and activities to populate
 *
 * Revision 1.49  2005/01/09 17:26:29  vanmer
 * - moved session_check after get/post variable are processed (for ACL)
 * - added default return_url if none specified from calling pages
 * - internationalized follow up string when creating a follow-up activity
 *
 * Revision 1.48  2005/01/09 13:58:51  braverock
 * - use of single = instead of == in comparison for $associate_activities
 *   Solves SF bug 1098200 submitted by Fu22Ba55
 *
 * Revision 1.47  2005/01/06 17:24:32  introspectshun
 * - Combined conditional for status label
 *
 * Revision 1.46  2004/12/20 21:47:59  neildogg
 * - Changed to handle a custom return_url (fixed)
 *
 * Revision 1.45  2004/12/20 21:46:26  neildogg
 * - Changed to handle a custom return_url
 *
 * Revision 1.44  2004/12/20 20:10:05  neildogg
 * Made sure the user change only applies to the activities reload
 *
 * Revision 1.43  2004/12/20 15:30:41  neildogg
 * - If the user was empty and the Insert Log button was used, move it to the session user
 *
 * Revision 1.42  2004/12/20 13:58:37  neildogg
 * This isn't even used anywhere, but it's fixed anyway
 *
 * Revision 1.41  2004/11/15 16:16:16  neildogg
 * Improperly ordered function call
 *
 * Revision 1.40  2004/10/18 14:12:11  vanmer
 * - fixed auto-advance workflow bug
 * - now checks for activities in the next status before advancing
 *
 * Revision 1.39  2004/08/16 15:18:15  neildogg
 * - Removed $old_sort_order as it is used nowhere else
 *  - and eg was being used on opportunities table
 *  - (which has no sort order)
 *
 * Revision 1.38  2004/08/13 10:22:12  cpsource
 * - Define a default value for old_status
 *
 * Revision 1.37  2004/08/11 18:44:18  braverock
 * - allow clearing a previously set activity completion
 *
 * Revision 1.36  2004/07/30 15:27:16  braverock
 * - move undefined variable check for activity status above followup check
 *   - resolves SF bug 999663 reported by John Fawcett
 *
 * Revision 1.35  2004/07/28 20:44:43  neildogg
 * - Added field recent_action to recent_items
 *  - Same function works transparently
 *  - Current items have recent_action=''
 *  - update_recent_items has new optional parameter
 *
 * Revision 1.34  2004/07/27 14:44:04  neildogg
 * - Removed unnecessary code
 *  - Changed $sql variable to proper variable
 *  - Changed variable to function call
 *
 * Revision 1.33  2004/07/27 10:02:14  cpsource
 * - Add routine arr_vars_post_with_cmd and test.
 *
 * Revision 1.32  2004/07/27 09:26:06  cpsource
 * - Move opportunity_description to the optionally passed area.
 *
 * Revision 1.31  2004/07/19 21:19:52  neildogg
 * - Allow contact to be shifted with opportunity as well as activity
 *
 * Revision 1.30  2004/07/16 04:53:51  introspectshun
 * - Localized strings for i18n/translation support
 *
 * Revision 1.29  2004/07/14 22:54:39  introspectshun
 * - Altered LEFT JOINs to use standard ON syntax rather than USING
 * - Statuses SQL update query now uses GetUpdateSQL
 *
 * Revision 1.28  2004/07/14 18:34:15  braverock
 * - fixed logic error that could result in assignment rather than comparison
 *
 * Revision 1.27  2004/07/14 18:33:28  neildogg
 * - Found the correct logic error, thanks to Brian
 *
 * Revision 1.26  2004/07/14 16:53:01  neildogg
 * - Removed duplicate code
 *  - Fixed logic problem
 *  - Added hook on completion (for automated tasks)
 *
 * Revision 1.25  2004/07/14 15:22:17  cpsource
 * - Fixed various undefines, including:
 *     $opportunity_description
 *     $followup
 *     $saveandnext
 *     $table_status_id
 *     $probability
 *
 * Revision 1.24  2004/07/13 19:52:37  braverock
 * - add ability to process activity association to open Opportunity/Case
 *   for unassociated activities based on $associate_activities global
 *
 * Revision 1.23  2004/07/10 13:51:18  braverock
 * - improved date handling error checking
 *
 * Revision 1.22  2004/07/07 18:06:18  neildogg
 * - Added sticky opportunity description
 *
 * Revision 1.21  2004/07/02 17:59:45  neildogg
 * - Variable passed properly to browse-next.php
 *
 * Revision 1.20  2004/06/24 19:58:47  braverock
 * - committing enhancements to Save&Next functionality
 *   - patches submitted by Neil Roberts
 *
 * Revision 1.19  2004/06/21 16:10:07  braverock
 * - improved error handling around changes to Probability
 *
 * Revision 1.18  2004/06/15 19:20:22  introspectshun
 * - Save and Next now uses GetUpdateSQL()
 *
 * Revision 1.17  2004/06/13 09:15:07  braverock
 * - add Save & Next functionality
 *   - code contributed by Neil Roberts
 *
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