<?php
/**
 * @author Glenn Powers
 *
 * $Id: open-items.php,v 1.8 2004/06/12 05:35:58 introspectshun Exp $
 */
require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb/adodb-pager.inc.php');
require_once($include_directory . 'adodb-params.php');

// $session_user_id = session_check();
$msg = $_GET['msg'];
$user_id = $_GET['user_id'];
$type = $_GET['type'];
$friendly = $_GET['friendly'];
$send_email = $_GET['send_email'];
$send_email_to = $_GET['send_email_to'];
$all_users = $_GET['all_users'];
$display = $_GET['display'];

$use_hr = 1; // comment this out to remove <hr>'s from between lines
$say_no_when_none = 1; // display "NO (CASES|ACTIVITIES|CAMPAIGNS|OPPORTUNITES} for First_Names Last_Name"

$userArray = array();

if ($friendly) {
    $display = "";
    }

$page_title = "Open Items";
if (($display) || (!$friendly)) {
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
if (($display) || (!$friendly)) {
    echo "
<table>
    <form action=open-items.php method=get>
    <input type=hidden name=display value=y>
    <tr>
        <th>User</th>
        <th>Type</th>
        <th></th>
    </tr>
        <tr>
            <td>" . $user_menu . "</td>
            <td>
                <select name=type>
                    <option value=all";

if ($type == "all") {
    echo " selected ";
}

echo ">All</option>
                    <option value=activities";

if ($type == "activities") {
    echo " selected ";
}

echo ">Activities</option>
                    <option value=campaigns";

if ($type == "campaigns") {
    echo " selected ";
}

echo ">Campaigns</option>
                    <option value=opportunities";

if ($type == "opportunities") {
    echo " selected ";
}

echo ">Opportunities</option>
                    <option value=cases";

if ($type == "cases") {
    echo " selected ";
}

echo ">Cases</option>
                </select></td>
            <td>
                <input class=button type=submit value=Go>
            </td>
        </tr>
        <tr>
            <td>
                <input name=all_users type=checkbox ";

    if ($all_users) {
        echo "checked";
    }

    echo ">All Users
            </td>
            <td>
                <input name=friendly type=checkbox ";

    if ($friendly) {
        echo "checked";
    }

    echo ">Printer Friendly
            </td>
            </td>
            <td>
            </td>
        </tr>
        <tr>
            <td align=right>
                <input name=send_email type=checkbox ";

    if ($send_email) {
        echo "checked";
    }

    echo ">Send Email To:
            </td>
            <td>
                <input name=send_email_to type=text value=" . $send_email_to . ">
            </td>

    </form>
</table>
<p>&nbsp;</p>
    ";
}
?>

<?php

if (($user_id) && (!$all_users)) {
    $userArray = array($user_id);
}

if ($all_users) {
    $sql = "select user_id from users";
    $rst = $con->execute($sql);
    while (!$rst->EOF) {
        array_push($userArray, $rst->fields['user_id']);
        $rst->movenext();
    }
    $rst->close();
}

if ($userArray) {
foreach ($userArray as $key => $user_id) {

    $sql = "select * from users where user_id = $user_id";
    $rst = $con->execute($sql);
    $username = $rst->fields['username'];
    $email = $rst->fields['email'];
    $name =  $rst->fields['first_names'] . " " . $rst->fields['last_name']; 
    $rst->close();

    if (($type == "activities") || ($type == "all")) {
        $sql = "SELECT * from activities where activity_status = 'o' and activity_record_status = 'a' and user_id = $user_id order by entered_at ";
        $rst = $con->execute($sql);

        if (!$rst->EOF) {
            $output .= "<p><font size=+2><b>OPEN ACTIVITIES for $name<b></font><br></p>\n";
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
        if ($say_no_when_none) {
            $output .= "<p><b>NO OPEN ACTIVITIES for $name<b><br></p>\n";
        }
    }

    } // End Activity Type
    if (($type == "campaigns") || ($type == "all")) {
         $sql = "SELECT * from campaign_statuses, campaigns where
                 campaign_statuses.campaign_status_id = campaigns.campaign_status_id
                 and campaign_statuses.status_open_indicator = 'o'
                 and campaign_record_status = 'a'
                 and user_id = $user_id order by entered_at ";
        $rst = $con->execute($sql);
        if (!$rst->EOF) {
            $output .= "<p><font size=+2><b>OPEN CAMPAIGNS for $name<b></font><br></p>\n";
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
    else {
        if ($say_no_when_none) {
            $output .= "<p><b>NO OPEN CAMPAIGNS for $name<b><br></p>\n";
        }
    }
    } // End Campaigns Type
    if (($type == "opportunities") || ($type == "all")) {
        $sql = "SELECT * from opportunities, opportunity_statuses where
                status_open_indicator = 'o'
                and opportunity_record_status = 'a'
                and opportunity_statuses.opportunity_status_id = opportunities.opportunity_status_id 
                and user_id = $user_id
                order by entered_at ";

        $rst = $con->execute($sql);
        if (!$rst->EOF) {
            $output .= "<p><font size=+2><b>OPEN OPPORTUNITIES for $name<b></font><br></p>\n";
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
    else {
        if ($say_no_when_none) {
            $output .= "<p><b>NO OPEN OPPORTUNITIES for $name<b><br></p>\n";
        }
    }
    } // End Opportunities Type
    if (($type == "cases") || ($type == "all")) {
        $sql = "SELECT * from cases, case_statuses where
                status_open_indicator = 'o'
                and case_record_status = 'a'
                and case_statuses.case_status_id = cases.case_status_id
                and user_id = $user_id
                order by entered_at ";

        $rst = $con->execute($sql);
        if (!$rst->EOF) {
            $output .= "<p><font size=+2><b>OPEN CASES for $name<b></font><br></p>\n";
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
            if ($say_no_when_none) {
                $output .= "<p><b>NO OPEN CASES for $name<b><br></p>\n";
            }
        }
    } // End Cases Type
} // End Foreach User
} // End If UserArray

$con->close();
if ($send_email) {
    if ($send_email_to) {
        $email = $send_email_to;
    }
    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
    mail($email, "XRMS: Open Items", $output, $headers);
    if (($display) || ($friendly)) {
        echo $output;
    }
}
else {
    echo $output;
}

if (($display) || (!$friendly)) {
    end_page();
}

/**
 * $Log: open-items.php,v $
 * Revision 1.8  2004/06/12 05:35:58  introspectshun
 * - Add adodb-params.php include for multi-db compatibility.
 * - Corrected order of arguments to implode() function.
 *
 * Revision 1.7  2004/04/28 15:44:23  gpowers
 * added $say_no_when_none - shows "NO .. for .. " when no items present
 * added no_items for opportunities and campaigns
 * added send_email_to support on web form.
 * changed from showing username to showing user's first_names last_name
 * changed display html / display friendly / send email logic
 *
 * Revision 1.6  2004/04/23 15:24:49  gpowers
 * Fixes Bug #938616, requires campaign_statuses.status_open_indicator
 *
 * Revision 1.5  2004/04/22 22:30:56  gpowers
 * fixed duplicate lines in report
 *
 * Revision 1.3  2004/04/20 19:37:28  braverock
 * - cleaned up sql formatting to handle more cases and be less error prone
 *   - partially fixes SF bugs 938616 & 938620
 *
 * Revision 1.2  2004/04/20 15:02:44  braverock
 * - removed hard coding of open status indicators for cases and opportunities
 *   - partially fixes SF bug 938616
 *
 * Revision 1.1  2004/04/20 13:34:42  braverock
 * - add activity times report
 * - add open items report
 * - add completed items report
 *   - apply SF patches 928336, 937994, 938094 submitted by Glenn Powers
 *
 */
?>
