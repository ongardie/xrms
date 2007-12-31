<?php
/**
 * Manage category-scopes
 *
 * $Id: one.php,v 1.1 2007/12/31 19:05:25 randym56 Exp $
 */

require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check( 'Admin' );

$category_scope_id = $_GET['category_scope_id'];

$con = get_xrms_dbconnection();

$sql = "select * from category_scopes where category_scope_id = $category_scope_id";

$rst = $con->execute($sql);

if ($rst) {
	
	$category_scope_short_name = $rst->fields['category_scope_short_name'];
	$category_scope_pretty_name = $rst->fields['category_scope_pretty_name'];
	$category_scope_pretty_plural = $rst->fields['category_scope_pretty_plural'];
	$category_scope_display_html = $rst->fields['category_scope_display_html'];
	$on_what_table = $rst->fields['on_what_table'];
	
	$rst->close();
}

$con->close();

$page_title = _("Category Scope Details").': '.$category_scope_pretty_name;
start_page($page_title);

?>

<div id="Main">
    <div id="Content">

		<form action=edit-2.php method=post>
		<input type=hidden name=category_scope_id value="<?php  echo $category_scope_id; ?>">
		<table class=widget cellspacing=1>
			<tr>
				<td class=widget_header colspan=4><?php echo _("Edit Category Scope Information"); ?></td>
			</tr>
			<tr>
				<td class=widget_label_right><?php echo _("On What Table"); ?></td>
				<td class=widget_content_form_element><input type=hidden name=on_what_table value="<?php  echo $on_what_table; ?>"><?php echo $on_what_table; ?></td>
			</tr>
			<tr>
				<td class=widget_label_right><?php echo _("Short Name"); ?></td>
				<td class=widget_content_form_element><input type=text size=10 name=category_scope_short_name value="<?php  echo $category_scope_short_name; ?>"></td>
			</tr>
			<tr>
				<td class=widget_label_right><?php echo _("Full Name"); ?></td>
				<td class=widget_content_form_element><input type=text size=20 name=category_scope_pretty_name value="<?php  echo $category_scope_pretty_name; ?>"></td>
			</tr>
			<tr>
				<td class=widget_label_right><?php echo _("Full Plural"); ?></td>
				<td class=widget_content_form_element><input type=text size=20 name=category_scope_pretty_plural value="<?php  echo $category_scope_pretty_plural; ?>"></td>
			</tr>
			<tr>
				<td class=widget_label_right><?php echo _("Display HTML"); ?></td>
				<td class=widget_content_form_element><input type=text size=30 name=category_scope_display_html value="<?php  echo $category_scope_display_html; ?>"></td>
			</tr>
			<tr>
				<td class=widget_content_form_element colspan=2><input class=button type=submit value="<?php echo _("Save Changes"); ?>"></td>
			</tr>
		</table>
		</form>

		<form action=delete.php method=post onsubmit="javascript: return confirm('<?php echo addslashes(_("Delete Category Scope?")); ?>');">
		<input type=hidden name=category_scope_id value="<?php  echo $category_scope_id; ?>">
		<table class=widget cellspacing=1>
			<tr>
				<td class=widget_header colspan=4><?php echo _("Delete Category Scope"); ?></td>
			</tr>
			<tr>
				<td class=widget_content>
				<?php echo _("Click the button below to permanently remove this item."); ?>
				<p><?php echo _("Note: This action CANNOT be undone!"); ?>
				<p><input class=button type=submit value="<?php echo _("Delete"); ?>">
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
 * Revision 1.1  2007/12/31 19:05:25  randym56
 * Function to add/edit Category Scopes table
 *
 * Revision 1.0  2007-12-31 16:56:47  randym56
 * - new functions added
 *
 */
?>

