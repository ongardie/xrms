<?php
/**
 * Insert a new opportunity into the database
 *
 * $Id: new-2.php,v 1.4 2004/06/03 16:16:18 braverock Exp $
 */

//include common files
require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

//check security
$session_user_id = session_check();

$opportunity_status_id = $_POST['opportunity_status_id'];
$size = $_POST['size'];
$probability = $_POST['probability'];
$user_id = $_POST['user_id'];
$company_id = $_POST['company_id'];
$contact_id = $_POST['contact_id'];
$campaign_id = $_POST['campaign_id'];
$opportunity_title = $_POST['opportunity_title'];
$close_at = $_POST['close_at'];
$opportunity_description = $_POST['opportunity_description'];

$campaign_id = ($campaign_id > 0) ? $campaign_id : 0;

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
// $con->debug = 1;

$sql = "insert into opportunities set
        opportunity_status_id = $opportunity_status_id,
        user_id = $user_id,
        company_id =  $company_id,
        contact_id = $contact_id,
        campaign_id = $campaign_id,
        opportunity_title = " . $con->qstr($opportunity_title, get_magic_quotes_gpc()) . ",
        opportunity_description = " . $con->qstr($opportunity_description, get_magic_quotes_gpc()) . ",
        size = $size,
        probability = $probability,
        close_at = " . $con->dbdate($close_at . ' 23:59:59') . ",
        entered_at = " . $con->dbtimestamp(mktime()) . ",
        entered_by = $session_user_id,
        last_modified_at = " . $con->dbtimestamp(mktime()) . ",
        last_modified_by = $session_user_id"
        ;

$con->execute($sql);

$opportunity_id = $con->insert_id();

$on_what_table = "opportunities";
$on_what_id = $opportunity_id;
//generate activities for the new opportunity
$on_what_table_template = "opportunity_statuses";
$on_what_id_template = $opportunity_status_id;
require_once("../activities/workflow-activities.php");


$con->close();

header("Location: one.php?msg=opportunity_added&opportunity_id=$opportunity_id");

/**
 * $Log: new-2.php,v $
 * Revision 1.4  2004/06/03 16:16:18  braverock
 * - add functionality to support workflow and activity templates
 *   - functionality contributed by Brad Marshall
 *
 * Revision 1.3  2004/04/13 15:08:37  maulani
 * - cleanup sql
 *
 * Revision 1.2  2004/01/26 19:34:48  braverock
 * - cleaned up sql
 * - added phpdoc
 *
 */
?>
