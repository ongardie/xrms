<?php
/**
 * Mark a note as deleted
 *
 * $Id: delete-2.php,v 1.4 2005/02/11 00:49:11 braverock Exp $
 */

require_once('../../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');
require_once($include_directory . 'utils-accounting.php');

$session_user_id = session_check();

$return_url = $_GET['return_url'];
$info_id = $_GET['info_id'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
//$con->debug = 1;

$tbl = 'info';
$rec = array();
$rec['info_record_status'] = 'd';

if (!$con->AutoExecute($tbl, $rec, 'UPDATE', "info_id = $info_id")) {
    db_error_handler ($con, $ins);
}

$con->close();

header("Location: ".$http_site_root.$return_url);

/**
 * $Log: delete-2.php,v $
 * Revision 1.4  2005/02/11 00:49:11  braverock
 * - modified to correctly pass contact_id and return_url
 *
 * Revision 1.3  2005/02/10 13:42:18  braverock
 * - update to newest info plugin provided by Keith Edmunds
 *   - now uses ADOdb for database access
 *
 * Revision 1.2  2004/12/31 22:54:18  gpowers
 * - added ability to add info inside a larger content box
 *
 * Revision 1.1  2004/07/22 19:49:48  gpowers
 * - enables deletion of an info item
 *
 * Revision 1.2  2004/06/12 06:23:27  introspectshun
 * - Now use ADODB GetInsertSQL, GetUpdateSQL, date and Concat functions.
 *
 * Revision 1.1  2004/03/07 14:03:31  braverock
 * - add delete functionality to Notes
 *
 */
?>
