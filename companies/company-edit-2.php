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

$session_user_id = session_check();

$relationship_id = $_POST['relationship_id'];
$working_direction = $_POST['working_direction'];
$return_url = $_POST['return_url'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

$rst = $con->execute("select * from relationships where relationship_id='$relationship_id'");

if($_POST['unassociate']) {
    $sql = "update relationships set
        relationship_status = 'd'
        where relationship_id = $relationship_id";
}
elseif($_POST['make_default']) {
    $sql = "update contacts
        set company_id = " . $rst->fields['to_what_id'] . "
        where contact_id = " . $rst->fields['from_what_id'];
}
 //$con->debug=1;

$con->execute($sql);
$con->close();

header("Location: " . $http_site_root . "/" . $return_url);

/**
 * $Log: company-edit-2.php,v $
 * Revision 1.1  2004/07/01 19:48:10  braverock
 * - add new configurable relationships code
 *   - adapted from patches submitted by Neil Roberts
 *
 */
?>