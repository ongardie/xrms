<?php
/**
 * Edit the information for a single case
 *
 * $Id: one.php,v 1.10 2005/07/11 13:55:45 braverock Exp $
 */

require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check( 'Admin' );

$case_type_id = $_GET['case_type_id'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

$sql = "select * from case_types where case_type_id = $case_type_id";

//$con->debug=1;

$rst = $con->execute($sql);

if ($rst) {

    $case_type_short_name = $rst->fields['case_type_short_name'];
    $case_type_pretty_name = $rst->fields['case_type_pretty_name'];
    $case_type_pretty_plural = $rst->fields['case_type_pretty_plural'];
    $case_type_display_html = $rst->fields['case_type_display_html'];

    $rst->close();
}

$con->close();

$page_title = _("Case Type Details").': '._($case_type_pretty_name);
start_page($page_title);

?>

<div id="Main">
    <div id="Content">

        <form action=edit-2.php method=post>
        <input type=hidden name=case_type_id value="<?php  echo $case_type_id; ?>">
        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=4><?php echo _("Edit Case Type Information"); ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Short Name"); ?></td>
                <td class=widget_content_form_element><input type=text size=10 name=case_type_short_name value="<?php  echo $case_type_short_name; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Full Name"); ?></td>
                <td class=widget_content_form_element><input type=text size=20 name=case_type_pretty_name value="<?php  echo $case_type_pretty_name; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Full Plural"); ?></td>
                <td class=widget_content_form_element><input type=text size=20 name=case_type_pretty_plural value="<?php  echo $case_type_pretty_plural; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Display HTML"); ?></td>
                <td class=widget_content_form_element><input type=text size=30 name=case_type_display_html value="<?php  echo $case_type_display_html; ?>"></td>
            </tr>
            <tr>
                <td class=widget_content_form_element colspan=2><input class=button type=submit value="<?php echo _("Save Changes"); ?>"></td>
            </tr>
        </table>
        </form>
    </div>

        <!-- right column //-->
    <div id="Sidebar">

        <form action=delete.php method=post  onsubmit="javascript: return confirm('<?php echo _("Delete Case Type?"); ?>');">
            <input type=hidden name=case_type_id value="<?php  echo $case_type_id; ?>">
            <table class=widget cellspacing=1>
                <tr>
                    <td class=widget_header colspan=4><?php echo _("Delete Case Type"); ?></td>
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
</div>

<?php

end_page();

/**
 * $Log: one.php,v $
 * Revision 1.10  2005/07/11 13:55:45  braverock
 * - remove trainling whitespace
 *
 * Revision 1.9  2005/05/10 13:30:52  braverock
 * - localized string patches provided by Alan Baghumian (alanbach)
 *
 * Revision 1.8  2004/07/25 17:56:23  johnfawcett
 * - reinserted ? in gettex string - needed by some languages
 * - corrected bug: did not ask for confirm on delete
 * - standardized delete text and button
 *
 * Revision 1.7  2004/07/25 15:46:19  johnfawcett
 * - unified page title
 * - removed punctuation from gettext strings
 *
 * Revision 1.6  2004/07/16 23:51:35  cpsource
 * - require session_check ( 'Admin' )
 *
 * Revision 1.5  2004/07/16 13:51:56  braverock
 * - localize strings for i18n translation support
 *   - applies modified patches from Sebastian Becker (hyperpac)
 *
 * Revision 1.4  2004/06/14 21:48:25  introspectshun
 * - Add adodb-params.php include for multi-db compatibility.
 * - Now use ADODB GetInsertSQL, GetUpdateSQL functions.
 *
 * Revision 1.3  2004/04/16 22:18:24  maulani
 * - Add CSS2 Positioning
 *
 * Revision 1.2  2004/03/21 23:55:51  braverock
 * - fix SF bug 906413
 * - add phpdoc
 *
 */
?>