<?php

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

$session_user_id = session_check();

$company_id = $_POST['company_id'];
$address_id = $_POST['address_id'];
$salutation = $_POST['salutation'];
$last_name = $_POST['last_name'];
$first_names = $_POST['first_names'];
$gender = $_POST['gender'];
$date_of_birth = $_POST['date_of_birth'];
$summary = $_POST['summary'];
$title = $_POST['title'];
$description = $_POST['description'];
$email = $_POST['email'];
$email2 = $_POST['email2'];
$work_phone = $_POST['work_phone'];
$cell_phone = $_POST['cell_phone'];
$home_phone = $_POST['home_phone'];
$fax = $_POST['fax'];
$aol_name = $_POST['aol_name'];
$yahoo_name = $_POST['yahoo_name'];
$msn_name = $_POST['msn_name'];
$interests = $_POST['interests'];
$profile = $_POST['profile'];
$custom1 = $_POST['custom1'];
$custom2 = $_POST['custom2'];
$custom3 = $_POST['custom3'];
$custom4 = $_POST['custom4'];

$last_name = (strlen($last_name) > 0) ? $last_name : "[last name]";
$first_names = (strlen($first_names) > 0) ? $first_names : "[first names]";

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

$sql = "insert into contacts (company_id, address_id, salutation, last_name, first_names, gender, date_of_birth, summary, title, description, email, work_phone, cell_phone, home_phone, fax, aol_name, yahoo_name, msn_name, interests, profile, custom1, custom2, custom3, custom4, entered_at, entered_by, last_modified_at, last_modified_by) values ($company_id, $address_id, " . $con->qstr($salutation, get_magic_quotes_gpc()) . ", " . $con->qstr($last_name, get_magic_quotes_gpc()) . ", " . $con->qstr($first_names, get_magic_quotes_gpc()) . ", " . $con->qstr($gender, get_magic_quotes_gpc()) . ", " . $con->qstr($date_of_birth, get_magic_quotes_gpc()) . ", " . $con->qstr($summary, get_magic_quotes_gpc()) . ", " . $con->qstr($title, get_magic_quotes_gpc()) . ", " . $con->qstr($description, get_magic_quotes_gpc()) . ", " . $con->qstr($email, get_magic_quotes_gpc()) . ", " . $con->qstr($work_phone, get_magic_quotes_gpc()) . ", " . $con->qstr($cell_phone, get_magic_quotes_gpc()) . ", " . $con->qstr($home_phone, get_magic_quotes_gpc()) . ", " . $con->qstr($fax, get_magic_quotes_gpc()) . ", " . $con->qstr($aol_name, get_magic_quotes_gpc()) . ", " . $con->qstr($yahoo_name, get_magic_quotes_gpc()) . ", " . $con->qstr($msn_name, get_magic_quotes_gpc()) . ", " . $con->qstr($interests, get_magic_quotes_gpc()) . ", " . $con->qstr($profile, get_magic_quotes_gpc()) . ", " . $con->qstr($custom1, get_magic_quotes_gpc()) . ", " . $con->qstr($custom2, get_magic_quotes_gpc()) . ", " . $con->qstr($custom3, get_magic_quotes_gpc()) . ", " . $con->qstr($custom4, get_magic_quotes_gpc()) . ", " . $con->dbtimestamp(mktime()) . ", $session_user_id, " . $con->dbtimestamp(mktime()) . ", $session_user_id)";

$con->execute($sql);
$con->close();

header("Location: ../companies/one.php?msg=contact_added&company_id=$company_id");

?>