<?php
/**
 * Save changes to divisions
 *
 * $Id: edit-division-2.php,v 1.1 2004/01/26 19:18:02 braverock Exp $
 */

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

$session_user_id = session_check();

$division_id = $_POST['division_id'];
$company_id = $_POST['company_id'];
$division_name = $_POST['division_name'];
$description = $_POST['description'];

$use_pretty_address = ($use_pretty_address == 'on') ? "'t'" : "'f'";

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

//$con->debug=1;

$sql = "update company_division set
        division_id = $division_id,
        division_name = " . $con->qstr($division_name, get_magic_quotes_gpc()) . ",
        description = " . $con->qstr($description, get_magic_quotes_gpc()) . ",
        last_modified_at = " . $con->dbtimestamp(mktime()) . ",
        last_modified_by = $session_user_id
        where division_id = $division_id";

$con->execute($sql);

header("Location: divisions.php?msg=saved&company_id=$company_id");

/**
 * $Log: edit-division-2.php,v $
 * Revision 1.1  2004/01/26 19:18:02  braverock
 * - added company division pages and fields
 * - added phpdoc
 *
 */
?>