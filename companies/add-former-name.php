<?php
/**
 * Add Former Name
 *
 * $Id: add-former-name.php,v 1.5 2004/06/12 05:03:16 introspectshun Exp $
 */
require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();

$company_id = $_POST['company_id'];
$former_name = $_POST['former_name'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
// $con->debug = 1;

$sql = "SELECT * FROM company_former_names WHERE 1 = 2"; //select empty record as placeholder
$rst = $con->execute($sql);

$rec = array();
$rec['company_id'] = $company_id;
$rec['namechange_at'] = $con->DBTimestamp(mktime());
$rec['former_name'] = $former_name;

$ins = $con->GetInsertSQL($rst, $rec, get_magic_quotes_gpc());
$con->execute($ins);

add_audit_item($con, $session_user_id, 'created', 'company_former_names', $company_id, 1);

$con->close();

header("Location: former-names.php?company_id=$company_id");

/**
 * $Log: add-former-name.php,v $
 * Revision 1.5  2004/06/12 05:03:16  introspectshun
 * - Now use ADODB GetInsertSQL, GetUpdateSQL, date and Concat functions.
 * - Corrected order of arguments to implode() function.
 *
 * Revision 1.4  2004/05/10 13:09:14  maulani
 * - add level to audit trail
 *
 * Revision 1.3  2004/05/06 13:33:39  gpowers
 * changed return URL to former-names.php
 *
 * Revision 1.2  2004/03/26 20:55:59  maulani
 * - Add audit trail to company-related items
 * - Add phpdoc
 *
 *
 */
?>