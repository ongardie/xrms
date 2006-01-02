<?php
/**
 * Add Former Name
 *
 * $Id: delete-relationship.php,v 1.4 2006/01/02 22:56:26 vanmer Exp $
 */
require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

$session_user_id = session_check();

$to_what_id = $_GET['to_what_id'];
$from_what_id = $_GET['from_what_id'];
$relationship_type_id = $_GET['relationship_type_id'];
$company_id = $_GET['company_id'];

$con = get_xrms_dbconnection();
// $con->debug = 1;

$sql = "delete from relationships where from_what_id = " .  $con->qstr($from_what_id, get_magic_quotes_gpc())
     . " AND to_what_id = " . $con->qstr($to_what_id, get_magic_quotes_gpc())
     . " AND relationship_type_id = " . $con->qstr($relationship_type_id, get_magic_quotes_gpc());

$con->execute($sql);

$con->close();

header("Location: relationships.php?company_id=$company_id");

/**
 * $Log: delete-relationship.php,v $
 * Revision 1.4  2006/01/02 22:56:26  vanmer
 * - changed to use centralized dbconnection function
 *
 * Revision 1.3  2004/07/01 19:49:13  braverock
 * - add new configurable relationships code
 *   - adapted from patches submitted by Neil Roberts
 *
 * Revision 1.2  2004/05/10 13:09:14  maulani
 * - add level to audit trail
 *
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
