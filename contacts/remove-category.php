<?php

require_once('include-locations-location.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();

$msg         = isset($_GET['msg']) ? $_GET['msg'] : '';
$contact_id  = $_GET['contact_id'];
$category_id = $_GET['category_id'];

$con = get_xrms_dbconnection();
// $con->debug = 1;

$sql = "delete from entity_category_map
where category_id = $category_id
and on_what_table = 'contacts'
and on_what_id = $contact_id";
$con->execute($sql);

$con->close();

header("Location: categories.php?contact_id=$contact_id");

/**
 * $Log: remove-category.php,v $
 * Revision 1.6  2006/01/02 23:00:00  vanmer
 * - changed to use centralized dbconnection function
 *
 * Revision 1.5  2004/07/25 12:49:56  braverock
 * - remove lang file require_once, as it is no longer used
 *
 * Revision 1.4  2004/07/22 11:21:13  cpsource
 * - All paths now relative to include-locations-location.inc
 *   Code cleanup for Create Contact for 'Self'
 *
 * Revision 1.3  2004/07/13 16:21:42  cpsource
 * - don't use uninitialized variables
 *   do language processing
 *   add cvs revision history to bottom
 */
?>