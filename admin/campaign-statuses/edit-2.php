<?php
/**
 * Save an updated campaign status to database after editing it.
 *
 * $Id: edit-2.php,v 1.7 2010/12/07 22:41:03 gopherit Exp $
 */

// Include required files
require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check( 'Admin' );

$campaign_status_id = (int)$_POST['campaign_status_id'];
$campaign_status_short_name = $_POST['campaign_status_short_name'];
$campaign_status_pretty_name = $_POST['campaign_status_pretty_name'];
$campaign_status_pretty_plural = $_POST['campaign_status_pretty_plural'];
$campaign_status_display_html = $_POST['campaign_status_display_html'];
$campaign_status_long_desc = $_POST['campaign_status_long_desc'];
$status_open_indicator = $_POST['status_open_indicator'];
$sort_order = $_POST['sort_order'];

$con = get_xrms_dbconnection();

// $con->debug = 1;

$sql = "SELECT * FROM campaign_statuses WHERE campaign_status_id = $campaign_status_id";
$rst = $con->execute($sql);

// It is useful to have $sort_order as a string so we can validate it here
if ($sort_order == '') {
    // Get the last sort_order value so we can put the new record at the bottom of the list
    $sort_order_sql = "SELECT sort_order
                        FROM campaign_statuses
                        WHERE campaign_status_record_status='a'
                        AND campaign_type_id = ". $rst->fields['campaign_type_id'] ."
                        AND campaign_status_id != $campaign_status_id
                        ORDER BY sort_order DESC";
    $sort_order_rst = $con->execute($sort_order_sql);
    if (!$sort_order_rst) {
        db_error_handler($con, $sort_order_rst);
    } else {
        $sort_order = $sort_order_rst->fields['sort_order'] + 1;
        $sort_order_rst->close();
    }
} else {
    $sort_order = (int)$sort_order;
}

// Get the opportunity_type_id so we can send the user back to where they came from
$campaign_type_id = $rst->fields['campaign_type_id'];

$rec = array();
$rec['campaign_status_short_name'] = $campaign_status_short_name;
$rec['campaign_status_pretty_name'] = $campaign_status_pretty_name;
$rec['campaign_status_pretty_plural'] = $campaign_status_pretty_plural;
$rec['campaign_status_display_html'] = $campaign_status_display_html;
$rec['campaign_status_long_desc'] = $campaign_status_long_desc;
$rec['status_open_indicator'] = $status_open_indicator;
$rec['sort_order'] = $sort_order;

$upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
$con->execute($upd);

$con->close();

// Go back to the main opportunity status page after updating
header("Location: some.php?campaign_type_id=".$campaign_type_id);
?>