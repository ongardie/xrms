<?php
/**
 * /admin/roles/some.php
 *
 * List roles
 *
 * $Id: some.php,v 1.2 2004/04/16 22:18:26 maulani Exp $
 */

require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

$session_user_id = session_check();

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

$sql = "select * from roles where role_record_status = 'a' order by role_id";
$rst = $con->execute($sql);

if ($rst) {
	while (!$rst->EOF) {
		$table_rows .= '<tr>';
		$table_rows .= '<td class=widget_content><a href=one.php?role_id=' . $rst->fields['role_id'] . '>' . $rst->fields['role_pretty_name'] . '</a></td>';
		$table_rows .= '</tr>';
		$rst->movenext();
	}
	$rst->close();
}

$con->close();

$page_title = "Manage Roles";
start_page($page_title);

?>

<div id="Main">
    <div id="Content">

		<table class=widget cellspacing=1>
			<tr>
				<td class=widget_header colspan=4>Roles</td>
			</tr>
			<tr>
				<td class=widget_label>Name</td>
			</tr>
			<?php  echo $table_rows;; ?>
		</table>

    </div>

        <!-- right column //-->
    <div id="Sidebar">

		<form action=new-2.php method=post>
		<table class=widget cellspacing=1>
			<tr>
				<td class=widget_header colspan=2>Add New Role</td>
			</tr>
			<tr>
				<td class=widget_label_right>Short Name</td>
				<td class=widget_content_form_element><input type=text name=role_short_name size=10></td>
			</tr>
			<tr>
				<td class=widget_label_right>Full Name</td>
				<td class=widget_content_form_element><input type=text name=role_pretty_name size=20></td>
			</tr>
			<tr>
				<td class=widget_label_right>Full Plural Name</td>
				<td class=widget_content_form_element><input type=text name=role_pretty_plural size=20></td>
			</tr>
			<tr>
				<td class=widget_label_right>Display HTML</td>
				<td class=widget_content_form_element><input type=text name=role_display_html size=30></td>
			</tr>
			<tr>
				<td class=widget_content_form_element colspan=2><input class=button type=submit value="Add"></td>
			</tr>
		</table>
		</form>

    </div>
</div>

<?php

end_page();

/**
 * $Log: some.php,v $
 * Revision 1.2  2004/04/16 22:18:26  maulani
 * - Add CSS2 Positioning
 *
 *
 */
?>
