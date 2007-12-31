<?php
/**
 * Edit Category Scopes
 *
 * $Id: edit-2.php,v 1.1 2007/12/31 19:05:25 randym56 Exp $
 */

require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check( 'Admin' );

$category_scope_id = $_POST['category_scope_id'];
$category_scope_short_name = $_POST['category_scope_short_name'];
$category_scope_pretty_name = $_POST['category_scope_pretty_name'];
$category_scope_pretty_plural = $_POST['category_scope_pretty_plural'];
$category_scope_display_html = $_POST['category_scope_display_html'];
$on_what_table = $_POST['on_what_table'];

$con = get_xrms_dbconnection();

$sql = "SELECT * FROM category_scopes WHERE category_scope_id = $category_scope_id";
$rst = $con->execute($sql);

$rec = array();
$rec['category_scope_short_name'] = $category_scope_short_name;
$rec['category_scope_pretty_name'] = $category_scope_pretty_name;
$rec['category_scope_pretty_plural'] = $category_scope_pretty_plural;
$rec['category_scope_display_html'] = $category_scope_display_html;
$rec['on_what_table'] = $on_what_table;

$upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
$con->execute($upd);

$con->close();

header("Location: some.php");

/**
 * $Log: edit-2.php,v $
 * Revision 1.1  2007/12/31 19:05:25  randym56
 * Function to add/edit Category Scopes table
 *
 * Revision v 1.0 2007/12/31 11:09:59 randym56 Exp $
 * Add function
 *
 */
?>
