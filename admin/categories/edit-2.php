<?php

require_once('../../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$category_id = $_POST['category_id'];
$category_short_name = $_POST['category_short_name'];
$category_pretty_name = $_POST['category_pretty_name'];
$category_pretty_plural = $_POST['category_pretty_plural'];
$category_display_html = $_POST['category_display_html'];

$session_user_id = session_check( 'Admin' );

$con = get_xrms_dbconnection();

$sql = "SELECT * FROM categories WHERE category_id = $category_id";
$rst = $con->execute($sql);

$rec = array();
$rec['category_short_name'] = $category_short_name;
$rec['category_pretty_name'] = $category_pretty_name;
$rec['category_pretty_plural'] = $category_pretty_plural;
$rec['category_display_html'] = $category_display_html;

$upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
$con->execute($upd);

$con->close();

header("Location: some.php");

?>
