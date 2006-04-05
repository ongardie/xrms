<?php
/**
 * Index for reports.
 *
 * $Id: index.php,v 1.25 2006/04/05 01:21:28 vanmer Exp $
 */
require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();

$msg = isset($_GET['msg']) ? $_GET['msg'] : '';

$con = get_xrms_dbconnection();

$user_menu = get_user_menu($con);

$con->close();

$page_title = _("Reports");
start_page($page_title, true, $msg);

?>

<div id="Main">
    <div id="ContentFullWidth">

        <table class=widget cellspacing=1 width="100%">
            <col id="report" width="30%"><col id="description">
            <tr>
                <td colspan=2 class=widget_header><?php echo _("Graphs"); ?></td>
            </tr>
            <tr>
                <td colspan=2 class=widget_label_center><?php echo _("Company"); ?></td>
            </tr>
            <tr>
                <td class=widget_content><a href="companies-by-crm-status.php"><?php echo _("Companies by CRM Status"); ?></a></td>
                <td class=widget_content><?php echo _("Your sales funnel - how many of your accounts are in each stage of the customer development process?"); ?></td>
            </tr>
            <tr>
                <td class=widget_content><a href="companies-by-industry.php"><?php echo _("Companies by Industry"); ?></a></td>
                <td class=widget_content><?php echo _("How many companies are in each industry?"); ?></td>
            </tr>
            <tr>
                <td class=widget_content><a href="companies-by-company-source.php"><?php echo _("Companies by Source"); ?></a></td>
                <td class=widget_content><?php echo _("How many of your accounts come from each source?"); ?></td>
            </tr>
            <tr>
                <td colspan=2 class=widget_label_center><?php echo _("Opportunity"); ?></td>
            </tr>
            <tr>
                <td class=widget_content>
                    <a href="opportunities-quantity-by-opportunity-status.php"><?php echo _("Quantity by Status"); ?></a>
                </td>
                <td class=widget_content><?php echo _("How many opportunities are in each stage of the sales closing process?"); ?></td>
            </tr>
            <tr>
                <td class=widget_content><a href="opportunities-size-by-opportunity-status.php"><?php echo _("Size by Status"); ?></a></td>
                <td class=widget_content><?php echo _("How much potential revenue is in each stage of the sales closing process?"); ?></td>
            </tr>
            <tr>
                <td class=widget_content><a href="opportunities-quantity-by-industry.php"><?php echo _("Quantity by Industry"); ?></a></td>
                <td class=widget_content><?php echo _("How many opportunities are tied to companies in each industry?"); ?></td>
            </tr>
            <tr>
                <td class=widget_content><a href="opportunities-size-by-industry.php"><?php echo _("Size by Industry"); ?></a></td>
                <td class=widget_content><?php echo _("How much potential revenue is tied to companies in each industry?"); ?></td>
            </tr>
            <tr>
                <td colspan=2 class=widget_label_center><?php echo _("Case"); ?></td>
            </tr>
            <tr>
                <td class=widget_content><a href="cases-by-case-status.php"><?php echo _("Cases by Status"); ?></a></td>
                <td class=widget_content><?php echo _("How many cases are in each stage of the case resolution process?"); ?></td>
            </tr>
        </table>

        
    <table class=widget cellspacing=1 width="100%">
      <col id="report" width="30%"><col id="description"> 
      <tr> 
        <td colspan=2 class=widget_header> 
          <?php echo _("Reports"); ?>
        </td>
      </tr>
      <tr> 
        <td colspan=2 class=widget_label_center> 
          <?php echo _("Company Reports"); ?>
        </td>
      </tr>
      <tr> 
        <td class=widget_content><a href="companies-list.php"> 
          <?php echo _("Company List"); ?>
          </a></td>
        <td class=widget_content> 
          <?php echo _("List of companies, addresses and phone numbers"); ?>
        </td>
      </tr>
      <tr> 
        <td class=widget_content><a href="company-contacts-printout.php"> 
          <?php echo _("Contacts at Companies"); ?>
          </a></td>
        <td class=widget_content> 
          <?php echo _("Printable contact summary information for Companies in search Results."); ?>
        </td>
      </tr>
      <tr>
        <td class=widget_content><a href="activity-summary.php">
          <?php echo _("Activity Summary"); ?>
          </a></td>
        <td class=widget_content>
           <?php echo _("Summarizes the count of activities by type, for companies.  Can be restricted by user, category and
 dates"); ?>
        </td>
      </tr>
      <tr> 
        <td colspan=2 class=widget_label_center> 
          <?php echo _("User Reports"); ?>
        </td>
      </tr>
      <tr> 
        <td class=widget_content><a href="activitytimes.php"> 
          <?php echo _("Activity Time Sheets"); ?>
          </a></td>
        <td class=widget_content> 
          <?php echo _("List Activiites by Start, End, and User (also shows Duration, Company and Contact)"); ?>
        </td>
      </tr>
      <tr> 
        <td class=widget_content><a href="overdue-items.php"> 
          <?php echo _("Overdue Items"); ?>
          </a></td>
        <td class=widget_content> 
          <?php echo _("List Overdue Items by User and Type"); ?>
        </td>
      </tr>
      <tr> 
        <td class=widget_content><a href="open-items.php"> 
          <?php echo _("Open Items"); ?>
          </a></td>
        <td class=widget_content> 
          <?php echo _("List Open Items by User and Type"); ?>
        </td>
      </tr>
      <tr> 
        <td class=widget_content><a href="completed-items.php"> 
          <?php echo _("Completed Items"); ?>
          </a></td>
        <td class=widget_content> 
          <?php echo _("List Completed Items by Date Range, User and Type"); ?>
        </td>
      </tr>
      <tr> 
        <td class=widget_content><a href="stale-opportunities.php">
          <?php echo _("Stale Opportunities"); ?>
          </a></td>
        <td class=widget_content>
          <?php echo _("Stale opportunities by Last Activity Date and User"); ?>
        </td>
      </tr>
      <tr> 
        <td class=widget_content><a href="stale-crm-status.php">
          <?php echo _("Stale Companies"); ?>
          </a></td>
        <td class=widget_content>
          <?php echo _("Stale Companies - by Crms Status, Last Activity Date and User"); ?>
        </td>
      </tr>
      <tr> 
        <td class=widget_content><a href="audit-items.php"> 
          <?php echo _("Audit Items"); ?>
          </a></td>
        <td class=widget_content> 
          <?php echo _("List Audit Items by Date and User"); ?>
        </td>
      </tr>
      <!-- <tr>
                <td class=widget_content><a href="sales-automation.php"><?php echo _("Sales Force Automation"); ?></a></td>
                <td class=widget_content><?php echo _("Measure performance of users over a selectable timeframe"); ?></td>
            </tr> -->
      <tr> 
        <td class=widget_content colspan="2"> 
          <form action="user-activity.php" method=post>
            <?php echo _("Activity Report for"); ?>
            <?php echo $user_menu; ?>
            <input class=button type=submit value="<?php echo _("Go"); ?>">
          </form>
        </td>
      </tr>
      <tr> 
        <td colspan=2 class=widget_label_center> 
          <?php echo _("Custom Reports"); ?>
        </td>
      </tr>
      <?php
                // allow plugins to insert thier own reports on the main reports page
                do_hook ('reports_bottom');
                // eventually, this will need to be one hook per report section, as more reports are created
            ?>
    </table>

    </div>

</div>

<?php

end_page();

/**
 * $Log: index.php,v $
 * Revision 1.25  2006/04/05 01:21:28  vanmer
 * - added link to new activity summary report
 *
 * Revision 1.24  2006/01/28 22:22:40  niclowe
 * First Commit of new reports - Stale Companies and Opportunities
 *
 * Revision 1.23  2006/01/02 23:46:52  vanmer
 * - changed to use centralized dbconnection function
 *
 * Revision 1.22  2005/03/21 13:40:58  maulani
 * - Remove redundant code by centralizing common user menu call
 *
 * Revision 1.21  2005/02/10 03:52:47  braverock
 * - remove sales-automation link until we can rewrite the dashboard report
 *
 * Revision 1.20  2005/01/03 04:36:10  ebullient
 * make reports/index.php use full width
 *
 * Revision 1.19  2004/07/25 19:31:11  johnfawcett
 * - corrected gettext string
 *
 * Revision 1.18  2004/07/21 11:59:29  cpsource
 * - Define $msg from GET
 *
 * Revision 1.17  2004/07/20 18:36:58  introspectshun
 * - Localized strings for i18n/translation support
 *
 * Revision 1.16  2004/07/17 17:52:45  braverock
 * - Add Companies List report contributed by John Fawcett
 *
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
