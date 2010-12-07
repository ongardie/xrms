<?php
/**
 * Save an updated opportunity status to database after editing it.
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

$opportunity_status_id = (int)$_POST['opportunity_status_id'];
$opportunity_status_short_name = $_POST['opportunity_status_short_name'];
$opportunity_status_pretty_name = $_POST['opportunity_status_pretty_name'];
$opportunity_status_pretty_plural = $_POST['opportunity_status_pretty_plural'];
$opportunity_status_display_html = $_POST['opportunity_status_display_html'];
$opportunity_status_long_desc = $_POST['opportunity_status_long_desc'];
$status_open_indicator = $_POST['status_open_indicator'];
$sort_order = $_POST['sort_order'];

$con = get_xrms_dbconnection();

// $con->debug = 1;

$sql = "SELECT * FROM opportunity_statuses WHERE opportunity_status_id = $opportunity_status_id";
$rst = $con->execute($sql);

// It is useful to have $sort_order as a string so we can validate it here
if ($sort_order == '') {
    // Get the last sort_order value so we can put the new record at the bottom of the list
    $sort_order_sql = "SELECT sort_order
                        FROM opportunity_statuses
                        WHERE opportunity_status_record_status='a'
                        AND opportunity_type_id = ". $rst->fields['opportunity_type_id'] ."
                        AND opportunity_status_id != $opportunity_status_id
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
$opportunity_type_id = $rst->fields['opportunity_type_id'];

$rec = array();
$rec['opportunity_status_short_name'] = $opportunity_status_short_name;
$rec['opportunity_status_pretty_name'] = $opportunity_status_pretty_name;
$rec['opportunity_status_pretty_plural'] = $opportunity_status_pretty_plural;
$rec['opportunity_status_display_html'] = $opportunity_status_display_html;
$rec['opportunity_status_long_desc'] = $opportunity_status_long_desc;
$rec['status_open_indicator'] = $status_open_indicator;
$rec['sort_order'] = $sort_order;

$upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
$con->execute($upd);

$con->close();

// Go back to the main opportunity status page after updating
header("Location: some.php?opportunity_type_id=".$opportunity_type_id);

/**
 * $Log: edit-2.php,v $
 * Revision 1.10  2010/12/07 22:32:07  gopherit
 * Exposed the sort order of each workflow status so users can see it and modify it.
 *
 * Revision 1.9  2010/11/26 21:19:14  gopherit
 * Casted $_POST-ed integer values to (int) for increased security.
 *
 * Revision 1.8  2006/12/05 11:10:01  jnhayart
 * Add cosmetics display, and control localisation
 *
 * Revision 1.7  2006/01/02 21:59:08  vanmer
 * - changed to use centralized database connection function
 *
 * Revision 1.6  2004/07/16 23:51:37  cpsource
 * - require session_check ( 'Admin' )
 *
 * Revision 1.5  2004/06/14 22:36:43  introspectshun
 * - Add adodb-params.php include for multi-db compatibility.
 * - Now use ADODB GetInsertSQL, GetUpdateSQL functions.
 *
 * Revision 1.4  2004/03/15 16:49:55  braverock
 * - add sort_order and open status indicator to opportunity statuses
 *
 * Revision 1.3  2004/01/25 18:39:35  braverock
 * - fixed insert bugs so long_desc will be displayed and inserted properly
 * - added phpdoc
 *
 */
?>