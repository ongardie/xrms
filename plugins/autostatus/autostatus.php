<?php
/**
 * The main page for the Demo plugin
 *
 * @todo create more examples here.
 *
 * $Id: autostatus.php,v 1.1 2004/05/06 14:30:14 gpowers Exp $
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

//uncomment the debug line to see what's going on with the query
// $con->debug = 1;

//You would define any SQL you needed from the XRMS database here and execute it...

//close the database connection, as we are done with it.
$con->close();

$page_title = "Server Status";
start_page($page_title);

?>

<div id="Main">
    <div id="Content">
        <?php include("index.html");  // Edit this to point to your MRTG page ?>
    </div>
</div>

<?php

end_page();

/**
 * $Log: autostatus.php,v $
 * Revision 1.1  2004/05/06 14:30:14  gpowers
 * This is a simple plugin for including an Autostatus page in XRMS.
 *
 * Revision 1.1  2004/05/06 14:10:43  gpowers
 * This is a simple plugin for including an MRTG page in XRMS.
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
