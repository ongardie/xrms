<?php

require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

$session_user_id = session_check();

$user_id = $_GET['user_id'];

// $con = &adonewconnection($xrms_db_dbtype);
// $con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

// $con->close();

$page_title = "Change Password";
start_page($page_title);

?>

<table border=0 cellpadding=0 cellspacing=0 width=100%>
	<tr>
		<td class=lcol width=35% valign=top>
		
		<form action="change-password-2.php" method="post">
		<input type=hidden name=user_id value="<?php echo $user_id; ?>">
		<table class=widget cellspacing=1 width=100%>
			<tr>
				<td class=widget_header colspan=4>Change Password</td>
			</tr>
			<tr>
				<td class=widget_label_right>New Password</td>
				<td class=widget_content_form_element><input type=password size=30 name=password></td>
			</tr>
			<tr>
				<td class=widget_content_form_element colspan=2><input class=button type=submit value="Save Changes"></td>
			</tr>
		</table>
		</form>

		</td>
		
		<!-- gutter //-->
		<td class=gutter width=1%>
		&nbsp;
		</td>
		
		<!-- right column //-->
		
		<td class=rcol width=64% valign=top>
		&nbsp;
		</td>
		
	</tr>
</table>

<?php end_page();; ?>
