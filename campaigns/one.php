<?php

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

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

$sql_activities = "select activity_id, activity_title, scheduled_at, a.entered_at, activity_status,
at.activity_type_pretty_name, u.username, if(activity_status = 'o' and scheduled_at < now(), 1, 0) as is_overdue
from activity_types at, users u, activities a
where a.on_what_table = 'campaigns' and on_what_id = $campaign_id
and a.user_id = u.user_id
and a.activity_type_id = at.activity_type_id
and a.activity_record_status = 'a'
order by is_overdue desc, a.scheduled_at desc, a.entered_at desc";

$rst = $con->selectlimit($sql_activities, $display_how_many_activities_on_contact_page);

if ($rst) {
    while (!$rst->EOF) {

        $open_p = $rst->fields['activity_status'];
        $scheduled_at = $rst->unixtimestamp($rst->fields['scheduled_at']);
        $is_overdue = $rst->fields['is_overdue'];

        if ($open_p == 'o') {
            if ($is_overdue) {
                $classname = 'overdue_activity';
            } else {
                $classname = 'open_activity';
            }
        } else {
            $classname = 'closed_activity';
        }

        $contact_name = $rst->fields['contact_first_names'] . ' ' . $rst->fields['contact_last_name'];

        $activity_rows .= '<tr>';
        $activity_rows .= "<td class='$classname'><a href='$http_site_root/activities/one.php?return_url=/cases/one.php?case_id=$case_id&activity_id=" . $rst->fields['activity_id'] . "'>" . $rst->fields['activity_title'] . '</a></td>';
        $activity_rows .= '<td class=' . $classname . '>' . $rst->fields['username'] . '</td>';
        $activity_rows .= '<td class=' . $classname . '>' . $rst->fields['activity_type_pretty_name'] . '</td>';
        $activity_rows .= '<td class=' . $classname . '>' . $con->userdate($rst->fields['scheduled_at']) . '</td>';
        $activity_rows .= '</tr>';
        $rst->movenext();
    }
    $rst->close();
}

$categories_sql = "select category_pretty_name
from categories
where category_record_status = 'a'
and category_id in (select category_id from entity_category_map where on_what_table = 'campaigns' and on_what_id = $campaign_id)
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

$categories = implode($categories, ", ");

$sql = "select * from notes
where on_what_table = 'campaigns' and on_what_id = $campaign_id
and note_record_status = 'a' order by entered_at desc";

$rst = $con->execute($sql);

if ($rst) {
    while (!$rst->EOF) {
        $note_rows .= '<tr>';
        $note_rows .= '<td class=widget_content>' . $rst->fields['note_description'] . '</td>';
        $note_rows .= '</tr>';
        $rst->movenext();
    }
    $rst->close();
}

$sql = "select * from files, users where files.entered_by = users.user_id and on_what_table = 'campaigns' and on_what_id = $campaign_id and file_record_status = 'a'";

$rst = $con->execute($sql);

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

$sql = "select username, user_id from users where user_record_status = 'a' order by username";
$rst = $con->execute($sql);
$user_menu = $rst->getmenu2('user_id', $session_user_id, false);
$rst->close();

$sql = "select activity_type_pretty_name, activity_type_id from activity_types where activity_type_record_status = 'a'";
$rst = $con->execute($sql);
$activity_type_menu = $rst->getmenu2('activity_type_id', '', false);
$rst->close();

$con->close();

if (strlen($note_rows) == 0) {
    $note_rows = "<tr><td class=widget_content colspan=4>No notes</td></tr>";
}

if (strlen($categories) == 0) {
    $categories = "No categories";
}

if (strlen($file_rows) == 0) {
    $file_rows = "<tr><td class=widget_content colspan=4>No files</td></tr>";
}

$page_title = "One Campaign : $campaign_title";
start_page($page_title, true, $msg);

?>

<table border=0 cellpadding=0 cellspacing=0 width=100%>
    <tr>
        <td class=lcol width=70% valign=top>

        <table class=widget cellspacing=1 width=100%>
            <tr>
                <td class=widget_header>Campaign Details</td>
            </tr>
            <tr>
                <td class=widget_content>

                    <table border=0 cellpadding=0 cellspacing=0 width=100%>
                        <tr>
                            <td width=50% class=clear align=left valign=top>
                                <table border=0 cellpadding=0 cellspacing=0 width=100%>
                                <tr>
                                    <td width=1% class=sublabel>Title</td>
                                    <td class=clear><?php  echo $campaign_title; ?></td>
                                </tr>
                                <tr>
                                    <td class=sublabel>Type</td>
                                    <td class=clear><?php  echo $campaign_type_display_html; ?></td>
                                </tr>
                                <tr>
                                    <td class=sublabel>Status</td>
                                    <td class=clear><?php  echo $campaign_status_display_html; ?></td>
                                </tr>
                                <tr>
                                    <td class=sublabel>Cost</td>
                                    <td class=clear><?php  echo number_format($cost, 2); ?></td>
                                </tr>
                                <tr>
                                    <td class=sublabel>&nbsp;</td>
                                    <td class=clear>&nbsp;</td>
                                </tr>
                                <tr>
                                    <td class=sublabel>Created</td>
                                    <td class=clear><?php  echo $entered_at; ?> (<?php  echo $entered_by; ?>)</td>
                                </tr>
                                <tr>
                                    <td class=sublabel>Last Modified</td>
                                    <td class=clear><?php  echo $last_modified_at; ?> (<?php  echo $last_modified_by; ?>)</td>
                                </tr>
                                </table>
                            </td>

                            <td width=50% class=clear align=left valign=top>

                                <table border=0 cellpadding=0 cellspacing=0 width=100%>
                                <tr>
                                    <td width=1% class=sublabel>Contact</td>
                                    <td class=clear><a href="<?php  echo $http_site_root; ?>/contacts/one.php?contact_id=<?php  echo $contact_id; ?>"><?php  echo $first_names; ?> <?php  echo $last_name; ?></a></td>
                                </tr>
                                <tr>
                                    <td class=sublabel>Work Phone</td>
                                    <td class=clear><?php  echo $work_phone; ?></td>
                                </tr>
                            </table>

                            </td>
                        </tr>
                    </table>

                    <p><?php  echo $campaign_description; ?>

                </td>
            </tr>
            <tr>
                <td class=widget_content_form_element><input class=button type=button value="Edit" onclick="javascript: location.href='edit.php?campaign_id=<?php  echo $campaign_id; ?>';"></td>
            </tr>
        </table>

        <!-- activities //-->
        <form action="<?php  echo $http_site_root; ?>/activities/new-2.php" method=post>
        <input type=hidden name=return_url value="/campaigns/one.php?campaign_id=<?php  echo $campaign_id; ?>">
        <input type=hidden name=on_what_table value="campaigns">
        <input type=hidden name=on_what_id value="<?php  echo $campaign_id; ?>">
        <input type=hidden name=activity_status value="o">
        <table class=widget cellspacing=1 width=100%>
            <tr>
                <td class=widget_header colspan=5>Activities</td>
            </tr>
            <tr>
                <td class=widget_label>Title</td>
                <td class=widget_label>User</td>
                <td class=widget_label>Type</td>
                <td colspan=2 class=widget_label>On</td>
            </tr>
            <tr>
                <td class=widget_content_form_element><input type=text name=activity_title size=50></td>
                <td class=widget_content_form_element><?php  echo $user_menu; ?></td>
                <td class=widget_content_form_element><?php  echo $activity_type_menu; ?></td>
                <td colspan=2 class=widget_content_form_element><input type=text size=10 name=scheduled_at value="<?php  echo date('Y-m-d'); ?>"> <input class=button type=submit value="Add"> <input class=button type=button onclick="javascript: markComplete();" value="Done"></td>
            </tr>
            <?php  echo $activity_rows; ?>
        </table>
        </form>

        </td>
        <!-- gutter //-->
        <td class=gutter width=1%>
        &nbsp;
        </td>
        <!-- right column //-->
        <td class=rcol width=29% valign=top>

        <!-- categories //-->
        <table class=widget cellspacing=1 width=100%>
            <tr>
                <td class=widget_header>Categories</td>
            </tr>
            <tr>
                <td class=widget_content><?php  echo $categories; ?></td>
            </tr>
            <tr>
                <td class=widget_content_form_element><input type=button class=button onclick="javascript: location.href='categories.php?campaign_id=<?php  echo $campaign_id; ?>';" value="Manage"></td>
            </tr>
        </table>

        <!-- notes //-->
        <form action="<?php  echo $http_site_root; ?>/notes/new.php" method="post">
        <input type=hidden name=on_what_table value="campaigns">
        <input type=hidden name=on_what_id value="<?php  echo $campaign_id; ?>">
        <input type=hidden name=return_url value="/campaigns/one.php?campaign_id=<?php  echo $campaign_id; ?>">
        <table class=widget cellspacing=1 width=100%>
            <tr>
                <td class=widget_header>Notes</td>
            </tr>
            <?php  echo $note_rows; ?>
            <tr>
                <td class=widget_content_form_element><input type=submit class=button value="New"></td>
            </tr>
        </table>
        </form>

        <!-- files //-->
        <form action="<?php  echo $http_site_root; ?>/files/new.php" method="post">
        <input type=hidden name=on_what_table value="campaigns">
        <input type=hidden name=on_what_id value="<?php  echo $campaign_id; ?>">
        <input type=hidden name=return_url value="/campaigns/one.php?campaign_id=<?php  echo $campaign_id; ?>">
        <table class=widget cellspacing=1 width=100%>
            <tr>
                <td class=widget_header colspan=5>Files</td>
            </tr>
            <tr>
                <td class=widget_label>Name</td>
                <td class=widget_label>Size</td>
                <td class=widget_label>Owner</td>
                <td class=widget_label>Date</td>

            </tr>
            <?php  echo $file_rows; ?>
            <tr>
                <td class=widget_content_form_element colspan=5><input type=submit class=button value="New"></td>
            </tr>
        </table>
        </form>

        </td>
    </tr>
</table>

<?php end_page(); ?>
