<?php
/**
 * Add Category
 *
 * $Id: add-category.php,v 1.3 2004/06/12 05:03:16 introspectshun Exp $
 */
require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

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

$sql = "SELECT * FROM entity_category_map WHERE 1 = 2"; //select empty record as placeholder
$rst = $con->execute($sql);

$rec = array();
$rec['category_id'] = $category_id;
$rec['on_what_table'] = 'companies';
$rec['on_what_id'] = $company_id;

$ins = $con->GetInsertSQL($rst, $rec, get_magic_quotes_gpc());
$con->execute($ins);

add_audit_item($con, $session_user_id, 'created', 'entity_category_map', $category_id, 1);

$con->close();

header("Location: categories.php?company_id=$company_id");

/**
 * $Log: add-category.php,v $
 * Revision 1.3  2004/06/12 05:03:16  introspectshun
 * - Now use ADODB GetInsertSQL, GetUpdateSQL, date and Concat functions.
 * - Corrected order of arguments to implode() function.
 *
 * Revision 1.2  2004/05/10 13:03:04  maulani
 * - add phpdoc
 * - add audit trail entry
 *
 *
 */
?>