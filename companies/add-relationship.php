<?php

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

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

$sql = "insert into company_relationship (company_from_id, relationship_type, company_to_id, established_at) values (" . $company_id . ", '" . $relation_array[$relation] . "', " . $company2_id . ", now())";
$con->execute($sql);


$sql = "insert into company_relationship (company_from_id, relationship_type, company_to_id, established_at) values (" . $company2_id . ", '" . $relation_array[$relation2] . "', " . $company_id . ", now())";
$con->execute($sql);

$con->close();

header("Location: relationships.php?company_id=$company_id");

?>