<?php
/**
 * Delete company
 *
 * $Id: delete.php,v 1.3 2004/05/10 13:09:14 maulani Exp $
 */
require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'utils-accounting.php');

$session_user_id = session_check();

$company_id = $_POST['company_id'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

$sql = "update companies set company_record_status = 'd' where company_id = $company_id";
$con->execute($sql);
$sql = "update contacts set contact_record_status = 'd' where company_id = $company_id";
$con->execute($sql);
$sql = "update opportunities set opportunity_record_status = 'd' where company_id = $company_id";
$con->execute($sql);
$sql = "update cases set case_record_status = 'd' where company_id = $company_id";
$con->execute($sql);
$sql = "update addresses set address_record_status = 'd' where company_id = $company_id";
$con->execute($sql);
$sql = "update activities set activity_record_status = 'd' where on_what_table = 'companies' and on_what_id = $company_id";
$con->execute($sql);
$sql = "update files set file_record_status = 'd' where on_what_table = 'companies' and on_what_id = $company_id";
$con->execute($sql);

add_audit_item($con, $session_user_id, 'deleted', 'companies', $company_id, 1);

$con->close();

header("Location: some.php?msg=company_deleted");

/**
 * $Log: delete.php,v $
 * Revision 1.3  2004/05/10 13:09:14  maulani
 * - add level to audit trail
 *
 * Revision 1.2  2004/03/26 20:55:59  maulani
 * - Add audit trail to company-related items
 * - Add phpdoc
 *
 *
 */
?>