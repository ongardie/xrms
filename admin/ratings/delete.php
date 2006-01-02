<?php
/**
 * Delete a Rating by setting the rating_record_status to 'd'
 *
 * $Id: delete.php,v 1.5 2006/01/02 22:03:16 vanmer Exp $
 */
require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check( 'Admin' );

$rating_id = $_POST['rating_id'];

$con = get_xrms_dbconnection();

$sql = "SELECT * FROM ratings WHERE rating_id = $rating_id";
$rst = $con->execute($sql);

$rec = array();
$rec['rating_record_status'] = 'd';

$upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
$con->execute($upd);

$con->close();

header("Location: some.php");

/**
 * $Log: delete.php,v $
 * Revision 1.5  2006/01/02 22:03:16  vanmer
 * - changed to use centralized dbconnection function
 *
 * Revision 1.4  2004/07/16 23:51:37  cpsource
 * - require session_check ( 'Admin' )
 *
 * Revision 1.3  2004/06/14 22:38:46  introspectshun
 * - Add adodb-params.php include for multi-db compatibility.
 * - Now use ADODB GetInsertSQL, GetUpdateSQL functions.
 *
 * Revision 1.2  2004/02/14 15:41:12  braverock
 * - add phpdoc
 *
 */
?>