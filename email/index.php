<?php
/**
 *
 * Email.
 *
 * $Id: index.php,v 1.4 2004/06/14 16:54:37 introspectshun Exp $
 */

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();
$msg = $_GET['msg'];
$array_of_companies = array();
$array_of_companies = unserialize($_SESSION['array_of_companies']);

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

$sql = "SELECT * FROM users WHERE user_id = $session_user_id";
$rst = $con->execute($sql);

$rec = array();
$rec['last_hit'] = $time();

$upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
$con->execute($upd);

$sql = "select * from email_templates where email_template_record_status = 'act' order by email_template_title";

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
 * $Log: index.php,v $
 * Revision 1.4  2004/06/14 16:54:37  introspectshun
 * - Add adodb-params.php include for multi-db compatibility.
 * - Corrected order of arguments to implode() function.
 * - Now use ADODB GetInsertSQL, GetUpdateSQL functions.
 *
 * Revision 1.3  2004/04/17 16:00:36  maulani
 * - Add CSS2 positioning
 *
 *
 */
?>
