<?php
/**
 * Delete a division by setting its status
 *
 * $Id: delete-division.php,v 1.1 2004/01/26 19:18:02 braverock Exp $
 */

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'utils-accounting.php');

$session_user_id = session_check();

$company_id = $_GET['company_id'];
$division_id = $_GET['division_id'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

$sql = "update company_division set
        division_record_status = 'd',
        last_modified_at = " . $con->dbtimestamp(mktime()) . ",
        last_modified_by = $session_user_id
        where division_id = $division_id";

$con->execute($sql);

$con->close();

header("Location: divisions.php?msg=address_deleted&company_id=$company_id");

/**
 * $Log: delete-division.php,v $
 * Revision 1.1  2004/01/26 19:18:02  braverock
 * - added company division pages and fields
 * - added phpdoc
 *
 */
?>