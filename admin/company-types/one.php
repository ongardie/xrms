<?php

require_once('vars.php');
require_once('utils-interface.php');
require_once('utils-misc.php');
require_once('adodb/adodb.inc.php');

$session_user_id = session_check();

$company_type_id = $_GET['company_type_id'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

$sql = "select * from company_types where company_type_id = $company_type_id";

$rst = $con->execute($sql);

if ($rst) {
	
	$company_type_short_name = $rst->fields['company_type_short_name'];
	$company_type_pretty_name = $rst->fields['company_type_pretty_name'];
	$company_type_pretty_plural = $rst->fields['company_type_pretty_plural'];
	$company_type_display_html = $rst->fields['company_type_display_html'];
	
	$rst->close();
}

$page_title = $company_type_pretty_name;
start_page($page_title);

?>

<table border=0 cellpadding=0 cellspacing=0 width=100%>
	<tr>
		<td class=lcol width=25% valign=top>
		
		<form action=edit-2.php method=post>
		<input type=hidden name=company_type_id value="<?php  echo $company_type_id; ?>">
		<table class=widget cellspacing=1 width=100%>
			<tr>
				<td class=widget_header colspan=4>Edit Company Type Information</td>
			</tr>
			<tr>
				<td class=widget_label_right>Short Name</td>
				<td class=widget_content_form_element><input type=text name=company_type_short_name value="<?php  echo $company_type_short_name; ?>"></td>
			</tr>
			<tr>
				<td class=widget_label_right>Full Name</td>
				<td class=widget_content_form_element><input type=text name=company_type_pretty_name value="<?php  echo $company_type_pretty_name; ?>"></td>
			</tr>
			<tr>
				<td class=widget_label_right>Full Plural</td>
				<td class=widget_content_form_element><input type=text name=company_type_pretty_plural value="<?php  echo $company_type_pretty_plural; ?>"></td>
			</tr>
			<tr>
				<td class=widget_label_right>Display HTML</td>
				<td class=widget_content_form_element><input type=text name=company_type_display_html value="<?php  echo $company_type_display_html; ?>"></td>
			</tr>
			<tr>
				<td class=widget_content_form_element colspan=2><input class=button type=submit value="Save Changes"></td>
			</tr>
		</table>
		</form>

		<form action=delete.php method=post>
		<input type=hidden name=company_type_id value="<?php  echo $company_type_id; ?>">
		<table class=widget cellspacing=1 width=100%>
			<tr>
				<td class=widget_header colspan=4>Delete Company Type</td>
			</tr>
			<tr>
				<td class=widget_content>
				Click the button below to remove this company type from the system.
				<p>Note: This action CANNOT be undone!
				<p><input class=button type=submit value="Delete Company Type">
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
