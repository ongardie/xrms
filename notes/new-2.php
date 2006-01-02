<?php
/**
 * Insert a new Note into the Database
 *
 * $Id: new-2.php,v 1.6 2006/01/02 23:29:27 vanmer Exp $
 */

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();
$return_url = $_POST['return_url'];

$on_what_table = $_POST['on_what_table'];
$on_what_id = $_POST['on_what_id'];
$note_description = $_POST['note_description'];

$con = get_xrms_dbconnection();
// $con->debug = 1;

//save to database
$rec = array();
$rec['on_what_table'] = $on_what_table;
$rec['on_what_id'] = $on_what_id;
$rec['note_description'] = $note_description;
$rec['entered_at'] = time();
$rec['entered_by'] = $session_user_id;

$tbl = 'notes';
$ins = $con->GetInsertSQL($tbl, $rec, get_magic_quotes_gpc());
$con->execute($ins);

$con->close();

header("Location: " . $http_site_root . $return_url);

/**
 * $Log: new-2.php,v $
 * Revision 1.6  2006/01/02 23:29:27  vanmer
 * - changed to use centralized dbconnection function
 *
 * Revision 1.5  2004/07/07 22:37:40  introspectshun
 * - Now passes a table name instead of a recordset into GetInsertSQL
 *
 * Revision 1.4  2004/06/21 14:25:00  braverock
 * - localized strings for i18n/internationalization/translation support
 *
 */
?>