<?php
/**
 * /admin/salutations/some.php
 *
 * List salutations
 *
 * $Id: some.php,v 1.2 2006/01/02 22:11:29 vanmer Exp $
 */

require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check( 'Admin' );

$con = get_xrms_dbconnection();

$sql = "select * from salutations order by salutation_sort_value";
$rst = $con->execute($sql);

if ($rst) {
    while (!$rst->EOF) {
        $table_rows .= '<tr>';
        $table_rows .= '<td class=widget_content><a href=one.php?salutation_id=' . $rst->fields['salutation_id'] . '>' . _($rst->fields['salutation']) . '</a></td>';
        $table_rows .= '</tr>';
        $rst->movenext();
    }
    $rst->close();
}

$con->close();

$page_title = _("Manage Salutations");
start_page($page_title);

?>

<div id="Main">
    <div id="Content">

        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=4><?php echo _("Salutations"); ?></td>
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
                <td class=widget_header colspan=2><?php echo _("Add New Salutation"); ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Salutation"); ?></td>
                <td class=widget_content_form_element><input type=text name=salutation size=20></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Sort Value"); ?></td>
                <td class=widget_content_form_element><input type=text name=salutation_sort_value size=20></td>
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
 * Revision 1.2  2006/01/02 22:11:29  vanmer
 * - changed to use centralized database connection function
 *
 * Revision 1.1  2005/04/10 17:33:36  maulani
 * - Add administrative tool to modify salutations popup list
 *
 *
 */
?>
