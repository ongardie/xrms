<?php
/**
 * Edit address for a contact
 *
 * $Id: edit-address.php,v 1.2 2004/06/10 17:49:19 gpowers Exp $
 */

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

$session_user_id = session_check();
$msg = $_GET['msg'];
$contact_id = $_GET['contact_id'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
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
    $address_body = $rst->fields['address_body'];
    $use_pretty_address = $rst->fields['use_pretty_address'];
    $rst->close();
}

$sql = "select country_name, country_id from countries where country_record_status = 'a' order by country_name";
$rst = $con->execute($sql);
if (!$country_id) {$country_id = $default_country_id;}

$country_menu = $rst->getmenu2('country_id', $country_id, false);
$rst->close();

$company_name = fetch_company_name($con, $company_id);

$sql = "select * from companies c
where c.company_record_status = 'a'
and c.company_id = $company_id";

$rst = $con->execute($sql);

if ($rst) {
    while (!$rst->EOF) {
        $show_company = false;
        $company_addresses = "";
        if ($rst->fields['default_primary_address'] == $address_id) {
            $company_addresses .= 'Primary Address<br />';
            $show_company = true;
        }

        if ($rst->fields['default_billing_address'] == $address_id) {
            $company_addresses .= 'Billing Address<br />';
            $show_company = true;
        }

        if ($rst->fields['default_shipping_address'] == $address_id) {
            $company_addresses .= 'Shipping Address<br />';
            $show_company = true;
        }

        if ($rst->fields['default_payment_address'] == $address_id) {
            $company_addresses .= 'Payment Address<br />';
            $show_company = true;
        }

        if ($show_company) {
            $addresses .= '<tr>';
            $addresses .= "<td class=widget_label_right><a href=../companies/edit-address.php?company_id=$company_id&address_id=" . $rst->fields['address_id'] . '>' . $rst->fields['company_name'] . '</a><td class=widget_content>';
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


$page_title = $contact_name . " - Edit Address";
start_page($page_title, true, $msg);

?>

<div id="Main">
    <div id="Content">

        <form action=edit-address-2.php method=post>
        <input type=hidden name=contact_id value=<?php echo $contact_id; ?>>
        <input type=hidden name=company_id value=<?php echo $company_id; ?>>
        <input type=hidden name=address_id value=<?php echo $address_id; ?>>
        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=2>This Address Is Also Used By</td>
            </tr>
            <?php echo $addresses; ?>
            <tr>
                <td class=widget_header colspan=2>Create New Address?</td>
            </tr>
            <tr>
                <td></td>
                <td class=widget_label colspan=2>
                    Editing/Deleting this record will change the address for <em><b>ALL</b</em>
                    companies and contacts listed abve.<br /><br />
                    Create a New Address? <input type=checkbox name=new>
                </td>
            </tr>
            <tr>
                <td class=widget_header colspan=2>Edit Address</td>
            </tr>
            <tr>
                <td class=widget_label_right>Contact</td>
                <td class=widget_content><a href="one.php?contact_id=<?php echo $contact_id; ?>"><?php  echo $contact_name; ?></a></td>
            </tr>
            <tr>
                <td class=widget_label_right>Address Name</td>
                <td class=widget_content_form_element><input type=text size=30 name=address_name value="<?php echo $address_name; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right>Line 1</td>
                <td class=widget_content_form_element><input type=text size=30 name=line1 value="<?php echo $line1; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right>Line 2</td>
                <td class=widget_content_form_element><input type=text size=30 name=line2 value="<?php echo $line2; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right>City</td>
                <td class=widget_content_form_element><input type=text size=30 name=city value="<?php echo $city; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right>State/Province</td>
                <td class=widget_content_form_element><input type=text size=20 name=province value="<?php echo $province; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right>Postal Code</td>
                <td class=widget_content_form_element><input type=text size=10 name=postal_code value="<?php echo $postal_code; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right>Country</td>
                <td class=widget_content_form_element><?php echo $country_menu; ?></td>
            </tr>
            <tr>
                <td class=widget_label_right_91px>Address Body</td>
                <td class=widget_content_form_element><textarea rows=5 cols=60 name=address_body><?php echo $address_body; ?></textarea> <input type="checkbox" name="use_pretty_address"<?php if ($use_pretty_address == 't') {echo " checked";} ?>> Use</td>
            </tr>
            <tr>
                <td class=widget_content_form_element colspan=2><input class=button type=submit value="Save Changes"> <input class=button type=button value="Delete Address" onclick="javascript: location.href='delete-address.php?contact_id=<?php echo $contact_id ?>&address_id=<?php echo $address_id ?>';"></td>
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
 *
 *
 */
?>
