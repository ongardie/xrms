<?php
/**
 * Add Relationship
 *
 * $Id: add-relationship.php,v 1.8 2004/07/05 21:17:01 introspectshun Exp $
 *
 * @todo put back in established at date parsing
 */

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

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

$sql = "SELECT * FROM relationships WHERE 1 = 2"; //select empty record as placeholder
$rst = $con->execute($sql);

$rec = array();
$rec['from_what_id'] = $from_what_id;
$rec['to_what_id'] = $to_what_id;
$rec['relationship_type_id'] = $relationship_type_id;
$rec['established_at'] = time();

$ins = $con->GetInsertSQL($rst, $rec, get_magic_quotes_gpc());
$con->execute($ins);

add_audit_item($con, $session_user_id, 'created', 'relationships', $company_id, 1);

$con->close();

header("Location: relationships.php?company_id=$company_id");

/**
 * $Log: add-relationship.php,v $
 * Revision 1.8  2004/07/05 21:17:01  introspectshun
 * - Now uses GetInsertSQL
 * - Updated add_audit_item to reflect new relationship table
 *
 * Revision 1.7  2004/07/01 19:49:13  braverock
 * - add new configurable relationships code
 *   - adapted from patches submitted by Neil Roberts
 *
 * Revision 1.6  2004/06/15 14:17:01  gpowers
 * - correct time formats
 *
 * Revision 1.5  2004/06/12 17:10:24  gpowers
 * - removed DBTimeStamp() calls for compatibility with GetInsertSQL() and
 *   GetUpdateSQL()
 *
 * Revision 1.4  2004/06/12 05:03:16  introspectshun
 * - Now use ADODB GetInsertSQL, GetUpdateSQL, date and Concat functions.
 * - Corrected order of arguments to implode() function.
 *
 * Revision 1.3  2004/05/10 13:09:14  maulani
 * - add level to audit trail
 *
 * Revision 1.2  2004/03/26 20:55:59  maulani
 * - Add audit trail to company-related items
 * - Add phpdoc
 *
 *
 */
?>