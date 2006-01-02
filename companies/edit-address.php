<?php
/**
 * Edit address for a company
 *
 * $Id: edit-address.php,v 1.12 2006/01/02 22:56:27 vanmer Exp $
 */

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');
require_once($include_directory . 'confgoto.php');

$session_user_id = session_check();
$msg = isset($_GET['msg']) ? $_GET['msg'] : '';
$company_id = $_GET['company_id'];
$address_id = $_GET['address_id'];

$con = get_xrms_dbconnection();

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
    $address_type = $rst->fields['address_type'];
    $address_body = $rst->fields['address_body'];
    $use_pretty_address = $rst->fields['use_pretty_address'];
    $rst->close();
}

$address_type_menu = build_address_type_menu($con, $address_type);

$sql = "select country_name, country_id from countries where country_record_status = 'a' order by country_name";
$rst = $con->execute($sql);
$country_menu = $rst->getmenu2('country_id', $country_id, false);
$rst->close();

$con->close();

$page_title = $company_name . " - " . _("Edit Address");
start_page($page_title, true, $msg);

// include confGoTo javascrip module
confGoTo_includes();

?>

<div id="Main">
    <div id="Content">

        <form action=edit-address-2.php method=post>
        <input type=hidden name=company_id value=<?php echo $company_id; ?>>
        <input type=hidden name=address_id value=<?php echo $address_id; ?>>
        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=2><?php echo _("Edit Address"); ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Company"); ?></td>
                <td class=widget_content><a href="../companies/one.php?company_id=<?php echo $company_id; ?>"><?php  echo $company_name; ?></a></td>
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
                <td class=widget_content_form_element><textarea rows=5 cols=60 name=address_body><?php echo $address_body; ?></textarea> 
                  <input type="checkbox" name="use_pretty_address"<?php if ($use_pretty_address == 't') {echo " checked";} ?>> <?php echo _("Use"); ?></td>
            </tr>
            <tr>
                <td class=widget_content_form_element colspan=2>
                  <input class=button type=submit value="<?php echo _("Save Changes"); ?>"> 
<?php
        		  $quest = _("Delete Address?");
        		  $button = _("Delete Address");
				  $to_url = "delete-address.php?company_id=$company_id&address_id=$address_id";
				  acl_confGoTo( $quest, $button, $to_url, 'companies', $company_id, "Delete" );
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
 * Revision 1.12  2006/01/02 22:56:27  vanmer
 * - changed to use centralized dbconnection function
 *
 * Revision 1.11  2005/06/01 16:08:10  vanmer
 * - added ACL control over whether delete button appears on address
 *
 * Revision 1.10  2005/04/11 02:06:49  maulani
 * - Add address type.  RFE 862049 (maulani)
 *
 * Revision 1.9  2004/08/02 22:28:11  maulani
 * - Have delete address button use confGoTo confirmation dialog to confirm delete
 *
 * Revision 1.8  2004/07/30 11:23:38  cpsource
 * - Do standard msg processing
 *   Default use_pretty_address in new-2.php set to null
 *
 * Revision 1.7  2004/07/21 19:17:56  introspectshun
 * - Localized strings for i18n/l10n support
 *
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
