<?php
/**
 * Add Relationship
 *
 * $Id: add-relationship.php,v 1.4 2004/06/12 05:03:16 introspectshun Exp $
 */
require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();

$company_id = $_POST['company_id'];
$relation = $_POST['relation'];
if (($relation + 2) % 2) {
  $relation2=$relation-1;
} else {
  $relation2=$relation+1;
} 
$company2_id = $_POST['company2_id'];

$relation_array = array("Acquired", "Acquired by", "Consultant for", "Retains consultant", "Manufactures for", "Uses manufacturer", "Subsidiary of", "Parent company of", "Alternate address for", "Parent address for");

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
// $con->debug = 1;

$sql = "SELECT * FROM company_relationship WHERE 1 = 2"; //select empty record as placeholder
$rst = $con->execute($sql);

$rec = array();
$rec['company_from_id'] = $company_id;
$rec['relationship_type'] = $relation_array[$relation];
$rec['company_to_id'] = $company2_id;
$rec['established_at'] = $con->DBTimestamp(mktime());

$ins = $con->GetInsertSQL($rst, $rec, get_magic_quotes_gpc());
$con->execute($ins);

add_audit_item($con, $session_user_id, 'created', 'company_relationship', $company_id, 1);


$sql = "SELECT * FROM company_relationship WHERE 1 = 2"; //select empty record as placeholder
$rst = $con->execute($sql);

$rec = array();
$rec['company_from_id'] = $company2_id;
$rec['relationship_type'] = $relation_array[$relation2];
$rec['company_to_id'] = $company_id;
$rec['established_at'] = $con->dbtimestamp(mktime());

$ins = $con->GetInsertSQL($rst, $rec, get_magic_quotes_gpc());
$con->execute($ins);

add_audit_item($con, $session_user_id, 'created', 'company_relationship', $company2_id, 1);

$con->close();

header("Location: relationships.php?company_id=$company_id");

/**
 * $Log: add-relationship.php,v $
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