<?php

require_once('vars.php');
require_once('utils-interface.php');
require_once('utils-misc.php');
require_once('adodb/adodb.inc.php');

$session_user_id = session_check();
$msg = $_GET['msg'];
$array_of_companies = array();
$array_of_companies = unserialize($_SESSION['array_of_companies']);

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
$con->execute("update users set last_hit = " . $con->dbtimestamp(mktime()) . " where user_id = $session_user_id");

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

<table border=0 cellpadding=0 cellspacing=0 width=100%>
	<tr>
		<td class=lcol width=65% valign=top>

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
		
		</td>
		<!-- gutter //-->
		<td class=gutter width=2%>
		&nbsp;
		</td>
		<!-- right column //-->
		<td class=rcol width=33% valign=top>
		
    	</td>
	</tr>
</table>

<?php end_page(); ?>