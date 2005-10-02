<?php
/**
 * Manage custom fields
 *
 * $Id: some.php,v 1.1 2005/10/02 23:57:33 vanmer Exp $
 */

require_once('../../../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

require_once('../cf_functions.php');
require_once('../display_functions.php');

$session_user_id = session_check( 'Admin' );

$sql = "SELECT object_id,
			   object_name,
			   type_name
		FROM   cf_objects
		WHERE  record_status = 'a'";

$rst = execute_sql($sql);

if ($rst) {
	while (!$rst->EOF) {
		$table_rows .= '
			<tr>
				<td class=widget_content>
					<a href=one.php?object_id=' . 
						$rst->fields['object_id'] . '>' . 
							$rst->fields['object_name'] .
					'</a>
				</td>
				<td class=widget_content>
					<a href=edit-definitions.php?object_id=' .
					$rst->fields['object_id'] . '>' . 
						_("Edit").'
					</a>
				</td>
				<td class=widget_content>' . 
					_($rst->fields['type_name']) . '
				</td>
			</tr>
		';
		$rst->movenext();
	}
	$rst->close();
}

$page_title = _("Manage Custom Fields");
start_page($page_title);

?>

<div id="Main">
	<div id="Content">

		<table class=widget cellspacing=1>
			<tr>
				<td class=widget_header><?php echo _("Custom Fields Object Name"); ?></td>
				<td class=widget_header><?php echo _("Fields"); ?></td>
				<td class=widget_header><?php echo _("Display On"); ?></td>
			</tr>
			<?php  echo $table_rows; ?>
		</table>

	</div>

	<!-- right column //-->
	<div id="Sidebar">

		<form action="add-2.php" method=post>
		<table class=widget cellspacing=1>
			<tr>
				<td class=widget_header colspan=2>
					<?php echo _("Add New Custom Fields Object"); ?>
				</td>
			</tr>
			<tr>
				<td class=widget_label_right>
					<?php echo _("Name"); ?>
				</td>
				<td class=widget_content_form_element>
					<input type=text name=object_name size=30>
				</td>
			</tr>
			<tr>
				<td class=widget_label_right>
					<?php echo _("Display On"); ?>
				</td>
				<td class=widget_content_form_element>
					<?php echo get_object_type_select(); ?>
				</td>
			</tr>
			<tr>
				<td class=widget_content_form_element colspan=2>
					<input class=button type=submit value="<?php echo _("Add"); ?>">
				</td>
			</tr>
		</table>
		</form>
		<table  class=widget>
			<tr>
				<td class=widget_content>
					<?php
						echo _("Custom Fields plugin ");
						echo $cf_plugin_version;
					?>
				</td>
			</tr>
		</table>
	</div>
</div>

<?php

end_page();

/**
 * $Log: some.php,v $
 * Revision 1.1  2005/10/02 23:57:33  vanmer
 * - Initial Revison of the custom_fields plugin, thanks to Keith Edmunds
 *
 * Revision 1.3  2005/02/11 00:54:55  braverock
 * - add phpdoc where neccessary
 * - fix code formatting and comments
 *
 * Revision 1.2  2004/11/12 06:36:37  gpowers
 * - added support for single display_on add/edit/delete/show
 *
 * Revision 1.1  2004/11/10 07:27:49  gpowers
 * - added admin screens for info types
 */
?>
