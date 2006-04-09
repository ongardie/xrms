<?php
/**
 * The user's personal home page.
 *
 * @todo make the user's home page configurable,
 *       to create a 'personal dashboard'
 *
 *
 * $Id: home.php,v 1.72 2006/04/09 14:22:24 braverock Exp $
 */

// include the common files
require_once('../include-locations.inc');

//BASIC CHECKS FOR CONFIGURATION
if ($include_directory == "/full/path/to/xrms/include/") {
    $path=realpath('..');
    $msg=_("Please read the README file and configure your include-locations.inc file, in directory") ." $path";
//    $redirect="../login.php?msg=$msg";
//    Header("Location: $redirect");
    echo $msg;
    exit;
}


require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');
require_once($include_directory . 'classes/Pager/Pager_Columns.php');
require_once($include_directory . 'classes/Pager/GUP_Pager.php');
require_once('../activities/activities-widget.php');
require_once('../activities/activities-pager-functions.php');

//connect to the database
$con = @get_xrms_dbconnection();
if (!$con->_connectionID) {
    $msg=_("You must configure your database connection in vars.php before using XRMS.").'<br>'.$con->_errorMsg;
    $redirect="../login.php?msg=$msg";
    Header("Location: $redirect");
    exit;
}
//$con->debug=1;

$sql = "SELECT user_id FROM users";
$rst=$con->SelectLimit($sql,1);
if (!$rst) {
    $msg=_("Installation of database tables must be completed before using XRMS.  Please read the README and then run install/install.php.");
    $redirect="../login.php?msg=$msg";
    Header("Location: $redirect");
    exit;
}


//see if we are logged in
$session_user_id = session_check();

if (!$_SESSION['active_nav_items']) {
    $_SESSION['active_nav_items']=get_active_nav_items();
}
$active_nav_items=$_SESSION['active_nav_items'];
//check if there is only one more item than home and prefences, if so unset home and redirect to that item
if ((!array_key_exists('home',$active_nav_items))) {
    $redirect_url=false;
    foreach ($active_nav_items as $key=>$item) {
        if ($key!='preferences') {
            $redirect_url=$http_site_root.$item['href'];
	    break;
        }
    }
    if ($redirect_url) {
        $_SESSION['active_nav_items']=$active_nav_items;
        Header("Location: $redirect_url");
        exit;
    }
}

// get call arguments
$msg = isset($_POST['msg']) ? $_POST['msg'] : '';
if (!$msg) {$msg = isset($_GET['msg']) ? $_GET['msg'] : ''; };
$results_view_type = isset($_POST['results_view_type']) ? $_POST['results_view_type'] : 'list';
getGlobalVar($calendar_start_date, 'calendar_start_date');

global $return_url;
$return_url = '/private/home.php';


//if phone browser detected redirect to phone plugin
if (stristr($_SERVER['HTTP_USER_AGENT'], "MMP")) {
    header("Location: ../plugins/phone");
    exit;
}

/*********************************/
/*** Include the sidebar boxes ***/
//include the Cases sidebar
$case_sidebar_default_columns= array('case_name', 'company', 'priority','due');
$case_limit_sql = "and cases.user_id = $session_user_id";
require_once("../cases/sidebar.php");

//include the opportunities sidebar
$opportunity_limit_sql = "and opportunities.user_id = $session_user_id \nand status_open_indicator = 'o'";
require_once("../opportunities/sidebar.php");

//include the files sidebar
$on_what_table = ''; // cause all file records to be selected by ../files/sidebar.php (Why are 'files' displayed here anyway???)
require_once("../files/sidebar.php");

//include the notes sidebar
$on_what_table = 'users'; // only show personal notes
// override notes sidebar to return new notes to this page
$notes_return_url = '/private/home.php';
$notes_on_what_id = $session_user_id;
require_once("../notes/sidebar.php");

//call the sidebar hook
if ( !isset($sidebar_rows) ) {
  $sidebar_rows = '';
}
$sidebar_rows = do_hook_function('private_sidebar_bottom', $sidebar_rows);

/** End of the sidebar includes **/
/*********************************/

//call the FRONT SPLASH hook
if ( !isset($front_splash) ) {
  $front_splash = '';
}
$front_splash = do_hook_function('private_front_splash', $front_splash);

/** End of the sidebar includes **/
/*********************************/

//uncomment the debug line to see what's going on with the query
//$con->debug = 1;

$user_contact_id= $_SESSION['user_contact_id'];

if ($user_contact_id) {
    $extra_where = "AND ((a.user_id = $session_user_id) OR (activity_participants.contact_id=$user_contact_id))";
} else {
    $extra_where = "AND (a.user_id = $session_user_id) ";
}
$form_name = 'ActivitiesView';

$search_terms = array('activity_status'                 => "'o'");

$default_columns = array('title', 'type', 'contact', 'activity_about', 'scheduled', 'due');

// Make sure $private_body_rows is always defined.
$private_body_rows = '';
// add hook for body of home page under activity widget
$private_body_rows = do_hook_function('private_body_bottom', $private_body_rows);

$activities_widget =  GetActivitiesWidget($con, $search_terms, $form_name, _('Search Results'), $session_user_id, $return_url, $extra_where, null, $default_columns, true, array('due' => 'asc'));

//close the database connection, as we are done with it.
$con->close();

if (!strlen($files_rows) > 0) {
    // Make sure $file_rows is always defined.
    $files_rows = "<tr><td class=widget_content colspan=7>" . _("No open files") . "</td></tr>";
}

if (!strlen($activity_rows) > 0) {
   // Make sure $activity_rows is always defined.
    $activity_rows = "<tr><td class=widget_content colspan=7>" . _("No open activities") . "</td></tr>";
}

$page_title = _("Home");
start_page($page_title,true,$msg);

?>

<div id="Main">
    <div id="Content">
        <? echo $front_splash;?>
        <!-- Display Type -->
        <form action="home.php" method="POST" name="<?php echo $form_name; ?>">
        <!-- List or Calendar View -->
        <?php
            echo $activities_widget['content'];
            //echo $activities_widget['sidebar'];
            echo $activities_widget['js'];
        ?>
        </form>

        <!-- Body Hook Return (e.g.) Non-Uploaded Files //-->
        <?php  echo $private_body_rows; ?>

    </div>

        <!-- right column //-->
    <div id="Sidebar">
        <table class=widget cellspacing=1 width="100%">
            <tr>
                <td class=widget_header><?php echo _("Documentation"); ?></td>
            </tr>
            <tr>
                <td><a href="../doc/users/XRMS_User_Manual.pdf" target="_blank"><?php echo _("User Manual"); ?></a> (PDF)</td>
            </tr>
<?php $param=NULL; echo do_hook_function('home_docs', $param); ?>
        </table>

            <!-- opportunities //-->
            <?php  echo $opportunity_rows; ?>

            <!-- cases //-->
            <?php  echo $case_rows; ?>

            <!-- files //-->
            <?php  echo $file_rows; ?>

            <!-- notes //-->
            <?php  echo $note_rows; ?>

        <!-- sidebar plugins //-->
        <?php echo $sidebar_rows; ?>

    </div>
</div>

<?php

end_page();

/**
 * $Log: home.php,v $
 * Revision 1.72  2006/04/09 14:22:24  braverock
 * - make User Manual pdf link open a new window
 *
 * Revision 1.71  2006/03/16 06:47:33  ongardie
 * - When there is no Home menu item, it should redirect to the next one - not the last.
 * - Redirecting to Activities rather than Reports/Admin (assuming defaults) makes more sense.
 *
 * Revision 1.70  2006/03/16 00:37:39  vanmer
 * - added a few checks for broken pieces before continuing to render home page
 * - redirects to login with error messages for better error handling
 *
 * Revision 1.69  2006/03/13 07:23:30  vanmer
 * - changed to redirect to another page if home is not in the list of available navigational items
 *
 * Revision 1.68  2006/01/02 23:31:30  vanmer
 * - changed to use centralized dbconnection function
 *
 * Revision 1.67  2005/12/26 21:45:37  braverock
 * - add private_body_bottom hook to support non_uploaded_files functionality moving to plugin
 *   contributed by Florent Jekot <fjekot at fontaine-consultants dot fr>
 *
 * Revision 1.66  2005/12/06 19:07:50  daturaarutad
 * removed non_uploaded_file code
 *
 * Revision 1.65  2005/09/29 20:53:37  niclowe
 * added teamnotice plugin
 *
 * Revision 1.64  2005/09/16 21:42:04  ycreddy
 * Adding user_contact_id to the where clause only if it non 0 - significantly increases the performance of the Query for large activity data set
 *
 * Revision 1.63  2005/09/09 22:38:51  daturaarutad
 * set default sort due=>asc in pager/widget
 *
 * Revision 1.62  2005/07/28 15:50:32  vanmer
 * - changed to show the company in the cases sidebar by default
 *
 * Revision 1.61  2005/06/29 20:55:57  daturaarutad
 * add default column "due" to activities widget
 *
 * Revision 1.60  2005/06/29 17:20:25  daturaarutad
 * fixed default columns list for activities pager
 *
 * Revision 1.59  2005/06/28 20:10:59  daturaarutad
 * set default columns in pager widget, updated for new GetActivitiesWidget param list
 *
 * Revision 1.58  2005/06/27 16:34:47  daturaarutad
 * updated to use GetActivitiesWidget() for pager and calendar
 *
 * Revision 1.57  2005/06/20 07:08:44  alanbach
 * Some translation & gettext corrections.
 *
 * Revision 1.55  2005/05/31 20:51:35  ycreddy
 * Updated the activity GROUP BY clause to make it compatible with MS SQL Server
 *
 * Revision 1.54  2005/05/31 15:22:12  ycreddy
 * Adding the missing alias for company
 *
 * Revision 1.53  2005/05/27 22:33:03  ycreddy
 * Fixed the LEFT OUTER to be compatible with SQL Server
 *
 * Revision 1.52  2005/05/25 17:30:24  vanmer
 * - added all activities for which user is a participant to activity list on home.php
 *
 * Revision 1.51  2005/05/25 15:38:10  daturaarutad
 * added activity_id to query for calendar to use
 *
 * Revision 1.50  2005/05/25 15:09:12  braverock
 * - fix msg support to not stomp $_GET
 *
 * Revision 1.49  2005/05/20 23:13:43  daturaarutad
 * updated to use GUP_Pager for activities; small change for calendar which is not quite perfect but better at starting on a reasonable date
 *
 * Revision 1.48  2005/05/18 16:18:02  daturaarutad
 * updated Calendar constructor and include location
 *
 * Revision 1.47  2005/05/05 17:37:05  daturaarutad
 * updated for changes to CalendarView interface
 *
 * Revision 1.46  2005/04/14 21:20:17  daturaarutad
 * added calendar view of activities
 *
 * Revision 1.45  2005/04/07 18:16:20  vanmer
 * - changed second parameter to do_hook_function to pass variable instead of passing reference (reference is now in function definition)
 *
 * Revision 1.44  2005/01/17 13:39:50  braverock
 * - add db_error_handler to all queries
 *
 * Revision 1.43  2005/01/11 23:21:10  vanmer
 * - changed home.php to automatically populate the browse activities, so that save and next will operate off the user's activities
 *
 * Revision 1.42  2005/01/03 03:23:41  ebullient
 * additional theme (green), make User Manual link not a "header"
 *
 * Revision 1.41  2004/10/01 20:09:39  introspectshun
 * - Now only shows personal notes (should be user-configurable someplace?)
 * - Now uses notes sidebar form to create personal notes rather than "New Personal Note" form
 *
 * Revision 1.40  2004/08/23 01:47:50  d2uhlman
 * check for MMP in user header and if so redirect to phone plugin
 *
 * Revision 1.39  2004/08/05 15:45:38  braverock
 * - add a commented out debug line and a few more checks to make sure we have a result set
 *
 * Revision 1.38  2004/07/30 11:12:58  cpsource
 * - Got msg in standard format.
 *
 * Revision 1.37  2004/07/25 14:13:54  johnfawcett
 * - removed PDF from translated text
 *
 * Revision 1.36  2004/07/22 16:38:46  gpowers
 * - added 'home_docs' plugin hook
 *   - allows plugin documentation to appear on home page.
 *
 * Revision 1.35  2004/07/20 19:59:04  introspectshun
 * - Localized button value for i18n/l10n support
 *
 * Revision 1.34  2004/07/20 11:40:53  cpsource
 * - Added support for $msg
 *
 * Revision 1.33  2004/07/16 07:27:39  introspectshun
 * - Localized strings for i18n/translation support
 *
 * Revision 1.32  2004/07/15 17:44:05  cpsource
 * - Resolve undef sidebar_rows
 *
 * Revision 1.31  2004/07/14 20:19:51  cpsource
 * - Resolved $company_count not being set properly
 *   opportunities/some.php tried to set $this which can't be done in PHP V5
 *
 * Revision 1.30  2004/07/14 19:02:04  gpowers
 * - added 'private_sidebar_bottom' plugin hook
 *   - for info plugin
 *
 * Revision 1.29  2004/07/14 14:41:00  cpsource
 * - Defined $files_rows so it wouldn't be used undefined.
 *   selected a.activity_description so it would be pulled from db
 *   Defined $activity_rows so it wouldn't be used undefined.
 *
 * Revision 1.28  2004/07/14 14:30:31  cpsource
 * - Make sure $nu_file_rows is always defined as something (now $private_body_rows)
 *
 * Revision 1.27  2004/07/10 13:10:49  braverock
 * - applied undefined variables patch
 *   - applies SF patch  submitted by cpsource
 *
 * Revision 1.26  2004/07/10 12:00:48  braverock
 * - changed xrms_user_manual.pdf to XRMS_User_Manual.pdf
 *   - resolves SF bug 987496 reported by kennyg1
 *
 * Revision 1.25  2004/07/09 18:47:27  introspectshun
 * - Removed CAST(x AS CHAR) for wider database compatibility
 * - The modified MSSQL driver overrides the default Concat function to cast all datatypes as strings
 *
 * Revision 1.24  2004/06/25 03:21:17  braverock
 * - modify so non-uploaded files only display when there are records (again)
 *
 * Revision 1.23  2004/06/21 20:48:47  introspectshun
 * - Now use CAST AS CHAR to convert integers to strings in Concat function calls.
 *
 * Revision 1.22  2004/06/21 16:02:18  braverock
 * - fixed timestamp to be in proper database compliant mode
 *
 * Revision 1.21  2004/06/16 20:38:20  gpowers
 * - removed $this from session_check()
 *   - it is incompatible with PHP5
 *
 * Revision 1.20  2004/06/15 14:18:04  gpowers
 * - corrected time formats
 *
 * Revision 1.19  2004/06/12 16:46:55  braverock
 * - remove CAST on CONCAT
 *    - databases should implicitly convert numeric to string and
 *    - VARCHAR is not universally supported
 * - changed is_overdue to work with ends_at
 *
 * Revision 1.18  2004/06/12 16:19:20  braverock
 * - convert timestamp to work wuith adodb changes
 *
 * Revision 1.17  2004/06/12 07:01:10  introspectshun
 * - Now use ADODB Concat function.
 *
 * Revision 1.16  2004/05/27 20:45:36  gpowers
 * Added "Documentation" Sidebar box with link to User Manual (PDF)
 *
 * Revision 1.15  2004/05/27 18:10:47  gpowers
 * Applied Patch [ 957550 ] Scheduled activities with different colour
 * submitted by miguel Gonçves - mig77
 *
 * Revision 1.14  2004/04/19 03:43:34  braverock
 *  - Add Personal Notes
 *    - apply SF patch 934480 submitted by Glenn Powers
 *
 * Revision 1.13  2004/04/07 13:50:54  maulani
 * - Set CSS2 positioning for the home page
 *
 * Revision 1.12  2004/03/24 15:02:11  braverock
 * - add non-uploaded files dsplay on home page
 * - only display if the user has 'non-uploaded' files
 * - modified from code provided by Olivier Colonna of Fontaine Consulting
 *
 * Revision 1.11  2004/03/15 16:41:21  braverock
 * - show only open opportunities on the home page
 *
 * Revision 1.10  2004/03/07 17:47:10  braverock
 * -changed to use $display_how_many_activities_on_home_page
 *
 * Revision 1.9  2004/03/07 14:09:14  braverock
 * - use centralized side-bar code in advance of i18n conversion
 *
 */
?>
