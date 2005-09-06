<?php
/**
 * Insert company details into the database
 *
 * $Id: edit-2.php,v 1.19 2005/09/06 17:33:34 ycreddy Exp $
 */
require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');
require_once($include_directory . 'utils-accounting.php');

$company_id = $_POST['company_id'];
$on_what_id=$company_id;

$session_user_id = session_check('','Update');

$company_name = $_POST['company_name'];
$legal_name = $_POST['legal_name'];
$company_code = $_POST['company_code'];
$crm_status_id = $_POST['crm_status_id'];
$company_source_id = $_POST['company_source_id'];
$industry_id = $_POST['industry_id'];
$rating_id = $_POST['rating_id'];
$user_id = $_POST['user_id'];
$phone = preg_replace("/[^\d]/", '', $_POST['phone']);
$phone2 = preg_replace("/[^\d]/", '',$_POST['phone2']);
$fax = preg_replace("/[^\d]/", '',$_POST['fax']);
$url = $_POST['url'];
$employees = $_POST['employees'];
$revenue = $_POST['revenue'];
$profile = $_POST['profile'];
$custom1 = $_POST['custom1'];
$custom2 = $_POST['custom2'];
$custom3 = $_POST['custom3'];
$custom4 = $_POST['custom4'];

if (!$rating_id) { $rating_id = 0; }

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

// $con->debug=1;

$sql = "SELECT * FROM companies WHERE company_id = $company_id";
$rst = $con->execute($sql);

$rec = array();
$rec['last_modified_by'] = $session_user_id;
$rec['last_modified_at'] = mktime();
$rec['crm_status_id'] = $crm_status_id;
$rec['company_source_id'] = $company_source_id;
$rec['industry_id'] = $industry_id;
$rec['rating_id'] = $rating_id;
$rec['user_id'] = $user_id;
$rec['company_name'] = $company_name;
$rec['legal_name'] = $legal_name;
$rec['company_code'] = $company_code;
$rec['phone'] = $phone;
$rec['phone2'] = $phone2;
$rec['fax'] = $fax;
$rec['url'] = $url;
$rec['employees'] = $employees;
$rec['revenue'] = $revenue;
$rec['custom1'] = $custom1;
$rec['custom2'] = $custom2;
$rec['custom3'] = $custom3;
$rec['custom4'] = $custom4;
$rec['profile'] = $profile;

$upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
$sysst = $con->execute($upd);
if (!$sysst){
	//there was a problem, notify the user
	db_error_handler ($con, $upd);
}

$param = array($rst, $rec);
do_hook_function('company_edit_2', $param);

$accounting_rows = do_hook_function('company_accounting_inline_edit_2', $accounting_rows);

add_audit_item($con, $session_user_id, 'updated', 'companies', $company_id, 1);

$con->close();

header("Location: one.php?msg=saved&company_id=$company_id");

/**
 * $Log: edit-2.php,v $
 * Revision 1.19  2005/09/06 17:33:34  ycreddy
 * Added code to Strip off non digit characters from Fax
 *
 * Revision 1.18  2005/07/24 20:37:55  maulani
 * - Add db_error_handler call after database call
 *
 * Revision 1.17  2005/06/05 17:21:37  braverock
 * - add standard new/edit hooks
 *
 * Revision 1.16  2005/04/26 17:28:04  gpowers
 * - added Extension ("x") to contact work phone
 * - removed non-digits from phone numbers in edit-2's, new-2's
 * - updated work phone display to include Extension
 *
 * Revision 1.15  2005/03/18 20:53:29  gpowers
 * - added hooks for inline info plugin
 *
 * Revision 1.14  2005/01/13 18:20:28  vanmer
 * - Basic ACL changes to allow create/delete/update functionality to be restricted
 *
 * Revision 1.13  2004/06/15 13:50:46  gpowers
 * - forgot to disable debug in previous commit
 *
 * Revision 1.12  2004/06/15 13:49:21  gpowers
 * - removed dbtimestamp() function, b/c it didn't work with MySQL.
 *
 * Revision 1.11  2004/06/12 05:03:16  introspectshun
 * - Now use ADODB GetInsertSQL, GetUpdateSQL, date and Concat functions.
 * - Corrected order of arguments to implode() function.
 *
 * Revision 1.10  2004/05/10 13:09:14  maulani
 * - add level to audit trail
 *
 * Revision 1.9  2004/03/26 20:55:59  maulani
 * - Add audit trail to company-related items
 * - Add phpdoc
 *
 * Revision 1.8  2004/02/14 15:27:19  braverock
 * - add ratings to the editing of companies
 *
 * Revision 1.7  2004/02/10 17:53:44  maulani
 * Set last modified user and date
 *
 * Revision 1.6  2004/01/26 19:18:29  braverock
 * - cleaned up sql format
 * - added phpdoc
 *
 */
?>
