<?php
/**
 * The main page for the Demo plugin
 *
 * @todo create more examples here.
 *
 * $Id: autostatus.php,v 1.3 2004/07/22 13:15:30 gpowers Exp $
 */

// include the common files
require_once('../../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

$session_user_id = session_check();

$msg = $_GET['msg'];

//connect to the database
$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

//uncomment the debug line to see what's going on with the query
// $con->debug = 1;

/*********************************/
/*** Include the sidebar boxes ***/
//include the Cases sidebar
$case_limit_sql = "and cases.user_id = $session_user_id";
require_once($xrms_file_root."/cases/sidebar.php");

//include the opportunities sidebar
$opportunity_limit_sql = "and opportunities.user_id = $session_user_id \nand status_open_indicator = 'o'";

require_once($xrms_file_root."/opportunities/sidebar.php");

//include the files sidebar
require_once($xrms_file_root."/files/sidebar.php");

//include the notes sidebar
require_once($xrms_file_root."/notes/sidebar.php");

/** End of the sidebar includes **/
/*********************************/

//You would define any SQL you needed from the XRMS database here and execute it...

//close the database connection, as we are done with it.
$con->close();

$page_title = _("Server Status");
start_page($page_title);

?>

<div id="Main">
    <div id="Content">
        <?php include("index.html");  // Edit this to point to your MRTG page ?>
    </div>
</div>

        <!-- right column //-->
    <div id="Sidebar">

            <!-- opportunities //-->
            <?php  echo $opportunity_rows; ?>

            <!-- cases //-->
            <?php  echo $case_rows; ?>

            <!-- files //-->
            <?php  echo $file_rows; ?>

            <!-- notes //-->
            <?php  echo $note_rows; ?>

    </div>
</div>

<?php

end_page();

/**
 * $Log: autostatus.php,v $
 * Revision 1.3  2004/07/22 13:15:30  gpowers
 * - added sidebars
 * - i18n'ed page_title
 * - removed unrelated phpdoc notes
 *
 * Revision 1.2  2004/06/16 21:00:36  gpowers
 * - removed $this from session_check()
 *   - it is incompatible with PHP5
 *
 * Revision 1.1  2004/05/06 14:30:14  gpowers
 * This is a simple plugin for including an Autostatus page in XRMS.
 *
 */
?>
