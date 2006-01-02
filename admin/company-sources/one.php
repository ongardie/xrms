<?php
/**
 * /admin/company-sources/one.php
 *
 * Description
 *
 * $Id: one.php,v 1.11 2006/01/02 21:45:15 vanmer Exp $
 */

require_once('../../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$thispage = $_SERVER['REQUEST_URI'];
$session_user_id = session_check( 'Admin' );

$company_source_id = $_GET['company_source_id'];

$con = get_xrms_dbconnection();

$sql = "select * from company_sources where company_source_id = $company_source_id";

$rst = $con->execute($sql);

if ($rst) {

    $company_source_short_name = $rst->fields['company_source_short_name'];
    $company_source_pretty_name = $rst->fields['company_source_pretty_name'];
    $company_source_pretty_plural = $rst->fields['company_source_pretty_plural'];
    $company_source_display_html = $rst->fields['company_source_display_html'];
    $company_source_score_adjustment = $rst->fields['company_source_score_adjustment'];

    $rst->close();
}

$con->close();

$page_title = _("Company Source Details").': '._($company_source_pretty_name);
start_page($page_title);

?>

<div id="Main">
    <div id="Content">

        <form action="edit-2.php" method=post>
        <input type=hidden name=company_source_id value="<?php  echo $company_source_id; ?>">
        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=4><?php echo _("Edit Company Source Information"); ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Short Name"); ?></td>
                <td class=widget_content_form_element><input type=text name=company_source_short_name value="<?php  echo $company_source_short_name; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Full Name"); ?></td>
                <td class=widget_content_form_element><input type=text name=company_source_pretty_name value="<?php  echo $company_source_pretty_name; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Full Plural"); ?></td>
                <td class=widget_content_form_element><input type=text name=company_source_pretty_plural value="<?php  echo $company_source_pretty_plural; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Display HTML"); ?></td>
                <td class=widget_content_form_element><input type=text name=company_source_display_html value="<?php  echo $company_source_display_html; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Score Adjustment"); ?></td>
                <td class=widget_content_form_element><input type=text size=5 name=company_source_score_adjustment value="<?php  echo $company_source_score_adjustment; ?>"></td>
            </tr>
            <tr>
                <td class=widget_content_form_element colspan=2><input class=button type=submit value="<?php echo _("Save Changes"); ?>"></td>
            </tr>
        </table>
        </form>

        <form action="delete.php" method=post onsubmit="javascript: return confirm('<?php echo _("Delete Company Source?"); ?>');">
        <input type=hidden name=company_source_id value="<?php  echo $company_source_id; ?>">
        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=4><?php echo _("Delete Company Source"); ?></td>
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
 * Revision 1.11  2006/01/02 21:45:15  vanmer
 * - changed to use centralized database connection function
 *
 * Revision 1.10  2005/05/10 13:31:23  braverock
 * - localized string patches provided by Alan Baghumian (alanbach)
 *
 * Revision 1.9  2004/07/25 18:05:42  johnfawcett
 * - reinserted ? in gettext string - needed by some languages
 * - standardized delete text and button
 *
 * Revision 1.8  2004/07/25 15:51:17  johnfawcett
 * - unified page title
 * - removed punctuation from gettext strings
 *
 * Revision 1.7  2004/07/16 23:51:36  cpsource
 * - require session_check ( 'Admin' )
 *
 * Revision 1.6  2004/07/16 13:51:56  braverock
 * - localize strings for i18n translation support
 *   - applies modified patches from Sebastian Becker (hyperpac)
 *
 * Revision 1.5  2004/06/14 21:55:05  introspectshun
 * - Add adodb-params.php include for multi-db compatibility.
 * - Now use ADODB GetInsertSQL, GetUpdateSQL functions.
 *
 * Revision 1.4  2004/04/16 22:18:25  maulani
 * - Add CSS2 Positioning
 *
 *
 */
?>
