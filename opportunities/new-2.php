<?php
/**
 * Insert a new opportunity into the database
 *
 * $Id: new-2.php,v 1.8 2005/01/06 20:48:19 vanmer Exp $
 */

//include common files
require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

//check security
$session_user_id = session_check();

$opportunity_status_id = $_POST['opportunity_status_id'];
$size = $_POST['size'];
$probability = $_POST['probability'];
$user_id = $_POST['user_id'];
$company_id = $_POST['company_id'];
$division_id = $_POST['division_id'];
$contact_id = $_POST['contact_id'];
$campaign_id = $_POST['campaign_id'];
$opportunity_title = $_POST['opportunity_title'];
$close_at = $_POST['close_at'];
$opportunity_description = $_POST['opportunity_description'];

$campaign_id = ($campaign_id > 0) ? $campaign_id : 0;

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
// $con->debug = 1;

//save to database
$rec = array();
$rec['opportunity_status_id'] = $opportunity_status_id;
$rec['user_id'] = $user_id;
$rec['company_id'] =  $company_id;
$rec['division_id'] =  $division_id;
$rec['contact_id'] = $contact_id;
$rec['campaign_id'] = $campaign_id;
$rec['opportunity_title'] = $opportunity_title;
$rec['opportunity_description'] = $opportunity_description;
$rec['size'] = $size;
$rec['probability'] = $probability;
//should modify opportunities/cases/etc to use a 'date' type for these fields.
$rec['close_at'] = strtotime("+23 hours 59 minutes",strtotime($close_at));
$rec['entered_at'] = time();
$rec['entered_by'] = $session_user_id;
$rec['last_modified_at'] = time();
$rec['last_modified_by'] = $session_user_id;

$tbl = 'opportunities';
$ins = $con->GetInsertSQL($tbl, $rec, get_magic_quotes_gpc());
$con->execute($ins);

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
 * Revision 1.8  2005/01/06 20:48:19  vanmer
 * - added retrieve/save of division_id to edit and new pages
 *
 * Revision 1.7  2004/07/07 22:39:46  introspectshun
 * - Now passes a table name instead of a recordset into GetInsertSQL
 *
 * Revision 1.6  2004/06/21 03:54:22  braverock
 * - fixed timestamp for new opportunity
 *
 * Revision 1.5  2004/06/14 17:41:36  introspectshun
 * - Add adodb-params.php include for multi-db compatibility.
 * - Corrected order of arguments to implode() function.
 * - Now use ADODB GetInsertSQL, GetUpdateSQL, Concat and Date functions.
 *
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
