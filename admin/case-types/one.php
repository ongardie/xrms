<?php

require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

$session_user_id = session_check();

$case_type_id = $_GET['case_type_id'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

$sql = "select * from case_types where case_type_id = $case_type_id";

$rst = $con->execute($sql);

if ($rst) {
	
	$case_type_short_name = $rst->fields['case_type_short_name'];
	$case_type_pretty_name = $rst->fields['case_type_pretty_name'];
	$case_type_pretty_plural = $rst->fields['case_type_pretty_plural'];
	$case_type_display_html = $rst->fields['case_type_display_html'];
	
	$rst->close();
}

$con->close();

$page_title = "One Case Type : $case_type_pretty_name";
start_page($page_title);

?>

<table border=0 cellpadding=0 cellspacing=0 width=100%>
	<tr>
		<td class=lcol width=25% valign=top>
		
		<form action=edit-2.php method=post>
		<input type=hidden name=case_type_id value="<?php  echo $case_type_id; ?>">
		<table class=widget cellspacing=1 width=100%>
			<tr>
				<td class=widget_header colspan=4>Edit Case Type Information</td>
			</tr>
			<tr>
				<td class=widget_label_right>Short Name</td>
				<td class=widget_content_form_element><input type=text size=10 name=case_type_short_name value="<?php  echo $account_status_short_name; ?>"></td>
			</tr>
			<tr>
				<td class=widget_label_right>Full Name</td>
				<td class=widget_content_form_element><input type=text size=20 name=case_type_pretty_name value="<?php  echo $account_status_pretty_name; ?>"></td>
			</tr>
			<tr>
				<td class=widget_label_right>Full Plural</td>
				<td class=widget_content_form_element><input type=text size=20 name=case_type_pretty_plural value="<?php  echo $account_status_pretty_plural; ?>"></td>
			</tr>
			<tr>
				<td class=widget_label_right>Display HTML</td>
				<td class=widget_content_form_element><input type=text size=30 name=case_type_display_html value="<?php  echo $account_status_display_html; ?>"></td>
			</tr>
			<tr>
				<td class=widget_content_form_element colspan=2><input class=button type=submit value="Save Changes"></td>
			</tr>
		</table>
		</form>

		<form action=delete.php method=post>
		<input type=hidden name=case_type_id value="<?php  echo $case_type_id; ?>" onsubmit="javascript: return confirm('Delete Case Type?');">
		<table class=widget cellspacing=1 width=100%>
			<tr>
				<td class=widget_header colspan=4>Delete Case Type</td>
			</tr>
			<tr>
				<td class=widget_content>
				Click the button below to remove this case type from the system.
				<p>Note: This action CANNOT be undone!
				<p><input class=button type=submit value="Delete Case Type">
				</td>
			</tr>
		</table>
		</form>
		
		</td>
		
		<!-- gutter //-->
		<td class=gutter width=2%>
		&nbsp;
		</td>
		
		<!-- right column //-->
		
		<td class=rcol width=73% valign=top>
		&nbsp;
		</td>
		
	</tr>
</table>

<?php end_page();; ?>
