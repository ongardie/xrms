<?php
/**
 * Common user interface functions file.
 *
 * $Id: utils-interface.php,v 1.22 2004/07/19 14:43:51 cpsource Exp $
 */

if ( !defined('IN_XRMS') )
{
  die('Hacking attempt');
  exit;
}

require_once ($include_directory.'plugin.php');

/**
 * function status_msg
 *
 * This utility function will take a status code and turn it into a status message.
 */
function status_msg($msg) {
    switch ($msg) {

      // handle know messages
        case 'noauth':
            return _("We could not authenticate you.") . ' ' . _("Please try again.");
        case 'saved':
            return _("Changes saved.");
        case 'activity_added':
            return _("Activity added.");
        case 'contact_added':
            return _("Contact added.");
        case 'company_added':
            return _("Company added.");
        case 'no_change':
            return _("Status not changed.") . ' ' . _("This activity is still open.");

    // handle unknown messages
    default:
      if ( $msg ) {
	// at least TRY to return a message
	return _("$msg.");
      }
      break;
    }
} //end status_msg fn

/**
 * function start_page
 *
 * This function is called to set up the page structure for all XRMS pages
 */
function start_page($page_title = '', $show_navbar = true, $msg = '') {

    global $page_title_height;
    global $http_site_root;

    $msg = status_msg($msg);

    $stylesheet = "$http_site_root/stylesheet.css";

    echo <<<EOQ
    <!DOCTYPE HTML PUBLIC
    "-//W3C//DTD HTML 4.01 Transitional//EN"
    "http://www.w3.org/TR/html4/loose.dtd" >
    <html>
    <head>
    <title>$page_title</title>
    <link rel=stylesheet href="$stylesheet">
    <link rel=stylesheet  type="text/css" href="$http_site_root/js/jscalendar/calendar-blue.css">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    </head>
    <body>
    <div id="page_header">$page_title</div>
EOQ;

    if ($show_navbar) {
      $session_username = $_SESSION['username'];
      echo <<<EOQ
    <div id="navline">
        <span id="navbar">
        <a href="$http_site_root/private/home.php">Home</a> &bull;
        <a href="$http_site_root/activities/some.php">Activities</a> &bull;
        <a href="$http_site_root/companies/some.php">Companies</a> &bull;
        <a href="$http_site_root/contacts/some.php">Contacts</a> &bull;
        <a href="$http_site_root/campaigns/some.php">Campaigns</a> &bull;
        <a href="$http_site_root/opportunities/some.php">Opportunities</a> &bull;
        <a href="$http_site_root/cases/some.php">Cases</a> &bull;
        <a href="$http_site_root/files/some.php">Files</a> &bull;
EOQ;

    //place the menu_line hook before Reports and Adminstration link
    do_hook ('menuline');

    echo <<<EOQ

        <a href="$http_site_root/reports/index.php">Reports</a> &bull;
        <a href="$http_site_root/admin/routing.php">Administration</a>
        </span>
        <div id="loginbar">Logged in as: $session_username &bull; <a href="$http_site_root/logout.php">Logout</a></div>
    </div>
EOQ;
    }

    if (strlen($msg) > 0) echo <<<EOQ
        <div id="msg">
            {$msg}
        </div>
EOQ;
} // end start_page fn

/**
 * function end_page
 *
 * This function closes off the page structure.
 *
 * This function also contains the end_page hook to allow
 * for adding stuff to the page footer via a hook.
 *
 * Any common page footer would end this.
 */
function end_page() {

    /**
     * place the end_page hook before we close the body and html
     * I don't think any of the tables should still be open, so a
     * hook writer would need to add thier own structure.
     */
    do_hook ('end_page');

    echo "
    </body>
    </html>\n";

} //end end_page fn

/**
 * build salutation menu
 *
 * @todo localize this using gettext, not included strings.
 *
 * @param integer $salutation
 * @return string $salutation_menu
 */
function build_salutation_menu($salutation) {

    global $include_directory;

    require_once($include_directory . 'lang/' . $_SESSION['language'] . '.php');

    // global $salutation_array;

    $salutation_menu  = "<select name=salutation>";
    $salutation_menu .= "\n<option value=0 > ";

    for ($i = 0; $i < sizeof($salutation_array); $i++) {
        $salutation_menu .= "\n<option value='" . $salutation_array[$i] . "'";
        if ($salutation == $salutation_array[$i]) {
            $salutation_menu .= " selected";
        }
        $salutation_menu .= ">" . $salutation_array[$i];
    }

    $salutation_menu .= "\n</select>";

    return $salutation_menu;
} //end build_salutation_menu fn

/*
 * JScalendar calendar widget settings
 * Patch by Miguel Gonçalves ( Mig77 at users.sourceforge.net)
 */

function jscalendar_includes() {

    global $http_site_root;

    echo <<<EOQ
    <!-- JSCALENDAR SCRIPT INCLUDES -->
    <script type="text/javascript" src="$http_site_root/js/jscalendar/calendar.js"></script>
    <script type="text/javascript" src="$http_site_root/js/jscalendar/lang/calendar-en.js"></script>
    <script type="text/javascript" src="$http_site_root/js/jscalendar/calendar-setup.js"></script>
    <!-- JSEND CALENDAR SCRIPT INCLUDES -->
EOQ;

} //end jscalendar_includes fn

/**
 * $Log: utils-interface.php,v $
 * Revision 1.22  2004/07/19 14:43:51  cpsource
 * - Don't repeat status_msg message if unknown type.
 *
 * Revision 1.21  2004/07/19 14:40:12  cpsource
 * - Remove unnecessary 'break' from status_msg
 *   Allow status_msg to at least TRY to return an error message
 *
 * Revision 1.20  2004/07/14 11:50:50  cpsource
 * - Added security feature IN_XRMS
 *
 * Revision 1.19  2004/07/13 15:44:03  maulani
 * - Make Unicode the default character set for XRMS
 *
 * Revision 1.18  2004/07/10 13:07:58  braverock
 * - change $include_locations to $include_direcectory
 *   - applies SF patch 976192 submitted by cpsource
 *
 * Revision 1.17  2004/07/10 12:52:47  braverock
 * - added global $include_directory
 *   - applies SF patch 976707 submitted by cpsource
 *
 * Revision 1.16  2004/07/02 15:01:22  maulani
 * - Move calendar stylesheet link into the head section of the webpage instead
 *   of the body.  Link statements in the body section are not valid.
 *
 * Revision 1.15  2004/06/21 15:50:57  braverock
 * - localized strings for i18n/internationalization/translation support
 *
 * Revision 1.14  2004/06/04 15:54:26  gpowers
 * Applied Patch [ 965012 ] Calendar replacement By: miguel Gonçves - mig77
 * (This code was orginially placed in vars.php)
 *
 * Revision 1.13  2004/06/03 16:32:13  braverock
 * - add functionality to support workflow and activity templates
 *   - functionality contributed by Brad Marshall
 *
 * Revision 1.12  2004/05/09 04:05:23  braverock
 * - change reports link to reports/index.php to help webservers that don't treat
 *   index.php as an auto-loaded index.
 *
 * Revision 1.11  2004/04/10 11:51:14  braverock
 * - remove trailing whitespace
 *
 * Revision 1.10  2004/04/09 19:54:42  braverock
 * - add Activities to top menu
 *
 * Revision 1.9  2004/04/06 21:59:16  maulani
 * - Begin conversion of positioning tables to CSS
 *   - Remove tables from all page headers
 *   - Position login with CSS
 *
 * Revision 1.8  2004/03/22 15:56:42  maulani
 * - Fix bug 921105 reported by maulani--partial display of menubar on
 *   screens that should not have a menubar
 *
 * Revision 1.7  2004/03/20 20:03:24  braverock
 * - add code to enable plugins
 * - add menuline and end_page hooks to start
 *
 * Revision 1.6  2004/03/12 15:46:52  maulani
 * Temporary change for use until full access control is implemented
 * - Block non-admin users from the administration screen
 * - Allow all users to modify their own user record and password
 * - Add phpdoc
 *
 * Revision 1.5  2004/02/16 20:14:11  maulani
 * Close table tag when nav bar not used
 *
 * Revision 1.4  2004/01/26 19:23:39  braverock
 * - moved interface functions from utils-misc.php
 *
 */
?>