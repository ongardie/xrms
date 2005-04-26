<?php
/**
 * Common user interface functions file.
 *
 * $Id: utils-interface.php,v 1.56 2005/04/26 17:53:31 vanmer Exp $
 */

if ( !defined('IN_XRMS') )
{
  die('Hacking attempt');
  exit;
}

//include utils-misc if it isn't already defined, solves PHP 5 error
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'utils-preferences.php');

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
    case 'division_added':
      return _("Division Added.");

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
    
    if (!$xcon) {
        global $xrms_db_dbtype;
        global $xrms_db_server;
        global $xrms_db_username;
        global $xrms_db_password;
        global $xrms_db_dbname;
        $xcon = &adonewconnection($xrms_db_dbtype);
        $xcon->nconnect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
    }
    $user_id=$_SESSION['session_user_id'];

    
    $msg = status_msg($msg);
    if ($user_id) {
        $user_css_theme = get_user_preference($xcon, $user_id, 'css_theme');
	if ($user_css_theme) $css_theme=$user_css_theme;
    }
//    echo "    $css_theme = get_user_preference($con, $user_id, 'css_theme');";
    $curtheme = empty($css_theme) ? 'basic' : $css_theme;
    $cssroot = $http_site_root.'/css/';

    // Array containing list of named styles.
    //    array('basic' => '/path/to/basic.css');
    // If a particular style requires multiple files, specify them as a nested array
    //    array('multi' => array('first.css','second.css','third.css'));
    $cssthemes = array(
        'basic'       => array($cssroot.'basic/basic.css',
                               $http_site_root.'/js/jscalendar/calendar-blue.css'),
        'basic-left'  => array($cssroot.'basic/basic-left.css',
                               $cssroot.'basic/basic-left-ie.css',
                               $http_site_root.'/js/jscalendar/calendar-blue.css'),
        'green'       => $cssroot.'green/green.css',
        'green-left'  => array($cssroot.'green/green-left.css',
                               $cssroot.'green/green-left-ie.css'),
        'simple'      => array($cssroot.'simple/simple.css',
                               $cssroot.'simple/calendar-simple.css'),
        'simple-left' => array($cssroot.'simple/simple-left.css',
                               $cssroot.'simple/simple-left-ie.css',
                               $cssroot.'simple/calendar-simple.css')
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
    //quick and dirty hack to include and force logo style after all other styles have been applied
    //should also check system parameters to see if logo should be displayed
    if (get_system_parameter($xcon, 'Show Logo') == 'y') {
        echo css_link($cssroot.'logo.css', $curtheme, false);
    }
?>

</head>

  <body <?php do_hook('bodytags'); ?>>
  <?php do_hook('topofpage'); ?>
  <div id="page_header"><?php echo $page_title; ?></div>
<?php
  // Show navbar..
  if ($show_navbar) {
    render_nav_line();
  }

  // Show $msg, if present
  if (strlen($msg) > 0) {
    echo '  <div id="msg">'. $msg ."</div>\n";
  }
} // end start_page fn


//hack to fake ACL authentication until acl is completely integrated
if (!function_exists('check_object_permission_bool')) {
    function check_object_permission_bool($user, $object=false, $action='Read', $table=false) {
        return true;
    }
}

//hack to fake ACL authentication until acl is completely integrated
if (!function_exists('check_permission_bool')) {
    function check_permission_bool($user, $object=false, $id, $action='Read', $table=false) {
        return true;
    }
}

//hack to fake ACL authentication until acl is completely integrated
if (!function_exists('acl_get_list')) {
    function acl_get_list($user, $object) {
        return true;
    }
}

/**
 * function render_nav_line
 *
 * This function closes off the page structure.
 *
 * This function also contains the end_page hook to allow
 * for adding stuff to the page footer via a hook.
 *
 * Any common page footer would end this.
 */
function render_nav_line() {
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
      
      <?php if (check_object_permission_bool($_SESSION['session_user_id'], false, 'Read', 'activities')) echo http_root_href('/activities/some.php',    _("Activities")). ' &bull; '; ?> 
      <?php if (check_object_permission_bool($_SESSION['session_user_id'], false, 'Read', 'companies')) echo http_root_href('/companies/some.php',     _("Companies")).' &bull; '; ?>
      <?php if (check_object_permission_bool($_SESSION['session_user_id'], false, 'Read', 'contacts')) echo http_root_href('/contacts/some.php',      _("Contacts")).' &bull; '; ?>
      <?php if (check_object_permission_bool($_SESSION['session_user_id'], false, 'Read', 'campaigns')) echo http_root_href('/campaigns/some.php',     _("Campaigns")).' &bull; '; ?>
      <?php if (check_object_permission_bool($_SESSION['session_user_id'], false, 'Read', 'opportunities')) echo http_root_href('/opportunities/some.php', _("Opportunities")).' &bull; '; ?>
      <?php if (check_object_permission_bool($_SESSION['session_user_id'], false, 'Read', 'cases')) echo http_root_href('/cases/some.php',         _("Cases")).' &bull; '; ?>
      <?php if (check_object_permission_bool($_SESSION['session_user_id'], false, 'Read', 'files')) echo http_root_href('/files/some.php',         _("Files")).' &bull; '; ?>

<?php
    //place the menu_line hook before Reports and Adminstration link
    do_hook ('menuline');
?>
      <?php if (check_object_permission_bool($_SESSION['session_user_id'], 'Reports',  'Read')) echo http_root_href('/reports/index.php',      _("Reports")) . ' &bull; '; ?> 
      <?php if (check_object_permission_bool($_SESSION['session_user_id'], 'Administration', 'Read' )) echo http_root_href('/admin/routing.php',      _("Administration")). ' &bull; '; ?>
      <?php echo http_root_href('/admin/users/self.php', _("Preferences")); ?>
  </div><!-- end of navline -->
<?php

}
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
 * Retrieve menu of Salutations 
 *
 * @param  handle  $con database connection
 * @param  integer $salutation to set the menu to
 * @param  boolean $blank_salutation include a blank area
 * @return string  $salutation_menu the html menu to display
 */
function build_salutation_menu(&$con, $salutation='', $blank_salutation=false) {

    $sql = "
    SELECT salutation
    FROM salutations
    ORDER BY salutation_sort_value
    ";
	$rst = $con->execute($sql);
    if (!$rst) {
        db_error_handler($con, $sql);
    }
	$salutation_menu = $rst->getmenu('salutation', $salutation, $blank_salutation);
	$rst->close();
	
	return $salutation_menu;
} //end build_salutation_menu fn

/**
 * Retrieve menu of Address Types 
 *
 * @param  handle  $con database connection
 * @param  integer $address_type to set the menu to
 * @return string  $address_type_menu the html menu to display
 */
function build_address_type_menu(&$con, $address_type='') {

    $sql = "
    SELECT address_type
    FROM address_types
    ORDER BY address_type_sort_value
    ";
	$rst = $con->execute($sql);
    if (!$rst) {
        db_error_handler($con, $sql);
    }
	$address_type_menu = $rst->getmenu('address_type', $address_type, false);
	$rst->close();
	
	return $address_type_menu;
} //end build_salutation_menu fn

/*****************************************************************************/
/**
 * Function public string buildDataTable( array, [[array,] string] )
 *
 * Accepting an array for the data body and an optional
 * array for a header row, this function will create a grided
 * HTML table.
 *
 * @name buildDataTable
 * @author Walter Torres <walter@torres.ws>
 *
 * @category displayWidgets
 * @uses none
 * @requires none
 * @static
 * @final
 * @access public
 * @version
 *
 * @param array  $aryRecordSet Two dimensional array of data
 *        array  $aryHeader    flat array of with header text
 *        string $tableTitle   String to use as a Title for the Table
 * @return string $table       HTML string of TAble with data
 *
 **/
function buildDataTable ( $aryRecordSet = null,
                          $aryHeader = null,
                          $tableTitle = null
                        )
{
  /** Variable local string $table
   * @varstring $table holds generated HTML
   * @name var_name
   *
   * @abstract container to hold gnerated HTML as array is processed
   *
   * @access private
   * @static
   * @since 1.0
   *
   **/
    $table = '';

    // If an array is not sent, we don't do a thing
    if ( isset ( $aryRecordSet ) )
    {
        // Open Table TAG
        $table .= '<table class="widget" border="0">';

        // Table header, only if Title is defined
        if ( isset ( $tableTitle ) )
        {
            $table .= '<tr>';
            $table .=   '<td class="widget_header" colspan="' . count ( $aryHeader ) . '">';
            $table .=     $tableTitle;
            $table .=   '</td>';
            $table .= '</tr>'."\n";
        }

        // Column Header Row, only if defined
        if ( isset ( $aryHeader ) )
        {
            $table .= '<tr>';
            foreach ( $aryHeader as $strLabel )
            {
                $table .= '<td valign="top" class="widget_header">';
                $table .= $strLabel;
                $table .= '</td>';
            }
            $table .= '</tr>';
        }

        // We need to count the rows for alternating row display
        $rowCount = 0;

        // Loop through data array
        foreach ( $aryRecordSet as $aryRecSet )
        {
            // We need to track ROW count to display alternate ROW backgrounds
            $rowCount++;
            $style = ( $rowCount % 2 ) ? 'widget_content' : 'widget_content_alt';

            // each sub-array will be a single ROW of the table
            $table .= '<tr class="' . $style . '">';
            // Loop across the the sub-array
            foreach ( $aryRecSet as $strLabel )
            {
                $table .= '<td valign="top" class="'. $style . '">';
                $table .=    $strLabel;
                $table .= '</td>';
            }
            $table .= '</tr>';
        }

        // We're done, close the table
        $table .= '</table>';
    }

       return $table;
};

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

function javascript_tooltips_include() {
    global $javascript_tooltips_included;

	if(!isset($javascript_tooltips_included)) {
    	global $http_site_root;
        echo <<<EOQ
    		<!-- TOOLTIP SCRIPT INCLUDES -->
			<script type="text/javascript" src="$http_site_root/js/wz_tooltip.js"></script>
    		<!-- TOOLTIP SCRIPT INCLUDES -->
EOQ;
    	$javascript_tooltips_included = true;
	}
}

function render_edit_button($text='Edit', $type='submit', $onclick=false, $name=false, $id=false, $_table=false, $_id=false) {
    return render_ACL_button('Update', $text, $type, $onclick, $name, $id, $_table, $_id);
}

function render_delete_button($text='Delete', $type='submit', $onclick=false, $name=false, $id=false, $_table=false, $_id=false) {
    return render_ACL_button('Delete', $text, $type, $onclick, $name, $id, $_table, $_id);
}

function render_read_button($text='Read', $type='submit', $onclick=false, $name=false, $id=false, $_table=false, $_id=false) {
    return render_ACL_button('Read', $text, $type, $onclick, $name, $id, $_table, $_id);
}

function render_create_button($text='Create', $type='submit', $onclick=false, $name=false, $id=false, $_table=false, $_id=false) {
    return render_ACL_button('Create', $text, $type, $onclick, $name, $id, $_table, $_id);
}

function render_ACL_button($action, $text='Create', $type='submit', $onclick=false, $name=false, $id=false, $_table=false, $_id=false) {
    global $on_what_table;
    global $session_user_id;
    global $on_what_id;
    if ($_table) $table=$_table;
    else $table=$on_what_table;
    if ($_id) $cid=$_id;
    else $cid=$on_what_id;
    
    if (!$cid) { 
        if (!check_object_permission_bool($session_user_id, false, $action, $table))
            return false;
    } else {
        if (!check_permission_bool($session_user_id, false, $cid, $action,$table))
            return false;
    }
    return render_button($text, $type, $onclick, $name, $id);
}

function render_button($text='Edit', $type='submit', $onclick=false, $name=false, $id=false) {        
    $text=_($text);
    $ret= "<input class=button value=\"$text\"";
    if ($name) {
        $ret.=" name=\"$name\"";
    }
    if ($id) {
        $ret .= " id=\"$id\"";
    }
    if ($onclick) {
        $ret .= " onclick=\"$onclick\"";
    }
    if ($type) {
        $ret .= " type=\"$type\"";
    }
    $ret .=">";
    return $ret;
}

/**
 * Retrieve menu of XRMS users 
 *
 * @param  handle  $con database connection
 * @param  integer $user_id to set the menu to
 * @param  boolean $blank_user include a blank area
 * @return string  $user_menu the html menu to display
 */
function get_user_menu(&$con, $user_id='', $blank_user=false) {

    $sql = '
    SELECT ' . $con->Concat("first_names","' '","last_name") . " AS name, user_id
    FROM users
    WHERE user_record_status = 'a'
    ORDER BY last_name, first_names
    ";
	$rst = $con->execute($sql);
    if (!$rst) {
        db_error_handler($con, $sql);
    }
	$user_menu = $rst->getmenu2('user_id', $user_id, $blank_user);
	$rst->close();
	
	return $user_menu;
}

/**
 *
 * Creates an HTML SELECT list to display contents of an array
 *
 * @param adodbconnection $con
 * @param string $fieldname specifying html fieldname to use
 * @param integer $selected_value specifying which value should be selected by default
 * @param string $extra_html_elements  specifying any extra HTML attributes to be included inside the SELECT tag
 * @param bool $show_blank_first indicating if a blank record should be placed first in the select list (allows for no value to be submitted)
 *
 * @return string containing html widget for HTML SELECT Object
 */
function create_select_from_array($array, $fieldname, $selected_value=false, $extra_html_elements='', $show_blank_first=true) {
    if (!$array OR !$fieldname) return false;
    $html_element="<SELECT name=\"$fieldname\" $extra_html_elements>\n";
    if ($show_blank_first) $html_element.="<OPTION value=\"\">---Please select one---</OPTION>\n";
    foreach ($array as $akey=>$aval) {
        $selected=(($akey==$selected_value) ? ' SELECTED ' : '');
        $html_element.="<OPTION value=\"$akey\"$selected>$aval</OPTION>\n";
    }
    $html_element.="</SELECT>\n";
    return $html_element;
}

/**
 * $Log: utils-interface.php,v $
 * Revision 1.56  2005/04/26 17:53:31  vanmer
 * -check for system parameter for Show Logo before including logo.css
 *
 * Revision 1.55  2005/04/22 07:41:02  vanmer
 * - by default include stylesheet for logo display on every page
 * @TODO check system parameters before including stylesheet
 *
 * Revision 1.54  2005/04/15 07:02:32  vanmer
 * - added function to display array as an html select
 *
 * Revision 1.53  2005/04/11 19:34:41  gpowers
 * - replaced $body_tags var with bodytags plugin hook
 * - added topofpage plugin hook
 *
 * Revision 1.52  2005/04/11 02:07:56  maulani
 * - Add address-type menu
 *
 * Revision 1.51  2005/04/07 13:57:04  maulani
 * - Add salutation table to allow installation configurable list.  Also add
 *   many more default entries.
 *   RFE 913526 by algon.
 *
 * Revision 1.50  2005/03/29 19:10:44  gpowers
 * - based Reports ACL on object name, not table name (bug?)
 * - changed 'reports' to 'Reports' for consistancy
 *
 * Revision 1.49  2005/03/21 13:05:57  maulani
 * - Remove redundant code by centralizing common user menu call
 *
 * Revision 1.48  2005/03/04 13:31:39  braverock
 * - add 'simple' CSS styles to the array
 *   @todo make CSS theme selectable from system parameters and user prefs
 *
 * Revision 1.47  2005/02/14 21:27:13  vanmer
 * - updated fake acl_get_list to reflect expected behavior of real acl_get_list
 *
 * Revision 1.46  2005/02/10 23:20:49  daturaarutad
 * added function for including javascript tooltip code
 *
 * Revision 1.45  2005/02/08 17:54:38  vanmer
 * - changed user css_theme load to not override of previously set css_theme variable
 * - added builddatatable function (by Walter Torres)
 *
 * Revision 1.44  2005/01/28 22:59:22  braverock
 * - add msg for adding new division
 *
 * Revision 1.43  2005/01/25 06:01:27  vanmer
 * - added check for user preference of css theme if set
 * - altered ACL button functions to call centralized function
 *
 * Revision 1.42  2005/01/09 18:04:55  vanmer
 * - changed database ID to cid to avoid conflict with id for HTML element in all render_ button functions
 *
 * Revision 1.41  2005/01/09 17:04:54  vanmer
 * - added needed defaults to optional parameters in ACL stub functions
 * - added Preferences link to take a user to their own edit page
 *
 * Revision 1.40  2005/01/06 15:41:57  vanmer
 * - split up navigation line into seperate function from start_page
 * - added fake ACL functions to allow ACL integration to continue without breaking existing systems
 * - added functions to render navigation buttons (read, create, delete, update)
 * - added ACL checks to restrict navigation bar and button display
 *
 * Revision 1.39  2005/01/03 03:23:42  ebullient
 * additional theme (green), make User Manual link not a "header"
 *
 * Revision 1.38  2004/12/27 17:32:30  braverock
 * - added hook 'loginbar' for plugins to reference near the logged in user text.
 *
 * Revision 1.37  2004/12/23 20:05:29  daturaarutad
 * added check for globally defined CSS theme ($css_theme) to start_page()
 *
 * Revision 1.36  2004/12/22 23:43:42  ebullient
 * New Stylesheets, changes to add <link .. > lines for new sheets
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
