<?php
/**
 * Insert a new contact into the database
 *
 * $Id: new-2.php,v 1.9 2004/06/15 14:29:00 gpowers Exp $
 */
require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

$session_user_id = session_check();

$company_id = $_POST['company_id'];
$address_id = $_POST['address_id'];
$division_id = $_POST['division_id'];
if ($division_id != '') {
    $division_str = "division_id = $division_id ,\n";
}
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

$sql = "insert into contacts set
        company_id = $company_id,
        address_id = $address_id,
        $division_str
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
        salutation = " . $con->qstr($salutation, get_magic_quotes_gpc()) . ",
        gender = " . $con->qstr($gender, get_magic_quotes_gpc()) . ",
        date_of_birth = " . $con->qstr($date_of_birth, get_magic_quotes_gpc()) . ",
        profile = " . $con->qstr($profile, get_magic_quotes_gpc()) . ",
        custom1 = " . $con->qstr($custom1, get_magic_quotes_gpc()) . ",
        custom2 = " . $con->qstr($custom2, get_magic_quotes_gpc()) . ",
        custom3 = " . $con->qstr($custom3, get_magic_quotes_gpc()) . ",
        custom4 = " . $con->qstr($custom4, get_magic_quotes_gpc()) . ",
        entered_by = $session_user_id,
        entered_at = " . time() . ",
        last_modified_at = " . time() . ",
        last_modified_by = $session_user_id"
        ;

// $con->debug=1;

$con->execute($sql);
$con->close();

header("Location: ../companies/one.php?msg=contact_added&company_id=$company_id");

/**
 * $Log: new-2.php,v $
 * Revision 1.9  2004/06/15 14:29:00  gpowers
 * - correct time formats
 *
 * Revision 1.8  2004/01/26 19:13:34  braverock
 * - added company division fields
 * - added phpdoc
 *
 */
?>
