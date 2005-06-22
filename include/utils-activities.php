<?php
/**
 * Utility functions for manipulating activities
 *
 * These functions create, retrieve, delete and modify activities, activity participants,
 * and the possibly positions a participant can serve within an activity type
 * This file should be included anywhere activities need to be created or modified
 *
 * @author Aaron van Meerten
 *
 * $Id: utils-activities.php,v 1.9 2005/06/22 17:43:46 jswalter Exp $

 */

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
function add_activity($con, $activity_data, $participants=false)
{

    // Right off the bat, if these are not set, we can't do anything!
    if ( (! $con)  ||  (! $activity_data ) )
        return false;

    //save to database
    global $session_user_id;

    //Turn activity_data array into variables
    extract($activity_data);

    // Now check these, we need these as well
    if ( (! $activity_type_id) || (! $company_id) )
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
    $rec['ends_at']          = ($scheduled_at > $ends_at) ? $rec['scheduled_at'] : strtotime($ends_at);

    // If this is not defined, then pull it from $session_user_id
    $rec['user_id']          = ($user_id)        ? $user_id        : $session_user_id;

    // A 'title' for this activity for future reference and review
    $rec['activity_title']   = ($activity_title) ? $activity_title : _("[none]");

    // A brief description of the activity for future reference and review
    $rec['activity_description']  = ($activity_description) ? $activity_description : '';

    // These values, if not defined, will be set by default values defined within the Database
    // Therefore they do not need to be created within this array for RECORD insertion
    if ($activity_status)      { $rec['activity_status']      = $activity_status; }
    if ($on_what_status > 0)   { $rec['on_what_status']       = $on_what_status; }
    if ($completed_at)         { $rec['completed_at']         = $completed_at; }
    if ($on_what_table)        { $rec['on_what_table']        = $on_what_table; }
    if ($on_what_id > 0)       { $rec['on_what_id']           = $on_what_id; }
    if ($company_id > 0)       { $rec['company_id']           = $company_id; }
    if ($contact_id > 0)       { $rec['contact_id']           = $contact_id; }

    $tbl = 'activities';
    $ins = $con->GetInsertSQL($tbl, $rec, get_magic_quotes_gpc());
    $rst=$con->execute($ins);

    if (!$rst) { db_error_handler($con, $ins); return false; }
    $activity_id = $con->insert_id();

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

        $sql = "select a.*, addr.*, c.company_id, c.company_name, cont.first_names, cont.last_name
        from activities a
        left join contacts cont on a.contact_id = cont.contact_id
        join companies c ON c.company_id = a.company_id
        left join addresses addr ON addr.address_id = c.default_primary_address";

        $where=array();
        if (!$show_deleted) $activity_data['activity_record_status']='a';
        $tablename='a';
    if (array_key_exists('activity_id',$activity_data) AND trim($activity_data['activity_id'])) {
        $where['activity_id'] = $activity_data['activity_id'];
        $wherestr=make_where_string($con, $where, $tablename);
    } else {
        $wherestr=make_where_string($con, $activity_data, $tablename);
    }
    if ($wherestr) $sql.=" WHERE $wherestr";

    $rst = $con->execute($sql);
    if (!$rst) { db_error_handler($con, $sql); return false; }
    if ($rst->EOF) return false;
    else {
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
function update_activity($con, $activity_data, $activity_id=false, $activity_rst=false, $update_default_participant=true) {
    if (!$activity_id AND !$activity_rst) return false;
    if (!$activity_data) return false;
    if (!$activity_rst) {
        $sql = "SELECT * FROM activities WHERE activity_id=$activity_id";
        $activity_rst=$con->execute($sql);
        if (!$activity_rst) { db_error_handler($con, $activity_sql); return false; }
    }
        if (!$activity_id) $activity_id=$activity_rst->fields['activity_id'];

        if ($update_default_participant) {
            if ($activity_data['contact_id']) {
                if ($activity_data['contact_id']!=$activity_rst->fields['contact_id']) {
                //contact changed, change default participant
                if ($activity_rst->fields['contact_id']) {
                    $activity_participant=get_activity_participants($con, $activity_id, $activity_rst->fields['contact_id'], 1);
                    if ($activity_participant) {
                        //get existing default participant, mark it as removed
                        $participant_data=current($activity_participant);
                        $activity_participant_id=$participant_data['activity_participant_id'];
                        $ret=delete_activity_participant($con, $activity_participant_id);
                    }
                }
               }
            }
            if ($activity_data['contact_id'] AND $activity_data['contact_id']!='NULL') {
                //new contact for activity is not blank, so add it as the new default participant
                $activity_participant_id=add_activity_participant($con, $activity_id, $activity_data['contact_id'], 1);
            }
        }
        if (($activity_data['activity_status']=='c') AND ($activity_rst->fields['activity_status']!='c')) {
            $activity_data['completed_by']=$_SESSION['session_user_id'];
            $activity_data['completed_at']=time();
        }
        if (($activity_data['activity_status']!='c') AND ($activity_rst->fields['activity_status']=='c')) {
            $activity_data['completed_by']='NULL';
            $activity_data['completed_at']='NULL';
        }
    $update_sql = $con->getUpdateSQL($activity_rst, $activity_data);
    if ($update_sql) {
        $update_rst=$con->execute($update_sql);
        if (!$update_rst) { db_error_handler($con, $update_sql); return false; }
        return true;
    } else return true;
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
function add_participant_position($con, $activity_type_id=false, $activity_participant_position_name=false) {
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
    $insql=$con->GetInsertSQL($table, $add);
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
    if ($activity_participant_position_name) {$where[]= "activity_participant_position_name=".$con->qstr($activity_participant_position_name, get_magic_quotes_gpc()); }
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
  * $Log: utils-activities.php,v $
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