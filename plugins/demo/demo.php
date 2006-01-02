<?php
/**
 * The main page for the Demo plugin
 *
 * @todo create more examples here.
 *
 * $Id: demo.php,v 1.5 2006/01/02 23:52:14 vanmer Exp $
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
$con = get_xrms_dbconnection();

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

//uncomment the debug line to see what's going on with the query
// $con->debug = 1;


//You would define any SQL you needed from the XRMS database here and execute it...

//close the database connection, as we are done with it.
$con->close();

$page_title = "Demo Plugin";
start_page($page_title);

?>

<div id="Main">
    <div id="Content">
                Demo Plugin.  You would place your main page content here.
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
 * $Log: demo.php,v $
 * Revision 1.5  2006/01/02 23:52:14  vanmer
 * - changed to use centralized dbconnection function
 *
 * Revision 1.4  2004/06/16 21:00:36  gpowers
 * - removed $this from session_check()
 *   - it is incompatible with PHP5
 *
 * Revision 1.3  2004/05/04 23:55:30  maulani
 * - Add CSS2 positioning to plugin demo.
 *
 * Revision 1.2  2004/03/29 13:26:57  maulani
 * - patch #922717 submitted by Glenn Powers (gpowers)
 * - fix table formatting
 *
 * Revision 1.1  2004/03/20 20:09:35  braverock
 * Initial Revision of Demo plugin to demonstrate using hooks
 *
 */
?>
