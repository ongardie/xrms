<?php
/**
 * Utility functions for manipulating activities
 *
 * These functions create, retrieve, delete and modify contacts
 * This file should be included anywhere contacts need to be created or modified
 *
 * @author Aaron van Meerten
 *
 * $Id: utils-contacts.php,v 1.3 2005/11/23 17:34:21 jswalter Exp $
 *
 */


/**********************************************************************/
/**
 *
 * Adds a contact to the system, based on array of data about the contact
 * Runs hook functions and adds audit items when complete
 *
 * @param adodbconnection $con with handle to the database
 * @param array $contact with data about the contact, to add
 *
 * @return $contact_id with newly created contact, or false if failure occured
*/
function add_contact($con, $contact) {
    global $session_user_id;

    /* NULL AVOIDING UNTIL THESE FIELDS ARE MARKED AS ALLOWED TO BE NULL IN THE DATABASE */
    //avoid nulls on the custom1-4 fields
    $contact['custom1'] = array_key_exists('custom1',$contact) ? $contact['custom1'] : "";
    $contact['custom2'] = array_key_exists('custom2',$contact) ? $contact['custom2'] : "";
    $contact['custom3'] = array_key_exists('custom3',$contact) ? $contact['custom3'] : "";
    $contact['custom4'] = array_key_exists('custom4',$contact) ? $contact['custom4'] : "";

    //avoid nulls on the IM fields, although these should be moved to a plugin
    $contact['aol_name']   = array_key_exists('aol_name',$contact) ? $contact['aol_name'] : "";
    $contact['yahoo_name'] = array_key_exists('yahoo_name',$contact) ? $contact['yahoo_name'] : "";
    $contact['msn_name']   = array_key_exists('msn_name',$contact) ? $contact['msn_name'] : "";



    //set contact defaults
    $now=time();
    $contact['entered_at']=$now;
    $contact['last_modified_at']=$now;
    $contact['entered_by']=$session_user_id;
    $contact['last_modified_by']=$session_user_id;
    $contact['contact_record_status']='a';
    if (!$contact['home_address_id']) $contact['home_address_id']=1;


    $contact['last_name'] = (strlen($contact['last_name']) > 0) ? $contact['last_name'] : "[last name]";
    $contact['first_names'] = (strlen($contact['first_names']) > 0) ? $contact['first_names'] : "[first names]";
    // If salutation is 0, make sure you replace it with an empty string
    if(!$contact['salutation']) {
        $contact['salutation'] = "";
    }

    /** CLEAN INCOMING DATA FIELDS ***/
    $contact_phone_fields=array('work_phone','cell_phone','home_phone','fax');
    $phone_clean_count=clean_phone_fields($contact, $contact_phone_fields);

    $tbl='contacts';
    $ins = $con->GetInsertSQL($tbl, $contact, get_magic_quotes_gpc());
    if ($ins) {
        $rst = $con->execute($ins);
        if ($rst) {
            $contact_id = $con->Insert_ID();
            $rec['contact_id']=$contact_id;

            add_audit_item($con, $session_user_id, 'created', 'contacts', $contact_id, 1);

            //add to recently viewed list
            update_recent_items($con, $session_user_id, "contacts", $contact_id);

            do_hook_function('contact_new_2', $rec);
            return $contact_id;
        } else {
            db_error_handler($con, $ins);
            return false;
        }
    } else return false;
}

/**********************************************************************/
/**
 *
 * Searches for a contact based on data about the contact
 *
 * @param adodbconnection $con with handle to the database
 * @param array $contact_data with fields to search for
 * @param boolean $show_deleted specifying if deleted contacts should be included (defaults to false)
 * @param boolean $return_recordset indicating if adodb recordset object should be returned (defaults to false)
 *
 * @return array $results with either an array of contact records, or a recordset object (false on failure)
*/
function find_contact($con, $contact_data, $show_deleted=false, $return_recordset=false) {
    $sql = "SELECT * FROM contacts";

    if (array_key_exists('contact_id',$contact_data) AND trim($contact_data['contact_id'])) {
        $contact= get_contact($con, $contact_id, $return_recordset);
        if ($contact AND is_array($contact)) return array($contact);
        else return $contact;
    } else {

        $extra_where=array();
        foreach ($contact_data as $ckey=>$cval) {
            switch ($ckey) {
                case 'email':
                case 'title':
                case 'last_name':
                case 'first_names':
                case 'description':
                    unset($contact_data[$ckey]);
                    $extra_where[]="$ckey LIKE ".$con->qstr($cval);
                break;
            }
        }
        if (!$show_deleted) $contact_data['contact_record_status']='a';

        /** CLEAN INCOMING DATA FIELDS ***/
        $contact_phone_fields=array('work_phone','cell_phone','home_phone','fax');
        $phone_clean_count=clean_phone_fields($contact_data, $contact_phone_fields);

        if (count($extra_where)==0) $extra_where=false;
        $wherestr=make_where_string($con, $contact_data, $tablename, $extra_where);
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
 * Gets a contact based on the database identifer if that contact
 *
 * @param adodbconnection $con with handle to the database
 * @param integer $contact_id with ID of the contact to get details about
 * @param boolean $return_recordset indicating if adodb recordset object should be returned (defaults to false)
 *
 * @return array $results with either an array of contact fields, or a recordset object (false on failure)
*/
function get_contact($con, $contact_id, $return_rst=false) {
    if (!$contact_id) return false;
    $sql = "SELECT * FROM contacts WHERE contact_id=$contact_id";
    $rst = $con->execute($sql);
    if (!$rst) { db_error_handler($con, $sql); return false; }
    else {
        if ($return_rst) {
            return $rst;
       } else return $rst->fields;
    }
    //shouldn't ever get here
    return false;
}

/**********************************************************************/
/**
 *
 * Updates an contact in XRMS from an associative array
 * Either an contact_id must be explicitly set or an adodbrecordset for the record to be updated
 * must be passed in or the function will fail
 *
 * @param adodbconnection $con handle to the database
 * @param array $contact_data with associative array defining contact data to update
 * @param integer $contact_id optionally identifying contact in the database (required if not passing in a ecordset to $contact_rst)
 * @param adodbrecordset $contact_rst optionally providing a recordset to use for the update (required if not passing in an integer for $contact_id)
 *
 * @return boolean specifying if update succeeded
 */
function update_contact($con, $contact, $contact_id=false, $contact_rst=false) {
    global $session_user_id;

    if (!$contact) return false;
    if (!$contact_rst) {
        $contact_rst=get_contact($con, $contact_id, true);
    }
    if (!$contact_rst) return false;

    /** CLEAN INCOMING DATA FIELDS ***/
    $contact_phone_fields=array('work_phone','cell_phone','home_phone','fax');
    $phone_clean_count=clean_phone_fields($contact, $contact_phone_fields);

    $rec['last_modified_at'] = time();
    $rec['last_modified_by'] = $session_user_id;


    $upd = $con->GetUpdateSQL($contact_rst, $contact, false, get_magic_quotes_gpc());
    if ($upd) {
        $rst=$con->execute($upd);
        if (!$rst) { db_error_handler($con, $upd); return false; }
    }


    //this will run whether or not base contact changed
    $param = array($contact_rst, $contact);
    do_hook_function('contact_edit_2', $param);

    add_audit_item($con, $session_user_id, 'updated', 'contacts', $contact_id, 1);

    return true;
}

/**********************************************************************/
/**
 *
 * Deletes an contact from XRMS, based on passed in contact_id
 * Can delete contact from database or mark as removed using record status
 *
 * @param adodbconnection $con handle to the database
 * @param integer $contact_id identifying which contact to delete
 * @param boolean $delete_from_database specifying if contact should be deleted from the database, or simply marked with a deleted flag (defaults to false, mark with deleted flag)
 *
 * @return boolean indicating success of delete operation
 */
function delete_contact($con, $contact_id, $delete_from_database=false) {
    if (!$contact_id) return false;
    if ($delete_from_database) {
        $sql = "DELETE FROM contacts";
    } else {
        $sql = "UPDATE contacts SET contact_record_status=" . $con->qstr('d');
    }
    $sql .= "  WHERE contact_id=$contact_id";

    $rst=$con->execute($sql);
    if (!$rst) { db_error_handler($con, $sql); return false; }

    return true;
}

 /**
 * $Log: utils-contacts.php,v $
 * Revision 1.3  2005/11/23 17:34:21  jswalter
 *  - moved 'clean_phone_fields()' to "utils-misc.php"
 *
 * Revision 1.2  2005/11/18 20:34:38  vanmer
 * - changed to updated contact modified by/time for update_contact
 *
 * Revision 1.1  2005/11/18 20:04:48  vanmer
 * -Initial revision of an API for managing contacts in XRMS
 *
**/
 ?>