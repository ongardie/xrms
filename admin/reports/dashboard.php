<?php
/**
 * admin/reports/dashboard.php - Administrator's Dashboard
 *
 * Displays Audit entries and new activity counts.  Needs work.
 *
 * $Id: dashboard.php,v 1.7 2004/05/27 12:03:03 braverock Exp $
 */

require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

$session_user_id = session_check();

$msg = $_GET['msg'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
// $con->debug = 1;

$sql_audit_items = "select ai.*, u.username
from audit_items ai, users u
where ai.user_id = u.user_id
order by ai.audit_item_timestamp desc";

$rst = $con->selectlimit($sql_audit_items, $display_how_many_audit_items_on_dashboard);

if ($rst) {
    while (!$rst->EOF) {
        $audit_items .= '<tr>';
        $audit_items .= '<td class=widget_content>' . $rst->fields['username'] . '</td>';
        $audit_items .= '<td class=widget_content>' . $rst->fields['audit_item_type'] . '</td>';
        $audit_items .= '<td class=widget_content>' . $rst->usertimestamp($rst->fields['audit_item_timestamp'], 'M d h:i A') . '</td>';
        $audit_items .= '</tr>';
        $rst->movenext();
    }
    $rst->close();
}


// What is the first day of the week
$sql = 'Select DATE_SUB(CURRENT_DATE,INTERVAL (DAYOFWEEK(CURRENT_DATE)-1) DAY) AS first_day_week';
$rst = $con->execute($sql);
$first_day_week = $rst->fields['first_day_week'] . ' 00:00:00';
$rst->close();

// What is the first day of the month
$sql = 'Select DATE_SUB(CURRENT_DATE,INTERVAL (DAYOFMONTH(CURRENT_DATE)-1) DAY) AS first_day_month';
$rst = $con->execute($sql);
$first_day_month = $rst->fields['first_day_month'] . ' 00:00:00';
$rst->close();


$begin_today = $con->dbdate(date('Y-m-d 00:00:00'));
$begin_week = $con->dbdate(date($first_day_week));
$begin_month = $con->dbdate(date($first_day_month));

//get the menu of XRMS users
$sql2 = "select username, user_id from users where user_record_status = 'a' order by username";
$rst = $con->execute($sql2);
$user_menu = $rst->getmenu2('user_id', $user_id, false,true,5);
$rst->close();


// how many activities were created today
$sql = "select count(*) as activity_count from activities where activity_record_status='a' and entered_at >= " . $begin_today;
$rst = $con->execute($sql);
$activity_count_today = $rst->fields['activity_count'];
$rst->close();

// how many activities were created this week
$sql = "select count(*) as activity_count from activities where activity_record_status='a' and entered_at >= " . $begin_week;
$rst = $con->execute($sql);
$activity_count_week = $rst->fields['activity_count'];
$rst->close();

// how many activities were created this month
$sql = "select count(*) as activity_count from activities where activity_record_status='a' and entered_at >= " . $begin_month;
$rst = $con->execute($sql);
$activity_count_month = $rst->fields['activity_count'];
$rst->close();

// how many companies were created today
$sql = "select count(*) as company_count from companies where company_record_status='a' and entered_at >= " . $begin_today;
$rst = $con->execute($sql);
$company_count_today = $rst->fields['company_count'];
$rst->close();

// how many companies were created this week
$sql = "select count(*) as company_count from companies where company_record_status='a' and entered_at >= " . $begin_week;
$rst = $con->execute($sql);
$company_count_week = $rst->fields['company_count'];
$rst->close();

// how many companies were created this month
$sql = "select count(*) as company_count from companies where company_record_status='a' and entered_at >= " . $begin_month;
$rst = $con->execute($sql);
$company_count_month = $rst->fields['company_count'];
$rst->close();

// how many contacts were created today
$sql = "select count(*) as contact_count from contacts where contact_record_status='a' and entered_at >= " . $begin_today;
$rst = $con->execute($sql);
$contact_count_today = $rst->fields['contact_count'];
$rst->close();

// how many contacts were created this week
$sql = "select count(*) as contact_count from contacts where contact_record_status='a' and entered_at >= " . $begin_week;
$rst = $con->execute($sql);
$contact_count_week = $rst->fields['contact_count'];
$rst->close();

// how many contacts were created this month
$sql = "select count(*) as contact_count from contacts where contact_record_status='a' and entered_at >= " . $begin_month;
$rst = $con->execute($sql);
$contact_count_month = $rst->fields['contact_count'];
$rst->close();

// how many opportunities were created today
$sql = "select count(*) as opportunity_count from opportunities where opportunity_record_status='a' and entered_at >= " . $begin_today;
$rst = $con->execute($sql);
$opportunity_count_today = $rst->fields['opportunity_count'];
$rst->close();

// how many opportunities were created this week
$sql = "select count(*) as opportunity_count from opportunities where opportunity_record_status='a' and entered_at >= " . $begin_week;
$rst = $con->execute($sql);
$opportunity_count_week = $rst->fields['opportunity_count'];
$rst->close();

// how many opportunities were created this month
$sql = "select count(*) as opportunity_count from opportunities where opportunity_record_status='a' and entered_at >= " . $begin_month;
$rst = $con->execute($sql);
$opportunity_count_month = $rst->fields['opportunity_count'];
$rst->close();

// how much were those opportunities worth today?
$sql = "select sum(size) as opportunity_total from opportunities where opportunity_record_status='a' and entered_at >= " . $begin_today;
$rst = $con->execute($sql);
$opportunity_total_today = '$' . number_format($rst->fields['opportunity_total'], 2);
$rst->close();

// how much were those opportunities worth this week?
$sql = "select sum(size) as opportunity_total from opportunities where opportunity_record_status='a' and entered_at >= " . $begin_week;
$rst = $con->execute($sql);
$opportunity_total_week = '$' . number_format($rst->fields['opportunity_total'], 2);
$rst->close();

// how much were those opportunities worth this month?
$sql = "select sum(size) as opportunity_total from opportunities where opportunity_record_status='a' and entered_at >= " . $begin_month;
$rst = $con->execute($sql);
$opportunity_total_month = '$' . number_format($rst->fields['opportunity_total'], 2);
$rst->close();

// how many cases were created today
$sql = "select count(*) as case_count from cases where case_record_status='a' and entered_at >= " . $begin_today;
$rst = $con->execute($sql);
$case_count_today = $rst->fields['case_count'];
$rst->close();

// how many cases were created this week
$sql = "select count(*) as case_count from cases where case_record_status='a' and entered_at >= " . $begin_week;
$rst = $con->execute($sql);
$case_count_week = $rst->fields['case_count'];
$rst->close();

// how many cases were created this month
$sql = "select count(*) as case_count from cases where case_record_status='a' and entered_at >= " . $begin_month;
$rst = $con->execute($sql);
$case_count_month = $rst->fields['case_count'];
$rst->close();

$con->close();

$page_title = "Digital Dashboard";
start_page($page_title);

?>
     <script language="javascript" src="<?php  echo $http_site_root; ?>/js/calendar1.js"></script>

<div id="Main">
    <div id="Content">

            <!-- new today //-->
      <form action="reports.php" method=post name="frmReport">
        <table class=widget cellspacing=1 width=100%>
          <tr>
            <td colspan=5 class=widget_header>Reporting </td>
          </tr>
          <tr>
            <td class=widget_label> Reporting Period</td>
            <td class=widget_label>User</td>
            <td class=widget_label>Opportunity </td>
            <td class=widget_label>Activity</td>
            <td class=widget_label>Case</td>
          </tr>
          <tr>
            <td width="31%" class="widget_content_form_element">From
              <input name="period_from" type=text value="<?php echo date('Y-m-d'); ?>" size=10>
              <a href="javascript:cal1.popup();"><img class=date_picker border=0 src="../../img/cal.gif"></a>
              To
              <input name="period_to" type=text value="<?php echo date('Y-m-d'); ?>" size=10>
              <a href="javascript:cal2.popup();"><img class=date_picker border=0 src="../../img/cal.gif"></a>
            </td>
            <td width="14%" class="widget_content"> <?php echo $user_menu; ?></td>
            <td width="19%" valign="middle" bgcolor="#ffffff"><input name="opp_name" type=text disabled="disabled" size=10>
              <input name="opt_opp" type="checkbox" onClick="oppChange(this.form);" value="1"></td>
            <td width="18%" valign="middle" bgcolor="#ffffff"><input name="act_name" type=text disabled="disabled" size=10>
              <input name="opt_act" type="checkbox" onClick="actChange(this.form);" value="1"></td>
            <td width="18%" valign="middle" bgcolor="#ffffff"><input name="cases_name" type=text disabled="disabled" size=10>
              <input name="opt_case" type="checkbox" onClick="caseChange(this.form);" value="1"></td>
          </tr>
          <tr>
            <td colspan=5 class=widget_content_form_element> <input name="submit" type=submit class=button value="Search">
              <input name="button" type=button class=button onClick="javascript: clearSearchCriteria();" value="Clear Search"></td>
          </tr>
          <tr>
            <td colspan=5 class=widget_content>&nbsp;</td>
          </tr>
          <tr>
            <td colspan=5 class=widget_content>Note:</td>
          </tr>
          <tr>
            <td colspan=5 class=widget_content>1) If checkboxes checked, than
              conditions will be included and results related to condition shall
              be shown if Database has these records. </td>
          </tr>
          <tr>
            <td colspan=5 class=widget_content>2) The three search boxes for Opportunity,
              Activity and Case are enabled <br>
              when respective checkbox is checked. They are used to narrow down
              the <br>
              search results.</td>
          </tr>
        </table>
      </form>
    </div>

        <!-- right column //-->
    <div id="Sidebar">

            <!-- new today //-->
            <table class=widget cellspacing=1 width="100%">
                <tr>
                    <td class=widget_header colspan=8>New</td>
                </tr>
                <tr>
                    <td class=widget_label>Item</td>
                    <td class=widget_label>Today</td>
                    <td class=widget_label>This Week</td>
                    <td class=widget_label>This Month</td>
                </tr>
                <tr>
                    <td class=widget_content>Activities</td>
                    <td class=widget_content><?php  echo $activity_count_today; ?></td>
                    <td class=widget_content><?php  echo $activity_count_week; ?></td>
                    <td class=widget_content><?php  echo $activity_count_month; ?></td>
                </tr>
                <tr>
                    <td class=widget_content>Companies</td>
                    <td class=widget_content><?php  echo $company_count_today; ?></td>
                    <td class=widget_content><?php  echo $company_count_week; ?></td>
                    <td class=widget_content><?php  echo $company_count_month; ?></td>
                </tr>
                <tr>
                    <td class=widget_content>Contacts</td>
                    <td class=widget_content><?php  echo $contact_count_today; ?></td>
                    <td class=widget_content><?php  echo $contact_count_week; ?></td>
                    <td class=widget_content><?php  echo $contact_count_month; ?></td>
                </tr>
                <tr>
                    <td class=widget_content>Opportunities</td>
                    <td class=widget_content><?php  echo $opportunity_count_today; ?> for <?php echo $opportunity_total_today; ?></td>
                    <td class=widget_content><?php  echo $opportunity_count_week; ?> for <?php echo $opportunity_total_week; ?></td>
                    <td class=widget_content><?php  echo $opportunity_count_month; ?> for <?php echo $opportunity_total_month; ?></td>
                </tr>
                <tr>
                    <td class=widget_content>Cases</td>
                    <td class=widget_content><?php  echo $case_count_today; ?></td>
                    <td class=widget_content><?php  echo $case_count_week; ?></td>
                    <td class=widget_content><?php  echo $case_count_month; ?></td>
                </tr>
            </table>

            <!-- system snapshot //-->
            <table class=widget cellspacing=1 width="100%">
                <tr>
                    <td class=widget_header colspan=4>Audit Items</td>
                </tr>
                <tr>
                    <td class=widget_label>User</td>
                    <td class=widget_label>Action</td>
                    <td class=widget_label>Date</td>
                </tr>
                <?php  echo $audit_items; ?>
            </table>

    </div>

</div>

<?php

end_page();

/**
 * $Log: dashboard.php,v $
 * Revision 1.7  2004/05/27 12:03:03  braverock
 * - added additional database error handling
 *
 * Revision 1.6  2004/04/20 20:03:07  braverock
 * - add additional activity reporting to the admin interface
 *   - modified from SF patch 927132 submitted by s-t
 *
 * Revision 1.5  2004/04/16 14:44:31  maulani
 * - Add CSS2 positioning
 * - Cleanup HTML so page validates
 * - Add phpdoc
 *
 * Revision 1.4  2004/04/08 18:13:28  maulani
 * - Ignore deleted records in new counts
 *
 * Revision 1.3  2004/03/31 18:47:19  maulani
 * - Fix bug 926884 this month column of dashboard summary was displaying
 *   this weeks values
 *
 * Revision 1.2  2004/03/09 15:21:59  maulani
 * - Added phpdoc
 * - Widened Audit Items layout to display date on one line
 * - Added New This Week and This Month columns
 *
 */
?>