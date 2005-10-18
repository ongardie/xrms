<?php
/**
 * Insert item details into the database
 *
 * $Id: edit-2.php,v 1.2 2005/10/18 21:33:23 vanmer Exp $
 */
require_once('../../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'utils-accounting.php');

require_once('cf_functions.php');
require_once('display_functions.php');

$session_user_id = session_check();

global $http_site_root;

$con = connect();

# Get values from $_POST array
extract($_POST);
$values = $_POST['fields'];

# We must have object_id and key_id
assert($object_id);
assert($key_id);

save_values ($values, $instance_id, $object_id, $key_id, $subkey_id);

$return_url = urldecode($return_url);
header("Location: $return_url");

/**
 * $Log: edit-2.php,v $
 * Revision 1.2  2005/10/18 21:33:23  vanmer
 * - added subkey restriction to edit and edit processing pages
 *
 * Revision 1.1  2005/10/02 23:57:33  vanmer
 * - Initial Revison of the custom_fields plugin, thanks to Keith Edmunds
 *
 * Revision 1.9  2005/03/18 20:54:37  gpowers
 * - added support for inline (custom fields) info
 *
 * Revision 1.8  2005/02/11 00:49:11  braverock
 * - modified to correctly pass contact_id and return_url
 *
 */
?>
