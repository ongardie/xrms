<?php
/**
 * save an updated an opportunity status  to database after editing it.
 *
 * $Id: edit-2.php,v 1.7 2006/01/02 21:59:08 vanmer Exp $
 */

require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check( 'Admin' );

$opportunity_status_id = $_POST['opportunity_status_id'];
$opportunity_status_short_name = $_POST['opportunity_status_short_name'];
$opportunity_status_pretty_name = $_POST['opportunity_status_pretty_name'];
$opportunity_status_pretty_plural = $_POST['opportunity_status_pretty_plural'];
$opportunity_status_display_html = $_POST['opportunity_status_display_html'];
$opportunity_status_long_desc = $_POST['opportunity_status_long_desc'];
$status_open_indicator = $_POST['status_open_indicator'];

$con = get_xrms_dbconnection();

//$con->debug=1;

$sql = "SELECT * FROM opportunity_statuses WHERE opportunity_status_id = $opportunity_status_id";
$rst = $con->execute($sql);

$rec = array();
$rec['opportunity_status_short_name'] = $opportunity_status_short_name;
$rec['opportunity_status_pretty_name'] = $opportunity_status_pretty_name;
$rec['opportunity_status_pretty_plural'] = $opportunity_status_pretty_plural;
$rec['opportunity_status_display_html'] = $opportunity_status_display_html;
$rec['opportunity_status_long_desc'] = $opportunity_status_long_desc;
$rec['status_open_indicator'] = $status_open_indicator;

$upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
$con->execute($upd);

$con->close();

//go back to the main opportunity status page after updating
header("Location: some.php");

/**
 * $Log: edit-2.php,v $
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