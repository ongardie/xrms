<?php
/**
 * Common user interface functions file.
 *
 * $Id: utils-interface.php,v 1.38 2004/12/27 17:32:30 braverock Exp $
 */

if ( !defined('IN_XRMS') )
{
  die('Hacking attempt');
  exit;
}

//include utils-misc if it isn't already defined, solves PHP 5 error
require_once($include_directory . 'utils-misc.php');

/**
 * Include the i18n files, as every file with output will need them
 *
 * @todo sort out a better include strategy to simplify it across
 *       the XRMS code base.
 */
require_once($include_directory . 'i18n.php');

require_once ($include_directory.'plugin.php');

//set up internationalization
set_up_language($xrms_default_language, true, true);

/**
 * function status_msg
 *
 * This utility function will take a status code and turn it into a status message.
 */
function status_msg($msg) {
  switch ($msg) {

      // handle known messages
    case 'no_case':
      return _("No Case To Delete.");

    case 'company_added':
      return _("Company Added.");
    case 'company_deleted':
      return _("Company Deleted.");
    case 'contact_added':
      return _("Contact Added.");
    case 'contact_deleted':
      return _("Contact Deleted.");
    case 'address_added':
      return _("Address Added.");
    case 'address_deleted':
      return _("Address Deleted.");

    case 'campaign_added':
      return _("Campaign Added.");
    case 'campaign_deleted':
      return _("Campaign Deleted.");
    case 'opportunity_added':
      return _("Opportunity Added.");
    case 'opportunity_deleted':
      return _("Opportunity Deleted.");
    case 'activity_added':
      return _("Activity Added.");
    case 'activity_deleted':
      return _("Activity Deleted.");
    case 'case_added':
      return _("Case Added.");
    case 'case_deleted':
      return _("Case Deleted.");

    case 'added':
      return _("Added");
    case 'deleted':
      return _("Deleted");
    case 'password_no_match':
      return _("Password Does Not Match.");
    case 'noauth':
      return _("We could not authenticate you.") . ' ' . _("Please try again.");
    case 'saved':
      return _("Changes saved.");
    case 'no_change':
      return _("Status not changed.") . ' ' . _("This activity is still open.");

    // handle unknown messages
    default:
      if ( $msg ) {
        // at least TRY to return a message
        return _("$msg");
      }
      break;
  }
} //end status_msg fn

function http_root_href($url, $text, $title = NULL) {
    global $http_site_root;
    if ( empty($title) )
        $title = $text;
    return '<a title="'.$title.'" href="'.$http_site_root.$url.'">'.$text.'</a>';
}

function css_link($url, $name = null, $alt = true, $mtype = 'screen') {
    global $http_site_root;

    if ( empty($url) )
        return '';

    $onlyIE = strpos($url, '-ie') !== false;
    $ie1 = ( $onlyIE ) ? "<!--[if IE]>\n" : '';
    $ie2 = ( $onlyIE ) ? "<![endif]-->\n" : '';

    if ( strpos($url, 'print') !== false )
        $mtype = 'print';

    $href  = 'href="'.$url.'" ';
    $media = 'media="'.$mtype.'" ';

    if ( empty($name) ) {
        $title = '';
        $rel   = 'rel="stylesheet"';
    } else {
        $title =  empty($name) ? '' : 'title="'.$name.'" ';
        $rel   = 'rel="'.( $alt ? 'alternate ' : '' ).'stylesheet" ';
    }

    return $ie1.'  <link '.$media.$title.$rel.'type="text/css" '.$href." />\n".$ie2;
}

/**
 * function start_page
 *
 * This function is called to set up the page structure for all XRMS pages
 *
 * @param string  $page_title  Title for the page
 * @param boolean $show_navbar true/false whether to show the menu bar
 * @param string  $msg         error or other notification message
 */
function start_page($page_title = '', $show_navbar = true, $msg = '') {

    global $http_site_root;
    global $app_title;
    global $css_theme;

    $msg = status_msg($msg);

    $curtheme = empty($css_theme) ? 'basic' : $css_theme;
    $cssroot = $http_site_root.'/css/';


    // Array containing list of named styles.
    //    array('basic' => '/path/to/basic.css');
    // If a particular style requires multiple files, specify them as a nested array
    //    array('multi' => array('first.css','second.css','third.css'));
    $cssthemes = array(
        'basic'      => array($cssroot.'basic/basic.css',
                              $http_site_root.'/js/jscalendar/calendar-blue.css'),
        'basic-left' => array($cssroot.'basic/basic-left.css',
                              $cssroot.'basic/basic-left-ie.css',
                              $http_site_root.'/js/jscalendar/calendar-blue.css'),
                      );
?>
<!DOCTYPE HTML PUBLIC
    "-//W3C//DTD HTML 4.01 Transitional//EN"
    "http://www.w3.org/TR/html4/loose.dtd" />
<html>
<head>
  <title><?php echo "$app_title : $page_title"; ?></title>
<?php
    // CSS styles that apply to all media: basic layout and font/size attributes
    echo css_link($cssroot.'layout.css', null, false, 'all');
    echo css_link($cssroot.'style.css', null, false, 'all');
    // CSS styles that apply only to printed page - should after any 'all' media
    echo css_link($cssroot.'print.css');
    // CSS styles that apply only to screen rendering
    echo css_link($cssroot.'xrmsstyle.css');
    // base layout and style mods for display in IE
    echo css_link($cssroot.'xrmsstyle-ie.css');

    // Add stylesheets for defined themes (see comment above, re: $cssthemes)
    // If the URI contains -ie, treat as an ie-only stylesheet
    foreach ( $cssthemes as $theme => $attr )
    {
        if ( is_string($attr) ) {
            // Simple string format: $themename => $themeURI
            echo css_link($attr, $theme, ($theme != $curtheme));
        } elseif ( is_array($attr) ) {
            // Array of URIs for defined theme
            foreach ( $attr as $cssfile )
                echo css_link($cssfile, $theme, ($theme != $curtheme));
        }
    }
?>
</head>
<body>
  <div id="page_header"><?php echo $page_title; ?></div>
<?php
  // Show navbar..
  if ($show_navbar) {
    $session_username = $_SESSION['username'];
?>
  <div id="loginbar">
    <?php
        echo _("Logged in as") .': ' . $session_username .' &bull; '
               . http_root_href('/logout.php',             _("Logout"))
               . '<br>';
        do_hook('loginbar');
    ?>
  </div>
  <div id="navline">
      <?php echo http_root_href('/private/home.php',       _("Home")); ?> &bull;
      <?php echo http_root_href('/activities/some.php',    _("Activities")); ?> &bull;
      <?php echo http_root_href('/companies/some.php',     _("Companies")); ?> &bull;
      <?php echo http_root_href('/contacts/some.php',      _("Contacts")); ?> &bull;
      <?php echo http_root_href('/campaigns/some.php',     _("Campaigns")); ?> &bull;
      <?php echo http_root_href('/opportunities/some.php', _("Opportunities")); ?> &bull;
      <?php echo http_root_href('/cases/some.php',         _("Cases")); ?> &bull;
      <?php echo http_root_href('/files/some.php',         _("Files")); ?> &bull;

<?php
    //place the menu_line hook before Reports and Adminstration link
    do_hook ('menuline');
?>
      <?php echo http_root_href('/reports/index.php',      _("Reports")); ?> &bull;
      <?php echo http_root_href('/admin/routing.php',      _("Administration")); ?>
  </div><!-- end of navline -->
<?php
  }

  // Show $msg, if present
  if (strlen($msg) > 0) {
    echo '  <div id="msg">'. $msg ."</div>\n";
  }
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
function end_page($use_hook = true) {

    /**
     * place the end_page hook before we close the body and html
     * I don't think any of the tables should still be open, so a
     * hook writer would need to add thier own structure.
     */
  if ( $use_hook )
    do_hook ('end_page');
?>
</body>
</html>
<?php
} //end end_page fn

/**
 * build salutation menu
 *
 * @todo move the salutation strings into the database, and make configurable
 *
 * @param integer $salutation
 * @return string $salutation_menu
 */
function build_salutation_menu($salutation) {

    global $include_directory;

    $salutation_array = array(_("Mr."), _("Ms."), _("Mrs."), _("Miss"), _("Dr."), _("Rev."));

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

        global $jscalendar_included;

        if(!isset($jscalendar_included)) {
        echo <<<EOQ
    <!-- JSCALENDAR SCRIPT INCLUDES -->
    <script type="text/javascript" src="$http_site_root/js/jscalendar/calendar.js"></script>
    <script type="text/javascript" src="$http_site_root/js/jscalendar/lang/calendar-en.js"></script>
    <script type="text/javascript" src="$http_site_root/js/jscalendar/calendar-setup.js"></script>
    <!-- JSEND CALENDAR SCRIPT INCLUDES -->
EOQ;
            $jscalendar_included = true;
        }

} //end jscalendar_includes fn

/**
 * $Log: utils-interface.php,v $
 * Revision 1.38  2004/12/27 17:32:30  braverock
 * - added hook 'loginbar' for plugins to reference near the logged in user text.
 *
 * Revision 1.37  2004/12/23 20:05:29  daturaarutad
 * added check for globally defined CSS theme ($css_theme) to start_page()
 *
 * Revision 1.36  2004/12/22 23:43:42  ebullient
 * Sorry for the lack of comment on the css adds - meant to commit all of them
 * together, and hit the wrong key.
 *
 * Anyway, the css additions are the new stylesheets, I should have some
 * more samples after the holiday.
 *
 * The sheets are broken up into pieces:
 *  All Media
 *   layout.css - major content blocks
 *   style.css  - basic fonts, table and form alignment, some padding
 *
 *  Print Media
 *   print.css  - what makes it look pretty in the print preview
 *
 *  Screen Media
 *   xrmsstyle.css - Additional font/margin/padding adjustments for the screen only
 *   xrmsstyle-ie.css - Making it pretty in IE too
 *
 *  Alternate Style Sheets - Screen Media
 *    (if you have Mozilla/Firefox, there are extensions to switch between them)
 *
 *  basic:
 *   basic.css - Default Colors/backgrounds, etc. Should look familiar
 *
 *  basic-left:
 *   basic-left.css - extension of basic.css with sidebar on the left side
 *   basic-left-ie.css - make left sidebar look nice in IE, too.
 *
 *  I did not get everything done that I wanted today - fell down a rabbit hole playing
 *  with fonts.
 *
 *  Left to do (if anyone wants to beat me to it, have a blast):
 *
 *   * Change pages with no Sidebar:
 *         use ContentFullWidth instead of Content
 *         remove empty Sidebar section
 *      (Reports, Open Activities, New/Edit Case, New/Edit Note, Manage Category, etc.)
 *
 *   * Add additional class to "Activity" and "Search" tables that appear on
 *     various forms so they don't print..
 *         <div class="noprint">
 *             <... Activity or Search table thing ..>
 *         </div>
 *
 *   * Surround generated report information in a Report tag..
 *         <div class="noprint">
 *             <.. form for report input parameters ..>
 *         </div>
 *         <div id="generated_report">
 *             <.. table containing report output ..>
 *         </div>
 *
 *   * Change some of the admin forms (like New Role and New User) so that the
 *     form is in the Content section, and not in the Sidebar. (In fact, you can
 *     then remove the sidebar, as in the first bullet).
 *
 * Happy Holidays!
 *
 * Erin
 *
 * Revision 1.35  2004/10/25 23:57:01  daturaarutad
 * Added a check to jscalendar_includes() so that the files are only included once.
 *
 * Revision 1.34  2004/08/16 20:02:22  johnfawcett
 * - moved set_up_language() call into global scope to pick up strings that
 *   were not being translated because the gettext calls were done before
 *   the language had been setup.
 *
 * Revision 1.33  2004/08/12 11:10:19  braverock
 * - add require_once for utils-misc.php to solve SF bug 1005069
 *
 * Revision 1.32  2004/08/06 16:54:20  braverock
 * - pull i18n vars definitions in a 'global' in start_page fn
 *
 * Revision 1.31  2004/08/06 14:47:07  braverock
 * - push in changes to turn on i18n gettext
 *
 * Revision 1.30  2004/08/02 12:04:46  cpsource
 * - Per bug 997663, add confirm for delete of cases.
 *
 * Revision 1.29  2004/07/29 23:49:01  maulani
 * - update html to improve formatting
 *
 * Revision 1.28  2004/07/26 13:10:20  braverock
 * - added global $app_title to place it in function scope.
 *
 * Revision 1.27  2004/07/26 03:49:57  braverock
 * - added $app_title to browser page title
 *   - implements SF Feature Request # 966189
 *
 * Revision 1.26  2004/07/25 13:09:38  braverock
 * - remove trailing whitespace
 *
 * Revision 1.25  2004/07/25 13:07:55  braverock
 * - remove lang file require_once, as it is no longer used
 * - move salutation array into the build_salutation_menu fn
 * - localize the salutation strings in the array for now
 *
 * Revision 1.24  2004/07/22 15:19:02  gpowers
 * - Added status_msg's
 *   - Fixed SF bug [ 993841 ] unhandled $msg's
 *     - Submitted By: cpsource - cpsource
 *   - Also changed list order (to improve ease of code editing)
 *   - Checked and added matched Add/Delete pairs
 *
 * Revision 1.23  2004/07/21 23:50:36  introspectshun
 * - Finished localizing strings for i18n/l10n support
 *
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
