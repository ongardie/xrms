<?php
/**
 *
 * /campaigns/lists/delete.php - Delete a campaign list
 *
 * $Id: delete.php,v 1.1 2011/01/14 15:51:28 gopherit Exp $
 */

// Where do we include from
require_once('../../include-locations.inc');

// Include necessary files
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

// Ensure we have a valid logged-in user with Delete permissions
$session_user_id = session_check('','Delete');

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
$sql = 'SELECT campaign_list_record_status
        FROM campaign_lists
        WHERE campaign_list_record_status = \'a\'
        AND campaign_list_id = '. $campaign_list_id;
$rst = $con->Execute($sql);

if ($rst AND !$rst->EOF) {

    // Prepare the new record
    $rec = array();
    $rec = $rst->fields;

    // Mark the campaign list record as deleted
    $rec['campaign_list_record_status'] = 'd';

    // Update the record in the database
    $ins = $con->GetUpdateSQL($rst, $rec, FALSE, get_magic_quotes_gpc());
    $rst = $con->Execute($ins);

    // We do not need the database connection anymore
    $con->close();

    header('Location: '. $http_site_root . $return_url .'&msg='. _('Campaign list successfully deleted'));

} else {
    if (!$rst) {
        db_error_handler ($con, $sql);
    } else {
        header("Location: $http_site_root$return_url&msg=". _('Campaign list deletion failed.') .' '. _('Could not find the campaign list requested.'));
        exit;

    }
}


 /**
  * $Log: delete.php,v $
  * Revision 1.1  2011/01/14 15:51:28  gopherit
  * Implemented the Campaign Lists functionality to allow launching of campaign workflows on lists of contacts created with /contacts/some.php
  *
  *
  */
?>