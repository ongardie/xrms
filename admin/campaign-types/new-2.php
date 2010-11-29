<?php
/**
 * Insert a new campaign type into the database
 *
 */

require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check( 'Admin' );

$campaign_type_short_name = $_POST['campaign_type_short_name'];
$campaign_type_pretty_name = $_POST['campaign_type_pretty_name'];
$campaign_type_pretty_plural = $_POST['campaign_type_pretty_plural'];
$campaign_type_display_html = $_POST['campaign_type_display_html'];

$con = get_xrms_dbconnection();

//save to database
$rec = array();
$rec['campaign_type_short_name'] = $campaign_type_short_name;
$rec['campaign_type_pretty_name'] = $campaign_type_pretty_name;
$rec['campaign_type_pretty_plural'] = $campaign_type_pretty_plural;
$rec['campaign_type_display_html'] = $campaign_type_display_html;

$tbl = "campaign_types";
$ins = $con->GetInsertSQL($tbl, $rec, get_magic_quotes_gpc());
$con->execute($ins);

$con->close();

header("Location: some.php");

?>
