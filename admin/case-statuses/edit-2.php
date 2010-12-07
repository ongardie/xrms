<?php
/**
 * Save an updated case status to database after editing it.
 *
 * $Id: edit-2.php,v 1.10 2010/12/07 22:32:07 gopherit Exp $
 */

// Include required files
require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check( 'Admin' );

$case_status_id = (int)$_POST['case_status_id'];
$case_status_short_name = $_POST['case_status_short_name'];
$case_status_pretty_name = $_POST['case_status_pretty_name'];
$case_status_pretty_plural = $_POST['case_status_pretty_plural'];
$case_status_display_html = $_POST['case_status_display_html'];
$case_status_long_desc = $_POST['case_status_long_desc'];
$status_open_indicator = $_POST['status_open_indicator'];
$sort_order = $_POST['sort_order'];

$con = get_xrms_dbconnection();

// $con->debug = 1;

$sql = "SELECT * FROM case_statuses WHERE case_status_id = $case_status_id";
$rst = $con->execute($sql);

// It is useful to have $sort_order as a string so we can validate it here
if ($sort_order == '') {
    // Get the last sort_order value so we can put the new record at the bottom of the list
    $sort_order_sql = "SELECT sort_order
                        FROM case_statuses
                        WHERE case_status_record_status='a'
                        AND case_type_id = ". $rst->fields['case_type_id'] ."
                        AND case_status_id != $case_status_id
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
$case_type_id = $rst->fields['case_type_id'];

$rec = array();
$rec['case_status_short_name'] = $case_status_short_name;
$rec['case_status_pretty_name'] = $case_status_pretty_name;
$rec['case_status_pretty_plural'] = $case_status_pretty_plural;
$rec['case_status_display_html'] = $case_status_display_html;
$rec['case_status_long_desc'] = $case_status_long_desc;
$rec['status_open_indicator'] = $status_open_indicator;
$rec['sort_order'] = $sort_order;

$upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
$con->execute($upd);

$con->close();

// Go back to the main case status page after updating
header("Location: some.php?case_type_id=".$case_type_id);

/**
 * $Log: edit-2.php,v $
 * Revision 1.10  2010/12/07 22:32:07  gopherit
 * Exposed the sort order of each workflow status so users can see it and modify it.
 *
 * Revision 1.9  2010/11/26 21:22:06  gopherit
 * Casted $_POST-ed integer values to (int) for increased security.
 *
 * Revision 1.8  2006/01/02 21:41:51  vanmer
 * - changed to use centralized dbconnection function
 *
 * Revision 1.7  2005/01/10 23:34:05  vanmer
 * - added open_indicator to status save
 *
 * Revision 1.6  2005/01/10 21:38:56  vanmer
 * - added case_type, needed for distinguishing between statuses
 *
 * Revision 1.5  2004/12/31 17:52:56  braverock
 * - add description for consistency
 *
 * Revision 1.4  2004/07/16 23:51:35  cpsource
 * - require session_check ( 'Admin' )
 *
 * Revision 1.3  2004/06/14 21:37:55  introspectshun
 * - Add adodb-params.php include for multi-db compatibility.
 * - Now use ADODB GetInsertSQL, GetUpdateSQL functions.
 *
 * Revision 1.2  2004/03/22 02:52:59  braverock
 * - redirect to some.php
 * - add phpdoc
 *
 */
?>