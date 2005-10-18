<?php
/**
 * Edit item details
 *
 * $Id: edit.php,v 1.2 2005/10/18 21:33:23 vanmer Exp $
 */

require_once('../../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

require_once('cf_functions.php');
require_once('display_functions.php');

$session_user_id = session_check();

$con = connect();

# Define symbols for passed data
extract ($_GET);

# If this is an existing instance we'll have an instance_id (if
# this is a new instance then we'll have an object_id but instance_id
# will be zero). So: if we have an instance_id then get object_id.
if ($instance_id) {
	$sql = "SELECT object_id, key_id, subkey_id
			FROM	cf_instances
			WHERE	instance_id = $instance_id
			AND 	record_status = 'a'";
	$rst = execute_sql($sql);
	extract($rst->fields);
}

# Get list of fields defined for this object in column order
$sql = "SELECT	field_column
		FROM 	cf_fields
		WHERE 	record_status = 'a'
		AND		object_id = $object_id
		ORDER BY field_column";
$columns = array_unique($con->GetCol($sql));

if (!$columns) {
	db_error_handler($con, "Error retrieving columns in custom_fields/edit.php");
	assert(False);
}

# Process data one display column at a time, collating the HTML
$data = array();
foreach($columns as $column) {
	# Format this column
	$data[$column] = get_edit_column($object_id, $instance_id, $column);
}

# Get object name
$sql = "SELECT	object_name
		FROM	cf_objects
		WHERE	object_id = $object_id
		AND		record_status = 'a'";
$object_name = $con->GetOne($sql);

# Generate page title
$parent_name = get_parent_name($object_id, $key_id, $subkey_id);
$page_title = "$parent_name: $object_name";

# Display
start_page($page_title, true, $msg);
?>

<div id="Main">
  <div id="Content">
	<form action=edit-2.php method=post>
      <input type=hidden name=return_url value=<?php  echo urlencode($return_url); ?>>
      <input type=hidden name=object_id value=<?php  echo $object_id; ?>>
      <input type=hidden name=key_id value=<?php  echo $key_id; ?>>
      <input type=hidden name=subkey_id value=<?php echo $subkey_id; ?>>
      <input type=hidden name=instance_id value=<?php  echo $instance_id; ?>>
	  <table class=widget cellspacing=1>
		<tr>
		  <td class=widget_header>
			<?php echo _("Details"); ?>
		  </td>
		</tr>
		<tr>
		  <td class=widget_content>
			<table border=0 cellpadding=0 cellspacing=0 width=100%>
			  <tr>
				<?php foreach ($data as $column) { ?>
				  <td width=<?php echo $column_width ?> class=clear align=left valign=top>
					<table border=0 cellpadding=0 cellspacing=0 width=100%>
					  <?php echo $column; ?>
					</table>
				  </td>
				<?php } ?>
			  </tr>
			</table>
			<p><?php  echo $profile; ?>
		  </td>
		</tr>
		 <tr>
		   <td class=widget_content_form_element colspan=2>
			 <input class=button type=submit value="Save Changes">&nbsp;
			 <input class=button type=button value="<?php echo _("Back"); ?>"
			 onclick="javascript: location.href='<?php echo $return_url; ?>';">
		   </td>
		 </tr>
	  </table>
	</form>
  </div>

		<!-- right column //-->
  <div id="Sidebar">

  </div>

</div>

<?php

end_page();

/**
 * $Log: edit.php,v $
 * Revision 1.2  2005/10/18 21:33:23  vanmer
 * - added subkey restriction to edit and edit processing pages
 *
 * Revision 1.1  2005/10/02 23:57:33  vanmer
 * - Initial Revison of the custom_fields plugin, thanks to Keith Edmunds
 *
 * Revision 1.12  2005/03/18 20:54:37  gpowers
 * - added support for inline (custom fields) info
 *
 * Revision 1.11  2005/02/18 14:15:50  braverock
 * - fix fallback default return_url to be correct when contatenated w/ http_site_root
 *   - patch supplied by Keith Edmunds
 *
 * Revision 1.10  2005/02/15 15:08:30  ycreddy
 * Included adodb-params.php for Result Set lookup based on column name
 *
 * Revision 1.9  2005/02/11 19:03:12  vanmer
 * - added check for role access before allowing user to edit item definitions
 *
 * Revision 1.8  2005/02/11 13:49:02  braverock
 * - fix handling of return_url
 * - remove references to server_info and replace with just info
 *
 * Revision 1.7  2005/02/11 00:49:11  braverock
 * - modified to correctly pass contact_id and return_url
 *
 */
?>
