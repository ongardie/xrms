<?php
/**
 * Edit address for a company
 *
 * $Id: edit-address.php,v 1.6 2004/06/12 05:03:16 introspectshun Exp $
 */

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();
$msg = $_GET['msg'];
$company_id = $_GET['company_id'];
$address_id = $_GET['address_id'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

$sql = "select a.*, c.company_name from companies c, addresses a where c.company_id = a.company_id and a.address_id = $address_id";

$rst = $con->execute($sql);

if ($rst) {
    $country_id = $rst->fields['country_id'];
    $company_name = $rst->fields['company_name'];
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
$country_menu = $rst->getmenu2('country_id', $country_id, false);
$rst->close();

$con->close();

$page_title = $company_name . " - Edit Address";
start_page($page_title, true, $msg);

?>

<div id="Main">
    <div id="Content">

        <form action=edit-address-2.php method=post>
        <input type=hidden name=company_id value=<?php echo $company_id; ?>>
        <input type=hidden name=address_id value=<?php echo $address_id; ?>>
        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=2>Edit Address</td>
            </tr>
            <tr>
                <td class=widget_label_right>Company</td>
                <td class=widget_content><a href="../companies/one.php?company_id=<?php echo $company_id; ?>"><?php  echo $company_name; ?></a></td>
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
                <td class=widget_content_form_element colspan=2><input class=button type=submit value="Save Changes"> <input class=button type=button value="Delete Address" onclick="javascript: location.href='delete-address.php?company_id=<?php echo $company_id ?>&address_id=<?php echo $address_id ?>';"></td>
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
 * Revision 1.6  2004/06/12 05:03:16  introspectshun
 * - Now use ADODB GetInsertSQL, GetUpdateSQL, date and Concat functions.
 * - Corrected order of arguments to implode() function.
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
