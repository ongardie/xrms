<?php
/**
 * delete (set status to 'd') the information for a single campaign type
 *
 * $Id: delete.php,v 1.5 2010/11/29 15:16:03 gopherit Exp $
 */

require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check( 'Admin' );

$campaign_type_id = $_POST['campaign_type_id'];

$con = get_xrms_dbconnection();

$sql = "SELECT * FROM campaign_types WHERE campaign_type_id = $campaign_type_id";
$rst = $con->execute($sql);

$rec = array();
$rec['campaign_type_record_status'] = 'd';

$upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
$con->execute($upd);

// Mark all the child campaign_statuses records as deleted
$sql = "UPDATE campaign_statuses SET campaign_status_record_status = 'd' WHERE campaign_type_id = $campaign_type_id";
$rst = $con->execute($sql);

$con->close();

header("Location: some.php");

?>