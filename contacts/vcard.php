<?php
/**
 * export vcard
 *
 * This page allows for export vcard for a single contact.
 *
 * $Id: vcard.php,v 1.5 2004/06/28 13:38:49 braverock Exp $
 */
require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();
require_once($include_directory . 'lang/' . $_SESSION['language'] . '.php');

$contact_id = $_GET['contact_id'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
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

$rst = "BEGIN:VCARD
VERSION:2.1
N:".$last_name.";".$first_names."
FN:".$first_names." ".$last_name."
TITLE:".$title."
ORG:".$company_name."
ADR;WORK:;;".$line1." - ".$line2.";".$city.";;".$postal_code.";".$country."
TEL;WORK;VOICE:".$work_phone."
TEL;FAX:".$fax."
EMAIL:".$email."
TEL;CELL:".$cell_phone."
REV:20031119T213210Z
END:VCARD";



$filename =  'contact_' . $session_user_id . '.vcf';

$fp = fopen($tmp_export_directory . $filename, 'w');

fwrite($fp,$rst);



$con->close();

header("Location: {$http_site_root}/export/{$filename}");


/**
 * $Log: vcard.php,v $
 * Revision 1.5  2004/06/28 13:38:49  braverock
 * - save with unix line endings
 * - fix log phpdoc
 *
<?php
/**
 * export vcard
 *
 * This page allows for export vcard for a single contact.
 *
 * $Id: vcard.php,v 1.5 2004/06/28 13:38:49 braverock Exp $
 */
require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();
require_once($include_directory . 'lang/' . $_SESSION['language'] . '.php');

$contact_id = $_GET['contact_id'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
// $con->debug = 1;


$sql = "select cont.*,
c.company_id, company_name, company_code,
line1, line2, addresses.city, province, addresses.postal_code, address_body, use_pretty_address, country_name, address_format_string,
u1.username as entered_by_username, u2.username as last_modified_by_username, u3.username as account_owner,
account_status_display_html, crm_status_display_html
from contacts cont, companies c, users u1, users u2, users u3, account_statuses as1, crm_statuses crm, addresses, countries, address_format_strings afs
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

$rst = "BEGIN:VCARD
VERSION:2.1
N:".$last_name.";".$first_names."
FN:".$first_names." ".$last_name."
TITLE:".$title."
ORG:".$company_name."
ADR;WORK:;;".$line1." - ".$line2.";".$city.";;".$postal_code.";".$country."
TEL;WORK;VOICE:".$work_phone."
TEL;FAX:".$fax."
EMAIL:".$email."
TEL;CELL:".$cell_phone."
REV:20031119T213210Z
END:VCARD";



$filename =  'contact_' . $session_user_id . '.vcf';

$fp = fopen($tmp_export_directory . $filename, 'w');

fwrite($fp,$rst);



$con->close();

header("Location: {$http_site_root}/export/{$filename}");


/**
 * Revision 1.4  2004/06/24 19:45:33  introspectshun
<?php
/**
 * export vcard
 *
 * This page allows for export vcard for a single contact.
 *
 * $Id: vcard.php,v 1.5 2004/06/28 13:38:49 braverock Exp $
 */
require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();
require_once($include_directory . 'lang/' . $_SESSION['language'] . '.php');

$contact_id = $_GET['contact_id'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
// $con->debug = 1;


$sql = "select cont.*,
c.company_id, company_name, company_code,
line1, line2, addresses.city, province, addresses.postal_code, address_body, use_pretty_address, country_name, address_format_string,
u1.username as entered_by_username, u2.username as last_modified_by_username, u3.username as account_owner,
account_status_display_html, crm_status_display_html
from contacts cont, companies c, users u1, users u2, users u3, account_statuses as1, crm_statuses crm, addresses, countries, address_format_strings afs
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

$rst = "BEGIN:VCARD
VERSION:2.1
N:".$last_name.";".$first_names."
FN:".$first_names." ".$last_name."
TITLE:".$title."
ORG:".$company_name."
ADR;WORK:;;".$line1." - ".$line2.";".$city.";;".$postal_code.";".$country."
TEL;WORK;VOICE:".$work_phone."
TEL;FAX:".$fax."
EMAIL:".$email."
TEL;CELL:".$cell_phone."
REV:20031119T213210Z
END:VCARD";



$filename =  'contact_' . $session_user_id . '.vcf';

$fp = fopen($tmp_export_directory . $filename, 'w');

fwrite($fp,$rst);



$con->close();

header("Location: {$http_site_root}/export/{$filename}");


/**
 * - Fixed merge problem caused by incompatible line breaks.
<?php
/**
 * export vcard
 *
 * This page allows for export vcard for a single contact.
 *
 * $Id: vcard.php,v 1.5 2004/06/28 13:38:49 braverock Exp $
 */
require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();
require_once($include_directory . 'lang/' . $_SESSION['language'] . '.php');

$contact_id = $_GET['contact_id'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
// $con->debug = 1;


$sql = "select cont.*,
c.company_id, company_name, company_code,
line1, line2, addresses.city, province, addresses.postal_code, address_body, use_pretty_address, country_name, address_format_string,
u1.username as entered_by_username, u2.username as last_modified_by_username, u3.username as account_owner,
account_status_display_html, crm_status_display_html
from contacts cont, companies c, users u1, users u2, users u3, account_statuses as1, crm_statuses crm, addresses, countries, address_format_strings afs
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

    $rst = "BEGIN:VCARD
    VERSION:2.1
    N:".$last_name.";".$first_names."
    FN:".$first_names." ".$last_name."
    TITLE:".$title."
    ORG:".$company_name."
    ADR;WORK:;;".$line1." - ".$line2.";".$city.";;".$postal_code.";".$country."
    TEL;WORK;VOICE:".$work_phone."
    TEL;FAX:".$fax."
    EMAIL:".$email."
    TEL;CELL:".$cell_phone."
    REV:20031119T213210Z
    END:VCARD";

    $filename =  'contact_' . $session_user_id . '.vcf';

    $fp = fopen($tmp_export_directory . $filename, 'w');

    fwrite($fp,$rst);

} else {
    db_error_handler($con, $sql);
}


$con->close();

header("Location: {$http_site_root}/export/{$filename}");

/**
 * $Log: vcard.php,v $
 * Revision 1.5  2004/06/28 13:38:49  braverock
 * - save with unix line endings
 * - fix log phpdoc
 *
 *
 * Revision 1.1  2004/05/27 20:22:29  gpowers
 * Export one contact to a Vcard
 * Patch [ 951084 ] Export VCARD
 * Submitted By: frenchman
 *
 * Revision 1.0 2004/04/16 frenchman
 */
?>