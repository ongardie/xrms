<?php
/**
 * Manage crm statuses
 *
 * $Id: one.php,v 1.4 2004/06/14 22:14:42 introspectshun Exp $
 */

require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();

$crm_status_id = $_GET['crm_status_id'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

$sql = "select * from crm_statuses where crm_status_id = $crm_status_id";

$rst = $con->execute($sql);

if ($rst) {
	
	$crm_status_short_name = $rst->fields['crm_status_short_name'];
	$crm_status_pretty_name = $rst->fields['crm_status_pretty_name'];
	$crm_status_pretty_plural = $rst->fields['crm_status_pretty_plural'];
	$crm_status_display_html = $rst->fields['crm_status_display_html'];
	
	$rst->close();
}

$con->close();

$page_title = "One CRM Status : $crm_status_pretty_name";
start_page($page_title);

?>

<div id="Main">
    <div id="Content">

		<form action=edit-2.php method=post>
		<input type=hidden name=crm_status_id value="<?php  echo $crm_status_id; ?>">
		<table class=widget cellspacing=1>
			<tr>
				<td class=widget_header colspan=4>Edit Rating Information</td>
			</tr>
			<tr>
				<td class=widget_label_right>Short Name</td>
				<td class=widget_content_form_element><input type=text size=10 name=crm_status_short_name value="<?php  echo $crm_status_short_name; ?>"></td>
			</tr>
			<tr>
				<td class=widget_label_right>Full Name</td>
				<td class=widget_content_form_element><input type=text size=20 name=crm_status_pretty_name value="<?php  echo $crm_status_pretty_name; ?>"></td>
			</tr>
			<tr>
				<td class=widget_label_right>Full Plural</td>
				<td class=widget_content_form_element><input type=text size=20 name=crm_status_pretty_plural value="<?php  echo $crm_status_pretty_plural; ?>"></td>
			</tr>
			<tr>
				<td class=widget_label_right>Display HTML</td>
				<td class=widget_content_form_element><input type=text size=30 name=crm_status_display_html value="<?php  echo $crm_status_display_html; ?>"></td>
			</tr>
			<tr>
				<td class=widget_content_form_element colspan=2><input class=button type=submit value="Save Changes"></td>
			</tr>
		</table>
		</form>

		<form action="delete.php" method=post onsubmit="javascript: return confirm('Delete CRM Status?');">
		<input type=hidden name="crm_status_id" value="<?php  echo $crm_status_id; ?>">
		<table class=widget cellspacing=1>
			<tr>
				<td class=widget_header colspan=4>Delete CRM Status</td>
			</tr>
			<tr>
				<td class=widget_content>
				Click the button below to remove this status from the system.
				<p>Note: This action CANNOT be undone!
				<p><input class=button type=submit value="Delete Status">
				</td>
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
 * $Log: one.php,v $
 * Revision 1.4  2004/06/14 22:14:42  introspectshun
 * - Add adodb-params.php include for multi-db compatibility.
 * - Now use ADODB GetInsertSQL, GetUpdateSQL functions.
 *
 * Revision 1.3  2004/04/16 22:18:25  maulani
 * - Add CSS2 Positioning
 *
 * Revision 1.2  2004/04/08 16:56:48  maulani
 * - Update javascript declaration
 * - Add phpdoc
 *
 *
 */
?>

