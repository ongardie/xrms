<?php

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();
$msg = $_GET['msg'];

$email_template_title = $_POST['email_template_title'];
$email_template_body = $_POST['email_template_body'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

$sql = "SELECT * FROM email_templates WHERE 1 = 2"; //select empty record as placeholder
$rst = $con->execute($sql);

$rec = array();
$rec['email_template_title'] = $email_template_title;
$rec['email_template_body'] = $email_template_body;

$ins = $con->GetInsertSQL($rst, $rec, get_magic_quotes_gpc());
$con->execute($ins);

$email_template_id = $con->insert_id();

$con->close();

header("Location: email-2.php?email_template_id=$email_template_id");

?>