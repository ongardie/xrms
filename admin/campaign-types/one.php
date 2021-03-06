<?php
/**
 * Manage campaign types
 *
 * $Id: one.php,v 1.13 2010/12/06 21:56:13 gopherit Exp $
 */

require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check( 'Admin' );

$campaign_type_id = $_GET['campaign_type_id'];

$con = get_xrms_dbconnection();

$sql = "select * from campaign_types where campaign_type_id = $campaign_type_id";

//$con->debug=1;

$rst = $con->execute($sql);

if ($rst) {

    $campaign_type_short_name = $rst->fields['campaign_type_short_name'];
    $campaign_type_pretty_name = $rst->fields['campaign_type_pretty_name'];
    $campaign_type_pretty_plural = $rst->fields['campaign_type_pretty_plural'];
    $campaign_type_display_html = $rst->fields['campaign_type_display_html'];

	$rst->close();
}

$con->close();

$page_title = _("Campaign Type Details").': '.$campaign_type_pretty_name;
start_page($page_title);

?>

<div id="Main">
    <div id="Content">

        <form action=edit-2.php method=post>
        <input type=hidden name=campaign_type_id value="<?php  echo $campaign_type_id; ?>">
        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=4><?php echo _("Edit Campaign Type Information"); ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Short Name"); ?></td>
                <td class=widget_content_form_element><input type=text size=10 name=campaign_type_short_name value="<?php  echo $campaign_type_short_name; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Full Name"); ?></td>
                <td class=widget_content_form_element><input type=text size=20 name=campaign_type_pretty_name value="<?php  echo $campaign_type_pretty_name; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Full Plural"); ?></td>
                <td class=widget_content_form_element><input type=text size=20 name=campaign_type_pretty_plural value="<?php  echo $campaign_type_pretty_plural; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Display HTML"); ?></td>
                <td class=widget_content_form_element><input type=text size=30 name=campaign_type_display_html value="<?php  echo htmlspecialchars($campaign_type_display_html); ?>"></td>
            </tr>
            <tr>
                <td class=widget_content_form_element colspan=2><input class=button type=submit value="<?php echo _("Save Changes"); ?>"></td>
            </tr>
        </table>
        </form>
    </div>

        <!-- right column //-->
    <div id="Sidebar">

        <form action=delete.php method=post onsubmit="javascript: return confirm('<?php echo addslashes(_("Delete Campaign Type?")); ?>');">
            <input type=hidden name=campaign_type_id value="<?php  echo $campaign_type_id; ?>">
            <table class=widget cellspacing=1>
                <tr>
                    <td class=widget_header colspan=4><?php echo _("Delete Campaign Type"); ?></td>
                </tr>
                <tr>
                    <td class=widget_content>
                        <p style="color: red;"><?php echo _("Notice: Deleting this Campaign Type will also delete ALL Campaign Statuses attached to it and ALL Activity Templates attached to those Statuses."); ?></p>
                        <p style="font-weight: bold; color: red;"><?php echo _("WARNING: This action CANNOT be undone!"); ?></p>
                        <p><input class=button type=submit value="<?php echo _("Delete"); ?>"></p>
                    </td>
                </tr>
            </table>
        </form>

    </div>
</div>

<?php

end_page();

/**
 * $Log: one.php,v $
 * Revision 1.13  2010/12/06 21:56:13  gopherit
 * Deleting a workflow type now results in not only deleting all its statuses but also deleting all the activity templates attached to those statuses.
 *
 * Revision 1.12  2010/11/29 14:45:14  gopherit
 * Moved the 'Delete Campaign Type' box into the sidebar for consistency across the UI.
 *
 * Revision 1.11  2007/09/17 21:31:53  myelocyte
 * - Fixed bugs: 984168, 984170 and similar bugs not reported
 *    I have added htmlspecialchars function in one.php before all display_html variable is
 *    displayed. This same error affected most of the display_html fields in Admin section.
 *    I tried to fix them all.
 *
 * Revision 1.10  2006/12/05 11:09:59  jnhayart
 * Add cosmetics display, and control localisation
 *
 * Revision 1.9  2006/01/02 21:37:29  vanmer
 * - changed to use centralized dbconnection function
 *
 * Revision 1.8  2004/07/25 17:37:27  johnfawcett
 * - reinserted ? in gettext string - needed by some languages
 * - standardized delete text and button
 *
 * Revision 1.7  2004/07/25 15:36:37  johnfawcett
 * - unified page title
 * - removed punctuation from gettext strings
 *
 * Revision 1.6  2004/07/16 23:51:34  cpsource
 * - require session_check ( 'Admin' )
 *
 * Revision 1.5  2004/07/16 13:51:54  braverock
 * - localize strings for i18n translation support
 *   - applies modified patches from Sebastian Becker (hyperpac)
 *
 * Revision 1.4  2004/06/14 21:13:22  introspectshun
 * - Add adodb-params.php include for multi-db compatibility.
 * - Now use ADODB GetInsertSQL, GetUpdateSQL functions.
 *
 * Revision 1.3  2004/04/16 19:07:47  maulani
 * - Use CSS2 positioning
 * - Fix HTML so it will validate
 * - Fix delete confirmation bug
 *
 * Revision 1.2  2004/04/08 16:56:47  maulani
 * - Update javascript declaration
 * - Add phpdoc
 *
 */
?>