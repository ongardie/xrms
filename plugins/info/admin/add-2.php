<?php
/**
 * Commit the new Activity Type to the database
 *
 * $Id: add-2.php,v 1.1 2004/11/10 07:27:49 gpowers Exp $
 */

require_once('../../../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check( 'Admin' );

$info_type_name = $_POST['info_type_name'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

//save to database
$rec = array();

//set the other variables
$rec['info_type_name'] = $info_type_name;

//commit it
$tbl = "info_types";
$ins = $con->GetInsertSQL($tbl, $rec, get_magic_quotes_gpc());
$rst = $con->execute($ins);
$info_type_id = $con->insert_id();
if (!$rst) {
    db_error_handler ($con, $ins);
}


//save to database
$rec = array();

//set the other variables
$rec['info_type_id'] = $info_type_id;
$rec['display_on'] = "private_sidebar";

//commit it
$tbl = "info_display_map";
$ins = $con->GetInsertSQL($tbl, $rec, get_magic_quotes_gpc());
$rst = $con->execute($ins);
if (!$rst) {
    db_error_handler ($con, $ins);
}

$con->close();

header("Location: some.php");

/**
 * $Log: add-2.php,v $
 * Revision 1.1  2004/11/10 07:27:49  gpowers
 * - added admin screens for info types
 *
 * Revision 1.6  2004/07/16 23:51:34  cpsource
 * - require session_check ( 'Admin' )
 *
 * Revision 1.5  2004/07/16 15:04:45  braverock
 * - add phpdoc
 *
 */
?>
