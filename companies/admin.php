<?php
/**
 * Set admin items for a company
 *
 * $Id: admin.php,v 1.8 2006/01/02 22:56:26 vanmer Exp $
 */

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

// require_once($include_directory . 'phpgacl/gacl.class.php');

$session_user_id = session_check();

$msg        = isset($_GET['msg'])        ? $_GET['msg'] : '';
$company_id = isset($_GET['company_id']) ? $_GET['company_id'] : '';

// $gacl = new gacl();
// $gacl_check = $gacl->acl_check('users', $session_user_id, 'company', 'view', 'companies', $company_id);
// $gacl_check = ($gacl_check) ? "True" : "False";

$con = get_xrms_dbconnection();

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

$page_title = $company_name . " - " . _("Admin");

start_page($page_title, true, $msg);

?>

<div id="Main">
    <div id="Content">

		<form action=admin-2.php method=post>
		<input type=hidden name=company_id value=<?php echo $company_id; ?>>
		<table class=widget cellspacing=1>
			<tr>
				<td class=widget_header colspan=2><?php echo _("Edit Account Information"); ?></td>
			</tr>
			<tr>
				<td class=widget_label_right><?php echo _("Account Status"); ?></td>
				<td class=widget_content_form_element><?php echo $account_status_menu; ?></td>
			</tr>
			<tr>
				<td class=widget_label_right><?php echo _("Tax ID"); ?></td>
				<td class=widget_content_form_element><input type=text size=10 name=tax_id value="<?php echo $tax_id; ?>"></td>
			</tr>
			<tr>
				<td class=widget_label_right><?php echo _("Credit Limit"); ?></td>
				<td class=widget_content_form_element><input type=text size=10 name=credit_limit value="<?php echo $credit_limit; ?>"></td>
			</tr>
			<tr>
				<td class=widget_label_right><?php echo _("Rating"); ?></td>
				<td class=widget_content_form_element><?php echo $rating_menu; ?></td>
			</tr>
			<tr>
				<td class=widget_label_right><?php echo _("Terms"); ?></td>
				<td class=widget_content_form_element>Net &nbsp;<input type=text size=3 name=terms value="<?php echo $terms; ?>"> Days</td>
			</tr>
			<tr>
				<td class=widget_label_right><?php echo _("Customer Key"); ?></td>
				<td class=widget_content_form_element><input type=text size=10 name=extref1 value="<?php echo $extref1; ?>"></td>
			</tr>
			<tr>
				<td class=widget_label_right><?php echo _("Vendor Key"); ?></td>
				<td class=widget_content_form_element><input type=text size=10 name=extref2 value="<?php echo $extref2; ?>"></td>
			</tr>
			<tr>
				<td class=widget_content_form_element colspan=2><input class=button type=submit value="<?php echo _("Save Changes"); ?>"></td>
			</tr>
		</table>
		</form>

		<form action="delete.php" method=post onsubmit="javascript: return confirm('<?php echo _("Delete Company?"); ?>');">
		<input type=hidden name=company_id value="<?php echo $company_id; ?>">
		<table class=widget cellspacing=1>
			<tr>
				<td class=widget_header colspan=4><?php echo _("Delete Company"); ?></td>
			</tr>
			<tr>
				<td class=widget_content>
				<p><?php echo _("Click the button below to remove this company (and all associated contacts, activities, opportunities, cases, etc.) from the system."); ?>
				<p><input class=button type=submit value="<?php echo _("Delete Company"); ?>">
				</td>
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
 * $Log: admin.php,v $
 * Revision 1.8  2006/01/02 22:56:26  vanmer
 * - changed to use centralized dbconnection function
 *
 * Revision 1.7  2004/07/21 19:17:56  introspectshun
 * - Localized strings for i18n/l10n support
 *
 * Revision 1.6  2004/07/19 20:59:31  cpsource
 * - Fix undefined $msg
 *
 * Revision 1.5  2004/06/12 05:03:16  introspectshun
 * - Now use ADODB GetInsertSQL, GetUpdateSQL, date and Concat functions.
 * - Corrected order of arguments to implode() function.
 *
 * Revision 1.4  2004/04/16 22:19:38  maulani
 * - Add CSS2 positioning
 *
 * Revision 1.3  2004/04/08 17:00:59  maulani
 * - Update javascript declaration
 * - Add phpdoc
 *
 *
 */
?>
