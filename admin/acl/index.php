<?php
/**
 * The main page for the ACL Management pageset
 *
 * Copyright (c) 2004 Explorer Fund Advisors, LLC
 * All Rights Reserved.
 *
 * $Id: index.php,v 1.3 2005/05/10 13:28:14 braverock Exp $
 *
 * @todo write dashboard
 */

// include the common files
require_once('../../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

//set target and see if we are logged in
$this_page = $_SERVER['REQUEST_URI'];
$session_user_id = session_check();

$msg = $_GET['msg'];

//connect to the database
/*********************************/
/*** Include the sidebar boxes ***/

/** End of the sidebar includes **/
/*********************************/

//uncomment the debug line to see what's going on with the query
// $con->debug = 1;


//You would define any SQL you needed from the XRMS database here and execute it...


// put portfolio plugin specific data stuff in here

//close the database connection, as we are done with it.
global $css_theme;
$page_title = _("ACL Management");
$css_theme="basic-left";
start_page($page_title,true, $msg);

?>
<div id="Main">
    <?php include("xrms_acl_nav.php"); ?>
    <div id="Content">
        <table class=widget cellspacing=1 width="100%">
        <tr><td class=widget_header><?php echo _("Dashboard");?></td></tr>
            <tr>
                <td class=widget_content width="75%" valign=top>
          <?php echo _("This is the Access Control List system for XRMS"); ?>.<p>  <?php echo _("Please select a section to manage from the sidebar") ?>.
                </td>
            </tr>
        </table>
    </div>
</div>
<?php

end_page();

/**
 * $Log: index.php,v $
 * Revision 1.3  2005/05/10 13:28:14  braverock
 * - localized strings patches provided by Alan Baghumian (alanbach)
 *
 * Revision 1.2  2005/01/13 21:13:58  vanmer
 * - altered flippant text to reflect production environments
 *
 * Revision 1.1  2005/01/13 17:16:15  vanmer
 * - Initial Commit for ACL Administration interface
 *
 * Revision 1.2  2004/12/27 23:48:50  ke
 * - adjusted to reflect new stylesheet
 *
 * Revision 1.1  2004/12/02 09:32:58  ke
 * - initial revision of ACL management navigation links and main page
 * Bug 64
 *
 */
?>