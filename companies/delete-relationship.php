<?php
/**
 * Add Former Name
 *
 * $Id: delete-relationship.php,v 1.1 2004/05/06 13:35:24 gpowers Exp $
 */
require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

$session_user_id = session_check();

$company_to_id = $_GET['company_to_id'];
$company_from_id = $_GET['company_from_id'];
$relationship_type = $_GET['relationship_type'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
// $con->debug = 1;

$sql = "delete from company_relationship where company_from_id = " .  $con->qstr($company_from_id, get_magic_quotes_gpc())
     . " AND company_to_id = " . $con->qstr($company_to_id, get_magic_quotes_gpc())
     . " AND relationship_type = " . $con->qstr($relationship_type, get_magic_quotes_gpc());

$con->execute($sql);

add_audit_item($con, $session_user_id, 'deleted company relationship', 'companies', $company_id);

$con->close();

header("Location: relationships.php?company_id=$company_from_id");

/**
 * $Log: delete-relationship.php,v $
 * Revision 1.1  2004/05/06 13:35:24  gpowers
 * This implements the deletion of relationships.
 *
 * Revision 1.2  2004/03/26 20:55:59  maulani
 * - Add audit trail to company-related items
 * - Add phpdoc
 *
 *
 */
?>
