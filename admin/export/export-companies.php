<?php
/**
 * Export all contacts with all fileds and Company information
 *
 * @author Fontain Consulting Group (France)
 *
 * @todo include division namer export when contact is associated with a Division
 */

//include required files
require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb/toexport.inc.php');
require_once($include_directory . 'adodb-params.php');

$sql = " SELECT
  cont.salutation AS 'Salutation',
  cont.last_name AS 'Last Name',
  cont.first_names AS 'First Names',
  cont.gender AS 'Gender',
  cont.date_of_birth AS 'Date of Birth',
  cont.summary AS 'Summary',
  cont.title AS 'Title',
  cont.description AS 'Description',
  cont.email AS 'Email',
  cont.work_phone AS 'Work Phone',
  cont.cell_phone AS 'Cell Phone',
  cont.home_phone AS 'Home Phone',
  cont.fax AS 'Fax',
  cont.aol_name AS 'AOL',
  cont.yahoo_name AS 'Yahoo',
  cont.msn_name AS 'MSN',
  cont.interests AS 'Interests',
  cont.profile AS 'Profile',
  cont.custom1 AS 'Custom1',
  cont.custom2 AS 'Custom2',
  cont.custom3 AS 'Custom3',
  cont.custom4 AS 'Custom4',
  c.company_code AS 'Company Code',
  c.company_name AS 'Company',
  c.legal_name AS 'Legal Name',
  c.tax_id AS 'Tax',
  c.profile AS 'Profile',
  c.phone AS 'Company Phone',
  c.phone2 AS 'Alt. Company Phone',
  c.fax AS 'Company Fax',
  c.url AS 'URL',
  c.employees AS 'No of Employees',
  c.revenue AS 'Revenue',
  c.credit_limit AS 'Credit Limit',
  c.terms AS 'Terms',
  c.custom1 AS 'Company Custom1',
  c.custom2 AS 'Company Custom2',
  c.custom3 AS 'Company Custom3',
  c.custom4 AS 'Company Custom4',
  c.extref1 AS 'Ext Ref 1',
  c.extref2 AS 'Ext Ref 2',
  c.extref3 AS 'Ext Ref 3',
  a.address_body AS 'Address Body',
  a.line1 AS 'Address Line 1',
  a.line2 AS 'Address Line 2',
  a.city AS 'City',
  a.province AS 'Province',
  a.postal_code AS 'Postal Code',
  a.use_pretty_address AS 'Use Pretty Address',
  cs.company_source_pretty_name AS 'Company Source',
  i.industry_pretty_name AS 'Industry',
  crm.crm_status_pretty_name AS 'CRM Status',
  ast.account_status_pretty_name AS 'Account Status',
  coun.country_name AS 'Country'
FROM contacts cont, companies c, addresses a, company_sources cs, industries i, crm_statuses crm, account_statuses ast, countries coun
WHERE
  cont.contact_record_status = 'a' AND
  c.company_record_status = 'a' AND
  cont.address_id = a.address_id AND
  cont.company_id = c.company_id AND
  c.company_source_id = cs.company_source_id AND
  c.industry_id = i.industry_id AND
  c.crm_status_id = crm.crm_status_id AND
  c.account_status_id = ast.account_status_id AND
  a.country_id = coun.country_id
ORDER BY c.company_name DESC, cont.last_name DESC, cont.first_names DESC";

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

$rst = $con->execute($sql);

$fp = fopen($xrms_file_root . '/tmp/contacts-export.csv', 'w');

if (($fp)) {
    rs2csvfile($rst, $fp);
    $rst->close();
    fclose($fp);
} else {
    echo '<br><h1>'._("Unable to Open file for writing.").'</h1>';
    $con->close();
    exit;
}

$con->close();

header("Location: {$http_site_root}/tmp/contacts-export.csv");

/**
 * $Log: export-companies.php,v $
 * Revision 1.5  2004/07/16 13:51:58  braverock
 * - localize strings for i18n translation support
 *   - applies modified patches from Sebastian Becker (hyperpac)
 *
 * Revision 1.4  2004/06/14 22:24:40  introspectshun
 * - Add adodb-params.php include for multi-db compatibility.
 *
 * Revision 1.3  2004/03/15 14:15:07  braverock
 * - added code to export all available contact fields on contact/company export
 *   - new code provided by Olivier Colonna of Fontaine Consulting
 * - add phpdoc
 *
 */
?>