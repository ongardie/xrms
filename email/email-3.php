<?php
/**
 *
 * Confirm email recipients.
 *
 * $Id: email-3.php,v 1.10 2004/08/26 22:55:26 niclowe Exp $
 */

require_once('include-locations-location.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();
$msg = $_GET['msg'];

$sender_name = $_POST['sender_name'];
$sender_address = $_POST['sender_address'];
$bcc_address = $_POST['bcc_address'];
$email_template_title = $_POST['email_template_title'];
$email_template_body = $_POST['email_template_body'];

$_SESSION['sender_name'] = serialize($sender_name);
$_SESSION['sender_address'] = serialize($sender_address);
$_SESSION['bcc_address'] = serialize($bcc_address);
$_SESSION['email_template_title'] = serialize($email_template_title);
$_SESSION['email_template_body'] = serialize($email_template_body);

$array_of_contacts = unserialize($_SESSION['array_of_contacts']);

if (is_array($array_of_contacts))
	$imploded_contacts = implode(',', $array_of_contacts);
else
	echo _("WARNING: No array of contacts!") . "<br>";
	

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
//$con->debug = 1;

$sql = "select cont.contact_id, cont.email, cont.first_names, cont.last_name, c.company_name, u.username
from contacts cont, companies c, users u
where c.company_id = cont.company_id
and c.user_id = u.user_id
and cont.contact_id in ($imploded_contacts)
and length(cont.email) > 0
and contact_record_status = 'a' order by c.company_name,cont.last_name asc";

$rst = $con->execute($sql);
if ($rst) {
    while (!$rst->EOF) {
        $contact_rows .= "<tr>";
        $contact_rows .= "<td class=widget_content_form_element><input type=checkbox name=array_of_contacts[] value=" . $rst->fields['contact_id'] . " checked></td>";
        $contact_rows .= "<td class=widget_content>" . $rst->fields['company_name'] . "</td>";
        $contact_rows .= "<td class=widget_content>" . $rst->fields['username'] . "</td>";
        $contact_rows .= "<td class=widget_content>" . $rst->fields['first_names'] . ' ' . $rst->fields['last_name'] . "</td>";
        $contact_rows .= "<td class=widget_content>" . $rst->fields['email'] . "</td>";
        $contact_rows .= "</tr>\n";
        $rst->movenext();
    }

    $rst->close();
}

$con->close();

$page_title = _("Confirm Recipients");
start_page($page_title, true, $msg);

?>

<div id="Main">
    <div id="Content">

        <form action=email-4.php method=post>
		<table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=5><?php echo _("Confirm Recipients"); ?></td>
            </tr>
            <tr>
                <td class=widget_label>&nbsp;</td>
                <td class=widget_label><?php echo _("Company"); ?></td>
                <td class=widget_label><?php echo _("Owner"); ?></td>
                <td class=widget_label><?php echo _("Contact"); ?></td>
                <td class=widget_label><?php echo _("E-Mail"); ?></td>
            </tr>
            <?php  echo $contact_rows ?>
            <tr>
                <td class=widget_content_form_element colspan=5><input type=submit class=button value="<?php echo _("Continue"); ?>"></td>
            </tr>
		</table>
        </form>

    </div>

        <!-- right column //-->
    <div id="Sidebar">

		&nbsp;

    </div>

</div>

<?php

end_page();

/**
 * $Log: email-3.php,v $
 * Revision 1.10  2004/08/26 22:55:26  niclowe
 * Enabled mail merge functionality for companies/some.php
 * Sorted pre-sending email checkbox page by company then contact lastname
 * Enabled mail merge for advanced-search companies
 *
 * Revision 1.9  2004/08/18 00:06:17  niclowe
 * Fixed bug 941839 - Mail Merge not working
 *
 * Revision 1.8  2004/08/04 21:46:42  introspectshun
 * - Localized strings for i18n/l10n support
 * - All paths now relative to include-locations-location.inc
 *
 * Revision 1.7  2004/07/04 07:51:33  metamedia
 * Minor changes and bug fixes to ensure that a mail merge from companies/one.php works.
 *
 * Revision 1.6  2004/07/03 15:03:52  metamedia
 * Minor bug fixes so that a mail merge from company/one.php (and hopefully other pages) will work.
 *
 * Revision 1.5  2004/06/14 16:54:37  introspectshun
 * - Add adodb-params.php include for multi-db compatibility.
 * - Corrected order of arguments to implode() function.
 * - Now use ADODB GetInsertSQL, GetUpdateSQL functions.
 *
 * Revision 1.4  2004/04/17 16:00:36  maulani
 * - Add CSS2 positioning
 *
 *
 */
?>
