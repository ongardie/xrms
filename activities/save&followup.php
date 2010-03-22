<?php
/**
 * activites/new&followup.php - This script saves an edited activity into the
 * database and, if the has requested it, also inserts a
 * followup activity.  Adapted from activities/edit-2.php.
 *
 */

//include required files
require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-preferences.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'utils-activities.php');
require_once($include_directory . 'utils-workflow.php');
require_once($include_directory . 'utils-cases.php');
require_once($include_directory . 'utils-opportunities.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$con = get_xrms_dbconnection();
//$con->debug = 1;

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
                   'division_id' => arr_vars_POST ,
                   'email_to' => arr_vars_POST ,
                   'table_name' => arr_vars_POST ,
                   'table_status_id' => arr_vars_POST ,
                   'thread_id' => arr_vars_POST ,
                   'address_id' => arr_vars_POST ,
                   'followup_from_id' => arr_vars_POST ,
		   'associate_activities' => arr_vars_POST ,
                   'activity_recurrence_id' => arr_vars_POST ,

                   // optionally posted data
                   'opportunity_description' => arr_vars_POST_UNDEF ,
                   'probability' => arr_vars_POST_UNDEF ,
                   // These fields may be set if the user wants to schedule a
                   // followup activity
                   'followup' => arr_vars_POST_UNDEF ,
                   'followup_transfer_notes' => arr_vars_POST_UNDEF ,

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

// @TODO: Move the default_activity_duration setting to the system preferences
// Default activty duration 15 mins (900 seconds)
$default_activity_duration = 900;

// Convert all times to unix timestamps, perform sanity check on the values
// or set them, if not passed-in.  Remove all guesswork!
$scheduled_at = strtotime($scheduled_at);
$scheduled_at = $scheduled_at ? $scheduled_at : strtotime("now");

$ends_at = strtotime($ends_at);
// No activity lasts zero time!
$ends_at = ($ends_at && ($ends_at > $scheduled_at)) ? $ends_at : $scheduled_at + $default_activity_duration;

if ($followup) {
    // Mark this activity as completed if a follow up is to be scheduled
    $activity_status = 'c';
    if(!$thread_id) {
        $thread_id = $activity_id;
    }

    // @TODO: Move the $default_followup_time setting to the system preferences.
    // It is validated in several places in XRMS yet it is defined nowhere.
    // We will assume it is an offset in seconds.

    // If default_followup_time is not set, set it at 1 week (604800 seconds)
    $followup_scheduled_at = (isset($default_followup_time) && $default_followup_time) ? $scheduled_at + $default_followup_time : $scheduled_at + 604800;
    $followup_ends_at = $followup_scheduled_at + $default_activity_duration;
}

// Get the format for date/time
$datetime_format = set_datetime_format($con, $session_user_id);

// Convert all dates back to the appropriate format...
// @TODO: Going back and forth to unix timestamps and the $datetime_format is
// way too clumsy.  Unix timestamps would have done just fine.  In addition,
// there appears to be no consensus if dates should be stored in 'Y-m-d H:i:s'
// or $datetime_format (compare /activities/new-2.php and /activities/edit-2.php
// and their child scripts /activities/new@followup.php and
// /activities/save&followup.php.  Potential problems down the road!
//
$ends_at_string = date($datetime_format,$ends_at);
$starts_at_string = date($datetime_format,$scheduled_at);
if ($followup) {
    $followup_scheduled_at = date($datetime_format, $followup_scheduled_at);
    $followup_ends_at = date($datetime_format, $followup_ends_at);
}

//get the existing activity record for use later in the script
$activity = get_activity($con, $activity_id);

$new_contact_id = ($contact_id > 0) ? $contact_id : 0;

if (!$contact_id OR $contact_id==0) {
    // set to the previous contact_id
    $contact_id=$activity['contact_id'];
}

// if it's closed but wasn't before, update the completed_at timestamp
$completed_at = ($activity_status == 'c') && ($current_activity_status != 'c') ? time() : NULL;
$completed_by= ($activity_status == 'c') && ($current_activity_status != 'c') ? $session_user_id : NULL;

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
if(empty($user_id)) {
    // If the user ID was empty
    // then we're going to assume that the current user has taken over the activity.
    $rec['user_id']          = $session_user_id;
} else {
    $rec['user_id']          = $user_id;
}
$rec['division_id']          = $division_id;
//use new contact ID here to update contact with newly set ID
$rec['contact_id']           = $new_contact_id;
$rec['activity_title']       = trim($activity_title);
$rec['activity_description'] = trim($activity_description);
$rec['last_modified_by']     = $session_user_id;
$rec['last_modified_at']     = date($datetime_format,time());
$rec['scheduled_at']         = $scheduled_at;
$rec['ends_at']              = $ends_at;
$rec['completed_at']         = $completed_at;
$rec['activity_status']      = $activity_status;
$rec['on_what_table']        = $on_what_table;
$rec['on_what_id']           = $on_what_id;
$rec['completed_by']         = $completed_by;
$rec['thread_id']            = $thread_id;
$rec['address_id']           = $address_id;
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
                // print_r($rec);
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

// Prepare to send an email if the user has requested it
if (!empty($email_to)) {

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

    $output = _("Activity") .": <a href=\"" . full_http_site_root() . "/activities/one.php?activity_id=" . $activity_id . "\">" . htmlspecialchars($activity_title) . "</a>";
    $output .= "\n<br>" . _("Activity Type") . ": " .  $activity_type;
    $output .= "\n<br>" . _("Owner") . ": " .  $username;
    $output .= "\n<br>" . _("Scheduled Start") . ": " . $starts_at_string; //line added by Randy 6/15/07
    $output .= "\n<br>" . _("Scheduled End") . ": " . $ends_at_string;
    $output .= "\n<br>" . _("Company") . ": " . $company_name;
    $output .= "\n<br>" . _("Contact") . ": " . $contact_name . "<br>\n";

    if (get_user_preference($con, $user_id, "html_activity_notes") == 'y') {
        $tmp = trim($activity_description);
    } else {
        $tmp = htmlspecialchars(nl2br(trim($activity_description)));
    }

    $output .= "\n<hr>". _("Activity Notes") .": <br>\n".  $tmp;

    $from_email_address = $con->GetOne('SELECT email FROM users WHERE user_id=?', $session_user_id);
    if (!$from_email_address)
         $from_email_address = get_system_parameter($con, "Sender Email Address");

    // Provide the activity_mailer hook to allow plugins to use an alternative email and logging script
    $tmp = array();
    $tmp['from'] = $from_email_address;
    $tmp['to'] = $email_to;
    $tmp['subject'] = _("Updated Activity") . ": " . $activity_title;
    $tmp['body_html'] = $output;
    $activity_mailer = do_hook_function('activity_mailer', $tmp);
    if (!$activity_mailer) {

        require_once $include_directory . 'classes/SMTPs/SMTPs.php';
        $objSMTP = new SMTPs ();
        $objSMTP->setConfig( $include_directory . 'classes/SMTPs/SMTPs.ini.php');

        $objSMTP->setFrom ( $from_email_address  );
        $objSMTP->setSubject ( _("Updated Activity") . ": " . $activity_title );
        $objSMTP->setTo ( $email_to );
        $objSMTP->setBodyContent ( $output, 'html');

        $objSMTP->sendMsg ();
        $errors = $objSMTP->getErrors();
        if ((!empty($errors)) and ($errors <> "No Errors Generated."))
            trigger_error('SMTP errors: '.$errors, E_USER_WARNING); else
                    {
                    //add activity record to user who received the e-mail
                    $sql_insert_activity = "insert into activities set
                            activity_type_id = '3',
                            user_id = $user_id,
                            company_id = $company_id,
                            contact_id = $contact_id,
                            activity_title = 'Email: Activity Sent To Another User',
                            activity_description = 'User: {$email_to} -> $output',
                            entered_at = ".$con->dbtimestamp(mktime()).",
                            scheduled_at = ".$con->dbtimestamp(mktime()).",
                            ends_at = ".$con->dbtimestamp(mktime()).",
                            entered_by = $session_user_id,
                            activity_status = 'c'";

                    $con->execute($sql_insert_activity);
                    }
    }
}

if ($followup) {
    $followup_rec = $rec;
    // We'll leave the following checks in for a possible future enhancement of the UI
    // to resemble the functionality of /activities/activities-widget.php
    if ($followup_user_id) $followup_rec['user_id'] = $followup_user_id;
    if ($followup_activity_type_id) $followup_rec['activity_type_id'] = $followup_activity_type_id;
    if ($followup_contact_id) $followup_rec['contact_id'] = $followup_contact_id;

    $followup_rec['company_id'] = $company_id;
    $followup_rec['scheduled_at'] = $followup_scheduled_at;
    $followup_rec['ends_at'] = $followup_ends_at;
    $followup_rec['activity_status'] = 'o';
    $followup_rec['resolution_description'] = NULL;
    $followup_rec['activity_resolution_type_id'] = NULL;
    $followup_rec['activity_title'] = _('Follow-Up') .' '. $activity_title;
    $followup_rec['on_what_status'] = $old_status;
    if (!$followup_transfer_notes) $followup_rec['activity_description'] = NULL;
    if (!$followup_rec['thread_id']) $followup_rec['thread_id'] = $activity_id;
    $followup_rec['followup_from_id'] = $activity_id;

    $followup_activity_id = add_activity($con, $followup_rec, false, $magicq);
    $con->close();

    if (!$followup_activity_id) {
        var_dump($followup_rec);
//        $msg=urlencode(_("Failed to add followup activity"));
//        header("Location: " . $http_site_root . $return_url."&msg=$msg");
        exit();
    }
    
    // Send the user to edit the follow-up activity
    header("Location: " . $http_site_root . "/activities/one.php?return_url=" . $return_url . "&activity_id=" . $followup_activity_id);

} else {
    // Close the database connection because we don't need it anymore.
    $con->close();

    if($saveandnext) {
        header("Location: browse-next.php?activity_id=$activity_id");
    } elseif($recurrence) {
        header("Location: recurrence_sidebar.php?activity_id=$activity_id&activity_recurrence_id=$activity_recurrence_id");
    } elseif($print_view) {
        header("Location: " . $http_site_root . $return_url . "&print_view=true");
    } else {
        header("Location: " . $http_site_root . $return_url);
    }
}

?>