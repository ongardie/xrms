<?php
/**
 *
 * Email.
 *
 * $Id: email.php,v 1.4 2004/04/17 16:00:36 maulani Exp $
 */

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

$session_user_id = session_check();
$msg = $_GET['msg'];

$scope = $_POST['scope'];

// opportunities
$user_id = $_POST['user_id'];
$contact_id = $_POST['contact_id'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
// $con->debug = 1;

switch ($scope) {
    case "companies":
        $sql = "select cont.contact_id 
        from contacts cont, companies c, opportunities o 
        where c.company_id = o.company_id 
        and c.company_id = cont.company_id 
        and cont.contact_record_status = 'a'";
    case "company":
        $sql = "select cont.contact_id 
        from contacts cont, companies c, cases ca 
        where c.company_id = ca.case_id 
        and c.company_id = cont.company_id 
        and cont.contact_record_status = 'a'";
    case "opportunities":
        $sql = "select cont.contact_id 
        from contacts cont, companies c, opportunities o 
        where c.company_id = o.company_id 
        and c.company_id = cont.company_id 
        and cont.contact_record_status = 'a'";
    case "cases":
        $sql = "select cont.contact_id 
        from contacts cont, companies c, cases ca 
        where c.company_id = ca.case_id 
        and c.company_id = cont.company_id 
        and cont.contact_record_status = 'a'";
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
	$tablerows = '<tr><td class=widget_content colspan=1>No e-mail templates</td></tr>';
}

$con->close();

$page_title = 'Bulk E-Mail';
start_page($page_title, true, $msg);

?>

<div id="Main">
    <div id="Content">

        <form action=email-2.php method=post>
		<table class=widget cellspacing=1>
			<tr>
				<td class=widget_header colspan=20>E-Mail Templates</td>
			</tr>
			<tr>
				<td class=widget_label width=1%>&nbsp;</td>
				<td class=widget_label>Template</td>
			</tr>
            <?php  echo $tablerows ?>
			<tr>
				<td class=widget_content_form_element colspan=2><input class=button type=submit value="Continue"></td>
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
 * Revision 1.4  2004/04/17 16:00:36  maulani
 * - Add CSS2 positioning
 *
 *
 */
?>
