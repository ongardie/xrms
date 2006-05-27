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
 * $Id: utils-cases.php,v 1.7 2006/05/27 20:06:00 ongardie Exp $
 *
 */

require_once('utils-typestatus.php');
require_once('utils-workflow.php');
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
 * - integer company_id              - Company this case belongs to
 * - string case_title               - Title for the case
 * - integer case_type_id              - type_id for the case type, from the case_types table
 * - integer case_status_id         - status_id for the case status, from the case_statuses table
 * - date due_at ? used to determine the deadline on this case
 *
 * These fields are optional, some may be derived from other fields if not defined.
 * - integer case_priority_id ? indicates which specific case_priority this case has
 * -integer company_id ? identifies the company record associated with this case
 * -integer division_id ? identifies the particular division within the company associated with this case
 * -integer contact_id ? identifies the contact associated with the case
 * -integer user_id ? identifies the system user who owns the case
 * -integer priority ? identifies the priority of the case
 * -string case_title ? string identifying the case
 * -string case_description ? text field with a long description of the case
 *
 * Do not define these fields, they are auto-defined
 * -date last_modified_at ? timestamp for when the case was last changed, automatically generated
 * -integer last_modified_by ? identifies the system user who last changed the case, automatically generated
 * -char case_record_status ? character identifying the status of the record (a for active, d for del *
 * -integer closed_by ? identifies the system user who last changed the case to a closed status, automatically generated
 * -integer closed_at ? when was record modified - this will be the same as 'entered_at'
 * -date entered_at              - when was record created
 * -integer entered_by              - who created the record
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

    // If there is not a 'company_id', one needs to be located or created (unless this is an existing case)
    if ( ((! $case_info['company_id']) || ( $case_info['company_id']) == 0) AND (!$case_info['case_id']))
    {
            $case_info['company_id'] = 1;
    }

    //associate case info as case_data.  Could parse case_info and only pull case fields, but for now we'll use all fields passed in
    $case_data = $case_info;


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

        if (strlen($case_data['contact_id']) == 0)
                $case_data['contact_id']  = $found_case_data['contact_id'];

        if (strlen($case_data['case_status_id']) == 0)
                $case_data['case_status_id']  = $found_case_data['case_status_id'];

        if ($case_data['case_status_id']) {
            if ($case_data['case_status_id']!=$found_case_data['case_status_id']) {
                $case_data=set_case_open_closed_by_status($con, $case_data, $case_data['case_status_id']);
            }
        }

        // Update case data record
        $_retVal = __record_update ( $con, $_table_name, 'case_id', $case_data, $_magic_quotes );

        if ($_retVal['case_id'] == 0)
                $_retVal['case_id']  = $case_data['case_id'];

//            $_retVal = $_retVal['case_id'];
        $case_id=$_retVal['case_id'];
        //this will run whether or not base case changed
        $param = array($_retVal, $case_data);
        do_hook_function('case_edit_2', $param);

        if ($case_data['case_status_id'] != $found_case_data['case_status_id']) {
            $add_workflow_activities=true;
            add_workflow_history($con, 'cases', $case_id, $found_case_data['case_status_id'], $case_data['case_status_id']);
        }

        $audit_type = 'updated';
    }

    // This is a new Record
    else
    {
        // If a case has the needed elements, we will add it
        if ( ( $case_data['company_id'] ) && ( ( $case_data['case_title'] ) && ( $case_data['case_type_id'] ) ) && ($case_data['case_status_id']) && ($case_data['due_at']) )
        {
            // Need to clean up the data

            // "Account Owner"
            $case_data['user_id']          = (strlen($case_data['user_id']) > 0)         ? $case_data['user_id']         : $session_user_id;

            //do other case defaults here
            $case_data=set_case_open_closed_by_status($con, $case_data, $case_data['case_status_id']);

            $case_array = __record_insert ( $con, 'cases', $case_data, $_magic_quotes, true );

            $case_id=$case_array['case_id'];
            $_retVal = $case_id;

            if ($case_id) {

                $add_workflow_activities=true;

                //add to recently viewed list
                update_recent_items($con, $session_user_id, $_table_name, $case_id);
    
                $case_data['case_id'] = $case_id;
                do_hook_function('case_new_2', $case_data);
    
                $audit_type = 'created';
            }
        }
    }

    if ($add_workflow_activities) {
        $on_what_id_template = $case_data['case_status_id'];
        $on_what_table_template = "case_statuses";
        add_workflow_activities($con, $on_what_table_template, $on_what_id_template, 'cases',$case_id, $case_data['company_id'], $case_data['contact_id']);
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
            $ret[]=$rst->getRowAssoc(false);
            $rst->movenext();
        }
    }
    if (count($ret)>0) return $ret;
    else return false;

};

/**********************************************************************/
/**
 *
 * Gets a case based on the database identifer of that case
 *
 * @param adodbconnection  $con               with ADOdb connection Object
 * @param integer          $case_id        with ID of the case to get details about
 * @param boolean          $return_recordset  indicating if adodb recordset object should be returned (defaults to false)
 *
 * @return array $results with either an array of case fields, or a recordset object (false on failure)
*/
function get_case($con, $case_id, $return_rst = false) {
    if (!$case_id) return false;

$sql = "SELECT
        ca.*,
        cas.case_status_display_html, cap.case_priority_display_html, cat.case_type_id, cat.case_type_display_html,
        u1.username as entered_by_username, u2.username as last_modified_by_username,
        u3.username as case_owner_username,
        u4.username as closed_by_username
        FROM
        cases ca
        LEFT OUTER JOIN case_statuses cas ON ca.case_status_id = cas.case_status_id
        LEFT OUTER JOIN case_priorities cap ON ca.case_priority_id = cap.case_priority_id
        LEFT OUTER JOIN case_types cat ON ca.case_type_id = cat.case_type_id
        LEFT OUTER JOIN users u1 ON ca.entered_by = u1.user_id
        LEFT OUTER JOIN users u2 ON ca.last_modified_by = u2.user_id
        LEFT OUTER JOIN users u3 ON ca.user_id = u3.user_id
        LEFT OUTER JOIN users u4 ON u4.user_id=ca.closed_by
        WHERE case_id = $case_id";

    $rst = $con->execute($sql);
    if (!$rst) { db_error_handler($con, $sql); return false; }
    else {
        if ($return_rst) {
            return $rst;
       } else return $rst->getRowAssoc(false);
    }
    //shouldn't ever get here
    return false;
};

/**********************************************************************/
/**
 *
 * Updates a case in XRMS from an associative array
 * Either an case_id must be explicitly set or an adodbrecordset for the record to be updated
 * must be passed in or the function will fail
 * Is a wrapper for add_update_case which can pull case_id from a recordset if provided
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
    //if we're passed a recordset and it's not empty, populate case_id from it if it was not provided
    if ($case_rst AND !$case_rst->EOF AND !$case_id) {
         $case_id=$case_rst->fields['case_id'];
    }
    if ($case_id) $case['case_id']=$case_id;
    else return false;
    return add_update_case($con, $case, false, $magic_quotes);
};


/**********************************************************************/
/**
 *
 * Takes a case record array and adds a closed_by and closed_at fieldset based on the status of the case
 *
 * @param adodbconnection  $con           ADOdb connection Object
 * @param array            $rec  with associative array defining case data
 * @param integer          $status_id    identifying case status in the database
 *
 * @return array with original $rec with new keys 'closed_at' and 'closed_by' based on status provided
 */
function set_case_open_closed_by_status($con, $rec=false, $status_id=false) {
    global $session_user_id;
    if (!$rec OR !$status_id) return false;
    $status_data=get_entity_status($con, 'case',$status_id);
    $open_indicator=$status_data['status_open_indicator'];
    switch ($open_indicator) {
        case 'o':
            $rec['closed_at']=NULL;
            $rec['closed_by']=0;
        break;
        case 'u':
        case 'r':
            $rec['closed_at']=time();
            $rec['closed_by']=$session_user_id;
        break;
    }
    return $rec;
}

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
    global $session_user_id;
    if (!$case_id) return false;
    if ($delete_from_database) {
        $sql = "DELETE FROM cases";
    } else {
        $sql = "UPDATE cases SET case_record_status=" . $con->qstr('d');
    }
    $sql .= "  WHERE case_id=$case_id";

    $rst=$con->execute($sql);
    if (!$rst) { db_error_handler($con, $sql); return false; }

    add_audit_item($con, $session_user_id, 'deleted', 'cases', $case_id, 1);

    return true;
};


/**********************************************************************/
/** Include the misc utilities file */
include_once $include_directory . 'utils-misc.php';


// ============================================================================

 /**
 * $Log: utils-cases.php,v $
 * Revision 1.7  2006/05/27 20:06:00  ongardie
 * - Typo in comment.
 *
 * Revision 1.6  2006/05/03 00:01:47  vanmer
 * - added check to ensure company_id isn't reset to 1 if opportunity or case already exists
 * - added lookup of assumed data in the record on update
 *
 * Revision 1.5  2006/05/02 00:49:14  vanmer
 * - added joins to related case data, for use in cases one page
 *
 * Revision 1.4  2006/04/29 01:46:12  vanmer
 * - moved workflow activity instantiation into cases API and out of edit-2.php and new-2.php
 *
 * Revision 1.3  2006/04/28 03:25:59  vanmer
 * - updated case API with proper PHPDoc
 * - removed commented and unused code
 *
 * Revision 1.2  2006/04/28 02:45:59  vanmer
 * - added status check to see if case should be open or closed
 * - added types and status API include
 *
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
