<?php
/**
 * Update an edited rating
 *
 * $Id: edit-2.php,v 1.2 2004/02/14 15:41:12 braverock Exp $
 */
require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

$session_user_id = session_check();

$rating_id = $_POST['rating_id'];
$rating_short_name = $_POST['rating_short_name'];
$rating_pretty_name = $_POST['rating_pretty_name'];
$rating_pretty_plural = $_POST['rating_pretty_plural'];
$rating_display_html = $_POST['rating_display_html'];

//make the ratings match the rating_pretty_name if the user didn't enter them
if (!strlen(rating_pretty_plural) > 0) { $rating_pretty_plural = $rating_pretty_name; }
if (!strlen(rating_display_html) > 0)  { $rating_display_html  = $rating_pretty_name; }

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

$sql = "update ratings set rating_short_name = " . $con->qstr($rating_short_name) . ", rating_pretty_name = " . $con->qstr($rating_pretty_name) . ", rating_pretty_plural = " . $con->qstr($rating_pretty_plural) . ", rating_display_html = " . $con->qstr($rating_display_html) . " WHERE rating_id = $rating_id";
$con->execute($sql);

$con->close();

header("Location: some.php");

/**
 * $Log: edit-2.php,v $
 * Revision 1.2  2004/02/14 15:41:12  braverock
 * - add phpdoc
 *
 */
?>
