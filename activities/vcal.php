<?php
/**
 * export vcal
 *
 * This page allows for export vcal for a single activity.
 *
 * $Id: vcal.php,v 1.5 2006/01/02 21:23:19 vanmer Exp $

 */
//include required files
require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');
$session_user_id = session_check();

$activity_id = $_GET['activity_id'];
$return_url = $_GET['return_url'];

$con = get_xrms_dbconnection();

update_recent_items($con, $session_user_id, "activities", $activity_id);


// $con->debug = 1;

$sql = "select a.*, c.company_id, c.company_name, cont.first_names, cont.last_name
from companies c, activities a left join contacts cont on a.contact_id = cont.contact_id
where a.company_id = c.company_id
and activity_id = $activity_id";

$rst = $con->execute($sql);

if ($rst) {
    $activity_title = $rst->fields['activity_title'];
    $activity_description = str_replace('
','=0D=0A',$rst->fields['activity_description']);
    $company_name = $rst->fields['company_name'];
    $contact_id = $rst->fields['contact_id'];
    $contact_first_names = $rst->fields['first_names'];
    $contact_last_name = $rst->fields['last_name'];
    $on_what_id = $rst->fields['on_what_id'];
    $scheduled_at = date('Ymd', strtotime($rst->fields['scheduled_at']));
    $ends_at = date('Ymd', strtotime($rst->fields['ends_at']));
    $rst->close();
}

$sql = "select cont.*,
c.company_id, company_name, company_code,
line1, line2, addresses.city, province, addresses.postal_code, address_body, use_pretty_address, iso_code3, address_format_string,
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
    $postal_code = $rst->fields['postal_code'];
    $country = $rst->fields['iso_code3'];
    $rst->close();
}

$rst = "BEGIN:VCALENDAR
VERSION:1.0
PRODID:XRMS

BEGIN:VEVENT
SUMMARY:".$activity_title." [".$contact_last_name.", ".$contact_first_names." (".$company_name.")]
DESCRIPTION;QUOTED-PRINTABLE:".$activity_description
."=0D=0A=0D=0A".$salutation." ".$first_names." ".$last_name
."=0D=0A".$work_phone
."=0D=0A".$cell_phone
."=0D=0A".$email
."=0D=0A=0D=0A".$company_name
."=0D=0A".$line1
."=0D=0A".$line2
."=0D=0A".$postal_code." ".$city." ".$country
."
DTSTART:".$scheduled_at."
DTEND:".$ends_at."
END:VEVENT

END:VCALENDAR";

$filename =  'activity_' . $session_user_id . '.vcs';

$fp = fopen($tmp_export_directory . $filename, 'w');

fwrite($fp,$rst);

$con->close();

header("Location: {$http_site_root}/export/{$filename}");

/**
 * $Log: vcal.php,v $
 * Revision 1.5  2006/01/02 21:23:19  vanmer
 * - changed to use centralized database connection function
 *
 * Revision 1.4  2004/07/25 12:27:43  braverock
 * - remove lang file require_once, as it is no longer used
 *
 * Revision 1.3  2004/06/15 18:03:33  introspectshun
 * - Fixe merge problem caused by incompatible line breaks.
 *
 * Revision 1.2  2004/06/11 21:22:21  introspectshun
 * - Add adodb-params.php include for multi-db compatibility.
 *
 * Revision 1.1  2004/05/27 20:35:27  gpowers
 * Patch [ 951138 ] Export Activities vCALENDAR
 * Export one activity into the vCalendar format.
 * Submitted By: frenchman
 *
 * Revision 1.0 2004/04/16 frenchman
 */
?>