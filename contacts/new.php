<?php

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

$session_user_id = session_check();

$msg = $_GET['msg'];
$company_id = $_GET['company_id'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

$sql = "select company_name, phone, fax from companies where company_id = $company_id";

$rst = $con->execute($sql);

if ($rst) {
	$company_name = $rst->fields['company_name'];
	$phone = $rst->fields['phone'];
	$fax = $rst->fields['fax'];
	$rst->close();
}

$sql = "select address_name, address_id from addresses where company_id = $company_id and address_record_status = 'a' order by address_id";
$rst = $con->execute($sql);
$address_menu = $rst->getmenu2('address_id', $address_id, false);
$rst->close();

$con->close();

$page_title = "New Contact for $company_name";
start_page($page_title, true, $msg);

?>

<table border=0 cellpadding=0 cellspacing=0 width=100%>
	<tr>
		<td class=lcol width=45% valign=top>

		<form action=new-2.php method=post>
		<input type=hidden name=company_id value="<?php echo $company_id; ?>">
		<table class=widget cellspacing=1 width=100%>
			<tr>
				<td class=widget_header colspan=2>Contact Information</td>
			</tr>
			<tr>
				<td class=widget_label_right>Address</td>
				<td class=widget_content_form_element><?php echo $address_menu ?></td>
			</tr>
			<tr>
				<td class=widget_label_right>First Names</td>
				<td class=widget_content_form_element><input type=text name=first_names size=30></td>
			</tr>
			<tr>
				<td class=widget_label_right>Last Name</td>
				<td class=widget_content_form_element><input type=text name=last_name size=30></td>
			</tr>
			<tr>
				<td class=widget_label_right>Summary</td>
				<td class=widget_content_form_element><input type=text name=summary size=35></td>
			</tr>
			<tr>
				<td class=widget_label_right>Title</td>
				<td class=widget_content_form_element><input type=text name=title size=35></td>
			</tr>
			<tr>
				<td class=widget_label_right>Description</td>
				<td class=widget_content_form_element><input type=text name=description size=35></td>
			</tr>
			<tr>
				<td class=widget_label_right>E-Mail</td>
				<td class=widget_content_form_element><input type=text name=email size=30></td>
			</tr>
			<tr>
				<td class=widget_label_right>Work Phone</td>
				<td class=widget_content_form_element><input type=text name=work_phone size=30 value="<?php  echo $phone; ?>"></td>
			</tr>
			<tr>
				<td class=widget_label_right>Cell Phone</td>
				<td class=widget_content_form_element><input type=text name=cell_phone size=30></td>
			</tr>
			<tr>
				<td class=widget_label_right>Home Phone</td>
				<td class=widget_content_form_element><input type=text name=home_phone size=30></td>
			</tr>
			<tr>
				<td class=widget_label_right>Fax</td>
				<td class=widget_content_form_element><input type=text name=fax size=30 value="<?php  echo $fax; ?>"></td>
			</tr>
			<tr>
				<td class=widget_label_right>AOL Name</td>
				<td class=widget_content_form_element><input type=text name=aol_name size=30></td>
			</tr>
			<tr>
				<td class=widget_label_right>Yahoo Name</td>
				<td class=widget_content_form_element><input type=text name=yahoo_name size=30></td>
			</tr>
			<tr>
				<td class=widget_label_right>MSN Name</td>
				<td class=widget_content_form_element><input type=text name=msn_name size=30></td>
			</tr>
			<tr>
				<td class=widget_label_right>Interests</td>
				<td class=widget_content_form_element><input type=text name=interests size=35></td>
			</tr>
			<tr>
				<td class=widget_content_form_element colspan=2><input class=button type=submit value="Add Contact"></td>
			</tr>
		</table>
		</form>

		</td>
		<!-- gutter //-->
		<td class=gutter width=2%>
		&nbsp;
		</td>
		<!-- right column //-->
		<td class=rcol width=53% valign=top>
		
		&nbsp;
		
		</td>
	</tr>
</table>

<script language=javascript>

function initialize() {
	document.forms[0].first_names.focus();
}

initialize();

</script>

<?php end_page();; ?>