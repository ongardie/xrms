<?php
/**
 * Edit a campaign
 *
 * $Id: one.php,v 1.13 2004/07/25 20:51:34 braverock Exp $
 */

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();
$msg = $_GET['msg'];

$campaign_id = $_GET['campaign_id'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
// $con->debug = 1;

update_recent_items($con, $session_user_id, "campaigns", $campaign_id);

$sql = "select cam.*, camt.campaign_type_display_html, cams.campaign_status_display_html, u1.username as entered_by_username, u2.username as last_modified_by_username, u3.username as campaign_owner_username
from campaigns cam, campaign_types camt, campaign_statuses cams, users u1, users u2, users u3
where cam.campaign_type_id = camt.campaign_type_id
and cam.campaign_status_id = cams.campaign_status_id
and cam.entered_by = u1.user_id
and cam.last_modified_by = u2.user_id
and cam.user_id = u3.user_id
and cam.campaign_id = $campaign_id";

$rst = $con->execute($sql);

if ($rst) {
    $campaign_title = $rst->fields['campaign_title'];
    $campaign_description = $rst->fields['campaign_description'];
    $campaign_type_display_html = $rst->fields['campaign_type_display_html'];
    $campaign_status_display_html = $rst->fields['campaign_status_display_html'];
    $cost = $rst->fields['cost'];
    $campaign_owner_username = $rst->fields['campaign_owner_username'];
    $entered_at = $con->userdate($rst->fields['entered_at']);
    $last_modified_at = $con->userdate($rst->fields['last_modified_at']);
    $entered_by = $rst->fields['entered_by_username'];
    $last_modified_by = $rst->fields['last_modified_by_username'];
    $rst->close();
}

$categories_sql = "select category_pretty_name
from categories c, category_scopes cs, category_category_scope_map ccsm, entity_category_map ecm
where ecm.on_what_table = 'campaigns'
and ecm.on_what_id = $campaign_id
and ecm.category_id = c.category_id
and cs.category_scope_id = ccsm.category_scope_id
and c.category_id = ccsm.category_id
and cs.on_what_table = 'campaigns'
and category_record_status = 'a'
order by category_pretty_name";

$rst = $con->execute($categories_sql);
$categories = array();

if ($rst) {
    while (!$rst->EOF) {
        array_push($categories, $rst->fields['category_pretty_name']);
        $rst->movenext();
    }
    $rst->close();
}

$categories = implode(', ', $categories);

$sql = "select note_id, note_description, entered_by, entered_at, username from notes, users
where notes.entered_by = users.user_id
and on_what_table = 'campaigns' and on_what_id = $campaign_id
and note_record_status = 'a' order by entered_at desc";

$rst = $con->execute($sql);

$note_rows = '';
if ($rst) {
    while (!$rst->EOF) {
        $note_rows .= "<tr>";
        $note_rows .= "<td class=widget_content><font class=note_label>" . $con->userdate($rst->fields['entered_at']) . " &bull; " . $rst->fields['username'] . " &bull; <a href='../notes/edit.php?note_id=" . $rst->fields['note_id'] . "&return_url=/campaigns/one.php?campaign_id=" . $campaign_id . "'>Edit</a></font><br>" . $rst->fields['note_description'] . "</td>";
        $note_rows .= "</tr>";
        $rst->movenext();
    }
    $rst->close();
}

$sql = "select * from files, users where files.entered_by = users.user_id and on_what_table = 'campaigns' and on_what_id = $campaign_id and file_record_status = 'a'";

$rst = $con->execute($sql);

$file_rows = '';
if ($rst) {
    while (!$rst->EOF) {
        $file_rows .= '<tr>';
        $file_rows .= "<td class=widget_content><a href='$http_site_root/files/one.php?return_url=/campaigns/one.php?campaign_id=$campaign_id&file_id=" . $rst->fields['file_id'] . "'>" . $rst->fields['file_pretty_name'] . '</a></td>';
        $file_rows .= '<td class=widget_content>' . pretty_filesize($rst->fields['file_size']) . '</td>';
        $file_rows .= '<td class=widget_content>' . $rst->fields['username'] . '</td>';
        $file_rows .= '<td class=widget_content>' . $con->userdate($rst->fields['entered_at']) . '</td>';
        $file_rows .= '</tr>';
        $rst->movenext();
    }
    $rst->close();
}

$con->close();

if (strlen($note_rows) == 0) {
    $note_rows = "<tr><td class=widget_content colspan=4>" . _("No notes") . "</td></tr>";
}

if (strlen($categories) == 0) {
    $categories = _("No categories");
}

if (strlen($file_rows) == 0) {
    $file_rows = "<tr><td class=widget_content colspan=4>" . _("No files") . "</td></tr>";
}

$page_title = _("Campaign Details") .': '. $campaign_title;
start_page($page_title, true, $msg);

?>

<div id="Main">
    <div id="Content">

        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header><?php echo _("Campaign Details"); ?></td>
            </tr>
            <tr>
                <td class=widget_content>

                    <table border=0 cellpadding=0 cellspacing=0 width=100%>
                        <tr>
                            <td width=50% class=clear align=left valign=top>
                                <table border=0 cellpadding=0 cellspacing=0 width=100%>
                                <tr>
                                    <td width=1% class=sublabel><?php echo _("Title"); ?></td>
                                    <td class=clear><?php echo $campaign_title; ?></td>
                                </tr>
                                <tr>
                                    <td class=sublabel><?php echo _("Type"); ?></td>
                                    <td class=clear><?php echo $campaign_type_display_html; ?></td>
                                </tr>
                                <tr>
                                    <td class=sublabel><?php echo _("Status"); ?></td>
                                    <td class=clear><?php echo $campaign_status_display_html; ?></td>
                                </tr>
                                <tr>
                                    <td class=sublabel><?php echo _("Cost"); ?></td>
                                    <td class=clear><?php echo number_format($cost, 2); ?></td>
                                </tr>
                                <tr>
                                    <td class=sublabel>&nbsp;</td>
                                    <td class=clear>&nbsp;</td>
                                </tr>
                                <tr>
                                    <td class=sublabel><?php echo _("Created"); ?></td>
                                    <td class=clear><?php echo $entered_at; ?> (<?php  echo $entered_by; ?>)</td>
                                </tr>
                                <tr>
                                    <td class=sublabel><?php echo _("Last Modified"); ?></td>
                                    <td class=clear><?php echo $last_modified_at; ?> (<?php  echo $last_modified_by; ?>)</td>
                                </tr>
                                </table>
                            </td>

                            <td width=50% class=clear align=left valign=top>

                                <table border=0 cellpadding=0 cellspacing=0 width=100%>
                                    </table>

                            </td>
                        </tr>
                    </table>

                    <p><?php  echo $campaign_description; ?>

                </td>
            </tr>
            <tr>
                <td class=widget_content_form_element><input class=button type=button value="<?php echo _("Edit"); ?>" onclick="javascript: location.href='edit.php?campaign_id=<?php  echo $campaign_id; ?>';"></td>
            </tr>
        </table>

    </div>

        <!-- right column //-->
    <div id="Sidebar">

        <!-- categories //-->
        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header><?php echo _("Categories"); ?></td>
            </tr>
            <tr>
                <td class=widget_content><?php echo $categories; ?></td>
            </tr>
            <tr>
                <td class=widget_content_form_element><input type=button class=button onclick="javascript: location.href='categories.php?campaign_id=<?php echo $campaign_id; ?>';" value="<?php echo _("Manage"); ?>"></td>
            </tr>
        </table>

        <!-- notes //-->
        <form action="../notes/new.php" method="post">
        <input type="hidden" name="on_what_table" value="campaigns">
        <input type="hidden" name="on_what_id" value="<?php echo $campaign_id; ?>">
        <input type="hidden" name="return_url" value="/campaigns/one.php?campaign_id=<?php echo $campaign_id; ?>">
        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header><?php echo _("Notes"); ?></td>
            </tr>
            <?php echo $note_rows; ?>
            <tr>
                <td class=widget_content_form_element colspan=4><input type=submit class=button value="<?php echo _("New"); ?>"></td>
            </tr>
        </table>
        </form>

        <!-- files //-->
        <form action="<?php  echo $http_site_root; ?>/files/new.php" method="post">
        <input type=hidden name=on_what_table value="campaigns">
        <input type=hidden name=on_what_id value="<?php echo $campaign_id; ?>">
        <input type=hidden name=return_url value="/campaigns/one.php?campaign_id=<?php echo $campaign_id; ?>">
        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=5><?php echo _("Files"); ?></td>
            </tr>
            <tr>
                <td class=widget_label><?php echo _("Name"); ?></td>
                <td class=widget_label><?php echo _("Size"); ?></td>
                <td class=widget_label><?php echo _("Owner"); ?></td>
                <td class=widget_label><?php echo _("Date"); ?></td>

            </tr>
            <?php  echo $file_rows; ?>
            <tr>
                <td class=widget_content_form_element colspan=5><input type=submit class=button value="<?php echo _("New"); ?>"></td>
            </tr>
        </table>
        </form>

    </div>
</div>

<?php

end_page();

/**
 * $Log: one.php,v $
 * Revision 1.13  2004/07/25 20:51:34  braverock
 * - added semicolons to some echo statements that were missing them
 *
 * Revision 1.12  2004/07/25 15:26:31  johnfawcett
 * - unified page title
 * - removed punctuation from gettext string
 *
 * Revision 1.11  2004/07/19 17:19:52  cpsource
 * - Resolved undefs
 *
 * Revision 1.10  2004/07/16 05:28:14  introspectshun
 * - Localized strings for i18n/translation support
 *
 * Revision 1.9  2004/06/12 03:27:32  introspectshun
 * - Now use ADODB GetInsertSQL, GetUpdateSQL, date and Concat functions.
 * - Corrected order of arguments to implode() function.
 *
 * Revision 1.8  2004/04/17 16:02:40  maulani
 * - Add CSS2 positioning
 *
 * Revision 1.7  2004/04/16 22:20:55  maulani
 * - Add CSS2 positioning
 *
 * Revision 1.6  2004/04/08 16:58:23  maulani
 * - Update javascript declaration
 * - Add phpdoc
 */
?>