<?php
/**
 * Set addresses for a company
 *
 * $Id: addresses.php,v 1.20 2005/04/05 18:09:07 daturaarutad Exp $
 */

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');
require_once ($include_directory . 'classes/Pager/Pager_Columns.php');
require_once ($include_directory . 'classes/Pager/GUP_Pager.php');


$session_user_id = session_check();

getGlobalVar($msg, 'msg');
getGlobalVar($company_id, 'company_id');

global $con;
$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
// $con->debug = 1;

$company_name = fetch_company_name($con, $company_id);

 $addresses .= "<td class=widget_label_right_91px><a href=edit-address.php?company_id=$company_id&address_id=" . $rst->fields['address_id'] . '>' . $rst->fields['address_name'] . '</a></td>';

$sql = "select " . 
$con->Concat($con->qstr('<a href="edit-address.php?company_id=' . $company_id . '&address_id='), 'a.address_id', $con->qstr('">'), 'a.address_name', $con->qstr('</a>')) . " as address_link,
a.address_name, a.address_id, c.default_primary_address, c.default_billing_address, c.default_shipping_address, c.default_payment_address from companies c, addresses a
where a.address_record_status = 'a'
and c.company_id = a.company_id
and c.company_id = $company_id";


$columns=array();
$columns[] = array('name' => _('Address Name'), 'index_sql' => 'address_link', 'sql_sort_column' => 'a.address_name');
$columns[] = array('name' => _('Used By Contacts'), 'index_calc' => 'used_by_contacts');
$columns[] = array('name' => _('Formatted Address'), 'index_calc' => 'formatted_address');
$columns[] = array('name' => _('Primary Default'), 'index_calc' => 'primary_default', 'not_sortable' => 'true', 'css_classname' => 'center');
$columns[] = array('name' => _('Billing Default'), 'index_calc' => 'billing_default', 'not_sortable' => 'true', 'css_classname' => 'center');
$columns[] = array('name' => _('Shipping Default'), 'index_calc' => 'shipping_default', 'not_sortable' => 'true', 'css_classname' => 'center');
$columns[] = array('name' => _('Payment Default'), 'index_calc' => 'payment_default', 'not_sortable' => 'true', 'css_classname' => 'center');


function GetAddressesPagerData($row) {
	global $con;

	// formatted_address
	$row['formatted_address'] = get_formatted_address($con, $row['address_id']);

	// used_by_contacts
    $sql2 = "select contact_id, address_id, last_name, first_names  from contacts where contact_record_status='a' and address_id = {$row['address_id']}";
    $rst2 = $con->execute($sql2);

    $row['used_by_contacts'] = '';
	if($rst2) {
    	while(!$rst2->EOF) {
        	$row['used_by_contacts'] .= "<a href='../contacts/one.php?contact_id="
                    	. $rst2->fields['contact_id'] . "'>"
                    	. $rst2->fields['first_names'] . " "
                    	. $rst2->fields['last_name'] . "</a><br>";
        	$rst2->MoveNext();
    	}
	}

	// form elements
	$row['primary_default'] = "<input type=radio name=default_primary_address value=" . $row['address_id'];
	if($row['default_primary_address'] == $row['address_id']) {
		$row['primary_default'] .= ' checked';
	}
	$row['primary_default'] .= '>';

	$row['billing_default'] = "<input type=radio name=default_billing_address value=" . $row['address_id'];
	if($row['default_billing_address'] == $row['address_id']) {
		$row['billing_default'] .= ' checked';
	}
	$row['billing_default'] .= '>';

	$row['shipping_default'] = "<input type=radio name=default_shipping_address value=" . $row['address_id'];
	if($row['default_shipping_address'] == $row['address_id']) {
		$row['shipping_default'] .= ' checked';
	}
	$row['shipping_default'] .= '>';

	$row['payment_default'] = "<input type=radio name=default_payment_address value=" . $row['address_id'];
	if($row['default_payment_address'] == $row['address_id']) {
		$row['payment_default'] .= ' checked';
	}
	$row['payment_default'] .= '>';

	return $row;
}

$pager = new GUP_Pager($con, $sql, 'GetAddressesPagerData', _('Addresses'), 'AddressPagerForm', 'AddressesPager', $columns, false, true);

// Save Defaults button posts to set-address-defaults.php
$endrows = "<tr><td class=widget_content_form_element colspan=10><input class=button type=button onclick=\"document.AddressPagerForm.action='set-address-defaults.php'; document.AddressPagerForm.submit();\" value=\"" . _("Save Defaults") . "\"></td></tr>";
$pager->AddEndRows($endrows);

global $system_rows_per_page;

$address_pager = $pager->Render($system_rows_per_page);



$sql = "select country_name, country_id from countries where country_record_status = 'a' order by country_name";
$rst = $con->execute($sql);
$country_menu = $rst->getmenu2('country_id', $default_country_id, false);
$rst->close();

$con->close();

$page_title = $company_name . " - " . _("Addresses");
start_page($page_title, true, $msg);

?>

<div id="Main">
    <div id="ContentFullWidth">

        <!-- existing addresses //-->
        <form action=addresses.php method=post name="AddressPagerForm">
        <input type=hidden name=company_id value=<?php  echo $company_id; ?>>
		<?php echo $address_pager; ?>
         </form>

        <!-- new address //-->
        <form action=add-address.php method=post>
        <input type=hidden name=company_id value=<?php  echo $company_id; ?>>
        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=2><?php echo _("New Address"); ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Company"); ?></td>
                <td class=widget_content><a href="../companies/one.php?company_id=<?php echo $company_id; ?>"><?php  echo $company_name; ?></a></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Address Name"); ?></td>
                <td class=widget_content_form_element><input type=text name=address_name size=30></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Line 1"); ?></td>
                <td class=widget_content_form_element><input type=text name=line1 size=30></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Line 2"); ?></td>
                <td class=widget_content_form_element><input type=text name=line2 size=30></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("City"); ?></td>
                <td class=widget_content_form_element><input type=text name=city size=30></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("State/Province"); ?></td>
                <td class=widget_content_form_element><input type=text name=province size=20></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Postal Code"); ?></td>
                <td class=widget_content_form_element><input type=text name=postal_code size=10></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Country"); ?></td>
                <td class=widget_content_form_element><?php echo $country_menu ?></td>
            </tr>
            <tr>
                <td class=widget_label_right_91px><?php echo _("Override Address"); ?></td>
                <td class=widget_content_form_element><textarea rows=5 cols=60 name=address_body></textarea> <input type="checkbox" name="use_pretty_address"> <?php echo _("Use"); ?></td>
            </tr>
            <tr>
                <td class=widget_content_form_element colspan=2><input class=button type=submit value="<?php echo _("Add"); ?>"></td>
            </tr>
        </table>
        </form>

    </div>
</div>

<?php

end_page();

/**
 * $Log: addresses.php,v $
 * Revision 1.20  2005/04/05 18:09:07  daturaarutad
 * fixed missing edit link
 *
 * Revision 1.19  2005/04/01 23:06:19  daturaarutad
 * moved address listing into a GUP_Pager
 *
 * Revision 1.18  2004/08/03 13:41:15  maulani
 * - Use full width since sidebar not needed
 *
 * Revision 1.17  2004/08/02 22:09:55  maulani
 * - Company addresses screen will no longer show deleted contacts
 *
 * Revision 1.16  2004/07/21 19:17:56  introspectshun
 * - Localized strings for i18n/l10n support
 *
 * Revision 1.15  2004/07/19 12:52:01  cpsource
 * - Fix undefined variable usages.
 *
 * Revision 1.14  2004/06/30 11:39:25  braverock
 * - fixes a bug in showing contacts using address
 *   - patch supplied by David Uhlman
 *
 * Revision 1.13  2004/06/12 05:03:16  introspectshun
 * - Now use ADODB GetInsertSQL, GetUpdateSQL, date and Concat functions.
 * - Corrected order of arguments to implode() function.
 *
 * Revision 1.12  2004/06/09 18:07:52  gpowers
 * - fixed reversal of "Used by Contacts" and "Formatted Address" columns
 *
 * Revision 1.11  2004/06/09 18:05:56  gpowers
 * - added "Used by Contacts" which lists which contacts are using each
 *   address, this also allows Billing/Shipping/Payment address for a
 *   company to be changed to the address of a contact of the company
 *
 * Revision 1.10  2004/06/04 15:47:15  braverock
 * - move current addresses to top of screen, because this is the most used functionality
 *
 * Revision 1.9  2004/05/21 13:06:09  maulani
 * - Create get_formatted_address function which centralizes the address
 *   formatting code into one routine in utils-misc.
 *
 * Revision 1.8  2004/04/16 22:19:38  maulani
 * - Add CSS2 positioning
 *
 * Revision 1.7  2004/04/08 17:00:59  maulani
 * - Update javascript declaration
 * - Add phpdoc
 *
 *
 */
?>
