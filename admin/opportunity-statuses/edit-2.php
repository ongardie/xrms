<?php
/**
 * update an opportunity status after editing it.
 *
 * $Id: edit-2.php,v 1.3 2004/01/25 18:39:35 braverock Exp $
 */

require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

$session_user_id = session_check();

$opportunity_status_id = $_POST['opportunity_status_id'];
$opportunity_status_short_name = $_POST['opportunity_status_short_name'];
$opportunity_status_pretty_name = $_POST['opportunity_status_pretty_name'];
$opportunity_status_pretty_plural = $_POST['opportunity_status_pretty_plural'];
$opportunity_status_display_html = $_POST['opportunity_status_display_html'];
$opportunity_status_long_desc = $_POST['opportunity_status_long_desc'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

//$con->debug=1;

$sql = "update opportunity_statuses set
        opportunity_status_short_name = " . $con->qstr($opportunity_status_short_name, get_magic_quotes_gpc()) . ",
        opportunity_status_pretty_name = " . $con->qstr($opportunity_status_pretty_name, get_magic_quotes_gpc()) . ",
        opportunity_status_pretty_plural = " . $con->qstr($opportunity_status_pretty_plural, get_magic_quotes_gpc()) . ",
        opportunity_status_display_html = " . $con->qstr($opportunity_status_display_html, get_magic_quotes_gpc()) . ",
        opportunity_status_long_desc = " . $con->qstr($opportunity_status_long_desc, get_magic_quotes_gpc()) . "
        WHERE opportunity_status_id = $opportunity_status_id";

$con->execute($sql);

$con->close();

//go back to the main opportunity status page after updating
header("Location: some.php");

/**
 * $Log: edit-2.php,v $
 * Revision 1.3  2004/01/25 18:39:35  braverock
 * - fixed insert bugs so long_desc will be disoplayed and inserted properly
 * - added phpdoc
 *
 */
?>
