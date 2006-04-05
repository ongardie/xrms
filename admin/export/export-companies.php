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

$session_user_id = session_check( 'Admin' );

$con = get_xrms_dbconnection();

$user_id     = $_POST['user_id'];
$category_id = $_POST['category_id'];
$csv_output  = $_POST['csv_output'];
$page_title = _("Full Export");

// get users
$sql2 = "select username, user_id from users where user_record_status = 'a' order by username";
$rst = $con->execute($sql2);
$user_menu = $rst->getmenu2('user_id', $user_id, true);
$rst->close();

// get categories
$sql2  = "select category_pretty_name, c.category_id";
$sql2 .= " from categories c, category_scopes cs, category_category_scope_map ccsm";
$sql2 .= " where c.category_id = ccsm.category_id";
$sql2 .= " and cs.on_what_table =  'companies'";
$sql2 .= " and ccsm.category_scope_id = cs.category_scope_id";
$sql2 .= " and category_record_status =  'a'";
$sql2 .= " order by category_pretty_name";
$rst = $con->execute($sql2);
$category_menu = $rst->getmenu2('category_id', $category_id, true);
$rst->close();

if ($csv_output) {
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
  cont.profile AS 'Contact Profile',
  cont.custom1 AS 'Custom1',
  cont.custom2 AS 'Custom2',
  cont.custom3 AS 'Custom3',
  cont.custom4 AS 'Custom4',
  c.company_code AS 'Company Code',
  c.company_name AS 'Company',
  c.legal_name AS 'Legal Name',
  c.tax_id AS 'Tax',
  c.profile AS 'Company Profile',
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
  coun.country_name AS 'Country',
  catmap.on_what_id, catmap.on_what_table
FROM contacts cont, companies c, addresses a, company_sources cs, industries i, crm_statuses crm, account_statuses ast, countries coun, entity_category_map catmap
WHERE
  cont.contact_record_status = 'a' AND
  c.company_record_status = 'a' AND
  cont.address_id = a.address_id AND
  cont.company_id = c.company_id AND
  c.company_source_id = cs.company_source_id AND
  c.industry_id = i.industry_id AND
  c.crm_status_id = crm.crm_status_id AND
  c.account_status_id = ast.account_status_id AND
  a.country_id = coun.country_id ";
  if ($category_id) {
    $sql .= "AND category_id = $category_id ";
    $sql .= "AND catmap.on_what_id=c.company_id and catmap.on_what_table='companies' ";
  }
  if ($user_id) $sql .= "AND c.user_id = $user_id ";

$sql.=" ORDER BY c.company_name DESC, cont.last_name DESC, cont.first_names DESC";

$rst = $con->execute($sql);
if (!$rst) { db_error_handler($con, $sql); return false; }

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
} else {
       start_page($page_title, true, $msg);
       echo "<form action='export-companies.php' method=post>\n"
       ."<table class =widget><tr>"
                       ."<td class=widget_label></td>\n"
                       ."<td class=widget_label>"._("User")."</td>\n"
                       ."<td class=widget_label>"._("Category")."</td>\n"
                       ."<td class=widget_label></td></tr>\n"
               ."<tr><td class=widget_content_form_element></td>"
                       ."<td class=widget_content_form_element>$user_menu</td>\n"
                       ."<td class=widget_content_form_element>$category_menu</td>"
                       ."</tr></table>\n";
       echo "<table class=widget>\n"
               ."<tr><td class=widget_content_form_element>"
                       ."<td class=widget_content_form_element>\n"
                               ."<table class=widget_content_form_element>"
                               ."<td>"._("Generate a CSV file")."</td>"
                               ."<td><input class=button name=csv_output type=submit "
                               ."value='Output'>\n"
               ,"</td></table></table></form>\n";
       end_page();
}


$con->close();

if ($csv_output) header("Location: {$http_site_root}/tmp/contacts-export.csv");

/**
 * $Log: export-companies.php,v $
 * Revision 1.9  2006/04/05 01:11:27  vanmer
 * - updated to give some granularity of the export of companies/contacts data
 *
 * Revision 1.8  2006/01/02 21:50:29  vanmer
 * - changed to use centralized dbconnection function
 *
 * Revision 1.7  2005/09/06 16:04:39  braverock
 * - add Admin ACL restriction to export functions.
 *   credit Bert (SF:camel2004) for the patch
 *
 * Revision 1.6  2005/03/30 03:47:34  niclowe
 * renamed ambiguous contact and company 'profile' field to 'contact profile' and 'company profile' as it caused abberant behaviour on import of field data (second profile data record not recorded)
 *
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