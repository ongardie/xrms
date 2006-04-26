<?php
/**
 * Insert company details into the database
 *
 * $Id: edit-2.php,v 1.22 2006/04/26 20:07:29 braverock Exp $
 */
require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');
require_once($include_directory . 'utils-accounting.php');
require_once($include_directory . 'utils-companies.php');

$rec = array();
$rec['company_id'] = $_POST['company_id'];

$company_id=$rec['company_id'];

$session_user_id = session_check('','Update');

$rec['company_name'] = $_POST['company_name'];
$rec['legal_name'] = $_POST['legal_name'];
$rec['company_code'] = $_POST['company_code'];
$rec['crm_status_id'] = $_POST['crm_status_id'];
$rec['company_source_id'] = $_POST['company_source_id'];
$rec['industry_id'] = $_POST['industry_id'];
$rec['rating_id'] = $_POST['rating_id'];
$rec['user_id'] = $_POST['user_id'];
$rec['phone'] = preg_replace("/[^\d]/", '', $_POST['phone']);
$rec['phone2'] = preg_replace("/[^\d]/", '',$_POST['phone2']);
$rec['fax'] = preg_replace("/[^\d]/", '',$_POST['fax']);
$rec['url'] = $_POST['url'];
$rec['employees'] = $_POST['employees'];
$rec['revenue'] = $_POST['revenue'];
$rec['profile'] = $_POST['profile'];
$rec['custom1'] = $_POST['custom1'];
$rec['custom2'] = $_POST['custom2'];
$rec['custom3'] = $_POST['custom3'];
$rec['custom4'] = $_POST['custom4'];
$rec['account_status_id'] = $_POST['account_status_id'];
$rec['tax_id'] = $_POST['tax_id'];
$rec['credit_limit'] = $_POST['credit_limit'];
$rec['rating_id'] = $_POST['rating_id'];
$rec['terms'] = $_POST['terms'];
$rec['extref1'] = $_POST['extref1'];
$rec['extref2'] = $_POST['extref2'];

//set some values that can't be NULL
$rec['credit_limit'] = ($rec['credit_limit'] > 0) ? $rec['credit_limit'] : 0;
$rec['terms'] = ($rec['terms'] > 0) ? $rec['terms'] : 0;
$con = get_xrms_dbconnection();

// $con->debug=1;
update_company($con, $rec, $rec['company_id'], false, get_magic_quotes_gpc());

$accounting_rows = do_hook_function('company_accounting_inline_edit_2', $accounting_rows);

/*
    // these will probably go away soon or be moved to a hook, since they aren't implemented anyway
        // update_vendor_account_information($extref2, $vendor_credit_limit, $vendor_terms);
        // update_customer_account_information($extref1, $customer_credit_limit, $customer_terms);
    // placed here as reminders to normalize how we're going to deal with accounting data
*/

$con->close();

header("Location: one.php?msg=saved&company_id=$company_id");

/**
 * $Log: edit-2.php,v $
 * Revision 1.22  2006/04/26 20:07:29  braverock
 * - move accounting and credit fields from old companies/admin* pages
 *
 * Revision 1.21  2006/04/21 23:21:40  braverock
 * - first revision to use update_company api fn
 *
 * Revision 1.20  2006/01/02 22:56:27  vanmer
 * - changed to use centralized dbconnection function
 *
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
 */
?>
