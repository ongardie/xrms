<?php
/**
 * The main page for the Demo plugin
 *
 * @todo create more examples here.
 *
 * $Id: head_design.php,v 1.1 2004/08/04 15:29:17 gpowers Exp $
 */

global $xrms_file_root;

/*********************************/
/*** Include the sidebar boxes ***/
//include the Cases sidebar
$case_limit_sql = "and cases.user_id = $session_user_id";
//require_once($xrms_file_root."/cases/sidebar.php");

//include the opportunities sidebar
$opportunity_limit_sql = "and opportunities.user_id = $session_user_id \nand status_open_indicator = 'o'";

//require_once($xrms_file_root."/opportunities/sidebar.php");

//include the files sidebar
//require_once($xrms_file_root."/files/sidebar.php");

//include the notes sidebar
//require_once($xrms_file_root."/notes/sidebar.php");

/** End of the sidebar includes **/
/*********************************/

//uncomment the debug line to see what's going on with the query
// $con->debug = 1;


//You would define any SQL you needed from the XRMS database here and execute it...

//close the database connection, as we are done with it.
//$con->close();

$page_title = "Bookmarks";
start_page($page_title);

?>

<div id="Main">
    <div id="Content">
