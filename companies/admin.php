<?php

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

// require_once($include_directory . 'phpgacl/gacl.class.php');

$session_user_id = session_check();
$msg = $_GET['msg'];

$company_id = $_GET['company_id'];

// $gacl = new gacl();
// $gacl_check = $gacl->acl_check('users', $session_user_id, 'company', 'view', 'companies', $company_id);
// $gacl_check = ($gacl_check) ? "True" : "False";

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

$sql = "select * from companies where company_id = $company_id";

$rst = $con->execute($sql);

if ($rst) {
	$company_name = $rst->fields['company_name'];
	$tax_id = $rst->fields['tax_id'];
	$account_status_id = $rst->fields['account_status_id'];
	$credit_limit = $rst->fields['credit_limit'];
	$rating_id = $rst->fields['rating_id'];
	$terms = $rst->fields['terms'];
	$extref1 = $rst->fields['extref1'];
	$extref2 = $rst->fields['extref2'];
	$rst->close();
}

$sql = "select account_status_pretty_name, account_status_id from account_statuses where account_status_record_status = 'a'";
$rst = $con->execute($sql);
$account_status_menu = $rst->getmenu2('account_status_id', $account_status_id, false);
$rst->close();

$sql = "select rating_pretty_name, rating_id from ratings where rating_record_status = 'a'";
$rst = $con->execute($sql);
$rating_menu = $rst->getmenu2('rating_id', $rating_id, false);
$rst->close();

$con->close();

$page_title = $company_name . " - Admin";

start_page($page_title, true, $msg);

?>

<table border=0 cellpadding=0 cellspacing=0 width=100%>
	<tr>
		<td class=lcol width=25% valign=top>

		<form action=admin-2.php method=post>
		<input type=hidden name=company_id value=<?php echo $company_id; ?>>
		<table class=widget cellspacing=1 width=100%>
			<tr>
				<td class=widget_header colspan=2>Edit Account Information</td>
			</tr>
			<tr>
				<td class=widget_label_right>Account&nbsp;Status</td>
				<td class=widget_content_form_element><?php echo $account_status_menu; ?></td>
			</tr>
			<tr>
				<td class=widget_label_right>Tax&nbsp;ID</td>
				<td class=widget_content_form_element><input type=text size=10 name=tax_id value="<?php echo $tax_id; ?>"></td>
			</tr>
			<tr>
				<td class=widget_label_right>Credit&nbsp;Limit</td>
				<td class=widget_content_form_element><input type=text size=10 name=credit_limit value="<?php echo $credit_limit; ?>"></td>
			</tr>
			<tr>
				<td class=widget_label_right>Rating</td>
				<td class=widget_content_form_element><?php echo $rating_menu; ?></td>
			</tr>
			<tr>
				<td class=widget_label_right>Terms</td>
				<td class=widget_content_form_element>Net &nbsp;<input type=text size=3 name=terms value="<?php echo $terms; ?>"> Days</td>
			</tr>
			<tr>
				<td class=widget_label_right>Customer Key</td>
				<td class=widget_content_form_element><input type=text size=10 name=extref1 value="<?php echo $extref1; ?>"></td>
			</tr>
			<tr>
				<td class=widget_label_right>Vendor Key</td>
				<td class=widget_content_form_element><input type=text size=10 name=extref2 value="<?php echo $extref2; ?>"></td>
			</tr>
			<tr>
				<td class=widget_content_form_element colspan=2><input class=button type=submit value="Save Changes"></td>
			</tr>
		</table>
		</form>

		<form action="delete.php" method=post onsubmit="javascript: return confirm('Delete Company?');">
		<input type=hidden name=company_id value="<?php echo $company_id; ?>">
		<table class=widget cellspacing=1 width=100%>
			<tr>
				<td class=widget_header colspan=4>Delete Company</td>
			</tr>
			<tr>
				<td class=widget_content>
				<p>Click the button below to remove this company (and all associated contacts, activities, opportunities, cases, etc.) from the system.
				<p><input class=button type=submit value="Delete Company">
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