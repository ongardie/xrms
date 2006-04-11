<?php
/**
 * Main page for the administration screens.
 *
 * $Id: index.php,v 1.40 2006/04/11 13:26:31 braverock Exp $
 */

//include required stuff
require_once('../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check( 'Admin' );

// get display message
$msg = isset($_GET['msg']) ? $_GET['msg'] : '';

// stub out
if ( 0 ) {
  // open a connection to the database
  $con = get_xrms_dbconnection();

  // get the user info
  $user_menu = get_user_menu($con);

  // close the connection to the database
  $con->close();
}

$page_title = _("Administration");

start_page($page_title, true, $msg);
?>

<div id="Main">
    <div id="Content">

        <table class=widget cellspacing=1 width="100%">
            <tr>
                <td class=widget_header><?php echo _("System Administration"); ?></td>
            </tr>
            <!-- <tr>
                <td class=widget_content><a href="reports/dashboard.php"><?php echo _("Digital Dashboard"); ?></a></td>
            </tr> -->
            <tr>
                <td class=widget_content><a href="update.php"><?php echo _("Database Structure Update"); ?></a></td>
            </tr>
            <tr>
                <td class=widget_content><a href="data_clean.php"><?php echo _("Data Cleanup"); ?></a></td>
            </tr>
        </table>

        <table class=widget cellspacing=1 width="100%">
            <tr>
                <td class=widget_header><?php echo _("Documentation"); ?></td>
            </tr>
            <tr>
                <td><a href="../doc/users/XRMS_Administrator_Guide.pdf" target="_blank"><?php echo _("Administrator Guide"); ?></a> (PDF)</td>
            </tr>
            <?php do_hook('admin_docs'); ?>
        </table>

        <?php do_hook('admin_body_bottom'); ?>

    </div>

        <!-- right column //-->
    <div id="Sidebar">

        <!-- import/export //-->
        <table class=widget cellspacing=1 width="100%">
            <tr>
                <td class=widget_header><?php echo _("Import/Export"); ?></td>
            </tr>
            <tr>
                <td class=widget_content>
                    <a href="import/import-companies.php"><?php echo _("Import Companies/Contacts"); ?></a>
                </td>
            </tr>
            <tr>
                <td class=widget_content>
                    <a href="export/export-companies.php"><?php echo _("Export Companies/Contacts"); ?></a>
                </td>
            </tr>
            <tr>
                <td class=widget_content>
                    <a href="export/export-companies-ldap.php"><?php echo _("Export Companies/Contacts as LDAP/LDIF"); ?></a>
                </td>
            </tr>
            <tr>
                <td class=widget_content>
                    <a href="export/export-company-address.php"><?php echo _("Export Companies with address and phone info"); ?></a>
                </td>
            </tr>
        </table>

        <!-- management //-->
        <table class=widget cellspacing=1 width="100%">
            <tr>
                <td class=widget_header><?php echo _("Manage"); ?></td>
            </tr>
            <tr>
                <td class=widget_content>
                    <a href="acl/index.php"><?php echo _("ACL"); ?></a>
                </td>
            </tr>
            <tr>
                <td class=widget_content>
                    <a href="activity-templates/some.php"><?php echo _("Activity Templates"); ?></a>
                </td>
            </tr>
            <tr>
                <td class=widget_content>
                    <a href="email-templates/email_template_list.php"><?php echo _("Email Templates"); ?></a>
                </td>
            </tr>
            <tr>
                <td class=widget_content>
                    <a href="users/change-owner.php"><?php echo _("Change Record Owner"); ?></a>
                </td>
            </tr>
            <tr>
                <td class=widget_content>
                    <a href="country-address-format/index.php"><?php echo _("Country Localization Formats"); ?></a>
                </td>
            </tr>
            <tr>
                <td class=widget_content>
                    <a href="system-parameters/admin-prefs.php"><?php echo _("System Preferences"); ?></a>
                </td>
            </tr>
            <tr>
                <td class=widget_content>
                    <a href="users/some.php"><?php echo _("Users"); ?></a>
                </td>
            </tr>
        </table>

        <!-- types and statusest //-->
        <table class=widget cellspacing=1 width="100%">
            <tr>
                <td class=widget_header><?php echo _("Types and Statuses"); ?></td>
            </tr>
            <tr>
                <td class=widget_content>
                    <a href="account-statuses/some.php"><?php echo _("Account Statuses"); ?></a>
                </td>
            </tr>
            <tr>
                <td class=widget_content>
                    <a href="activity-types/some.php"><?php echo _("Activity Types"); ?></a>
                </td>
            </tr>
            <tr>
                <td class=widget_content>
                    <a href="activity-resolution-types/some.php"><?php echo _("Activity Resolution Types"); ?></a>
                </td>
            </tr>
            <tr>
                <td class=widget_content>
                    <a href="address-types/some.php"><?php echo _("Address Types"); ?></a>
                </td>
            </tr>
            <tr>
                <td class=widget_content>
                    <a href="campaign-statuses/some.php"><?php echo _("Campaign Statuses"); ?></a>
                </td>
            </tr>
            <tr>
                <td class=widget_content>
                    <a href="campaign-types/some.php"><?php echo _("Campaign Types"); ?></a>
                </td>
            </tr>
            <tr>
                <td class=widget_content>
                    <a href="case-priorities/some.php"><?php echo _("Case Priorities"); ?></a>
                </td>
            </tr>
            <tr>
                <td class=widget_content>
                    <a href="case-statuses/some.php"><?php echo _("Case Statuses"); ?></a>
                </td>
            </tr>
            <tr>
                <td class=widget_content>
                    <a href="case-types/some.php"><?php echo _("Case Types"); ?></a>
                </td>
            </tr>
            <tr>
                <td class=widget_content>
                    <a href="categories/some.php"><?php echo _("Categories"); ?></a>
                </td>
            </tr>
            <tr>
                <td class=widget_content>
                    <a href="company-sources/some.php"><?php echo _("Company Sources"); ?></a>
                </td>
            </tr>
            <tr>
                <td class=widget_content>
                    <a href="company-types/some.php"><?php echo _("Company Types"); ?></a>
                </td>
            </tr>
            <tr>
                <td class=widget_content>
                    <a href="crm-statuses/some.php"><?php echo _("CRM Statuses"); ?></a>
                </td>
            </tr>
            <tr>
                <td class=widget_content>
                    <a href="industries/some.php"><?php echo _("Industries"); ?></a>
                </td>
            </tr>
            <tr>
                <td class=widget_content>
                    <a href="opportunity-statuses/some.php"><?php echo _("Opportunity Statuses"); ?></a>
                </td>
            </tr>
            <tr>
                <td class=widget_content>
                    <a href="opportunity-types/some.php"><?php echo _("Opportunity Types"); ?></a>
                </td>
            </tr>
            <tr>
                <td class=widget_content>
                    <a href="ratings/some.php"><?php echo _("Ratings"); ?></a>
                </td>
            </tr>
            <tr>
                <td class=widget_content>
                    <a href="relationship-types/some.php"><?php echo _("Relationship Types"); ?></a>
                </td>
            </tr>
            <tr>
                <td class=widget_content>
                    <a href="salutations/some.php"><?php echo _("Salutations"); ?></a>
                </td>
            </tr>
        </table>

        <!-- plugins //-->
        <table class=widget cellspacing=1 width="100%">
            <tr>
                <td class=widget_header><?php echo _("Plugins"); ?></td>
            </tr>
            <tr>
                <td class=widget_content>
                    <a href="plugin/plugin-admin.php"><?php echo _("Plugin Activation"); ?></a>
                </td>
            </tr>
            <?php do_hook ('plugin_admin'); ?>
        </table>

    </div>

</div>

<?php

end_page();

/**
 * $Log: index.php,v $
 * Revision 1.40  2006/04/11 13:26:31  braverock
 * - change from do_hook_function to do_hook for admin_docs hook,
 *   as no return is expected, just direct output
 *
 * Revision 1.39  2006/04/09 14:24:24  braverock
 * - Add Administrator Guide PDF link
 * - add admin_docs plugin hook
 *
 * Revision 1.38  2006/04/09 00:41:08  braverock
 * - add hook admin_body_bottom
 *   patch requested by Jean-Noel Hayart
 *
 * Revision 1.37  2006/01/02 22:38:16  vanmer
 * - changed to use centralized dbconnection function
 *
 * Revision 1.36  2005/07/06 21:10:46  braverock
 * - add opportunity types
 *
 * Revision 1.35  2005/07/06 17:27:14  vanmer
 * - changed to use system preferences interface instead of system parameter interface
 *
 * Revision 1.34  2005/06/30 04:37:03  vanmer
 * - added link to activity resolution types admin interface
 *
 * Revision 1.33  2005/06/23 16:55:19  vanmer
 * - added link to newly created email template system
 *
 * Revision 1.32  2005/04/11 00:43:51  maulani
 * - Add address-types
 *
 * Revision 1.31  2005/04/10 17:34:22  maulani
 * - Add salutations
 *
 * Revision 1.30  2005/04/10 16:51:02  maulani
 * - Alphabetize types and statuses
 *
 * Revision 1.29  2005/03/28 17:03:01  gpowers
 * - added "Change Record Owner" Option under "Manage"
 * - removed extraneous blank lines under "System Administration"
 *
 * Revision 1.28  2005/03/21 13:05:58  maulani
 * - Remove redundant code by centralizing common user menu call
 *
 * Revision 1.27  2005/02/10 02:00:54  braverock
 * - disable digital dashboard link until we can fix the problems with this page
 *
 * Revision 1.26  2005/01/13 17:17:15  vanmer
 * - Added ACL Administration links, removed deprecated roles system
 *
 * Revision 1.25  2004/12/30 18:51:09  braverock
 * - localize strings
 * - patch provided by Ozgur Cayci
 *
 * Revision 1.24  2004/08/17 17:31:45  gpowers
 * - added sidebar section headings: Manage, Types and Statuses, Plugins
 * - changed "Plugin Administration" to "Plugin Activation"
 *   - each plugin should provide it's own "Administration"
 * - added 'plugin_admin' hook in "Plugins" section
 *
 * Revision 1.23  2004/07/27 13:13:30  braverock
 * - add export-company-address and export-companies-ldap to the list
 *
 * Revision 1.22  2004/07/16 18:52:43  cpsource
 * - Add role check inside of session_check
 *
 * Revision 1.21  2004/07/16 15:13:05  cpsource
 * - Prevent non-Admin's from running admin/index.php
 *
 * Revision 1.20  2004/07/16 13:52:00  braverock
 * - localize strings for i18n translation support
 *   - applies modified patches from Sebastian Becker (hyperpac)
 *
 * Revision 1.19  2004/07/16 12:35:10  cpsource
 * - Stub login feature for more debug.
 *
 * Revision 1.18  2004/07/16 12:22:20  cpsource
 * - Logic fix for checking roles
 *
 * Revision 1.17  2004/07/16 12:10:03  cpsource
 * - Add $role from routing to SESSION so that index.php
 *   can check we are Admin or Developer before we
 *   allow users to run admin.
 *
 *   Without this check, someone who's not logged in as anything
 *   can point their browser at xrms/admin/index.php and run
 *   the script.
 *
 * Revision 1.16  2004/07/14 16:24:22  maulani
 * - Add system parameters modification to administrative functions
 *
 * Revision 1.15  2004/07/12 18:49:47  neildogg
 * - Added link for relationship types management
 *
 * Revision 1.14  2004/07/07 20:46:25  neildogg
 * - Added support for phone format editing
 *
 * Revision 1.13  2004/06/16 20:54:59  gpowers
 * - removed $this from session_check()
 *   - it is incompatible with PHP5
 *
 * Revision 1.12  2004/06/14 18:13:51  introspectshun
 * - Add adodb-params.php include for multi-db compatibility.
 * - Now use ADODB GetInsertSQL, GetUpdateSQL functions.
 *
 * Revision 1.11  2004/06/03 16:14:40  braverock
 * - add functionality to support workflow and activity templates
 *   - functionality contributed by Brad Marshall
 *
 * Revision 1.10  2004/05/27 17:10:55  gpowers
 * removed PDA Synchronization sidebar. this feature doesn't exist in XRMS.
 *
 * Revision 1.9  2004/04/20 22:29:26  braverock
 * - add country address formats
 *   - modified from SF patch 938811 to fix SF bug 925470
 *
 * Revision 1.8  2004/04/15 22:04:37  maulani
 * - Change to CSS2 positioning
 * - Clean HTML to achieve validation
 *
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
