<?php
/**
 * The main page for the Demo plugin
 *
 * @todo create more examples here.
 *
 * $Id: demo.php,v 1.1 2004/03/20 20:09:35 braverock Exp $
 */

// include the common files
require_once('../../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

//set target and see if we are logged in
$this = $_SERVER['REQUEST_URI'];
$session_user_id = session_check( $this );

$msg = $_GET['msg'];

//connect to the database
$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

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

<table border=0 cellpadding=0 cellspacing=0 width="100%">
    <tr>
        <td class=lcol width="75%" valign=top>
            <tr>
                Demo Plugin.  You would place your main page content here.
            </tr>
        </td>

        <!-- gutter //-->
        <td class=gutter width=1%>
        &nbsp;
        </td>

        <!-- right column //-->
        <td class=rcol width="24%" valign=top>

            <!-- opportunities //-->
            <?php  echo $opportunity_rows; ?>

            <!-- cases //-->
            <?php  echo $case_rows; ?>

            <!-- files //-->
            <?php  echo $file_rows; ?>

            <!-- notes //-->
            <?php  echo $note_rows; ?>

        </td>
    </tr>
</table>

<?php

end_page();

/**
 * $Log: demo.php,v $
 * Revision 1.1  2004/03/20 20:09:35  braverock
 * Initial Revision of Demo plugin to demonstrate using hooks
 *
 */
?>