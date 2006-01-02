<?php
/**
 * Edit the details for one Case Priority
 *
 * $Id: one.php,v 1.9 2006/01/02 21:41:50 vanmer Exp $
 */

//include required files
require_once('../../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check( 'Admin' );

$case_priority_id = $_GET['case_priority_id'];

$con = get_xrms_dbconnection();

$sql = "select * from case_priorities where case_priority_id = $case_priority_id";

$rst = $con->execute($sql);

if ($rst) {

    $case_priority_short_name = $rst->fields['case_priority_short_name'];
    $case_priority_pretty_name = $rst->fields['case_priority_pretty_name'];
    $case_priority_pretty_plural = $rst->fields['case_priority_pretty_plural'];
    $case_priority_display_html = $rst->fields['case_priority_display_html'];
    $case_priority_score_adjustment = $rst->fields['case_priority_score_adjustment'];

    $rst->close();
}

$con->close();

$page_title = _("Case Priority Details").': '.$case_priority_pretty_name;
start_page($page_title);

?>

<div id="Main">
    <div id="Content">

        <form action="edit-2.php" method=post>
        <input type=hidden name=case_priority_id value="<?php  echo $case_priority_id; ?>">
        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=4><?php echo _("Edit Priority Type Information"); ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Short Name"); ?></td>
                <td class=widget_content_form_element><input type=text name=case_priority_short_name value="<?php  echo $case_priority_short_name; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Full Name"); ?></td>
                <td class=widget_content_form_element><input type=text name=case_priority_pretty_name value="<?php  echo $case_priority_pretty_name; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Full Plural"); ?></td>
                <td class=widget_content_form_element><input type=text name=case_priority_pretty_plural value="<?php  echo $case_priority_pretty_plural; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Display HTML"); ?></td>
                <td class=widget_content_form_element><input type=text name=case_priority_display_html value="<?php  echo $case_priority_display_html; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Score Adjustment"); ?></td>
                <td class=widget_content_form_element><input type=text size=5 name=case_priority_score_adjustment value="<?php  echo $case_priority_score_adjustment; ?>"></td>
            </tr>
            <tr>
                <td class=widget_content_form_element colspan=2><input class=button type=submit value="<?php echo _("Save Changes"); ?>"></td>
            </tr>
        </table>
        </form>

        <form action="delete.php" method=post onsubmit="javascript: return confirm('<?php echo _("Delete Priority Type?"); ?>');">
        <input type=hidden name=case_priority_id value="<?php  echo $case_priority_id; ?>">
        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=4><?php echo _("Delete Priority Type"); ?></td>
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
 * Revision 1.9  2006/01/02 21:41:50  vanmer
 * - changed to use centralized dbconnection function
 *
 * Revision 1.8  2004/07/25 17:40:21  johnfawcett
 * - reinserted ? in gettext string - needed for some languages
 * - standardized delete text and button
 *
 * Revision 1.7  2004/07/25 15:54:27  johnfawcett
 * - unified page title
 * - removed punctuation from gettext strings
 *
 * Revision 1.6  2004/07/16 23:51:35  cpsource
 * - require session_check ( 'Admin' )
 *
 * Revision 1.5  2004/07/16 13:51:54  braverock
 * - localize strings for i18n translation support
 *   - applies modified patches from Sebastian Becker (hyperpac)
 *
 * Revision 1.4  2004/06/14 21:17:06  introspectshun
 * - Add adodb-params.php include for multi-db compatibility.
 * - Now use ADODB GetInsertSQL, GetUpdateSQL functions.
 *
 * Revision 1.3  2004/04/16 22:18:24  maulani
 * - Add CSS2 Positioning
 *
 * Revision 1.2  2004/03/22 02:14:45  braverock
 * - debug SF bug 906413
 * - add phpdoc
 *
 */
?>
