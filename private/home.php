<?php

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

$session_user_id = session_check();

$msg = $_GET['msg'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
// $con->debug = 1;

$sql_activities = "select activity_id, 
activity_title, 
scheduled_at, 
ends_at,
a.on_what_table, 
a.on_what_id, 
a.entered_at, 
activity_status, 
at.activity_type_pretty_name, 
c.company_id, 
c.company_name, 
cont.contact_id, 
cont.first_names as contact_first_names, 
cont.last_name as contact_last_name, 
if(activity_status = 'o' and ends_at < now(), 1, 0) as is_overdue
from activity_types at, companies c, activities a left join contacts cont on a.contact_id = cont.contact_id
where a.user_id = $session_user_id
and a.activity_type_id = at.activity_type_id
and a.company_id = c.company_id
and a.activity_status = 'o'
and a.activity_record_status = 'a'
order by is_overdue desc, a.scheduled_at, a.entered_at";

$rst = $con->selectlimit($sql_activities, $display_how_many_activities_on_company_page);

if ($rst) {
    while (!$rst->EOF) {
		
		$company_id = $rst->fields['company_id'];
		$company_name = $rst->fields['company_name'];
        $activity_title = $rst->fields['activity_title'];
        $activity_description = $rst->fields['activity_description'];
        $on_what_table = $rst->fields['on_what_table'];
        $on_what_id = $rst->fields['on_what_id'];
        $scheduled_at = $con->userdate($rst->fields['scheduled_at']);
        $ends_at = $con->userdate($rst->fields['ends_at']);
        $activity_status = $rst->fields['activity_status'];
		
		$attached_to_link = '';
		$attached_to_name = '';
        
        if ($on_what_table == 'opportunities') {
            $attached_to_link = "<a href='$http_site_root/opportunities/one.php?opportunity_id=$on_what_id'>";
            $sql = "select opportunity_title as attached_to_name from opportunities where opportunity_id = $on_what_id";
        } elseif ($on_what_table == 'cases') {
            $attached_to_link = "<a href='$http_site_root/cases/one.php?case_id=$on_what_id'>";
            $sql = "select case_title as attached_to_name from cases where case_id = $on_what_id";
		} else {
            $attached_to_link = "";
            $sql = "select * from users where 1 = 2";
        }
		
        $rst2 = $con->execute($sql);
        
		if ($rst2) {
            $attached_to_name = $rst2->fields['attached_to_name'];
            $rst2->close();
        }
		
        $attached_to_link .= $attached_to_name . "</a>";
		
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

        $activity_rows .= '<tr>';
        $activity_rows .= "<td class='$classname'><a href='$http_site_root/activities/one.php?return_url=/private/home.php&activity_id=" . $rst->fields['activity_id'] . "'>" . $rst->fields['activity_title'] . '</a></td>';
        $activity_rows .= '<td class=' . $classname . '>' . $rst->fields['activity_type_pretty_name'] . '</td>';
        $activity_rows .= '<td class=' . $classname . "><a href='../companies/one.php?company_id=" . $rst->fields['company_id'] . "'>" . $rst->fields['company_name'] . '</a></td>';
        $activity_rows .= '<td class=' . $classname . "><a href='../contacts/one.php?contact_id=" . $rst->fields['contact_id'] . "'>" . $rst->fields['contact_first_names'] . ' ' .  $rst->fields['contact_last_name'] . '</a></td>';
        $activity_rows .= '<td class=' . $classname . '>' . $attached_to_link . '</td>';
        $activity_rows .= '<td class=' . $classname . '>' . $con->userdate($rst->fields['scheduled_at']) . '</td>';
        $activity_rows .= '<td class=' . $classname . '>' . $con->userdate($rst->fields['ends_at']) . '</td>';
        $activity_rows .= '</tr>';
        $rst->movenext();
    }
    $rst->close();
}

$con->close();

if (!strlen($activity_rows) > 0) {
    $activity_rows = "<tr><td class=widget_content colspan=7>No open activities</td></tr>";
}

$page_title = "Home";
start_page($page_title);

?>

<table border=0 cellpadding=0 cellspacing=0 width=100%>
    <tr>
        <td class=lcol width=75% valign=top>

        <table class=widget cellspacing=1 width=100%>
            <tr>
                <td class=widget_header colspan=7>Open Activities</td>
            </tr>
            <tr>
                <td class=widget_label>Activity</td>
                <td class=widget_label>Type</td>
                <td class=widget_label>Company</td>
                <td class=widget_label>Contact</td>
                <td class=widget_label>About</td>
                <td class=widget_label>Scheduled</td>
                <td class=widget_label>Due</td>
            </tr>
            <?php  echo $activity_rows ?>
        </table>

        </td>
        <!-- gutter //-->
        <td class=gutter width=1%>
        &nbsp;
        </td>
        <!-- right column //-->
        <td class=rcol width=24% valign=top>

        </td>
    </tr>
</table>

<?php end_page(); ?>