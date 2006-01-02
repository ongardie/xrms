<?php
/**
 * /admin/roles/one.php
 *
 * Edit roles
 *
 * $Id: one.php,v 1.5 2006/01/02 22:03:16 vanmer Exp $
 */

require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();

$relationship_type_id = $_GET['relationship_type_id'];

$con = get_xrms_dbconnection();

$sql = "select * from relationship_types where relationship_type_id = $relationship_type_id";

$rst = $con->execute($sql);

if ($rst) {

    $relationship_name = $rst->fields['relationship_name'];
        $from_what_table = $rst->fields['from_what_table'];
        $to_what_table = $rst->fields['to_what_table'];
        $from_what_text = $rst->fields['from_what_text'];
        $to_what_text = $rst->fields['to_what_text'];
        $pre_formatting = $rst->fields['pre_formatting'];
        $post_formatting = $rst->fields['post_formatting'];

    $rst->close();
}

$con->close();

$page_title = _("Relationship Type Details").': '.$relationship_name;
start_page($page_title);

?>

<div id="Main">
    <div id="Content">

        <form action=edit-2.php method=post>
        <input type=hidden name=relationship_type_id value="<?php  echo $relationship_type_id; ?>">
        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=2><?php echo _("Edit Role Information"); ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Relationship Name"); ?></td>
                <td class=widget_content_form_element><input type=text size=20 name=relationship_name value="<?php  echo $relationship_name; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("From What Table"); ?></td>
                <td class=widget_content_form_element><input type=text size=20 name=from_what_table value="<?php  echo $from_what_table; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("To What Table"); ?></td>
                <td class=widget_content_form_element><input type=text size=20 name=to_what_table value="<?php  echo $to_what_table; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("From Text"); ?></td>
                <td class=widget_content_form_element><input type=text size=20 name=from_what_text value="<?php  echo $from_what_text; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("To Text"); ?></td>
                <td class=widget_content_form_element><input type=text size=20 name=to_what_text value="<?php  echo $to_what_text; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Pre Formatting"); ?></td>
                <td class=widget_content_form_element><input type=text size=20 name=pre_formatting value="<?php  echo htmlspecialchars($pre_formatting); ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Post Formatting"); ?></td>
                <td class=widget_content_form_element><input type=text size=20 name=post_formatting value="<?php  echo htmlspecialchars($post_formatting); ?>"></td>
            </tr>
            <tr>
                <td class=widget_content_form_element colspan=2><input class=button type=submit value="<?php echo _("Save Changes"); ?>""></td>
            </tr>
        </table>
        </form>

        <form action=delete.php method=post onsubmit="javascript: return confirm('<?php echo _("Delete Relationship Type?"); ?>');">
        <input type=hidden name=relationship_type_id value="<?php  echo $relationship_type_id; ?>">
        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=4><?php echo _("Delete Role"); ?></td>
            </tr>
            <tr>
                <td class=widget_content>
                <?php echo _("Click the button below to permanently remove this item."); ?>
                                <p><?php echo _("You should NOT delete the last element of a relationship type, or a used relationship type."); ?></p>
                <p><?php echo _("Note: This action CANNOT be undone!"); ?></p>
                <p><input class=button type=submit value="<?php echo _("Delete"); ?>"</p>
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
 * Revision 1.5  2006/01/02 22:03:16  vanmer
 * - changed to use centralized dbconnection function
 *
 * Revision 1.4  2004/09/29 14:38:38  braverock
 * - add phpdoc
 * - rationalize indentation
 * - fix return url after edit
 *
 * Revision 1.3  2004/07/25 18:52:36  johnfawcett
 * - standardized page title
 * - standardized delete text and button
 * - corrected parse error
 * - corrected bug: confirm delete not working
 *
 * Revision 1.2  2004/07/18 16:03:59  braverock
 * - localize strings for i18n translation support
 *   - applies modified patches from Sebastian Becker (hyperpac)
 *
 * Revision 1.1  2004/07/12 18:47:59  neildogg
 * - Added Relationship Type management
 *
 * Revision 1.3  2004/06/14 22:47:04  introspectshun
 * - Add adodb-params.php include for multi-db compatibility.
 * - Now use ADODB GetInsertSQL, GetUpdateSQL functions.
 *
 * Revision 1.2  2004/04/16 22:18:26  maulani
 * - Add CSS2 Positioning
 */
?>