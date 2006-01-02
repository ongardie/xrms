<?php
/**
 * 'Delete' an activity
 *
 * $Id: delete.php,v 1.6 2006/01/02 21:23:18 vanmer Exp $
 */
  
require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');
require_once($include_directory . 'utils-accounting.php');

$activity_id = $_GET['activity_id'];
$on_what_id=$activity_id;

$session_user_id = session_check('','Delete');

$return_url = $_GET['return_url'];
$save_and_next = $_GET['save_and_next'];

$con = get_xrms_dbconnection();
// $con->debug = 1;

$sql = "SELECT * FROM activities WHERE activity_id = $activity_id";
$rst = $con->execute($sql);

$rec = array();
$rec['activity_record_status'] = 'd';

$upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
$con->execute($upd);

add_audit_item($con, $session_user_id, 'deleted', 'activities', $activity_id, 1);

$con->close();

if($save_and_next) {
    header("Location: browse-next.php?activity_id=$activity_id");
} else {
    header("Location: " . $http_site_root . $return_url);
}

/**
 * $Log: delete.php,v $
 * Revision 1.6  2006/01/02 21:23:18  vanmer
 * - changed to use centralized database connection function
 *
 * Revision 1.5  2005/02/10 21:16:41  maulani
 * - Add audit trail entries
 *
 * Revision 1.4  2005/01/29 13:12:38  braverock
 * - add 'Save and Next' browse support to delete
 *   resolves SF bug 1103958 reported by proto23
 *
 */
?>