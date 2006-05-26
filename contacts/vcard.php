<?php
/**
 * export vcard
 *
 * This page allows for export vcard for a single contact.
 *
 * $Id: vcard.php,v 1.11 2006/05/26 19:38:02 ongardie Exp $
 */
require_once('include-locations-location.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();

$contact_id = $_GET['contact_id'];

$con = get_xrms_dbconnection();
// $con->debug = 1;


$sql = "select
cont.*,
c.company_id, company_name, company_code,
line1, line2, addresses.city, province, addresses.postal_code, address_body, use_pretty_address, country_name, address_format_string,
u1.username as entered_by_username, u2.username as last_modified_by_username, u3.username as account_owner,
account_status_display_html, crm_status_display_html
from contacts cont, companies c, users u1, users u2, users u3,
account_statuses as1, crm_statuses crm, addresses, countries, address_format_strings afs
where cont.company_id = c.company_id
and cont.entered_by = u1.user_id
and cont.last_modified_by = u2.user_id
and c.user_id = u3.user_id
and c.account_status_id = as1.account_status_id
and c.crm_status_id = crm.crm_status_id
and countries.country_id = addresses.country_id
and countries.address_format_string_id = afs.address_format_string_id
and addresses.address_id = cont.address_id
and contact_id = $contact_id";

$rst = $con->execute($sql);

if ($rst) {
    $company_name = $rst->fields['company_name'];
    $last_name = $rst->fields['last_name'];
    $first_names = $rst->fields['first_names'];
    $salutation = $rst->fields['salutation'];
    $title = $rst->fields['title'];
    $email = $rst->fields['email'];
    $work_phone = $rst->fields['work_phone'];
    $cell_phone = $rst->fields['cell_phone'];
    $fax = $rst->fields['fax'];
    $line1 = $rst->fields['line1'];
    $line2 = $rst->fields['line2'];
    $city = $rst->fields['city'];
    $province = $rst->fields['province'];
    $postal_code = $rst->fields['postal_code'];
    $country = $rst->fields['country_name'];
    $rst->close();
}

$con->close();

$vcard = "BEGIN:VCARD
VERSION:2.1
N:".$last_name.";".$first_names."
FN:".$first_names." ".$last_name."
TITLE:".$title."
ORG:".$company_name."
ADR;WORK:;;".$line1." - ".$line2.";".$city.";".$province.";".$postal_code.";".$country."
TEL;WORK;VOICE:".$work_phone."
TEL;FAX:".$fax."
EMAIL:".$email."
TEL;CELL:".$cell_phone."
REV:20031119T213210Z
END:VCARD";

$filesize = strlen($vcard);

$filename =  $last_name . '.' . $first_names . '.vcf';

SendDownloadHeaders("text", "x-vcard", $filename, false, $filesize);
echo $vcard;
exit;

/**
 * $Log: vcard.php,v $
 * Revision 1.11  2006/05/26 19:38:02  ongardie
 * - Added provinces to vcard output.
 *
 * Revision 1.10  2006/01/02 23:00:00  vanmer
 * - changed to use centralized dbconnection function
 *
 * Revision 1.9  2004/12/24 16:25:57  braverock
 * -modified to use lastname.firstname.vcf as vcard filenname
 *
 * Revision 1.8  2004/12/24 16:16:15  braverock
 * - modified to use SendDownloadHeaders
 *
 * Revision 1.7  2004/07/25 12:50:53  braverock
 * - remove lang file require_once, as it is no longer used
 * - remove CVS conflict copies of code that made
 *   three copies of most of the code in this file
 *
 * Revision 1.6  2004/07/22 11:21:13  cpsource
 * - All paths now relative to include-locations-location.inc
 *   Code cleanup for Create Contact for 'Self'
 *
 * Revision 1.5  2004/06/28 13:38:49  braverock
 * - save with unix line endings
 * - fix log phpdoc
 *
 * - Fixed merge problem caused by incompatible line breaks.
 *
 * Revision 1.1  2004/05/27 20:22:29  gpowers
 * Export one contact to a Vcard
 * Patch [ 951084 ] Export VCARD
 * Submitted By: frenchman
 *
 * Revision 1.0 2004/04/16 frenchman
 */
?>
