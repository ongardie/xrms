<?php
/**
 * Commit the new Info Type to the database
 *
 * $Id: add-2.php,v 1.5 2005/03/18 21:11:37 gpowers Exp $
 */

require_once('../../../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check( 'Admin' );

$info_type_name = $_POST['info_type_name'];
$display_on = $_POST['display_on'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

//save to database
$rec = array();

//set into type
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
$rec['display_on'] = $display_on;

//commit it
$tbl = "info_display_map";
$ins = $con->GetInsertSQL($tbl, $rec, get_magic_quotes_gpc());
$rst = $con->execute($ins);
if (!$rst) {
    db_error_handler ($con, $ins);
}

//insert NAME element
$rec = array();
$rec['element_label'] = _("Name");
$rec['element_type'] = "'name'";
$rec['element_enabled'] = 1;
$rec['info_type_id'] = $info_type_id;

//commit it
$tbl = "info_element_definitions";
$ins = $con->GetInsertSQL($tbl, $rec, get_magic_quotes_gpc());
$rst = $con->execute($ins);
if (!$rst) {
    db_error_handler ($con, $ins);
}

$con->close();

header("Location: some.php");

/**
 * $Log: add-2.php,v $
 * Revision 1.5  2005/03/18 21:11:37  gpowers
 * - removed (commented) notes
 *
 * Revision 1.4  2005/03/18 20:54:37  gpowers
 * - added support for inline (custom fields) info
 *
 * Revision 1.3  2005/02/11 00:54:55  braverock
 * - add phpdoc where neccessary
 * - fix code formatting and comments
 *
 * Revision 1.2  2004/11/12 06:36:37  gpowers
 * - added support for single display_on add/edit/delete/show
 *
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