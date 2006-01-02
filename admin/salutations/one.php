<?php
/**
 * /admin/salutations/one.php
 *
 * Edit salutation
 *
 * $Id: one.php,v 1.2 2006/01/02 22:11:29 vanmer Exp $
 */

require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check( 'Admin' );

$salutation_id = $_GET['salutation_id'];

$con = get_xrms_dbconnection();

$sql = "select * from salutations where salutation_id = $salutation_id";

$rst = $con->execute($sql);

if ($rst) {

    $salutation = $rst->fields['salutation'];
    $salutation_sort_value = $rst->fields['salutation_sort_value'];

    $rst->close();
}

$con->close();

$page_title = _("Salutation Details").": $salutation";
start_page($page_title);

?>

<table border=0 cellpadding=0 cellspacing=0>
    <tr>
        <td class=lcol width=25% valign=top>

        <form action=edit-2.php method=post>
        <input type=hidden name=salutation_id value="<?php echo $salutation_id; ?>">
        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=4><?php echo _("Edit Salutation"); ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Salutation"); ?></td>
                <td class=widget_content_form_element>
                    <input type=text size=20 name=salutation value="<?php echo $salutation; ?>">
                </td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Salutation Sort Value"); ?></td>
                <td class=widget_content_form_element><input type=text size=20 name=salutation_sort_value value="<?php  echo $salutation_sort_value; ?>"></td>
            </tr>
            <tr>
                <td class=widget_content_form_element colspan=2><input class=button type=submit value="<?php echo _("Save Changes"); ?>"></td>
            </tr>
        </table>
        </form>

        <form action=delete.php method=post>
        <input type=hidden name=salutation_id value="<?php  echo $salutation_id; ?>" onsubmit="javascript: return confirm('<?php echo _("Delete Salutation?"); ?>');">
        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=4><?php echo _("Delete Salutation"); ?></td>
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
 * Revision 1.2  2006/01/02 22:11:29  vanmer
 * - changed to use centralized database connection function
 *
 * Revision 1.1  2005/04/10 17:33:36  maulani
 * - Add administrative tool to modify salutations popup list
 *
 *
 */
?>
