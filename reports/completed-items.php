<?php
/**
 * Create a graph of activity for the requested user.
 *
 * @author Glenn Powers
 *
 * $Id: completed-items.php,v 1.18 2006/01/02 23:46:52 vanmer Exp $
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
$starting = $_GET['starting'];
$ending = $_GET['ending'];
$user_id = $_GET['user_id'];
$type = $_GET['type'];
$friendly = $_GET['friendly'];
$send_email = $_GET['send_email'];
$send_email_to = $_GET['send_email_to'];
$all_users = $_GET['all_users'];
$display = $_GET['display'];

$userArray = array();

if (!$starting) {
    $starting = date("Y-m-d") . " 00:00:00";
    }

if (!$ending) {
    $ending = date("Y-m-d") . " 23:59:59";
    }

if ($friendly) {
    $display = "";
    }

$page_title = _("Completed Items");
if (($display) || (!$friendly)) {
    start_page($page_title, true, $msg);
}

$con = get_xrms_dbconnection();
// $con->debug = 1;

$use_hr = get_system_parameter($con, 'Reports--Use Horizontal Rule');
$say_no_when_none = get_system_parameter($con, 'Reports--Show No Items Found');

$user_menu = get_user_menu($con);
?>

<?php
if (($display) || (!$friendly)) {
    echo "
<form action=completed-items.php method=get>
<table>
    <input type=hidden name=display value=y>
    <tr>
        <th align=left>" . _("Start") . "</th>
        <th align=left>" . _("End") . "</th>
        <th align=left>" . _("User") . "</th>
        <th align=left>" . _("Type") . "</th>
        <th align=left></th>
    </tr>
        <tr>
            <td><input type=text name=starting value=\"" . $starting . "\"></td>
            <td><input type=text name=ending value=\"" . $ending . "\"></td>
            <td>" . $user_menu . "</td>
            <td>
                <select name=type>
                    <option value=all";

    if ($type == "all") {
        echo " selected ";
    }

    echo ">" . _("All") . "</option>
                    <option value=activities";

    if ($type == "activities") {
        echo " selected ";
    }

    echo ">" . _("Activities") . "</option>
                    <option value=campaigns";

    if ($type == "campaigns") {
        echo " selected ";
    }

    echo ">" . _("Campaigns") . "</option>
                    <option value=opportunities";

    if ($type == "opportunities") {
        echo " selected ";
    }

    echo ">" . _("Opportunities") . "</option>
                    <option value=cases";

    if ($type == "cases") {
        echo " selected ";
    }

    echo ">" . _("Cases") . "</option>
                </select></td>
            <td>
                <input class=button type=submit value=" . _("Go") . ">
            </td>
        </tr>
        <tr>

            <td align=right>
                <input name=send_email type=checkbox ";

    if ($send_email) {
        echo "checked";
    }

    echo ">" . _("Send Email To") . ": 
            </td>
            <td>
                <input name=send_email_to type=text value=" . $send_email_to . ">
            </td>
            <td>
                <input name=all_users type=checkbox ";

    if ($all_users) {
        echo "checked";
    }

    echo ">" . _("All Users") . "
            </td>
            <td>
                <input name=friendly type=checkbox ";

    if ($friendly) {
        echo "checked";
    }

    echo ">" . _("Printer Friendly") . "
            </td>
            </td>
            <td>
            </td>
        </tr>
    </table>
</form>
    <p>&nbsp;</p>".'<div id="report">';
}
?>

<?php

if (($user_id) && (!$all_users)) {
    $userArray = array($user_id);
}

if ($all_users) {
    $sql = "select user_id from users where user_record_status = 'a' order by last_name, first_names";
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
        $sql = "SELECT * from activities where activity_status = 'c' and activity_record_status = 'a' and user_id = $user_id and entered_at between " . $con->qstr($starting, get_magic_quotes_gpc()) . " and " . $con->qstr($ending, get_magic_quotes_gpc()) . "order by entered_at ";
        $rst = $con->execute($sql);

        if (!$rst->EOF) {
            $output .= "<p><font size=+2><b>" . _("COMPLETED ACTIVITIES for") . " $name</b></font><br></p>\n";
            $output .= "<table>";
            $output .= "<tr><td colspan=6><hr></td></tr>\n";
            $output .= "    <tr>";
            $output .= "        <th align=left>" . _("Start") . "</th>";
            $output .= "        <th align=left>" . _("End") . "</th>";
            $output .= "        <th align=left>" . _("Type") . "</th>";
            $output .= "        <th align=left>" . _("Company") . "</th>";
            $output .= "        <th align=left>" . _("Contact") . "</th>";
            $output .= "        <th align=left>" . _("Activity") . "</th>";
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
                $output .= "<td><a href=\"" . $http_site_root . "/activities/one.php?activity_id=" . $rst->fields['activity_id'] . "\">" . $rst->fields['activity_title'] . "</a></td>\n</td>\n";
                if ($use_hr =='y') {
                    $output .= "<tr><td colspan=7><hr></td></tr>\n";
                }
                $rst->movenext();
            }
        $rst->close();
        $output .= "</table>";
        }
    else {
        if ($say_no_when_none =='y') {
            $output .= "<p><b>" . _("NO COMPLETED ACTIVITIES for") . " $name</b><br></p>\n";
        }
    }
    } // End Activities
    if (($type == "campaigns") || ($type == "all")) {
         $sql = "SELECT * from campaign_statuses, campaigns where
                campaign_statuses.campaign_status_id = campaigns.campaign_status_id
                and campaign_statuses.status_open_indicator = 'o'
                and campaign_record_status = 'a'
                and user_id = $user_id and ends_at between "
                . $con->qstr($starting, get_magic_quotes_gpc()) . "
                and " . $con->qstr($ending, get_magic_quotes_gpc()) . "
                order by entered_at ";
        $rst = $con->execute($sql);
        if (!$rst->EOF) {
            $output .= "<p><font size=+2><b>" . _("COMPLETED CAMPAIGNS for") . " $name</b></font><br></p>\n";
            $output .= "<table>";
            $output .= "<tr><td colspan=4><hr></td></tr>\n";
            $output .= "    <tr>";
            $output .= "        <th align=left>" . _("Start") . "</th>";
            $output .= "        <th align=left>" . _("End") . "</th>";
            $output .= "        <th align=left>" . _("Type") . "</th>";
            $output .= "        <th align=left>" . _("Campaign") . "</th>";
            $output .= "    </tr>";
            $output .= "<tr><td colspan=4><hr></td></tr>\n";
            while (!$rst->EOF) {
                $output .= "<tr>\n<td>" . $rst->fields['starts_at'] . "&nbsp;&nbsp;&nbsp;</td>\n";
                $output .= "<td>" . $rst->fields['ends_at'] . "&nbsp;&nbsp;</td>\n";
                $sql4 = "SELECT campaign_type_pretty_name from campaign_types where campaign_type_id = " . $rst->fields['campaign_type_id'];
                $rst4 = $con->execute($sql4);
                $output .= "<td>" . $rst4->fields['campaign_type_pretty_name'] . "&nbsp;&nbsp;</td>\n";
                $output .= "<td><a href=\"" . $http_site_root . "/campaigns/one.php?campaign_id=" . $rst->fields['campaign_id'] . "\">" . $rst->fields['campaign_title'] . "</a></td>\n</td>\n";
                if ($use_hr =='y') {
                    $output .= "<tr><td colspan=4><hr></td></tr>\n";
                }
                $rst->movenext();
            }
            $rst->close();
            $output .= "</table>";
        }
    else {
        if ($say_no_when_none =='y') {
            $output .= "<p><b>" . _("NO COMPLETED CAMPAIGNS for") . " $name</b><br></p>\n";
        }
    }
    } // End Campaigns
    if (($type == "opportunities") || ($type == "all")) {
        $sql = "SELECT * from opportunities, opportunity_statuses where
                opportunity_statuses.status_open_indicator != 'o'
                and opportunity_record_status = 'a'
                and opportunity_statuses.opportunity_status_id = opportunities.opportunity_status_id
                and user_id = $user_id
                and close_at between " . $con->qstr($starting, get_magic_quotes_gpc())
              . " and " . $con->qstr($ending, get_magic_quotes_gpc()) . "
                order by entered_at";
        $rst = $con->execute($sql);
        if (!$rst->EOF) {
            $output .= "<p><font size=+2><b>" . _("COMPLETED OPPORTUNITIES for") . " $name</b></font><br></p>\n";
            $output .= "<table>";
            $output .= "<tr><td colspan=4><hr></td></tr>\n";
            $output .= "    <tr>";
            $output .= "        <th align=left>" . _("Entered") . "</th>";
            $output .= "        <th align=left>" . _("Type") . "</th>";
            $output .= "        <th align=left>" . _("Company") . "</th>";
            $output .= "        <th align=left>" . _("Contact") . "</th>";
            $output .= "        <th align=left>" . _("Opportunity") . "</th>";
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
                $output .= "<td><a href=\"" . $http_site_root . "/opportunities/one.php?opportunity_id=" . $rst->fields['opportunity_id'] . "\">" . $rst->fields['opportunity_title'] . "</a></td>\n</td>\n";
                if ($use_hr =='y') {
                    $output .= "<tr><td colspan=4><hr></td></tr>\n";
                }
                $rst->movenext();
            }
            $rst->close();
            $output .= "</table>";
        }
    else {
        if ($say_no_when_none =='y') {
            $output .= "<p><b>" . _("NO COMPLETED OPPORTUNITIES for") . " $name</b><br></p>\n";
        }
    }
    } // End Opportunities
    if (($type == "cases") || ($type == "all")) {
        $sql = "SELECT * from cases, case_statuses where
                status_open_indicator != 'o'
                and case_record_status = 'a'
                and case_statuses.case_status_id = cases.case_status_id
                and user_id = $user_id
                and entered_at between " . $con->qstr($starting, get_magic_quotes_gpc())
              . " and " . $con->qstr($ending, get_magic_quotes_gpc()) . "
              order by entered_at";
        $rst = $con->execute($sql);
        if (!$rst->EOF) {
            $output .= "<p><font size=+2><b>" . _("COMPLETED CASES for") . " $name</b></font><br></p>\n";
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
                $output .= "<td><a href=\"" . $http_site_root . "/cases/one.php?case_id=" . $rst->fields['case_id'] . "\">" . $rst->fields['case_title'] . "</a></td>\n</td>\n";
                if ($use_hr =='y') {
                    $output .= "<tr><td colspan=5><hr></td></tr>\n";
                }
                $rst->movenext();
            }
            $rst->close();
            $output .= "</table>";
        }
        else {
            if ($say_no_when_none =='y') {
                $output .= "<p><b>" . _("NO COMPLETED CASES for") . " $name</b><br></p>\n";
            }
        }
    } // End Cases Type
} // End Foreach User
} // End If UserArray
$from_email_address = get_system_parameter($con, "Sender Email Address");
$con->close();
if ($send_email) {
    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "From: " . $from_email_address . "\r\n";
    $headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
    mail($send_email_to, "$app_title: $page_title", $output, $headers);
    if (($display) || ($friendly)) {
        echo $output;
    }
}
else {
    echo $output;
}
if (($display) || (!$friendly)) {
    echo '</div>';
    end_page();
}

/**
 * $Log: completed-items.php,v $
 * Revision 1.18  2006/01/02 23:46:52  vanmer
 * - changed to use centralized dbconnection function
 *
 * Revision 1.17  2005/12/08 23:39:09  vanmer
 * - changed completed item report to correct reflect open indicator codes for case statuses
 *
 * Revision 1.16  2005/03/21 13:40:58  maulani
 * - Remove redundant code by centralizing common user menu call
 *
 * Revision 1.15  2005/02/09 19:20:54  maulani
 * - fix parameter call to occur after database connection established
 *
 * Revision 1.14  2005/02/05 16:44:19  maulani
 * - Change report options to use system parameters
 *
 * Revision 1.13  2005/01/30 12:52:02  maulani
 * - Add from email address to emailed reports
 *
 * Revision 1.12  2005/01/03 06:37:19  ebullient
 * update reports - graphs centered on page, reports surrounded by divs
 *
 * Revision 1.11  2004/09/02 23:34:26  maulani
 * - Fix syntax error
 * - Eliminate deleted users from all users report
 *
 * Revision 1.10  2004/07/20 18:36:58  introspectshun
 * - Localized strings for i18n/translation support
 *
 * Revision 1.9  2004/07/20 17:28:50  gpowers
 * - fixed links in report
 *   - relative links didn't work in email report
 *
 * Revision 1.8  2004/06/12 05:35:58  introspectshun
 * - Add adodb-params.php include for multi-db compatibility.
 * - Corrected order of arguments to implode() function.
 *
 * Revision 1.7  2004/05/06 15:55:29  maulani
 * - Fixes bug 949294 where email was misdirected
 *
 * Revision 1.6  2004/04/28 15:46:02  gpowers
 * added $say_no_when_none - shows "NO .. for .. " when no items present
 * added no_items for opportunities and campaigns
 * added send_email_to support on web form.
 * changed from showing username to showing user's first_names last_name
 * changed display html / display friendly / send email logic
 * menu selections are now persistent across page loads
 *
 * Revision 1.5  2004/04/23 15:43:17  gpowers
 * fixed select on campaigns and opportunities
 *
 * Revision 1.4  2004/04/23 15:24:10  gpowers
 * Fixes Bug #938620, requires campaign_statuses.status_open_indicator
 *
 * Revision 1.3  2004/04/22 22:34:17  gpowers
 * fixed duplicate lines in report
 *
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
