<?php
/**
 * Utility functions for manipulating activities
 *
 * These functions create, retrieve, delete and modify activities, activity participants,
 * and the possibly positions a participant can serve within an activity type
 * This file should be included anywhere activities need to be created or modified
 *
 * @author Aaron van Meerten
 * @package XRMS_API
 *
 * $Id: utils-activities.php,v 1.30 2006/07/12 03:38:26 vanmer Exp $

 */

require_once('utils-workflow.php');

/**********************************************************************/

/**
 *
 * Adds an activity to XRMS based on data in the associative array,
 * returning the id of the newly created activity if successful
 * participants may optionally be passed in through the participants array of associative
 * array records specifying contact_id and activity_participant_position_id
 *
 * These 'activities' tables fields are required.
 * This method will fail without them.
 * - activity_type_id        - is pulled from activity_type table
 * - company_id              - which company is this activity related to
 * - activity_title          - Activity title, ie: SUBJECT of email
 *
 * These fields are optional, some may be derived from other fields if not defined.
 * - user_id                 - sets ownership, defaults to session_user_id
 * - contact_id              - who is this activity related to
 * - on_what_table           - what the activity is attached or related to
 * - on_what_id              - which ID to use for this relationship
 * - on_what_status          - workflow status
 * - activity_description    - A short description of the activity
 * - scheduled_at            - this might be a future activity/event, defaults to 'entered_at'
 * - ends_at                 - this activity may have duration, ie: phone call. If not defined, defaults to 'scheduled_at'
 * - activity_status         - [o] Open [c] Completed, defaults to [o]
 * - completed_bol           - is this activity finsished?
 * - completed_at            - when was the activity finshed. Uses NOW()
 * - completed_by            - who finshed it, uses session_user_id
 * - thread_id               - activity_id of the root of the thread
 * - followup_from_id        - activity_id of the parent of this activity
 *
 * Do not define these fields, they are auto-defined
 * - activity_id             - auto increment field
 * - entered_at              - when was record created
 * - entered_by              - who created the record
 * - last_modified_at        - when was record modified - this will be the same as 'entered_at'
 * - last_modified_by        - who modified the record  - this will be the same as 'entered_by'
 * - activity_record_status  - the database defaults this to [a] Active

 *
 * @param adodbconnection $con handle to the database
 * @param array $activity_data with associative array defining activity data (extract()'d inside function)
 * @param array $participants with participants and positions (contacts who participated in the activity)
 *
 * @return integer $activity_id identifying newly created activity or false for failure
 */
function add_activity($con, $activity_data, $participants=false, $magic_quotes=false)
{

    // Right off the bat, if these are not set, we can't do anything!
    if ( (! $con)  ||  (! $activity_data ) )
        return false;

    //save to database
    global $session_user_id;

    //Turn activity_data array into variables
    extract($activity_data);

    // We need an activity type and a company_id OR an on_what relationship
    if ((!$activity_type_id) || !($company_id || ($on_what_table && $on_what_id)))
        return false;

    // Create new RECORD array '$rec' for SQL INSERT

    // This var was already checked, if it wasn't valid, we wouldn't be here
    $rec['activity_type_id'] = $activity_type_id;

    // These values are auto set, thay can not be modified via API
    $rec['entered_at']       = time();
    $rec['entered_by']       = $session_user_id;

    // Because this is a "create" method, these values are derived from the above values
    // and can not be modified via API
    $rec['last_modified_at'] = $rec['entered_at'];
    $rec['last_modified_by'] = $rec['entered_by'];

    // If this is not defined, then derive it from current time
    $rec['scheduled_at']     = ($scheduled_at)   ? strtotime($scheduled_at) : $rec['entered_at'];

    // This does 2 things:
    //  * Checks to make sure that '$ends_at' is defined
    //  * Checks that '$ends_at' is defined as a date *after* '$scheduled_at'
    $rec['ends_at']          = ($ends_at) ? (($scheduled_at > $ends_at) ? $rec['scheduled_at'] : strtotime($ends_at)) : $rec['scheduled_at'];

    // If this is not defined, then pull it from $session_user_id
    $rec['user_id']          = ($user_id)        ? $user_id        : $session_user_id;

    // A 'title' for this activity for future reference and review
    $rec['activity_title']   = ($activity_title) ? $activity_title : _("[none]");

    // A brief description of the activity for future reference and review
    $rec['activity_description']  = ($activity_description) ? $activity_description : '';

    // These values, if not defined, will be set by default values defined within the Database
    // Therefore they do not need to be created within this array for RECORD insertion
    if ($activity_status)      { $rec['activity_status']      = $activity_status; }
    if ($activity_template_id > 0)       { $rec['activity_template_id']           = $activity_template_id; }
    if ($on_what_status > 0)   { $rec['on_what_status']       = $on_what_status; }
    if ($completed_at)         { $rec['completed_at']         = $completed_at; }
    if ($thread_id)            { $rec['thread_id']            = $thread_id; }
    if ($followup_from_id)     { $rec['followup_from_id']     = $followup_from_id; }
    if ($on_what_table)        { $rec['on_what_table']        = $on_what_table; }
    if ($on_what_id > 0)       { $rec['on_what_id']           = $on_what_id; }
    if ($company_id > 0)       { $rec['company_id']           = $company_id; }
    if ($contact_id > 0)       { $rec['contact_id']           = $contact_id; }
    if ($address_id > 0)       { $rec['address_id']           = $address_id; }
    if ($activity_recurrence_id > 0) { $rec['activity_recurrence_id']           = $activity_recurrence_id; }
    if ($activity_resolution_type_id > 0) { $rec['activity_resolution_type_id'] = $activity_resolution_type_id; }
    if ($activity_priority_id > 0) { $rec['activity_priority_id']           = $activity_priority_id; }
    if ($resolution_description > 0) { $rec['resolution_description']           = $resolution_description; }

    $tbl = 'activities';
    $ins = $con->GetInsertSQL($tbl, $rec, $magic_quotes);
    $rst=$con->execute($ins);
    if (!$rst) { db_error_handler($con, $ins); return false; }
    $activity_id = $con->insert_id();

    $rec['activity_id']=$activity_id;
    do_hook_function('activity_new_2', $rec);

    add_audit_item($con, $session_user_id, 'created', 'activities', $activity_id, 1);

    if (!$participants) {
        $participants=array(array('contact_id'=>$contact_id, 'activity_participant_position_id'=>1));
    }

   foreach ($participants as $pdata) {
        add_activity_participant($con, $activity_id, $pdata['contact_id'], $pdata['activity_participant_position_id']);
    }

    return $activity_id;

}

/**********************************************************************/
/**
 *
 * Gets one or more activities from XRMS, based on criteria passed in through associative array (array key used as fieldname)
 * Can return an adodbrecordset object instead of an associative array of records through optional flag
 *
 * @param adodbconnection $con handle to the database
 * @param array $activity_data with associative array defining activity data to search for
 * @param boolean $show_deleted specifying if deleted activities should be included in the search (defaults to false, only active activities)
 * @param boolean $return_recordset specifying if the function should return a recordset or associative array with the results of the search (defaults to false, return associative array)
 *
 * @return array $activity_data with results of search or false if search finds no results/failed
 */
function get_activity($con, $activity_data, $show_deleted=false, $return_recordset=false) {

    if (!$activity_data) return false;
    if (!is_array($activity_data)) {
        //assume they just passed an activity_id, like the other API's
        $activity_id= $activity_data ;
        if (!$activity_id) return false;
        $activity_data=array();
        $activity_data['activity_id']=$activity_id;
    }

    $sql = "SELECT
                a.*, addr.*, a.address_id AS activity_address_id, c.company_id, c.company_name, cont.first_names, cont.last_name, " .
                $con->Concat("u1.first_names", $con->qstr(' '), "u1.last_name") . " AS entered_by_username, " .
                $con->Concat("u2.first_names", $con->qstr(' '), "u2.last_name") . " AS last_modified_by_username, " .
                $con->Concat("u3.first_names", $con->qstr(' '), "u3.last_name") . " AS completed_by_username " . "
            FROM
                activities a
                left outer join users u1 ON a.entered_by = u1.user_id
                left outer join users u2 ON a.last_modified_by = u2.user_id
                left outer join users u3 ON a.completed_by = u3.user_id
                    left outer join contacts cont on a.contact_id = cont.contact_id
                    join companies c ON c.company_id = a.company_id
                    left outer join addresses addr ON addr.address_id = c.default_primary_address";

    $where=array();
    if (!$show_deleted) $activity_data['activity_record_status']='a';
    $tablename='a';

    if (array_key_exists('activity_id',$activity_data) AND trim($activity_data['activity_id'])) {
        $where['activity_id'] = $activity_data['activity_id'];
        $wherestr=make_where_string($con, $where, $tablename);
    } else {
        $extra_where=array();
        foreach ($activity_data as $akey=>$aval) {
            switch ($akey) {
                case 'due_before':
                    unset($activity_data[$akey]);
                    $extra_where[]="ends_at<=".$con->DBTimestamp($aval);
                break;
            }
        }
        if (count($extra_where)==0) $extra_where=false;
        $wherestr=make_where_string($con, $activity_data, $tablename, $extra_where);
    }
    if ($wherestr) $sql.=" WHERE $wherestr";

    $rst = $con->execute($sql);
    if (!$rst) { //database error
        db_error_handler($con, $sql);
        return false;
    } elseif ($rst->EOF) { //no record returned
        return false;
    } else { //we got a record
        //now process our return options
        if ($return_recordset) return $rst;
        while (!$rst->EOF) {
            $ret[]=$rst->fields;
            $rst->movenext();
        }
    }
    if (count($ret)>0) return $ret;
    else return false;
}

/**********************************************************************/
/**
 *
 * Updates an activity in XRMS from an associative array
 * Either an activity_id must be explicitly set or an adodbrecordset for the record to be updated
 * must be passed in or the function will fail
 *
 * @param adodbconnection $con handle to the database
 * @param array $activity_data with associative array defining activity data to update
 * @param integer $activity_id optionally identifying activity in the database (required if not passing in a ecordset to $activity_rst)
 * @param adodbrecordset $activity_rst optionally providing a recordset to use for the update (required if not passing in an integer for $activity_id)
 * @param boolean $update_default_participant specifying if default participant for activity should be updated, if contact_id is updated (defaults to true, will update default participant)
 *
 * @return boolean specifying if update succeeded
 */
function update_activity($con, $activity_data, $activity_id=false, $activity_rst=false, $update_default_participant=true, $magic_quotes=false, $return_url=false, $table_status_id=false, $old_status=false) {
    global $session_user_id;
    if (!$activity_id AND !$activity_rst) return false;
    if (!$activity_data) return false;
    $ret=array();
    if (!$activity_rst) {
        $sql = "SELECT * FROM activities WHERE activity_id=$activity_id";
        $activity_rst=$con->execute($sql);
        if (!$activity_rst) { db_error_handler($con, $activity_sql); return false; }
    }
        if (!$activity_id) $activity_id=$activity_rst->fields['activity_id'];

        if ($update_default_participant) {
            if (array_key_exists('contact_id',$activity_data)) {
//                echo '<pre>'; print_r($activity_data); print_r($activity_rst->fields);
                if ($activity_data['contact_id']!=$activity_rst->fields['contact_id']) {
                //contact changed, change default participant
                if ($activity_rst->fields['contact_id']) {
                    $activity_participant=get_activity_participants($con, $activity_id, $activity_rst->fields['contact_id'], 1);
                    if ($activity_participant) {
                        //get existing default participant, mark it as removed
                        $participant_data=current($activity_participant);
                        $activity_participant_id=$participant_data['activity_participant_id'];
                        $dret=delete_activity_participant($con, $activity_participant_id);
                        $updated_participant=true;
                    }
                }
               }
            }
            if ($activity_data['contact_id'] AND $activity_data['contact_id']!='NULL') {
                //new contact for activity is not blank, so add it as the new default participant
                $activity_participant_id=add_activity_participant($con, $activity_id, $activity_data['contact_id'], 1);
                $updated_participant=true;
            }
        }
        if (($activity_data['activity_status']=='c') AND ($activity_rst->fields['activity_status']!='c')) {
            $activity_data['completed_by']=$_SESSION['session_user_id'];
            $activity_data['completed_at']=time();
            $completed_activity=true;
        }

        if (($activity_data['activity_status']!='c') AND ($activity_rst->fields['activity_status']=='c')) {
            $activity_data['completed_by']='NULL';
            $activity_data['completed_at']='NULL';
            $completed_activity=false;
        }

    $update_sql = $con->getUpdateSQL($activity_rst, $activity_data, false, $magic_quotes);

    if ($update_sql) {
        $update_rst=$con->execute($update_sql);
        if (!$update_rst) { db_error_handler($con, $update_sql); return false; }
        $updated_sql=true;
    }

    $param = array($activity_rst, $activity_data);
    do_hook_function('activity_edit_2', $param);
    
    // if it's closed but wasn't before, allow the computer to perform an action if it wants to
    if($completed_activity) {
            do_hook_function("run_on_completed", $activity_id);
    }

     //DO WORKFLOW STUFF HERE
    $activity_template_id = $activity_rst->fields['activity_template_id'];
    $company_id=$activity_data['company_id'];
    $contact_id=$activity_data['contact_id'];
    if (!$company_id) $company_id=$activity_rst->fields['company_id'];
    if (!$contact_id) $contact_id=$activity_rst->fields['contact_id'];

    $on_what_id=$activity_data['on_what_id'];
    if (!$on_what_id) {
        $on_what_id=$activity_rst->fields['on_what_id'];
    }

    $on_what_table=$activity_data['on_what_table'];
    if (!$on_what_table) {
        $on_what_table=$activity_rst->fields['on_what_table'];
    }

    if ($completed_activity && $activity_template_id) {
        $ret=workflow_activity_completed($con, $on_what_table, $on_what_id, $activity_template_id, $company_id, $contact_id, $return_url);
    } else {
         //hack to allow related entity status change from activity controlled by workflow activity action
        //defaults to allowing change unless underlying workflow actions have already happened, in which case do not allow manual status change
         $ret['allow_status_change']=true; 
    }


    if ($updated_sql OR $updated_participants) {
         add_audit_item($con, $session_user_id, 'updated', 'activities', $activity_id, 1);
     }

    if (!$ret) $ret=true;
    return $ret;
}

/**********************************************************************/
/**
 *
 * Deletes an activity from XRMS, based on passed in activity_id
 * Can delete activity from database or mark as removed using record status
 *
 * @param adodbconnection $con handle to the database
 * @param integer $activity_id identifying which activity to delete
 * @param boolean $delete_from_database specifying if activity should be deleted from the database, or simply marked with a deleted flag (defaults to false, mark with deleted flag)
 * @param boolean $delete_participants indicating if activity_participants should also be removed
 *
 * @return array $activity_data with results of search or false if search finds no results/failed
 */
function delete_activity($con, $activity_id=false, $delete_from_database=false, $delete_participants=true) {
    if (!$activity_id) return false;
    if ($delete_from_database) {
        $sql = "DELETE FROM activities WHERE activity_id=$activity_id";
    } else {
        $update_array=array('activity_record_status'=>'d');
        $sql = "SELECT * FROM activities WHERE activity_id=$activity_id";
        $update_rst=$con->execute($sql);
        if (!$update_rst) {db_error_handler($con, $sql); return false; }
        $sql = $con->GetUpdateSQL($update_rst, $update_array, true, get_magic_quotes_gpc());
    }
    if (!$sql) return false;

    if ($delete_participants) {
        $activity_participants=get_activity_participants($con, $activity_id);
        if ($activity_participants) {
            foreach ($activity_participants as $participant_info) {
                $ret=delete_activity_participant($con, $participant_info['activity_participant_id'], $delete_from_database);
            }
        }
    }
    $rst=$con->execute($sql);
    if (!$rst) {db_error_handler($con, $sql); return false; }
    return true;
}
/**********************************************************************/
/**
 *
 * Deletes 0 or more activities from XRMS, based on passed in where_clause
 * Can delete activity from database or mark as removed using record status
 *
 * @param adodbconnection $con handle to the database
 * @param string $where_clause identifying which activitie(s) to delete
 * @param boolean $delete_from_database specifying if activity should be deleted from the database, or simply marked with a deleted flag (defaults to false, mark with deleted flag)
 * @param boolean $delete_participants indicating if activity_participants should also be removed
 *
 * @return array $activity_data with results of search or false if search finds no results/failed
 */
function delete_activities($con, $where_clause=false, $delete_from_database=false, $delete_participants=true) {
    if (!$where_clause) return false;
    if ($delete_from_database) {
        $sql = "DELETE FROM activities WHERE $where_clause";
    } else {
        $update_array=array('activity_record_status'=>'d');
        $sql = "SELECT * FROM activities WHERE $where_clause";
        $update_rst=$con->execute($sql);
        if (!$update_rst) {db_error_handler($con, $sql); return false; }
        $sql = $con->GetUpdateSQL($update_rst, $update_array, true, get_magic_quotes_gpc());
    }
    if (!$sql) return false;

    if ($delete_participants) {
        $activity_participants=get_activity_participants($con, $activity_id);
        if ($activity_participants) {
            foreach ($activity_participants as $participant_info) {
                $ret=delete_activity_participant($con, $participant_info['activity_participant_id'], $delete_from_database);
            }
        }
    }
    $rst=$con->execute($sql);
    if (!$rst) {db_error_handler($con, $sql); return false; }
    return true;
}

/**********************************************************************/
/**
 *
 * Defines participant positions in the database, based on activity type
 * Allows duplicates
 *
 * @param adodbconnection $con handle to the database
 * @param integer $activity_type_id with integer of activity type to add a participant position for or 'null' for global
 * @param string $activity_participant_position_name with name of new activity participant position
 *
 * @return integer $participant_position_id with ID of newly created activity participant position
 */
function add_participant_position($con, $activity_type_id=false, $activity_participant_position_name=false, $magic_quotes=false) {
    if ((!$activity_type_id) AND ($activity_type_id!==false)) { echo "MISSING activity_type_id $activity_type_id"; return false; }
    if (!$activity_participant_position_name) return false;

    $add['activity_type_id']=$activity_type_id;
    $add['participant_position_name']=$activity_participant_position_name;
    if ($activity_type_id===NULL OR $activity_type_id=='null') {
        $add['activity_type_id']=NULL;
        $add['global_flag']=1;
    }
    $table="activity_participant_positions";
//    print_r($add);
    $insql=$con->GetInsertSQL($table, $add, $magic_quotes);
    if ($insql) {
        $rst=$con->Execute($insql);
        if (!$rst) { db_error_handler($con, $insql); return false; }
        $new_position=$con->Insert_ID();
        return $new_position;
    } else return false;
}

/**********************************************************************/
/**
 *
 * Retrieves matching participant positions through criteria passed in
 * Result set is associative array keyed by db identifier for activity participant position
 *
 * @param adodbconnection $con handle to the database
 * @param string $activity_participant_position_name optionally specifying the name of the position
 * @param integer $activity_type_id optionally specifying the type of activity to find positions for
 * @param integer $activity_participant_position_id optionally specifying the database identifier for the desired position
 * @param boolean $show_globals indicating whether or not to include global positions in search (defaults to true, include them)
 *
 * @return array of participant position records, associative arrays keyed by fieldname
 */
function get_activity_participant_positions($con, $activity_participant_position_name=false, $activity_type_id=false, $activity_participant_position_id=false, $show_globals=true) {
    $sql = "SELECT * from activity_participant_positions";
    $where=array();
    if ($activity_participant_position_name) {$where[]= "participant_position_name=".$con->qstr($activity_participant_position_name, get_magic_quotes_gpc()); }
    if ($activity_type_id) {
        if ($show_globals) { $where[]= "((activity_type_id=$activity_type_id) OR global_flag=1)"; }
        else {$where[]= "(activity_type_id=$activity_type_id)";}
    }
    if ( $activity_participant_position_id) { $where[]=" activity_participant_position_id= $activity_participant_position_id"; }
    if (count($where)>0) {
        $wherestr=implode(" AND ", $where);
    } else $wherestr=false;
    if ($wherestr) $sql.=" WHERE $wherestr";

    $rst = $con->execute($sql);

    if (!$rst) { db_error_handler($con, $sql);  return false; }

    $ret=array();
    while (!$rst->EOF) {
        $ret[$rst->fields['activity_participant_position_id']]=$rst->fields;
        $rst->movenext();
    }
    if (count($ret)>0) {
        return $ret;
    } else return false;

}

/**********************************************************************/
/**
 *
 * Adds contacts to activities as participants, with particular positions
 *
 * @param adodbconnection $con handle to the database
 * @param integer $activity_id with database identifier of activity to add participant to
 * @param integer $contact_id with database identifier of contact to add to activity
 * @param integer $activity_participant_position_id optionally providing position of contact in activity (defaults to 1, Participant)
 *
 * @return integer $activity_participant_id with ID of newly created activity participant
 */
function add_activity_participant($con, $activity_id, $contact_id, $activity_participant_position_id=false) {
    if (!$activity_participant_position_id) $activity_participant_position_id=1;
        if (!$contact_id) {  return false; }
        if (!$activity_id) { return false; }

        $current_participants=get_activity_participants($con, $activity_id, $contact_id, $activity_participant_position_id, false);
        if ($current_participants) {
            $participant=current($current_participants);
            if ($participant['ap_record_status']=='d') {
                //participant already exists, is deleted, so update to active
                $update_sql = "UPDATE activity_participants SET ap_record_status=" . $con->qstr('a',get_magic_quotes_gpc()) . " WHERE activity_participant_id={$participant['activity_participant_id']}";
                $update_rst=$con->execute($update_sql);
                if (!$update_rst) { db_error_handler($con, $update_sql); return false; }
                else return $participant['activity_participant_id'];
            } else {
                //participant already exists and is active, fail
                return false;
            }
        }
        //no activity participant found matching, so add new one

    $activity_participant['activity_id']=$activity_id;
    $activity_participant['contact_id']=$contact_id;
    $activity_participant['activity_participant_position_id']=$activity_participant_position_id;

        $table="activity_participants";
    $insert_sql = $con->getInsertSQL($table, $activity_participant);
    if ($insert_sql) {
        $insert_rst=$con->execute($insert_sql);
        if (!$insert_rst) { db_error_handler($con, $insert_sql); return false; }
        $activity_participant_id=$con->Insert_ID();
        return $activity_participant_id;
    }
    return false;
}

/**********************************************************************/
/**
 *
 * Gets a list of contacts who participated in an activity
 *
 * @param adodbconnection $con handle to the database
 * @param integer $activity_id optionally specifiying database identifier of activity
 * @param integer $contact_id optionally specifying database identifier of contact
 * @param integer $activity_participant_position_id optionally providing position of contact in activity
 * @param boolean $show_active specifying if search should be limited to active records (defaults to true)
 *
 * @return array of activity participant records, associative array keyed by fieldname
 */
function get_activity_participants($con, $activity_id, $contact_id=false, $activity_participant_position_id=false, $show_active=true) {
    $where=array();
    $where['activity_id']=$activity_id;
    if ($contact_id) {
        $where['contact_id']=$contact_id;
    }
    if ($activity_participant_position_id) {
        $where['activity_participant_position_id']=$activity_participant_position_id;
    }
    if ($show_active) {
        $where['ap_record_status']='a';
    }

    $tablename="activity_participants";
    $wherestr=make_where_string($con, $where, $tablename);
    $name_to_get = $con->Concat(implode(", ' ' , ", table_name('contacts')));
    $sql = "SELECT $tablename.*, activity_participant_positions.participant_position_name, $name_to_get as contact_name FROM $tablename LEFT OUTER JOIN activity_participant_positions ON $tablename.activity_participant_position_id=activity_participant_positions.activity_participant_position_id LEFT OUTER JOIN contacts on contacts.contact_id=$tablename.contact_id";
    if ($wherestr) $sql .= " WHERE $wherestr";
    $rst=$con->execute($sql);
    if (!$rst) { db_error_handler($con, $sql); return false; }
    else {
        if (!$rst->EOF) {
            $ret=array();
            while (!$rst->EOF) {
                $ret[]=$rst->fields;
                $rst->movenext();
            }
            if (count($ret)>0) {
                return $ret;
            }
        }
    }
    return false;
}

/**********************************************************************/
/**
 *
 * Removes a contact from the list of activity participants
 *
 * @param adodbconnection $con handle to the database
 * @param integer $activity_participant_id with database identifier of activity participant
 * @param boolean $delete_from_database specifying if record should be deleted or simply marked as removed
 *
 * @return boolean indicating success of delete operation
 */
function delete_activity_participant($con, $activity_participant_id, $delete_from_database=false) {
    if (!$activity_participant_id) return false;
    $tablename="activity_participants";
    $wherestr = " activity_participant_id=$activity_participant_id";
    if ($delete_from_database) {
        $sql = "DELETE FROM $tablename WHERE $wherestr";
    } else {
        $sql = "UPDATE $tablename SET ap_record_status=".$con->qstr('d',get_magic_quotes_gpc());
        $sql .= " WHERE $wherestr";
    }
    $result_rst=$con->execute($sql);
    if (!$result_rst) { db_error_handler($con, $sql); return false; }
    else return true;
}

/**
 * Function to retrieve data about an activity type
 *
 * @param adodbconnection $con with handle to the database
 * @param string $short_name with short identifier to search for activity type
 * @param string $pretty_name with descriptive identifier for activity type
 * @param integer $type_id with integer identifier for the activity type
 * @param array with data about activity type, or false if not found
**/
function get_activity_type($con, $short_name=false, $pretty_name=false, $type_id=false) {
    if (!$short_name AND !$pretty_name AND !$type_id) return false;
    $sql = "SELECT * FROM activity_types";
    if ($short_name) {
        $where[]="activity_type_short_name LIKE ".$con->qstr($short_name, get_magic_quotes_gpc());
    }
    if ($pretty_name) {
        $where[]="activity_type_pretty_name LIKE ".$con->qstr($short_name, get_magic_quotes_gpc());
    }
    if ($type_id) {
        $where[]="activity_type_id=$type_id";
    }
    $wherestr=implode(" AND ", $where);
    $sql .=" WHERE $wherestr";
    $rst=$con->execute($sql);
    if (!$rst) { db_error_handler($con, $sql); return false; }

    if (!$rst->EOF) return $rst->fields;

    return false;
}
/**
 * Function to add to add an activity type to the system, based on parameters
 *
 * @param $con handle to the database
 * @param $short_name with short string identifier for activity type.  This will be unique, ideally
 * @param $pretty_name with descriptive name for the activity type
 * @param $pretty_plural with plural version of the descriptive name
 * @param $display_html with HTML to display for the activity type name
 * @param $score_adjustment optionally providing a score adjustment associated with this activity type (defaults to 0)
 * @param $sort_order optionally providing a sort order entry for this activity type.  Defaults to 1, or place with top activity types
 * @param $user_editable optionally specifying if the activity type can be edited by the administrator or is a locked system activity type (defaults to true, administrators may edit this activity type)
 * @return integer $activity_type_id with existing or newly added activity type, or false for error
**/
function add_activity_type($con, $short_name, $pretty_name, $pretty_plural, $display_html, $score_adjustment=0, $sort_order=1, $user_editable=true, $magic_quotes=false) {
    if (!$con) return false;
    if (!$short_name) return false;
    $type = get_activity_type($con, $short_name);
    //if there's an existing record, use it
    if ($type) return $type['activity_type_id'];

    $rec=array();
    $rec['activity_type_short_name']=$short_name;
    $rec['activity_type_record_status']='a';

    if ($user_editable) $rec['user_editable_flag']=1;
    else $rec['user_editable_flag']=0;

    if (!$sort_order) $sort_order=1;
    $rec['sort_order']=$sort_order;

    if (!$pretty_name) $pretty_name=$short_name;
    if (!$pretty_plural) $pretty_plural=$pretty_name;
    if (!$display_html) $display_html = $pretty_name;

    $rec['activity_type_pretty_name']=$pretty_name;
    $rec['activity_type_pretty_plural']=$pretty_plural;
    $rec['activity_type_score_adjustment']=$score_adjustment;
    $rec['activity_type_display_html']=$display_html;

    $table="activity_types";
    $ins = $con->getInsertSQL($table, $rec, $magic_quotes);
    if ($ins) {
        $rst=$con->execute($ins);
        if (!$rst) { db_error_handler($con, $ins); }
        $activity_type_id=$con->Insert_ID();
        return $activity_type_id;
    }
}

/**
 * Function to install the default participant positions into the database, including string definition for translation.
 * Checks to ensure that positions are not already installed, so can be run safely more than once on a database
 *
 * @param adodbconnection $con with handle to the database where the types should be installed
**/
function install_default_activity_participant_positions($con) {
    //set these variables in order to allow localization of these strings.  New positions should also be added in this manner
    $s=_("Caller");
    $s=_("To");
    $s=_("From");
    $s=_("CC");
    $s=_("BCC");
    $s=_("Organizer");

    $default_activity_positions=array(
                                                        'CTO' => array( 'Caller' ),
                                                        'CFR' => array('Caller' ),
                                                        'ETO' => array('To','From','CC','BCC'),
                                                        'EFR' => array('To','From','CC','BCC'),
                                                        'FTO' => array ('To','From'),
                                                        'FFR' => array ('To','From'),
                                                        'LTT' => array ('To','From'),
                                                        'LTF' => array ('To','From'),
                                                        'MTG' => array('Organizer')
                                                        );
    foreach($default_activity_positions as $type_short_name=>$default_positions) {
        $activity_type=get_activity_type($con, $type_short_name);
        if ($activity_type) {
            $activity_type_id=$activity_type['activity_type_id'];
            $positions = get_activity_participant_positions($con, false, $activity_type_id);
            if ($positions) {
                $existing_positions=array();
                foreach ($positions as $pos_data) {
                    if ($pos_data['global_flag']!=1) {
                        $existing_positions[]=$pos_data['participant_position_name'];
                    }
                }
                $new_positions=array_diff($default_positions, $existing_positions);
                foreach ($new_positions as $position_name) {
                    add_participant_position($con, $activity_type_id, $position_name);
                }
            }
        }
    }

}

/**
 * Function to retrieve a user_id which can be said to be the least busy user holding the specified role.
 * Least busy is defined as the least number of open activities before the due date specified
 * Every user holding the specified role in the ACL will be examined
 *
 * @param adodbconnection $con with handle to the database where the ACL tables are stored
 * @param integer $role_id with identifier for role which users should hold
 * @param date $due_date with date to cut off activities at, for the purpose of counting open activities
 * @return $user_id with user identifier who is least busy, or false for failure
**/
function get_least_busy_user_in_role($con, $role_id, $due_date=false) {
    global $session_user_id;
    //hack to return the current user if no role was specified
    if (!$role_id) return false;
    if (!$due_date) $due_date=time();
    $users_in_role = get_users_in_role($con, $role_id);
    if (!$users_in_role) return false;
    $user_counts=array();

    foreach ($users_in_role as $user_id) {
        $rec['user_id']=$user_id;
        $rec['activity_status']='o';
        $rec['due_before']=$due_date;
        $activity_list=get_activity($con, $rec);
        $user_counts[$user_id]=count($activity_list);
    }
    asort($user_counts);
    $users=array_keys($user_counts);
    $lower_user=current($users);
    return $lower_user;
}

 /**
  * $Log: utils-activities.php,v $
  * Revision 1.30  2006/07/12 03:38:26  vanmer
  * - ensure that delete of participant doesn't remove final $ret value
  *
  * Revision 1.29  2006/06/21 15:46:59  jswalter
  *  - address_id was being defined twice (from activitivies and company tables) in 'get_activity()' SQL, therfore the later value was used. address_id from the activities table is now defined as 'activities_address_id'
  *
  * Revision 1.28  2006/05/06 09:29:27  vanmer
  * - added hook functions to activities API from new-2 and edit-2 activities pages
  * - added call to run workflow activity completed code for workflow engine
  *
  * Revision 1.27  2006/05/02 00:41:18  vanmer
  * - moved recurrence lookup back into activities/one.php
  * - changed get_ call for activities to do outer joins on all non-critical tables
  *
  * Revision 1.26  2006/05/01 19:34:32  braverock
  * - handle either an array or an integer as inputs
  *
  * Revision 1.25  2006/04/28 16:37:12  braverock
  * - update get_activity() fn to retrieve all fields required by the UI
  * - add check in get_activity()  to make sure we have a record, and not just an empty result set
  * - add standardized processing of entered,modified,completed fields and usernames to get_activity()
  * - add lookup for activity recurrence in get_activity()
  *
  * Revision 1.24  2006/04/05 00:44:10  vanmer
  * - added magic quote parameter to all activities functions which call getUpdateSQL or getInsertSQL
  *
  * Revision 1.23  2006/02/21 01:59:03  vanmer
  * - changed to ensure that activities with no end date are set to end date same as start date, to fix strtotime error
  *
  * Revision 1.22  2005/12/02 00:47:37  vanmer
  * - added more PHPDoc comments
  * - added XRMS_API package tag
  *
  * Revision 1.21  2005/09/21 19:59:57  vanmer
  * - added address_id to add_activity function, to allow location to be set on an activity
  *
  * Revision 1.20  2005/09/09 00:40:53  vanmer
  * - added function for adding activity types to XRMS
  *
  * Revision 1.19  2005/07/20 22:20:17  jswalter
  *  - seems that the "GetInsertSQL" was not behaving properly. It nw handles quotes properly
  *
  * Revision 1.18  2005/07/08 02:35:39  vanmer
  * - changed to return false instead of session_user_id when failed to find least available user for a role
  *
  * Revision 1.17  2005/07/07 20:56:57  vanmer
  * - added extra search when building activity where clause, to include extra parameters that are not fields
  * - added function to determine which user in a role is the least busy
  *
  * Revision 1.16  2005/07/06 21:49:29  vanmer
  * - now track which template an activity was spawned from
  *
  * Revision 1.15  2005/07/06 16:00:44  daturaarutad
  * change add_activity requirements to need an activity type and a company_id OR an on_what relationship
  *
  * Revision 1.14  2005/06/30 21:40:32  daturaarutad
  * add thread_id and followup_from_id to add_activity
  *
  * Revision 1.13  2005/06/30 04:37:57  vanmer
  * - altered to properly handle changes in contact_id => change in activity_participant
  * - altered to audit on update, when either activity or participant changes
  *
  * Revision 1.12  2005/06/29 18:41:39  vanmer
  * - added get_magic_quotes_gpc call to update sql to allow for single quotes within strings
  *
  * Revision 1.11  2005/06/24 18:46:08  daturaarutad
  * add activity_recurrence_id to list of activity fields in add_activity
  *
  * Revision 1.10  2005/06/22 19:44:18  vanmer
  * - fixed incorrect fieldname in participant position lookup
  *
  * Revision 1.9  2005/06/22 17:43:46  jswalter
  *  - heavly modified 'add_activity()' to make it more "encapsulated"
  *
  * Revision 1.8  2005/06/17 00:04:23  vanmer
  * - added new function to install the default participant positions for the default activity types
  *
  * Revision 1.7  2005/06/03 16:40:09  daturaarutad
  * added delete_activities (plural)
  *
  * Revision 1.6  2005/05/25 05:35:53  vanmer
  * - added update so that if activity is completed, completed_by is automatically set
  *
  * Revision 1.5  2005/05/06 20:50:43  vanmer
  * - added function for fetching activity types
  *
  * Revision 1.4  2005/05/06 00:43:16  vanmer
  * - fixed misnamed field when adding a new activity without any participants specified
  *
  * Revision 1.3  2005/04/23 17:49:25  vanmer
  * - changed activity_participant_record_status to ap_record_status to work around 30 character limit in mssql adodb driver
  *
  * Revision 1.2  2005/04/15 08:02:53  vanmer
  * - added flag to control delete of participants when activity is deleted through API
  * - added logic for allowing contact change in activity update code to update default participant
  *
  * Revision 1.1  2005/04/15 07:33:49  vanmer
  * - Initial revision of API for managing activities, participants, and participant positions
  *
  *
**/
?>