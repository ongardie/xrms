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
// $con->debug = 1;

$company_name = fetch_company_name($con, $company_id);

$sql = "select * from companies c, addresses a, countries, address_format_strings afs
where a.country_id = countries.country_id
and a.address_record_status = 'a'
and c.company_id = a.company_id
and countries.address_format_string_id = afs.address_format_string_id
and c.company_id = $company_id";

$rst = $con->execute($sql);

if ($rst) {
    while (!$rst->EOF) {
        $addresses .= '<tr>';
        $addresses .= "<td class=widget_label_right_91px><a href=edit-address.php?company_id=$company_id&address_id=" . $rst->fields['address_id'] . '>' . $rst->fields['address_name'] . '</a></td>';
        if ($rst->fields['use_pretty_address'] == 't') {
            $addresses .= '<td class=widget_content>' . nl2br($rst->fields['address_body']) . '</td>';
        } else {
            $addresses .= '<td class=widget_content>'
                         . $rst->fields['line1'] . '<br>'
                         . $rst->fields['line2'] . '<br>'
                         . $rst->fields['city'] . ' , '
                         . $rst->fields['province'] . ' , '
                         . $rst->fields['postal_code']
                         . '</td>';
        }

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

$page_title = $company_name . " - Addresses";
start_page($page_title, true, $msg);

?>

<table border=0 cellpadding=0 cellspacing=0 width=100%>
    <tr>
        <td class=lcol width=65% valign=top>

        <!-- new address //-->
        <form action=add-address.php method=post>
        <input type=hidden name=company_id value=<?php  echo $company_id; ?>>
        <table class=widget cellspacing=1 width=100%>
            <tr>
                <td class=widget_header colspan=2>New Address</td>
            </tr>
            <tr>
                <td class=widget_label_right>Company</td>
                <td class=widget_content><a href="../companies/one.php?company_id=<?php echo $company_id; ?>"><?php  echo $company_name; ?></a></td>
            </tr>
            <tr>
                <td class=widget_label_right>Address Name</td>
                <td class=widget_content_form_element><input type=text name=address_name size=30></td>
            </tr>
            <tr>
                <td class=widget_label_right>Line 1</td>
                <td class=widget_content_form_element><input type=text name=line1 size=30></td>
            </tr>
            <tr>
                <td class=widget_label_right>Line 2</td>
                <td class=widget_content_form_element><input type=text name=line2 size=30></td>
            </tr>
            <tr>
                <td class=widget_label_right>City</td>
                <td class=widget_content_form_element><input type=text name=city size=30></td>
            </tr>
            <tr>
                <td class=widget_label_right>State/Province</td>
                <td class=widget_content_form_element><input type=text name=province size=20></td>
            </tr>
            <tr>
                <td class=widget_label_right>Postal Code</td>
                <td class=widget_content_form_element><input type=text name=postal_code size=10></td>
            </tr>
            <tr>
                <td class=widget_label_right>Country</td>
                <td class=widget_content_form_element><?php echo $country_menu ?></td>
            </tr>
            <tr>
                <td class=widget_label_right_91px>Override Address</td>
                <td class=widget_content_form_element><textarea rows=5 cols=60 name=address_body></textarea> <input type="checkbox" name="use_pretty_address"> Use</td>
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
                <td class=widget_header colspan=6>Addresses</td>
            </tr>
            <tr>
                <td class=widget_label>Name</td>
                <td class=widget_label>Body</td>
                <td class=widget_label>Primary Default</td>
                <td class=widget_label>Billing Default</td>
                <td class=widget_label>Shipping Default</td>
                <td class=widget_label>Payment Default</td>
            </tr>
            <?php  echo $addresses; ?>
            </tr>
            </tr>
                <td class=widget_content_form_element colspan=6><input class=button type=submit value="Save Defaults"></td>
            </tr>
        </table>
        </form>

        </td>
        <!-- gutter //-->
        <td class=gutter width=1%>
        &nbsp;
        </td>
        <!-- right column //-->
        <td class=rcol width=34% valign=top>

        </td>
    </tr>
</table>

<?php end_page();; ?>