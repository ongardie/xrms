<?php
/**
 * Utility functions for manipulating campaign workflows
 *
 * These functions create, retrieve, delete and modify campaign workflows
 *
 * Adapted from utils-opportunities.php by Aaron van Meerten
 * @author gopherit, Ivaylo Boiadjiev, 360 TEAM Ltd.
 * @package XRMS_API
 *
 * $Id: utils-campaigns.php,v 1.2 2010/12/16 14:25:06 gopherit Exp $
 *
 */

require_once('utils-typestatus.php');
require_once('utils-workflow.php');
/**********************************************************************/
/**
 *
 * Adds or modifies a campaign within XRMS, based on array of data about the campaign
 *
 * Define this field if the record should be updated, otherwise leave out of array
 * - campaign_id              - Campaign ID, once a campaign record is created
 *
 * These 'campaigns' tables fields are required.
 * This method will fail without them.
 * - integer company_id              - Company this campaign belongs to
 * - string campaign_title               - Title for the campaign
 * - integer campaign_type_id              - type_id for the campaign type, from the campaign_types table
 * - integer campaign_status_id         - status_id for the campaign status, from the campaign_statuses table
 * - date close_at ? used to determine the deadline on this campaign
 *
 * These fields are optional, some may be derived from other fields if not defined.
 * - integer campaign_priority_id ? indicates which specific campaign_priority this campaign has
 * -integer company_id ? identifies the company record associated with this campaign
 * -integer division_id ? identifies the particular division within the company associated with this campaign
 * -integer contact_id ? identifies the contact associated with the campaign
 * -integer user_id ? identifies the system user who owns the campaign
 * -integer priority ? identifies the priority of the campaign
 * -string campaign_title ? string identifying the campaign
 * -string campaign_description ? text field with a long description of the campaign
 *
 * Do not define these fields, they are auto-defined
 * -date last_modified_at ? timestamp for when the campaign was last changed, automatically generated
 * -integer last_modified_by ? identifies the system user who last changed the campaign, automatically generated
 * -char campaign_record_status ? character identifying the status of the record (a for active, d for del *
 * -integer closed_by ? identifies the system user who last changed the campaign to a closed status, automatically generated
 * -integer closed_at ? when was record modified - this will be the same as 'entered_at'
 * -date entered_at              - when was record created
 * -integer entered_by              - who created the record
 *
 * @param adodbconnection  $con               with handle to the database
 * @param array            $campaign_info      with data about the campaign, to add/update
 * @param boolean          $_return_data      F - returns record ID, T - returns record in an array
 * @param boolean          $_magic_quotes     F - inbound data is not "add slashes", T - data is "add slashes"
 *
 * @return mixed $campaign_id of newly created or modified campaign, record data array or false if failure occured
 */
function add_update_campaign($con, $campaign_info, $_return_data = false, $_magic_quotes =  false )
{
   /**
    * Default return value
    *
    * Campaign ID or boolean upon failure
    * Default value is set at FALSE
    *
    * @var mixed $_retVal Indicates if Campaign was created or not
    * @access private
    * @static
    */
    $_retVal = false;

    global $session_user_id;

    // If there is not a 'company_id', one needs to be located or created unless campaign already exists

    if ( ((! $campaign_info['company_id']) || ( $campaign_info['company_id']) == 0) AND (!$campaign_info['campaign_id']) )
    {
            $campaign_info['company_id'] = 1;
    }

    //associate campaign info as campaign_data.  Could parse campaign_info and only pull campaign fields, but for now we'll use all fields passed in
    $campaign_data = $campaign_info;


    // If 'field' this exists, but has no data, remove it
    if (strlen($campaign_data['user_id']) == 0)
        unset ( $campaign_data['user_id'] );

    if (strlen($campaign_data['campaign_id']) == 0)
        unset ( $campaign_data['campaign_id'] );

    // Prep array for "search", only on these fields
    $extra_where = array();
    foreach ($campaign_data as $_field => $_value) {
        switch ($_field) {
            case 'campaign_id':
//                campaign 'work_phone':
//                campaign 'cell_phone':
//                campaign 'home_phone':
                $extra_where[$_field] = $_value;
            break;
        }
    }

    $_table_name = 'campaigns';

    // Determine if this campaign already exists
    $found_campaign_data = __record_find ( $con, $_table_name, $extra_where, 'AND', $_magic_quotes );

    // What's the primary key for this data set
    $_primay_key = $found_campaign_data['primarykey'];

    // If this campaign exists already
    if ( $found_campaign_data[$_primay_key] )
    {
        // We found it, so pull record ID
        $campaign_data[$_primay_key] = $found_campaign_data[$_primay_key];

        // Need to clean up the data
        // "Account Owner"
        if (strlen($campaign_data['user_id']) == 0)
                $campaign_data['user_id']  = $found_campaign_data['user_id'];

        if (strlen($campaign_data['company_id']) == 0)
                $campaign_data['company_id']  = $found_campaign_data['company_id'];

        if (strlen($campaign_data['contact_id']) == 0)
                $campaign_data['contact_id']  = $found_campaign_data['contact_id'];

        if (strlen($campaign_data['campaign_status_id']) == 0)
            $campaign_data['campaign_status_id']  = $found_campaign_data['campaign_status_id'];

        if ($campaign_data['campaign_status_id']) {
            if ($campaign_data['campaign_status_id']!=$found_campaign_data['campaign_status_id']) {
                $campaign_data=set_campaign_open_closed_by_status($con, $campaign_data, $campaign_data['campaign_status_id']);
            }
        }

        // Update campaign data record
        $_retVal = __record_update ( $con, $_table_name, 'campaign_id', $campaign_data, $_magic_quotes );

        if ($_retVal['campaign_id'] == 0)
                $_retVal['campaign_id']  = $campaign_data['campaign_id'];

//            $_retVal = $_retVal['campaign_id'];
        $campaign_id=$_retVal['campaign_id'];
        //this will run whether or not base campaign changed
        $param = array($_retVal, $campaign_data);
        do_hook_function('campaign_edit_2', $param);

        if ($campaign_data['campaign_status_id'] != $found_campaign_data['campaign_status_id']) {
            $add_workflow_activities=true;

             add_workflow_history($con, 'campaigns', $campaign_id, $found_campaign_data['campaign_status_id'], $campaign_data['campaign_status_id']);
        }

        $audit_type = 'updated';
    }

    // This is a new Record
    else
    {
        // If a campaign has the needed elements, we will add it
        if ( ( $campaign_data['company_id'] ) && ( ( $campaign_data['campaign_title'] ) && ( $campaign_data['campaign_type_id'] ) ) && ($campaign_data['campaign_status_id']) && ($campaign_data['close_at']) )
        {
            // Need to clean up the data

            // "Account Owner"
            $campaign_data['user_id']          = (strlen($campaign_data['user_id']) > 0)         ? $campaign_data['user_id']         : $session_user_id;

            //do other campaign defaults here
            $campaign_data=set_campaign_open_closed_by_status($con, $campaign_data, $campaign_data['campaign_status_id']);

            $campaign_array = __record_insert ( $con, 'campaigns', $campaign_data, $_magic_quotes, true );

            $campaign_id=$campaign_array['campaign_id'];
            $_retVal = $campaign_id;

            if ($campaign_id) {
                //be sure to add the activities associated with this new record
                $add_workflow_activities=true;

                //add to recently viewed list
                update_recent_items($con, $session_user_id, $_table_name, $campaign_id);

                $campaign_data['campaign_id'] = $campaign_id;
                do_hook_function('campaign_new_2', $campaign_data);

                $audit_type = 'created';
            }
        }
    }
    if ($add_workflow_activities) {
        $on_what_id_template = $campaign_data['campaign_status_id'];
        $on_what_table_template = "campaign_statuses";
        add_workflow_activity($con, $on_what_table_template, $on_what_id_template, 'campaigns',$campaign_id, $campaign_data['company_id'], $campaign_data['contact_id']);
    }
    // Set audit trail
    add_audit_item($con, $session_user_id, $audit_type, $_table_name, $campaign_id, 1);

    return $_retVal;
};


/**********************************************************************/
/**
 *
 * Adds a campaign to the system, based on array of data about the campaign
 * Runs hook functions and adds audit items when complete
 *
 * This is now just a wrapper to the new method 'add_update_campaign' to
 * maintain BC with plug-ins that expect this
 *
 * @param adodbconnection  $con      with ADOdb connection Object
 * @param array            $campaign  with data about the campaign, to add
 * @param boolean          $magic_quotes     F - inbound data is not magic_quoted by php, T - data is magic quoted
 *
 * @depreciated
 *
 * @return $campaign_id with newly created campaign, or false if failure occured
 */
function add_campaign($con, $campaign, $magic_quotes=false)
{
    return add_update_campaign($con, $campaign, false, $magic_quotes);
};

/**********************************************************************/
/**
 *
 * Searches for a campaign based on data about the campaign
 *
 * @param adodbconnection  $con               with ADOdb connection Object
 * @param array            $campaign_data      with fields to search for
 * @param boolean          $show_deleted      specifying if deleted campaigns should be included (defaults to false)
 * @param boolean          $return_recordset  indicating if adodb recordset object should be returned (defaults to false)
 *
 * @return array $results with either an array of campaign records, or a recordset object (false on failure)
*/
function find_campaign($con, $campaign_data, $show_deleted = false, $return_recordset = false)
{
    $sql = "SELECT * FROM campaigns";

    if (array_key_exists('campaign_id',$campaign_data) AND trim($campaign_data['campaign_id'])) {
        $campaign= get_campaign($con, $campaign_id, $return_recordset);
        if ($campaign AND is_array($campaign)) return array($campaign);
        else return $campaign;
    } else {

        $extra_where=array();
        foreach ($campaign_data as $ckey=>$cval) {
            switch ($ckey) {
                case 'campaign_title':
                case 'campaign_description':
//                campaign 'description':
                    unset($campaign_data[$ckey]);
                    $extra_where[]="$ckey LIKE ".$con->qstr($cval);
                break;
            }
        }
        if (!$show_deleted) $campaign_data['campaign_record_status']='a';

        if (count($extra_where)==0) $extra_where=false;
        $wherestr=make_where_string($con, $campaign_data, $tablename, $extra_where);
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
 * Gets a campaign based on the database identifer of that campaign
 *
 * @param adodbconnection  $con               with ADOdb connection Object
 * @param integer          $campaign_id        with ID of the campaign to get details about
 * @param boolean          $return_recordset  indicating if adodb recordset object should be returned (defaults to false)
 *
 * @return array $results with either an array of campaign fields, or a recordset object (false on failure)
*/
function get_campaign($con, $campaign_id, $return_rst = false) {
    if (!$campaign_id) return false;

$sql = "SELECT  campaigns.*,
                u1.username as entered_by_username,
                u2.username as last_modified_by_username,
                u3.username as campaign_owner_username,
                cs.campaign_status_display_html,
                ct.campaign_type_id,
                ct.campaign_type_display_html, cam.campaign_title
        FROM campaigns
        LEFT OUTER JOIN campaigns cam on campaigns.campaign_id = cam.campaign_id
        LEFT OUTER JOIN users u1
            ON campaigns.entered_by = u1.user_id
        LEFT OUTER JOIN users u2
            ON campaigns.last_modified_by = u2.user_id
        LEFT OUTER JOIN users u3
            ON campaigns.user_id = u3.user_id
        LEFT OUTER JOIN campaign_types ct
            ON ct.campaign_type_id = campaigns.campaign_type_id
        LEFT OUTER JOIN campaign_statuses cs
            ON cs.campaign_status_id = campaigns.campaign_status_id
        WHERE campaigns.campaign_id = $campaign_id";


//    $sql = "SELECT * FROM campaigns WHERE campaign_id=$campaign_id";


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
 * Updates an campaign in XRMS from an associative array
 * Either an campaign_id must be explicitly set or an adodbrecordset for the record to be updated
 * must be passed in or the function will fail
 * Is a wrapper for add_update_campaign which can pull campaign_id from a recordset if provided
 *
 * @param adodbconnection  $con           ADOdb connection Object
 * @param array            $campaign_data  with associative array defining campaign data to update
 * @param integer          $campaign_id    optionally identifying campaign in the database (required if not passing in a recordset to $campaign_rst)
 * @param adodbrecordset   $campaign_rst   optionally providing a recordset to use for the update (required if not passing in an integer for $campaign_id)
 * @param boolean          $magic_quotes     F - inbound data is not magic_quoted by php, T - data is magic quoted
 *
 * @return boolean specifying if update succeeded
 */
function update_campaign($con, $campaign, $campaign_id = false, $campaign_rst = false, $magic_quotes=false)
{
    //if we're passed a recordset and it's not empty, populate campaign_id from it if it was not provided
    if ($campaign_rst AND !$campaign_rst->EOF AND !$campaign_id) {
         $campaign_id=$campaign_rst->fields['campaign_id'];
    }
    if ($campaign_id) $campaign['campaign_id']=$campaign_id;
    else return false;
    return add_update_campaign($con, $campaign, false, $magic_quotes);
};


/**********************************************************************/
/**
 *
 * Takes a campaign record array and adds a closed_by and closed_at fieldset based on the status of the campaign
 *
 * @param adodbconnection  $con           ADOdb connection Object
 * @param array            $rec  with associative array defining campaign data
 * @param integer          $status_id    identifying campaign status in the database
 *
 * @return array with original $rec with new keys 'closed_at' and 'closed_by' based on status provided
 */
function set_campaign_open_closed_by_status($con, $rec=false, $status_id=false) {
    global $session_user_id;
    if (!$rec OR !$status_id) return false;
    $status_data=get_entity_status($con, 'campaign',$status_id);
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
 * Deletes an campaign from XRMS, based on passed in campaign_id
 * Can delete campaign from database or mark as removed using record status
 *
 * @param adodbconnection  $con                   ADOdb connection Object
 * @param integer          $campaign_id            identifying which campaign to delete
 * @param boolean          $delete_from_database  specifying if campaign should be deleted from the database, or simply marked with a deleted flag (defaults to false, mark with deleted flag)
 *
 * @return boolean indicating success of delete operation
 */
function delete_campaign($con, $campaign_id, $delete_from_database = false)
{
    global $session_user_id;
    if (!$campaign_id) return false;
    if ($delete_from_database) {
        $sql = "DELETE FROM campaigns";
    } else {
        $sql = "UPDATE campaigns SET campaign_record_status=" . $con->qstr('d');
    }
    $sql .= "  WHERE campaign_id=$campaign_id";

    $rst=$con->execute($sql);
    if (!$rst) { db_error_handler($con, $sql); return false; }

    $campaign_data['campaign_id'] = $campaign_id;
    do_hook_function('campaign_delete', $campaign_data);

    add_audit_item($con, $session_user_id, 'deleted', 'campaigns', $campaign_id, 1);

    return true;
};

/**
 *
 * Advances the campaign workflow upon after a campaign workflow activity has been completed.
 *
 * @param adodbconnection   $con                    ADOdb connection Object
 * @param integer           $campaign_id            Identifies to which campaign does the workflow belong to
 * @param integer           $activity_template_id   Identifies where we are in the workflow
 * @param integer           $company_id             Identifies which company the activity relates to
 * @param integer           $contact_id             Identifies which contact the activity relates to
 *
 * @return boolean                                  Indicates whether the workflow processing operation was successful
 */
function campaign_workflow_activity_completed($con, $campaign_id, $activity_template_id, $company_id, $contact_id) {

    // We cannot proceed without any of the parameters so make sure they are all there
    if (!$campaign_id OR !$activity_template_id OR !$company_id OR !$contact_id)
        return false;

    // Retrieve all the activity statuses associated with this campaign_type_id
    $sql = "SELECT campaign_statuses.campaign_status_id
            FROM campaign_statuses, campaigns
            WHERE campaign_statuses.campaign_type_id = campaigns.campaign_type_id
            AND campaigns.campaign_id = $campaign_id
            ORDER BY campaign_statuses.sort_order";
    $campaign_statuses_rst = $con->Execute($sql);
    if (!$campaign_statuses_rst) {
        db_error_handler($con, $sql);
        return FALSE;
    }

    // Retrieve all the activity templates associated with each campaign status and sort them sequentially
    $activity_templates = array();
    while (!$campaign_statuses_rst->EOF) {
        $sql = "SELECT activity_template_id, sort_order
                FROM activity_templates
                WHERE on_what_table = 'campaign_statuses'
                AND on_what_id = ". $campaign_statuses_rst->fields['campaign_status_id'] ."
                ORDER BY sort_order";
        $rst = $con->Execute($sql);
        if (!$rst) {
            db_error_handler($con, $sql);
            return FALSE;
        }

        while (!$rst->EOF) {
            $activity_templates[] = array(
                                        (int)$rst->fields['activity_template_id'],
                                        (int)$campaign_statuses_rst->fields['campaign_status_id'],
                                        (int)$rst->fields['sort_order']);
            $rst->MoveNext();
        }
        $campaign_statuses_rst->MoveNext();
    }

    // Find the activity template which follows the currently used one
    $current_template = FALSE;
    foreach ($activity_templates as $activity_template) {
        if ($activity_template[0] == $activity_template_id)
            $current_template = $activity_template;
        elseif ($current_template) {
            $new_template_id = $activity_template[0];
            $new_status_id = $activity_template[1];
            $new_sort_order = $activity_template[2];
            break;
        }
    }

    // Create the new campaign workflow activity
    if ($new_template_id) {
        add_workflow_activity($con, 'campaign_statuses', $new_status_id, 'campaigns', $campaign_id, $company_id, $contact_id, $new_sort_order);
        return TRUE;

    } else {
        return FALSE;
    }
}

/**********************************************************************/
/** Include the misc utilities file */
include_once $include_directory . 'utils-misc.php';


// ============================================================================

 /**
 * $Log: utils-campaigns.php,v $
 * Revision 1.2  2010/12/16 14:25:06  gopherit
 * Eliminated an unnecessary SQL query in the campaign_workflow_activity_completed() method.
 *
 * Revision 1.1  2010/12/15 22:52:51  gopherit
 * Implemented advancing of the campaign workflow on campaign workflow activity completion.
 *
 *
 *
**/
 ?>