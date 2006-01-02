<?php
/**
 *
 * Send reminder email for each overdue activity.  Call from cron job to regularly
 * prompt users regarding overdue activities.
 *
 * $Id: overdue-email.php,v 1.2 2006/01/02 23:46:52 vanmer Exp $
 */
require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb/adodb-pager.inc.php');
require_once($include_directory . 'adodb-params.php');

// $session_user_id = session_check();  Commented out because this code is called from cron job

$con = get_xrms_dbconnection();
// $con->debug = 1;

$from_email_address = get_system_parameter($con, "Sender Email Address");

$page_title = _("Overdue Items to Email");
start_page($page_title, true, '');

echo "<p>&nbsp;</p>";

$sql = "select u.user_id, u.username, u.email, u.first_names, u.last_name, c.company_name,
            a.scheduled_at, a.ends_at, a.activity_title, a.activity_id, a.contact_id,
            t.activity_type_pretty_name
        from users u, activities a, activity_types t, companies c
        where u.user_record_status = 'a' 
        and u.user_id=a.user_id
        and a.activity_status = 'o'
        and a.activity_record_status = 'a'
        and a.ends_at < now()
        and a.activity_type_id = t.activity_type_id
        and a.company_id = c.company_id
        order by u.last_name, u.first_names, a.entered_at";
$rst = $con->execute($sql);

if ($rst) {
    $num_late = $rst->RecordCount();
	if ($num_late > 0){
		while (!$rst->EOF) {
			$user_id = $rst->fields['user_id'];
			$username = $rst->fields['username'];
			$email = $rst->fields['email'];
			$name =  $rst->fields['first_names'] . " " . $rst->fields['last_name'];
			$activity_type_pretty_name = $rst->fields['activity_type_pretty_name'];
			$company_name = $rst->fields['company_name'];
			$scheduled_at = $rst->fields['scheduled_at'];
			$ends_at = $rst->fields['ends_at'];
			$activity_title = $rst->fields['activity_title'];
			$activity_id = $rst->fields['activity_id'];
			$contact_id = $rst->fields['contact_id'];
			
			$activity_link = "<a href=\"" . $http_site_root . "/activities/one.php?activity_id=" . $activity_id . "\">" . $activity_title . "</a>";

			$sql_contact = "SELECT last_name, first_names from contacts where contact_id = $contact_id";
			$rst_contact = $con->execute($sql_contact);
			$contact_name =  $rst_contact->fields['first_names'] . " " . $rst_contact->fields['last_name'];

            $output .= "<p><font size=+2><b>" . _("OVERDUE ACTIVITY for") . " $name</b></font><br></p>\n";
            $output .= "<table>";
            $output .= "<tr><td colspan=2><hr></td></tr>\n";
            $output .= "    <tr><td align=left>" . _("Start") . "</td><td align=left>" . $scheduled_at . "</td></tr>\n";
            $output .= "    <tr><td align=left>" . _("End") . "</td><td align=left>" . $ends_at . "</td></tr>\n";
            $output .= "    <tr><td align=left>" . _("Type") . "</td><td align=left>" . $activity_type_pretty_name . "</td></tr>\n";
            $output .= "    <tr><td align=left>" . _("Company") . "</td><td align=left>" . $company_name . "</td></tr>\n";
            $output .= "    <tr><td align=left>" . _("Contact") . "</td><td align=left>" . $contact_name . "</td></tr>\n";
            $output .= "    <tr><td align=left>" . _("Activity") . "</td><td align=left>" . $activity_link . "</td></tr>\n";
            $output .= "<tr><td colspan=2><hr></td></tr>\n";

            $output .= "</table>";
            
            $title = _("XRMS: Overdue Items for ") . $name;
			$headers  = "MIME-Version: 1.0\r\n";
			$headers .= "From: " . $from_email_address . "\r\n";
			$headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
			mail($email, $title, $output, $headers);

			echo $output;			
			$output = "";

			$rst->movenext();
		}
	}
	$rst->close();
} else {
	db_error_handler ($con,$sql);
}

$con->close();

end_page();

/**
 * $Log: overdue-email.php,v $
 * Revision 1.2  2006/01/02 23:46:52  vanmer
 * - changed to use centralized dbconnection function
 *
 * Revision 1.1  2005/04/01 10:27:09  maulani
 * - Add report to email overdue activities to XRMS users
 *   Useful for low volume sites where users are logging into  XRMS infrequently
 *
 *
 */
?>
