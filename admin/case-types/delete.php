<?php
/**
 * delete (set status to 'd') the information for a single case
 *
 * $Id: delete.php,v 1.7 2010/12/06 21:56:13 gopherit Exp $
 */

require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check( 'Admin' );

$case_type_id = (int)$_POST['case_type_id'];

$con = get_xrms_dbconnection();

// Delete all activity templates attached to this case type through
// the case_statuses_table
$sql = "UPDATE activity_templates at, case_statuses cs
        SET at.activity_template_record_status = 'd'
        WHERE at.on_what_table = 'case_statuses'
        AND at.activity_template_record_status = 'a'
        AND at.on_what_id IN (SELECT case_status_id
                                FROM case_statuses cs
                                WHERE cs.case_type_id = $case_type_id
                                AND cs.case_status_record_status = 'a')";
$rst = $con->Execute($sql);

// Delete the child case_statuses
$sql = "UPDATE case_statuses SET case_status_record_status = 'd' WHERE case_type_id = $case_type_id";
$rst = $con->execute($sql);

// And delete the case type
$sql = "SELECT * FROM case_types WHERE case_type_id = $case_type_id";
$rst = $con->execute($sql);

$rec = array();
$rec['case_type_record_status'] = 'd';

$upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
$con->execute($upd);


$con->close();

header("Location: some.php");

/**
 * $Log: delete.php,v $
 * Revision 1.7  2010/12/06 21:56:13  gopherit
 * Deleting a workflow type now results in not only deleting all its statuses but also deleting all the activity templates attached to those statuses.
 *
 * Revision 1.6  2006/12/14 17:41:44  fcrossen
 * - mark child case-status records as deleted when a case-type is deleted
 *
 * Revision 1.5  2006/01/02 21:41:51  vanmer
 * - changed to use centralized dbconnection function
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
