<?php
/**
 * Mark a note as deleted
 *
 * $Id: delete.php,v 1.2 2004/06/12 06:23:27 introspectshun Exp $
 */

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');
require_once($include_directory . 'utils-accounting.php');

$session_user_id = session_check();

$return_url = $_GET['return_url'];
$note_id = $_GET['note_id'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

$sql = "SELECT * FROM notes WHERE note_id = $note_id";
$rst = $con->execute($sql);

$rec = array();
$rec['note_record_status'] = 'd';

$upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
$con->execute($upd);

$con->close();

header("Location: {$http_site_root}/{$return_url}");

/**
 * $Log: delete.php,v $
 * Revision 1.2  2004/06/12 06:23:27  introspectshun
 * - Now use ADODB GetInsertSQL, GetUpdateSQL, date and Concat functions.
 *
 * Revision 1.1  2004/03/07 14:03:31  braverock
 * - add delete functionality to Notes
 *
 */
?>