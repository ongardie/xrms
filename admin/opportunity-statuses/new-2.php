<?php
/**
 * Insert a new opportunity status into the database
 *
 * $Id: new-2.php,v 1.6 2004/06/14 22:36:43 introspectshun Exp $
 */

require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();

$opportunity_status_short_name = $_POST['opportunity_status_short_name'];
$opportunity_status_pretty_name = $_POST['opportunity_status_pretty_name'];
$opportunity_status_pretty_plural = $_POST['opportunity_status_pretty_plural'];
$opportunity_status_display_html = $_POST['opportunity_status_display_html'];
$opportunity_status_long_desc = $_POST['opportunity_status_long_desc'];
$status_open_indicator = $_POST['status_open_indicator'];

//set defaults if we didn't get everything we need
if (strlen($opportunity_status_pretty_plural) > 0) {
    $opportunity_status_pretty_plural = $opportunity_status_pretty_plural;
} else {
    $opportunity_status_pretty_plural = $opportunity_status_pretty_name;
}
if (strlen($opportunity_status_display_html) > 0) {
    $opportunity_status_display_html = $opportunity_status_display_html;
} else {
    $opportunity_status_display_html = $opportunity_status_pretty_name;
}

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);


//get next sort_order value, put it at the bottom of the list
$sql = "select sort_order from opportunity_statuses where opportunity_status_record_status='a' order by sort_order desc";
$rst = $con->execute($sql);
$sort_order = $rst->fields['sort_order'] + 1;

$sql = "SELECT * FROM opportunity_statuses WHERE 1 = 2"; //select empty record as placeholder
$rst = $con->execute($sql);

$rec = array();
$rec['opportunity_status_short_name'] = $opportunity_status_short_name;
$rec['opportunity_status_pretty_name'] = $opportunity_status_pretty_name;
$rec['opportunity_status_pretty_plural'] = $opportunity_status_pretty_plural;
$rec['opportunity_status_display_html'] = $opportunity_status_display_html;
$rec['opportunity_status_long_desc'] = $opportunity_status_long_desc;
$rec['status_open_indicator'] = $status_open_indicator;
$rec['sort_order'] = $sort_order;

$ins = $con->GetInsertSQL($rst, $rec, get_magic_quotes_gpc());
$con->execute($ins);

$con->close();

//go back to the main opportunity status page after updating
header("Location: some.php");

/**
 * $Log: new-2.php,v $
 * Revision 1.6  2004/06/14 22:36:43  introspectshun
 * - Add adodb-params.php include for multi-db compatibility.
 * - Now use ADODB GetInsertSQL, GetUpdateSQL functions.
 *
 * Revision 1.5  2004/06/03 16:13:22  braverock
 * - add functionality to support workflow and activity templates
 * - add functionality to support changing sort order
 *   - functionality contributed by Brad Marshall
 *
 * Revision 1.4  2004/03/15 16:49:56  braverock
 * - add sort_order and open status indicator to opportunity statuses
 *
 * Revision 1.3  2004/01/25 18:39:40  braverock
 * - fixed insert bugs so long_desc will be disoplayed and inserted properly
 * - added phpdoc
 *
 */
?>
