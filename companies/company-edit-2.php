<?php
/**
 * Delete Company
 *
 * Delete confirmed company from delete-company.php by submitted company_id
 *
 * @author Neil Roberts
 */

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();

$relationship_id = $_POST['relationship_id'];
$working_direction = $_POST['working_direction'];
$return_url = $_POST['return_url'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

$sql = "SELECT * FROM relationships WHERE relationship_id = '$relationship_id'";
$rst = $con->execute($sql);

if($_POST['unassociate']) {
    $rec = array();
    $rec['relationship_status'] = 'd';

    $upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
    $con->execute($upd);
}
elseif($_POST['make_default']) {
    $sql = "SELECT * FROM contacts WHERE contact_id = '" . $rst->fields['from_what_id'] . "'";
    $rst2 = $con->execute($sql);

    $rec = array();
    $rec['company_id'] = $rst->fields['to_what_id'];

    $upd = $con->GetUpdateSQL($rst2, $rec, false, get_magic_quotes_gpc());
    $con->execute($upd);
}
 //$con->debug=1;

$con->close();

header("Location: " . $http_site_root . "/" . $return_url);

/**
 * $Log: company-edit-2.php,v $
 * Revision 1.2  2004/07/05 21:54:54  introspectshun
 * - Now uses GetUpdateSQL
 *
 * Revision 1.1  2004/07/01 19:48:10  braverock
 * - add new configurable relationships code
 *   - adapted from patches submitted by Neil Roberts
 *
 */
?>