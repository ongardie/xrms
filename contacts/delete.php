<?php

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

$session_user_id = session_check();
$contact_id = $_GET['contact_id'];
$company_id = $_GET['company_id'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

$sql = "update contacts set contact_record_status = 'd' where contact_id = $contact_id";
$con->execute($sql);

$con->close();

header("Location: {$http_site_root}/companies/one.php?company_id=$company_id&msg=contact_deleted");

?>