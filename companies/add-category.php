<?php
/**
 * Add Category
 *
 * $Id: add-category.php,v 1.2 2004/05/10 13:03:04 maulani Exp $
 */
require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

$session_user_id = session_check();
$msg = $_GET['msg'];

$company_id = $_GET['company_id'];
$category_id = $_GET['category_id'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
// $con->debug = 1;

$sql = "delete from entity_category_map 
where category_id = $category_id 
and on_what_table = 'companies' 
and on_what_id = $company_id";
$con->execute($sql);

$sql = "insert into entity_category_map (category_id, on_what_table, on_what_id) values ($category_id, 'companies', $company_id)";
$con->execute($sql);

add_audit_item($con, $session_user_id, 'created', 'entity_category_map', $category_id, 1);

$con->close();

header("Location: categories.php?company_id=$company_id");

/**
 * $Log: add-category.php,v $
 * Revision 1.2  2004/05/10 13:03:04  maulani
 * - add phpdoc
 * - add audit trail entry
 *
 *
 */
?>