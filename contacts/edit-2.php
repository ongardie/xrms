<?php

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

$session_user_id = session_check();

$contact_id = $_POST['contact_id'];
$address_id = $_POST['address_id'];
if (!$address_id) { $address_id=1; };
$first_names = $_POST['first_names'];
$last_name = $_POST['last_name'];
$summary = $_POST['summary'];
$title = $_POST['title'];
$description = $_POST['description'];
$email = $_POST['email'];
$work_phone = $_POST['work_phone'];
$cell_phone = $_POST['cell_phone'];
$home_phone = $_POST['home_phone'];
$fax = $_POST['fax'];
$aol_name = $_POST['aol_name'];
$yahoo_name = $_POST['yahoo_name'];
$msn_name = $_POST['msn_name'];
$interests = $_POST['interests'];
$custom1 = $_POST['custom1'];
$custom2 = $_POST['custom2'];
$custom3 = $_POST['custom3'];
$custom4 = $_POST['custom4'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

$sql = "update contacts set address_id = $address_id,
last_name = " . $con->qstr($last_name, get_magic_quotes_gpc()) . ",
first_names = " . $con->qstr($first_names, get_magic_quotes_gpc()) . ",
summary = " . $con->qstr($summary, get_magic_quotes_gpc()) . ",
title = " . $con->qstr($title, get_magic_quotes_gpc()) . ",
description = " . $con->qstr($description, get_magic_quotes_gpc()) . ",
email = " . $con->qstr($email, get_magic_quotes_gpc()) . ",
work_phone = " . $con->qstr($work_phone, get_magic_quotes_gpc()) . ",
cell_phone = " . $con->qstr($cell_phone, get_magic_quotes_gpc()) . ",
home_phone = " . $con->qstr($home_phone, get_magic_quotes_gpc()) . ",
fax = " . $con->qstr($fax, get_magic_quotes_gpc()) . ",
aol_name = " . $con->qstr($aol_name, get_magic_quotes_gpc()) . ",
yahoo_name = " . $con->qstr($yahoo_name, get_magic_quotes_gpc()) . ",
msn_name = " . $con->qstr($msn_name, get_magic_quotes_gpc()) . ",
interests = " . $con->qstr($interests, get_magic_quotes_gpc()) . ",
custom1 = " . $con->qstr($custom1, get_magic_quotes_gpc()) . ",
custom2 = " . $con->qstr($custom2, get_magic_quotes_gpc()) . ",
custom3 = " . $con->qstr($custom3, get_magic_quotes_gpc()) . ",
custom4 = " . $con->qstr($custom4, get_magic_quotes_gpc()) . ",
last_modified_at = " . $con->dbtimestamp(mktime()) . ",
last_modified_by = $session_user_id
where contact_id = $contact_id";


$con->execute($sql);

header("Location: one.php?msg=saved&contact_id=$contact_id");

?>