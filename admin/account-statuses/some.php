<?php
/**
 * /admin/account-statuses/some.php
 *
 * List account-status
 *
 * $Id: some.php,v 1.9 2006/01/02 21:26:21 vanmer Exp $
 */

require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check( 'Admin' );

$con = get_xrms_dbconnection();

$sql = "select * from account_statuses where account_status_record_status = 'a' order by account_status_id";
$rst = $con->execute($sql);

if ($rst) {
    while (!$rst->EOF) {
        $table_rows .= '<tr>';
        $table_rows .= '<td class=widget_content><a href=one.php?account_status_id=' . $rst->fields['account_status_id'] . '>' . _($rst->fields['account_status_pretty_name']) . '</a></td>';
        $table_rows .= '</tr>';
        $rst->movenext();
    }
    $rst->close();
}

$con->close();

$page_title = _("Manage Account Statuses");
start_page($page_title);

?>

<div id="Main">
    <div id="Content">

        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=4><?php echo _("Account Statuses"); ?></td>
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
                <td class=widget_header colspan=2><?php echo _("Add New Account Status"); ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Short Name"); ?></td>
                <td class=widget_content_form_element><input type=text name=account_status_short_name size=10></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Full Name"); ?></td>
                <td class=widget_content_form_element><input type=text name=account_status_pretty_name size=20></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Full Plural Name"); ?></td>
                <td class=widget_content_form_element><input type=text name=account_status_pretty_plural size=20></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Display HTML"); ?></td>
                <td class=widget_content_form_element><input type=text name=account_status_display_html size=30></td>
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
 * Revision 1.9  2006/01/02 21:26:21  vanmer
 * - changed to use centralized xrms dbconnection function
 *
 * Revision 1.8  2004/11/26 17:18:28  braverock
 * - localized strings for i18n
 *
 * Revision 1.7  2004/07/16 23:51:33  cpsource
 * - require session_check ( 'Admin' )
 *
 * Revision 1.6  2004/07/15 20:17:53  introspectshun
 * - Localized button value string
 *
 * Revision 1.5  2004/07/15 20:00:00  introspectshun
 * - Localized strings for i18n/translation support
 *
 * Revision 1.4  2004/06/14 18:17:43  introspectshun
 * - Add adodb-params.php include for multi-db compatibility.
 * - Now use ADODB GetInsertSQL, GetUpdateSQL functions.
 *
 * Revision 1.3  2004/04/16 22:18:23  maulani
 * - Add CSS2 Positioning
 *
 * Revision 1.2  2004/03/24 18:12:45  maulani
 * - add phpdoc
 *
 *
 */
?>
