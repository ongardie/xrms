<?php
/**
 * Add Former Name
 *
 * $Id: add-former-name.php,v 1.3 2004/05/06 13:33:39 gpowers Exp $
 */
require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

$session_user_id = session_check();

$company_id = $_POST['company_id'];
$former_name = $_POST['former_name'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
// $con->debug = 1;

$sql = "insert into company_former_names (company_id, namechange_at, former_name) values ($company_id, now(), " . $con->qstr($former_name, get_magic_quotes_gpc()) . ")";

$con->execute($sql);

add_audit_item($con, $session_user_id, 'add former name', 'companies', $company_id);

$con->close();

header("Location: former-names.php?company_id=$company_id");

/**
 * $Log: add-former-name.php,v $
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
