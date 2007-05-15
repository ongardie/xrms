<?php
/**
 * Delete a division by setting its status
 *
 * $Id: delete-division.php,v 1.5 2007/05/15 23:17:30 ongardie Exp $
 */

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'utils-accounting.php');

$session_user_id = session_check();

$company_id = $_GET['company_id'];
$division_id = $_GET['division_id'];

$con = get_xrms_dbconnection();

$sql = "SELECT * FROM company_division WHERE division_id = $division_id";
$rst = $con->execute($sql);

$rec = array();
$rec['division_record_status'] = 'd';
$rec['last_modified_at'] = time();
$rec['last_modified_by'] = $session_user_id;

$upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
$con->execute($upd);

$con->close();

header("Location: divisions.php?msg=division_deleted&company_id=$company_id");

/**
 * $Log: delete-division.php,v $
 * Revision 1.5  2007/05/15 23:17:30  ongardie
 * - Addresses now associate with on_what_table, on_what_id instead of company_id.
 *
 * Revision 1.4  2006/01/02 22:56:26  vanmer
 * - changed to use centralized dbconnection function
 *
 * Revision 1.3  2004/06/12 17:10:24  gpowers
 * - removed DBTimeStamp() calls for compatibility with GetInsertSQL() and
 *   GetUpdateSQL()
 *
 * Revision 1.2  2004/06/12 05:03:16  introspectshun
 * - Now use ADODB GetInsertSQL, GetUpdateSQL, date and Concat functions.
 * - Corrected order of arguments to implode() function.
 *
 * Revision 1.1  2004/01/26 19:18:02  braverock
 * - added company division pages and fields
 * - added phpdoc
 *
 */
?>
