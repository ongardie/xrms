<?php
/**
 * /admin/account-statuses/one.php
 *
 * Edit account-status
 *
 * $Id: one.php,v 1.9 2006/01/02 21:26:21 vanmer Exp $
 */

require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check( 'Admin' );

$account_status_id = $_GET['account_status_id'];

$con = get_xrms_dbconnection();

$sql = "select * from account_statuses where account_status_id = $account_status_id";

$rst = $con->execute($sql);

if ($rst) {

    $account_status_short_name = $rst->fields['account_status_short_name'];
    $account_status_pretty_name = $rst->fields['account_status_pretty_name'];
    $account_status_pretty_plural = $rst->fields['account_status_pretty_plural'];
    $account_status_display_html = $rst->fields['account_status_display_html'];

    $rst->close();
}

$con->close();

$page_title = _("Account Status Details").": $account_status_pretty_name";
start_page($page_title);

?>

<table border=0 cellpadding=0 cellspacing=0>
    <tr>
        <td class=lcol width=25% valign=top>

        <form action=edit-2.php method=post>
        <input type=hidden name=account_status_id value="<?php echo $account_status_id; ?>">
        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=4><?php echo _("Edit Account Status Information"); ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Short Name"); ?></td>
                <td class=widget_content_form_element>
                    <input type=text size=10 name=account_status_short_name value="<?php echo $account_status_short_name; ?>">
                </td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Full Name"); ?></td>
                <td class=widget_content_form_element><input type=text size=20 name=account_status_pretty_name value="<?php  echo $account_status_pretty_name; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Full Plural"); ?></td>
                <td class=widget_content_form_element><input type=text size=20 name=account_status_pretty_plural value="<?php  echo $account_status_pretty_plural; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Display HTML"); ?></td>
                <td class=widget_content_form_element><input type=text size=30 name=account_status_display_html value="<?php  echo $account_status_display_html; ?>"></td>
            </tr>
            <tr>
                <td class=widget_content_form_element colspan=2><input class=button type=submit value="<?php echo _("Save Changes"); ?>"></td>
            </tr>
        </table>
        </form>

        <form action=delete.php method=post>
        <input type=hidden name=account_status_id value="<?php  echo $account_status_id; ?>" onsubmit="javascript: return confirm('<?php echo _("Delete Account Status?"); ?>');">
        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=4><?php echo _("Delete Account Status"); ?></td>
            </tr>
            <tr>
                <td class=widget_content>
                <?php
                    echo _("Click the button below to permanently remove this item.")
                       . '<p>'
                       . _("Note: This action CANNOT be undone!")
					   . '</p>';
                ?>
                <p>
                <input class=button type=submit value="<?php echo _("Delete"); ?>">
                </td>
            </tr>
        </table>
        </form>

        </td>

        <!-- gutter //-->
        <td class=gutter width=2%>
        &nbsp;
        </td>

        <!-- right column //-->

        <td class=rcol width=73% valign=top>
        &nbsp;
        </td>

    </tr>
</table>

<?php

end_page();

/**
 * $Log: one.php,v $
 * Revision 1.9  2006/01/02 21:26:21  vanmer
 * - changed to use centralized xrms dbconnection function
 *
 * Revision 1.8  2004/07/25 17:19:59  johnfawcett
 * - Reinserted ? in gettext strings - needed for some languages
 * - Standardized Delete text and button
 *
 * Revision 1.7  2004/07/25 15:00:20  johnfawcett
 * - unified page title
 * - corrected gettext call
 * - removed punctuation from gettext string
 *
 * Revision 1.6  2004/07/16 23:51:33  cpsource
 * - require session_check ( 'Admin' )
 *
 * Revision 1.5  2004/07/14 16:28:09  braverock
 * - applied modified version of i18n conversion submitted by Sebastian Becker (hyperpac)
 *
 * Revision 1.4  2004/06/14 18:17:43  introspectshun
 * - Add adodb-params.php include for multi-db compatibility.
 * - Now use ADODB GetInsertSQL, GetUpdateSQL functions.
 *
 * Revision 1.3  2004/04/16 22:18:23  maulani
 * - Add CSS2 Positioning
 *
 * Revision 1.2  2004/03/24 18:12:44  maulani
 * - add phpdoc
 *
 */
?>
