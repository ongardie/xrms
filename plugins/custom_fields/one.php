<?php
/**
 * Details about one item
 *
 * $Id: one.php,v 1.1 2005/10/02 23:57:33 vanmer Exp $
 *
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

# Make sure we have an instance_id
assert ($instance_id);

# Get object_id and key_id
$sql = "SELECT	object_id, key_id
		FROM	cf_instances
		WHERE	instance_id = $instance_id
		AND		record_status = 'a'";
$rst = execute_sql($sql);
extract ($rst->fields);

# Get object name
$sql = "SELECT	object_name
		FROM	cf_objects
		WHERE	object_id = $object_id
		AND		record_status = 'a'";
$object_name = $con->GetOne($sql);

# Generate page title
$parent_name = get_parent_name($object_id, $key_id);
$page_title = "$parent_name: $object_name";

# Display
start_page($page_title, true, $msg);
?>

<div id="Main">

<?php
echo get_instance_detail ($instance_id, $object_id, $return_url);
?>
		<!-- right column //-->
	<div id="Sidebar">

	</div>

</div>

<?php

end_page();

/**
 * $Log: one.php,v $
 * Revision 1.1  2005/10/02 23:57:33  vanmer
 * - Initial Revison of the custom_fields plugin, thanks to Keith Edmunds
 *
 * Revision 1.15  2005/04/01 21:50:14  ycreddy
 * Added changes to handle  multiple instance values for an element type
 *
 * Revision 1.14  2005/03/18 20:54:37  gpowers
 * - added support for inline (custom fields) info
 *
 * Revision 1.13  2005/02/15 15:10:13  ycreddy
 * Included adodb-params.php so that Column name based lookup on a Result Set works properly on SQL Server
 *
 * Revision 1.12  2005/02/11 13:55:14  braverock
 * - fix handling of return_url
 * - remove references to server_info and replace with just info
 *
 * Revision 1.11  2005/02/11 00:49:11  braverock
 * - modified to correctly pass contact_id and return_url
 *
 */
?>
