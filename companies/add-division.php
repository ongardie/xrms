<?php
/**
 * Add a division to a company
 *
 * $Id: add-division.php,v 1.1 2004/01/26 19:18:02 braverock Exp $
 */

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

$session_user_id = session_check();

$company_id = $_POST['company_id'];
$division_name = $_POST['division_name'];
$description = $_POST['description'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

//$con->debug = 1;

$sql = "insert into company_division set
        company_id = $company_id,
        division_name = " . $con->qstr($division_name, get_magic_quotes_gpc()) . ",
        description = " . $con->qstr($description, get_magic_quotes_gpc()) . ",
        entered_at = " . $con->dbtimestamp(mktime()) . ",
        entered_by = $session_user_id ,
        last_modified_at = " . $con->dbtimestamp(mktime()) . ",
        last_modified_by = $session_user_id
      ";

$con->execute($sql);
$con->close();

header("Location: divisions.php?msg=address_added&company_id=$company_id");

/**
 * $Log: add-division.php,v $
 * Revision 1.1  2004/01/26 19:18:02  braverock
 * - added company division pages and fields
 * - added phpdoc
 *
 */
?>
