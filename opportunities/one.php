<?php
/**
 * View a single Sales Opportunity
 *
 * $Id: one.php,v 1.7 2004/02/06 22:47:37 maulani Exp $
 */

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

$session_user_id = session_check();
$msg = $_GET['msg'];

$opportunity_id = $_GET['opportunity_id'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
// $con->debug = 1;

update_recent_items($con, $session_user_id, "opportunities", $opportunity_id);

$sql = "select o.*, c.company_id, c.company_name, c.company_code, cont.first_names, cont.last_name, cont.work_phone, cont.email,
u1.username as entered_by_username, u2.username as last_modified_by_username,
u3.username as opportunity_owner_username, u4.username as account_owner_username,
as1.account_status_display_html, r.rating_display_html, crm_status_display_html, os.opportunity_status_display_html, cam.campaign_title
from companies c, contacts cont, users u1, users u2, users u3, users u4, account_statuses as1, ratings r, crm_statuses crm, opportunity_statuses os, opportunities o left join campaigns cam on o.campaign_id = cam.campaign_id
where o.company_id = c.company_id
and o.contact_id = cont.contact_id
and o.entered_by = u1.user_id
and o.last_modified_by = u2.user_id
and o.user_id = u3.user_id
and c.user_id = u4.user_id
and c.account_status_id = as1.account_status_id
and c.rating_id = r.rating_id
and c.crm_status_id = crm.crm_status_id
and o.opportunity_status_id = os.opportunity_status_id
and opportunity_id = $opportunity_id";

$rst = $con->execute($sql);

if ($rst) {
    $company_id = $rst->fields['company_id'];
    $company_name = $rst->fields['company_name'];
    $company_code = $rst->fields['company_code'];
    $contact_id = $rst->fields['contact_id'];
    $first_names = $rst->fields['first_names'];
    $last_name = $rst->fields['last_name'];
    $work_phone = $rst->fields['work_phone'];
    $email = $rst->fields['email'];
    $crm_status_display_html = $rst->fields['crm_status_display_html'];
    $account_status_display_html = $rst->fields['account_status_display_html'];
    $rating_display_html = $rst->fields['rating_display_html'];
    $contact_id = $rst->fields['contact_id'];
    $campaign_id = $rst->fields['campaign_id'];
    $campaign_title = $rst->fields['campaign_title'];
    $opportunity_status_display_html = $rst->fields['opportunity_status_display_html'];
    $opportunity_owner_username = $rst->fields['opportunity_owner_username'];
    $account_owner_username = $rst->fields['account_owner_username'];
    $opportunity_title = htmlspecialchars($rst->fields['opportunity_title']);
    $opportunity_description = $rst->fields['opportunity_description'];
    $size = $rst->fields['size'];
    $probability = $rst->fields['probability'];
    $close_at = $con->userdate($rst->fields['close_at']);
    $entered_at = $con->userdate($rst->fields['entered_at']);
    $last_modified_at = $con->userdate($rst->fields['last_modified_at']);
    $entered_by = $rst->fields['entered_by_username'];
    $last_modified_by = $rst->fields['last_modified_by_username'];
    $rst->close();
}

// most recent activities

$sql_activities = "select activity_id,
    activity_title,
    scheduled_at,
    a.entered_at,
    a.on_what_table,
    a.on_what_id,
    activity_status,
    at.activity_type_pretty_name,
    cont.contact_id,
    cont.first_names as contact_first_names,
    cont.last_name as contact_last_name,
    u.username,
    if(activity_status = 'o' and ends_at < now(), 1, 0) as is_overdue
    from activity_types at, users u, activities a left join contacts cont on a.contact_id = cont.contact_id
    where a.on_what_table = 'opportunities'
    and a.on_what_id = $opportunity_id
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
        $on_what_table = $rst->fields['on_what_table'];
        $on_what_id = $rst->fields['on_what_id'];

        if ($open_p == 'o') {
            if ($is_overdue) {
                $classname = 'overdue_activity';
            } else {
                $classname = 'open_activity';
            }
        } else {
            $classname = 'closed_activity';
        }

        $activity_rows .= '<tr>';
        $activity_rows .= "<td class='$classname'><a href='$http_site_root/activities/one.php?return_url=/contacts/one.php?contact_id=$contact_id&activity_id=" . $rst->fields['activity_id'] . "'>" . $rst->fields['activity_title'] . '</a></td>';
        $activity_rows .= '<td class=' . $classname . '>' . $rst->fields['username'] . '</td>';
        $activity_rows .= '<td class=' . $classname . '>' . $rst->fields['activity_type_pretty_name'] . '</td>';
        $activity_rows .= '<td class=' . $classname . '>' . $rst->fields['contact_first_names'] . ' ' . $rst->fields['contact_last_name'] . "</td>";
        $activity_rows .= '<td colspan=2 class=' . $classname . '>' . $con->userdate($rst->fields['scheduled_at']) . '</td>';
        $activity_rows .= '</tr>';
        $rst->movenext();
    }
    $rst->close();
}

$categories_sql = "select category_pretty_name
    from categories c, category_scopes cs, category_category_scope_map ccsm, entity_category_map ecm
    where ecm.on_what_table = 'opportunities'
    and ecm.on_what_id = $opportunity_id
    and ecm.category_id = c.category_id
    and cs.category_scope_id = ccsm.category_scope_id
    and c.category_id = ccsm.category_id
    and cs.on_what_table = 'opportunities'
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

$categories = implode($categories, ", ");

$sql = "select note_id, note_description, entered_by, entered_at, username from notes, users
where notes.entered_by = users.user_id
and on_what_table = 'opportunities' and on_what_id = $opportunity_id
and note_record_status = 'a' order by entered_at desc";

$rst = $con->execute($sql);

if ($rst) {
    while (!$rst->EOF) {
        $note_rows .= "<tr>";
        $note_rows .= "<td class=widget_content><font class=note_label>" . $con->userdate($rst->fields['entered_at']) . " &bull; " . $rst->fields['username'] . " &bull; <a href='../notes/edit.php?note_id=" . $rst->fields['note_id'] . "&return_url=/opportunities/one.php?opportunity_id=" . $opportunity_id . "'>Edit</a></font><br>" . $rst->fields['note_description'] . "</td>";
        $note_rows .= "</tr>";
        $rst->movenext();
    }
    $rst->close();
}

$sql = "select * from files, users where files.entered_by = users.user_id and on_what_table = 'opportunities' and on_what_id = $opportunity_id and file_record_status = 'a'";

$rst = $con->execute($sql);

if ($rst) {
    while (!$rst->EOF) {
        $file_rows .= '<tr>';
        $file_rows .= "<td class=widget_content><a href='$http_site_root/files/one.php?return_url=/opportunities/one.php?opportunity_id=$opportunity_id&file_id=" . $rst->fields['file_id'] . "'>" . $rst->fields['file_pretty_name'] . '</a></td>';
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

$sql = "select concat(first_names, ' ', last_name), contact_id from contacts where company_id = $company_id and contact_record_status = 'a' order by last_name";
$rst = $con->execute($sql);
if ($rst) {
    $contact_menu = $rst->getmenu2('contact_id', $contact_id, true);
    $rst->close();
}


$con->close();

if (strlen($categories) == 0) {
    $categories = "No categories";
}

if (strlen($activity_rows) == 0) {
    $activity_rows = "<tr><td class=widget_content colspan=6>No activities</td></tr>";
}

if (strlen($note_rows) == 0) {
    $note_rows = "<tr><td class=widget_content colspan=4>No notes</td></tr>";
}

if (strlen($file_rows) == 0) {
    $file_rows = "<tr><td class=widget_content colspan=4>No files</td></tr>";
}

$page_title = "One Opportunity : $opportunity_title";
start_page($page_title, true, $msg);

?>

<table border=0 cellpadding=0 cellspacing=0 width=100%>
    <tr>
        <td class=lcol width=70% valign=top>

        <table class=widget cellspacing=1 width=100%>
            <tr>
                <td class=widget_header>Opportunity Details</td>
            </tr>
            <tr>
                <td class=widget_content>

                    <table border=0 cellpadding=0 cellspacing=0 width=100%>
                        <tr>
                            <td width=50% class=clear align=left valign=top>
                                <table border=0 cellpadding=0 cellspacing=0 width=100%>
                                <tr>
                                    <td width=1% class=sublabel>Title</td>
                                    <td class=clear><?php  echo $opportunity_title; ?></td>
                                </tr>
                                <tr>
                                    <td class=sublabel>Owner</td>
                                    <td class=clear><?php  echo $opportunity_owner_username; ?></td>
                                </tr>
                                <tr>
                                    <td class=sublabel>Campaign</td>
                                    <td class=clear><a href="../campaigns/one.php?campaign_id=<?php  echo $campaign_id; ?>"><?php  echo $campaign_title; ?></a></td>
                                </tr>
                                <tr>
                                    <td class=sublabel>Size</td>
                                    <td class=clear>$<?php  echo number_format($size, 2); ?></td>
                                </tr>
                                <tr>
                                    <td class=sublabel>Probability</td>
                                    <td class=clear><?php  echo $probability; ?>%</td>
                                </tr>
                                <tr>
                                    <td class=sublabel>Weighted&nbsp;Size</td>
                                    <td class=clear>$<?php  echo number_format($size * $probability/100, 2); ?></td>
                                </tr>
                                <tr>
                                    <td class=sublabel>Status</td>
                                    <td class=clear><?php  echo $opportunity_status_display_html; ?></td>
                                </tr>
                                <tr>
                                    <td class=sublabel>Closes</td>
                                    <td class=clear><?php  echo $close_at; ?></td>
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
                                <tr>
                                    <td class=sublabel>E-Mail</td>
                                    <td class=clear><a href='mailto:<?php echo $email . "' onclick=\"location.href='../activities/new-2.php?user_id=$session_user_id&activity_type_id=3&on_what_id=$opportunity_id&contact_id=$contact_id&on_what_table=opportunities&activity_title=email RE: $opportunity_title&company_id=$company_id&email=$email&return_url=/opportunities/one.php?opportunity_id=$opportunity_id'\" >" . htmlspecialchars($email); ?></a></td>
                                </tr>
                                <tr>
                                    <td class=sublabel>&nbsp;</td>
                                    <td class=clear>&nbsp;</td>
                                </tr>
                                <tr>
                                    <td class=sublabel>Company</td>
                                    <td class=clear><a href="<?php  echo $http_site_root; ?>/companies/one.php?company_id=<?php  echo $company_id; ?>"><?php  echo $company_name; ?></a> (<?php  echo $company_code; ?>)</td>
                                </tr>
                                <tr>
                                    <td class=sublabel>Acct. Owner</td>
                                    <td class=clear><?php  echo $account_owner_username; ?></td>
                                </tr>
                                <tr>
                                    <td class=sublabel>CRM Status</td>
                                    <td class=clear><?php  echo $crm_status_display_html; ?></td>
                                </tr>
                                <tr>
                                    <td class=sublabel>Account Status</td>
                                    <td class=clear><?php  echo $account_status_display_html; ?></td>
                                </tr>
                            </table>

                            </td>
                        </tr>
                    </table>

                    <p>
                    <?php
                        // clean this up for display
                        $opportunity_description = htmlspecialchars ($opportunity_description);
                        $opportunity_description = str_replace("\n", '<br>', $opportunity_description);
                        echo $opportunity_description;
                    ?>

                </td>
            </tr>
            <tr>
                <td class=widget_content_form_element><input class=button type=button value="Edit" onclick="javascript: location.href='edit.php?opportunity_id=<?php  echo $opportunity_id; ?>';"></td>
            </tr>
        </table>

        <!-- activities //-->
        <form action="../activities/new-2.php" method=post>
        <input type=hidden name=return_url value="/opportunities/one.php?opportunity_id=<?php  echo $opportunity_id; ?>">
        <input type=hidden name=company_id value="<?php echo $company_id ?>">
        <input type=hidden name=on_what_table value="opportunities">
        <input type=hidden name=on_what_id value="<?php  echo $opportunity_id; ?>">
        <input type=hidden name=activity_status value="o">
        <table class=widget cellspacing=1 width=100%>
            <tr>
                <td class=widget_header colspan=6>Activities</td>
            </tr>
            <tr>
                <td class=widget_label>Title</td>
                <td class=widget_label>User</td>
                <td class=widget_label>Type</td>
                <td class=widget_label>Contact</td>
                <td colspan=2 class=widget_label>On</td>
            </tr>
            <tr>
                <td class=widget_content_form_element><input type=text name=activity_title size=50></td>
                <td class=widget_content_form_element><?php  echo $user_menu; ?></td>
                <td class=widget_content_form_element><?php  echo $activity_type_menu; ?></td>
                <td class=widget_content_form_element><?php  echo $contact_menu; ?></td>
                <td colspan=2 class=widget_content_form_element><input type=text size=12 name=scheduled_at value="<?php  echo date('Y-m-d'); ?>"> <input class=button type=submit value="Add"> <input class=button type=button onclick="javascript: markComplete();" value="Done"></td>
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
                <td class=widget_content_form_element><input type=button class=button onclick="javascript: location.href='categories.php?opportunity_id=<?php  echo $opportunity_id; ?>';" value="Manage"></td>
            </tr>
        </table>

        <!-- notes //-->
        <form action="../notes/new.php" method="post">
        <input type="hidden" name="on_what_table" value="opportunities">
        <input type="hidden" name="on_what_id" value="<?php echo $opportunity_id ?>">
        <input type="hidden" name="return_url" value="/opportunities/one.php?opportunity_id=<?php echo $opportunity_id ?>">
        <table class=widget cellspacing=1 width=100%>
            <tr>
                <td class=widget_header>Notes</td>
            </tr>
            <?php echo $note_rows; ?>
            <tr>
                <td class=widget_content_form_element colspan=4><input type=submit class=button value="New"></td>
            </tr>
        </table>
        </form>

        <!-- files //-->
        <form action="<?php  echo $http_site_root; ?>/files/new.php" method="post">
        <input type=hidden name=on_what_table value="opportunities">
        <input type=hidden name=on_what_id value="<?php  echo $opportunity_id; ?>">
        <input type=hidden name=return_url value="/opportunities/one.php?opportunity_id=<?php  echo $opportunity_id; ?>">
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

<?php

end_page();

/**
 * $Log:
 */
?>
