<?php
/**
 * Edit custom field details
 *
 * $Id: edit-definitions.php,v 1.1 2005/10/02 23:57:33 vanmer Exp $
 */

require_once('../../../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'utils-accounting.php');
require_once($include_directory . 'confgoto.php');

require_once('../cf_functions.php');
require_once('definition_functions.php');

$con = connect();

# Check user has admin privileges
$session_user_id = session_check('Admin');

# Which object are we editing?
$object_id = $_GET['object_id'];
assert($object_id);

# Get object info
$sql = "SELECT	object_name, cf_objects.type_name as type_name,
		label_field_id, display
	FROM	cf_objects, cf_types
	WHERE	cf_objects.object_id = $object_id
	AND	cf_objects.type_name=cf_types.type_name
	AND	cf_objects.record_status = 'a'
	AND	cf_types.record_status = 'a'";
$rst_object_info = execute_sql($sql);
extract($rst_object_info->fields);

$in_sidebar = ($display == "sidebar");
$inline = ($display == "inline");

# Check to see if we called ourselves from one of the buttons on
# form. If so, take appropriate action. There should only be one
# variable (at most) starting with "btn".
$add_new_element = False;
foreach($_GET as $key=>$value) {
	if (!strncmp($key, "btn", 3)) {
		switch($key) {
		
		case "btnNew":
			$add_new_element = True;
			break;

		case "btnSave":
			save_changes();
			header("Location: some.php");
			break;
		
		case "btnSaveAndNew":
			save_changes();
			$add_new_element = True;
			break;
		
		default:
			# Check for delete button
			if (!strncmp($key, "btnDelete", 9)) {
				# Get the element id to delete
				$delete_id = substr($key, 9);
				# TODO: KAE: confirm delete
				do_delete_element($delete_id);
				header("Location: edit-definitions.php?object_id=$object_id");
			}
		}
	}
}

# Get list of defined elements for this custom fields object
$sql = "SELECT	* 
	FROM	cf_fields
	WHERE	object_id = $object_id 
	AND	record_status = 'a'
	ORDER BY field_column, 
		 field_order,
		 field_id";
$rst = execute_sql($sql);

# Should we add a new field?
if (0 == $rst->RecordCount()) {
	$add_new_element = True;
}

# Build array of column headers for display
$headers = array(
	_("Label"),
	_("Type"),
	_("Default Value"),
	_("Possible Values"),
	_("Order"));
if (!$inline) {
	$headers[] = _("Column");
}
if ($in_sidebar) {
	$headers[] = _("Display in Sidebar?");
}
$headers[] = "&nbsp;";

# Build array of row-arrays for display
$rows = array();
while (!$rst->EOF) {
	$rows[] = get_row_html($rst->fields, $in_sidebar, $inline);
	$rst->movenext();
}

# Do we need to display an empty row for a new element?
if ($add_new_element) {
   # Add 'blank' element to rows
   $types = array('text','select','radio','checkbox','textarea');
   $blank = array(
	 "field_id" => 0,
	 "field_label" => "",
	 "field_type" => $types,
	 "field_column" => "1",
	 "field_order" => "",
	 "default_value" => "",
	 "possible_values" => "",
	 "display_in_sidebar" => 0,
   );
   $rows[] = get_row_html($blank, $in_sidebar, $inline);
}

# Get list of candidates for element label (ie, the element used
# to "name" this object and displayed in the sidebar as a link
# to get the full details). $label_field_id is already set to the 
# current label field_id; after this select box it may be reset
# to a new value.

# First get current label field_id
$sql = "SELECT	label_field_id
		FROM	cf_objects
		WHERE	object_id = $object_id
		AND		record_status = 'a'";
$label_field_id = $con->GetOne($sql);

# Now build select box for label
$name_selector = "";
if ($in_sidebar) {
	$sql = "SELECT	field_label, field_id
		FROM	cf_fields
		WHERE	object_id = $object_id
		AND	field_type IN ('text','radio','select')
		AND	record_status = 'a'";
	$rst = execute_sql($sql);
	$name_selector = $rst->GetMenu2('label_field_id', $label_field_id, 
			$blank1stItem=true);
}

$page_title = _("Edit Field Definitions") . ": $object_name";
start_page($page_title, true);

?>

<div id="Main">
  <div id="Content">
	<form action="edit-definitions.php" method="get">
	<input type=hidden name=object_id value="<?php echo $object_id ?>">
	<input type=hidden name=new_element value="<?php echo $add_new_element ?>">
	  <table class=widget cellspacing=1>
		<tr>
		  <td class=widget_header colspan=9><?php echo _("Field Definitions"); ?></td>
		</tr>
		<tr>
			<?php foreach($headers as $header) { ?>
				<th><?php echo $header; ?></th>
			<?php } ?>
		</tr>
		<?php foreach($rows as $row) { ?>
			<tr>
				<?php foreach($row as $col) { ?>
					<td><?php echo $col; ?></td>
				<?php } ?>
			</tr>
		<?php } ?>
		<tr>
		  <td class=widget_content_form_element colspan=9>
			<?php 
				if (!empty($name_selector)) {
					echo _("Name field:");
					echo "&nbsp;";
					echo $name_selector;
				}
			?>
			<input class=button type=submit value="Save Changes" name="btnSave">
			&nbsp;
			<?php if ($add_new_element) { ?>
			  <input class=button type=submit 
			   value="Save and add another field" 
			   name="btnSaveAndNew">
			  &nbsp;  
			<?php } else { ?>
			  <input class=button type=submit 
			   value="Add new field" 
			   name="btnNew">
			  &nbsp;  
		  <?php } ?>		  
		  <input class=button type=button value="<?php echo _("Back"); ?>"
			onclick="javascript: location.href='some.php'">
		  </td>
		</tr>
	  </table>
	</form>
  </div>
</div>

<?php
end_page();
?>
