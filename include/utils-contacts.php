<?php
/**
 * Utility functions for manipulating activities
 *
 * These functions create, retrieve, delete and modify contacts
 * This file should be included anywhere contacts need to be created or modified
 *
 * @author Aaron van Meerten
 * @package XRMS_API
 *
 * $Id: utils-contacts.php,v 1.25 2006/06/15 21:33:46 vanmer Exp $
 *
 */

require_once($include_directory.'utils-companies.php');
require_once($include_directory.'utils-typestatus.php');

/**********************************************************************/
/**
 *
 * Adds or modifies a contact within XRMS, based on array of data about the contact
 *
 * Define this field if the record should be updated, otherwise leave out of array
 * - contact_id              - Contact ID, once a contact record is created
 *
 * These 'contacts' tables fields are required.
 * This method will fail without them.
 * - company_id              - Company this person belongs to
 * - address_id              - Which address to use for this person
 * - home_address_id         - This persons home address
 * - last_name               - Last Name
 * - first_names             - First Name
 * - email                   - Contacts eMail Address
 *
 * These fields are optional, some may be derived from other fields if not defined.
 * - user_id                 - "Account Owner" of contact data, Defaults to who created the record
 * - division_id             - Division with the company
 * - salutation              - Salutation for Addressing
 * - gender                  - Contact Gender
 * - date_of_birth           - Contacts Birth Date
 * - summary                 -
 * - title                   - Title within Company
 * - description             -
 * - work_phone              - Contacts Work Phone Number
 * - work_phone_ext          - Contacts Work Number Extention
 * - cell_phone              - Contacts Cellphone Number
 * - home_phone              - Contacts Home Phone Number
 * - fax                     - Contacts FAX Number
 * - tax_id                  - Contacts SSN/TIN
 * - aol_name                - America Online IM "handle" name
 * - yahoo_name              - Yahoo IM "handle" name
 * - msn_name                - MSN IM "handle" name
 * - interests               -
 * - profile                 -
 * - custom1                 - Custom Field #1
 * - custom2                 - Custom Field #2
 * - custom3                 - Custom Field #3
 * - custom4                 - Custom Field #4
 * - extref1                 - External Reference Field #1
 * - extref2                 - External Reference Field #2
 * - extref3                 - External Reference Field #3
 *
 * Do not define these fields, they are auto-defined
 * - entered_at              - when was record created
 * - entered_by              - who created the record
 * - last_modified_at        - when was record modified - this will be the same as 'entered_at'
 * - last_modified_by        - who modified the record  - this will be the same as 'entered_by'
 * - contact_record_status   - the database defaults this to [a] Active
 * - email_status            - the database defaults this to [a] Active
 *
 * @param adodbconnection  $con               with handle to the database
 * @param array            $contact_info      with data about the contact, to add/update
 * @param boolean          $_return_data      F - returns record ID, T - returns record in an array
 * @param boolean          $_magic_quotes     F - inbound data is not "add slashes", T - data is "add slashes"
 *
 * @return mixed $contact_id of newly created or modified contact, record data array or false if failure occured
 */
function add_update_contact($con, $contact_info, $_return_data = false, $_magic_quotes =  false )
{
   /**
    * Default return value
    *
    * Contact ID or boolean upon failure
    * Default value is set at FALSE
    *
    * @var mixed $_retVal Indicates if Contact was created or not
    * @access private
    * @static
    */
    $_retVal = false;

    global $session_user_id;

    // If there is not a 'company_id', one needs to be located or created
    if ( (! $contact_info['company_id']) || ( $contact_info['company_id']) == 0 )
    {
        $undefined_company_method=get_user_preference($con, $session_user_id, 'undefined_company_method');

        if ( (! $contact_info['company_name']) && (! $contact_info['first_names']) && (! $contact_info['last_name']) )
        {
            if ($undefined_company_method=='reject') return false;

            // Since there is not info to create or derive a company name, use unknown company
            $contact_info['company_id'] = 1;
        } else {

            switch ($undefined_company_method) {
                case 'contact_name':
                    // Check here to see if the Unknown company flag is set
                    // There needs to be a company name
                    if ( ! $contact_info['company_name'] ) {
                        $contact_info['company_name'] = $contact_info['first_names'] . ' ' . $contact_info['last_name'];
                    }

                    // Retrieve company id
                    $_company_data = add_update_company ( $con, $contact_info );

                    // Pull out company_id
                    $contact_info['company_id'] = $_company_data['company_id'];
                    $contact_info['address_id'] = $_company_data['address_id'];
                break;

                case 'reject':
                    return false;
                break;

                case 'unknown':
                default:
                    $contact_info['company_id']=1;
                break;
            }
        }
    }

    // Other sub-systems can handle "personal" information. They may or may not
    // utilize other fields that the 'contacts' table don't need to deal with.
    // This array (below) will pull out only the fields we need and process them.
    // This way we make sure we hae the data we need, and only that data.
    $contact_data = pull_contact_fields ( $contact_info );

    /* CLEAN INCOMING DATA FIELDS */
    // make sure the phone numbers are in a format we can deal with
    $contact_phone_fields = array('work_phone','cell_phone','home_phone','fax');
    $phone_clean_count    = clean_phone_fields($contact_info, $contact_phone_fields);

    // If 'field' this exists, but has no data, remove it
    if (strlen($contact_data['user_id']) == 0)
        unset ( $contact_data['user_id'] );

    if (strlen($contact_data['contact_id']) == 0)
        unset ( $contact_data['contact_id'] );

    // Prep array for "search", only on these fields
    $extra_where = array();
    foreach ($contact_data as $_field => $_value) {
        switch ($_field) {
            case 'contact_id':
            case 'email':
            case 'last_name':
            case 'first_names':
                $extra_where[$_field] = $_value;
            case 'company_id':
                if ($_value>1) {
                    //add company_id to the WHERE clause search list if we have a non-1 id
                    $extra_where[$_field] = $_value;
                }
            break;
        }
    }

    $_table_name = 'contacts';

    // Determine if this contact already exists
    $found_contact_data = __record_find ( $con, $_table_name, $extra_where, 'AND', $_magic_quotes );

    // What's the primary key for this data set
    $_primay_key = $found_contact_data['primarykey'];

    // If this contact exists already
    if ( $found_contact_data[$_primay_key] )
    {
        // We found it, so pull record ID
        $contact_data[$_primay_key] = $found_contact_data[$_primay_key];

        // Need to clean up the data
        // "Account Owner"
        if (strlen($contact_data['user_id']) == 0)
                $contact_data['user_id']  = $found_contact_data['user_id'];

        if (strlen($contact_data['company_id']) == 0)
                $contact_data['company_id']  = $found_contact_data['company_id'];

        if (strlen($contact_data['home_address_id']) == 0)
                $contact_data['home_address_id']  = $found_contact_data['home_address_id'];

        if (strlen($contact_data['last_name']) == 0)
                $contact_data['last_name']  = $found_contact_data['last_name'];

        if (strlen($contact_data['first_names']) == 0)
                $contact_data['first_names']  = $found_contact_data['first_names'];

        // Update contact data record
        $_retVal = __record_update ( $con, $_table_name, 'contact_id', $contact_data, $_magic_quotes );

        if ($_retVal['contact_id'] == 0)
                $_retVal['contact_id']  = $contact_data['contact_id'];

//            $_retVal = $_retVal['contact_id'];
        $contact_id=$_retVal['contact_id'];
        //this will run whether or not base contact changed
        $param = array($_retVal, $contact_data);
        do_hook_function('contact_edit_2', $param);

        $audit_type = 'updated';
    } else {
        // This is a new Record
        // If a company has not been defined, AND names are not given, this can be be dealt with
        if ( ( $contact_data['company_id'] ) && ( ( $contact_data['last_name'] ) || ( $contact_data['first_names'] ) ) )
        {
            // Need to clean up the data

            // "Account Owner"
            $contact_data['user_id']          = (strlen($contact_data['user_id']) > 0)         ? $contact_data['user_id']         : $session_user_id;

            // If salutation is 0, make sure you replace it with an empty string
            $contact_data['salutation']       = (strlen($contact_data['salutation']) > 0)      ? $contact_data['salutation']      : "";

            $contact_data['last_name']        = (strlen($contact_data['last_name']) > 0)       ? $contact_data['last_name']       : _("[last name]");
            $contact_data['first_names']      = (strlen($contact_data['first_names']) > 0)     ? $contact_data['first_names']     : _("[first names]");

            // If 'gender' is not defined, define it
            if(!$contact_data['gender'])
                $contact_data['gender'] = 'u';

            $contact_array = __record_insert ( $con, 'contacts', $contact_data, $_magic_quotes, true );

            $contact_id=$contact_array['contact_id'];
            $_retVal = $contact_id;

            //add to recently viewed list
            update_recent_items($con, $session_user_id, $_table_name, $contact_id);

            $contact_data['contact_id'] = $contact_id;
            do_hook_function('contact_new_2', $contact_data);

            $audit_type = 'created';
        }
    }

    // Set audit trail
    add_audit_item($con, $session_user_id, $audit_type, $_table_name, $contact_id, 1);

    return $_retVal;
};


/**********************************************************************/
/**
 *
 * Adds a contact to the system, based on array of data about the contact
 * Runs hook functions and adds audit items when complete
 *
 * This is now just a wrapper to the new method 'add_update_contact' to
 * maintain BC with plug-ins that expect this
 *
 * @param adodbconnection  $con      with ADOdb connection Object
 * @param array            $contact  with data about the contact, to add
 * @param boolean          $magic_quotes     F - inbound data is not magic_quoted by php, T - data is magic quoted
 *
 * @depreciated
 *
 * @return $contact_id with newly created contact, or false if failure occured
 */
function add_contact($con, $contact, $magic_quotes=false)
{
    return add_update_contact($con, $contact, false, $magic_quotes);
};

/**********************************************************************/
/**
 *
 * Searches for a contact based on data about the contact
 *
 * @param adodbconnection  $con               with ADOdb connection Object
 * @param array            $contact_data      with fields to search for
 * @param boolean          $show_deleted      specifying if deleted contacts should be included (defaults to false)
 * @param boolean          $return_recordset  indicating if adodb recordset object should be returned (defaults to false)
 *
 * @return array $results with either an array of contact records, or a recordset object (false on failure)
*/
function find_contact($con, $contact_data, $show_deleted = false, $return_recordset = false)
{
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
//                case 'description':
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

};

/**********************************************************************/
/**
 * Gets a contact based on the database identifer contact_id
 *
 *
 * Return all fields required by the UI to display the contact,
 * including joins and queries on other tables.
 *
 * @param adodbconnection  $con               with ADOdb connection Object
 * @param integer          $contact_id        with ID of the contact to get details about
 * @param boolean          $return_recordset  indicating if adodb recordset object should be returned (defaults to false)
 *
 * @return array $results with either an array of contact fields, or a recordset object (false on failure)
 */
function get_contact($con, $contact_id, $return_rst = false, $include_extras=true) {
    if (!$contact_id) return false;
    $sql = "SELECT contacts.*"; 
    if ($include_extras) { 
        $sql.=",c.company_id, company_name, company_code, " .
        $con->Concat("u1.first_names", $con->qstr(' '), "u1.last_name") . " AS entered_by_username, " .
        $con->Concat("u2.first_names", $con->qstr(' '), "u2.last_name") . " AS last_modified_by_username, " .
        $con->Concat("u3.first_names", $con->qstr(' '), "u3.last_name") . " AS owner_username ";
    }
    $sql  .= " FROM contacts"; 
    if ($include_extras) {
        $sql .=" left outer join companies c ON contacts.company_id = c.company_id
                    left outer join users u1 ON contacts.entered_by = u1.user_id
                    left outer join users u2 ON contacts.last_modified_by = u2.user_id
                    left outer join users u3 ON contacts.user_id = u3.user_id";
    }
    $sql .=" WHERE contact_id = $contact_id";
    $rst = $con->execute($sql);
    if (!$rst) { db_error_handler($con, $sql); return false; }
    else { //OK, we have a result set.
        // Make sure we have a contact
        if ($rst->NumRows()) {
            //now get the statuses account_status_display_html, crm_status_display_html
            if ($include_extras) {
                $account_status_fields = get_entity_status($con, 'account', $rst->fields['account_status_id']);
                if ($account_status_fields) {
                    $rst->fields['account_status_pretty_name']  = $account_status_fields['account_status_pretty_name'];
                    $rst->fields['account_status_display_html'] = $account_status_fields['account_status_display_html'];
                }
                $crm_status_fields = get_entity_status($con, 'crm', $rst->fields['crm_status_id']);
                if ($crm_status_fields) {
                    $rst->fields['crm_status_pretty_name']      = $crm_status_fields['crm_status_pretty_name'];
                    $rst->fields['crm_status_display_html']     = $crm_status_fields['crm_status_display_html'];
                }
            }
            if ($return_rst) {
                return $rst;
            } else return $rst->fields;
        } else return false; //no contact record found
    }
    //shouldn't ever get here
    return false;
};

/**********************************************************************/
/**
 *
 * Updates an contact in XRMS from an associative array
 * Either an contact_id must be explicitly set or an adodbrecordset for the record to be updated
 * must be passed in or the function will fail
 *
 * @param adodbconnection  $con           ADOdb connection Object
 * @param array            $contact_data  with associative array defining contact data to update
 * @param integer          $contact_id    optionally identifying contact in the database (required if not passing in a recordset to $contact_rst)
 * @param adodbrecordset   $contact_rst   optionally providing a recordset to use for the update (required if not passing in an integer for $contact_id)
 * @param boolean          $magic_quotes     F - inbound data is not magic_quoted by php, T - data is magic quoted
 *
 * @return boolean specifying if update succeeded
 */
function update_contact($con, $contact, $contact_id = false, $contact_rst = false, $magic_quotes=false)
{

    global $session_user_id;

    if (!$contact) return false;
    if (!$contact_rst) {
        $contact_rst=get_contact($con, $contact_id, true, false);
    }
    if (!$contact_rst) return false;

    //** CLEAN INCOMING DATA FIELDS ***
    $contact_phone_fields=array('work_phone','cell_phone','home_phone','fax');
    $phone_clean_count=clean_phone_fields($contact, $contact_phone_fields);

    $rec['last_modified_at'] = time();
    $rec['last_modified_by'] = $session_user_id;


    $upd = $con->GetUpdateSQL($contact_rst, $contact, false, $magic_quotes);
    if ($upd) {
        $rst=$con->execute($upd);
        if (!$rst) { db_error_handler($con, $upd); return false; }
    }

    //this will run whether or not base contact changed
    $param = array($contact_rst, $contact);
    do_hook_function('contact_edit_2', $param);

    add_audit_item($con, $session_user_id, 'updated', 'contacts', $contact_id, 1);

    return true;

};

/**********************************************************************/
/**
 *
 * Deletes an contact from XRMS, based on passed in contact_id
 * Can delete contact from database or mark as removed using record status
 *
 * @param adodbconnection  $con                   ADOdb connection Object
 * @param integer          $contact_id            identifying which contact to delete
 * @param boolean          $delete_from_database  specifying if contact should be deleted from the database, or simply marked with a deleted flag (defaults to false, mark with deleted flag)
 *
 * @return boolean indicating success of delete operation
 */
function delete_contact($con, $contact_id, $delete_from_database = false)
{
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
};



/**
 *
 * Pulls only contact field data from given array
 *
 * @param array $array_data array to retrieve contact data from
 *
 * @return array $contact_fields contact "only" fields found in given array
 */
function pull_contact_fields ( $array_data )
{
    if ( ! $array_data )
        return $array_data;

    // Retrieve only the field names we can handle
    $contact_fields = array ( 'company_id'           => '',
                              'division_id'          => '',
                              'address_id'           => '',
                              'home_address_id'      => '',
                              'last_name'            => '',
                              'first_names'          => '',
                              'email'                => '',
                              'salutation'           => '',
                              'gender'               => '',
                              'date_of_birth'        => '',
                              'summary'              => '',
                              'title'                => '',
                              'description'          => '',
                              'work_phone'           => '',
                              'work_phone_ext'       => '',
                              'cell_phone'           => '',
                              'home_phone'           => '',
                              'fax'                  => '',
                              'tax_id'               => '',
                              'interests'            => '',
                              'profile'              => '',
                              'custom1'              => '',
                              'custom2'              => '',
                              'custom3'              => '',
                              'custom4'              => '',
                              'extref1'              => '',
                              'extref2'              => '',
                              'extref3'              => '',
                              'address_name'         => '',
                              'address_body'         => '',
                              'address_type'         => '',
                              'use_pretty_address'   => '',
                              'offset'               => '',
                              'daylight_savings_id'  => ''
                          );

    // Now pull out the fields we need
    return array_intersect_key_2($contact_fields, $array_data);

};

/**********************************************************************/
/** Include the misc utilities file */
include_once $include_directory . 'utils-misc.php';


/**********************************************************************/
/**********************************************************************/
 /**
 * $Log: utils-contacts.php,v $
 * Revision 1.25  2006/06/15 21:33:46  vanmer
 * - changed link from company owner to contact owner
 * - changed fieldname from account_owner to owner_username
 *
 * Revision 1.24  2006/05/02 00:45:02  vanmer
 * - changed get_contact function to left outer join all non-critical table
 * - allow contact sql to be simplified (no joins)
 * - changed update_contact to use simplified contact sql for passing recordset into getUpdateSQL
 *
 * Revision 1.23  2006/04/28 15:24:53  braverock
 * - update get_contact() fn to retrieve all fields required by the UI
 *   - use types and statuses API to retrieve account_status and crm_status information
 * - add check in get_contact()  to make sure we have a record, and not just an empty result set
 *
 * Revision 1.22  2006/04/26 02:14:50  vanmer
 * - added user preference handling for adding a new contact with no company specified
 *
 * Revision 1.21  2006/04/25 15:23:04  braverock
 * - fix switch/case in add_update_contact to include company_id
 *   in record search if we have a valid company_id
 *   fixes SF bug: https://sourceforge.net/tracker/index.php?func=detail&aid=1475671&group_id=88850&atid=588128
 * - NOTE: still lots to do on this function to make it behave/perform better, but should be safe now
 *
 * Revision 1.20  2006/04/21 22:58:33  braverock
 * - localize default first name and last name
 *
 * Revision 1.19  2006/04/11 00:42:08  vanmer
 * - added missing PHPDoc parameters
 *
 * Revision 1.18  2006/04/05 19:22:22  vanmer
 * - added needed default parameter on add_contact
 *
 * Revision 1.17  2006/04/05 00:44:59  vanmer
 * - added magic quotes parameters to all contacts functions which call getUpdateSQL or getInsertSQL
 *
 * Revision 1.16  2006/03/19 02:18:41  ongardie
 * - Allow empty salutation for new contacts.
 *
 * Revision 1.15  2006/01/13 00:00:27  vanmer
 * - removed getRelationshipID function (moved to utils-relationships)
 *
 * Revision 1.14  2006/01/12 15:42:26  jswalter
 *  - added 'getRelationshipID()' to collection
 *
 * Revision 1.13  2006/01/09 21:05:41  jswalter
 *  - if no company name or user names are given, default to '1'
 *
 * Revision 1.12  2006/01/03 21:03:18  vanmer
 * - added code to ensure that the contact_id variable is set, since it is used later in the code
 *
 * Revision 1.11  2005/12/22 22:50:36  jswalter
 *  - modified 'add_update_company()' to default to '1' for unknown contacts and company records
 *
 * Revision 1.10  2005/12/20 18:34:17  jswalter
 *  - removed 'home_address_id' assignment from add_update method
 * BUg 777
 *
 * Revision 1.9  2005/12/20 07:54:15  jswalter
 *  - completed 'add_update_contact()'
 * Bug 777
 *
 * Revision 1.8  2005/12/15 00:16:08  jswalter
 *  - added a bit more "intelligent" processing in "add_update"
 *  - created new function to retrieve only fields that are in "contacts" table
 *  - company_id is now placed in contact record
 *
 * Revision 1.7  2005/12/10 20:09:34  vanmer
 * - removed parameters to getUpdateSQL, to allow update of contact with strange characters
 *
 * Revision 1.6  2005/12/07 00:14:53  jswalter
 *  - added new method 'add_update_contact()' to replace the existing add() and update() methods
 *  - modified 'add_contact()' to be a BC wrapper for 'add_update_contact()'
 *
 * Revision 1.5  2005/12/05 21:10:46  jswalter
 *  - removed IM fields from 'add_contacts'
 *
 * Revision 1.4  2005/12/02 01:50:18  vanmer
 * - added XRMS_API package tag
 *
 * Revision 1.3  2005/11/23 17:34:21  jswalter
 *  - moved 'clean_phone_fields()' to "utils-misc.php"
 *
 * Revision 1.2  2005/11/18 20:34:38  vanmer
 * - changed to updated contact modified by/time for update_contact
 *
 * Revision 1.1  2005/11/18 20:04:48  vanmer
 * -Initial revision of an API for managing contacts in XRMS
 **/
 ?>
