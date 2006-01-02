<?php
/**
 * Add a division to a company
 *
 * $Id: add-division.php,v 1.13 2006/01/02 22:56:26 vanmer Exp $
 */

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$on_what_table='company_division';
$session_user_id = session_check('','Create');

$company_id = $_POST['company_id'];
$division_name = $_POST['division_name'];
if (!strlen($division_name)) {
    $division_name = _("[none]");
}
$address_id = $_POST['address_id'];
$description = $_POST['description'];

$con = get_xrms_dbconnection();

//$con->debug = 1;

//save to database
$rec = array();
$rec['company_id'] = $company_id;
$rec['division_name'] = $division_name;
$rec['address_id'] = $address_id;
$rec['description'] = $description;
$rec['entered_at'] = time();
$rec['entered_by'] = $session_user_id;
$rec['last_modified_at'] = time();
$rec['last_modified_by'] = $session_user_id;

$tbl = 'company_division';
$ins = $con->GetInsertSQL($tbl, $rec, get_magic_quotes_gpc());
$con->execute($ins);

$division_id = $con->insert_id();
$rec['division_id']=$division_id;

do_hook_function('new_division_process', $rec);

add_audit_item($con, $session_user_id, 'created', 'company_division', $division_id, 1);

$con->close();

header("Location: divisions.php?msg=division_added&company_id=$company_id");

/**
 * $Log: add-division.php,v $
 * Revision 1.13  2006/01/02 22:56:26  vanmer
 * - changed to use centralized dbconnection function
 *
 * Revision 1.12  2005/08/04 19:29:50  vanmer
 * - altered to return to divisions list after new division is added to a company
 *
 * Revision 1.11  2005/07/21 22:13:27  braverock
 * - set a default name for division if no name specified
 *
 * Revision 1.10  2005/01/28 22:58:22  braverock
 * - change return_url to companies/one.php with the correct message
 *
 * Revision 1.9  2005/01/25 23:29:20  vanmer
 * - added hook for plugin processing after new division creation
 *
 * Revision 1.8  2005/01/13 18:23:15  vanmer
 * - Basic ACL changes to allow create/delete functionality to be restricted
 *
 * Revision 1.7  2005/01/06 21:53:22  vanmer
 * - added address_id to new/edit-2 retrieve/store methods, to specify an address for a division
 *
 * Revision 1.6  2004/07/07 21:53:13  introspectshun
 * - Now passes a table name instead of a recordset into GetInsertSQL
 *
 * Revision 1.5  2004/06/12 17:10:24  gpowers
 * - removed DBTimeStamp() calls for compatibility with GetInsertSQL() and
 *   GetUpdateSQL()
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
 * Revision 1.1  2004/01/26 19:18:02  braverock
 * - added company division pages and fields
 * - added phpdoc
 *
 */
?>
