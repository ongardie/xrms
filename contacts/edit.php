<?php

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

$session_user_id = session_check();

$contact_id = $_GET['contact_id'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
// $con->debug = 1;

$con->execute("update users set last_hit = " . $con->dbtimestamp(mktime()) . " where user_id = $session_user_id");

update_recent_items($con, $session_user_id, "contacts", $contact_id);

$sql = "select cont.*, c.company_id, c.company_name
from contacts cont, companies c
where cont.company_id = c.company_id
and contact_id = $contact_id";

$rst = $con->execute($sql);

if ($rst) {
    $company_id = $rst->fields['company_id'];
    $company_name = $rst->fields['company_name'];
    $last_name = $rst->fields['last_name'];
    $first_names = $rst->fields['first_names'];
    $summary = $rst->fields['summary'];
    $title = $rst->fields['title'];
    $description = $rst->fields['description'];
    $email = $rst->fields['email'];
    $work_phone = $rst->fields['work_phone'];
    $cell_phone = $rst->fields['cell_phone'];
    $home_phone = $rst->fields['home_phone'];
    $aol_name = $rst->fields['aol_name'];
    $yahoo_name = $rst->fields['yahoo_name'];
    $msn_name = $rst->fields['msn_name'];
    $interests = $rst->fields['interests'];
    $custom1 = $rst->fields['custom1'];
    $custom2 = $rst->fields['custom2'];
    $custom3 = $rst->fields['custom3'];
    $custom4 = $rst->fields['custom4'];
    $rst->close();
}

//
//  list of most recent activities
//

$sql_activities = "select activity_id, activity_title, scheduled_at, a.entered_at, activity_status, at.activity_type_pretty_name, cont.first_names as contact_first_names, cont.last_name as contact_last_name, u.username, if(activity_status = 'o' and scheduled_at < now(), 1, 0) as is_overdue
from activity_types at, users u, activities a, contacts cont
where a.on_what_table = 'contacts' and on_what_id = $contact_id
and a.on_what_id = cont.contact_id
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
        $activity_rows .= "<td class='$classname'><a href='$http_site_root/activities/one.php?return_url=/contacts/one.php?contact_id=$contact_id&activity_id=" . $rst->fields['activity_id'] . "'>" . $rst->fields['activity_title'] . '</a></td>';
        $activity_rows .= '<td class=' . $classname . '>' . $rst->fields['username'] . '</td>';
        $activity_rows .= '<td class=' . $classname . '>' . $rst->fields['activity_type_pretty_name'] . '</td>';
        $activity_rows .= '<td class=' . $classname . '>' . $con->userdate($rst->fields['scheduled_at']) . '</td>';
        $activity_rows .= '</tr>';
        $rst->movenext();
    }
    $rst->close();
}

// we should allow users to delete this contact if there are others

$sql = "select count(contact_id) as contact_count from contacts where company_id = $company_id and contact_record_status = 'a'";
$rst = $con->execute($sql);
$contact_count = $rst->fields['contact_count'];
$rst->close();

$sql = "select username, user_id from users where user_record_status = 'a' order by username";
$rst = $con->execute($sql);
$user_menu = $rst->getmenu2('user_id', $session_user_id, false);
$rst->close();

$sql = "select activity_type_pretty_name, activity_type_id from activity_types where activity_type_record_status = 'a'";
$rst = $con->execute($sql);
$activity_type_menu = $rst->getmenu2('activity_type_id', '', false);
$rst->close();

add_audit_item($con, $session_user_id, 'view contact', 'contacts', $contact_id);

$con->close();

if (strlen($activity_rows) == 0) {
    $activity_rows = "<tr><td class=widget_content colspan=5>No activities</td></tr>";
}

$page_title = $first_names . ' ' . $last_name;
start_page($page_title, true, $msg);

?>

<table border=0 cellpadding=0 cellspacing=0 width=100%>
    <tr>
        <td class=lcol width=30% valign=top>

        <form action=edit-2.php method=post>
        <input type=hidden name=contact_id value=<?php  echo $contact_id; ?>>
        <table class=widget cellspacing=1 width=100%>
            <tr>
                <td class=widget_header colspan=2>Contact Information</td>
            </tr>
            <tr>
                <td class=widget_label_right>Company</td>
                <td class=widget_content_form_element><a href="<?php  echo $http_site_root;
?>/companies/one.php?company_id=<?php echo $company_id; ?>"><?php echo $company_name; ?></a></td>
            </tr>
            <tr>
                <td class=widget_label_right>First&nbsp;Names</td>
                <td class=widget_content_form_element><input type=text name=first_names value="<?php echo $first_names; ?>" size=30></td>
            </tr>
            <tr>
                <td class=widget_label_right>Last&nbsp;Name</td>
                <td class=widget_content_form_element><input type=text name=last_name value="<?php  echo $last_name; ?>" size=30></td>
            </tr>
            <tr>
                <td class=widget_label_right>Summary</td>
                <td class=widget_content_form_element><input type=text name=summary value="<?php  echo $summary; ?>" size=35></td>
            </tr>
            <tr>
                <td class=widget_label_right>Title</td>
                <td class=widget_content_form_element><input type=text name=title value="<?php  echo $title; ?>" size=30></td>
            </tr>
            <tr>
                <td class=widget_label_right>Description</td>
                <td class=widget_content_form_element><input type=text name=description value='<?php  echo $description; ?>' size=30></td>
            </tr>
            <tr>
                <td class=widget_label_right>E-Mail</td>
                <td class=widget_content_form_element><input type=text name=email value='<?php  echo $email; ?>' size=30></td>
            </tr>
            <tr>
                <td class=widget_label_right>Work&nbsp;Phone</td>
                <td class=widget_content_form_element><input type=text name=work_phone value='<?php  echo $work_phone; ?>' size=30></td>
            </tr>
            <tr>
                <td class=widget_label_right>Cell&nbsp;Phone</td>
                <td class=widget_content_form_element><input type=text name=cell_phone value='<?php  echo $cell_phone; ?>' size=30></td>
            </tr>
            <tr>
                <td class=widget_label_right>Home&nbsp;Phone</td>
                <td class=widget_content_form_element><input type=text name=home_phone value='<?php  echo $home_phone; ?>' size=30></td>
            </tr>
            <tr>
                <td class=widget_label_right>AOL&nbsp;Name</td>
                <td class=widget_content_form_element><input type=text name=aol_name value='<?php  echo $aol_name; ?>' size=25></td>
            </tr>
            <tr>
                <td class=widget_label_right>Yahoo&nbsp;Name</td>
                <td class=widget_content_form_element><input type=text name=yahoo_name value='<?php  echo $yahoo_name; ?>' size=25></td>
            </tr>
            <tr>
                <td class=widget_label_right>MSN&nbsp;Name</td>
                <td class=widget_content_form_element><input type=text name=msn_name value='<?php  echo $msn_name; ?>' size=25></td>
            </tr>
            <tr>
                <td class=widget_label_right>Interests</td>
                <td class=widget_content_form_element><input type=text name=interests size=35 value='<?php  echo $interests; ?>'></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php  echo $contact_custom1_label; ?></td>
                <td class=widget_content_form_element><input type=text name=custom1 size=35 value="<?php  echo $custom1; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php  echo $contact_custom2_label; ?></td>
                <td class=widget_content_form_element><input type=text name=custom2 size=35 value="<?php  echo $custom2; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php  echo $contact_custom3_label; ?></td>
                <td class=widget_content_form_element><input type=text name=custom3 size=35 value="<?php  echo $custom3; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php  echo $contact_custom4_label; ?></td>
                <td class=widget_content_form_element><input type=text name=custom4 size=35 value="<?php  echo $custom4; ?>"></td>
            </tr>
            <tr>
                <td class=widget_content_form_element colspan=2><input class=button type=submit
value="Save"> <input class=button type=button value="Mail Merge" onclick="javascript:
location.href='../email/email.php?scope=contact&contact_id=<?php  echo $contact_id; ?>';"><?php if ($contact_count > 1)
{echo(" <input type=button class=button onclick=\"javascript: location.href='delete.php?company_id=$company_id&contact_id=$contact_id';\" value='Delete' onclick=\"javascript: return confirm('Delete Contact?')\"");} ?></td>
            </tr>
        </table>
        </form>

        </td>
        <!-- gutter //-->
        <td class=gutter width=2%>
        &nbsp;
        </td>
        <!-- right column //-->
        <td class=rcol width=68% valign=top>

        </td>
    </tr>
</table>

<?php end_page();; ?>
