<?php
/**
 * Index for reports.
 *
 * $Id: index.php,v 1.15 2004/07/15 17:30:01 introspectshun Exp $
 */
require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

$sql2 = "select username, user_id from users where user_record_status = 'a' order by username";
$rst = $con->execute($sql2);
$user_menu = $rst->getmenu2('user_id', '', false);
$rst->close();

$con->close();

$page_title = 'Reports';
start_page($page_title, true, $msg);

?>

<div id="Main">
    <div id="Content">

        <table class=widget cellspacing=1 width="100%">
            <tr>
                <td colspan=2 class=widget_header>Graphs</td>
            </tr>
            <tr>
                <td colspan=2 class=widget_label_center>Company</td>
            </tr>
            <tr>
                <td class=widget_content><a href="companies-by-crm-status.php">Companies by CRM Status</a></td>
                <td class=widget_content> Your sales funnel - how many of your accounts are in each stage of the customer development process?</td>
            </tr>
            <tr>
                <td class=widget_content><a href="companies-by-industry.php">Companies by Industry</a></td>
                <td class=widget_content>How many companies are in each industry?</td>
            </tr>
            <tr>
                <td class=widget_content><a href="companies-by-company-source.php">Companies by Source</a></td>
                <td class=widget_content>How many of your accounts come from each source?</td>
            </tr>
            <tr>
                <td colspan=2 class=widget_label_center>Opportunity</td>
            </tr>
            <tr>
                <td class=widget_content>
                    <a href="opportunities-quantity-by-opportunity-status.php">Quantity by Status</a>
                </td>
                <td class=widget_content>How many opportunities are in each stage of the sales closing process?</td>
            </tr>
            <tr>
                <td class=widget_content><a href="opportunities-size-by-opportunity-status.php">Size by Status</a></td>
                <td class=widget_content>How much potential revenue is in each stage of the sales closing process?</td>
            </tr>
            <tr>
                <td class=widget_content><a href="opportunities-quantity-by-industry.php">Quantity by Industry</a></td>
                <td class=widget_content>How many opportunities are tied to companies in each industry?</td>
            </tr>
            <tr>
                <td class=widget_content><a href="opportunities-size-by-industry.php">Size by Industry</a></td>
                <td class=widget_content>How much potential revenue is tied to companies in each industry?</td>
            </tr>
            <tr>
                <td colspan=2 class=widget_label_center>Case</td>
            </tr>
            <tr>
                <td class=widget_content><a href="cases-by-case-status.php">Cases by Status</a></td>
                <td class=widget_content>How many cases are in each stage of the case resolution process?</td>
            </tr>
        </table>

        <table class=widget cellspacing=1 width="100%">
            <tr>
                <td colspan=2 class=widget_header>Reports</td>
            </tr>
            <tr>
                <td colspan=2 class=widget_label_center>Opportunity</td>
            </tr>
            <tr>
                <td class=widget_content><a href="company-contacts-printout.php">Contacts at Companies</a></td>
                <td class=widget_content>Printable contact summary information for Companies in search Results.</td>
            </tr>
            <tr>
                <td colspan=2 class=widget_label_center>User Reports</td>
            </tr>
            <tr>
                <td class=widget_content><a href="activitytimes.php">Activity Time Sheets</a></td>
                <td class=widget_content>
                    List Activiites by Start, End, and User (also shows Duration, Company and Contact)
                </td>
            </tr>
            <tr>
                <td class=widget_content><a href="overdue-items.php">Overdue Items</a></td>
                <td class=widget_content>List Overdue Items by User and Type</td>
            </tr>
            <tr>
                <td class=widget_content><a href="open-items.php">Open Items</a></td>
                <td class=widget_content>List Open Items by User and Type</td>
            </tr>
            <tr>
                <td class=widget_content><a href="completed-items.php">Completed Items</a></td>
                <td class=widget_content>List Completed Items by Date Range, User and Type</td>
            </tr>
            <tr>
                <td class=widget_content><a href="audit-items.php">Audit Items</a></td>
                <td class=widget_content>List Audit Items by Date and User</td>
            </tr>
            <tr>
                <td class=widget_content><a href="sales-automation.php">Sales Force Automation</a></td>
                <td class=widget_content>Measure performance of users over a selectable timeframe</td>
            </tr>
            <tr>
                <td class=widget_content colspan="2">
                    <form action="user-activity.php" method=post>
                        Activity Report for <?php echo $user_menu; ?>
                        <input class=button type=submit value="Go">
                    </form>
                </td>
            </tr>
            <tr>
                <td colspan=2 class=widget_label_center>Custom Reports</td>
            </tr>
            <?php
                // allow plugins to insert thier own reports on the main reports page
                do_hook ('reports_bottom');
                // eventually, this will need to be one hook per report section, as more reports are created
            ?>
        </table>

    </div>

    <!-- right column //-->
    <div id="Sidebar">


    </div>

</div>

<?php

end_page();

/**
 * $Log: index.php,v $
 * Revision 1.15  2004/07/15 17:30:01  introspectshun
 * - Fixed errant CVS Commit.
 *
 * Revision 1.14  2004/07/14 02:09:43  s-t
 * cvs commit index.php
 *
 * Revision 1.13  2004/06/21 15:40:02  gpowers
 * - added "overdue items" report
 *
 * Revision 1.12  2004/06/12 05:35:58  introspectshun
 * - Add adodb-params.php include for multi-db compatibility.
 * - Corrected order of arguments to implode() function.
 *
 * Revision 1.11  2004/06/07 16:39:53  gpowers
 * - Separated "Graphs" and "Reports"
 * - Correctly labelled graphs as "Graphs"
 *   (Users have reported being confused by the former label.)
 *
 * Revision 1.10  2004/06/05 16:03:39  braverock
 * - fixed typo in link
 *
 * Revision 1.9  2004/06/04 23:16:26  braverock
 * - add company contact summary printable report
 *
 * Revision 1.8  2004/05/09 03:56:37  braverock
 * - add plugin hook for reports
 *
 * Revision 1.7  2004/04/22 17:13:13  gpowers
 * Added Audit Items Report
 *
 * Revision 1.6  2004/04/20 13:34:42  braverock
 * - add activity times report
 * - add open items report
 * - add completed items report
 *   - apply SF patches 928336, 937994, 938094 submitted by Glenn Powers
 *
 * Revision 1.5  2004/04/15 22:04:39  maulani
 * - Change to CSS2 positioning
 * - Clean HTML to achieve validation
 *
 * Revision 1.4  2004/01/26 15:52:42  braverock
 * - fixed short tags
 * - added phpdoc
 *
 */
?>
