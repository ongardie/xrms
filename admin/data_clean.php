<?php
/**
 * install/data_clean.php - Cleanup the database
 *
 * When coding this file, it is important that everything only happen after
 * a test.  This file must be non-destructive and only make the changes that
 * must be made.
 *
 * @author Beth Macknik
 * $Id: data_clean.php,v 1.3 2004/04/13 15:47:12 maulani Exp $
 */

// where do we include from
require_once('../include-locations.inc');

// get required common files
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

// vars.php sets all of the installation-specific variables
require_once($include_directory . 'vars.php');

$session_user_id = session_check();

// make a database connection
$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

$msg = '';

// Make sure that there is a last name for every contact
$sql = "update contacts set last_name='[last name]' where last_name=''";
$rst = $con->execute($sql);

// Make sure that there is a first name for every contact
$sql = "update contacts set first_names='[first names]' where first_names=''";
$rst = $con->execute($sql);

// There needs to be at least one contact for each company
$sql = "SELECT companies.company_id, companies.company_record_status ";
$sql .= "FROM companies ";
$sql .= "LEFT JOIN contacts ON companies.company_id = contacts.company_id ";
$sql .= "WHERE contacts.company_id IS NULL";
$rst = $con->execute($sql);
$companies_to_fix = $rst->RecordCount();
if ($companies_to_fix > 0) {
    $msg .= "Need to create contacts for $companies_to_fix companies<BR><BR>";
    while (!$rst->EOF) {
        $company_id = $rst->fields['company_id'];
        $company_record_status = $rst->fields['company_record_status'];
		$sql = "insert into contacts set
				company_id = $company_id,
				last_name = 'Contact',
				first_names = 'Default',
				contact_record_status = '$company_record_status',
				entered_by = $session_user_id,
				entered_at = " . $con->dbtimestamp(mktime()) . ",
				last_modified_at = " . $con->dbtimestamp(mktime()) . ",
				last_modified_by = $session_user_id"
				;
        $con->execute($sql);

        $rst->movenext();
    }
}

// There needs to be at least one active contact for each active company
$sql = "SELECT companies.company_id ";
$sql .= "FROM companies ";
$sql .= "LEFT JOIN contacts ON companies.company_id = contacts.company_id ";
$sql .= "AND contacts.contact_record_status = 'a' ";
$sql .= "WHERE contacts.company_id IS NULL ";
$sql .= "AND companies.company_record_status = 'a' ";
$rst = $con->execute($sql);
$companies_to_fix = $rst->RecordCount();
if ($companies_to_fix > 0) {
    $msg .= "Need to create active contacts for $companies_to_fix active companies<BR><BR>";
    while (!$rst->EOF) {
        $company_id = $rst->fields['company_id'];
		$sql = "insert into contacts set
				company_id = $company_id,
				last_name = 'Contact',
				first_names = 'Default',
				entered_by = $session_user_id,
				entered_at = " . $con->dbtimestamp(mktime()) . ",
				last_modified_at = " . $con->dbtimestamp(mktime()) . ",
				last_modified_by = $session_user_id"
				;
        $con->execute($sql);

        $rst->movenext();
    }
}

// There needs to be at least one address for each company
$sql = "SELECT companies.company_id, companies.company_record_status ";
$sql .= "FROM companies ";
$sql .= "LEFT JOIN addresses ON companies.company_id = addresses.company_id ";
$sql .= "WHERE addresses.company_id IS NULL";
$rst = $con->execute($sql);
$companies_to_fix = $rst->RecordCount();
if ($companies_to_fix > 0) {
    $msg .= "Need to create addresses for $companies_to_fix companies<BR><BR>";
    while (!$rst->EOF) {
        $company_id = $rst->fields['company_id'];
        $company_record_status = $rst->fields['company_record_status'];
		$sql = "insert into addresses set
				company_id = $company_id,
				country_id = $default_country_id,
				address_name = 'Main',
				address_record_status = '$company_record_status'"
				;
        $con->execute($sql);

        $rst->movenext();
    }
}

// There needs to be at least one active contact for each active company
$sql = "SELECT companies.company_id ";
$sql .= "FROM companies ";
$sql .= "LEFT JOIN addresses ON companies.company_id = addresses.company_id ";
$sql .= "AND addresses.address_record_status = 'a' ";
$sql .= "WHERE addresses.company_id IS NULL ";
$sql .= "AND companies.company_record_status = 'a' ";
$rst = $con->execute($sql);
$companies_to_fix = $rst->RecordCount();
if ($companies_to_fix > 0) {
    $msg .= "Need to create active addresses for $companies_to_fix active companies<BR><BR>";
    while (!$rst->EOF) {
        $company_id = $rst->fields['company_id'];
		$sql = "insert into addresses set
				company_id = $company_id,
				country_id = $default_country_id,
				address_name = 'Main'"
				;
        $con->execute($sql);

        $rst->movenext();
    }
}

//close the database connection, because we don't need it anymore
$con->close();

$page_title = "Database Cleanup Complete";
start_page($page_title, true, $msg);

echo $msg;
?>

<BR>
Your database has been cleaned.
<BR><BR>


<?php

end_page();

/**
 * $Log: data_clean.php,v $
 * Revision 1.3  2004/04/13 15:47:12  maulani
 * - add data integrity check so all companies have addresses
 *
 * Revision 1.2  2004/04/13 15:06:41  maulani
 * - Add active contact data integrity check to database cleanup
 *
 * Revision 1.1  2004/04/12 18:59:01  maulani
 * - Make database structure and data cleanup available withing Admin interface
 *
 */
?>
