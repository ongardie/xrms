<?php
/**
 * delete (set status to 'd') the information for a single opportunity type
 *
 * $Id: delete.php,v 1.4 2010/12/06 21:56:13 gopherit Exp $
 */

require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check( 'Admin' );

$opportunity_type_id = (int)$_POST['opportunity_type_id'];

$con = get_xrms_dbconnection();

// Delete all activity templates attached to this opportunity type through
// the opportunity_statuses_table
$sql = "UPDATE activity_templates at, opportunity_statuses os
        SET at.activity_template_record_status = 'd'
        WHERE at.on_what_table = 'opportunity_statuses'
        AND at.activity_template_record_status = 'a'
        AND at.on_what_id IN (SELECT opportunity_status_id
                                FROM opportunity_statuses os
                                WHERE os.opportunity_type_id = $opportunity_type_id
                                AND os.opportunity_status_record_status = 'a')";
$rst = $con->Execute($sql);

// Delete the child opportunity_statuses
$sql = "UPDATE opportunity_statuses SET opportunity_status_record_status = 'd' WHERE opportunity_type_id = $opportunity_type_id";
$rst = $con->execute($sql);

// And delete the opportunity type
$sql = "SELECT * FROM opportunity_types WHERE opportunity_type_id = $opportunity_type_id";
$rst = $con->execute($sql);

$rec = array();
$rec['opportunity_type_record_status'] = 'd';

$upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
$con->execute($upd);


$con->close();

header("Location: some.php");

/**
 * $Log: delete.php,v $
 * Revision 1.4  2010/12/06 21:56:13  gopherit
 * Deleting a workflow type now results in not only deleting all its statuses but also deleting all the activity templates attached to those statuses.
 *
 * Revision 1.3  2006/12/14 17:46:16  fcrossen
 * - mark child opportunity-status records as deleted when an opportunity-type is deleted
 *
 * Revision 1.2  2006/01/02 21:59:08  vanmer
 * - changed to use centralized database connection function
 *
 * Revision 1.1  2005/07/06 21:08:57  braverock
 * - Initial Revision of Admin screens for opportunity types
 *
 * Revision 1.4  2004/07/16 23:51:35  cpsource
 * - require session_check ( 'Admin' )
 *
 * Revision 1.3  2004/06/14 21:48:24  introspectshun
 * - Add adodb-params.php include for multi-db compatibility.
 * - Now use ADODB GetInsertSQL, GetUpdateSQL functions.
 *
 * Revision 1.2  2004/03/21 23:55:51  braverock
 * - fix SF bug 906413
 * - add phpdoc
 *
 */
?>
