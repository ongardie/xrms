<?php
/**
 * Manage categories
 *
 * $Id: one.php,v 1.15 2006/01/02 21:43:28 vanmer Exp $
 */

require_once('../../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check( 'Admin' );

$category_id = $_GET['category_id'];

$con = get_xrms_dbconnection();

$sql = "select * from categories where category_id = $category_id";

$rst = $con->execute($sql);

if ($rst) {

        $category_short_name = $rst->fields['category_short_name'];
        $category_pretty_name = $rst->fields['category_pretty_name'];
        $category_pretty_plural = $rst->fields['category_pretty_plural'];
        $category_display_html = $rst->fields['category_display_html'];

        $rst->close();
}

// associated with

$sql = "select cs.category_scope_id, category_scope_pretty_plural
from category_scopes cs, category_category_scope_map ccsm
where cs.category_scope_id = ccsm.category_scope_id
and ccsm.category_id = $category_id
and category_scope_record_status = 'a'
order by category_scope_pretty_name";

$rst = $con->execute($sql);
$array_of_category_scopes = array();
array_push($array_of_category_scopes, 0);

if ($rst) {
        while (!$rst->EOF) {
                $associated_with .= "<a href='remove-scope.php?category_id=$category_id&category_scope_id=" . $rst->fields['category_scope_id'] . "'>" . _($rst->fields['category_scope_pretty_plural']) . "</a><br>";
                array_push($array_of_category_scopes, $rst->fields['category_scope_id']);
                $rst->movenext();
        }
        $rst->close();
}

// not associated with

$sql = "select category_scope_id, category_scope_pretty_plural
from category_scopes cs
where category_scope_id not in (" . implode(',', $array_of_category_scopes) . ")
and category_scope_record_status = 'a'
order by category_scope_pretty_name";

$rst = $con->execute($sql);

if ($rst) {
        while (!$rst->EOF) {
                $not_associated_with .= "<a href='add-scope.php?category_id=$category_id&category_scope_id=" . $rst->fields['category_scope_id'] . "'>" . _($rst->fields['category_scope_pretty_plural']) . "</a><br>";
                $rst->movenext();
        }
        $rst->close();
}

$con->close();

$page_title = _("Category Details").': '.$category_pretty_name;
start_page($page_title);

?>

<div id="Main">
    <div id="Content">

                <form action=edit-2.php method=post>
                <input type=hidden name=category_id value="<?php  echo $category_id; ?>">
                <table class=widget cellspacing=1>
                        <tr>
                                <td class=widget_header colspan=4><?php echo _("Edit Category Information"); ?></td>
                        </tr>
                        <tr>
                                <td class=widget_label_right><?php echo _("Short Name"); ?></td>
                                <td class=widget_content><input type=text name=category_short_name value="<?php  echo $category_short_name; ?>"></td>
                        </tr>
                        <tr>
                                <td class=widget_label_right><?php echo _("Full Name"); ?></td>
                                <td class=widget_content><input type=text name=category_pretty_name value="<?php  echo $category_pretty_name; ?>"></td>
                        </tr>
                        <tr>
                                <td class=widget_label_right><?php echo _("Full Plural"); ?></td>
                                <td class=widget_content><input type=text name=category_pretty_plural value="<?php  echo $category_pretty_plural; ?>"></td>
                        </tr>
                        <tr>
                                <td class=widget_label_right><?php echo _("Display HTML"); ?></td>
                                <td class=widget_content><input type=text name=category_display_html value="<?php  echo $category_display_html; ?>"></td>
                        </tr>
                        <tr>
                                <td class=widget_content colspan=2><input class=button type=submit value="<?php echo _("Save Changes"); ?>"></td>
                        </tr>
                </table>
                </form>

                <form action=delete.php method=post onsubmit="javascript: return confirm('<?php echo _("Delete Category?"); ?>');">
                <input type=hidden name=category_id value="<?php  echo $category_id; ?>">
                <table class=widget cellspacing=1>
                        <tr>
                                <td class=widget_header colspan=4><?php echo _("Delete Category"); ?></td>
                        </tr>
                        <tr>
                                <td class=widget_content>
                                <?php echo _("Click the button below to permanently remove this item."); ?>
                                <p><?php echo _("Note: This action CANNOT be undone!"); ?></p>
                                <p><input class=button type=submit value="<?php echo _("Delete"); ?>"></p>
                                </td>
                        </tr>
                </table>
                </form>

    </div>

        <!-- right column //-->
    <div id="Sidebar">

                <!-- category scopes //-->
                <table class=widget cellspacing=1>
                        <tr>
                                <td class=widget_header colspan=2><?php echo _("Category Scopes"); ?></td>
                        </tr>
                        <tr>
                                <td class=widget_label><?php echo _("Associated With"); ?></td>
                                <td class=widget_label><?php echo _("Not Associated With"); ?></td>
                        </tr>
                        <tr>
                                <td class=widget_content><?php  echo $associated_with; ?></td>
                                <td class=widget_content><?php  echo $not_associated_with; ?></td>
                        </tr>
                </table>


    </div>
</div>

<?php

end_page();

/**
 * $Log: one.php,v $
 * Revision 1.15  2006/01/02 21:43:28  vanmer
 * - changed to use centralized database connection function
 *
 * Revision 1.14  2005/05/10 13:31:22  braverock
 * - localized string patches provided by Alan Baghumian (alanbach)
 *
 * Revision 1.13  2004/07/25 18:02:28  johnfawcett
 * - reinserted ? into gettext string - needed for some languages
 * - standardized delete text
 * - added javascript call for confirm delete
 *
 * Revision 1.12  2004/07/25 15:48:36  johnfawcett
 * - unified page title
 *
 * Revision 1.11  2004/07/16 23:51:35  cpsource
 * - require session_check ( 'Admin' )
 *
 * Revision 1.10  2004/07/16 13:51:56  braverock
 * - localize strings for i18n translation support
 *   - applies modified patches from Sebastian Becker (hyperpac)
 *
 * Revision 1.9  2004/06/16 20:55:58  gpowers
 * - removed $this from session_check()
 *   - it is incompatible with PHP5
 *
 * Revision 1.8  2004/06/14 21:52:23  introspectshun
 * - Add adodb-params.php include for multi-db compatibility.
 * - Now use ADODB GetInsertSQL, GetUpdateSQL functions.
 *
 * Revision 1.7  2004/06/03 16:28:23  braverock
 * - remove newline at end of file
 *
 * Revision 1.6  2004/05/17 16:12:30  braverock
 * - applied patch to move $_GET after session_check
 *   - suggested to fix SF bug 955310 by Sergio Dominici (sirjo)
 *
 * Revision 1.5  2004/04/23 15:30:11  gpowers
 * added session_check
 *
 * Revision 1.4  2004/04/16 22:18:25  maulani
 * - Add CSS2 Positioning
 *
 * Revision 1.3  2004/04/08 16:56:47  maulani
 * - Update javascript declaration
 * - Add phpdoc
 *
 */
?>
