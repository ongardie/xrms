<?php
/**
 * Insert a new rating
 *
 * $Id: add-2.php,v 1.6 2006/01/02 22:03:16 vanmer Exp $
 */

require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check( 'Admin' );

$rating_short_name = $_POST['rating_short_name'];
$rating_pretty_name = $_POST['rating_pretty_name'];
$rating_pretty_plural = $_POST['rating_pretty_plural'];
$rating_display_html = $_POST['rating_display_html'];

//make the ratings match the rating_pretty_name if the user didn't enter them
if (!strlen(rating_pretty_plural) > 0) { $rating_pretty_plural = $rating_pretty_name; }
if (!strlen(rating_display_html) > 0)  { $rating_display_html  = $rating_pretty_name; }

$con = get_xrms_dbconnection();

//save to database
$rec = array();
$rec['rating_short_name'] = $rating_short_name;
$rec['rating_pretty_name'] = $rating_pretty_name;
$rec['rating_pretty_plural'] = $rating_pretty_plural;
$rec['rating_display_html'] = $rating_display_html;

$tbl = 'ratings';
$ins = $con->GetInsertSQL($tbl, $rec, get_magic_quotes_gpc());
$con->execute($ins);

$con->close();

header("Location: some.php");

/**
 * $Log: add-2.php,v $
 * Revision 1.6  2006/01/02 22:03:16  vanmer
 * - changed to use centralized dbconnection function
 *
 * Revision 1.5  2004/07/16 23:51:37  cpsource
 * - require session_check ( 'Admin' )
 *
 * Revision 1.4  2004/07/15 22:16:18  introspectshun
 * - Now passes a table name instead of a recordset into GetInsertSQL
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