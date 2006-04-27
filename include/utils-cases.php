<?php
/**
 * Utility functions for manipulating activities
 *
 * These functions create, retrieve, delete and modify cases
 * This file should be included anywhere cases need to be created or modified
 *
 * @author Aaron van Meerten
 * @package XRMS_API
 *
 * $Id: utils-cases.php,v 1.1 2006/04/27 03:24:04 vanmer Exp $
 *
 */


/**********************************************************************/
/**
 *
 * Adds or modifies a case within XRMS, based on array of data about the case
 *
 * Define this field if the record should be updated, otherwise leave out of array
 * - case_id              - Case ID, once a case record is created
 *
 * These 'cases' tables fields are required.
 * This method will fail without them.
 * - company_id              - Company this person belongs to
 * - address_id              - Which address to use for this person
 * - home_address_id         - This persons home address
 * - last_name               - Last Name
 * - first_names             - First Name
 * - email                   - Cases eMail Address
 *
 * These fields are optional, some may be derived from other fields if not defined.
 * - user_id                 - "Account Owner" of case data, Defaults to who created the record
 * - division_id             - Division with the company
 * - salutation              - Salutation for Addressing
 * - gender                  - Case Gender
 * - date_of_birth           - Cases Birth Date
 * - summary                 -
 * - title                   - Title within Company
 * - description             -
 * - work_phone              - Cases Work Phone Number
 * - work_phone_ext          - Cases Work Number Extention
 * - cell_phone              - Cases Cellphone Number
 * - home_phone              - Cases Home Phone Number
 * - fax                     - Cases FAX Number
 * - tax_id                  - Cases SSN/TIN
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
 * - case_record_status   - the database defaults this to [a] Active
 * - email_status            - the database defaults this to [a] Active
 *
 * @param adodbconnection  $con               with handle to the database
 * @param array            $case_info      with data about the case, to add/update
 * @param boolean          $_return_data      F - returns record ID, T - returns record in an array
 * @param boolean          $_magic_quotes     F - inbound data is not "add slashes", T - data is "add slashes"
 *
 * @return mixed $case_id of newly created or modified case, record data array or false if failure occured
 */
function add_update_case($con, $case_info, $_return_data = false, $_magic_quotes =  false )
{
   /**
    * Default return value
    *
    * Case ID or boolean upon failure
    * Default value is set at FALSE
    *
    * @var mixed $_retVal Indicates if Case was created or not
    * @access private
    * @static
    */
    $_retVal = false;

    global $session_user_id;

    // If there is not a 'company_id', one needs to be located or created
    if ( (! $case_info['company_id']) || ( $case_info['company_id']) == 0 )
    {
            $case_info['company_id'] = 1;
    }

    // Other sub-systems can handle "personal" information. They may or may not
    // utilize other fields that the 'cases' table don't need to deal with.
    // This array (below) will pull out only the fields we need and process them.
    // This way we make sure we hae the data we need, and only that data.
    $case_data = $case_info; //pull_case_fields ( $case_info );


    // If 'field' this exists, but has no data, remove it
    if (strlen($case_data['user_id']) == 0)
        unset ( $case_data['user_id'] );

    if (strlen($case_data['case_id']) == 0)
        unset ( $case_data['case_id'] );

    // Prep array for "search", only on these fields
    $extra_where = array();
    foreach ($case_data as $_field => $_value) {
        switch ($_field) {
            case 'case_id':
//                case 'work_phone':
//                case 'cell_phone':
//                case 'home_phone':
                $extra_where[$_field] = $_value;
            break;
        }
    }

    $_table_name = 'cases';

    // Determine if this case already exists
    $found_case_data = __record_find ( $con, $_table_name, $extra_where, 'AND', $_magic_quotes );

    // What's the primary key for this data set
    $_primay_key = $found_case_data['primarykey'];

    // If this case exists already
    if ( $found_case_data[$_primay_key] )
    {
        // We found it, so pull record ID
        $case_data[$_primay_key] = $found_case_data[$_primay_key];

        // Need to clean up the data
        // "Account Owner"
        if (strlen($case_data['user_id']) == 0)
                $case_data['user_id']  = $found_case_data['user_id'];

        if (strlen($case_data['company_id']) == 0)
                $case_data['company_id']  = $found_case_data['company_id'];

        // Update case data record
        $_retVal = __record_update ( $con, $_table_name, 'case_id', $case_data, $_magic_quotes );

        if ($_retVal['case_id'] == 0)
                $_retVal['case_id']  = $case_data['case_id'];

//            $_retVal = $_retVal['case_id'];
        $case_id=$_retVal['case_id'];
        //this will run whether or not base case changed
        $param = array($_retVal, $case_data);
        do_hook_function('case_edit_2', $param);

        $audit_type = 'updated';
    }

    // This is a new Record
    else
    {
        // If a company has not been defined, AND names are not given, this can be be dealt with
        if ( ( $case_data['company_id'] ) && ( ( $case_data['case_title'] ) && ( $case_data['case_type_id'] ) ) && ($case_data['case_status_id']) )
        {
            // Need to clean up the data

            // "Account Owner"
            $case_data['user_id']          = (strlen($case_data['user_id']) > 0)         ? $case_data['user_id']         : $session_user_id;

            //do other case defaults here


            $case_array = __record_insert ( $con, 'cases', $case_data, $_magic_quotes, true );

            $case_id=$case_array['case_id'];
            $_retVal = $case_id;

            //add to recently viewed list
            update_recent_items($con, $session_user_id, $_table_name, $case_id);

            $case_data['case_id'] = $case_id;
            do_hook_function('case_new_2', $case_data);

            $audit_type = 'created';
        }
    }

    // Set audit trail
    add_audit_item($con, $session_user_id, $audit_type, $_table_name, $case_id, 1);

    return $_retVal;
};


/**********************************************************************/
/**
 *
 * Adds a case to the system, based on array of data about the case
 * Runs hook functions and adds audit items when complete
 *
 * This is now just a wrapper to the new method 'add_update_case' to
 * maintain BC with plug-ins that expect this
 *
 * @param adodbconnection  $con      with ADOdb connection Object
 * @param array            $case  with data about the case, to add
 * @param boolean          $magic_quotes     F - inbound data is not magic_quoted by php, T - data is magic quoted
 *
 * @depreciated
 *
 * @return $case_id with newly created case, or false if failure occured
 */
function add_case($con, $case, $magic_quotes=false)
{
    return add_update_case($con, $case, false, $magic_quotes);
};

/**********************************************************************/
/**
 *
 * Searches for a case based on data about the case
 *
 * @param adodbconnection  $con               with ADOdb connection Object
 * @param array            $case_data      with fields to search for
 * @param boolean          $show_deleted      specifying if deleted cases should be included (defaults to false)
 * @param boolean          $return_recordset  indicating if adodb recordset object should be returned (defaults to false)
 *
 * @return array $results with either an array of case records, or a recordset object (false on failure)
*/
function find_case($con, $case_data, $show_deleted = false, $return_recordset = false)
{
    $sql = "SELECT * FROM cases";

    if (array_key_exists('case_id',$case_data) AND trim($case_data['case_id'])) {
        $case= get_case($con, $case_id, $return_recordset);
        if ($case AND is_array($case)) return array($case);
        else return $case;
    } else {

        $extra_where=array();
        foreach ($case_data as $ckey=>$cval) {
            switch ($ckey) {
                case 'case_title':
                case 'case_description':
//                case 'description':
                    unset($case_data[$ckey]);
                    $extra_where[]="$ckey LIKE ".$con->qstr($cval);
                break;
            }
        }
        if (!$show_deleted) $case_data['case_record_status']='a';

        if (count($extra_where)==0) $extra_where=false;
        $wherestr=make_where_string($con, $case_data, $tablename, $extra_where);
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
 *
 * Gets a case based on the database identifer if that case
 *
 * @param adodbconnection  $con               with ADOdb connection Object
 * @param integer          $case_id        with ID of the case to get details about
 * @param boolean          $return_recordset  indicating if adodb recordset object should be returned (defaults to false)
 *
 * @return array $results with either an array of case fields, or a recordset object (false on failure)
*/
function get_case($con, $case_id, $return_rst = false) {
    if (!$case_id) return false;
    $sql = "SELECT * FROM cases WHERE case_id=$case_id";
    $rst = $con->execute($sql);
    if (!$rst) { db_error_handler($con, $sql); return false; }
    else {
        if ($return_rst) {
            return $rst;
       } else return $rst->fields;
    }
    //shouldn't ever get here
    return false;
};

/**********************************************************************/
/**
 *
 * Updates an case in XRMS from an associative array
 * Either an case_id must be explicitly set or an adodbrecordset for the record to be updated
 * must be passed in or the function will fail
 *
 * @param adodbconnection  $con           ADOdb connection Object
 * @param array            $case_data  with associative array defining case data to update
 * @param integer          $case_id    optionally identifying case in the database (required if not passing in a recordset to $case_rst)
 * @param adodbrecordset   $case_rst   optionally providing a recordset to use for the update (required if not passing in an integer for $case_id)
 * @param boolean          $magic_quotes     F - inbound data is not magic_quoted by php, T - data is magic quoted
 *
 * @return boolean specifying if update succeeded
 */
function update_case($con, $case, $case_id = false, $case_rst = false, $magic_quotes=false)
{

    global $session_user_id;

    if (!$case) return false;
    if (!$case_rst) {
        $case_rst=get_case($con, $case_id, true);
    }
    if (!$case_rst) return false;

    $rec['last_modified_at'] = time();
    $rec['last_modified_by'] = $session_user_id;


    $upd = $con->GetUpdateSQL($case_rst, $case, false, $magic_quotes);
    if ($upd) {
        $rst=$con->execute($upd);
        if (!$rst) { db_error_handler($con, $upd); return false; }
    }

    //this will run whether or not base case changed
    $param = array($case_rst, $case);
    do_hook_function('case_edit_2', $param);

    add_audit_item($con, $session_user_id, 'updated', 'cases', $case_id, 1);

    return true;

};

/**********************************************************************/
/**
 *
 * Deletes an case from XRMS, based on passed in case_id
 * Can delete case from database or mark as removed using record status
 *
 * @param adodbconnection  $con                   ADOdb connection Object
 * @param integer          $case_id            identifying which case to delete
 * @param boolean          $delete_from_database  specifying if case should be deleted from the database, or simply marked with a deleted flag (defaults to false, mark with deleted flag)
 *
 * @return boolean indicating success of delete operation
 */
function delete_case($con, $case_id, $delete_from_database = false)
{
    if (!$case_id) return false;
    if ($delete_from_database) {
        $sql = "DELETE FROM cases";
    } else {
        $sql = "UPDATE cases SET case_record_status=" . $con->qstr('d');
    }
    $sql .= "  WHERE case_id=$case_id";

    $rst=$con->execute($sql);
    if (!$rst) { db_error_handler($con, $sql); return false; }

    return true;
};



/**
 *
 * Pulls only case field data from given array
 *
 * @param array $array_data array to retrieve case data from
 *
 * @return array $case_fields case "only" fields found in given array
 */
function pull_case_fields ( $array_data )
{
    if ( ! $array_data )
        return $array_data;

    // Retrieve only the field names we can handle
    $case_fields = array ( 'company_id'           => '',
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
    return array_intersect_key_2($case_fields, $array_data);

};

/**********************************************************************/
/** Include the misc utilities file */
include_once $include_directory . 'utils-misc.php';


// ============================================================================

 /**
 * $Log: utils-cases.php,v $
 * Revision 1.1  2006/04/27 03:24:04  vanmer
 * - Initial revision of a cases API, implements basic add/update/get/find/delete
 * - still needs to be updated with phpdoc and advanced functionality
 *
 * Revision 1.19  2006/04/11 00:42:08  vanmer
 * - added missing PHPDoc parameters
 *
 * Revision 1.18  2006/04/05 19:22:22  vanmer
 * - added needed default parameter on add_case
 *
 * Revision 1.17  2006/04/05 00:44:59  vanmer
 * - added magic quotes parameters to all cases functions which call getUpdateSQL or getInsertSQL
 *
 * Revision 1.16  2006/03/19 02:18:41  ongardie
 * - Allow empty salutation for new cases.
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
 * - added code to ensure that the case_id variable is set, since it is used later in the code
 *
 * Revision 1.11  2005/12/22 22:50:36  jswalter
 *  - modified 'add_update_company()' to default to '1' for unknown cases and company records
 *
 * Revision 1.10  2005/12/20 18:34:17  jswalter
 *  - removed 'home_address_id' assignment from add_update method
 * BUg 777
 *
 * Revision 1.9  2005/12/20 07:54:15  jswalter
 *  - completed 'add_update_case()'
 * Bug 777
 *
 * Revision 1.8  2005/12/15 00:16:08  jswalter
 *  - added a bit more "intelligent" processing in "add_update"
 *  - created new function to retrieve only fields that are in "cases" table
 *  - company_id is now placed in case record
 *
 * Revision 1.7  2005/12/10 20:09:34  vanmer
 * - removed parameters to getUpdateSQL, to allow update of case with strange characters
 *
 * Revision 1.6  2005/12/07 00:14:53  jswalter
 *  - added new method 'add_update_case()' to replace the existing add() and update() methods
 *  - modified 'add_case()' to be a BC wrapper for 'add_update_case()'
 *
 * Revision 1.5  2005/12/05 21:10:46  jswalter
 *  - removed IM fields from 'add_cases'
 *
 * Revision 1.4  2005/12/02 01:50:18  vanmer
 * - added XRMS_API package tag
 *
 * Revision 1.3  2005/11/23 17:34:21  jswalter
 *  - moved 'clean_phone_fields()' to "utils-misc.php"
 *
 * Revision 1.2  2005/11/18 20:34:38  vanmer
 * - changed to updated case modified by/time for update_case
 *
 * Revision 1.1  2005/11/18 20:04:48  vanmer
 * -Initial revision of an API for managing cases in XRMS
 *
**/
 ?>
