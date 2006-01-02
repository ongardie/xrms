<?php
/**
 * /admin/address-types/one.php
 *
 * Edit address-type
 *
 * $Id: one.php,v 1.2 2006/01/02 22:35:33 vanmer Exp $
 */

require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check( 'Admin' );

$address_type_id = $_GET['address_type_id'];

$con = get_xrms_dbconnection();

$sql = "select * from address_types where address_type_id = $address_type_id";

$rst = $con->execute($sql);

if ($rst) {

    $address_type = $rst->fields['address_type'];
    $address_type_sort_value = $rst->fields['address_type_sort_value'];

    $rst->close();
}

$con->close();

$page_title = _("Address Type Details").": $address_type";
start_page($page_title);

?>

<table border=0 cellpadding=0 cellspacing=0>
    <tr>
        <td class=lcol width=25% valign=top>

        <form action=edit-2.php method=post>
        <input type=hidden name=address_type_id value="<?php echo $address_type_id; ?>">
        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=4><?php echo _("Edit Address Type"); ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Address Type"); ?></td>
                <td class=widget_content_form_element>
                    <input type=text size=20 name=address_type value="<?php echo $address_type; ?>">
                </td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Address Type Sort Value"); ?></td>
                <td class=widget_content_form_element><input type=text size=20 name=address_type_sort_value value="<?php  echo $address_type_sort_value; ?>"></td>
            </tr>
            <tr>
                <td class=widget_content_form_element colspan=2><input class=button type=submit value="<?php echo _("Save Changes"); ?>"></td>
            </tr>
        </table>
        </form>

        <form action=delete.php method=post>
        <input type=hidden name=address_type_id value="<?php  echo $address_type_id; ?>" onsubmit="javascript: return confirm('<?php echo _("Delete address_type?"); ?>');">
        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=4><?php echo _("Delete Address Type"); ?></td>
            </tr>
            <tr>
                <td class=widget_content>
                <?php
                    echo _("Click the button below to permanently remove this item.")
                       . '<p>'
                       . _("Note: This action CANNOT be undone!")
					   . '</p>';
                ?>
                <p>
                <input class=button type=submit value="<?php echo _("Delete"); ?>">
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

<?php

end_page();

/**
 * $Log: one.php,v $
 * Revision 1.2  2006/01/02 22:35:33  vanmer
 * - changed to use centralized dbconnection function
 *
 * Revision 1.1  2005/04/11 00:43:25  maulani
 * - Add address type admin tool
 *
 */
?>
