<?php

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

$session_user_id = session_check();
$msg = $_GET['msg'];

$email_template_id = $_POST['email_template_id'];
$email_template_title = (strlen($_POST['email_template_title']) > 0) ? $_POST['email_template_title'] : "Template $email_template_id";
$email_template_body = $_POST['email_template_body'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

$sql = "update email_templates set email_template_title = " . $con->qstr($email_template_title, get_magic_quotes_gpc()) . ", email_template_body = " . $con->qstr($email_template_body, get_magic_quotes_gpc()) . " where email_template_id = $email_template_id";

$con->execute($sql);

$con->close();

header("Location: email-2.php?email_template_id=$email_template_id&msg=saved");

?>