<?php
/**
 * Insert a new campaign status into the database
 *
 *
 */

require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check( 'Admin' );

$campaign_status_short_name = $_POST['campaign_status_short_name'];
$campaign_status_pretty_name = $_POST['campaign_status_pretty_name'];
$campaign_status_pretty_plural = $_POST['campaign_status_pretty_plural'];
$campaign_status_display_html = $_POST['campaign_status_display_html'];
$campaign_status_long_desc = $_POST['campaign_status_long_desc'];
$status_open_indicator = $_POST['status_open_indicator'];
$campaign_type_id = (int)$_POST['campaign_type_id'];
$sort_order             = $_POST['sort_order'];

// Only insert the record if we have at least a short name or pretty name
// @TODO: Should send a message to the user here giving them a clue if we are
// doing nothing
if ((strlen($campaign_status_short_name) > 0) OR (strlen($campaign_status_pretty_name))) {

    //set defaults if we didn't get everything we need
    if (strlen($campaign_status_pretty_name) == 0) {
        $campaign_status_pretty_name = $campaign_status_short_name;
    }
    if (strlen($campaign_status_pretty_plural) == 0) {
        $campaign_status_pretty_plural = $campaign_status_pretty_name;
    }
    if (strlen($campaign_status_display_html) == 0) {
        $campaign_status_display_html = $campaign_status_pretty_name;
    }

    $con = get_xrms_dbconnection();

    // It is useful to have $sort_order as a string so we can validate it here
    if ($sort_order == '') {
        // Get the last sort_order value so we can put the new record at the bottom of the list
        $sql = "SELECT sort_order
                FROM campaign_statuses
                WHERE campaign_status_record_status='a'
                AND campaign_type_id = $campaign_type_id
                ORDER BY sort_order DESC";
        $rst = $con->execute($sql);
        if (!$rst) {
            db_error_handler($con, $sql);
        } else {
            $sort_order = $rst->fields['sort_order'] + 1;
            $rst->close();
        }
    } else {
        $sort_order = (int)$sort_order;
    }

    //save to database
    $rec = array();
    $rec['campaign_status_short_name'] = $campaign_status_short_name;
    $rec['campaign_status_pretty_name'] = $campaign_status_pretty_name;
    $rec['campaign_status_pretty_plural'] = $campaign_status_pretty_plural;
    $rec['campaign_status_display_html'] = $campaign_status_display_html;
    $rec['campaign_status_long_desc'] = $campaign_status_long_desc;
    $rec['status_open_indicator'] = $status_open_indicator;
    $rec['campaign_type_id'] = $campaign_type_id;
    $rec['sort_order'] = $sort_order;

    $tbl = 'campaign_statuses';
    $ins = $con->GetInsertSQL($tbl, $rec, get_magic_quotes_gpc());
    $con->execute($ins);

    $con->close();
}
// Go back to the main campaign status page after updating
header("Location: some.php?campaign_type_id=$campaign_type_id");

?>