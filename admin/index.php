<?php
/**
 * Main page for the administration screens.
 *
 * $Id: index.php,v 1.7 2004/04/13 15:06:41 maulani Exp $
 */

//include required stuff
require_once('../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

//set target and see if we are logged in
$this = $_SERVER['REQUEST_URI'];
$session_user_id = session_check( $this );

//connect to the database
$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

//get the user info
$sql2 = "select username, user_id from users where user_record_status = 'a' order by username";
$rst = $con->execute($sql2);
$user_menu = $rst->getmenu2('user_id', '', false);
$rst->close();

$con->close();

$page_title = 'Administration';

start_page($page_title, true, $msg);
?>

<table border=0 cellpadding=0 cellspacing=0 width=100%>
    <tr>
        <td class=lcol width=65% valign=top>

        <table class=widget cellspacing=1 width=100%>
            <tr>
                <td class=widget_header>System Administration</td>
            </tr>
            <tr>
                <td class=widget_content><a href="reports/dashboard.php">Digital Dashboard</a></td>
            </tr>
            <tr>
                <td class=widget_content><a href="update.php">Database Structure Update</a></td>
            </tr>
            <tr>
                <td class=widget_content><a href="data_clean.php">Data Cleanup</a></td>
            </tr>
            <tr>
                <td class=widget_content>&nbsp;</td>
            </tr>
            <tr>
                <td class=widget_content>&nbsp;</td>
            </tr>
            <tr>
                <td class=widget_content>&nbsp;</td>
            </tr>
            <tr>
                <td class=widget_content>&nbsp;</td>
            </tr>
            <tr>
                <td class=widget_content>&nbsp;</td>
            </tr>
            <tr>
                <td class=widget_content>&nbsp;</td>
            </tr>
            <tr>
                <td class=widget_content>&nbsp;</td>
            </tr>
        </table>

        </td>

        <!-- gutter //-->
        <td class=gutter width=2%>&nbsp;</td>

        <!-- right column //-->
        <td class=rcol width=33% valign=top>

        <!-- import/export //-->
        <table class=widget cellspacing=1 width=100%>
            <tr>
                <td class=widget_header>Import/Export</td>
            </tr>
            <tr>
                <td class=widget_content><a href=import/import-companies.php>Import Companies/Contacts</a></td>
            </tr>
            <tr>
                <td class=widget_content><a href=export/export-companies.php>Export Companies/Contacts</a></td>
            </tr>
        </table>

        <!-- management //-->
        <table class=widget cellspacing=1 width=100%>
            <tr>
                <td class=widget_header>Manage</td>
            </tr>
            <tr>
                <td class=widget_content><a href=users/some.php>Users</a></td>
            </tr>
            <tr>
                <td class=widget_content><a href=roles/some.php>Roles</a></td>
            </tr>
            <tr>
                <td class=widget_content><a href=plugin/plugin-admin.php>Plugin Administration</td>
            </tr>
            <tr>
                <td class=widget_content><a href=categories/some.php>Categories</td>
            </tr>
            <tr>
                <td class=widget_content><a href=activity-types/some.php>Activity Types</td>
            </tr>
            <tr>
                <td class=widget_content><a href=industries/some.php>Industries</a></td>
            </tr>
            <tr>
                <td class=widget_content><a href=company-types/some.php>Company Types</a></td>
            </tr>
            <tr>
                <td class=widget_content><a href=company-sources/some.php>Company Sources</a></td>
            </tr>
            <tr>
                <td class=widget_content><a href=crm-statuses/some.php>CRM Statuses</a></td>
            </tr>
            <tr>
                <td class=widget_content><a href=account-statuses/some.php>Account Statuses</a></td>
            </tr>
            <tr>
                <td class=widget_content><a href=ratings/some.php>Ratings</a></td>
            </tr>
            <tr>
                <td class=widget_content><a href=opportunity-statuses/some.php>Opportunity Statuses</a></td>
            </tr>
            <tr>
                <td class=widget_content><a href=case-types/some.php>Case Types</a></td>
            </tr>
            <tr>
                <td class=widget_content><a href=case-statuses/some.php>Case Statuses</a></td>
            </tr>
            <tr>
                <td class=widget_content><a href=case-priorities/some.php>Case Priorities</a></td>
            </tr>
            <tr>
                <td class=widget_content><a href=campaign-types/some.php>Campaign Types</a></td>
            </tr>
            <tr>
                <td class=widget_content><a href=campaign-statuses/some.php>Campaign Statuses</a></td>
            </tr>
        </table>

        <!-- synchronization options //-->
        <table class=widget cellspacing=1 width=100%>
            <tr>
                <td class=widget_header>PDA Synchronization</td>
            </tr>
            <tr>
                <td class=widget_content>PDA synchronization has not been enabled on this system.</td>
            </tr>
        </table>

        </td>
    </tr>
</table>

<?php

end_page();

/**
 * $Log: index.php,v $
 * Revision 1.7  2004/04/13 15:06:41  maulani
 * - Add active contact data integrity check to database cleanup
 *
 * Revision 1.6  2004/04/12 18:59:01  maulani
 * - Make database structure and data cleanup available withing Admin interface
 *
 * Revision 1.5  2004/03/23 21:46:57  braverock
 * -remove (somewhat broken right now)
 *  SF bug 921912
 *
 * Revision 1.4  2004/03/21 18:15:36  braverock
 * - add plugin admin link to administration menu
 *
 * Revision 1.3  2004/01/26 21:19:27  braverock
 * - added page target
 * - added phpdoc
 *
 */
?>