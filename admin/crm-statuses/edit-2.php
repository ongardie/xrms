<?php
/**
 * Insert the updated information into the database
 *
 * $Id: edit-2.php,v 1.2 2004/03/22 02:52:36 braverock Exp $
 */

// include required files
require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

$session_user_id = session_check();

$crm_status_id = $_POST['crm_status_id'];
$crm_status_short_name = $_POST['crm_status_short_name'];
$crm_status_pretty_name = $_POST['crm_status_pretty_name'];
$crm_status_pretty_plural = $_POST['crm_status_pretty_plural'];
$crm_status_display_html = $_POST['crm_status_display_html'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

$sql = "update crm_statuses set crm_status_short_name = " . $con->qstr($crm_status_short_name, get_magic_quotes_gpc()) . ", crm_status_pretty_name = " . $con->qstr($crm_status_pretty_name, get_magic_quotes_gpc()) . ", crm_status_pretty_plural = " . $con->qstr($crm_status_pretty_plural, get_magic_quotes_gpc()) . ", crm_status_display_html = " . $con->qstr($crm_status_display_html, get_magic_quotes_gpc()) . " WHERE crm_status_id = $crm_status_id";
$con->execute($sql);

$con->close();

header("Location: some.php");

/**
 * $Log: edit-2.php,v $
 * Revision 1.2  2004/03/22 02:52:36  braverock
 * - redirect to some.php
 *
 */
?>