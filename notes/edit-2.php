<?php
/**
 * Commit and edited note to the database
 *
 * $Id: edit-2.php,v 1.3 2004/06/21 14:24:59 braverock Exp $
 */

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();

$note_id = $_POST['note_id'];
$note_description = $_POST['note_description'];
$return_url = $_POST['return_url'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

$sql = "SELECT * FROM notes WHERE note_id = $note_id";
$rst = $con->execute($sql);

$rec = array();
$rec['note_description'] = $note_description;

$upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
$con->execute($upd);

$con->close();

header("Location: " . $http_site_root . $return_url);

/**
 * $Log: edit-2.php,v $
 * Revision 1.3  2004/06/21 14:24:59  braverock
 * - localized strings for i18n/internationalization/translation support
 *
 */
?>