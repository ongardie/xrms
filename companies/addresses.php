<?php
/**
 * Set addresses for a company
 *
 * $Id: addresses.php,v 1.18 2004/08/03 13:41:15 maulani Exp $
 */

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();

$msg        = isset($_GET['msg'])     ? $_GET['msg']     : '';
$company_id = isset($_GET['company_id']) ? $_GET['company_id'] : '';

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
// $con->debug = 1;

$company_name = fetch_company_name($con, $company_id);

$sql = "select * from companies c, addresses a
where a.address_record_status = 'a'
and c.company_id = a.company_id
and c.company_id = $company_id";
$rst = $con->execute($sql);

$addresses = '';
if ($rst) {
    while (!$rst->EOF) {
        $addresses .= '<tr>';
        $addresses .= "<td class=widget_label_right_91px><a href=edit-address.php?company_id=$company_id&address_id=" . $rst->fields['address_id'] . '>' . $rst->fields['address_name'] . '</a></td>';
        $address_to_display = get_formatted_address($con, $rst->fields['address_id']);

        $sql2 = "select contact_id, address_id, last_name, first_names  from contacts
        where contact_record_status='a' and address_id = " . $rst->fields['address_id'];
	$rst2 = $con->execute($sql2);
        $addresses .= "<td class=widget_content>";
	while(!$rst2->EOF) {
		$addresses .= "<a href='../contacts/one.php?contact_id="
                    . $rst2->fields['contact_id'] . "'>"
                    . $rst2->fields['first_names'] . " "
                    . $rst2->fields['last_name'] . "</a><br>";
		    $rst2->MoveNext();
	}
	$addresses .= "</td>";

        $addresses .= "<td class=widget_content>$address_to_display</td>";
        $addresses .= "<td class=widget_content><input type=radio name=default_primary_address value=" . $rst->fields['address_id'];

        if ($rst->fields['default_primary_address'] == $rst->fields['address_id']) {
            $addresses .= ' checked';
        }

        $addresses .= '></td>';
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
        <form action=set-address-defaults.php method=post>
        <input type=hidden name=company_id value=<?php  echo $company_id; ?>>
        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=7><?php echo _("Addresses"); ?></td>
            </tr>
            <tr>
                <td class=widget_label><?php echo _("Address Name"); ?></td>
                <td class=widget_label><?php echo _("Used by Contacts"); ?></td>
                <td class=widget_label><?php echo _("Formatted Address"); ?></td>
                <td class=widget_label><?php echo _("Primary Default"); ?></td>
                <td class=widget_label><?php echo _("Billing Default"); ?></td>
                <td class=widget_label><?php echo _("Shipping Default"); ?></td>
                <td class=widget_label><?php echo _("Payment Default"); ?></td>
            </tr>
            <?php  echo $addresses; ?>
            </tr>
            </tr>
                <td class=widget_content_form_element colspan=7><input class=button type=submit value="<?php echo _("Save Defaults"); ?>"></td>
            </tr>
        </table>
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
