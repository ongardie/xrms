<?php
/**
 * Insert company admin items into the database
 *
 * $Id: admin-2.php,v 1.6 2006/01/02 22:56:26 vanmer Exp $
 */
require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');
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

$con = get_xrms_dbconnection();

$sql = "SELECT * FROM companies WHERE company_id = $company_id";
$rst = $con->execute($sql);

$rec = array();
$rec['account_status_id'] = $account_status_id;
$rec['tax_id'] = $tax_id;
$rec['credit_limit'] = $credit_limit;
$rec['rating_id'] = $rating_id;
$rec['terms'] = $terms;
$rec['extref1'] = $extref1;
$rec['extref2'] = $extref2;

$upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
$con->execute($upd);

$sql = "select extref1, extref2 from companies where company_id = $company_id";
$rst = $con->execute($sql);
$extref1 = $rst->fields['extref1'];
$extref2 = $rst->fields['extref2'];
$rst->close();

add_audit_item($con, $session_user_id, 'updated company admin', 'companies', $company_id, 1);

$con->close();

update_vendor_account_information($extref2, $vendor_credit_limit, $vendor_terms);
update_customer_account_information($extref1, $customer_credit_limit, $customer_terms);

header("Location: one.php?msg=saved&company_id=$company_id");

/**
 * $Log: admin-2.php,v $
 * Revision 1.6  2006/01/02 22:56:26  vanmer
 * - changed to use centralized dbconnection function
 *
 * Revision 1.5  2004/06/12 05:03:16  introspectshun
 * - Now use ADODB GetInsertSQL, GetUpdateSQL, date and Concat functions.
 * - Corrected order of arguments to implode() function.
 *
 * Revision 1.4  2004/05/10 13:09:14  maulani
 * - add level to audit trail
 *
 * Revision 1.3  2004/03/26 20:55:59  maulani
 * - Add audit trail to company-related items
 * - Add phpdoc
 *
 *
 */
?>