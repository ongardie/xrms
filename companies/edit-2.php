<?php

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'utils-accounting.php');

$session_user_id = session_check();

$company_id = $_POST['company_id'];
$company_name = $_POST['company_name'];
$company_code = $_POST['company_code'];
$crm_status_id = $_POST['crm_status_id'];
$company_source_id = $_POST['company_source_id'];
$industry_id = $_POST['industry_id'];
$user_id = $_POST['user_id'];
$phone = $_POST['phone'];
$phone2 = $_POST['phone2'];
$fax = $_POST['fax'];
$url = $_POST['url'];
$address = $_POST['address'];
$city = $_POST['city'];
$state = $_POST['state'];
$postal_code = $_POST['postal_code'];
$country = $_POST['country'];
$employees = $_POST['employees'];
$revenue = $_POST['revenue'];
$profile = $_POST['profile'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

$sql = "update companies set crm_status_id = $crm_status_id, company_source_id = $company_source_id, industry_id = $industry_id, user_id = $user_id, company_name = " . $con->qstr($company_name, get_magic_quotes_gpc()) . ", company_code = " . $con->qstr($company_code, get_magic_quotes_gpc()) . ", phone = " . $con->qstr($phone, get_magic_quotes_gpc()) . ", phone2 = " . $con->qstr($phone2, get_magic_quotes_gpc()) . ", fax = " . $con->qstr($fax, get_magic_quotes_gpc()) . ", url = " . $con->qstr($url, get_magic_quotes_gpc()) . ", city = " . $con->qstr($city, get_magic_quotes_gpc()) . ", state = " . $con->qstr($state, get_magic_quotes_gpc()) . ", postal_code = " . $con->qstr($postal_code, get_magic_quotes_gpc()). ", country = " . $con->qstr($country, get_magic_quotes_gpc()) . ", employees = " . $con->qstr($employees, get_magic_quotes_gpc()) . ", revenue = " . $con->qstr($revenue, get_magic_quotes_gpc()) . ", profile = " . $con->qstr($profile, get_magic_quotes_gpc()) . " WHERE company_id = $company_id";
$con->execute($sql);

header("Location: one.php?msg=saved&company_id=$company_id");

?>