<?php
/**
 * Utility functions for manipulating opportunities
 *
 * These functions create, retrieve, delete and modify opportunities
 * This file should be included anywhere opportunities need to be created or modified
 *
 * @author Aaron van Meerten
 * @package XRMS_API
 *
 * $Id: utils-opportunities.php,v 1.4 2006/05/03 00:01:47 vanmer Exp $
 *
 */

require_once('utils-typestatus.php');
require_once('utils-workflow.php');
/**********************************************************************/
/**
 *
 * Adds or modifies a opportunity within XRMS, based on array of data about the opportunity
 *
 * Define this field if the record should be updated, otherwise leave out of array
 * - opportunity_id              - Opportunity ID, once a opportunity record is created
 *
 * These 'opportunities' tables fields are required.
 * This method will fail without them.
 * - integer company_id              - Company this opportunity belongs to
 * - string opportunity_title               - Title for the opportunity
 * - integer opportunity_type_id              - type_id for the opportunity type, from the opportunity_types table
 * - integer opportunity_status_id         - status_id for the opportunity status, from the opportunity_statuses table
 * - date close_at ? used to determine the deadline on this opportunity
 *
 * These fields are optional, some may be derived from other fields if not defined.
 * - integer opportunity_priority_id ? indicates which specific opportunity_priority this opportunity has
 * -integer company_id ? identifies the company record associated with this opportunity
 * -integer division_id ? identifies the particular division within the company associated with this opportunity
 * -integer contact_id ? identifies the contact associated with the opportunity
 * -integer user_id ? identifies the system user who owns the opportunity
 * -integer priority ? identifies the priority of the opportunity
 * -string opportunity_title ? string identifying the opportunity
 * -string opportunity_description ? text field with a long description of the opportunity
 *
 * Do not define these fields, they are auto-defined
 * -date last_modified_at ? timestamp for when the opportunity was last changed, automatically generated
 * -integer last_modified_by ? identifies the system user who last changed the opportunity, automatically generated
 * -char opportunity_record_status ? character identifying the status of the record (a for active, d for del *
 * -integer closed_by ? identifies the system user who last changed the opportunity to a closed status, automatically generated
 * -integer closed_at ? when was record modified - this will be the same as 'entered_at'
 * -date entered_at              - when was record created
 * -integer entered_by              - who created the record
 *
 * @param adodbconnection  $con               with handle to the database
 * @param array            $opportunity_info      with data about the opportunity, to add/update
 * @param boolean          $_return_data      F - returns record ID, T - returns record in an array
 * @param boolean          $_magic_quotes     F - inbound data is not "add slashes", T - data is "add slashes"
 *
 * @return mixed $opportunity_id of newly created or modified opportunity, record data array or false if failure occured
 */
function add_update_opportunity($con, $opportunity_info, $_return_data = false, $_magic_quotes =  false )
{
   /**
    * Default return value
    *
    * Opportunity ID or boolean upon failure
    * Default value is set at FALSE
    *
    * @var mixed $_retVal Indicates if Opportunity was created or not
    * @access private
    * @static
    */
    $_retVal = false;

    global $session_user_id;

    // If there is not a 'company_id', one needs to be located or created unless opportunity already exists

    if ( ((! $opportunity_info['company_id']) || ( $opportunity_info['company_id']) == 0) AND (!$opportunity_info['opportunity_id']) )
    {
            $opportunity_info['company_id'] = 1;
    }

    //associate opportunity info as opportunity_data.  Could parse opportunity_info and only pull opportunity fields, but for now we'll use all fields passed in
    $opportunity_data = $opportunity_info;


    // If 'field' this exists, but has no data, remove it
    if (strlen($opportunity_data['user_id']) == 0)
        unset ( $opportunity_data['user_id'] );

    if (strlen($opportunity_data['opportunity_id']) == 0)
        unset ( $opportunity_data['opportunity_id'] );

    // Prep array for "search", only on these fields
    $extra_where = array();
    foreach ($opportunity_data as $_field => $_value) {
        switch ($_field) {
            case 'opportunity_id':
//                opportunity 'work_phone':
//                opportunity 'cell_phone':
//                opportunity 'home_phone':
                $extra_where[$_field] = $_value;
            break;
        }
    }

    $_table_name = 'opportunities';

    // Determine if this opportunity already exists
    $found_opportunity_data = __record_find ( $con, $_table_name, $extra_where, 'AND', $_magic_quotes );

    // What's the primary key for this data set
    $_primay_key = $found_opportunity_data['primarykey'];

    // If this opportunity exists already
    if ( $found_opportunity_data[$_primay_key] )
    {
        // We found it, so pull record ID
        $opportunity_data[$_primay_key] = $found_opportunity_data[$_primay_key];

        // Need to clean up the data
        // "Account Owner"
        if (strlen($opportunity_data['user_id']) == 0)
                $opportunity_data['user_id']  = $found_opportunity_data['user_id'];

        if (strlen($opportunity_data['company_id']) == 0)
                $opportunity_data['company_id']  = $found_opportunity_data['company_id'];

        if (strlen($opportunity_data['contact_id']) == 0)
                $opportunity_data['contact_id']  = $found_opportunity_data['contact_id'];

        if (strlen($opportunity_data['opportunity_status_id']) == 0)
            $opportunity_data['opportunity_status_id']  = $found_opportunity_data['opportunity_status_id'];

        if ($opportunity_data['opportunity_status_id']) {
            if ($opportunity_data['opportunity_status_id']!=$found_opportunity_data['opportunity_status_id']) {
                $opportunity_data=set_opportunity_open_closed_by_status($con, $opportunity_data, $opportunity_data['opportunity_status_id']);
            }
        }

        // Update opportunity data record
        $_retVal = __record_update ( $con, $_table_name, 'opportunity_id', $opportunity_data, $_magic_quotes );

        if ($_retVal['opportunity_id'] == 0)
                $_retVal['opportunity_id']  = $opportunity_data['opportunity_id'];

//            $_retVal = $_retVal['opportunity_id'];
        $opportunity_id=$_retVal['opportunity_id'];
        //this will run whether or not base opportunity changed
        $param = array($_retVal, $opportunity_data);
        do_hook_function('opportunity_edit_2', $param);

        if ($opportunity_data['opportunity_status_id'] != $found_opportunity_data['opportunity_status_id']) {
            $add_workflow_activities=true;

             add_workflow_history($con, 'opportunities', $opportunity_id, $found_opportunity_data['opportunity_status_id'], $opportunity_data['opportunity_status_id']);
        }

        $audit_type = 'updated';
    }

    // This is a new Record
    else
    {
        // If a opportunity has the needed elements, we will add it
        if ( ( $opportunity_data['company_id'] ) && ( ( $opportunity_data['opportunity_title'] ) && ( $opportunity_data['opportunity_type_id'] ) ) && ($opportunity_data['opportunity_status_id']) && ($opportunity_data['close_at']) )
        {
            // Need to clean up the data

            // "Account Owner"
            $opportunity_data['user_id']          = (strlen($opportunity_data['user_id']) > 0)         ? $opportunity_data['user_id']         : $session_user_id;

            //do other opportunity defaults here
            $opportunity_data=set_opportunity_open_closed_by_status($con, $opportunity_data, $opportunity_data['opportunity_status_id']);

            $opportunity_array = __record_insert ( $con, 'opportunities', $opportunity_data, $_magic_quotes, true );

            $opportunity_id=$opportunity_array['opportunity_id'];
            $_retVal = $opportunity_id;

            if ($opportunity_id) {
                //be sure to add the activities associated with this new record
                $add_workflow_activities=true;

                //add to recently viewed list
                update_recent_items($con, $session_user_id, $_table_name, $opportunity_id);
    
                $opportunity_data['opportunity_id'] = $opportunity_id;
                do_hook_function('opportunity_new_2', $opportunity_data);
    
                $audit_type = 'created';
            }
        }
    }
    if ($add_workflow_activities) {
        $on_what_id_template = $opportunity_data['opportunity_status_id'];
        $on_what_table_template = "opportunity_statuses";
        add_workflow_activities($con, $on_what_table_template, $on_what_id_template, 'opportunities',$opportunity_id, $opportunity_data['company_id'], $opportunity_data['contact_id']);
    }
    // Set audit trail
    add_audit_item($con, $session_user_id, $audit_type, $_table_name, $opportunity_id, 1);

    return $_retVal;
};


/**********************************************************************/
/**
 *
 * Adds a opportunity to the system, based on array of data about the opportunity
 * Runs hook functions and adds audit items when complete
 *
 * This is now just a wrapper to the new method 'add_update_opportunity' to
 * maintain BC with plug-ins that expect this
 *
 * @param adodbconnection  $con      with ADOdb connection Object
 * @param array            $opportunity  with data about the opportunity, to add
 * @param boolean          $magic_quotes     F - inbound data is not magic_quoted by php, T - data is magic quoted
 *
 * @depreciated
 *
 * @return $opportunity_id with newly created opportunity, or false if failure occured
 */
function add_opportunity($con, $opportunity, $magic_quotes=false)
{
    return add_update_opportunity($con, $opportunity, false, $magic_quotes);
};

/**********************************************************************/
/**
 *
 * Searches for a opportunity based on data about the opportunity
 *
 * @param adodbconnection  $con               with ADOdb connection Object
 * @param array            $opportunity_data      with fields to search for
 * @param boolean          $show_deleted      specifying if deleted opportunities should be included (defaults to false)
 * @param boolean          $return_recordset  indicating if adodb recordset object should be returned (defaults to false)
 *
 * @return array $results with either an array of opportunity records, or a recordset object (false on failure)
*/
function find_opportunity($con, $opportunity_data, $show_deleted = false, $return_recordset = false)
{
    $sql = "SELECT * FROM opportunities";

    if (array_key_exists('opportunity_id',$opportunity_data) AND trim($opportunity_data['opportunity_id'])) {
        $opportunity= get_opportunity($con, $opportunity_id, $return_recordset);
        if ($opportunity AND is_array($opportunity)) return array($opportunity);
        else return $opportunity;
    } else {

        $extra_where=array();
        foreach ($opportunity_data as $ckey=>$cval) {
            switch ($ckey) {
                case 'opportunity_title':
                case 'opportunity_description':
//                opportunity 'description':
                    unset($opportunity_data[$ckey]);
                    $extra_where[]="$ckey LIKE ".$con->qstr($cval);
                break;
            }
        }
        if (!$show_deleted) $opportunity_data['opportunity_record_status']='a';

        if (count($extra_where)==0) $extra_where=false;
        $wherestr=make_where_string($con, $opportunity_data, $tablename, $extra_where);
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
 * Gets a opportunity based on the database identifer of that opportunity
 *
 * @param adodbconnection  $con               with ADOdb connection Object
 * @param integer          $opportunity_id        with ID of the opportunity to get details about
 * @param boolean          $return_recordset  indicating if adodb recordset object should be returned (defaults to false)
 *
 * @return array $results with either an array of opportunity fields, or a recordset object (false on failure)
*/
function get_opportunity($con, $opportunity_id, $return_rst = false) {
    if (!$opportunity_id) return false;

$sql = "SELECT
opportunities.*,
u1.username as entered_by_username, u2.username as last_modified_by_username,
u3.username as opportunity_owner_username,
u4.username as closed_by_username, 
os.opportunity_status_display_html, ot.opportunity_type_id, ot.opportunity_type_display_html, cam.campaign_title 
FROM
opportunities
LEFT OUTER JOIN campaigns cam on opportunities.campaign_id = cam.campaign_id
LEFT OUTER JOIN users u1 ON opportunities.entered_by = u1.user_id
LEFT OUTER JOIN users u2 ON opportunities.last_modified_by = u2.user_id
LEFT OUTER JOIN users u3 ON opportunities.user_id = u3.user_id
LEFT OUTER JOIN users u4 ON u4.user_id=opportunities.closed_by
LEFT OUTER JOIN opportunity_types ot ON ot.opportunity_type_id=opportunities.opportunity_type_id
LEFT OUTER JOIN opportunity_statuses os ON os.opportunity_status_id=opportunities.opportunity_status_id 
WHERE opportunity_id = $opportunity_id";


//    $sql = "SELECT * FROM opportunities WHERE opportunity_id=$opportunity_id";


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
 * Updates an opportunity in XRMS from an associative array
 * Either an opportunity_id must be explicitly set or an adodbrecordset for the record to be updated
 * must be passed in or the function will fail
 * Is a wrapper for add_update_opportunity which can pull opportunity_id from a recordset if provided
 *
 * @param adodbconnection  $con           ADOdb connection Object
 * @param array            $opportunity_data  with associative array defining opportunity data to update
 * @param integer          $opportunity_id    optionally identifying opportunity in the database (required if not passing in a recordset to $opportunity_rst)
 * @param adodbrecordset   $opportunity_rst   optionally providing a recordset to use for the update (required if not passing in an integer for $opportunity_id)
 * @param boolean          $magic_quotes     F - inbound data is not magic_quoted by php, T - data is magic quoted
 *
 * @return boolean specifying if update succeeded
 */
function update_opportunity($con, $opportunity, $opportunity_id = false, $opportunity_rst = false, $magic_quotes=false)
{
    //if we're passed a recordset and it's not empty, populate opportunity_id from it if it was not provided
    if ($opportunity_rst AND !$opportunity_rst->EOF AND !$opportunity_id) {
         $opportunity_id=$opportunity_rst->fields['opportunity_id'];
    }
    if ($opportunity_id) $opportunity['opportunity_id']=$opportunity_id;
    else return false;
    return add_update_opportunity($con, $opportunity, false, $magic_quotes);
};


/**********************************************************************/
/**
 *
 * Takes a opportunity record array and adds a closed_by and closed_at fieldset based on the status of the opportunity
 *
 * @param adodbconnection  $con           ADOdb connection Object
 * @param array            $rec  with associative array defining opportunity data
 * @param integer          $status_id    identifying opportunity status in the database
 *
 * @return array with original $rec with new keys 'closed_at' and 'closed_by' based on status provided
 */
function set_opportunity_open_closed_by_status($con, $rec=false, $status_id=false) {
    global $session_user_id;
    if (!$rec OR !$status_id) return false;
    $status_data=get_entity_status($con, 'opportunity',$status_id);
    $open_indicator=$status_data['status_open_indicator'];
    switch ($open_indicator) {
        case 'o':
            $rec['closed_at']=NULL;
            $rec['closed_by']=0;
        break;
        case 'w':
        case 'l':
            $rec['closed_at']=time();
            $rec['closed_by']=$session_user_id;
        break;
    }
    return $rec;
}

/**********************************************************************/
/**
 *
 * Deletes an opportunity from XRMS, based on passed in opportunity_id
 * Can delete opportunity from database or mark as removed using record status
 *
 * @param adodbconnection  $con                   ADOdb connection Object
 * @param integer          $opportunity_id            identifying which opportunity to delete
 * @param boolean          $delete_from_database  specifying if opportunity should be deleted from the database, or simply marked with a deleted flag (defaults to false, mark with deleted flag)
 *
 * @return boolean indicating success of delete operation
 */
function delete_opportunity($con, $opportunity_id, $delete_from_database = false)
{
    global $session_user_id;
    if (!$opportunity_id) return false;
    if ($delete_from_database) {
        $sql = "DELETE FROM opportunities";
    } else {
        $sql = "UPDATE opportunities SET opportunity_record_status=" . $con->qstr('d');
    }
    $sql .= "  WHERE opportunity_id=$opportunity_id";

    $rst=$con->execute($sql);
    if (!$rst) { db_error_handler($con, $sql); return false; }

    add_audit_item($con, $session_user_id, 'deleted', 'opportunities', $opportunity_id, 1);

    return true;
};


/**********************************************************************/
/** Include the misc utilities file */
include_once $include_directory . 'utils-misc.php';


// ============================================================================

 /**
 * $Log: utils-opportunities.php,v $
 * Revision 1.4  2006/05/03 00:01:47  vanmer
 * - added check to ensure company_id isn't reset to 1 if opportunity or case already exists
 * - added lookup of assumed data in the record on update
 *
 * Revision 1.3  2006/05/02 01:27:39  vanmer
 * - changed opportunities one.php to use get_opportunities and other get_ functions from the API
 * - updated get_opportunities function to do joins on related tables
 *
 * Revision 1.2  2006/04/29 01:48:25  vanmer
 * - replaced opportunites edit, new and delete pages to use opportunities API
 * - altered opportunities API to reflect correct codes for won/lost statuses
 * - moved workflow into opportunities API
 *
 * Revision 1.1  2006/04/28 04:29:36  vanmer
 * - Initial revision of the opportunities API and complete test suite
 * - still TODO is to update the PHPDoc for opportunities (still refects cases origins)
 *
 *
**/
 ?>
