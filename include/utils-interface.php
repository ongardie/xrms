<?php
/**
 * Common user interface functions file.
 *
 * @package XRMS_API
 *
 * $Id: utils-interface.php,v 1.107 2006/07/12 01:02:29 vanmer Exp $
 */

if ( !defined('IN_XRMS') )
{
  die('Hacking attempt');
  exit;
}

//include utils-misc if it isn't already defined, solves PHP 5 error
require_once($include_directory . 'vars.php');
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
    case 'noperm':
      return _("You are not authorized to perform this function.");
    case 'saved':
      return _("Changes saved.");
    case 'no_change':
      return _("Status not changed.") . ' ' . _("This activity is still open.");
    case 'no_auto_change':
      return _("Status not automatically changed.") . ' ' . _("Status should be changed by hand to reflect resolution.");
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

/**
 * Function to create a URL string relative to $http_site_root, with optional text and title
 *
**/
function http_root_href($url, $text, $title = NULL) {
    global $http_site_root;
    if ( empty($title) )
        $title = $text;
        $title = _("$title");
        $text  = _("$text");
    return '<a title="'.$title.'" href="'.$http_site_root.$url.'">'.$text.'</a>';
}

/**
 * Function to create stylesheet links that will work for multiple browsers
 *
**/
function css_link($url, $name = null, $alt = true, $mtype = 'screen') {
    global $http_site_root;

    $is_IE=false;

    if ( empty($url) )
        return '';
    // set to lower case to avoid errors
    $browser_user_agent = strtolower( $_SERVER['HTTP_USER_AGENT'] );


    if (stristr($browser_user_agent, "msie 4"))
    {
        $browser = 'msie4';
        $dom_browser = false;
        $is_IE = true;
    }
    elseif (stristr($browser_user_agent, "msie"))
    {
        $browser = 'msie';
        $dom_browser = true;
        $is_IE = true;
    }


    if ((strpos($url, '-ie')!== false) and !$is_IE) {
        //not IE, so don't render this sheet
        return;
    }

    if ( strpos($url, 'print') !== false )
        $mtype = 'print';

    $href  = 'href="'.$url.'" ';
    $media = 'media="'.$mtype.'" ';

    if ( empty($name) ) {
        $title = '';
        $rel   = 'rel="stylesheet" ';
    } else {
        $title =  empty($name) ? '' : 'title="'.$name.'" ';
        $rel   = 'rel="'.( $alt ? 'alternate ' : '' ).'stylesheet" ';
    }

    return '    <link '.$media.$title.$rel.'type="text/css" '.$href." />\n";
}

/**
 * Function to list files within a css directory, used when querying for theme directories
 *
**/
function list_css_files($cssdir,$cssroot) {
    if (!$cssroot OR !$cssdir) return false;
   if (is_dir($cssdir)) {
        $files=array();
        if ($dh = opendir($cssdir)) {
            while (($file = readdir($dh)) !== false) {
                if ((strlen($file)>3) AND strtolower(substr($file,strlen($file)-3,3))=='css') {
                    $files[]="$cssroot/$file";
                }
            }
        }
        closedir($dh);
    }
    if ($files) {
        sort($files);
        return $files;
    }
    return false;
}

/**
 * Function to retrieve the available css themes from the disk, or from the session if they have already be read
 *
**/
function get_css_themes() {
    global $http_site_root;
    global $xrms_file_root;
    //if themes have been retrieved, use them from the session
    if ($_SESSION) {
        if (array_key_exists('xrms_css_themes',$_SESSION)) {
            return $_SESSION['xrms_css_themes'];
        }
    }
    $css_themes=array();
    $cssroot = $http_site_root.'/css';
    if (!is_dir($xrms_file_root)) {
        echo '"'.$xrms_file_root.'" '._("is not a directory, please check your configuration.")."\n";
    }
    $cssdir = $xrms_file_root.'/css/';
    if (!is_dir($cssdir)) {
        $cssdir=realpath('css');
    }
    if (is_dir($cssdir)) {
        if ($dh = opendir($cssdir)) {
            while (($file = readdir($dh)) !== false) {
                if ($file!='..' AND $file!='.' AND $file!='CVS') {
                    $csssubdir=$cssdir.DIRECTORY_SEPARATOR.$file;
                    if (is_dir($csssubdir)){

                        $css_files=list_css_files($csssubdir, "$cssroot/$file");
                        $css_themes[$file]=$css_files;
                    }
                }
            }
            closedir($dh);
        }
    } else {
        echo '"'.$cssdir.'" '._("is not a directory, please check your configuration.")."\n";
    }
    $_SESSION['xrms_css_themes']=$css_themes;
    return $css_themes;
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
function start_page($page_title = '', $show_navbar = true, $msg = '', $show_topnav=true) {
    global $http_site_root;
    global $app_title;
    global $css_theme;
    global $xrms_notAlias;

    //get the database connection
    if (!isset($xcon) OR !$xcon) {
        $xcon=@get_xrms_dbconnection();
    }

    $user_id=$_SESSION['session_user_id'];
    $msg = status_msg($msg);

    if ($xcon->_connectionID) {
    ob_start();
    if ($user_id) {
        $user_css_theme = get_user_preference($xcon, $user_id, 'css_theme');
    } else $user_css_theme = get_admin_preference($xcon, 'css_theme');
    if ($user_css_theme) $css_theme=$user_css_theme;
//    echo "    $css_theme = get_user_preference($con, $user_id, 'css_theme');";
    ob_end_clean();
    }
    $curtheme = empty($css_theme) ? 'basic' : $css_theme;
    // Array containing list of named styles.
    //    array('basic' => '/path/to/basic.css');
    // If a particular style requires multiple files, specify them as a nested array
    //    array('multi' => array('first.css','second.css','third.css'));
    $cssroot=$http_site_root.'/css/';
    $cssthemes=get_css_themes();

    //pull in the $languages array into function scope
    global $languages;
?>
<!DOCTYPE HTML PUBLIC
    "-//W3C//DTD HTML 4.01 Transitional//EN"
    "http://www.w3.org/TR/html4/loose.dtd" />
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=<?php echo $languages[$xrms_notAlias]['CHARSET']; ?>">
    <title><?php echo "$app_title : $page_title"; ?></title>
<?php
    // include the jscalendar scripts
    jscalendar_includes();
    // CSS styles that apply to all media: basic layout and font/size attributes
    echo css_link($cssroot.'layout.css', null, false, 'all');
    echo css_link($cssroot.'style.css', null, false, 'all');
    // CSS style for treeview widget mktree.js
    echo css_link($cssroot.'mktree.css');
    // CSS styles that apply only to printed page - should after any 'all' media
    echo css_link($cssroot.'print.css');
    // CSS styles that apply only to screen rendering
    echo css_link($cssroot.'xrmsstyle.css');
    // base layout and style mods for display in IE
    echo css_link($cssroot.'xrmsstyle-ie.css');
    //quick and dirty hack to include and force logo style before the theme styles have been applied
    //should also check system parameters to see if logo should be displayed
    ob_start();
    $show_logo_pref=get_system_parameter($xcon, 'Show Logo');
    //show logo by default, if no preference is set
    if (!$show_logo_pref) $show_logo_pref='y';
    ob_end_clean();
    if (($show_logo_pref == 'y') AND $show_topnav) {
        echo css_link($cssroot.'logo.css', $curtheme, false);
    }

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
<?php if ($show_topnav) { ?>
  <body <?php do_hook('bodytags'); echo "DIR=".$_SESSION['DIR']; ?>>
  <?php do_hook('topofpage'); ?>
  <div id="page_header"><?php echo $page_title; ?><span id="header_logo"></span></div>
<?php
  // Show navbar..
  if ($show_navbar) {
    render_nav_line();
  }

  // Show $msg, if present
  if (strlen($msg) > 0) {
    echo '  <div id="msg">'. $msg ."</div>\n";
  }
  } //end check for topnav variable
} // end start_page fn

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

      <?php
            $item_separator=' &bull; ';
            if ($_SESSION['active_nav_items']) {
                $active_nav_items=$_SESSION['active_nav_items'];
            } else {
                $active_nav_items=get_active_nav_items();
                $_SESSION['active_nav_items']=$active_nav_items;
            }

            foreach ($active_nav_items as $type=>$item) {
                $output_html_array[]=http_root_href($item['href'],   $item['title']);
            }
            $output_html=implode($item_separator,$output_html_array);
            echo $output_html;

            //end of menuline hook, now deprecated in favor of menuline_nav_items
            do_hook ('menuline');
      ?>

  </div><!-- end of navline -->
<?php

}

/**
 * function get_active_nav_items
 *
 * This function returns an array of navigational items that the current user has permisison to
 *
 */
function get_active_nav_items() {
    $active_nav_items=array();
    global $nav_items;
    $nav_items=array();
    $nav_items['home']=array('href'=>'/private/home.php', 'title'=>_("Home"), 'object'=>'User');
    $nav_items['activity']=array('href'=>'/activities/some.php', 'table'=>'activities', 'title'=>_("Activities"));
    $nav_items['company']=array('href'=>'/companies/some.php', 'table'=>'companies', 'title'=>_("Companies"));
    $nav_items['contact']=array('href'=>'/contacts/some.php', 'table'=>'contacts', 'title'=>_("Contacts"));
    $nav_items['campaign']=array('href'=>'/campaigns/some.php', 'table'=>'campaigns', 'title'=>_("Campaigns"));
    $nav_items['opportunity']=array('href'=>'/opportunities/some.php', 'table'=>'opportunities', 'title'=>_("Opportunities"));
    $nav_items['case']=array('href'=>'/cases/some.php', 'table'=>'cases', 'title'=>_("Cases"));
    $nav_items['file']=array('href'=>'/files/some.php', 'table'=>'files', 'title'=>_("Files"));
    do_hook ('menuline_nav_items');
    $nav_items['reports']=array('href'=>'/reports/index.php', 'object'=>'Reports', 'title'=>_("Reports"));
    $nav_items['administration']=array('href'=>'/admin/routing.php', 'object'=>'Administration', 'title'=>_("Administration"));
    $nav_items['preferences']=array('href'=>'/admin/users/self.php',  'title'=>_("Preferences"), 'object'=>'User');
    foreach ($nav_items as $nav_key=>$item) {
        //if the navigation item specifies an object or a table, check the permission on it before making active
        if ($item['object'] OR $item['table']) {
            if (check_object_permission_bool($_SESSION['session_user_id'], $item['object'], 'Read', $item['table'])) {
                $active_nav_items[$nav_key]=$item;
            }
        } else {
            //otherwise, since no object/table was specified, item is always shown
            $active_nav_items[$nav_key]=$item;
        }
    }
    return $active_nav_items;
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

    global $session_user_id;
    if (!$session_user_id) { $user_id=0; }
    else { $user_id=$session_user_id; }
    global $con;
    if (!isset($econ)) @$econ=get_xrms_dbconnection();

    echo "\n".'<div id="footer">'."\n";

    if ( $use_hook ){
        /**
        * place the end_page hook before we close the body and html
        * I don't think any of the tables should still be open, so a
        * hook writer would need to add thier own structure.
        */
        do_hook ('end_page');
    }
    ob_start();
    $block_sf_page = get_user_preference($econ, $user_id, 'block_sf_link' );
    $hide_sf_image = get_user_preference($econ, $user_id, 'hide_sf_img');
    ob_end_clean();

    $alt_string=htmlspecialchars(_("XRMS SourceForge Project Page"));

    if ($hide_sf_image=='y') {
        $sf_image_attributes=' height="0" width="0"';
    } else { $sf_image_attributes="alt=\"$alt_string\""; }
    $econ->close();


    if ($block_sf_page!='y') {
        $alt_string=htmlspecialchars(_("XRMS SourceForge Project Page"));
        echo <<<TILLEND
        <A href="http://sourceforge.net/projects/xrms/">
                <IMG src="http://sourceforge.net/sflogo.php?group_id=88850&amp;type=1" border="0"
                    $sf_image_attributes
                 />
        </A>
TILLEND;
    }
    echo <<<TILLEND

    </div>
    </body>
    </html>
TILLEND;
}

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

/**
 * Retrieve menu of CRM Statuses
 *
 * @param  handle  $con database connection
 * @param  integer $crm_status_id to set the menu to (default)
 * @param  boolean $blank_crm_status include a blank area
 * @return string  $crm_status_menu the html menu to display
 */
function build_crm_status_menu(&$con, $crm_status_id='', $blank_crm_status=false) {
    $sql = "select crm_status_pretty_name, crm_status_id from crm_statuses where
            crm_status_record_status = 'a' order by sort_order";
    $rst = $con->execute($sql);
    if (!$rst) {
        db_error_handler($con, $sql);
    }
    $crm_status_menu = translate_menu($rst->getmenu2('crm_status_id', $crm_status_id, $blank_crm_status));
    $rst->close();

    return $crm_status_menu;
} //end build_crm_status_menu fn

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

/**
 * Function to add javascript include for the tooltips widget, ensure that this widget is only output once
 * Can be set to echo (by default) or return HTML string
 *
**/
function javascript_tooltips_include($output=true) {
    global $javascript_tooltips_included;
    $ret=false;
    if(!isset($javascript_tooltips_included)) {
        global $http_site_root;
        $ret = <<<EOQ
            <!-- TOOLTIP SCRIPT INCLUDES -->
            <script type="text/javascript" src="$http_site_root/js/wz_tooltip.js"></script>
            <!-- TOOLTIP SCRIPT INCLUDES -->
EOQ;
    if ($output) echo $ret;
        $javascript_tooltips_included = true;
    }
    return $ret;
}

/**
 * Function to add javascript include for the mktree widget, ensure that this widget is only output once
 * Can be set to echo (by default) or return HTML string
 *
**/
function javascript_mktree_include($output=true) {
    global $javascript_mktree_included;
    $ret=false;
    if (!isset($javascript_mktree_included)) {
        global $http_site_root;
    $ret = <<<EOQ
<!-- MKTREE SCRIPT INCLUDES -->
<script type="text/javascript" src="$http_site_root/js/mktree.js"></script>
<!-- MKTREE SCRIPT INCLUDES -->
EOQ;
        if ($output) echo $ret;
        $javascript_mktree_included=true;
    }
    return $ret;
}

/**
 * This function is a wrapper for render_ACL_button to query for Update permission on an object
**/
function render_edit_button($text='Edit', $type='submit', $onclick=false, $name=false, $id=false, $_table=false, $_id=false) {
    return render_ACL_button('Update', $text, $type, $onclick, $name, $id, $_table, $_id);
}

/**
 * This function is a wrapper for render_ACL_button to query for Export permission on an object
**/
function render_export_button($text='Export', $type='button', $onclick=false, $name=false, $id=false, $_table=false, $_id=false) {
    return render_ACL_button('Export', $text, $type, $onclick, $name, $id, $_table, $_id);
}

/**
 * This function is a wrapper for render_ACL_button to query for Delete permission on an object
**/
function render_delete_button($text='Delete', $type='submit', $onclick=false, $name=false, $id=false, $_table=false, $_id=false) {
    return render_ACL_button('Delete', $text, $type, $onclick, $name, $id, $_table, $_id);
}

/**
 * This function is a wrapper for render_ACL_button to query for Read permission on an object
**/
function render_read_button($text='Read', $type='submit', $onclick=false, $name=false, $id=false, $_table=false, $_id=false) {
    return render_ACL_button('Read', $text, $type, $onclick, $name, $id, $_table, $_id);
}

/**
 * This function is a wrapper for render_ACL_button to query for Create permission on an object
**/
function render_create_button($text='Create', $type='submit', $onclick=false, $name=false, $id=false, $_table=false, $_id=false) {
    return render_ACL_button('Create', $text, $type, $onclick, $name, $id, $_table, $_id);
}

/**
 * Function to render action buttons wrapper with an ACL permission check.
 * This function can be used directly or the wrapper functions for each basic type of button (create, read, update, delete and export).
 * Will return either a string with the button if permission is granted, or a blank string if not permission is given
 *
 * @param string $action with string describing the action ('Create', 'Read', 'Update', 'Delete', 'Export') the button will have
 * @param string $text with text to display on the button
 * @param string $type with type of HTML input this button will be, defaults to 'submit'
 * @param string $onclick with string for onclick handler for the button
 * @param string $name with HTML name of the button
 * @param integer $id optionally providing database identifier for the entity the action would apply to (otherwise uses global $on_what_id)
 * @param string $_table optionally providing tablename the entity in question exists in (otherwise uses global $on_what_table)
 *
 * @return string with HTML for button, or blank string if no permission is granted
**/
function render_ACL_button($action, $text='Create', $type='submit', $onclick=false, $name=false, $id=false, $_table=false, $_id=false) {
    global $on_what_table;
    global $session_user_id;
    global $on_what_id;
    if ($_table) $table=$_table;
    else $table=$on_what_table;
    if ($_id) $cid=$_id;
    else { if (!$_table) $cid=$on_what_id; else $cid=false;}


    if (!$cid) {
        if (!check_object_permission_bool($session_user_id, false, $action, $table))
            return false;
    } else {
        if (!check_permission_bool($session_user_id, false, $cid, $action,$table))
            return false;
    }
    return render_button($text, $type, $onclick, $name, $id);
}

/**
 * Function to create a string with the HTML for an input button
 *
**/
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
 * Retrieve menu of XRMS activity types
 *
 * @param  handle  $con database connection
 * @param  integer $activity_type_id to set the menu to
 * @param string $fieldname to change the default html fieldname of 'user_id'
 * @param  boolean $blank_user include a blank area
 * @return string  $activity_type_menu the html menu to display
 */
function get_activity_type_menu($con, $activity_type_id='', $fieldname='activity_type_id', $blank_type=false) {
    // create menu of activity types
    $sql = "SELECT activity_type_pretty_name, activity_type_id
            FROM activity_types
            WHERE activity_type_record_status = 'a'
            ORDER BY sort_order, activity_type_pretty_name";
    $rst = $con->execute($sql);
    if ($rst) {
        $activity_type_menu = $rst->getmenu2($fieldname, $activity_type_id, $blank_type, false,0, 'style="font-size: x-small; border: outset; width: 80px;"');
        $rst->close();
    }
    return $activity_type_menu;
}

/**
 * Retrieve menu of XRMS users
 *
 * @param handle  $con database connection
 * @param integer $user_id to set the menu to
 * @param boolean $blank_user include a blank area
 * @param string  $fieldname to change the default html fieldname of 'user_id'
 * @param boolean $truncate whether to force the drop-down to be narrow
 * @return string  $user_menu the html menu to display
 */
function get_user_menu(&$con, $user_id='', $blank_user=false, $fieldname='user_id', $truncate=true) {

    $sql = '
    SELECT ' . $con->Concat("last_name","', '","first_names") . " AS name, user_id
    FROM users
    WHERE user_record_status = 'a'
    ORDER BY last_name, first_names
    ";
    $rst = $con->execute($sql);
    if (!$rst) {
        db_error_handler($con, $sql);
    }
    if ($truncate) {
        $width_style = ' width: 80px; ';
    } else {
        $width_style = '';
    }
    $user_menu = $rst->getmenu2($fieldname, $user_id, $blank_user, false, 0, 'style="font-size: x-small; border: outset;'.$width_style.'"');
    $rst->close();

    return $user_menu;
}
/**
 * Creates an HTML form element based on parameters, and returns it as a string
 *
 *
 * @param string $element_type specifying type of element, currently supported: checkbox, select, radio, textarea and text (default)
 * @param string $element_name specifying the name of the html element
 * @param string $element_value optionally providing a value to set for the element
 * @param string $element_extra_attributes optionally providing extra parameters to the html element, added to end of element tag
 * @param integer $element_length optionally providing a length for attributes that can use it (text and textarea)
 * @param integer $element_height optionally providing a height for the attribute (only textarea)
 * @param array $possible_values providing values to populate, required for select and radio, should be in format so that array key is value of the element, and array value is the text to display
 * @param boolean $show_blank_first optionally specifying if a blank row should be added to element (select only)
 *
 * @return string $html with html element defined by parameters
 */
function create_form_element($element_type, $element_name, $element_value=false, $element_extra_attributes='', $element_length=false, $element_height=false, $possible_values=false, $show_blank_first=false, $read_only=false, $use_keys_for_option_values=true) {
if (!$element_type) return false;
if (!$element_name) return false;
if ($read_only) $element_extra_attributes.=' readonly disabled';

switch ($element_type) {
  case "checkbox":
    if ($element_value) { $show_value=$element_value; }
    else { $show_value=1; }
    $html .= "<input type=checkbox value=\"$show_value\" name=\"$element_name\"";
    if ($element_value) $html .= " CHECKED";
    $html .= " $element_extra_attributes>";
    break;

  case "select":
    $html=create_select_from_array($possible_values, $element_name, $element_value, $element_extra_attributes, $show_blank_first, $use_keys_for_option_values);
    break;

  case "radio":
    $contenders = $possible_values;
    foreach ($contenders as $pkey=>$possible_value) {
      $html .= "<label><input type=radio name=".$element_name;
      $html .= " VALUE=\"$pkey\"";
      if ($pkey == $element_value) {
        $html .= " CHECKED";
      }
      $html .= " $element_extra_attributes>".$possible_value."</label>&nbsp;";
    }
    break;

    $html .= "(radio) ".$element_value;
    break;

  case "textarea":
    if (!$element_length) $element_length=80;
    if (!$element_heigh) $element_height=8;
    $html .= "<textarea rows=$element_height cols=$element_length name='$element_name' $element_extra_attributes>";
    $html .= "$element_value</textarea>";
    break;

  case "text":
  default:
    if (!$element_length) $element_length=40;
    $html .= "<input type=text size=$element_length name='$element_name'";
    $html .= " value='$element_value' $element_extra_attributes>";
    break;
  }
  return $html;
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
function create_select_from_array($array, $fieldname, $selected_value=false, $extra_html_elements='', $show_blank_first=true, $use_keys_as_option_values=true) {
    if (!$array OR !$fieldname) return false;
    $html_element="<SELECT name=\"$fieldname\" $extra_html_elements>\n";
    if ($show_blank_first) $html_element.="<OPTION value=\"\">---Please select one---</OPTION>\n";
    foreach ($array as $akey=>$aval) {
        if ($use_keys_as_option_values) {
            $oval=$akey;
        } else {
            $oval=$aval;
        }
        $selected=(($oval==$selected_value) ? ' SELECTED ' : '');
        $html_element.="<OPTION value=\"$oval\"$selected>$aval</OPTION>\n";
    }
    $html_element.="</SELECT>\n";
    return $html_element;
}


/**
 *
 * Creates an expandable widget from data passed in, open to selected items
 *
 * @param array $list_data with data to render as list
 * @param string $tree_id with HTML ID for tree widget
 * @param array $selected identifying what element IDs should be expanded at load of page
 * @param bool $show_button controls display of expand/collapse button
 *
 * @return string containing html widget for list
 */
function render_tree_widget($list_data, $tree_id, $selected=false, $show_button=true, $include_mktree=true) {
    $treewidget_selected='';
    if ($selected) {
        if (!is_array($selected)) { $selected=array($selected); }
        foreach($selected as $select_value) {
          $treewidget_selected.="expandToItem('$tree_id','$select_value');\n";
        }
    }
    if ($include_mktree) $treewidget_rows.=javascript_mktree_include(false);
    $treewidget_expand=_("Expand");
    $treewidget_collapse=_("Collapse");
    $treewidget_rows.=<<<TILLEND
        <script language='javascript'>
        <!--
        function {$tree_id}_expandWidget() {
            var btControl;
            btControl=document.getElementById('bt_{$tree_id}_TreeControl');
            btControl.value='$treewidget_collapse';
            btControl.onclick={$tree_id}_collapseWidget;
            expandTree('$tree_id');
        }
        function {$tree_id}_collapseWidget() {
            var btControl;
            btControl=document.getElementById('bt_{$tree_id}_TreeControl');
            btControl.value='$treewidget_expand';
            btControl.onclick={$tree_id}_expandWidget;
            collapseTree('$tree_id');
        {$tree_id}_widgetDefaults();
        }
        function {$tree_id}_widgetDefaults() {
        $treewidget_selected
        }
        addEvent(window,"load",{$tree_id}_widgetDefaults);
        //-->
        </script>
TILLEND;
    $treewidget_rows.=render_tree_list($list_data, 'mktree', $tree_id);
    if ($show_button) {
        $treewidget_rows.="<input id=\"bt_{$tree_id}_TreeControl\" type=button class=button onclick=\"javascript:{$tree_id}_expandWidget()\" value=\"$treewidget_expand\">";
    }
   return $treewidget_rows;
}


/**
 *
 * Creates an expandable list to display contents of an array
 *
 * Uses array with array of $element
 * $element['text'] is text/html to display as list item
 * $element['class'] provies the CSS class name of the element
 * $element['id'] provies the html id of the element
 * $element['link_href'] is used as an href if provided
 * $element['link_class'] provides a CSS class name for the link
 * $element['link_extra'] is used as an href if provided
 * $element['children'] is an array of children elements, defined the same as $element
 *
 * @param array $data with elements to turn into an HTML list
 * @param string $topclass with CSS classname for top level unordered list
 * @return string with HTML of list corresponding to data, or false if no elements were found
 *
**/
function render_tree_list($data, $topclass='', $id=false) {
    if (!$data OR !is_array($data) OR (count($data)==0)) return false;
    if ($topclass) { $class="class=\"$topclass\""; } else $class='';
    if ($id) { $id="id=\"$id\""; } else $id='';
    $ret="<ul $class$id>";
    foreach ($data as $element) {
        if ($element['class']) { $liextra="class=\"{$element['class']}\""; }
        else {$liextra=''; }
        if ($element['id']) { $liextra.=' id="'.$element['id'].'"'; }
        $ret.="<li $liextra>";
        if ($element['link_href']) {
            if ($element['link_class']) {
                $link_extra="class=\"{$element['link_class']}\"";
            } else $link_extra='';
            if ($element['link_extra']) $link_extra.=' '.$element['link_extra'];
            $ret.="<a href=\"{$element['link_href']}\" $link_extra>";
        }
        $ret.=$element['text'];

        if ($element['link_href']) {
            $ret.='</a>';
        }
        if ($element['children']) $ret.=render_tree_list($element['children']);
        $ret.='</li>';
    }
    $ret.="</ul>";
    return $ret;
}


/**
 * $Log: utils-interface.php,v $
 * Revision 1.107  2006/07/12 01:02:29  vanmer
 * - added needed code to avoid notices
 *
 * Revision 1.106  2006/07/07 01:45:02  vanmer
 * - updated variable to allow proper check for browser for CSS links
 *
 * Revision 1.105  2006/07/05 13:13:59  braverock
 * - add tests once per session for existence of $cssdir and $xrms_file_root as real directories
 *   - tests suggested by user queuetue (Scott in Toronto) after he had missing theme list
 *
 * Revision 1.104  2006/05/06 09:31:21  vanmer
 * - added case for message about not changing the status of the an entity automatically
 *
 * Revision 1.103  2006/04/27 11:26:46  braverock
 * - add Charset encoding Header to start_page() for i18n
 *
 * Revision 1.102  2006/03/16 21:57:33  vanmer
 * - added span for header logo display
 *
 * Revision 1.101  2006/03/16 07:04:31  ongardie
 * - Fixed Reports href in Aaron's patch
 *
 * Revision 1.100  2006/03/16 06:36:01  ongardie
 * - Write back to $_SESSION['active_nav_items'] only if we haven't just copied it.
 *
 * Revision 1.99  2006/03/16 00:38:44  vanmer
 * - added case to attempt to guess css directory from current directory
 * - added output buffering to throw out errors when certain preferences fail due to database being unpopulated
 *
 * Revision 1.98  2006/03/13 07:24:10  vanmer
 * - fixed stranged character in last commit
 *
 * Revision 1.97  2006/03/13 07:20:47  vanmer
 * - altered navigational bar setup functionality to allow caching of available items
 * - added function to define and allow plugins to define navigational items in an array
 * - removed previous hardcoded navigational elements
 *
 * Revision 1.96  2006/03/03 02:23:57  vanmer
 * - added parameter to control if array select is rendered with the key or the value
 *
 * Revision 1.95  2005/12/02 01:13:54  vanmer
 * - added more PHPDoc comments
 * - added XRMS_API package tag
 *
 * Revision 1.94  2005/11/30 00:44:50  vanmer
 * - added read_only option for rendering form elements
 *
 * Revision 1.93  2005/10/06 04:30:06  vanmer
 * - updated log entries to reflect addition of code by Diego Ongaro at ETSZONE
 *
 * Revision 1.92  2005/10/04 23:21:44  vanmer
 * Patch to allow sort_order on the company CRM status field, thanks to Diego Ongaro at ETSZONE
 *
 * Revision 1.91  2005/08/28 17:45:17  daturaarutad
 * put css class in quotes in render_tree_widget
 *
 * Revision 1.90  2005/08/26 22:47:15  braverock
 * - add more ACL wrapping around menu items
 * - consolidate code blocks
 *
 * Revision 1.89  2005/08/25 22:44:12  braverock
 * - patch for HTML parsing compliance on IE
 *
 * Revision 1.88  2005/08/05 19:55:52  vanmer
 * - added parameter to control if widget should include mktree javascript and stylesheet links
 *
 * Revision 1.87  2005/08/05 18:57:54  vanmer
 * - added function to render a tree list widget from data and selected items
 *
 * Revision 1.86  2005/07/31 17:40:57  braverock
 * - add truncate option to get_user_menu function for use in forms
 *   where we don't need to restrict the width of the drop-down
 *
 * Revision 1.85  2005/07/26 23:30:14  vanmer
 * - added function to list activity types
 * - added extra parameters to user list to change fieldname
 *
 * Revision 1.84  2005/07/26 01:02:16  vanmer
 * - altered to allow definitions of id for the element, as well as CSS classes for both the element and the link
 * - altered to allow extra parameters to be passed along with the link and link class
 *
 * Revision 1.83  2005/07/25 20:55:34  vanmer
 * - added mktree to default included css for XRMS
 * - added function to include mktree javascript file only once
 * - added html id parameter to render_tree_view function, to allow id to be set on initial UL
 *
 * Revision 1.82  2005/07/25 20:02:22  vanmer
 * - added function for rendering an array into an unordered list, with recursion
 *
 * Revision 1.81  2005/07/22 06:51:33  vanmer
 * - added parameter controlling if start_page displays the top navigation, including everything after the head tag,
 * and the logo.css file
 * - above feature is primarily intended for mini-search, and other pages to inherit stylesheets
 *
 * Revision 1.80  2005/07/13 23:25:44  ycreddy
 * Update to the way the name is presented in a user menu - lastname, firstname instead of firstname lastname
 *
 * Revision 1.79  2005/07/13 15:59:15  vanmer
 * - cause render_button function to only check global $on_what_id if no parameters were passed in for table
 * - allows object permission to be checked instead of particular instance of object
 *
 * Revision 1.78  2005/07/12 19:09:40  vanmer
 * - removed erroneous assumption that $con will always be connected to an XRMS db
 *
 * Revision 1.77  2005/07/12 17:36:12  vanmer
 * - added message when user has no permission to an object in the system
 *
 * Revision 1.76  2005/07/12 16:47:46  vanmer
 * - changed to use econ connection instead of $con in end_page
 *
 * Revision 1.75  2005/07/12 15:00:19  braverock
 * - change end_page connection close
 *
 * Revision 1.74  2005/07/12 14:53:53  braverock
 * - change connection var to not collide with plugin connections
 *
 * Revision 1.73  2005/07/12 14:44:36  braverock
 * - clean up end page options around loading/displaying SF link
 *
 * Revision 1.72  2005/07/08 20:33:20  vanmer
 * - changed to not pass false user_id to get_user_preferences, allows non-logged-in system to retrieve system
 * preferences
 *
 * Revision 1.71  2005/07/08 18:48:48  vanmer
 * - added use of preferences to add and disable sourceforge logo at the bottom of every page
 *
 * Revision 1.70  2005/07/07 20:16:32  braverock
 * - trim width of user menu for better screen formatting
 *
 * Revision 1.69  2005/07/07 17:37:31  braverock
 * - move jscalendar_includes to inside start_page
 * - use function to get database connection
 *
 * Revision 1.68  2005/07/06 17:06:39  vanmer
 * - added check for admin preference on CSS style if user_id is not set (login page)
 *
 * Revision 1.67  2005/06/30 23:44:35  vanmer
 * - added parameter to optionally return output instead of echoing it directly
 *
 * Revision 1.66  2005/06/30 05:02:46  vanmer
 * - added handling for export permission as render button ACL function
 *
 * Revision 1.65  2005/06/28 13:58:30  braverock
 * - change line formatting of SF logo in advance of adding system parameters to control
 *
 * Revision 1.64  2005/06/24 13:48:40  braverock
 * - add SF logo link to XRMS project page to end_page fn
 *
 * Revision 1.63  2005/05/25 05:42:37  alanbach
 * Automatic RTL/LTR patch
 *
 * Revision 1.62  2005/05/10 21:37:20  braverock
 * - improve IE stylesheet checks
 *
 * Revision 1.61  2005/05/07 17:02:35  vanmer
 * - added check for session existing before attempting to retrieve xrms themes from session
 *
 * Revision 1.60  2005/05/06 15:23:11  braverock
 * - change order logo.css stylesheet is loaded in so that later shhets can
 *   override to change the logo
 *
 * Revision 1.59  2005/05/06 00:45:47  vanmer
 * - changed handling of css theme to automatically read list of themes from css directory
 *
 * Revision 1.58  2005/04/28 15:58:46  braverock
 * - applied patch to use language direction (rtl or ltr) supplied by
 *   XRMS Farsi translator Alan Baghumian (alanbach)
 *   allows use with rtl language like Farsi, Arabic, traditional Chinese
 *
 * Revision 1.57  2005/04/27 01:08:15  vanmer
 * - added function for rendering an html form element based on parameters
 *
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