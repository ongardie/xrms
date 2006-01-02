<?php

require_once('../../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check( 'Admin' );

$company_type_id = $_POST['company_type_id'];
$company_type_short_name = $_POST['company_type_short_name'];
$company_type_pretty_name = $_POST['company_type_pretty_name'];
$company_type_pretty_plural = $_POST['company_type_pretty_plural'];
$company_type_display_html = $_POST['company_type_display_html'];

$con = get_xrms_dbconnection();

$sql = "SELECT * FROM company_types WHERE company_type_id = $company_type_id";
$rst = $con->execute($sql);

$rec = array();
$rec['company_type_short_name'] = $company_type_short_name;
$rec['company_type_pretty_name'] = $company_type_pretty_name;
$rec['company_type_pretty_plural'] = $company_type_pretty_plural;
$rec['company_type_display_html'] = $company_type_display_html;

$upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
$con->execute($upd);

header("Location: some.php");

?>
