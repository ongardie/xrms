<?php
/**
 * Edit custom field types
 *
 * $Id: one.php,v 1.1 2005/10/02 23:57:33 vanmer Exp $
 */

require_once('../../../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

require_once('../cf_functions.php');

$session_user_id = session_check( 'Admin' );

$object_id = $_GET['object_id'];

$sql = "SELECT	object_name, cf_objects.type_name as type_name, display
		FROM	cf_objects, cf_types
		WHERE	object_id = $object_id
		AND		cf_objects.type_name = cf_types.type_name
		AND		cf_objects.record_status = 'a'
		AND		cf_types.record_status = 'a'";
$rst = execute_sql($sql);

extract($rst->fields);
	
$page_title = $object_name . ": ". _("Details");
start_page($page_title);

?>

<div id="Main">
	<div id="Content">

		<form action="edit-2.php" method=post>
		<input type=hidden name=object_id value="<?php  echo $object_id; ?>">
		<table class=widget cellspacing=1>
			<tr>
				<td class=widget_header colspan=4><?php echo _("Edit Custom Fields Object Information"); ?></td>
			</tr>
			<tr>
				<td class=widget_label_right>
					<?php echo _("Name"); ?>
				</td>
				<td class=widget_content_form_element>
					<input type=text name=object_name 
						value="<?php  echo $object_name; ?>">
				</td>
			</tr>
			<tr>
				<td class=widget_label_right>
					<?php echo _("Display On"); ?>
				</td>
				<td>
					<?php echo _($type_name); ?>
				</td>
			</tr>
			<tr>
				<td class=widget_content_form_element colspan=2>
					<input class=button type=submit value="<?php echo _("Save Changes"); ?>">
				</td>
			</tr>
		</table>
		</form>

		<?php
			# Don't allow deletion of inline objects - there should be one,
			# and only one, for each location that can disply inline objects
			if ($display != 'inline') { ?>
				<form action="delete.php" method=post onsubmit="javascript: return confirm('<?php echo _("Delete Custom Fields Type?"); ?>');">
				<input type=hidden name=object_id value="<?php  echo $object_id; ?>">
				<table class=widget cellspacing=1>
					<tr>
						<td class=widget_header colspan=4><?php echo _("Delete Custom Fields Object"); ?></td>
					</tr>
					<tr>
						<td class=widget_content>
							<?php echo _("Click the button below to permanently remove this item."); ?>
							<p>
								<?php echo _("Note: This action CANNOT be undone!"); ?>
							</p>
							<p>
								<input class=button type=submit value="<?php echo _("Delete"); ?>">
							</p>
						</td>
					</tr>
				</table>
			<?php } ?>
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
 * Revision 1.1  2005/10/02 23:57:33  vanmer
 * - Initial Revison of the custom_fields plugin, thanks to Keith Edmunds
 *
 * Revision 1.4  2005/04/01 20:15:03  ycreddy
 * Replaced LIMIT with the portable SelectLimit
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
 *
 */
?>
