<?php
/**
 * Delete company
 *
 * $Id: delete.php,v 1.7 2006/04/26 19:49:46 braverock Exp $
 */
require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');
require_once($include_directory . 'utils-accounting.php');

getGlobalVar($company_id,'company_id');
$on_what_id=$company_id;

$session_user_id = session_check('','Delete');


$con = get_xrms_dbconnection();
//$con->debug=1;


$sql = "SELECT * FROM companies WHERE company_id = $company_id";
$rst = $con->execute($sql);

$rec = array();
$rec['company_record_status'] = 'd';

$upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
$con->execute($upd);


$sql = "SELECT * FROM contacts WHERE company_id = $company_id";
$rst = $con->execute($sql);

$rec = array();
$rec['contact_record_status'] = 'd';

$upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
$con->execute($upd);


$sql = "SELECT * FROM opportunities WHERE company_id = $company_id";
$rst = $con->execute($sql);

$rec = array();
$rec['opportunity_record_status'] = 'd';

$upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
$con->execute($upd);


$sql = "SELECT * FROM cases WHERE company_id = $company_id";
$rst = $con->execute($sql);

$rec = array();
$rec['case_record_status'] = 'd';

$upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
$con->execute($upd);


$sql = "SELECT * FROM addresses WHERE company_id = $company_id";
$rst = $con->execute($sql);

$rec = array();
$rec['address_record_status'] = 'd';

$upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
$con->execute($upd);


$sql = "SELECT * FROM activities WHERE on_what_table = 'companies' AND on_what_id = $company_id";
$rst = $con->execute($sql);

$rec = array();
$rec['activity_record_status'] = 'd';

$upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
$con->execute($upd);


$sql = "SELECT * FROM files WHERE on_what_table = 'companies' AND on_what_id = $company_id";
$rst = $con->execute($sql);

$rec = array();
$rec['file_record_status'] = 'd';

$upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
$con->execute($upd);

add_audit_item($con, $session_user_id, 'deleted', 'companies', $company_id, 1);

$con->close();

header("Location: some.php?msg=company_deleted");

/**
 * $Log: delete.php,v $
 * Revision 1.7  2006/04/26 19:49:46  braverock
 * - change to use GetGlobalVar to handle both $_POST and $_GET
 *
 * Revision 1.6  2006/01/02 22:56:26  vanmer
 * - changed to use centralized dbconnection function
 *
 * Revision 1.5  2005/01/13 18:20:28  vanmer
 * - Basic ACL changes to allow create/delete/update functionality to be restricted
 *
 * Revision 1.4  2004/06/12 05:03:16  introspectshun
 * - Now use ADODB GetInsertSQL, GetUpdateSQL, date and Concat functions.
 * - Corrected order of arguments to implode() function.
 *
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