<?php
/**
 *
 * Email.
 *
 * $Id: email.php,v 1.7 2004/08/04 21:46:42 introspectshun Exp $
 */

require_once('include-locations-location.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();
$msg = $_GET['msg'];

$scope = $_GET['scope'];
$company_id = $_GET['company_id'];
// opportunities
$user_id = $_POST['user_id'];
$contact_id = $_POST['contact_id'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
//$con->debug = 1;

switch ($scope) {
    case "companies":
        $sql = "select cont.contact_id
        from contacts cont, companies c, opportunities o
        where c.company_id = o.company_id
        and c.company_id = cont.company_id
        and cont.contact_record_status = 'a'";
	break;
    case "company":
        $sql = "select cont.contact_id
        from contacts cont, companies c
        where c.company_id = $company_id
	and c.company_id = cont.company_id
        and cont.contact_record_status = 'a'";
	break;
    case "opportunities":
        $sql = "select cont.contact_id
        from contacts cont, companies c, opportunities o
        where c.company_id = o.company_id
        and c.company_id = cont.company_id
        and cont.contact_record_status = 'a'";
	break;
    case "cases":
        $sql = "select cont.contact_id
        from contacts cont, companies c, cases ca
        where c.company_id = ca.case_id
        and c.company_id = cont.company_id
        and cont.contact_record_status = 'a'";
	break;
}
$rst = $con->execute($sql);
$array_of_contacts = array();
if ($rst) {
    while (!$rst->EOF) {
        array_push($array_of_contacts, $rst->fields['contact_id']);
        $rst->movenext();
    }
}

$_SESSION['array_of_contacts'] = serialize($array_of_contacts);

$sql = "select * from email_templates where email_template_record_status = 'a' order by email_template_title";

$rst = $con->execute($sql);

$counter = 0;
if ($rst) {
	while (!$rst->EOF) {
		$counter ++;
		$checked = ($counter == 1) ? ' checked' : '';
		$tablerows .= '<tr>';
        $tablerows .= "<td class=widget_content_form_element><input type=radio name=email_template_id value=" . $rst->fields['email_template_id'] . $checked . "></td>";
		$tablerows .= '<td class=widget_content><a href=one-template.php?email_template_id=' . $rst->fields['email_template_id'] . '>' . $rst->fields['email_template_title'] . '</a></td>';
		$tablerows .= '</tr>';
		$rst->movenext();
	}
	$rst->close();
}

if (strlen($tablerows) == 0) {
	$tablerows = '<tr><td class=widget_content colspan=1>' . _("No e-mail templates") . '</td></tr>';
}

$con->close();

$page_title = _("Bulk E-Mail");
start_page($page_title, true, $msg);

?>

<div id="Main">
    <div id="Content">

        <form action=email-2.php method=post>
		<table class=widget cellspacing=1>
			<tr>
				<td class=widget_header colspan=20><?php echo _("E-Mail Templates"); ?></td>
			</tr>
			<tr>
				<td class=widget_label width=1%>&nbsp;</td>
				<td class=widget_label><?php echo _("Template"); ?></td>
			</tr>
            <?php  echo $tablerows ?>
			<tr>
				<td class=widget_content_form_element colspan=2><input class=button type=submit value="<?php echo _("Continue"); ?>"></td>
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
 * $Log: email.php,v $
 * Revision 1.7  2004/08/04 21:46:42  introspectshun
 * - Localized strings for i18n/l10n support
 * - All paths now relative to include-locations-location.inc
 *
 * Revision 1.6  2004/07/03 14:48:52  metamedia
 * Minor bug fixes so that the "mail merge" from a company work.
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
