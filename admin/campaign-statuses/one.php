<?php
/**
 * /admin/campaign-statuses/one.php
 *
 * Edit campaign-statuses
 *
 * $Id: one.php,v 1.2 2004/04/16 22:18:23 maulani Exp $
 */

require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

$session_user_id = session_check();

$campaign_status_id = $_GET['campaign_status_id'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

$sql = "select * from campaign_statuses where campaign_status_id = $campaign_status_id";

$rst = $con->execute($sql);

if ($rst) {

    $campaign_status_short_name = $rst->fields['campaign_status_short_name'];
    $campaign_status_pretty_name = $rst->fields['campaign_status_pretty_name'];
    $campaign_status_pretty_plural = $rst->fields['campaign_status_pretty_plural'];
    $campaign_status_display_html = $rst->fields['campaign_status_display_html'];

    $rst->close();
}

$con->close();

$page_title = "One Campaign Status : $campaign_status_pretty_name";
start_page($page_title);

?>

<div id="Main">
    <div id="Content">

        <form action=edit-2.php method=post>
        <input type=hidden name=campaign_status_id value="<?php  echo $account_status_id; ?>">
        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=4>Edit Campaign Status Information</td>
            </tr>
            <tr>
                <td class=widget_label_right>Short Name</td>
                <td class=widget_content_form_element><input type=text size=10 name=campaign_status_short_name value="<?php  echo $campaign_status_short_name; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right>Full Name</td>
                <td class=widget_content_form_element><input type=text size=20 name=campaign_status_pretty_name value="<?php  echo $campaign_status_pretty_name; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right>Full Plural</td>
                <td class=widget_content_form_element><input type=text size=20 name=campaign_status_pretty_plural value="<?php  echo $campaign_status_pretty_plural; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right>Display HTML</td>
                <td class=widget_content_form_element><input type=text size=30 name=campaign_status_display_html value="<?php  echo $campaign_status_display_html; ?>"></td>
            </tr>
            <tr>
                <td class=widget_content_form_element colspan=2><input class=button type=submit value="Save Changes"></td>
            </tr>
        </table>
        </form>

        <form action=delete.php method=post>
        <input type=hidden name=campaign_status_id value="<?php  echo $campaign_status_id; ?>" onsubmit="javascript: return confirm('Delete Opportunity Status?');">
        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=4>Delete Opportunity Status</td>
            </tr>
            <tr>
                <td class=widget_content>
                Click the button below to remove this account status from the system.
                <p>Note: This action CANNOT be undone!
                <p><input class=button type=submit value="Delete Opportunity Status">
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
 * Revision 1.2  2004/04/16 22:18:23  maulani
 * - Add CSS2 Positioning
 *
 *
 */
?>
