<?php
/**
 * Save changes to divisions
 *
 * $Id: edit-division-2.php,v 1.4 2005/01/06 21:53:22 vanmer Exp $
 */

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();

$division_id = $_POST['division_id'];
$company_id = $_POST['company_id'];
$address_id = $_POST['address_id'];
$division_name = $_POST['division_name'];
$description = $_POST['description'];

$use_pretty_address = ($use_pretty_address == 'on') ? "'t'" : "'f'";

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

//$con->debug=1;

$sql = "SELECT * FROM company_division WHERE division_id = $division_id";
$rst = $con->execute($sql);

$rec = array();
$rec['division_id'] = $division_id;
$rec['address_id'] = $address_id;
$rec['division_name'] = $division_name;
$rec['description'] = $description;
$rec['last_modified_at'] = time();
$rec['last_modified_by'] = $session_user_id;

$upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
$con->execute($upd);

header("Location: divisions.php?msg=saved&company_id=$company_id");

/**
 * $Log: edit-division-2.php,v $
 * Revision 1.4  2005/01/06 21:53:22  vanmer
 * - added address_id to new/edit-2 retrieve/store methods, to specify an address for a division
 *
 * Revision 1.3  2004/06/12 17:10:24  gpowers
 * - removed DBTimeStamp() calls for compatibility with GetInsertSQL() and
 *   GetUpdateSQL()
 *
 * Revision 1.2  2004/06/12 05:03:16  introspectshun
 * - Now use ADODB GetInsertSQL, GetUpdateSQL, date and Concat functions.
 * - Corrected order of arguments to implode() function.
 *
 * Revision 1.1  2004/01/26 19:18:02  braverock
 * - added company division pages and fields
 * - added phpdoc
 *
 */
?>
