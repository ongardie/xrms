<?php
/**
 *
 *
 * $Id: save-as-new-template.php,v 1.1 2008/03/15 16:54:31 randym56 Exp $
 */

require_once('include-locations-location.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();
$msg = $_GET['msg'];

$email_template_title = $_POST['email_template_title'];
$email_template_body = $_POST['email_template_body'];

$con = get_xrms_dbconnection();

//save to database
$rec = array();
$rec['email_template_title'] = $email_template_title;
$rec['email_template_body'] = $email_template_body;

$tbl = 'email_templates';
$ins = $con->GetInsertSQL($tbl, $rec, get_magic_quotes_gpc());
$con->execute($ins);

$email_template_id = $con->insert_id();

$con->close();

header("Location: email-2.php?email_template_id=$email_template_id");

/**
 * $Log: save-as-new-template.php,v $
 * Revision 1.1  2008/03/15 16:54:31  randym56
 * Updated SMTPs to allow for individual user SMTP addressing - requires installation and activation of mcrypt in PHP - follow README.txt instructions
 *
 */

?>
