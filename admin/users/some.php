<?php

require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

$session_user_id = session_check();

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

$sql = "select * from users, roles 
where users.role_id = roles.role_id 
and user_record_status = 'a' order by username";
$rst = $con->execute($sql);

if ($rst) {
	while (!$rst->EOF) {
		$table_rows .= '<tr>';
		$table_rows .= '<td class=widget_content><a href=one.php?user_id=' . $rst->fields['user_id'] . '>' . $rst->fields['username'] . '</a></td>';
		$table_rows .= '<td class=widget_content>' . $rst->fields['role_pretty_name'] . '</td>';
		$table_rows .= '<td class=widget_content>' . $rst->fields['last_name'] . ', ' . $rst->fields['first_names'] . '</td>';
		$table_rows .= '<td class=widget_content>' . $rst->fields['email'] . '</td>';
		$table_rows .= '</tr>';
		$rst->movenext();
	}
	$rst->close();
} else {
	$table_rows = '<tr><td> Unable to get data from database </td> </tr>';
}

$sql2 = "select role_pretty_name, role_id from roles where role_record_status = 'a' order by role_id";
$rst = $con->execute($sql2);
$role_menu = $rst->getmenu2('role_id', '', false);
$rst->close();

$con->close();

$page_title = "Manage Users";
start_page($page_title);

?>

<table border=0 cellpadding=0 cellspacing=0 width=100%>
	<tr>
		<td class=lcol width=65% valign=top>
		
		<table class=widget cellspacing=1 width=100%>
			<tr>
				<td class=widget_header colspan=4>Users</td>
			</tr>
			<tr>
				<td class=widget_label>Username</td>
				<td class=widget_label>Role</td>
				<td class=widget_label>Full Name</td>
				<td class=widget_label>E-Mail</td>
			</tr>
			<?php echo $table_rows;; ?>
		</table>
		
		</td>
		
		<!-- gutter //-->
		<td class=gutter width=2%>
		&nbsp;
		</td>
		
		<!-- right column //-->
		
		<td class=rcol width=33% valign=top>

		<form action=add-2.php method=post>
		<table class=widget cellspacing=1 width=100%>
			<tr>
				<td class=widget_header colspan=2>Add New User</td>
			</tr>
			<tr>
				<td class=widget_label_right>Role</td>
				<td class=widget_content_form_element><?php  echo $role_menu; ?></td>
			</tr>
			<tr>
				<td class=widget_label_right>Last Name</td>
				<td class=widget_content_form_element><input type=text name=last_name size=30></td>
			</tr>
			<tr>
				<td class=widget_label_right>First Names</td>
				<td class=widget_content_form_element><input type=text name=first_names size=30></td>
			</tr>
			<tr>
				<td class=widget_label_right>Username</td>
				<td class=widget_content_form_element><input type=text name=username></td>
			</tr>
			<tr>
				<td class=widget_label_right>Password</td>
				<td class=widget_content_form_element><input type=password name=password size=30></td>
			</tr>
			<tr>
				<td class=widget_label_right>E-Mail</td>
				<td class=widget_content_form_element><input type=text name=email size=30></td>
			</tr>
			<tr>
				<td class=widget_label_right>Language</td>
				<td class=widget_content_form_element>English</td>
			</tr>
			<tr>
				<td class=widget_label_right>GMT Offset</td>
				<td class=widget_content_form_element><input type=text size=5 name=gmt_offset></td>
			</tr>
			<tr>
				<td class=widget_label_right>Allow Access</td>
				<td class=widget_content_form_element><input type=checkbox name=allowed_p checked></td>
			</tr>
			<tr>
				<td class=widget_content_form_element colspan=2><input class=button type=submit value="Add"></td>
			</tr>
		</table>
		</form>

		</td>
	</tr>
</table>

<?php end_page(); ?>
