<?php
/**
 * Add a division to a company
 *
 * $Id: add-division.php,v 1.4 2004/06/12 05:03:16 introspectshun Exp $
 */

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();

$company_id = $_POST['company_id'];
$division_name = $_POST['division_name'];
$description = $_POST['description'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

//$con->debug = 1;

$sql = "SELECT * FROM company_division WHERE 1 = 2"; //select empty record as placeholder
$rst = $con->execute($sql);

$rec = array();
$rec['company_id'] = $company_id;
$rec['division_name'] = $division_name;
$rec['description'] = $description;
$rec['entered_at'] = $con->DBTimestamp(mktime());
$rec['entered_by'] = $session_user_id;
$rec['last_modified_at'] = $con->DBTimestamp(mktime());
$rec['last_modified_by'] = $session_user_id;

$ins = $con->GetInsertSQL($rst, $rec, get_magic_quotes_gpc());
$con->execute($ins);

$division_id = $con->insert_id();

add_audit_item($con, $session_user_id, 'created', 'company_division', $division_id, 1);

$con->close();

header("Location: divisions.php?msg=address_added&company_id=$company_id");

/**
 * $Log: add-division.php,v $
 * Revision 1.4  2004/06/12 05:03:16  introspectshun
 * - Now use ADODB GetInsertSQL, GetUpdateSQL, date and Concat functions.
 * - Corrected order of arguments to implode() function.
 *
 * Revision 1.3  2004/05/10 13:09:14  maulani
 * - add level to audit trail
 *
 * Revision 1.2  2004/03/26 20:55:59  maulani
 * - Add audit trail to company-related items
 * - Add phpdoc
 *
 * Revision 1.1  2004/01/26 19:18:02  braverock
 * - added company division pages and fields
 * - added phpdoc
 *
 */
?>