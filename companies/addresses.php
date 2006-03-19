<?php
/**
 * Set addresses for a company
 *
 * $Id: addresses.php,v 1.28 2006/03/19 01:51:39 ongardie Exp $
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
getGlobalVar($edit_contact_id, 'edit_contact_id');
getGlobalVar($final_return_url, 'final_return_url');

getGlobalVar($address_street, 'address_street');
getGlobalVar($address_city, 'address_city');
getGlobalVar($address_province, 'address_province');
getGlobalVar($address_country, 'address_country');
getGlobalVar($address_postal_code,'address_postal_code');

$return_url="addresses.php?company_id=$company_id&edit_contact_id=$edit_contact_id";

$url_return_url=urlencode($return_url);
global $con;
$con = get_xrms_dbconnection();
// $con->debug = 1;

$company_name = fetch_company_name($con, $company_id);

// $addresses .= "<td class=widget_label_right_91px><a href=one-address.php?company_id=$company_id&address_id=" . $rst->fields['address_id'] . '>' . $rst->fields['address_name'] . '</a></td>';

$sql = "select " . 
$con->Concat($con->qstr('<a href="one-address.php?form_action=edit&return_url='.$url_return_url.'&company_id=' . $company_id . '&address_id='), 'a.address_id', $con->qstr('">'), 'a.address_name', $con->qstr('</a>')) . " as address_link,
a.address_name, a.address_id, c.default_primary_address, c.default_billing_address, c.default_shipping_address, c.default_payment_address from companies c, addresses a
where a.address_record_status = 'a'
and c.company_id = a.company_id
and c.company_id = $company_id";

if ($address_street) {
    $sql .= " and (line1 LIKE " . $con->qstr("%$address_street%") . " OR line2 LIKE " . $con->qstr("%$address_street%"). ") ";
}
if ($address_city) {
    $sql .= " and city LIKE " . $con->qstr("%$address_city%");
}
if ($address_province) {
    $sql .= " and province LIKE " .$con->qstr("%$address_province%");
}
if ($address_country) {
    $sql .= " and country_id=$address_country";
}
if ($address_postal_code) {
    $sql .= " and postal_code LIKE " . $con->qstr("%$address_postal_code%");
}

$columns=array();
$columns[] = array('name' => _('Address Name'), 'index_sql' => 'address_link', 'sql_sort_column' => 'a.address_name');
$columns[] = array('name' => _('Used By Contacts'), 'index_calc' => 'used_by_contacts');
$columns[] = array('name' => _('Formatted Address'), 'index_calc' => 'formatted_address');
if (!$edit_contact_id) {
    $columns[] = array('name' => _('Primary Default'), 'index_calc' => 'primary_default', 'not_sortable' => 'true', 'css_classname' => 'center');
    $columns[] = array('name' => _('Billing Default'), 'index_calc' => 'billing_default', 'not_sortable' => 'true', 'css_classname' => 'center');
    $columns[] = array('name' => _('Shipping Default'), 'index_calc' => 'shipping_default', 'not_sortable' => 'true', 'css_classname' => 'center');
    $columns[] = array('name' => _('Payment Default'), 'index_calc' => 'payment_default', 'not_sortable' => 'true', 'css_classname' => 'center');
} else {
    $columns[] = array('name' => _('Business Address'), 'index_calc' => 'business_address', 'not_sortable' => 'true', 'css_classname' => 'center');
}
function GetAddressesPagerData($row) {
	global $con;
        global $edit_contact_id;
        
	// formatted_address
	$row['formatted_address'] = get_formatted_address($con, $row['address_id']);

	// used_by_contacts
    $sql2 = "SELECT contact_id, address_id, home_address_id, last_name, first_names  FROM contacts WHERE contact_record_status='a' AND (address_id = ".$row['address_id']." OR home_address_id = ".$row['address_id'].")";
    $rst2 = $con->execute($sql2);

    $row['used_by_contacts'] = '';
	if($rst2) {
    	while(!$rst2->EOF) {
                if ($rst2->fields['contact_id']==$edit_contact_id) {
                    $business_address=' checked';                    
                }
        	$row['used_by_contacts'] .= "<a href='../contacts/one.php?contact_id="
                    	. $rst2->fields['contact_id'] . "'>"
                    	. $rst2->fields['first_names'] . " "
                    	. $rst2->fields['last_name'] . "</a><br>";
        	$rst2->MoveNext();
    	}
	}

        if (!$edit_contact_id) {
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
        } else {
            $row['business_address'] = "<input type=radio name=alt_address value=\"{$row['address_id']}\" $business_address";
        }
	return $row;
}

$pager = new GUP_Pager($con, $sql, 'GetAddressesPagerData', _('Addresses'), 'AddressPagerForm', 'AddressesPager', $columns, false, true);

// Save Defaults button posts to set-address-defaults.php
if (!$edit_contact_id) {
    $endrows = "<tr><td class=widget_content_form_element colspan=10><input class=button type=button onclick=\"document.AddressPagerForm.action='set-address-defaults.php'; document.AddressPagerForm.submit();\" value=\"" . _("Save Defaults") . "\"><input type=button class=button name=btBackToCompany onclick=\"javascript: location.href='one.php?company_id=$company_id'\" value=\""._("Back To Company")."\"></td></tr>";
} else {
    if (!$final_return_url) $contact_return_url="$http_site_root/contacts/edit.php?contact_id=$edit_contact_id";
    else $contact_return_url=$final_return_url;
    $endrows = "<tr><td class=widget_content_form_element colspan=10><input type=hidden name=return_url value=\"$contact_return_url\"><input class=button type=button onclick=\"document.AddressPagerForm.action='../contacts/edit-address-2.php'; document.AddressPagerForm.submit();\" value=\"" . _("Update Contact") . "\"></td></tr>";
}    
$pager->AddEndRows($endrows);

global $system_rows_per_page;

$address_pager = $pager->Render($system_rows_per_page);

$address_type_menu = build_address_type_menu($con, 'unknown');

$sql = "select country_name, country_id from countries where country_record_status = 'a' order by country_name";
$rst = $con->execute($sql);
//$country_menu = $rst->getmenu2('country_id', $default_country_id, false);
$search_country_menu = $rst->getmenu2('address_country', $address_country,true);
$rst->close();

$con->close();

$page_title = $company_name . " - " . _("Addresses");
start_page($page_title, true, $msg);
$address_action="addresses.php";
?>

<div id="Main">
    <div id="Content">

        <!-- existing addresses //-->
        <form action="<?php echo $address_action; ?>" method=post name="AddressPagerForm">
        
        <table class=widget>
            <tr><td class=widget_header colspan=6><?php echo _("Search Addresses"); ?></td></tr>
            <tr><td class=widget_label><?php echo _("Street"); ?></td><td class=widget_content_form_element><input type=text size=15 name=address_street value="<?php echo $address_street; ?>"></td>
            <td class=widget_label><?php echo _("City"); ?></td><td class=widget_content_form_element><input type=text size=15 name=address_city value="<?php echo $address_city; ?>"></td><td class=widget_label><?php echo _("Postcode"); ?></td><td class=widget_content_form_element><input type=text size=7 name=address_postal_code value="<?php echo $address_postal_code; ?>"></td></tr>
            <tr><td class=widget_label><?php echo _("State/Province"); ?></td><td class=widget_content_form_element><input type=text size=15 name=address_province value="<?php echo $address_province; ?>"></td>
            <td class=widget_label><?php echo _("Country"); ?></td><td class=widget_content_form_element colspan=3><?php echo $search_country_menu; ?></td></tr>
            <tr><td class=widget_content_form_element colspan=6><input type=submit class=button value="<?php echo _("Search Addresses"); ?>"></td></tr>
            
        </table>
            
        <input type=hidden name=company_id value=<?php  echo $company_id; ?>>
        <input type=hidden name=edit_contact_id value=<?php  echo $edit_contact_id; ?>>
        <input type=hidden name=final_return_url value="<?php  echo $final_return_url; ?>">
        <input type=hidden name=contact_id value=<?php  echo $edit_contact_id; ?>>
		<?php echo $address_pager; ?>
         </form>

        <!-- new address //-->
        <form action=one-address.php method=post>
        
        <input type=hidden name=company_id value=<?php  echo $company_id; ?>>
        <input type=hidden name=form_action value=new>
        <input type=hidden name=return_url value=<?php echo "$http_site_root/companies/addresses.php?msg=saved&company_id=$company_id&edit_contact_id=$edit_contact_id&final_return_url=".urlencode($final_return_url); ?>>
        <input type=hidden name=final_return_url value="<?php echo $final_return_url; ?>">
    </div>
    <div id="Sidebar">
        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=2><?php echo _("Add New Address"); ?></td>
            </tr>
            <tr>
                <td class=widget_content_form_element colspan=2><input class=button type=submit value="<?php echo _("New Address"); ?>"></td>
            </tr>
        </table>
        </form>

    </div>
</div>

<?php

end_page();

/**
 * $Log: addresses.php,v $
 * Revision 1.28  2006/03/19 01:51:39  ongardie
 * - Used By Contacts now also includes contacts with the address as their home.
 *
 * Revision 1.27  2006/01/02 22:56:26  vanmer
 * - changed to use centralized dbconnection function
 *
 * Revision 1.26  2005/08/11 02:46:19  vanmer
 * - added postal code to mini search on addresses
 * - added qstr over all input'd variables
 *
 * Revision 1.25  2005/08/04 20:08:39  vanmer
 * - added a parameter to allow a final redirection after address has been selected, instead of automatically
 * redirecting to contact edit page
 *
 * Revision 1.24  2005/07/08 02:30:06  vanmer
 * - added link back to company when editing company addresses
 * - added refresh back to contact edit page when done selecting address
 *
 * Revision 1.23  2005/07/06 02:08:29  vanmer
 * - added support for selecting a business address for a contact using addresses pager
 *
 * Revision 1.22  2005/07/06 01:27:54  vanmer
 * - added search to top of addresses page
 * - added links to one-address.php instead of new address code
 * - added links to one-address.php instead of edit address pages
 *
 * Revision 1.21  2005/04/11 02:06:48  maulani
 * - Add address type.  RFE 862049 (maulani)
 *
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
