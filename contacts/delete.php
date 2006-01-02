<?php

require_once('include-locations-location.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$contact_id = $_GET['contact_id'];
$on_what_id=$contact_id;

$session_user_id = session_check('','Delete');
$company_id = $_GET['company_id'];

$con = get_xrms_dbconnection();

$sql = "SELECT * FROM contacts WHERE contact_id = $contact_id";
$rst = $con->execute($sql);

$rec = array();
$rec['contact_record_status'] = 'd';

$upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
$con->execute($upd);

add_audit_item($con, $session_user_id, 'deleted', 'contacts', $contact_id, 1);

$con->close();

header("Location: {$http_site_root}/companies/one.php?company_id=$company_id&msg=contact_deleted");

/**
 * $Log: delete.php,v $
 * Revision 1.7  2006/01/02 22:59:59  vanmer
 * - changed to use centralized dbconnection function
 *
 * Revision 1.6  2005/02/10 21:16:48  maulani
 * - Add audit trail entries
 *
 * Revision 1.5  2005/01/13 18:42:30  vanmer
 * - Basic ACL changes to allow create/delete/update functionality to be restricted
 *
 * Revision 1.4  2004/07/22 11:21:13  cpsource
 * - All paths now relative to include-locations-location.inc
 *   Code cleanup for Create Contact for 'Self'
 *
 * Revision 1.3  2004/07/13 09:34:51  cpsource
 * Add cvs logging.
 *
 */
?>