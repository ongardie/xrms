<?php

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

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

<table border=0 cellpadding=0 cellspacing=0 width=100%>
	<tr>
		<td class=lcol width=65% valign=top>

		<table class=widget cellspacing=1 width=100%>
			<tr>
				<td colspan=2 class=widget_header>Reports</td>
			</tr>
			<tr>
				<td colspan=2 class=widget_label_center>Company Reports</td>
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
				<td colspan=2 class=widget_label_center>Opportunity Reports</td>
			</tr>
			<tr>
				<td class=widget_content><a href="opportunities-quantity-by-opportunity-status.php">Quantity by Status</a></td>
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
				<td colspan=2 class=widget_label_center>Case Reports</td>
			</tr>
			<tr>
				<td class=widget_content><a href="cases-by-case-status.php">Cases by Status</a></td>
				<td class=widget_content>How many cases are in each stage of the case resolution process?</td>
			</tr>
			<tr>
				<td colspan=2 class=widget_label_center>User Reports</td>
			</tr>
			<tr>
				<td class=widget_content colspan="2"><form action="user-activity.php" method=post>Activity Report for <?= $user_menu ?> <input class=button type=submit value="Go"></form></td>
			</tr>
		</table>

		</td>
		
		<!-- gutter //-->
		<td class=gutter width=2%>&nbsp;</td>
		
		<!-- right column //-->
		<td class=rcol width=33% valign=top>

		</td>
	</tr>
</table>

<?php end_page(); ?>