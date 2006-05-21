<?php
/**
 * Create a graph of activity for the requested user.
 *
 * @author Glenn Powers
 *
 * $Id: audit-items.php,v 1.12 2006/05/21 14:42:38 jnhayart Exp $
 */
require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb/adodb-pager.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();
$msg = $_GET['msg'];
$starting = $_GET['starting'];
$ending = $_GET['ending'];
$user_id = $_GET['user_id'];
$connection_details = $_GET['connection_details'];
$all_dates = $_GET['all_dates'];
$all_users = $_GET['all_users'];

if(!isset($user_id)) $user_id=$session_user_id;

$page_title = _("Audit Log");
start_page($page_title, true, $msg);

$con = get_xrms_dbconnection();
// $con->debug = 1;

$user_menu = get_user_menu($con, $user_id);
?>
<form action="audit-items.php" method=get>
<table>
    <tr>
        <th><?php echo _("Start"); ?></th>
        <th><?php echo _("End"); ?></th>
        <th><?php echo _("User"); ?></th>
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
                <?php if ($all_dates) { echo "checked"; } ?>><?php echo _("All Dates"); ?></td>
            <td><input type=checkbox name=connection_details
                <?php if ($connection_details) { echo "checked"; } ?>><?php echo _("Connection Details"); ?></td>
            <td><!-- <input type=checkbox name=all_users>All Users --></td>
            <td><input class=button type=submit value="<?php echo _("Go"); ?>"></td>
        </tr>
</table>
</form>

<p>&nbsp;</p>
<div id="report">

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
        echo "        <th align=left>" . _("Line") . "</th>";
        echo "        <th align=left>" . _("Time") . "</th>";
        echo "        <th align=left>" . _("User") . "</th>";
        echo "        <th align=left>" . _("Type") . "</th>";
        echo "        <th align=left>" . _("Table") . "</th>";
        echo "        <th align=left>" . _("Name") . "</th>";
        echo "    </tr>";
        if ($connection_details) {
            echo "    <tr>";
            echo "        <th align=left></th>";
            echo "        <th align=left>" . _("Remote Addr") . "</th>";
            echo "        <th align=left>" . _("Remote Port") . "</th>";
            echo "        <th align=left colspan=3>" . _("Session Id") . "</th>";
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
                     $rst2->fields['first_names'] . "</a>&nbsp;&nbsp;&nbsp;</td>\n";
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
            elseif ($rst->fields['on_what_table'] == "opportunities") {
                $sql2 = "SELECT opportunity_title from opportunities where opportunity_id=" . $rst->fields['on_what_id'];
                $rst2 = $con->execute($sql2);
                echo "<td><a href=$http_site_root/opportunities/one.php?opportunity_id=" .
                     $rst->fields['on_what_id'] .
                     ">" . $rst2->fields['opportunity_title'] . "</a>&nbsp;&nbsp;&nbsp;</td>\n";
            }
            else {
                echo "<td>" . $rst->fields['on_what_id'] . "&nbsp;&nbsp;&nbsp;</td>\n";
            }
            echo "<tr>\n";
            if ($connection_details) {
                echo "<tr>\n";
                echo "<td></td>\n";
                echo "<td><a href=http://ws.arin.net/cgi-bin/whois.pl?queryinput=" .
                     $rst->fields['remote_addr'] . ">" . $rst->fields['remote_addr'] . "</a>" . 
                     "&nbsp;&nbsp;&nbsp;</td>\n";
                echo "<td>" . $rst->fields['remote_port'] . "&nbsp;&nbsp;&nbsp;</td>\n";
                echo "<td colspan=3>" . $rst->fields['session_id'] . "&nbsp;&nbsp;&nbsp;</td>\n";
                echo "</tr>\n";
                echo "<tr><td colspan=6><hr></td></tr>\n";
            }
            $rst->movenext();
        }
        $rst->close();
        echo "</table>";
    }
    else {
        echo _("No Activity.");
    }
    add_audit_item($con, $session_user_id, 'read', 'audit_items', '', 1);
    $con->close();
}

echo '</div>';
end_page();

/**
 * $Log: audit-items.php,v $
 * Revision 1.12  2006/05/21 14:42:38  jnhayart
 * Add title and hyperlink when audit items are opportunities
 *
 * Revision 1.11  2006/01/02 23:46:52  vanmer
 * - changed to use centralized dbconnection function
 *
 * Revision 1.10  2005/03/21 13:40:58  maulani
 * - Remove redundant code by centralizing common user menu call
 *
 * Revision 1.9  2005/02/10 21:49:14  maulani
 * - Default to current user instead of first alphabetically
 *
 * Revision 1.8  2005/01/03 06:37:19  ebullient
 * update reports - graphs centered on page, reports surrounded by divs
 *
 * Revision 1.7  2004/07/20 18:36:58  introspectshun
 * - Localized strings for i18n/translation support
 *
 * Revision 1.6  2004/06/12 05:35:58  introspectshun
 * - Add adodb-params.php include for multi-db compatibility.
 * - Corrected order of arguments to implode() function.
 *
 * Revision 1.5  2004/05/10 13:07:22  maulani
 * - Add level to audit trail
 * - Clean up audit trail text
 *
 * Revision 1.4  2004/05/07 15:12:38  maulani
 * - Fix bug 949440 Audit Items report showing contact entries with the last
 *   name repeated instead of last name, first name.
 *
 * Revision 1.3  2004/04/23 17:29:17  gpowers
 * Sorry that this is the third commit in a row. I noticed a <tr> that should
 * have been a </tr> in the last xrms-cvs list message. I also removed
 * http_user_agent from the header (oops) and slightly changed the format
 * of the conneciton details output (indented line).
 *
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
