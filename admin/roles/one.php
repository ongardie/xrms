<?php
/**
 * /admin/roles/one.php
 *
 * Edit roles
 *
 * $Id: one.php,v 1.7 2004/07/25 19:11:54 johnfawcett Exp $
 */

require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check( 'Admin' );

$role_id = $_GET['role_id'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

$sql = "select * from roles where role_id = $role_id";

$rst = $con->execute($sql);

if ($rst) {
	
	$role_short_name = $rst->fields['role_short_name'];
	$role_pretty_name = $rst->fields['role_pretty_name'];
	$role_pretty_plural = $rst->fields['role_pretty_plural'];
	$role_display_html = $rst->fields['role_display_html'];
	
	$rst->close();
}

$con->close();

$page_title = _("Role Details").': '.$role_pretty_name;
start_page($page_title);

?>

<div id="Main">
    <div id="Content">

		<form action=edit-2.php method=post>
		<input type=hidden name=role_id value="<?php  echo $role_id;; ?>">
		<table class=widget cellspacing=1>
			<tr>
				<td class=widget_header colspan=4><?php echo _("Edit Role Information"); ?></td>
			</tr>
			<tr>
				<td class=widget_label_right><?php echo _("Short Name"); ?></td>
				<td class=widget_content_form_element><input type=text size=10 name=role_short_name value="<?php  echo $role_short_name;; ?>"></td>
			</tr>
			<tr>
				<td class=widget_label_right><?php echo _("Full Name"); ?></td>
				<td class=widget_content_form_element><input type=text size=20 name=role_pretty_name value="<?php  echo $role_pretty_name;; ?>"></td>
			</tr>
			<tr>
				<td class=widget_label_right><?php echo _("Full Plural"); ?></td>
				<td class=widget_content_form_element><input type=text size=20 name=role_pretty_plural value="<?php  echo $role_pretty_plural;; ?>"></td>
			</tr>
			<tr>
				<td class=widget_label_right><?php echo _("Display HTML"); ?></td>
				<td class=widget_content_form_element><input type=text size=30 name=role_display_html value="<?php  echo $role_display_html;; ?>"></td>
			</tr>
			<tr>
				<td class=widget_content_form_element colspan=2><input class=button type=submit value="<?php echo _("Save Changes"); ?>"></td>
			</tr>
		</table>
		</form>

		<form action=delete.php method=post  onsubmit="javascript: return confirm('<?php echo _("Delete Role?"); ?>');">
		<input type=hidden name=role_id value="<?php  echo $role_id;; ?>">
		<table class=widget cellspacing=1>
			<tr>
				<td class=widget_header colspan=4><?php echo _("Delete Role"); ?></td>
			</tr>
			<tr>
				<td class=widget_content>
				<?php echo _("Click the button below to permanently remove this item."); ?>
				<p><?php echo _("Note: This action CANNOT be undone!"); ?></p>
				<p><input class=button type=submit value="<?php echo _("Delete"); ?>"></p>
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
 * Revision 1.7  2004/07/25 19:11:54  johnfawcett
 * - reinserted ? in gettext string - needed by some languages
 * - standardized delete text and button
 * - corrected bug: confirm delete not working
 *
 * Revision 1.6  2004/07/25 15:58:46  johnfawcett
 * - unified page title
 * - removed punctuation from gettext strings
 *
 * Revision 1.5  2004/07/16 23:51:38  cpsource
 * - require session_check ( 'Admin' )
 *
 * Revision 1.4  2004/07/16 13:52:00  braverock
 * - localize strings for i18n translation support
 *   - applies modified patches from Sebastian Becker (hyperpac)
 *
 * Revision 1.3  2004/06/14 22:47:04  introspectshun
 * - Add adodb-params.php include for multi-db compatibility.
 * - Now use ADODB GetInsertSQL, GetUpdateSQL functions.
 *
 * Revision 1.2  2004/04/16 22:18:26  maulani
 * - Add CSS2 Positioning
 *
 *
 */
?>
