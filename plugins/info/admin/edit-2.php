<?php
/**
 * Commit Changes to an Info Element Definition
 *
 * $Id: edit-2.php,v 1.3 2005/02/11 00:54:55 braverock Exp $
 */
 
require_once('../../../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check( 'Admin' );

$info_type_id = $_POST['info_type_id'];
$info_type_name = $_POST['info_type_name'];
$display_on = $_POST['display_on'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

// $con->debug = 1;

$sql = "SELECT * FROM info_types WHERE info_type_id = $info_type_id";
$rst = $con->execute($sql);

$rec = array();
$rec['info_type_name'] = $info_type_name;

$upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
$con->execute($upd);

$sql = "SELECT * FROM info_display_map WHERE info_type_id = $info_type_id";
$rst = $con->execute($sql);

$rec = array();
$rec['display_on'] = $display_on;

$upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
$con->execute($upd);

$con->close();

header("Location: one.php?info_type_id=$info_type_id");

/**
 * $Log: edit-2.php,v $
 * Revision 1.3  2005/02/11 00:54:55  braverock
 * - add phpdoc where neccessary
 * - fix code formatting and comments
 *
 */
?>