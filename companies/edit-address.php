<?php

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

$session_user_id = session_check();
$msg = $_GET['msg'];
$company_id = $_GET['company_id'];
$address_id = $_GET['address_id'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

$sql = "select * from companies c, addresses a";
$sql .= " where c.company_id = a.company_id";
$sql .= " and a.address_id = $address_id";

$rst = $con->execute($sql);

if ($rst) {
	$company_name = $rst->fields['company_name'];
	$address_name = $rst->fields['address_name'];
	$address_body = $rst->fields['address_body'];
	$rst->close();
}

$con->close();

$page_title = $company_name . " - Edit Address";
start_page($page_title, true, $msg);

?>

<table border=0 cellpadding=0 cellspacing=0 width=100%>
	<tr>
		<td class=lcol width=55% valign=top>

		<form action=edit-address-2.php method=post>
		<input type=hidden name=company_id value=<?php  echo $company_id; ?>>
		<input type=hidden name=address_id value=<?php  echo $address_id; ?>>
		<table class=widget cellspacing=1 width=100%>
			<tr>
				<td class=widget_header colspan=2>Edit Address</td>
			</tr>
			<tr>
				<td class=widget_label_right>Address Name</td>
				<td class=widget_content_form_element><input type=text size=40 name=address_name value="<?php  echo $address_name; ?>"></td>
			</tr>
			<tr>
				<td class=widget_label_right_91px>Address Body</td>
				<td class=widget_content_form_element><textarea rows=5 cols=60 name=address_body><?php  echo $address_body; ?></textarea></td>
			</tr>
			<tr>
				<td class=widget_content_form_element colspan=2><input class=button type=submit value="Save Changes"></td>
			</tr>
		</table>
		</form>

		</td>
		<!-- gutter //-->
		<td class=gutter width=2%>
		&nbsp;
		</td>
		<!-- right column //-->
		<td class=rcol width=43% valign=top>
		
		</td>
	</tr>
</table>

<?php end_page();; ?>