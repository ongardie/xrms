<?php
/**
 * Insert changes to a contact into the database.
 *
 * $Id: edit-2.php,v 1.11 2004/07/22 11:21:13 cpsource Exp $
 */

require_once('include-locations-location.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();

$contact_id = $_POST['contact_id'];
$address_id = $_POST['address_id'];
$division_id = $_POST['division_id'];
if (!$address_id) { $address_id=1; };
$first_names = $_POST['first_names'];
$last_name = $_POST['last_name'];
$summary = $_POST['summary'];
$title = $_POST['title'];
$description = $_POST['description'];
$date_of_birth = $_POST['date_of_birth'];
$gender = $_POST['gender'];
$salutation = $_POST['salutation'];
$email = $_POST['email'];
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

if ($salutation == '0') {
    $salutation = '';
}

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
// $con->debug=1;

$sql = "SELECT * FROM contacts WHERE contact_id = $contact_id";
$rst = $con->execute($sql);

$rec = array();
$rec['address_id'] = $address_id;
$rec['division_id'] = $division_id;
$rec['last_name'] = $last_name;
$rec['first_names'] = $first_names;
$rec['summary'] = $summary;
$rec['title'] = $title;
$rec['description'] = $description;
$rec['email'] = $email;
$rec['work_phone'] = $work_phone;
$rec['cell_phone'] = $cell_phone;
$rec['home_phone'] = $home_phone;
$rec['fax'] = $fax;
$rec['aol_name'] = $aol_name;
$rec['yahoo_name'] = $yahoo_name;
$rec['msn_name'] = $msn_name;
$rec['interests'] = $interests;
$rec['gender'] = $gender;
$rec['date_of_birth'] = $date_of_birth;
$rec['profile'] = $profile;
$rec['custom1'] = $custom1;
$rec['custom2'] = $custom2;
$rec['custom3'] = $custom3;
$rec['custom4'] = $custom4;
$rec['last_modified_at'] = time();
$rec['last_modified_by'] = $session_user_id;

if ($salutation != '0') {
    $rec['salutation'] = $salutation;
} else {
    $rec['salutation'] = '';
}

$upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
$con->execute($upd);

header("Location: one.php?msg=saved&contact_id=$contact_id");

/**
 * $Log: edit-2.php,v $
 * Revision 1.11  2004/07/22 11:21:13  cpsource
 * - All paths now relative to include-locations-location.inc
 *   Code cleanup for Create Contact for 'Self'
 *
 * Revision 1.10  2004/06/15 17:26:21  introspectshun
 * - Add adodb-params.php include for multi-db compatibility.
 * - Corrected order of arguments to implode() function.
 * - Now use ADODB GetInsertSQL, GetUpdateSQL and Concat functions.
 *
 * Revision 1.9  2004/06/15 14:30:20  gpowers
 * - correct time formats
 *
 * Revision 1.8  2004/02/24 19:59:39  braverock
 * - fixed salutation sql to not insert zero
 *
 * Revision 1.7  2004/02/21 00:17:33  maulani
 * If no salutation chosen, leave field blank
 *
 * Revision 1.6  2004/01/26 19:13:33  braverock
 * - added company division fields
 * - added phpdoc
 *
 */
?>
