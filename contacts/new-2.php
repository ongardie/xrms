<?php
/**
 * Insert a new contact into the database
 *
 * $Id: new-2.php,v 1.12 2004/07/08 19:47:50 neildogg Exp $
 */
require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();

$company_id = $_POST['company_id'];
$address_id = $_POST['address_id'];
$division_id = $_POST['division_id'];
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
// If salutation is 0, make sure you replace it with an empty string
if(!$salutation) {
    $salutation = "";
}

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

//save to database
$rec = array();
$rec['company_id'] = $company_id;
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
$rec['salutation'] = $salutation;
$rec['gender'] = $gender;
$rec['date_of_birth'] = $date_of_birth;
$rec['profile'] = $profile;
$rec['custom1'] = $custom1;
$rec['custom2'] = $custom2;
$rec['custom3'] = $custom3;
$rec['custom4'] = $custom4;
$rec['entered_by'] = $session_user_id;
$rec['entered_at'] = time();
$rec['last_modified_at'] = time();
$rec['last_modified_by'] = $session_user_id;

$tbl = 'contacts';
$ins = $con->GetInsertSQL($tbl, $rec, get_magic_quotes_gpc());
$con->execute($ins);

$con->close();

header("Location: ../companies/one.php?msg=contact_added&company_id=$company_id");

/**
 * $Log: new-2.php,v $
 * Revision 1.12  2004/07/08 19:47:50  neildogg
 * - Salutation was inserting 0 on a null salutation choice
 *
 * Revision 1.11  2004/07/07 21:59:47  introspectshun
 * - Now passes a table name instead of a recordset into GetInsertSQL
 *
 * Revision 1.10  2004/06/15 17:26:21  introspectshun
 * - Add adodb-params.php include for multi-db compatibility.
 * - Corrected order of arguments to implode() function.
 * - Now use ADODB GetInsertSQL, GetUpdateSQL and Concat functions.
 *
 * Revision 1.9  2004/06/15 14:29:00  gpowers
 * - correct time formats
 *
 * Revision 1.8  2004/01/26 19:13:34  braverock
 * - added company division fields
 * - added phpdoc
 *
 */
?>
