<?php
/**
 * /admin/account-statuses/some.php
 *
 * Delete account-status
 *
 * $Id: delete.php,v 1.5 2006/01/02 21:26:21 vanmer Exp $
 */

require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check( 'Admin' );

$account_status_id = $_POST['account_status_id'];

$con = get_xrms_dbconnection();

$sql = "SELECT * FROM account_statuses WHERE account_status_id = $account_status_id";
$rst = $con->execute($sql);

$rec = array();
$rec['account_status_record_status'] = 'd';

$upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
$con->execute($upd);

$con->close();

header("Location: some.php");

/**
 * $Log: delete.php,v $
 * Revision 1.5  2006/01/02 21:26:21  vanmer
 * - changed to use centralized xrms dbconnection function
 *
 * Revision 1.4  2005/04/10 17:34:54  maulani
 * - Add phpdoc
 *
 *
 */
?>
