<?php
/**
 * install/data_clean.php - Cleanup the database
 *
 * When coding this file, it is important that everything only happen after
 * a test.  This file must be non-destructive and only make the changes that
 * must be made.
 *
 * @author Beth Macknik
 * $Id: data_clean.php,v 1.3 2004/04/12 14:35:19 maulani Exp $
 */

// where do we include from
require_once('../include-locations.inc');

// get required common files
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

// vars.php sets all of the installation-specific variables
require_once($include_directory . 'vars.php');

// include the installation utility routines
require_once('install-utils.inc');

// make a database connection
$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

// Make sure that there is a last name for every contact
$sql = "update contacts set last_name='[last name]' where last_name=''";
$rst = $con->execute($sql);

// Make sure that there is a first name for every contact
$sql = "update contacts set first_names='[first names]' where first_names=''";
$rst = $con->execute($sql);

// There needs to be at least one contact for each company
$sql = "SELECT companies.company_id ";
$sql .= "FROM companies ";
$sql .= "LEFT JOIN contacts ON companies.company_id = contacts.company_id ";
$sql .= "WHERE contacts.company_id IS NULL";
$rst = $con->execute($sql);

//close the database connection, because we don't need it anymore
$con->close();

$page_title = "Database Cleanup Complete";
start_page($page_title, false, $msg);

?>

<BR>
Your database has been cleaned.
<BR><BR>
You may now <a href="../login.php">login</a>.


<?php

end_page();

/**
 * $Log: data_clean.php,v $
 * Revision 1.3  2004/04/12 14:35:19  maulani
 * - move structure change to update.php
 *
 * Revision 1.2  2004/04/09 17:13:28  braverock
 * - added alter table command to change company_legal_name to legal_name
 *   (only relevant for upgraded old installations)
 *
 * Revision 1.1  2004/04/07 20:16:21  maulani
 * - Set of routines to cleanup common data problems
 *
 *
 */
?>
