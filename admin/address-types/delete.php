<?php
/**
 * /admin/address-types/some.php
 *
 * Delete address-type
 *
 * $Id: delete.php,v 1.3 2007/05/15 23:17:29 ongardie Exp $
 */

require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check( 'Admin' );

$address_type_id = $_POST['address_type_id'];

$con = get_xrms_dbconnection();

$sql = "DELETE FROM address_types WHERE address_type_id = $address_type_id";
$rst = $con->execute($sql);

$con->close();

header("Location: some.php");

/**
 * $Log: delete.php,v $
 * Revision 1.3  2007/05/15 23:17:29  ongardie
 * - Addresses now associate with on_what_table, on_what_id instead of company_id.
 *
 * Revision 1.2  2006/01/02 22:35:33  vanmer
 * - changed to use centralized dbconnection function
 *
 * Revision 1.1  2005/04/11 00:43:25  maulani
 * - Add address type admin tool
 *
 */
?>
