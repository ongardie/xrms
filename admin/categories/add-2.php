<?php

require_once('../../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

$category_short_name = $_POST['category_short_name'];
$category_pretty_name = $_POST['category_pretty_name'];
$category_pretty_plural = $_POST['category_pretty_plural'];
$category_display_html = $_POST['category_display_html'];

$session_user_id = session_check( $this );

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

$sql = "insert into categories (category_short_name, category_pretty_name, category_pretty_plural, category_display_html) values (" . $con->qstr($category_short_name, get_magic_quotes_gpc()) . ", " . $con->qstr($category_pretty_name, get_magic_quotes_gpc()) . ", " . $con->qstr($category_pretty_plural, get_magic_quotes_gpc()) . ", " . $con->qstr($category_display_html, get_magic_quotes_gpc()) . ")";
$con->execute($sql);

$con->close();

header("Location: some.php");

?>
