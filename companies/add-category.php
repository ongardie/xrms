<?php
/**
 * Add Category
 *
 * $Id: add-category.php,v 1.6 2006/01/02 22:56:26 vanmer Exp $
 */
require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();
$msg = isset($_GET['msg']) ? $_GET['msg'] : '';

$company_id = $_GET['company_id'];
$category_id = $_GET['category_id'];

$con = get_xrms_dbconnection();
// $con->debug = 1;

$sql = "delete from entity_category_map 
where category_id = $category_id 
and on_what_table = 'companies' 
and on_what_id = $company_id";
$con->execute($sql);

//save to database
$rec = array();
$rec['category_id'] = $category_id;
$rec['on_what_table'] = 'companies';
$rec['on_what_id'] = $company_id;

$tbl = 'entity_category_map';
$ins = $con->GetInsertSQL($tbl, $rec, get_magic_quotes_gpc());
$con->execute($ins);

add_audit_item($con, $session_user_id, 'created', 'entity_category_map', $category_id, 1);

$con->close();

header("Location: categories.php?company_id=$company_id");

/**
 * $Log: add-category.php,v $
 * Revision 1.6  2006/01/02 22:56:26  vanmer
 * - changed to use centralized dbconnection function
 *
 * Revision 1.5  2004/07/30 11:23:38  cpsource
 * - Do standard msg processing
 *   Default use_pretty_address in new-2.php set to null
 *
 * Revision 1.4  2004/07/07 21:53:13  introspectshun
 * - Now passes a table name instead of a recordset into GetInsertSQL
 *
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