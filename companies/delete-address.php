<?php
/**
 * delete address for a company
 *
 * $Id: delete-address.php,v 1.6 2006/04/21 20:26:07 braverock Exp $
 */


require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');
require_once($include_directory . 'utils-accounting.php');

$session_user_id = session_check();

$company_id = $_GET['company_id'];
$address_id = $_GET['address_id'];

$con = get_xrms_dbconnection();

$test = delete_address($con, $address_id);

$con->close();

if ($test) {
    add_audit_item($con, $session_user_id, 'created', 'addresses', $address_id['primarykey'], 1);
    header("Location: addresses.php?msg=address_deleted&company_id=$company_id");
} else {
    $msg=urlencode(_("Updating Address Failed"));
    header("Location: addresses.php?msg=$msg&company_id=$company_id");
}

/**
 * $Log: delete-address.php,v $
 * Revision 1.6  2006/04/21 20:26:07  braverock
 * - modify to use addresses API
 *
 * Revision 1.5  2006/01/02 22:56:26  vanmer
 * - changed to use centralized dbconnection function
 *
 * Revision 1.4  2004/06/12 05:03:16  introspectshun
 * - Now use ADODB GetInsertSQL, GetUpdateSQL, date and Concat functions.
 * - Corrected order of arguments to implode() function.
 *
 * Revision 1.3  2004/06/09 17:43:59  gpowers
 * - added $Id and $Log tags
 *
 *
*/
?>
