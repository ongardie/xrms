<?php
/**
 * Delete (set status to 'd') a single case priority
 *
 * $Id: delete.php,v 1.2 2004/03/22 02:14:45 braverock Exp $
 */

//include required files

require_once('../../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

$session_user_id = session_check();

$case_priority_id = $_POST['case_priority_id'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

$sql = "update case_priorities set case_priority_record_status = 'd' where case_priority_id = $case_priority_id";
$con->execute($sql);

$con->close();

header("Location: some.php");

/**
 * $Log: delete.php,v $
 * Revision 1.2  2004/03/22 02:14:45  braverock
 * - debug SF bug 906413
 * - add phpdoc
 *
 */
?>
