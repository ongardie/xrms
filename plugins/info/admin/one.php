<?php
/**
 * Edit info item types
 *
 * $Id: one.php,v 1.4 2005/04/01 20:15:03 ycreddy Exp $
 */

require_once('../../../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check( 'Admin' );

$info_type_id = $_GET['info_type_id'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

$sql = "select * from info_types where info_type_id = $info_type_id";

$rst = $con->execute($sql);

if ($rst) {
    
    $info_type_name = $rst->fields['info_type_name'];
    
    $rst->close();
}

       $sql2 = "select * from info_display_map
                 where info_type_id = " . $rst->fields['info_type_id'];
        $rst2 = $con->SelectLimit($sql2, 1);
        if ($rst2) {
            while (!$rst2->EOF) {
                $display_on .= $rst2->fields['display_on'];
                $rst2->movenext();
            }
        }

$con->close();

$page_title = _("Infoy Type Details") . ': ' . $info_type_name;
start_page($page_title);

?>

<div id="Main">
    <div id="Content">

        <form action="edit-2.php" method=post>
        <input type=hidden name=info_type_id value="<?php  echo $info_type_id; ?>">
        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=4><?php echo _("Edit Info Type Information"); ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Name"); ?></td>
                <td class=widget_content_form_element><input type=text name=info_type_name value="<?php  echo $info_type_name; ?>"></td>
            <tr>
                <td class=widget_label_right>
                    <?php echo _("Display On"); ?>
                </td>
                <td>
                    <?php echo display_on_menu(); ?>
                </td>
            </tr>
            </tr>
            <tr>
                <td class=widget_content_form_element colspan=2><input class=button type=submit value="<?php echo _("Save Changes"); ?>"></td>
            </tr>
        </table>
        </form>

        <form action="delete.php" method=post onsubmit="javascript: return confirm('<?php echo _("Delete Info Type?"); ?>');">
        <input type=hidden name=info_type_id value="<?php  echo $info_type_id; ?>">
        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=4><?php echo _("Delete Info Type"); ?></td>
            </tr>
            <tr>
                <td class=widget_content>
	          <?php echo _("Click the button below to permanently remove this item."); ?>
                <p>
		    <?php echo _("Note: This action CANNOT be undone!"); ?>
                </p>
                <p><input class=button type=submit value="<?php echo _("Delete"); ?>">
                </p>
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
 * $Log: one.php,v $
 * Revision 1.4  2005/04/01 20:15:03  ycreddy
 * Replaced LIMIT with the portable SelectLimit
 *
 * Revision 1.3  2005/02/11 00:54:55  braverock
 * - add phpdoc where neccessary
 * - fix code formatting and comments
 *
 * Revision 1.2  2004/11/12 06:36:37  gpowers
 * - added support for single display_on add/edit/delete/show
 *
 * Revision 1.1  2004/11/10 07:27:49  gpowers
 * - added admin screens for info types
 *
 */
?>
