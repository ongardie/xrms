<?php
/**
 * admin/users/self.php - User self-administration page
 *
 * Users who do not have admin privileges can update their own
 * user record and password.
 *
 * $Id: self.php,v 1.3 2004/05/10 20:54:31 maulani Exp $
 */

require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

$session_user_id = session_check();

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

$sql = "select * from users where user_id = $session_user_id";

$rst = $con->execute($sql);

if ($rst) {
	
    $user_type_id = $rst->fields['user_type_id'];
	$username = $rst->fields['username'];
	$first_names = $rst->fields['first_names'];
	$last_name = $rst->fields['last_name'];
	$email = $rst->fields['email'];
	$gmt_offset = $rst->fields['gmt_offset'];
	$language = $rst->fields['language'];
	
	$rst->close();
}
//show_test_values($username, $last_name, $first_names, $session_user_id, $user_id);

$page_title = "One User : $first_names $last_name";
start_page($page_title);

?>

<div id="Main">
    <div id="Content">

		<form action=self-2.php method=post>
		<input type=hidden name=user_id value="<?php  echo $session_user_id; ?>">
		<table class=widget cellspacing=1>
			<tr>
				<td class=widget_header colspan=4>Edit User Information</td>
			</tr>
			<tr>
				<td class=widget_label_right>Last Name</td>
				<td class=widget_content_form_element><input type=text size=30 name=last_name value="<?php  echo $last_name; ?>"></td>
			</tr>
			<tr>
				<td class=widget_label_right>First Names</td>
				<td class=widget_content_form_element><input type=text size=30 name=first_names value="<?php  echo $first_names; ?>"></td>
			</tr>
			<tr>
				<td class=widget_label_right>Username</td>
				<td class=widget_content_form_element><?php  echo $username; ?></td>
			</tr>
			<tr>
				<td class=widget_label_right>E-Mail</td>
				<td class=widget_content_form_element><input type=text size=40 name=email value="<?php  echo $email; ?>"></td>
			</tr>
			<tr>
				<td class=widget_label_right>Language</td>
				<td class=widget_content_form_element>English</td></td>
			</tr>
			<tr>
				<td class=widget_label_right>GMT Offset</td>
				<td class=widget_content_form_element><input type=text size=5 name=gmt_offset value="<?php  echo $gmt_offset; ?>"></td>
			</tr>
			<tr>
				<td class=widget_content_form_element colspan=2><input class=button type=submit value="Save Changes"> <input class=button type=button onclick="javascript: location.href='change-password.php?user_id=<?php echo $session_user_id ?>';" value="Change Password"></td>
			</tr>
		</table>
		</form>

    </div>

        <!-- right column //-->
    <div id="Sidebar">

		&nbsp;

    </div>
</div>

<?php

end_page();

/**
 *$Log: self.php,v $
 *Revision 1.3  2004/05/10 20:54:31  maulani
 *- Fix bug 951490.  Unprivileged users will now return to the home screen
 *  after modifying their user records.
 *
 *Revision 1.2  2004/04/16 22:18:27  maulani
 *- Add CSS2 Positioning
 *
 *Revision 1.1  2004/03/12 15:46:51  maulani
 *Temporary change for use until full access control is implemented
 *- Block non-admin users from the administration screen
 *- Allow all users to modify their own user record and password
 *- Add phpdoc
 *
 *
 */
?>
