<?php

require_once('../../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$category_short_name = $_POST['category_short_name'];
$category_pretty_name = $_POST['category_pretty_name'];
$category_pretty_plural = $_POST['category_pretty_plural'];
$category_display_html = $_POST['category_display_html'];

$session_user_id = session_check( 'Admin' );

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

//save to database
$rec = array();
$rec['category_short_name'] = $category_short_name;
$rec['category_pretty_name'] = $category_pretty_name;
$rec['category_pretty_plural'] = $category_pretty_plural;
$rec['category_display_html'] = $category_display_html;

$tbl = "categories";
$ins = $con->GetInsertSQL($tbl, $rec, get_magic_quotes_gpc());
$con->execute($ins);

$con->close();

header("Location: some.php");

?>
