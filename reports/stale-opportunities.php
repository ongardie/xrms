<?php
/**
 * Create a graph of activity for the requested user.
 *
 * @author Glenn Powers
 *
 * $Id: stale-opportunities.php,v 1.1 2006/01/28 22:22:40 niclowe Exp $
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
$send_email = $_GET['send_email'];
$send_email_to = $_GET['send_email_to'];
$all_users = $_GET['all_users'];
$display = $_GET['display'];

$use_hr = 1; // comment this out to remove <hr>'s from between lines
$say_no_when_none = 1; // display "NO (CASES|ACTIVITIES|CAMPAIGNS|OPPORTUNITES} for First_Names Last_Name"

$userArray = array();
$starting=date("Y-m-d H:i:s");
if (!$starting) {
    $starting = "1970-01-01";
    }

if (!$ending) {
    $ending = "30 Days Ago";
    // $ending = date("Y-m-d H:i:s", mktime());
    }

if (!$today) {
    $user_today = date("Y-m-d H:i:s", mktime());
    }


if ($friendly) {
    $display = "";
    }

$page_title = "Stale Opportunities";
if (($display) || (!$friendly)) {
    start_page($page_title, true, $msg);
}

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
//$con->debug = 1;

$sql = "select username, user_id from users where user_record_status = 'a' order by username";
$rst = $con->execute($sql);
$user_menu = $rst->getmenu2('user_id', '', false);
$user_starting = $rst->usertimestamp( date("Y-m-d H:i:s", strtotime($starting)));
$user_ending = $rst->usertimestamp( date("Y-m-d H:i:s", strtotime($ending)));
$rst->close();
?>

<?php
if (($display) || (!$friendly)) {
    echo "
<p>&nbsp;</p>
<table>
    <form action=stale-opportunities.php method=get>
    <input type=hidden name=display value=y>
    <tr>
        <th align=left>Start</th>
        <th align=left>End</th>
        <th align=left>User</th>
        <th align=left></th>
    </tr>
        <tr>
            <td><input type=text name=starting value=\"" . $starting . "\"></td>
            <td><input type=text name=ending value=\"" . $ending . "\"></td>
            <td>" . $user_menu . "</td>
            <td>
                <input class=button type=submit value=Go>
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
        </tr>
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

//mark
        $sql = "SELECT * from opportunities, opportunity_statuses, activities where
                opportunity_statuses.status_open_indicator = 'o'
                and opportunity_record_status = 'a'
                and opportunity_statuses.opportunity_status_id = opportunities.opportunity_status_id
                and opportunities.user_id = $user_id
                and opportunities.contact_id = activities.contact_id
                and activities.user_id = $user_id
                and opportunities.entered_at between " . $con->qstr($user_starting, get_magic_quotes_gpc())
              . " and " . $con->qstr($user_ending, get_magic_quotes_gpc()) . "
                order by opportunities.entered_at";
        $rst = $con->execute($sql);
        if (!$rst->EOF) {
            $output .= "<p><font size=+2><b>STALE OPPORTUNITIES for $name<b></font><br></p>\n";
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
      $sql = "SELECT * from activities where
                activity_record_status = 'a'
                and contact_id = " . $rst->fields['contact_id'] . "
                and entered_at not between " . $con->qstr($user_ending, get_magic_quotes_gpc())
              . " and " . $con->qstr($user_today, get_magic_quotes_gpc()) . "
                order by entered_at";
        $rst2 = $con->execute($sql);
          if (!$rst2->EOF) {
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
                $output .= "<td><a href=\"../opportunities/one.php?opportunity_id=" . $rst->fields['opportunity_id'] . "\">" . $rst->fields['opportunity_title'] . "</a></td>


                <td>" . $rst->fields['activity_name'] . "</td>\n";
             }

                if ($use_hr) {
                    $output .= "<tr><td colspan=4><hr></td></tr>\n";
                }
                $rst->movenext();
            }
            $rst->close();
            $output .= "</table>";
        }
} // End Foreach User
} // End If UserArray

$con->close();
if ($send_email) {
    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
    mail($email, "$app_title: $page_title", $output, $headers);
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
 * $Log: stale-opportunities.php,v $
 * Revision 1.1  2006/01/28 22:22:40  niclowe
 * First Commit of new reports - Stale Companies and Opportunities
 *
 */
?>