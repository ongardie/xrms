<?php
/**
 * /admin/account-statuses/add-2.php
 *
 * Add account status
 *
 * $Id: add-2.php,v 1.7 2006/01/02 21:26:21 vanmer Exp $
 */

require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check( 'Admin' );

$account_status_short_name = $_POST['account_status_short_name'];
$account_status_pretty_name = $_POST['account_status_pretty_name'];
$account_status_pretty_plural = $_POST['account_status_pretty_plural'];
$account_status_display_html = $_POST['account_status_display_html'];

$con = get_xrms_dbconnection();

if (!$account_status_pretty_name) { $account_status_pretty_name = $account_status_short_name; }
if (!$account_status_pretty_plural) { $account_status_pretty_plural = $account_status_short_name; }
if (!$account_status_diplay_html) { $account_status_display_html = $account_status_short_name; }

//save to database
$rec = array();
$rec['account_status_short_name'] = $account_status_short_name;
$rec['account_status_pretty_name'] = $account_status_pretty_name;
$rec['account_status_pretty_plural'] = $account_status_pretty_plural;
$rec['account_status_display_html'] = $account_status_display_html;

$tbl = "account_statuses";
$ins = $con->GetInsertSQL($tbl, $rec, get_magic_quotes_gpc());
$con->execute($ins);

$con->close();

header("Location: some.php");

/**
 * $Log: add-2.php,v $
 * Revision 1.7  2006/01/02 21:26:21  vanmer
 * - changed to use centralized xrms dbconnection function
 *
 * Revision 1.6  2005/04/10 17:34:54  maulani
 * - Add phpdoc
 *
 *
 */
?>
