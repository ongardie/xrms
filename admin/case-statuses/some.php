<?php

require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

$session_user_id = session_check();

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

$sql = "select * from case_statuses where case_status_record_status = 'a' order by case_status_id";
$rst = $con->execute($sql);

if ($rst) {
	while (!$rst->EOF) {
		$table_rows .= '<tr>';
		$table_rows .= '<td class=widget_content><a href=one.php?case_status_id=' . $rst->fields['case_status_id'] . '>' . $rst->fields['case_status_pretty_name'] . '</a></td>';
		$table_rows .= '</tr>';
		$rst->movenext();
	}
	$rst->close();
}

$con->close();

$page_title = "Manage Case Statuses";
start_page($page_title);

?>

<table border=0 cellpadding=0 cellspacing=0 width=100%>
	<tr>
		<td class=lcol width=65% valign=top>
		
		<table class=widget cellspacing=1 width=100%>
			<tr>
				<td class=widget_header colspan=4>Case Statuses</td>
			</tr>
			<tr>
				<td class=widget_label>Name</td>
			</tr>
			<?php  echo $table_rows; ?>
		</table>
		
		</td>
		
		<!-- gutter //-->
		<td class=gutter width=2%>
		&nbsp;
		</td>
		
		<!-- right column //-->
		
		<td class=rcol width=33% valign=top>

		<form action=new-2.php method=post>
		<table class=widget cellspacing=1 width=100%>
			<tr>
				<td class=widget_header colspan=2>Add New Case Status</td>
			</tr>
			<tr>
				<td class=widget_label_right>Short Name</td>
				<td class=widget_content_form_element><input type=text name=case_status_short_name size=10></td>
			</tr>
			<tr>
				<td class=widget_label_right>Full Name</td>
				<td class=widget_content_form_element><input type=text name=case_status_pretty_name size=20></td>
			</tr>
			<tr>
				<td class=widget_label_right>Full Plural Name</td>
				<td class=widget_content_form_element><input type=text name=case_status_pretty_plural size=20></td>
			</tr>
			<tr>
				<td class=widget_label_right>Display HTML</td>
				<td class=widget_content_form_element><input type=text name=case_status_display_html size=30></td>
			</tr>
			<tr>
				<td class=widget_content_form_element colspan=2><input class=button type=submit value="Add"></td>
			</tr>
		</table>
		</form>

		</td>
	</tr>
</table>

<?php end_page();; ?>