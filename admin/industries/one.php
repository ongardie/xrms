<?php
/**
 * Manage industries
 *
 * $Id: one.php,v 1.11 2007/09/17 21:31:54 myelocyte Exp $
 */

require_once('../../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check( 'Admin' );

$industry_id = $_GET['industry_id'];

$con = get_xrms_dbconnection();

$sql = "select * from industries where industry_id = $industry_id";

$rst = $con->execute($sql);

if ($rst) {

    $industry_short_name = $rst->fields['industry_short_name'];
    $industry_pretty_name = $rst->fields['industry_pretty_name'];
    $industry_pretty_plural = $rst->fields['industry_pretty_plural'];
    $industry_display_html = $rst->fields['industry_display_html'];

    $rst->close();
}

$page_title = _("Industry Details").': '.$industry_pretty_name;
start_page($page_title);

?>

<div id="Main">
    <div id="Content">

        <form action=edit-2.php method=post>
        <input type=hidden name=industry_id value="<?php  echo $industry_id; ?>">
        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=4><?php echo _("Edit Industry Information"); ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Short Name"); ?></td>
                <td class=widget_content_form_element><input type=text name=industry_short_name value="<?php  echo $industry_short_name; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Full Name"); ?></td>
                <td class=widget_content_form_element><input type=text name=industry_pretty_name value="<?php  echo $industry_pretty_name; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Full Plural"); ?></td>
                <td class=widget_content_form_element><input type=text name=industry_pretty_plural value="<?php  echo $industry_pretty_plural; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Display HTML"); ?></td>
                <td class=widget_content_form_element><input type=text name=industry_display_html value="<?php  echo htmlspecialchars($industry_display_html); ?>"></td>
            </tr>
            <tr>
                <td class=widget_content_form_element colspan=2><input class=button type=submit value="<?php echo _("Save Changes"); ?>"></td>
            </tr>
        </table>
        </form>

        <form action=delete.php method=post onsubmit="javascript: return confirm('<?php echo addslashes(_("Delete Industry?")); ?>');">
        <input type=hidden name=industry_id value="<?php  echo $industry_id; ?>">
        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=4><?php echo _("Delete Industry"); ?></td>
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

        &nbsp;

    </div>
</div>

<?php

end_page();

/**
 * $Log: one.php,v $
 * Revision 1.11  2007/09/17 21:31:54  myelocyte
 * - Fixed bugs: 984168, 984170 and similar bugs not reported
 *    I have added htmlspecialchars function in one.php before all display_html variable is
 *    displayed. This same error affected most of the display_html fields in Admin section.
 *    I tried to fix them all.
 *
 * Revision 1.10  2006/12/05 11:10:00  jnhayart
 * Add cosmetics display, and control localisation
 *
 * Revision 1.9  2006/01/02 21:55:10  vanmer
 * - changed to use centralized database connection function
 *
 * Revision 1.8  2004/07/25 18:36:38  johnfawcett
 * - corrected string erroneously pasted
 *
 * Revision 1.7  2004/07/25 18:20:59  johnfawcett
 * - standardized page title
 * - standardized delete text and button
 * - added delete confirm (call to javascript)
 *
 * Revision 1.6  2004/07/16 23:51:37  cpsource
 * - require session_check ( 'Admin' )
 *
 * Revision 1.5  2004/07/16 13:51:58  braverock
 * - localize strings for i18n translation support
 *   - applies modified patches from Sebastian Becker (hyperpac)
 *
 * Revision 1.4  2004/06/14 22:25:28  introspectshun
 * - Add adodb-params.php include for multi-db compatibility.
 * - Now use ADODB GetInsertSQL, GetUpdateSQL functions.
 *
 * Revision 1.3  2004/04/16 22:18:26  maulani
 * - Add CSS2 Positioning
 *
 * Revision 1.2  2004/04/08 16:56:48  maulani
 * - Update javascript declaration
 * - Add phpdoc
 *
 *
 */
?>

