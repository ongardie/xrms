<?php
/**
 * /activities/some.php
 *
 * View a list of activities
 *
 * $Id: some.php,v 1.4 2004/03/24 18:04:13 maulani Exp $
 */

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

$user_id = 1;

$msg = $_GET['msg'];

$sql = "select *, if(activity_status = 'o' and ends_at < now(), 1, 0) as is_overdue 
from companies c, activity_types at, activities a left outer join contacts cont on cont.contact_id = a.contact_id 
where a.company_id = c.company_id 
and at.activity_type_id = a.activity_type_id 
and a.user_id = $user_id 
and activity_status = 'o' 
order by is_overdue desc, a.scheduled_at, a.entered_at desc";

$con = &adonewconnection($db_dbtype);
$con->connect($db_server, $db_username, $db_password, $db_dbname);

$rst = $con->execute($sql);

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
        
        $open_activities .= '<tr>';
        $open_activities .= '<td class=' . $classname . '><a href=/activities/one.php?activity_id=' . $rst->fields['activity_id'] . '>' . $rst->fields['activity_title'] . '</a></td>';
        $open_activities .= '<td class=' . $classname . '>' . $rst->fields['activity_type_pretty_name'] . '</td>';
        $open_activities .= '<td class=' . $classname . '>' . $rst->fields['company_name'] . '</td>';
        $open_activities .= '<td class=' . $classname . '>' . $rst->fields['first_names'] . " " . $rst->fields['last_name'] . '</td>';
        $open_activities .= '<td class=' . $classname . '>' . $con->userdate($rst->fields['scheduled_at']) . '</td>';
        $open_activities .= '</tr>';
        $rst->movenext();
    }
    $rst->close();
}

$con->close();

if (!strlen($open_activities) > 0) {
    $open_activities = "<tr><td class=widget_content colspan=5>No open activities</td></tr>";
}

$page_title = "Open Activities";
start_page($page_title);

?>

<table border=0 cellpadding=0 cellspacing=0 width=100%>
    <tr>
        <td class=lcol width=65% valign=top>

        <table class=widget cellspacing=1 width=100%>
            <tr>
                <td class=widget_header colspan=5>Activities</td>
            </tr>
            <tr>
                <td class=widget_label>Activity</td>
                <td class=widget_label>Type</td>
                <td class=widget_label>Company</td>
                <td class=widget_label>Contact</td>
                <td class=widget_label>Scheduled At</td>
            </tr>
            <?php  echo $open_activities; ?>
        </table>

        </td>
        <!-- gutter //-->
        <td class=gutter width=2%>
        &nbsp;
        </td>
        <!-- right column //-->
        <td class=rcol width=33% valign=top>
        
        </td>
    </tr>
</table>

<?php

end_page();

/**
 * $Log: some.php,v $
 * Revision 1.4  2004/03/24 18:04:13  maulani
 * - cleanup code formatting
 *
 * Revision 1.3  2004/03/24 17:18:37  maulani
 * - add phpdoc
 *
 *
 */
?>
