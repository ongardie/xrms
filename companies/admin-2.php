<?php
/**
 * Insert company admin items into the database
 *
 * $Id: admin-2.php,v 1.3 2004/03/26 20:55:59 maulani Exp $
 */
require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'utils-accounting.php');

$session_user_id = session_check();

$company_id = $_POST['company_id'];
$account_status_id = $_POST['account_status_id'];
$tax_id = $_POST['tax_id'];
$credit_limit = $_POST['credit_limit'];
$rating_id = $_POST['rating_id'];
$terms = $_POST['terms'];
$extref1 = $_POST['extref1'];
$extref2 = $_POST['extref2'];

$credit_limit = ($credit_limit > 0) ? $credit_limit : 0;
$terms = ($terms > 0) ? $terms : 0;

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

$sql = "update companies set account_status_id = $account_status_id, tax_id = " . $con->qstr($tax_id, get_magic_quotes_gpc()) . ", credit_limit = " . $con->qstr($credit_limit, get_magic_quotes_gpc()) . ", rating_id = $rating_id, terms = $terms, extref1 = " . $con->qstr($extref1, get_magic_quotes_gpc()) . ", extref2 = " . $con->qstr($extref2, get_magic_quotes_gpc()) . " where company_id = $company_id";
$con->execute($sql);

$sql = "select extref1, extref2 from companies where company_id = $company_id";
$rst = $con->execute($sql);
$extref1 = $rst->fields['extref1'];
$extref2 = $rst->fields['extref2'];
$rst->close();

add_audit_item($con, $session_user_id, 'edit company admin', 'companies', $company_id);

$con->close();

update_vendor_account_information($extref2, $vendor_credit_limit, $vendor_terms);
update_customer_account_information($extref1, $customer_credit_limit, $customer_terms);

header("Location: one.php?msg=saved&company_id=$company_id");

/**
 * $Log: admin-2.php,v $
 * Revision 1.3  2004/03/26 20:55:59  maulani
 * - Add audit trail to company-related items
 * - Add phpdoc
 *
 *
 */
?>
