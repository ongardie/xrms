<?php
/**
 * Create a graph of activity for the requested user.
 *
 * @author Glenn Powers
 *
 * $Id: completed-items.php,v 1.2 2004/04/20 19:37:27 braverock Exp $
 */
require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb/adodb-pager.inc.php');

// $session_user_id = session_check();
$msg = $_GET['msg'];
$starting = $_GET['starting'];
$ending = $_GET['ending'];
$user_id = $_GET['user_id'];
$type = $_GET['type'];
$friendly = $_GET['friendly'];
$use_hr = 1; // comment this out to remove <hr>'s from between lines
$send_email = $_GET['send_email'];

if (!$starting) {
    $starting = date("Y-m-d") . " 00:00:00";
    }

if (!$ending) {
    $ending = date("Y-m-d") . " 23:59:59";
    }

if ($send_email) {
    $friendly = "y";
}

$page_title = "Completed Items";
if ($friendly != "y") {
    start_page($page_title, true, $msg);
}

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
// $con->debug = 1;

$sql = "select username, user_id from users where user_record_status = 'a' order by username";
$rst = $con->execute($sql);
$user_menu = $rst->getmenu2('user_id', '', false);
$rst->close();
?>

<?php
if ($friendly != "y") {
    $output .= "
<table>
    <tr>
        <th align=left>Start</th>
        <th align=left>End</th>
        <th align=left>User</th>
        <th align=left>Type</th>
        <th align=left></th>
    </tr>
        <tr>
            <form action=completed-items.php method=get>
                <td><input type=text name=starting value=\"" . $starting . "\"></td>
                <td><input type=text name=ending value=\"" . $ending . "\"></td>
                <td>" . $user_menu . "</td>
                <td><select name=type>
                    <option value=all>All</option>
                    <option value=activities>Activities</option>
                    <option value=campaigns>Campaigns</option>
                    <option value=opportunities>Opportunities</option>
                    <option value=cases>Cases</option>
                    </select></td>
                <td><input class=button type=submit value=Go></td>
            </form>
        </tr>
</table>
<p>&nbsp;</p>
    ";
}
?>

<?php
if ($user_id) {

    $sql = "select username, email from users where user_id = $user_id";
    $rst = $con->execute($sql);
    $username = $rst->fields['username'];
    $email = $rst->fields['email'];
    $rst->close();
    if (($type == "activities") || ($type == "all")) {
        $sql = "SELECT * from activities where activity_status = 'c' and activity_record_status = 'a' and user_id = $user_id and entered_at between " . $con->qstr($starting, get_magic_quotes_gpc()) . " and " . $con->qstr($ending, get_magic_quotes_gpc()) . "order by entered_at ";
        $rst = $con->execute($sql);

        if ($rst) {
            $output .= "<p><font size=+2><b>COMPLETED ACTIVITIES for $username<b></font><br></p>\n";
            $output .= "<table>";
            $output .= "<tr><td colspan=6><hr></td></tr>\n";
            $output .= "    <tr>";
            $output .= "        <th align=left>Start</th>";
            $output .= "        <th align=left>End</th>";
            $output .= "        <th align=left>Type</th>";
            $output .= "        <th align=left>Company</th>";
            $output .= "        <th align=left>Contact</th>";
            $output .= "        <th align=left>Activity</th>";
            $output .= "    </tr>";
            $output .= "<tr><td colspan=6><hr></td></tr>\n";
            while (!$rst->EOF) {
                $output .= "<tr>\n<td>" . $rst->fields['scheduled_at'] . "&nbsp;&nbsp;&nbsp;</td>\n";
                $output .= "<td>" . $rst->fields['ends_at'] . "&nbsp;&nbsp;</td>\n";
                $sql4 = "SELECT activity_type_pretty_name from activity_types where activity_type_id = " . $rst->fields['activity_type_id'];
                $rst4 = $con->execute($sql4);
                $output .= "<td>" . $rst4->fields['activity_type_pretty_name'] . "&nbsp;&nbsp;</td>\n";
                // $output .= "<td>" . $rst->fields['activity_type_id'] . "&nbsp;&nbsp;</td>\n";
                $sql5 = "SELECT company_name from companies where company_id = " . $rst->fields['company_id'];
                $rst5 = $con->execute($sql5);
                $output .= "<td>" . $rst5->fields['company_name'] . "&nbsp;&nbsp;&nbsp;</td>\n";
                $sql6 = "SELECT last_name, first_names from contacts where contact_id = " . $rst->fields['contact_id'];
                $rst6 = $con->execute($sql6);
                $output .= "<td>" . $rst6->fields['last_name'] . ", " . $rst6->fields['first_names'] . "&nbsp;&nbsp;&nbsp;</td>\n";
                $output .= "<td><a href=\"../activities/one.php?activity_id=" . $rst->fields['activity_id'] . "\">" . $rst->fields['activity_title'] . "</a></td>\n</td>\n";
                if ($use_hr) {
                    $output .= "<tr><td colspan=7><hr></td></tr>\n";
                }
                $rst->movenext();
            }
        $rst->close();
        $output .= "</table>";
        }
    else {
        $output .= "<p><b>NO COMPLETED ACTIVITIES for $username<b><br></p>\n";
    }

    } // End Activity Type
    if (($type == "campaigns") || ($type == "all")) {
        $sql = "SELECT * from campaigns where campaign_status_id IN ('4') and campaign_record_status = 'a' and user_id = $user_id and entered_at between " . $con->qstr($starting, get_magic_quotes_gpc()) . " and " . $con->qstr($ending, get_magic_quotes_gpc()) . " order by entered_at ";
        $rst = $con->execute($sql);
        if ($rst) {
            $output .= "<p><font size=+2><b>COMPLETED CAMPAIGNS for $username<b></font><br></p>\n";
            $output .= "<table>";
            $output .= "<tr><td colspan=4><hr></td></tr>\n";
            $output .= "    <tr>";
            $output .= "        <th align=left>Start</th>";
            $output .= "        <th align=left>End</th>";
            $output .= "        <th align=left>Type</th>";
            $output .= "        <th align=left>Campaign</th>";
            $output .= "    </tr>";
            $output .= "<tr><td colspan=4><hr></td></tr>\n";
            while (!$rst->EOF) {
                $output .= "<tr>\n<td>" . $rst->fields['starts_at'] . "&nbsp;&nbsp;&nbsp;</td>\n";
                $output .= "<td>" . $rst->fields['ends_at'] . "&nbsp;&nbsp;</td>\n";
                $sql4 = "SELECT campaign_type_pretty_name from campaign_types where campaign_type_id = " . $rst->fields['campaign_type_id'];
                $rst4 = $con->execute($sql4);
                $output .= "<td>" . $rst4->fields['campaign_type_pretty_name'] . "&nbsp;&nbsp;</td>\n";
                $output .= "<td><a href=\"../campaigns/one.php?campaign_id=" . $rst->fields['campaign_id'] . "\">" . $rst->fields['campaign_title'] . "</a></td>\n</td>\n";
                if ($use_hr) {
                    $output .= "<tr><td colspan=4><hr></td></tr>\n";
                }
                $rst->movenext();
            }
            $rst->close();
            $output .= "</table>";
        }
    } // End Campaigns Type
    if (($type == "opportunities") || ($type == "all")) {
        $sql = "SELECT * from opportunities, opportunity_statuses where
                status_open_indicator = 'c'
                and opportunity_record_status = 'a'
                and user_id = $user_id
                and entered_at between " . $con->qstr($starting, get_magic_quotes_gpc())
              . " and " . $con->qstr($ending, get_magic_quotes_gpc()) . "
                order by entered_at";
        $rst = $con->execute($sql);
        if ($rst) {
            $output .= "<p><font size=+2><b>COMPLETED OPPORTUNITIES for $username<b></font><br></p>\n";
            $output .= "<table>";
            $output .= "<tr><td colspan=4><hr></td></tr>\n";
            $output .= "    <tr>";
            $output .= "        <th align=left>Entered</th>";
            $output .= "        <th align=left>Type</th>";
            $output .= "        <th align=left>Company</th>";
            $output .= "        <th align=left>Contact</th>";
            $output .= "        <th align=left>Opportunity</th>";
            $output .= "    </tr>";
            $output .= "<tr><td colspan=4><hr></td></tr>\n";
            while (!$rst->EOF) {
                $output .= "<tr>\n<td>" . $rst->fields['entered_at'] . "&nbsp;&nbsp;&nbsp;</td>\n";
                $output .= "<td>" . $rst->fields['ends_at'] . "&nbsp;&nbsp;</td>\n";
                $sql4 = "SELECT opportunity_type_pretty_name from campaign_types where opportunity_type_id = " . $rst->fields['opportunity_type_id'];
                $rst4 = $con->execute($sql4);
                $output .= "<td>" . $rst4->fields['opportunity_type_pretty_name'] . "&nbsp;&nbsp;</td>\n";
                $sql5 = "SELECT company_name from companies where company_id = " . $rst->fields['company_id'];
                $rst5 = $con->execute($sql5);
                $output .= "<td>" . $rst5->fields['company_name'] . "&nbsp;&nbsp;&nbsp;</td>\n";
                $sql6 = "SELECT last_name, first_names from contacts where contact_id = " . $rst->fields['contact_id'];
                $rst6 = $con->execute($sql6);
                $output .= "<td>" . $rst6->fields['last_name'] . ", " . $rst6->fields['first_names'] . "&nbsp;&nbsp;&nbsp;</td>\n";
                $output .= "<td><a href=\"../opportunities/one.php?opportunity_id=" . $rst->fields['opportunity_id'] . "\">" . $rst->fields['opportunity_title'] . "</a></td>\n</td>\n";
                if ($use_hr) {
                    $output .= "<tr><td colspan=4><hr></td></tr>\n";
                }
                $rst->movenext();
            }
            $rst->close();
            $output .= "</table>";
        }
    } // End Opportunities Type
    if (($type == "cases") || ($type == "all")) {
        $sql = "SELECT * from cases, case_statuses where
                status_open_indicator = 'c'
                and case_record_status = 'a'
                and user_id = $user_id
                and entered_at between " . $con->qstr($starting, get_magic_quotes_gpc())
              . " and " . $con->qstr($ending, get_magic_quotes_gpc()) . "
              order by entered_at";

        $rst = $con->execute($sql);
        if ($rst) {
            $output .= "<p><font size=+2><b>COMPLETED CASES for $username<b></font><br></p>\n";
            $output .= "<table>";
            $output .= "<tr><td colspan=5><hr></td></tr>\n";
            $output .= "    <tr>";
            $output .= "        <th align=left>Entered</th>";
            $output .= "        <th align=left>Due</th>";
            $output .= "        <th align=left>Company</th>";
            $output .= "        <th align=left>Contact</th>";
            $output .= "        <th align=left>Case</th>";
            $output .= "    </tr>";
            $output .= "<tr><td colspan=5><hr></td></tr>\n";
            while (!$rst->EOF) {
                $output .= "<tr>\n<td>" . $rst->fields['entered_at'] . "&nbsp;&nbsp;&nbsp;</td>\n";
                $output .= "<td>" . $rst->fields['due_at'] . "&nbsp;&nbsp;</td>\n";
                $sql5 = "SELECT company_name from companies where company_id = " . $rst->fields['company_id'];
                $rst5 = $con->execute($sql5);
                $output .= "<td>" . $rst5->fields['company_name'] . "&nbsp;&nbsp;&nbsp;</td>\n";
                $sql6 = "SELECT last_name, first_names from contacts where contact_id = " . $rst->fields['contact_id'];
                $rst6 = $con->execute($sql6);
                $output .= "<td>" . $rst6->fields['last_name'] . ", " . $rst6->fields['first_names'] . "&nbsp;&nbsp;&nbsp;</td>\n";
                $output .= "<td><a href=\"../cases/one.php?case_id=" . $rst->fields['case_id'] . "\">" . $rst->fields['case_title'] . "</a></td>\n</td>\n";
                if ($use_hr) {
                    $output .= "<tr><td colspan=5><hr></td></tr>\n";
                }
                $rst->movenext();
            }
            $rst->close();
            $output .= "</table>";
        }
        else {
            $output .= "<p><b>NO COMPLETED CASES for $username<b><br></p>\n";
        }
    } // End Cases Type

    //$rst->close();
} // End If User

$con->close();
if ($send_email == "y") {
    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
    mail($email, "$app_title: $page_title", $output, $headers);
}
else {
    echo $output;
}
end_page();

/**
 * $Log: completed-items.php,v $
 * Revision 1.2  2004/04/20 19:37:27  braverock
 * - cleaned up sql formatting to handle more cases and be less error prone
 *   - partially fixes SF bugs 938616 & 938620
 *
 * Revision 1.1  2004/04/20 13:34:42  braverock
 * - add activity times report
 * - add open items report
 * - add completed items report
 *   - apply SF patches 928336, 937994, 938094 submitted by Glenn Powers
 *
 */
?>