<?php
/**
 * Edit address for a contact
 *
 * $Id: edit-address.php,v 1.12 2006/01/02 22:59:59 vanmer Exp $
 */

require_once('include-locations-location.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');
require_once($include_directory . 'confgoto.php');

$session_user_id = session_check();
$msg = isset($_GET['msg']) ? $_GET['msg']: '';
$contact_id = $_GET['contact_id'];

$con = get_xrms_dbconnection();
// $con->debug = 1;

// This query is done separately in case there is no current address
$sql = "select * from contacts where contact_id = $contact_id";
$rst = $con->execute($sql);

$contact_name = $rst->fields['first_names'] . ' ' . $rst->fields['last_name'];
$company_id =  $rst->fields['company_id'];
$address_id =  $rst->fields['address_id'];

$sql = "select a.*, c.* from contacts c, addresses a where a.address_id = c.address_id and c.contact_id = $contact_id";

$rst = $con->execute($sql);

if ($rst) {
    $country_id = $rst->fields['country_id'];
    $address_name = $rst->fields['address_name'];
    $line1 = $rst->fields['line1'];
    $line2 = $rst->fields['line2'];
    $city = $rst->fields['city'];
    $province = $rst->fields['province'];
    $postal_code = $rst->fields['postal_code'];
    $address_type = $rst->fields['address_type'];
    $address_body = $rst->fields['address_body'];
    $use_pretty_address = $rst->fields['use_pretty_address'];
    $rst->close();
}

$address_type_menu = build_address_type_menu($con, $address_type);

$sql = "select country_name, country_id from countries where country_record_status = 'a' order by country_name";
$rst = $con->execute($sql);
if (!$country_id) {$country_id = $default_country_id;}

$country_menu = $rst->getmenu2('country_id', $country_id, false);
$rst->close();

$company_name = fetch_company_name($con, $company_id);


$sql = "select * from addresses a
where address_record_status = 'a'
and company_id = $company_id";

$rst = $con->execute($sql);

if ($rst) {
    while (!$rst->EOF) {
        $alt_addresses .= "<tr><td class=widget_label_right>"
                        . "<input type=radio name=alt_address value=\"" . $rst->fields['address_id'] . "\">"
                        . "<a href=../companies/edit-address.php?company_id=$company_id&address_id="
                        . $rst->fields['address_id'] . '>' . $rst->fields['address_name'] . '</a><td class=widget_content>'
                        . get_formatted_address($con, $rst->fields['address_id']) . "</td></tr>";
        $rst->movenext();
    }
    $rst->close();
}


$sql = "select * from companies c
where c.company_record_status = 'a'
and c.company_id = $company_id";

$rst = $con->execute($sql);

if ($rst) {
    while (!$rst->EOF) {
        $show_company = false;
        $company_addresses = "";
        if ($rst->fields['default_primary_address'] == $address_id) {
            $company_addresses .= _("Primary Address") . '<br />';
            $show_company = true;
        }

        if ($rst->fields['default_billing_address'] == $address_id) {
            $company_addresses .= _("Billing Address") . '<br />';
            $show_company = true;
        }

        if ($rst->fields['default_shipping_address'] == $address_id) {
            $company_addresses .= _("Shipping Address") . '<br />';
            $show_company = true;
        }

        if ($rst->fields['default_payment_address'] == $address_id) {
            $company_addresses .= _("Payment Address") . '<br />';
            $show_company = true;
        }

        if ($show_company) {
            $addresses .= '<tr>';
            $addresses .= "<td class=widget_label_right><a href=../companies/one.php?company_id=$company_id" . '>'
                           . $rst->fields['company_name'] . '</a><td class=widget_content>';
            $addresses .= $company_addresses . "</td>";
            $addresses .= '</tr>';
        }
        $rst->movenext();
    }
    $rst->close();
}


$sql = "select contact_id, company_id, address_id, last_name, first_names  from contacts
        where address_id = " . $address_id;
$rst = $con->execute($sql);

if ($rst) {
    while (!$rst->EOF) {
        $addresses .= "<tr>"
                    . "<td class=widget_label_right>"
                    . fetch_company_name($con, $company_id)
                    . "</td><td class=widget_content>"
                    . "<a href=\"one.php?contact_id="
                    . $rst->fields['contact_id'] . "\">"
                    . $rst->fields['first_names'] . " "
                    . $rst->fields['last_name'] . "</a><br />"
                    . "</td>"
                    . "<tr>";
        $rst->movenext();
    }
    $rst->close();
}

$con->close();


$page_title = $contact_name . " - " . _("Edit Address");
start_page($page_title, true, $msg);

// include confGoTo javascrip module
confGoTo_includes();

?>

<div id="Main">
    <div id="Content">

        <form action=edit-address-2.php method=post>
        <input type=hidden name=contact_id value=<?php echo $contact_id; ?>>
        <input type=hidden name=company_id value=<?php echo $company_id; ?>>
        <input type=hidden name=address_id value=<?php echo $address_id; ?>>
        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=2><?php echo _("This Address Is Also Used By"); ?></td>
            </tr>
            <?php echo $addresses; ?>
            <tr>
                <td class=widget_header colspan=2><?php echo _("Use Alternate Address"); ?></td>
            </tr>
            <?php echo $alt_addresses; ?>
            <tr>
                <td class=widget_header colspan=2><?php echo _("Create New Address?"); ?></td>
            </tr>
            <tr>
                <td></td>
                <td class=widget_label colspan=2>
                <?php echo _("Editing/Deleting this record will change the address for all companies and contacts listed above.")
                    . "<br /><br />"
                    . _("Create a New Address?")
                    . " <input type=checkbox name=new>";
                ?>
                </td>
            </tr>
            <tr>
                <td class=widget_header colspan=2><?php echo _("Edit Address"); ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Contact"); ?></td>
                <td class=widget_content><a href="one.php?contact_id=<?php echo $contact_id; ?>"><?php  echo $contact_name; ?></a></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Address Name"); ?></td>
                <td class=widget_content_form_element><input type=text size=30 name=address_name value="<?php echo $address_name; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Line 1"); ?></td>
                <td class=widget_content_form_element><input type=text size=30 name=line1 value="<?php echo $line1; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Line 2"); ?></td>
                <td class=widget_content_form_element><input type=text size=30 name=line2 value="<?php echo $line2; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("City"); ?></td>
                <td class=widget_content_form_element><input type=text size=30 name=city value="<?php echo $city; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("State/Province"); ?></td>
                <td class=widget_content_form_element><input type=text size=20 name=province value="<?php echo $province; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Postal Code"); ?></td>
                <td class=widget_content_form_element><input type=text size=10 name=postal_code value="<?php echo $postal_code; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Country"); ?></td>
                <td class=widget_content_form_element><?php echo $country_menu; ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Address Type"); ?></td>
                <td class=widget_content_form_element><?php echo $address_type_menu; ?></td>
            </tr>
            <tr>
                <td class=widget_label_right_91px><?php echo _("Address Body"); ?></td>
                <td class=widget_content_form_element><textarea rows=5 cols=60 name=address_body><?php echo $address_body; ?></textarea> <input type="checkbox" name="use_pretty_address"<?php if ($use_pretty_address == 't') {echo " checked";} ?>> <?php echo _("Use"); ?></td>
            </tr>
            <tr>
                <td class=widget_content_form_element colspan=2>
                  <input class=button type=submit value="<?php echo _("Save Changes"); ?>">
<?php
                  $quest = _("Delete Address?");
                  $button = _("Delete Address");
                  $to_url = "delete-address.php?contact_id=$contact_id&address_id=$address_id";
                  acl_confGoTo( $quest, $button, $to_url, 'contacts', $contact_id, 'Delete' );
?>
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
 * $Log: edit-address.php,v $
 * Revision 1.12  2006/01/02 22:59:59  vanmer
 * - changed to use centralized dbconnection function
 *
 * Revision 1.11  2005/06/07 21:38:55  braverock
 * - fix company name link
 *
 * Revision 1.10  2005/06/01 16:14:11  vanmer
 * - changed delete address button to be controlled by the ACL
 *
 * Revision 1.9  2005/04/11 02:08:44  maulani
 * - Add address types.  RFE 862049 (maulani)
 *
 * Revision 1.8  2004/08/02 22:28:11  maulani
 * - Have delete address button use confGoTo confirmation dialog to confirm delete
 *
 * Revision 1.7  2004/07/30 11:32:01  cpsource
 * - Define msg properly
 *   Fix bug with new.php wereby division_id and address_id were
 *     not set properly for getmenu2.
 *
 * Revision 1.6  2004/07/22 11:21:13  cpsource
 * - All paths now relative to include-locations-location.inc
 *   Code cleanup for Create Contact for 'Self'
 *
 * Revision 1.5  2004/07/21 15:20:04  introspectshun
 * - Localized strings for i18n/translation support
 * - Removed include of lang file
 *
 * Revision 1.4  2004/06/15 17:26:21  introspectshun
 * - Add adodb-params.php include for multi-db compatibility.
 * - Corrected order of arguments to implode() function.
 * - Now use ADODB GetInsertSQL, GetUpdateSQL and Concat functions.
 *
 * Revision 1.3  2004/06/10 18:05:51  gpowers
 * - added "Use Alternate Address" section
 *
 * Revision 1.2  2004/06/10 17:49:19  gpowers
 * - added "This Address Is Also Used By" and "Create New Address?"
 *   to avert unintended editing.
 *
 * Revision 1.1  2004/06/09 16:52:14  gpowers
 * - Contact Address Editing
 * - adapted from companies/edit-address.php
 *
 * Revision 1.5  2004/04/16 22:19:38  maulani
 * - Add CSS2 positioning
 *
 * Revision 1.4  2004/04/08 17:00:59  maulani
 * - Update javascript declaration
 * - Add phpdoc
 */
?>