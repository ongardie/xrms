<?php
/**
 * Save the updated activity information to the database
 *
 * @todo: potential security risk in pulling some of these variables from the submit
 *        should eventually do a select to get the variables if we are going
 *        to post a followup
 *
 * $Id: edit-2.php,v 1.52 2005/01/13 18:40:09 vanmer Exp $
 */

//include required files
require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');


// POST'ed in data
$arr_vars = array (
                   // posted data
                   'return_url' => arr_vars_POST ,
                   'activity_id' => arr_vars_POST ,
                   'activity_type_id' => arr_vars_POST ,
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

                   // optionally posted data
                   'opportunity_description' => arr_vars_POST_UNDEF ,
                   'probability' => arr_vars_POST_UNDEF ,
                   'followup' => arr_vars_POST_UNDEF ,
                   'saveandnext' => arr_vars_POST_UNDEF ,
                   'switch_opportunity' => arr_vars_POST_UNDEF ,
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
// set the correct activity status flag
if ($activity_status == 'on') {
    $activity_status = 'c';
} else {
    //force a value for open activity, so the GetUpdateSQL will work
    $activity_status = 'o';
}

//mark this activity as completed if follow up is to be scheduled
if ($followup) { $activity_status = 'c'; }

$scheduled_at = strtotime($scheduled_at);
//set scheduled_at to today if it is empty
if (!$scheduled_at) {
    $scheduled_at = strtotime(date('Y-m-d'));
}

// set ends_at to scheduled_at if it is empty
$ends_at = strtotime($ends_at);
if (!$ends_at) {
    $ends_at = $scheduled_at;
}

// make sure ends_at is later than scheduled at
if ($scheduled_at > $ends_at) {
   //set $ends_at to = $scheduled_at
   $ends_at = $scheduled_at;
}

$contact_id = ($contact_id > 0) ? $contact_id : 'NULL';

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
//$con->debug = 1;

// if it's closed but wasn't before, update the closed_at timestamp
$completed_at = ($activity_status == 'c') && ($current_activity_status != 'c') ? time() : 'NULL';

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

$sql = "SELECT contact_id
        FROM contacts
        WHERE contact_id=" . $contact_id . "
        AND cell_phone=''
        AND work_phone=''";
$rst = $con->execute($sql);
if(!$rst) {
    db_error_handler($con, $sql);
}
elseif($rst->rowcount()) {
    if($company_id) {
        update_recent_items($con, $session_user_id, "activities", $company_id, "sidebar_view");
    }
}

$sql = "SELECT * FROM activities WHERE activity_id = " . $activity_id;
$rst = $con->execute($sql);

$rec = array();
$rec['activity_type_id']     = $activity_type_id;
$rec['contact_id']           = $contact_id;
$rec['activity_title']       = $activity_title;
$rec['activity_description'] = $activity_description;
if(empty($user_id) && strstr($return_url, 'fill_user')) {
    //If the user ID was empty and we're returning to the same activity page
    // then we're going to assume that the user has taken over the activity.
    $rec['user_id']          = $session_user_id;
}
else {
    $rec['user_id']          = $user_id;
}
$rec['scheduled_at']         = $scheduled_at;
$rec['ends_at']              = $ends_at;
$rec['completed_at']         = $completed_at;
$rec['activity_status']      = $activity_status;
$rec['on_what_table']        = $on_what_table;
$rec['on_what_id']           = $on_what_id;

$upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
if (strlen($upd)>0) {
    $rst = $con->execute($upd);
    if (!$rst) {
        db_error_handler ($con, $upd);
    }
}

if($switch_opportunity == "on") {
    $sql = "SELECT * FROM opportunities WHERE opportunity_id = " . $on_what_id;
    $rst = $con->execute($sql);

    $rec = array();
    $rec['contact_id'] = $contact_id;

    $upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
    if(strlen($upd)) {
        $rst = $con->execute($upd);
        if(!$rst) {
            db_error_handler($con, $upd);
        }
    }
}

if($on_what_table == 'opportunities' and (strlen($opportunity_description)>0)) {
    //Update Opportunity Description
    $sql = "SELECT * FROM opportunities WHERE opportunity_id = " . $on_what_id;
    $rst = $con->execute($sql);

    $rec = array();
    $rec['opportunity_description'] = $opportunity_description;

    $upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
    if (strlen($upd)>0) {
        $desc_rst = $con->execute($upd);
        if (!$desc_rst) {
            db_error_handler ($con, $upd);
        }
    }
}

if($on_what_table == 'opportunities' and (strlen($probability)>0)) {
    $opp_sql = "SELECT * FROM opportunities WHERE opportunity_id = $on_what_id";
    $rst = $con->execute($opp_sql);

    $rec = array();
    $rec['probability'] = $probability;

    $upd = $con->GetUpdateSQL($rst, $rec, false, $magicq=get_magic_quotes_gpc());
    if (strlen($upd)>0) {
        //update the probability
        $prob_rst= $con->execute($upd);
        if (!$prob_rst) {
            db_error_handler ($con, $upd);
        } else {
            $prob_rst->close();
        }
    }
}

add_audit_item($con, $session_user_id, 'updated', 'activities', $activity_id, 1);

// if it's closed but wasn't before, allow the computer to perform an action if it wants to
if($activity_status == 'c' && $current_activity_status != 'c') {
    do_hook_function("run_on_completed", $activity_id);
}

//get sort_order field
$sql = "select * from " . strtolower($table_name) . "_statuses where " . strtolower($table_name) ."_status_id=$table_status_id";
$rst = $con->SelectLimit($sql, 1, 0);
$rst = $con->execute($sql);
if ($rst) {
    $sort_order = $rst->fields['sort_order'];
    switch (strtolower($table_name)) {
        case 'case':
            $type_id = $rst->fields[strtolower($table_name).'_type_id'];
        break;
        default:
            $type_id=false;
        break;
    }
    $rst->close();
}

//get current username
if($user_id) {
    $sql = "select username from users where user_id = $user_id";
    $rst = $con->SelectLimit($sql, 1, 0);
    if ($rst) { $username = $rst->fields['username']; }
    $rst->close();
}

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


// null out old_status
$old_status = '';

/* this saves case/opportunity status changes to the database when they are changed in one.php */
$table_name = strtolower($table_name);
if ($table_name !== "attached to") {
    $sql = "select * from $on_what_table where ".$table_name."_id=$on_what_id";
    $rst = $con->execute($sql);

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
            $sort_order++;
            //$con->debug=1;            
            $sql = "select * from {$table_name}_statuses
                where sort_order=$sort_order";
            if ($type_id) {
                $sql.=" and {$table_name}_type_id=$type_id ";
            }
            $sql.=" and {$table_name}_status_record_status='a'";
            $status_rst = $con->execute($sql);
            if (!$status_rst) db_error_handler($con, $sql);
            if ($status_rst AND ($status_rst->numRows()>0)) {
                $table_status_id = $status_rst->fields[$table_name . '_status_id'];
                
                //look for activity_templates defined for the next status in the workflow
                $sql = "select * from activity_templates where on_what_table=" . $con->qstr($table_name.'_statuses') . " AND on_what_id=$table_status_id";
                $rst=$con->execute($sql);
                if (!$rst) { db_error_handler($con,$sql); }
    
                //if there are templates defined for the next status, find it
                if ($rst->numRows()>0) {
                    $no_update = false;
                }
            }
        }
    }

    // check for status change
    if ($old_status !== $table_status_id){
        //if there is only one field, the result set is empty (no old activities)
        //  otherwise prompt the user
        if ($no_update) {
            if ($activity_return_id)
                $return_url = "/activities/one.php?msg=no_change&activity_id=$activity_return_id";
            elseif ($return_url) {
                if (strpos($return_url,'?')!==false) { $sep='&'; }
                else { $sep='?'; }
                $return_url.=$sep.'msg=no_change';
             }
             else 
                $return_url="/private/home.php?msg=no_change";
        }

        //update if there are no open activities
        if (!$no_update) {
            $sql = "SELECT * FROM " . $on_what_table . " WHERE " . $table_name . "_id = " . $on_what_id;
            $rst = $con->execute($sql);

            $rec = array();
            $rec[$table_name . "_status_id"] = $table_status_id;

            $upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());

            if (strlen($upd)>0) {
                //update the records
                $sc_rst = $con->execute($upd);
                if (!$sc_rst) {
                    db_error_handler($con, $upd);
                } else {
                    $sc_rst->close();
                }
            }
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
} elseif ($activity_status == 'c') {
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
    header ('Location: '.$http_site_root."/activities/new-2.php?user_id=$session_user_id&activity_type_id=$activity_type_id&on_what_id=$on_what_id&contact_id=$contact_id&on_what_table=$on_what_table&company_id=$company_id&user_id=$user_id&activity_title=".htmlspecialchars( _("Follow-up") . ' ' . $activity_title ) .  "&company_id=$company_id&activity_status=o&on_what_status=$old_status&return_url=$return_url&followup=true" );
} elseif($saveandnext) {
    header("Location: browse-next.php?activity_id=$activity_id");
} else {
    header("Location: " . $http_site_root . $return_url);
}

/**
 * $Log: edit-2.php,v $
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
