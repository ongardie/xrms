<?php
/**
 * Add Relationship
 *
 * $Id: add-relationship.php,v 1.7 2004/07/01 19:49:13 braverock Exp $
 *
 * @todo put back in established at date parsing
 * @todo modify to use GetInsertSQL
 */

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

$session_user_id = session_check();

$company_id = $_POST['company_id'];
$relationship_type_id = $_POST['relationship_type_id'];
$from_what_id = $_POST['company_id'];
$to_what_id = $_POST['to_what_id'];

list($from_or_to, $relationship_type_id) = explode('_', $relationship_type_id);

if($from_or_to == "to") {
    $temporary_id = $from_what_id;
    $from_what_id = $to_what_id;
    $to_what_id = $temporary_id;
}

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
// $con->debug = 1;

$sql = "insert into relationships (from_what_id, to_what_id, relationship_type_id, established_at)
    values (" . $from_what_id . ", " . $to_what_id . ", " . $relationship_type_id . ", now())";
$con->execute($sql);

add_audit_item($con, $session_user_id, 'created', 'company_relationship', $company_id, 1);

$con->close();

header("Location: relationships.php?company_id=$company_id");

/**
 * $Log: add-relationship.php,v $
 * Revision 1.7  2004/07/01 19:49:13  braverock
 * - add new configurable relationships code
 *   - adapted from patches submitted by Neil Roberts
 *
 * Revision 1.3  2004/05/10 13:09:14  maulani
 * - add level to audit trail
 *
 * Revision 1.2  2004/03/26 20:55:59  maulani
 * - Add audit trail to company-related items
 * - Add phpdoc
 */
?>