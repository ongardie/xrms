<?php
/**
 * Database updates for transfer contact
 *
 * $Id: transfer-2.php,v 1.1 2004/06/09 19:25:11 gpowers Exp $
 */


require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

$session_user_id = session_check();

$new_company_id = $_POST['company_id'];
$contact_id = $_POST['contact_id'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
// $con->debug = 1;

    $sql = "update contacts set company_id = $new_company_id where contact_id = $contact_id";
    $con->execute($sql);

    add_audit_item($con, $session_user_id, 'transferred', 'contacts', $contact_id, 1);

$con->close();

header("Location: one.php?msg=saved&contact_id=$contact_id");


/**
 * $Log: transfer-2.php,v $
 * Revision 1.1  2004/06/09 19:25:11  gpowers
 * - database updates for transfer of contact to new company
 *
 *
 */

?>
