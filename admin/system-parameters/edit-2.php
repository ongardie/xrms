<?php
/**
 * save the updated information for a single system parameter
 *
 * $Id: edit-2.php,v 1.2 2006/01/02 22:07:25 vanmer Exp $
 */

require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();

$param_id = $_POST['param_id'];
$param_value = $_POST['param_value'];

$con = get_xrms_dbconnection();

set_system_parameter($con, $param_id, $param_value);

$con->close();

header("Location: some.php");

/**
 * $Log: edit-2.php,v $
 * Revision 1.2  2006/01/02 22:07:25  vanmer
 * - changed to use centralized dbconnection function
 *
 * Revision 1.1  2004/07/14 16:23:37  maulani
 * - Add administrator capability to modify system parameters
 *
 *
 */
?>