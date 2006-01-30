<?php
/**
* Create a graph of activity for the requested user.
*
* @author Glenn Powers
*
* $Id: stale-crm-status.php,v 1.2 2006/01/30 17:48:01 niclowe Exp $
*/
require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

require_once($include_directory . 'classes/Pager/GUP_Pager.php');
require_once($include_directory . 'classes/Pager/Pager_Columns.php');

$form_name="Stale Crm Status";
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

$page_title = "Stale Companies";
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

$sql = "select crm_status_short_name,  crm_status_id from crm_statuses where crm_status_record_status = 'a' order by crm_status_short_name";
$rst = $con->execute($sql);
$crm_status_menu = $rst->getmenu2('crm_status_id', '', false);
$rst->close();

?>

<?php
if (($display) || (!$friendly)) {
			echo "
			<p>&nbsp;</p>
			<table>
			<form action=stale-crm-status.php method=get>
			<input type=hidden name=display value=y>
			<tr>
			<th align=left>Start</th>
			<th align=left>End</th>
			<th align=left>User</th>
			<th align=left>Crm Status</th>
			<th align=left></th>
			</tr>
			<tr>
			<td><input type=text name=starting value=\"" . $starting . "\"></td>
			<td><input type=text name=ending value=\"" . $ending . "\"></td>
			<td>" . $user_menu . "</td>
			<td>" . $crm_status_menu . "</td>
			<td>
			<input class=button type=submit value=Go>
			</td>
			</tr>
			<tr>

			<td align=right>
			<td>
			<input name=all_users type=checkbox ";

			if ($all_users) {
						echo "checked";
			}

			echo ">All Users
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
			$crm_status_id=$_REQUEST['crm_status_id'];
			$userlist="(";
			$i=0;
			$num_users=count($userArray);
			if($num_users==1){
						$userlist.=$userArray[0].")";
			}else{
						$i=1;
						foreach ($userArray as $key => $user_id) {
									if($i<>$num_users)$userlist.=$user_id.",";
									if($i==$num_users)$userlist.=$user_id.")";
									$i++;
						}
			}
			$sql="select CONCAT('<a href=../companies/one.php?company_id=',c.company_id, '>', c.company_name,'</a>') as company_name,
			a.last_modified_at
			FROM companies c LEFT JOIN activities a ON a.company_id=c.company_id
			WHERE
			c.crm_status_id='".$crm_status_id."'
			AND
			c.user_id IN $userlist
			AND
			c.company_record_status='a'
			AND
			(
			(a.activity_record_status='a' AND
			a.last_modified_at<".$con->qstr($user_ending, get_magic_quotes_gpc())."
			)
			OR
			ISNULL(a.last_modified_at)
			)
			group by c.company_name";

			$columns = array();
			$columns[] = array('name' => 'Company', 'index_sql' => 'company_name');
			$columns[] = array('name' => 'Last Activity Date', 'index_sql' => 'last_modified_at');


			$default_columns=array('Company','Last Activity Date');

			$pager_columns = new Pager_Columns('StaleCompaniesColumns', $columns, $default_columns, $form_name);
			$pager_columns_button = $pager_columns->GetSelectableColumnsButton();
			$pager_columns_selects = $pager_columns->GetSelectableColumnsWidget();



			$pager = new GUP_Pager($con, $sql,false, _("Stale Companies"), $form_name, 'StaleCompaniesColumns', $columns);
			$pager->Render(500);//having difficulty with pager next buttons..did not have time to debug fully.

}



$con->close();
if (($display) || (!$friendly)) {
			end_page();
}

/**
* $Log: stale-crm-status.php,v $
* Revision 1.2  2006/01/30 17:48:01  niclowe
* fixed bug in userlist for all users.
*
*/
?>
