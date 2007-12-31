<?php
/**
 * Manage Category Scopes
 *
 * $Id: some.php,v 1.1 2007/12/31 19:05:25 randym56 Exp $
 */

require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check( 'Admin' );

$con = get_xrms_dbconnection();

$sql = "select * from category_scopes where category_scope_record_status = 'a' order by category_scope_id";
$rst = $con->execute($sql);

if ($rst) {
	while (!$rst->EOF) {
		$table_rows .= '<tr>';
		$table_rows .= '<td class=widget_content>' . $rst->fields['category_scope_id'] . '</td>';
		$table_rows .= '<td class=widget_content>' . $rst->fields['category_scope_short_name'] . '</td>';
		$table_rows .= '<td class=widget_content><a href=one.php?category_scope_id=' . $rst->fields['category_scope_id'] . '>' . _($rst->fields['category_scope_pretty_name']) . '</a></td>';
		$table_rows .= '<td class=widget_content>' . $rst->fields['category_scope_pretty_plural'] . '</td>';
		$table_rows .= '<td class=widget_content>' . $rst->fields['category_scope_display_html'] . '</td>';
		$table_rows .= '<td class=widget_content>' . $rst->fields['on_what_table'] . '</td>';
		$table_rows .= '</tr>';
		$rst->movenext();
	}
	$rst->close();
}

$con->close();

$page_title = _("Manage Category Scopes");
start_page($page_title);

?>

<div id="Main">
    <div id="Content">

		<table class=widget cellspacing=1>
			<tr>
				<td class=widget_header colspan=6><?php echo _("Category Scopes"); ?></td>
			</tr>
			<tr>
				<td class=widget_label><?php echo _("ID"); ?></td>
				<td class=widget_label><?php echo _("Short Name"); ?></td>
				<td class=widget_label><?php echo _("Full Name"); ?></td>
				<td class=widget_label><?php echo _("Full Plural Name"); ?></td>
				<td class=widget_label><?php echo _("Display HTML"); ?></td>
				<td class=widget_label><?php echo _("On What Table"); ?></td>
			</tr>
			<?php  echo $table_rows; ?>
		</table>

    </div>

        <!-- right column //-->
    <div id="Sidebar">

		<form action=new-2.php method=post>
		<table class=widget cellspacing=1>
			<tr>
				<td class=widget_header colspan=2><?php echo _("Add New Category Scope"); ?></td>
			</tr>
			<tr>
				<td class=widget_label_right><?php echo _("Short Name"); ?></td>
				<td class=widget_content_form_element><input type=text name=category_scope_short_name size=10></td>
			</tr>
			<tr>
				<td class=widget_label_right><?php echo _("Full Name"); ?></td>
				<td class=widget_content_form_element><input type=text name=category_scope_pretty_name size=20></td>
			</tr>
			<tr>
				<td class=widget_label_right><?php echo _("Full Plural Name"); ?></td>
				<td class=widget_content_form_element><input type=text name=category_scope_pretty_plural size=20></td>
			</tr>
			<tr>
				<td class=widget_label_right><?php echo _("Display HTML"); ?></td>
				<td class=widget_content_form_element><input type=text name=category_scope_display_html size=30></td>
			</tr>
			<tr>
				<td class=widget_label_right><?php echo _("On What Table"); ?></td>
				<td class=widget_content_form_element><input type=text name=on_what_table size=30></td>
			</tr>
			<tr>
				<td class=widget_content_form_element colspan=2>WARNING: Be certain you understand the system and how attaching categories to tables will affect the system before you add more than the 5 default tables included with the initial installation.</td>
			</tr>
			<tr>
				<td class=widget_content_form_element colspan=2><input class=button type=submit value="<?php echo _("Add"); ?>"></td>
			</tr>
		</table>
		</form>

    </div>
</div>

<?php

end_page();

/**
 * $Log: some.php,v $
 * Revision 1.1  2007/12/31 19:05:25  randym56
 * Function to add/edit Category Scopes table
 *
 * Revision v 1.0 2007/12/31 11:09:59 randym56 Exp $
 * Add function
 *
 */
?>
