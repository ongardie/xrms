<?php
/**
 * Delete a Rating by setting the rating_record_status to 'd'
 *
 * $Id: delete.php,v 1.2 2004/02/14 15:41:12 braverock Exp $
 */
require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

$session_user_id = session_check();

$rating_id = $_POST['rating_id'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

$sql = "update ratings set rating_record_status = 'd' where rating_id = $rating_id";
$con->execute($sql);

$con->close();

header("Location: some.php");

/**
 * $Log: delete.php,v $
 * Revision 1.2  2004/02/14 15:41:12  braverock
 * - add phpdoc
 *
 */
?>