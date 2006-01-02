<?php
/**
 * /admin/address-types/some.php
 *
 * List address-types
 *
 * $Id: some.php,v 1.2 2006/01/02 22:35:33 vanmer Exp $
 */

require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check( 'Admin' );

$con = get_xrms_dbconnection();

$sql = "select * from address_types order by address_type_sort_value";
$rst = $con->execute($sql);

if ($rst) {
    while (!$rst->EOF) {
        $table_rows .= '<tr>';
        $table_rows .= '<td class=widget_content><a href=one.php?address_type_id=' . $rst->fields['address_type_id'] . '>' . _($rst->fields['address_type']) . '</a></td>';
        $table_rows .= '</tr>';
        $rst->movenext();
    }
    $rst->close();
}

$con->close();

$page_title = _("Manage Address Types");
start_page($page_title);

?>

<div id="Main">
    <div id="Content">

        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=4><?php echo _("Address Types"); ?></td>
            </tr>
            <tr>
                <td class=widget_label><?php echo _("Name"); ?></td>
            </tr>
            <?php  echo $table_rows; ?>
        </table>

    </div>

        <!-- right column //-->
    <div id="Sidebar">

        <form action=add-2.php method=post>
        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=2><?php echo _("Add New Address Type"); ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Address Type"); ?></td>
                <td class=widget_content_form_element><input type=text name=address_type size=20></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Sort Value"); ?></td>
                <td class=widget_content_form_element><input type=text name=address_type_sort_value size=20></td>
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
 * $Log: some.php,v $
 * Revision 1.2  2006/01/02 22:35:33  vanmer
 * - changed to use centralized dbconnection function
 *
 * Revision 1.1  2005/04/11 00:43:25  maulani
 * - Add address type admin tool
 *
 */
?>
