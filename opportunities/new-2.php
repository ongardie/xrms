<?php
/**
 * Insert a new opportunity into the database
 *
 * $Id: new-2.php,v 1.13 2006/04/29 01:48:25 vanmer Exp $
 */

//include common files
require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'utils-opportunities.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

//check security
$session_user_id = session_check('','Create');

$opportunity_status_id = $_POST['opportunity_status_id'];
$opportunity_type_id = $_POST['opportunity_type_id'];
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

$con = get_xrms_dbconnection();
// $con->debug = 1;

//save to database
$rec = array();
$rec['opportunity_status_id'] = $opportunity_status_id;
$rec['opportunity_type_id'] = $opportunity_type_id;
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

$opportunity_id=add_opportunity($con, $rec,get_magic_quotes_gpc());

$con->close();

header("Location: one.php?msg=opportunity_added&opportunity_id=$opportunity_id");

/**
 * $Log: new-2.php,v $
 * Revision 1.13  2006/04/29 01:48:25  vanmer
 * - replaced opportunites edit, new and delete pages to use opportunities API
 * - altered opportunities API to reflect correct codes for won/lost statuses
 * - moved workflow into opportunities API
 *
 * Revision 1.12  2006/04/22 08:38:49  jnhayart
 * add tracability on opportinites
 *
 * Revision 1.11  2006/01/02 23:29:27  vanmer
 * - changed to use centralized dbconnection function
 *
 * Revision 1.10  2005/07/06 22:50:32  braverock
 * - add opportunity types
 *
 * Revision 1.9  2005/01/13 19:08:56  vanmer
 * - Basic ACL changes to allow create/delete/update functionality to be restricted
 *
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
