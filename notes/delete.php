<?php
/**
 * Mark a note as deleted
 *
 * $Id: delete.php,v 1.1 2004/03/07 14:03:31 braverock Exp $
 */

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'utils-accounting.php');

$session_user_id = session_check();

$return_url = $_GET['return_url'];
$note_id = $_GET['note_id'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

$sql = "update notes set note_record_status = 'd' where note_id = $note_id";
$con->execute($sql);

$con->close();

header("Location: {$http_site_root}/{$return_url}");

/**
 * $Log: delete.php,v $
 * Revision 1.1  2004/03/07 14:03:31  braverock
 * - add delete functionality to Notes
 *
 */
?>