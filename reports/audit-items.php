<?php
/**
 * Create a graph of activity for the requested user.
 *
 * @author Glenn Powers
 *
 * $Id: audit-items.php,v 1.2 2004/04/23 17:14:14 gpowers Exp $
 */
require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb/adodb-pager.inc.php');

$session_user_id = session_check();
$msg = $_GET['msg'];
$starting = $_GET['starting'];
$ending = $_GET['ending'];
$user_id = $_GET['user_id'];
$connection_details = $_GET['connection_details'];
$all_dates = $_GET['all_dates'];
$all_users = $_GET['all_users'];

$page_title = "Audit Log";
start_page($page_title, true, $msg);

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
// $con->debug = 1;

$sql = "select username, user_id from users where user_record_status = 'a' order by username";
$rst = $con->execute($sql);
$user_menu = $rst->getmenu2('user_id', '', false);
$rst->close();
?>

<form action="audit-items.php" method=get>
<table>
    <tr>
        <th>Start</th>
        <th>End</th>
        <th>User</th>
        <th></th>
    </tr>
        <tr>
            <td><input type=text name=starting value="<?php  echo date("Y-m-d"); ?>"></td>
            <td><input type=text name=ending value="<?php  echo date("Y-m-d"); ?>"></td>
            <td><?php echo $user_menu; ?></td>
            <td></td>
        </tr>
        <tr>
            <td><input type=checkbox name=all_dates
                <?php if ($all_dates) { echo "checked"; } ?>>All Dates</td>
            <td><input type=checkbox name=connection_details
                <?php if ($connection_details) { echo "checked"; } ?>>Connection Details</td>
            <td><!-- <input type=checkbox name=all_users>All Users --></td>
            <td><input class=button type=submit value="Go"></td>
        </tr>
</table>
</form>

<p>&nbsp;</p>

<?php
if ($user_id) {

    $sql = "select username from users where user_id = $user_id";
    $rst = $con->execute($sql);
    $username = $rst->fields['username'];
    $rst->close();

    $start_date = "$starting 00:00:00";
    $end_date =  "$ending 23:23:59";

    if (!$all_dates) {
        $sql_date = " and audit_item_timestamp between " . $con->qstr($start_date, get_magic_quotes_gpc()) . "
             and " . $con->qstr($end_date, get_magic_quotes_gpc());
    }

    $sql = "SELECT * from audit_items where
             audit_item_record_status = 'a'
             and user_id = $user_id " . $sql_date . "
             order by audit_item_timestamp ";
    $rst = $con->execute($sql);

    if ($rst) {
        echo "<table>";
        echo "    <tr>";
        echo "        <th align=left>Line</th>";
        echo "        <th align=left>Time</th>";
        echo "        <th align=left>User</th>";
        echo "        <th align=left>Type</th>";
        echo "        <th align=left>Table</th>";
        echo "        <th align=left>Name</th>";
        echo "    </tr>";
        if ($connection_details) {
            echo "    <tr>";
            echo "        <th align=left>Remote Addr</th>";
            echo "        <th align=left>Remote Port</th>";
            echo "        <th align=left colspan=2>Session Id</th>";
            echo "        <th align=left colspan=2>HTTP User Agent</th>";
            echo "    </tr>";
            echo "    <tr><td colspan=6><hr></td></tr>";
        }
        while (!$rst->EOF) {
            echo "<tr>\n";
            echo "<td>" . $rst->fields['audit_item_id'] . "&nbsp;&nbsp;</td>\n";
            echo "<td>" . $rst->fields['audit_item_timestamp'] . "&nbsp;&nbsp;&nbsp;</td>\n";
            $sql4 = "SELECT last_name, first_names from users where user_id = " . $rst->fields['user_id'];
            $rst4 = $con->execute($sql4);
            echo "<td>" . $rst4->fields['last_name'] . ", " . $rst4->fields['first_names'] . "&nbsp;&nbsp;&nbsp;</td>\n";
            echo "<td><b>" . $rst->fields['audit_item_type'] . "</b>&nbsp;&nbsp;&nbsp;</td>\n";
            echo "<td>" . $rst->fields['on_what_table'] . "&nbsp;&nbsp;&nbsp;</td>\n";
            if ($rst->fields['on_what_table'] == "companies") {
                $sql2 = "SELECT company_name from companies where company_id=" . $rst->fields['on_what_id'];
                $rst2 = $con->execute($sql2);
                echo "<td><a href=$http_site_root/companies/one.php?company_id=" .
                     $rst->fields['on_what_id'] .
                     ">" . $rst2->fields['company_name'] .
                     "</a>&nbsp;&nbsp;&nbsp;</td>\n";
            }
            elseif ($rst->fields['on_what_table'] == "contacts") {
                $sql2 = "SELECT last_name, first_names from contacts where contact_id=" . $rst->fields['on_what_id'];
                $rst2 = $con->execute($sql2);
                echo "<td><a href=$http_site_root/contacts/one.php?contact_id=" .
                     $rst->fields['on_what_id'] .
                     ">" . $rst2->fields['last_name'] . ", " .
                     $rst2->fields['last_name'] . "</a>&nbsp;&nbsp;&nbsp;</td>\n";
            }
            elseif ($rst->fields['on_what_table'] == "activities") {
                $sql2 = "SELECT  activity_title from activities where activity_id=" . $rst->fields['on_what_id'];
                $rst2 = $con->execute($sql2);
                echo "<td><a href=$http_site_root/activities/one.php?activity_id=" .
                     $rst->fields['on_what_id'] .
                     ">" . substr($rst2->fields['activity_title'], 0, 40) .
                     "</a>&nbsp;&nbsp;&nbsp;</td>\n";
            }
            elseif ($rst->fields['on_what_table'] == "notes") {
                $sql2 = "SELECT note_description from notes where note_id=" . $rst->fields['on_what_id'];
                $rst2 = $con->execute($sql2);
                echo "<td><a href=$http_site_root/notes/edit.php?note_id=" .
                     $rst->fields['on_what_id'] .
                     ">" . substr($rst2->fields['note_description'], 0, 40) .
                     "</a>&nbsp;&nbsp;&nbsp;</td>\n";
            }
            else {
                echo "<td>" . $rst->fields['on_what_id'] . "&nbsp;&nbsp;&nbsp;</td>\n";
            }
            echo "<tr>\n";
            if ($connection_details) {
                echo "<tr>\n";
                echo "<td><a href=http://ws.arin.net/cgi-bin/whois.pl?queryinput=" .
                     $rst->fields['remote_addr'] . ">" . $rst->fields['remote_addr'] . "</a>" . 
                     "&nbsp;&nbsp;&nbsp;</td>\n";
                echo "<td>" . $rst->fields['remote_port'] . "&nbsp;&nbsp;&nbsp;</td>\n";
                echo "<td colspan=4>" . $rst->fields['session_id'] . "&nbsp;&nbsp;&nbsp;</td>\n";
                echo "<tr>\n";
                echo "<tr><td colspan=6><hr></td></tr>\n";
            }
            $rst->movenext();
        }
        $rst->close();
        echo "</table>";
    }
    else {
        echo "No Activity.";
    }
    add_audit_item($con, $session_user_id, 'read', 'audit_items', '');
    $con->close();
}

end_page();

/**
 * $Log: audit-items.php,v $
 * Revision 1.2  2004/04/23 17:14:14  gpowers
 * Removed http_user_agent from audit_items table. It is space consuming and
 * redundant, as most httpd servers can be configured to log this information.
 *
 * Revision 1.1  2004/04/22 17:05:40  gpowers
 * Added Audit Item Report
 *
 * Revision 1.0  2004/04/21 15:00:00  gpowers
 *
 */
?>
