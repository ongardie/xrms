<?php
/** 
 * admin/reports/dashboard.php - Administrator's Dashboard 
 * 
 * Displays Audit entries and new activity counts.  Needs work.
 * 
 * $Id: dashboard.php,v 1.2 2004/03/09 15:21:59 maulani Exp $ 
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
$sql = 'Select DATE_SUB(CURRENT_DATE,INTERVAL (DAYOFWEEK(CURRENT_DATE)-1) DAY) AS first_day_month';
$rst = $con->execute($sql);
$first_day_month = $rst->fields['first_day_month'] . ' 00:00:00';
$rst->close();


$begin_today = $con->dbdate(date('Y-m-d 00:00:00'));
$begin_week = $con->dbdate(date($first_day_week));
$begin_month = $con->dbdate(date($first_day_month));

//show_test_values($sql);


// how many activities were created today
$sql = "select count(*) as activity_count from activities where entered_at >= " . $begin_today;
$rst = $con->execute($sql);
$activity_count_today = $rst->fields['activity_count'];
$rst->close();

// how many activities were created this week
$sql = "select count(*) as activity_count from activities where entered_at >= " . $begin_week;
$rst = $con->execute($sql);
$activity_count_week = $rst->fields['activity_count'];
$rst->close();

// how many activities were created this month
$sql = "select count(*) as activity_count from activities where entered_at >= " . $begin_month;
$rst = $con->execute($sql);
$activity_count_month = $rst->fields['activity_count'];
$rst->close();

// how many companies were created today
$sql = "select count(*) as company_count from companies where entered_at >= " . $begin_today;
$rst = $con->execute($sql);
$company_count_today = $rst->fields['company_count'];
$rst->close();

// how many companies were created this week
$sql = "select count(*) as company_count from companies where entered_at >= " . $begin_week;
$rst = $con->execute($sql);
$company_count_week = $rst->fields['company_count'];
$rst->close();

// how many companies were created this month
$sql = "select count(*) as company_count from companies where entered_at >= " . $begin_month;
$rst = $con->execute($sql);
$company_count_month = $rst->fields['company_count'];
$rst->close();

// how many contacts were created today
$sql = "select count(*) as contact_count from contacts where entered_at >= " . $begin_today;
$rst = $con->execute($sql);
$contact_count_today = $rst->fields['contact_count'];
$rst->close();

// how many contacts were created this week
$sql = "select count(*) as contact_count from contacts where entered_at >= " . $begin_week;
$rst = $con->execute($sql);
$contact_count_week = $rst->fields['contact_count'];
$rst->close();

// how many contacts were created this month
$sql = "select count(*) as contact_count from contacts where entered_at >= " . $begin_month;
$rst = $con->execute($sql);
$contact_count_month = $rst->fields['contact_count'];
$rst->close();

// how many opportunities were created today
$sql = "select count(*) as opportunity_count from opportunities where entered_at >= " . $begin_today;
$rst = $con->execute($sql);
$opportunity_count_today = $rst->fields['opportunity_count'];
$rst->close();

// how many opportunities were created this week
$sql = "select count(*) as opportunity_count from opportunities where entered_at >= " . $begin_week;
$rst = $con->execute($sql);
$opportunity_count_week = $rst->fields['opportunity_count'];
$rst->close();

// how many opportunities were created this month
$sql = "select count(*) as opportunity_count from opportunities where entered_at >= " . $begin_month;
$rst = $con->execute($sql);
$opportunity_count_month = $rst->fields['opportunity_count'];
$rst->close();

// how much were those opportunities worth today?
$sql = "select sum(size) as opportunity_total from opportunities where entered_at >= " . $begin_today;
$rst = $con->execute($sql);
$opportunity_total_today = '$' . number_format($rst->fields['opportunity_total'], 2);
$rst->close();

// how much were those opportunities worth this week?
$sql = "select sum(size) as opportunity_total from opportunities where entered_at >= " . $begin_week;
$rst = $con->execute($sql);
$opportunity_total_week = '$' . number_format($rst->fields['opportunity_total'], 2);
$rst->close();

// how much were those opportunities worth this month?
$sql = "select sum(size) as opportunity_total from opportunities where entered_at >= " . $begin_month;
$rst = $con->execute($sql);
$opportunity_total_month = '$' . number_format($rst->fields['opportunity_total'], 2);
$rst->close();

// how many cases were created today
$sql = "select count(*) as case_count from cases where entered_at >= " . $begin_today;
$rst = $con->execute($sql);
$case_count_today = $rst->fields['case_count'];
$rst->close();

// how many cases were created this week
$sql = "select count(*) as case_count from cases where entered_at >= " . $begin_week;
$rst = $con->execute($sql);
$case_count_week = $rst->fields['case_count'];
$rst->close();

// how many cases were created this month
$sql = "select count(*) as case_count from cases where entered_at >= " . $begin_month;
$rst = $con->execute($sql);
$case_count_month = $rst->fields['case_count'];
$rst->close();

$con->close();

$page_title = "Digital Dashboard";
start_page($page_title);

?>

<table border=0 cellpadding=0 cellspacing=0 width=100%>
	<tr>
		<!-- left column //-->
		<td class=lcol width=63% valign=top>
	
			<!-- new today //-->
			<table class=widget cellspacing=1 width=100%>
				<tr>
					<td class=widget_header>Something</td>
				</tr>
				<tr>
					<td class=widget_content>Not sure what to put here yet.</td>
				</tr>
			</table>
	
    	</td>
    	
		<!-- gutter //-->
		<td class=gutter width=2%>
			&nbsp;
		</td>
		
		<!-- right column //-->
		<td class=rcol width=35% valign=top>
			
			<!-- new today //-->
			<table class=widget cellspacing=1 width=100%>
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
			<table class=widget cellspacing=1 width=100%>
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
			
		</td>
	</tr>
</table>

<?php 

end_page();

/** 
 * $Log: dashboard.php,v $
 * Revision 1.2  2004/03/09 15:21:59  maulani
 * - Added phpdoc
 * - Widened Audit Items layout to display date on one line
 * - Added New This Week and This Month columns
 *
 */ 
?> 
