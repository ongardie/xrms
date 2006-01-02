<?php
/**
 * Add Former Name
 *
 * $Id: delete-former-name.php,v 1.4 2006/01/02 22:56:26 vanmer Exp $
 */
require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();

$company_id = $_GET['company_id'];
$former_name = $_GET['former_name'];

$con = get_xrms_dbconnection();
// $con->debug = 1;

$sql = "delete from company_former_names where company_id = " . $company_id  . " AND former_name = " . $con->qstr($former_name, get_magic_quotes_gpc());

$con->execute($sql);

add_audit_item($con, $session_user_id, 'deleted', 'company_former_names', $company_id, 1);

$con->close();

header("Location: former-names.php?company_id=$company_id");

/**
 * $Log: delete-former-name.php,v $
 * Revision 1.4  2006/01/02 22:56:26  vanmer
 * - changed to use centralized dbconnection function
 *
 * Revision 1.3  2004/06/12 05:03:16  introspectshun
 * - Now use ADODB GetInsertSQL, GetUpdateSQL, date and Concat functions.
 * - Corrected order of arguments to implode() function.
 *
 * Revision 1.2  2004/05/10 13:09:14  maulani
 * - add level to audit trail
 *
 * Revision 1.1  2004/05/06 13:35:54  gpowers
 * This implements the deletion of Former Names.
 *
 * Revision 1.2  2004/03/26 20:55:59  maulani
 * - Add audit trail to company-related items
 * - Add phpdoc
 *
 *
 */
?>