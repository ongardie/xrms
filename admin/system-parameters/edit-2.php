<?php
/**
 * save the updated information for a single system parameter
 *
 * $Id: edit-2.php,v 1.1 2004/07/14 16:23:37 maulani Exp $
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

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

set_system_parameter($con, $param_id, $param_value);

$con->close();

header("Location: some.php");

/**
 * $Log: edit-2.php,v $
 * Revision 1.1  2004/07/14 16:23:37  maulani
 * - Add administrator capability to modify system parameters
 *
 *
 */
?>