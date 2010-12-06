<?php
/**
 * delete (set status to 'd') the information for a single campaign type
 *
 * $Id: delete.php,v 1.6 2010/12/06 21:56:13 gopherit Exp $
 */

require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check( 'Admin' );

$campaign_type_id = (int)$_POST['campaign_type_id'];

$con = get_xrms_dbconnection();

// Delete all activity templates attached to this campaign type through
// the campaign_statuses_table
$sql = "UPDATE activity_templates at, campaign_statuses cs
        SET at.activity_template_record_status = 'd'
        WHERE at.on_what_table = 'campaign_statuses'
        AND at.activity_template_record_status = 'a'
        AND at.on_what_id IN (SELECT campaign_status_id
                                FROM campaign_statuses cs
                                WHERE cs.campaign_type_id = $campaign_type_id
                                AND cs.campaign_status_record_status = 'a')";
$rst = $con->Execute($sql);

// Delete the child campaign_statuses
$sql = "UPDATE campaign_statuses SET campaign_status_record_status = 'd' WHERE campaign_type_id = $campaign_type_id";
$rst = $con->execute($sql);

// And delete the campaign type
$sql = "SELECT * FROM campaign_types WHERE campaign_type_id = $campaign_type_id";
$rst = $con->execute($sql);

$rec = array();
$rec['campaign_type_record_status'] = 'd';

$upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
$con->execute($upd);


$con->close();

header("Location: some.php");

?>