<?php

require_once('vars.php');
require_once('utils-interface.php');
require_once('utils-misc.php');
require_once('adodb/adodb.inc.php');

$session_user_id = session_check();
$msg = $_GET['msg'];

$email_template_id = $_POST['email_template_id'];
$email_template_title = (strlen($_POST['email_template_title']) > 0) ? $_POST['email_template_title'] : "Template $email_template_id";
$email_template_body = $_POST['email_template_body'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
$con->execute("update users set last_hit = " . $con->dbtimestamp(mktime()) . " where user_id = $session_user_id");

$sql = "update email_templates set email_template_title = " . $con->qstr($email_template_title, get_magic_quotes_gpc()) . ", email_template_body = " . $con->qstr($email_template_body, get_magic_quotes_gpc()) . " where email_template_id = $email_template_id";

$con->execute($sql);

$con->close();

header("Location: email-2.php?email_template_id=$email_template_id&msg=saved");

?>