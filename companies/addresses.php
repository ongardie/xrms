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

$sql = "select * from companies c, addresses a where c.company_id = a.company_id and c.company_id = $company_id";

$rst = $con->execute($sql);

if ($rst) {
	while (!$rst->EOF) {
		$company_name = $rst->fields['company_name'];
		$addresses .= '<tr>';
		$addresses .= "<td class=widget_label_right_91px><a href=edit-address.php?company_id=$company_id&address_id=" . $rst->fields['address_id'] . '>' . $rst->fields['address_name'] . '</a></td>';
		$addresses .= '<td class=widget_content>' . nl2br($rst->fields['address_body']) . '</td>';
		$addresses .= "<td class=widget_content><input type=radio name=default_billing_address value=" . $rst->fields['address_id'];
		
		if ($rst->fields['default_billing_address'] == $rst->fields['address_id']) {
			$addresses .= ' checked';
		}
		
		$addresses .= '></td>';
		$addresses .= "<td class=widget_content><input type=radio name=default_shipping_address value=" . $rst->fields['address_id'];
		
		if ($rst->fields['default_shipping_address'] == $rst->fields['address_id']) {
			$addresses .= ' checked';
		}
		
		$addresses .= '></td>';
		$addresses .= "<td class=widget_content><input type=radio name=default_payment_address value=" . $rst->fields['address_id'];
		
		if ($rst->fields['default_payment_address'] == $rst->fields['address_id']) {
			$addresses .= ' checked';
		}
		
		$addresses .= '></td>';
		$addresses .= '</tr>';
		$rst->movenext();
	}
	$rst->close();
}

$con->close();

$page_title = $company_name . " - Addresses";
start_page($page_title, true, $msg);

?>

<table border=0 cellpadding=0 cellspacing=0 width=100%>
	<tr>
		<td class=lcol width=55% valign=top>

		<!-- new address //-->
		<form action=add-address.php method=post>
		<input type=hidden name=company_id value=<?php  echo $company_id; ?>>
		<table class=widget cellspacing=1 width=100%>
			<tr>
				<td class=widget_header colspan=2>New Address</td>
			</tr>
			<tr>
				<td class=widget_label_right>Name</td>
				<td class=widget_content_form_element><input type=text name=address_name size=30></td>
			</tr>
			<tr>
				<td class=widget_label_right_91px>Address</td>
				<td class=widget_content_form_element><textarea rows=5 cols=60 name=address_body></textarea></td>
			</tr>
			<tr>
				<td class=widget_content_form_element colspan=2><input class=button type=submit value="Add"></td>
			</tr>
		</table>
		</form>

		<form action=set-address-defaults.php method=post>
		<input type=hidden name=company_id value=<?php  echo $company_id; ?>>
		<table class=widget cellspacing=1 width=100%>
			<tr>
				<td class=widget_header colspan=5>Addresses</td>
			</tr>
			<tr>
				<td class=widget_label>Name</td>
				<td class=widget_label>Body</td>
				<td class=widget_label>Shipping Default</td>
				<td class=widget_label>Billing Default</td>
				<td class=widget_label>Payment Default</td>
			</tr>
			<?php  echo $addresses; ?>
			</tr>
			</tr>
				<td class=widget_content_form_element colspan=5><input class=button type=submit value="Save Defaults"></td>
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