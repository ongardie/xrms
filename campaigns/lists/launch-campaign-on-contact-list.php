<?php
/**
 *
 * /campaigns/lists/launch-campaign-on-contact-list.php - Initiate the campaign
 *  workflow on all contacts listed in the selected campaign list
 *
 * $Id: launch-campaign-on-contact-list.php,v 1.1 2011/01/14 15:51:28 gopherit Exp $
 */

// Where do we include from
require_once('../../include-locations.inc');

// Include necessary files
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'utils-workflow.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

// Ensure we have a valid logged-in user with 'Create' permissions
$session_user_id = session_check('','Create');

// Passed-in parameters
$campaign_list_id = (int)$_GET['campaign_list_id'];
$return_url = $_GET['return_url'];
// /campaigns/lists/some.php is the only place this script is called so we can
// let the return_url flow right back to it
$return_url = "/campaigns/lists/some.php?return_url=$return_url";

// Without a campaign_list_id, we cannot do anything
if (!$campaign_list_id) {
    header("Location: " . $http_site_root . $return_url);
    exit;
}

// Get the database connection
$con=get_xrms_dbconnection();
//$con->debug = 1;

// Retrieve the campaign list
$sql = 'SELECT  cl.campaign_list_id,
                cl.user_id,
                cl.campaign_id,
                cl.target_contact_ids,
                cl.list_processing_started_on,
                cl.list_processing_started_by,
                cl.list_processing_ended_on,
                c.campaign_status_id
        FROM campaign_lists cl
        LEFT JOIN campaigns c
            ON cl.campaign_id = c.campaign_id
        WHERE campaign_list_record_status = \'a\'
        AND campaign_list_id = '. $campaign_list_id;
$rst = $con->Execute($sql);

if ($rst AND !$rst->EOF) {

    ///// Ensure we have everything we need /////
    if (!$rst->fields['campaign_id']) {
        header("Location: $http_site_root$return_url&msg=". _('Campaign list processing failed.') .' '. _('List is not attached to a campaign.'));
        exit;
    }
    if (!$rst->fields['user_id']) {
        header("Location: $http_site_root$return_url&msg=". _('Campaign list processing failed.') .' '. _('List is not assigned to a user.'));
        exit;
    }
    if ($rst->fields['user_id'] AND ($rst->fields['user_id'] != $session_user_id)) {
        header("Location: $http_site_root$return_url&msg=". _('Campaign list processing failed.') .' '. _('List assigned to another user.'));
        exit;
    }
    if ($rst->fields['list_processing_ended_on']) {
        header("Location: $http_site_root$return_url&msg=". _('This campaign list has already been processed.'));
        exit;
    }
    if ($rst->fields['list_processing_started_on']) {
        header("Location: $http_site_root$return_url&msg=". _('Processing of this campaign list has already been initiated.'));
        exit;
    }
    $target_contact_ids = explode(',', $rst->fields['target_contact_ids']);
    if (!is_array($target_contact_ids) OR count($target_contact_ids) < 1) {
        header("Location: $http_site_root$return_url&msg=". _('Campaign list processing failed.') .' '. _('List does not contain any contact targets.'));
        exit;
    }

    // Everything seems fine so proceed with the campaing launch on this list
    $list_processing_started_on = time();

    // Shared parameters
    $on_what_status_table = 'campaign_statuses';
    $campaign_id = $rst->fields['campaign_id'];
    $campaign_status_id = $rst->fields['campaign_status_id'];

    // Attach the initial campaign workflow activity to each target contact
    foreach ($target_contact_ids as $target_contact_id) {
        // Find the company_id for each target contact
        $sql = "SELECT company_id
                FROM contacts
                WHERE contact_id = $target_contact_id";
        $rst = $con->Execute($sql);
        $target_company_id = $rst->fields['company_id'];

        // Finally, attach the initial campaing workflow activity
        add_workflow_activity($con, $on_what_status_table, $campaign_status_id, 'campaigns', $campaign_id, $target_company_id, $target_contact_id);

    }

    // Timestamp the end of list processing
    $list_processing_ended_on = time();

    // And store the timestamps in the database
    $sql = "UPDATE campaign_lists
            SET list_processing_started_on = $list_processing_started_on,
                list_processing_started_by = $session_user_id,
                list_processing_ended_on = $list_processing_ended_on
            WHERE campaign_list_id = $campaign_list_id";
    $rst = $con->Execute($sql);

    // We do not need the database connection anymore
    $con->close();

    header('Location: '. $http_site_root . $return_url);

} else {
    if (!$rst) {
        db_error_handler ($con, $sql);
    } else {
        header("Location: $http_site_root$return_url&msg=". _('Campaign list processing failed.') .' '. _('Could not find the campaign list requested.'));
        exit;

    }
}

 /**
  * $Log: launch-campaign-on-contact-list.php,v $
  * Revision 1.1  2011/01/14 15:51:28  gopherit
  * Implemented the Campaign Lists functionality to allow launching of campaign workflows on lists of contacts created with /contacts/some.php
  *
  *
  */
?>