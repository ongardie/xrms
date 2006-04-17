<?php
/**
 * /activities/some.php
 *
 * Search for and View a list of activities
 *
 * $Id: some.php,v 1.127 2006/04/17 17:56:40 jnhayart Exp $
 */

// handle includes
require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'utils-saved-search.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');
require_once($include_directory . 'classes/Pager/GUP_Pager.php');
require_once($include_directory . 'classes/Pager/Pager_Columns.php');
require_once ($include_directory . 'classes/Pager/Session_Var_Watcher.php');
require_once('../activities/activities-widget.php');
require_once('../activities/activities-pager-functions.php');


// create session
$on_what_table='activities';
$session_user_id = session_check();

// Start connection
$con = get_xrms_dbconnection();

getGlobalVar($browse,'browse');
getGlobalVar($saved_id, 'saved_id');
getGlobalVar($saved_title, 'saved_title');
getGlobalVar($group_item, 'group_item');
getGlobalVar($delete_saved, 'delete_saved');
    

/*********** SAVED SEARCH BEGIN **********************/
load_saved_search_vars($con, $on_what_table, $saved_id, $delete_saved);

/*********** SAVED SEARCH END **********************/

$advanced_search = (!empty($_REQUEST['advanced_search'])) ? true : false;

// declare passed in variables
$arr_vars = array ( // local var name       // session variable name
                   'title'               => array ( 'activities_title', arr_vars_SESSION ) ,
                   'template_title'      => array ( 'activities_template_title', arr_vars_SESSION ) ,
                   'contact'             => array ( 'activities_contact', arr_vars_SESSION ) ,
                   'contact_id'          => array ( 'activities_contact_id', arr_vars_SESSION ) ,
                   'company'             => array ( 'activities_company', arr_vars_SESSION ) ,
                   'company_id'          => array ( 'activities_company_id', arr_vars_SESSION ) ,
                   'campaign_id'          => array ( 'activities_campaign_id', arr_vars_SESSION ) ,
           // 'owner'               => array ( 'activities_owner', arr_vars_SESSION ) ,
                   'before_after'        => array ( 'activities_before_after', arr_vars_SESSION ) ,
                   'start_end'           => array ( 'activities_start_end', arr_vars_SESSION ) ,
                   'activity_type_id'    => array ( 'activity_type_id', arr_vars_SESSION ) ,
                   'completed'           => array ( 'activities_completed', arr_vars_SESSION ) ,
                   'user_id'             => array ( 'activities_user_id', arr_vars_SESSION ) ,
                   // 'date'                => array ( 'date', arr_vars_SESSION ) ,
                   'search_date'         => array ( 'activities_date', arr_vars_SESSION ) ,
                   'time_zone_between'   => array ( 'time_zone_between', arr_vars_SESSION ) ,
                   'time_zone_between2'  => array ( 'time_zone_between2', arr_vars_SESSION ) ,
                   'opportunity_status_id' => array ( 'opportunity_status_id', arr_vars_SESSION ) ,
                   );

// get all passed in variables
arr_vars_get_all ( $arr_vars );

/*

// (introspectshun) Updated to use portable database code; removed MySQL-centric date functions
// This will work for positive and negative intervals automatically, so no need for conditional assignment of offset
// (a search for today will add an interval of '0 days')
// Warning: if a user wants to save a search for a particular date, this won't allow it, as it defaults to recurring search
if(isset($day_diff) and $day_diff) {
    $search_date = date('Y-m-d', time() + ($day_diff * 86400));
} else {
    if ( !$search_date ) {
        $search_date = date('Y-m-d', time());
    }
    $day_diff = round((strtotime($search_date) - strtotime(date('Y-m-d', time()))) / 86400);
}



//getGlobalVar($calendar_start_date, 'calendar_start_date');

//echo "search date is $search_date<br>";
//echo "calendar start date is $calendar_start_date<br>";
if($start_end == 'start') {
    $field = 'scheduled_at';
} else {
    $field = 'ends_at';
}

$offset_sql = '';

// first set up $offset_sql for before/after search_date
if (strlen($search_date) > 0 && $start_end != 'all') {
    $criteria_count++;

    if (!$before_after) {
        // before
        $offset_end = $con->OffsetDate($day_diff);
        $offset_sql .= " and a.$field < $offset_end";
    } elseif ($before_after === 'after') {
        // after
        $offset_start = $con->OffsetDate($day_diff);
        $offset_sql .= " and a.$field > $offset_start";
    } elseif ($before_after === 'on') {
        // same query for list and calendar views
        $offset_start = $con->OffsetDate($day_diff);
        $offset_end = $con->OffsetDate($day_diff+1);
        // midnight to midnight
        $offset_sql .= " and a.$field > $offset_start and a.$field < $offset_end";
    }
}
*/


if (!strlen($completed) > 0) {
    $completed ='o';
}

//If advanced search template title is on, it should override normal title
$title = ($template_title) ? $template_title : $title;

// set all session variables
arr_vars_session_set ( $arr_vars );

//uncomment this to see what's going on with the database
//$con->debug=1;

/*********************************
//*** Include the sidebar boxes ***/

/** End of the sidebar includes **
//*********************************/

if(strlen($time_zone_between) and strlen($time_zone_between2)) {
    update_daylight_savings($con);
}


if($advanced_search) {
    //get activities from templates
    $sql2 = "SELECT activity_title, activity_title as activity_title2
            FROM activity_templates
            WHERE activity_template_record_status='a'
            AND on_what_table = 'opportunity_statuses'
            GROUP BY activity_title
            ORDER BY activity_title";
    $rst = $con->execute($sql2);
    if (!$rst) {
        db_error_handler($con, $sql2);
    } elseif ($rst->rowcount()) {
        $activity_menu = $rst->getmenu2('template_title', $template_title, true);
        $rst->close();
    }
}

if($advanced_search) {
    //get campaign titles
    $sql2 = "SELECT campaign_title, campaign_id
             FROM campaigns
             WHERE campaign_record_status = 'a'";
    $rst = $con->execute($sql2);
    if (!$rst) {
        db_error_handler($con, $sql2);
    } elseif ($rst->rowcount()) {
        $campaign_menu = $rst->getmenu2('campaign_id', $campaign_id, true);
        $rst->close();
    }
}

if (!isset($user_id)) {
   $user_id=$session_user_id;
}

//get menu for users
//$sql2 = "(SELECT " . $con->qstr(_("Current User"),get_magic_quotes_gpc()) . ", '-1')"
//    . " UNION (select username, user_id from users where user_record_status = 'a')"
//       . " UNION (SELECT " . $con->qstr(_("Not Set"),get_magic_quotes_gpc()) . ", '-2')  ORDER BY 1";
//$rst = $con->execute($sql2);
//if (!$rst) {
//    db_error_handler($con, $sql2);
//} elseif ($rst->rowcount()) {
//    $user_menu = $rst->getmenu2('user_id', $user_id, true);
//    $rst->close();
//}

$user_menu = get_user_menu($con, $user_id, true);

if($advanced_search) {
    //get menu for opportunity_statuses
    $sql2 = "select opportunity_status_pretty_name, opportunity_status_id from opportunity_statuses where opportunity_status_record_status='a'";
    $rst = $con->execute($sql2);
    if (!$rst) {
        db_error_handler($con, $sql2);
    } elseif ($rst->rowcount()) {
        $opportunity_menu = $rst->getmenu2('opportunity_status_id', $opportunity_status_id, true);
        $rst->close();
    }
}

//get activity type menu
$sql_type = "SELECT activity_type_pretty_name, activity_type_id
             FROM activity_types
             WHERE activity_type_record_status = 'a'
             ORDER BY sort_order, activity_type_pretty_name";
$rst = $con->execute($sql_type);
$type_menu = translate_menu($rst->getmenu2('activity_type_id', $activity_type_id, true));
$rst->close();



/******* SAVED SEARCH BEGINS *****/
    $saved_data = $_POST;
    $saved_data["sql"] = $sql;
    $saved_data["day_diff"] = $day_diff;
    
    if(!$saved_title) {
        $saved_title = "Current";
        $group_item = 0;
    }
    if ($saved_title OR $browse) {
//        echo "adding saved search";
        $saved_id=add_saved_search_item($con, $saved_title, $group_item, $on_what_table, $saved_data);
//        echo "$saved_id=add_saved_search_item($con, $saved_title, $group_item, $on_what_table, $saved_data);";
    }    

//get saved searches
$rst=get_saved_search_item($con, $on_what_table, $session_user_id, false,  false, true,'search', true);
if( $rst AND $rst->RowCount() ) {
    $saved_menu = $rst->getmenu2('saved_id', 0, true) . ' <input name="delete_saved" type=submit class=button value="' . _("Delete") . '">';
} else {
  $saved_menu = '';
}
/********** SAVED SEARCH ENDS ****/

add_audit_item($con, $session_user_id, 'searched', 'activities', '', 4);

//debug
//echo htmlentities($sql).'<br>';

$page_title = _("Open Activities");
start_page($page_title, true, $msg);

?>

<?php jscalendar_includes(); ?>

<div id="Main">
    <div id="ContentFullWidth">

    <form action=some.php class="print" method=post name="ActivitiesData">
        <input type=hidden name=advanced_search value="<?php echo $advanced_search; ?>">
        <input type=hidden name=use_post_vars value=1>

        <table class=widget cellspacing=1 width="100%">
            <tr>
                <td class=widget_header colspan=4><?php echo _("Search Criteria"); ?></td>
            </tr>
            <tr>
                <td colspan="2" class=widget_label><?php echo _("Summary"); ?></td>
                <td class=widget_label><?php echo _("Contact"); ?></td>
                <td class=widget_label><?php echo _("Company"); ?></td>
            </tr>
            <tr>
                <td colspan="2" class=widget_content_form_element> <input type=text name="title" size=48 value="<?php  echo $title; ?>">
                </td>
                <td class=widget_content_form_element><input type=text name="contact" size=12 value="<?php  echo $contact; ?>">
                </td>
                <td class=widget_content_form_element><input type=text name="company" size=15 value="<?php  echo $company; ?>">
                </td>
            </tr>
<?php if($advanced_search) { ?>
            <tr>
                <td colspan="2" class=widget_label><?php echo _("Template Titles"); ?></td>
                <td class=widget_label><?php echo _("Contact ID"); ?></td>
                <td class=widget_label><?php echo _("Company ID"); ?></td>
            </tr>
            <tr>
                <td colspan="2" class=widget_content_form_element><?php echo $activity_menu; ?>
                </td>
                <td class=widget_content_form_element><input type=text name="contact_id" size=12 value="<?php  echo $contact_id; ?>">
                </td>
                <td class=widget_content_form_element><input type=text name="company_id" size=15 value="<?php  echo $company_id; ?>">
                </td>
            </tr>
            <tr>
                <td colspan="4" class=widget_label><?php echo _("Campaigns"); ?></td>
            </tr>
            <tr>
                <td colspan="4" class="widget_content_form_element"><?php echo $campaign_menu; ?></td>
            </tr>
<?php } //end if advanced search ?>
            <tr>
                <td class=widget_label><?php echo _("Owner"); ?></td>
                <td class=widget_label><?php echo _("Search By Date"); ?></td>
                <td class=widget_label><?php echo _("Type"); ?></td>
                <td class=widget_label><?php echo _("Completed"); ?></td>
            </tr>
            <tr>
                <td class=widget_content_form_element>
                    <?php  echo $user_menu; ?>
                </td>
                <td class=widget_content_form_element>
                    <select name="start_end">
                        <option value="end"<?php if($start_end == 'end') { print " selected"; }?>><?php echo _("Scheduled End"); ?></option>
                        <option value="start"<?php if($start_end == 'start') { print " selected"; }?>><?php echo _("Scheduled Start"); ?></option>
                        <option value="all"<?php if($start_end == 'all') { print " selected"; }?>><?php echo _("All Dates"); ?></option>
                    </select>
                    <select name="before_after">
                        <option value=""<?php if (!$before_after) { print " selected"; } ?>><?php echo _("Before"); ?></option>
                        <option value="after"<?php if ($before_after == "after") { print " selected"; } ?>><?php echo _("After"); ?></option>
                        <option value="on"<?php if ($before_after == "on") { print " selected"; } ?>><?php echo _("On"); ?></option>
                    </select>
                    <input type=text ID="f_date_d" name="search_date" size=12 value="<?php  echo $search_date; ?>">
                    <img ID="f_trigger_d" style="CURSOR: hand" border=0 src="../img/cal.gif" alt="">
                </td>
                <td class=widget_content_form_element>
                    <?php  echo $type_menu; ?>
                </td>
                <td class=widget_content_form_element>
                    <select name="completed">
                        <option value="all"<?php if ($completed == "all") { print " selected"; } ?>><?php echo _("All"); ?></option>
                        <option value="o"<?php if ($completed == "o" or !$completed) { print " selected"; } ?>><?php echo _("Non-Completed"); ?></option>
                        <option value="c"<?php if ($completed == "c") { print " selected"; } ?>><?php echo _("Completed"); ?></option>
                    </select>
                </td>
            </tr>
<?php if($advanced_search) { ?>
            <tr>
                <td class=widget_label colspan="2"><?php echo _("Local Time Between"); ?></td>
                <td class=widget_label colspan="2"><?php echo _("Opportunity Status"); ?></td>
            </tr>
            <tr>
                <!-- Time Zone Handling Selects -->
                <td class=widget_content_form_element colspan="2">
                     <select name="time_zone_between" >
                        <option></option>
                        <option value='0'>00:00:00</option>
                        <option value='1'>01:00:00</option>
                        <option value='2'>02:00:00</option>
                        <option value='3'>03:00:00</option>
                        <option value='4'>04:00:00</option>
                        <option value='5'>05:00:00</option>
                        <option value='6'>06:00:00</option>
                        <option value='7'>07:00:00</option>
                        <option value='8'>08:00:00</option>
                        <option value='9'>09:00:00</option>
                        <option value='10'>10:00:00</option>
                        <option value='11'>11:00:00</option>
                        <option value='12'>12:00:00</option>
                        <option value='13'>13:00:00</option>
                        <option value='14'>14:00:00</option>
                        <option value='15'>15:00:00</option>
                        <option value='16'>16:00:00</option>
                        <option value='17'>17:00:00</option>
                        <option value='18'>18:00:00</option>
                        <option value='19'>19:00:00</option>
                        <option value='20'>20:00:00</option>
                        <option value='21'>21:00:00</option>
                        <option value='22'>22:00:00</option>
                        <option value='23'>23:00:00</option>
                        <option value='24'>00:00:00</option>
                     </select>
                     <?php echo "\n"._("and")."\n"; ?>
                     <select name="time_zone_between2" >
                        <option></option>
                        <option value='0'>00:00:00</option>
                        <option value='1'>01:00:00</option>
                        <option value='2'>02:00:00</option>
                        <option value='3'>03:00:00</option>
                        <option value='4'>04:00:00</option>
                        <option value='5'>05:00:00</option>
                        <option value='6'>06:00:00</option>
                        <option value='7'>07:00:00</option>
                        <option value='8'>08:00:00</option>
                        <option value='9'>09:00:00</option>
                        <option value='10'>10:00:00</option>
                        <option value='11'>11:00:00</option>
                        <option value='12'>12:00:00</option>
                        <option value='13'>13:00:00</option>
                        <option value='14'>14:00:00</option>
                        <option value='15'>15:00:00</option>
                        <option value='16'>16:00:00</option>
                        <option value='17'>17:00:00</option>
                        <option value='18'>18:00:00</option>
                        <option value='19'>19:00:00</option>
                        <option value='20'>20:00:00</option>
                        <option value='21'>21:00:00</option>
                        <option value='22'>22:00:00</option>
                        <option value='23'>23:00:00</option>
                        <option value='24'>00:00:00</option>
                  </select>
                </td>
                <td class=widget_content_form_element colspan="2">
                    <?php echo $opportunity_menu; ?>
                </td>
            </tr>
<?php } //end if advanced search ?>
            <tr>
                <td class=widget_label colspan="2"><?php echo _("Saved Searches"); ?></td>
                <td class=widget_label colspan="2"><?php echo _("Search Title"); ?></td>
            </tr>
            <tr>
                <td class=widget_content_form_element colspan="2">
                    <?php echo ($saved_menu) ? $saved_menu : _("No Saved Searches"); ?>
                </td>
                <td class=widget_content_form_element colspan="2">
                    <input type=text name="saved_title" size=24>
                    <?php
                        if(check_user_role(false, $_SESSION['session_user_id'], 'Administrator')) {
                            echo _("Add to Everyone").' <input type=checkbox name="group_item" value=1>';
                        }
                    ?>
                </td>
            </tr>
            <tr>
                <td class=widget_label colspan="4"><?php echo _("Results View"); ?></td>
            </tr>
            <tr>
                <td class=widget_content_form_element colspan=4>
                    <input name="submitted" type=submit class=button value="<?php echo _("Search"); ?>">
                    <input name="button" type=button class=button onClick="javascript: clearSearchCriteria();" value="<?php echo _("Clear Search"); ?>">
                    <?php
                        if(!$advanced_search) {
                            echo ' <input name="advanced_search" type=button class=button onclick="javascript: location.href=\'some.php?advanced_search=true\';" value="'._("Advanced Search").'">';
                        }
                    ?>
                </td>
            </tr>
    </table>

<?php
$_SESSION["search_sql"]=$sql;


	// Pass search terms to GetActivitiesWidget
	$search_terms = array(  'title' 				=> $title,
							'contact' 				=> $contact,
							'contact_id' 			=> 	$contact_id,
							'company' 				=> $company,
							'company_id'			=> $company_id,
							'user_id' 				=> $user_id,
							'activity_type_id' 		=> $activity_type_id,
							'completed' 			=> $completed,
							//'offset_sql' 			=> $offset_sql,
                            'day_diff'              => $day_diff,
                            'start_end'              => $start_end,
                            'before_after'              => $before_after,
							'search_date' 			=> $search_date,
							'time_zone_between' 	=> $time_zone_between,
							'time_zone_between2' 	=> $time_zone_between2,
							'opportunity_status_id' => $opportunity_status_id,
							'campaign_id' 			=> $campaign_id
						);
	$return_url = '/activities/some.php';

	
	$activities_widget =  GetActivitiesWidget($con, $search_terms, 'ActivitiesData', _('Search Results'), $session_user_id, $return_url, null, null, null, false);	

	echo $activities_widget['content'];
	echo $activities_widget['sidebar'];
	echo $activities_widget['js'];



$con->close();
?>
    
    </form>

    </div>
        <!-- right column //-->
    <?php
    /*
    <div id="Sidebar">

    </div>
    */
    //no sidebar here yet, so make the table expand
    ?>
</div>

<script language="JavaScript" type="text/javascript">

function initialize() {
    document.forms[0].title.focus();
}

initialize();

function bulkEmail() {
    document.forms[0].action = "../email/email.php";
    document.forms[0].submit();
}

function clearSearchCriteria() {
    location.href = "some.php?clear=1";
}

function submitForm(adodbNextPage) {
    document.forms[0].activities_next_page.value = adodbNextPage;
    document.forms[0].submit();
}

function resort(sortColumn) {
    document.forms[0].sort_column.value = sortColumn + 1;
    document.forms[0].activities_next_page.value = '';
    document.forms[0].resort.value = 1;
    document.forms[0].submit();
}

Calendar.setup({
        inputField     :    "f_date_d",      // id of the input field
        ifFormat       :    "%Y-%m-%d",       // format of the input field
        showsTime      :    false,            // will display a time selector
        button         :    "f_trigger_d",   // trigger for the calendar (button ID)
        singleClick    :    false,           // double-click mode
        step           :    1,                // show all years in drop-down boxes (instead of every other year as default)
        align          :    "Bl"           // alignment (defaults to "Bl")
    });

</script>

<?php

    echo $calendar_js_functions;

end_page();

/**
 * $Log: some.php,v $
 * Revision 1.127  2006/04/17 17:56:40  jnhayart
 * change way to populate list of user with centralized function
 * get_user_menu() from /include/utils-interface.php
 *
 * Revision 1.126  2006/01/02 21:23:18  vanmer
 * - changed to use centralized database connection function
 *
 * Revision 1.125  2005/08/05 01:22:39  vanmer
 * - changed to use centralized saved search functionality
 *
 * Revision 1.124  2005/07/16 00:17:14  daturaarutad
 * moved browse button to activities_widget.php
 *
 * Revision 1.123  2005/07/15 22:51:35  vanmer
 * - changed to use new method of calling browse-next, relies on the search-sql session variable instead of saved
 * search
 *
 * Revision 1.122  2005/07/10 20:21:30  daturaarutad
 * moved sql_offset to activities-widget.php
 *
 * Revision 1.121  2005/07/08 20:15:03  vanmer
 * - changed to use new ACL functions to check for administrator access
 *
 * Revision 1.120  2005/06/30 18:34:57  daturaarutad
 * set return_url
 *
 * Revision 1.119  2005/06/28 20:11:38  daturaarutad
 * removed results_view_type; update GetActivitiesWidget param list
 *
 * Revision 1.118  2005/06/27 16:39:25  daturaarutad
 * updated to use GetActivitiesWidget() for pager and calendar
 *
 * Revision 1.117  2005/06/08 18:21:42  daturaarutad
 * adjusted the calendar query offset for monthly view
 *
 * Revision 1.116  2005/06/08 17:46:28  daturaarutad
 * apply calendar query date limit even when start_end == all
 *
 * Revision 1.115  2005/05/31 20:50:50  ycreddy
 * Updated the activity GROUP BY clause to make it compatible with MS SQL Server
 *
 * Revision 1.114  2005/05/25 17:17:53  vanmer
 * - added join to activity participants to link and search contacts who are participants on an activity
 *
 * Revision 1.113  2005/05/20 22:18:01  daturaarutad
 * fixed broken coloring for overdue/closed activities
 *
 * Revision 1.112  2005/05/19 13:20:43  maulani
 * - Remove trailing whitespace
 *
 * Revision 1.111  2005/05/18 20:07:04  daturaarutad
 * fixed a bug regarding . vs , in $con->Offset() for non-English languages (calendar)
 *
 * Revision 1.110  2005/05/18 16:20:07  daturaarutad
 * updated calendar
 *
 * Revision 1.109  2005/05/06 22:18:24  daturaarutad
 * fixed wrong var names for var_watcher; now resetting calendar_start_date whenever search dates are changed
 *
 * Revision 1.108  2005/05/06 15:43:23  braverock
 * - changed labels for scheduled_on, ends_on, and title/summay for consistency
 *
 * Revision 1.107  2005/05/05 17:58:03  daturaarutad
 * updated to reflect changes in CalendarView interface
 * mostly fixed 'week start' problem...still an issue with week view sometimes
 * monthly view should be working fine
 * no longer show list view if in calendar view
 *
 * Revision 1.106  2005/05/04 19:26:55  braverock
 * - changed labels for scheduled_on, ends_on, and title/summay for consistency
 *
 * Revision 1.105  2005/05/02 17:11:55  daturaarutad
 * fixed a bug in the date restriction for list view
 *
 * Revision 1.104  2005/04/29 17:44:11  daturaarutad
 * made form printable
 *
 * Revision 1.103  2005/04/29 16:12:22  daturaarutad
 * updated to use GUP_Pager for export
 *
 * Revision 1.102  2005/04/14 20:46:34  daturaarutad
 * fixed query syntax error
 *
 * Revision 1.101  2005/04/14 20:30:45  daturaarutad
 * added calendar to some.php
 *
 * Revision 1.100  2005/03/15 22:34:45  daturaarutad
 * pager tuning sql_sort_column
 *
 * Revision 1.99  2005/03/07 16:26:59  daturaarutad
 * removed some vestigal sort column stuff (that moved to pager)
 * added sql_sort_columns to speed up column sorting for concat'ed columns
 * disabled the opportunity.probability for now
 *
 * Revision 1.98  2005/03/04 17:57:13  daturaarutad
 * set Due to the default_sort
 *
 * Revision 1.97  2005/03/02 15:15:44  daturaarutad
 * removed redundant mail merge button and corresponding query
 *
 * Revision 1.96  2005/03/01 21:56:45  daturaarutad
 * set the css_classname for right-align on numerics in pager
 *
 * Revision 1.95  2005/02/28 22:18:38  daturaarutad
 * changed columns to be index_sql so that the pager knows it doesnt have to get the whole data set
 *
 * Revision 1.94  2005/02/25 03:41:02  daturaarutad
 * updated to use GUP_Pager class
 *
 * Revision 1.93  2005/02/15 20:52:00  vanmer
 * - removed broken cast statement from activities user list sql
 * - added order by statement to activities user list sql
 *
 * Revision 1.92  2005/02/15 15:17:30  ycreddy
 * Changed the SQL query that fetches users to make it compatible with SQL Server
 *
 * Revision 1.91  2005/02/14 22:04:05  vanmer
 * - updated to reflect ACL speed updates
 *
 * Revision 1.90  2005/02/10 01:08:51  braverock
 * - change order of LEFT OUTER JOIN query for MS SQL server portability
 *
 * Revision 1.89  2005/02/09 22:24:03  braverock
 * - localized pager column headers
 * - de-localized AS clauses in SQL
 *
 * Revision 1.88  2005/01/26 22:37:15  vanmer
 * - altered query to allow activities table to always directly preceed LEFT OUTER JOINS, to fix sql server error
 *
 * Revision 1.87  2005/01/25 22:11:59  daturaarutad
 * updated to use new XRMS_Pager and Pager_Columns to implement selectable columns
 *
 * Revision 1.86  2005/01/22 15:07:24  braverock
 * - add sort order to activity_types menu
 *
 * Revision 1.85  2005/01/13 22:35:34  vanmer
 * - removed HOUR sql code to allow compliance with other database servers
 *
 * Revision 1.84  2005/01/13 17:54:45  vanmer
 * - ACL restriction on activity list when searching
 *
 * Revision 1.83  2005/01/11 01:06:51  braverock
 * - remove lpad database query and replace with hard-coded select statements
 * - lpad isn't portable SQL, and we didn't need two extra queries there to make a list
 *
 * Revision 1.82  2005/01/07 15:55:56  braverock
 * - convert search params for UNION to string, int types
 *   enhances SQL portability across databases that require compatible types
 * - added check for old strings to not break saved searches
 *
 * Revision 1.81  2005/01/07 14:20:54  neildogg
 * - Saved search was being incorrectly called, moved restrictions to deletion only
 *
 * Revision 1.80  2004/12/27 13:15:54  braverock
 * - add additional database error handling to several of the search parameters
 * - fix problem with CURRENT_USER on some browsers
 * - localize 'Not Set' and 'Current User' strings
 *
 * Revision 1.79  2004/12/26 21:58:18  braverock
 * - fix string quoting to resolve problems with French translation
 *
 * Revision 1.78  2004/12/26 19:41:06  braverock
 * - fix string quoting to resolve problems with French translation
 *
 * Revision 1.77  2004/12/26 15:58:19  braverock
 * - fix string quoting to resolve problems with French translation
 *
 * Revision 1.76  2004/12/26 13:31:43  braverock
 * - fix string quoting to resolve problems with French translation
 *
 * Revision 1.75  2004/12/24 15:57:49  braverock
 * - expand to use ContentFullWidth with new CSS
 *
 * Revision 1.74  2004/12/23 16:16:24  neildogg
 * - Adjusted since advanced search is always submitted as a variable
 *
 * Revision 1.73  2004/12/20 22:16:06  neildogg
 * - Saves advanced search status
 *
 * Revision 1.72  2004/12/20 15:51:37  neildogg
 * - Because you can't order by a string
 *
 * Revision 1.71  2004/12/20 14:45:55  neildogg
 * - Changed user table to a left join to allow an empty user search
 *
 * Revision 1.70  2004/12/18 21:34:27  neildogg
 * Added empty user and current user search (great for saved searches)
 *
 * Revision 1.69  2004/12/18 21:21:31  neildogg
 * Added advanced search by Campaign
 *
 * Revision 1.68  2004/12/18 20:25:47  neildogg
 * Added Search by All Dates
 *
 * Revision 1.67  2004/12/18 20:06:50  neildogg
 * Added Search by Scheduled/Due and made ON date search accurate
 *
 * Revision 1.66  2004/11/26 17:26:19  braverock
 * - quote order by clause for i18n
 *
 * Revision 1.65  2004/11/26 17:22:00  braverock
 * - quote order by clause for i18n
 *
 * Revision 1.64  2004/11/26 15:10:35  braverock
 * - add translate_menu call to activity types menu for i18n
 *
 * Revision 1.63  2004/11/12 15:30:12  braverock
 * - added closing } to resolve parse error
 *
 * Revision 1.62  2004/11/12 15:25:09  braverock
 * - fixed short php tags
 * - cleaned up some other code formatting
 *
 * Revision 1.61  2004/10/29 15:34:11  introspectshun
 * - Added option to search activities 'on' a specific date
 *
 * Revision 1.60  2004/09/21 18:21:28  introspectshun
 * - Changed table order in main query FROM clause
 * - Join fails on MSSQL otherwise
 *
 * Revision 1.59  2004/08/25 15:49:39  introspectshun
 * - Fixed errant variable name
 *
 * Revision 1.58  2004/08/25 15:01:17  neildogg
 * - Searches local time with proper constraints
 * - Saves temporary searches properly
 *
 * Revision 1.57  2004/08/19 20:37:20  neildogg
 * - Added many advanced search features
 *  - Search by template title, contact ID, company ID
 *  - Search by opportunity status
 *  - Moved local time to advanced search
 *
 * Revision 1.56  2004/08/18 06:08:14  niclowe
 * Merged 1.54 neildogg chnages into 1.55 niclowe changes due to incorrect commit. by niclowe
 *
 * Revision 1.54  2004/08/16 21:02:09  neildogg
 * - Allows to find activities ending within a time range
 *   (for all time zones)
 *
 * Revision 1.53  2004/08/16 14:39:23  maulani
 * - Override ADODB_Pager class for activities to allow customization
 * - Customization still todo
 *
 * Revision 1.52  2004/08/05 18:42:56  neildogg
 * - Date offset now compatible thanks to
 *   advice from David Rogers
 *
 * Revision 1.51  2004/07/30 13:01:28  neildogg
 * - Restores $search_date using stored $day_diff
 *
 * Revision 1.50  2004/07/27 19:50:41  neildogg
 * - Major changes to browse functionality
 *  - Removal of sidebar for "browse" button
 *  - Removal of activity_type browse
 *  - Aesthetic modifications
 *  - Date in some.php is now mySQL curdate()
 *
 * Revision 1.49  2004/07/27 10:21:50  cpsource
 * - Fix some undefs
 *
 * Revision 1.48  2004/07/26 16:13:00  neildogg
 * - Now it actually defines an undefined variable
 *   instead of assigning it an empty string
 *
 * Revision 1.47  2004/07/23 21:46:54  cpsource
 * - Get rid of some undefined variable usages.
 *
 * Revision 1.46  2004/07/23 11:29:59  braverock
 * - remove hard-coded error_reporting
 * - add time() param to DBTimeStamp call
 *
 * Revision 1.45  2004/07/22 23:58:35  braverock
 * - further cleanup in formatting to minimize possibility of parse errors
 *
 * Revision 1.44  2004/07/22 23:48:25  braverock
 * - fixed a short php tag that was causing a parse error
 * - removed a bunch of stubbed out useless code
 *
 * Revision 1.43  2004/07/22 22:00:24  introspectshun
 * - Updated date offset logic to use portable database code
 * - Removed MySQL-centric date functions and conditional block
 *
 * Revision 1.42  2004/07/22 19:59:03  neildogg
 * - Missed concat .
 *
 * Revision 1.41  2004/07/22 19:57:49  neildogg
 * - activity_type_id is an ID
 *
 * Revision 1.40  2004/07/22 18:06:40  introspectshun
 * - Localized "Add to Everyone"
 *
 * Revision 1.39  2004/07/22 13:58:27  neildogg
 * - Limit group saved-search functionality to admin
 *
 * Revision 1.38  2004/07/21 22:39:04  neildogg
 * - Allow saved search deletion
 *
 * Revision 1.37  2004/07/21 20:32:19  neildogg
 * - Added ability to save searches
 *   Any improvements welcome
 *
 * Revision 1.36  2004/07/16 11:11:48  cpsource
 * - Remove unused $open_activities variable.
 *   Add a programming note on how to efficiently tell if string length is 0
 *
 * Revision 1.35  2004/07/16 04:53:51  introspectshun
 * - Localized strings for i18n/translation support
 *
 * Revision 1.34  2004/07/15 14:07:38  cpsource
 * - Ported arr_vars sub-system.
 *
 * Revision 1.33  2004/07/14 20:19:49  cpsource
 * - Resolved $company_count not being set properly
 *   opportunities/some.php tried to set $this which can't be done in PHP V5
 *
 * Revision 1.32  2004/07/13 14:18:19  neildogg
 * - Changed submit button name to another name
 *   resolves SF bug 9888931 reported by braverock
 *
 * Revision 1.31  2004/07/11 12:32:48  braverock
 * - eliminate manual table generation in favor of pager object
 * - rearrange column order based on input from Walt Pennington
 *
 * Revision 1.30  2004/07/10 12:24:59  braverock
 * - fixed undefined activity_id
 * - fixed misdefined activity_type_pretty_name
 * - fixes SF bugs reported by cpsource
 *
 * Revision 1.29  2004/07/10 12:14:53  braverock
 * - applied patch for undefined variables
 * - modified from SF patch 979124 supplied by cpsource
 *
 * Revision 1.28  2004/07/09 18:35:07  introspectshun
 * - Removed CAST(x AS CHAR) for wider database compatibility
 * - The modified MSSQL driver overrides the default Concat function to cast all datatypes as strings
 *
 * Revision 1.27  2004/07/05 20:29:09  introspectshun
 * - Updated Concat to use CAST AS CHAR for activity_id.
 *
 * Revision 1.26  2004/07/02 15:22:44  maulani
 * - Fix formatting and HTML so page will validate
 *
 * Revision 1.25  2004/06/26 15:17:14  braverock
 * - change search layout to two pages to improve CSS positioning
 *   - applied modified version of SF patch # 971474submitted by s-t
 *
 * Revision 1.24  2004/06/25 03:12:09  braverock
 * - make default search for open activities only
 *
 * Revision 1.23  2004/06/24 19:58:47  braverock
 * - committing enhancements to Save&Next functionality
 *   - patches submitted by Neil Roberts
 *
 * Revision 1.22  2004/06/22 11:04:16  braverock
 * - fixed timestamp to be in proper database compliant mode
 *
 * Revision 1.21  2004/06/21 20:51:01  introspectshun
 * - Now use CAST AS CHAR to convert integers to strings in Concat function calls.
 *
 * Revision 1.20  2004/06/15 14:13:36  gpowers
 * - corrected time formats: changed DBTimeStamp(time()) to time()
 *   -  DBTimeStamp(time()) does not work with MySQL
 *
 * Revision 1.19  2004/06/13 09:15:07  braverock
 * - add Save & Next functionality
 *   - code contributed by Neil Roberts
 *
 * Revision 1.18  2004/06/12 18:15:59  braverock
 * - fix DBTimestamp errors after upgrade
 * - remove CAST, as it is not standard across databases
 *   - database should explicitly convert number to string for CONCAT
 *
 * Revision 1.17  2004/06/11 21:20:11  introspectshun
 * - Now use ADODB Concat and Date functions.
 *
 * Revision 1.16  2004/06/04 16:43:17  braverock
 * - adjusted size of input boxes on search
 * - removed unecessary sidebar whitespace on this page, since it is not used
 *
 * Revision 1.15  2004/06/04 16:28:44  gpowers
 * Removed time selection from calendar applet settings
 *
 * Revision 1.14  2004/06/04 16:03:59  gpowers
 * Applied Patch [ 965012 ] Calendar replacement By: miguel Gonçves - mig77
 * w/minor changes: changed includes to function, used complete php tags
 *
 * Revision 1.13  2004/06/03 16:11:01  braverock
 * - add functionality to support workflow and activity templates
 *   - functionality contributed by Brad Marshall
 *
 * Revision 1.12  2004/05/10 13:07:20  maulani
 * - Add level to audit trail
 * - Clean up audit trail text
 *
 * Revision 1.11  2004/05/04 20:51:26  braverock
 * -set return_url on title link.
 *   - fixes SF bug 947755 reported by Beth Macknik (maulani)
 *
 * Revision 1.10  2004/04/27 13:43:24  gpowers
 * removed audit_items entry for searching. it is a duplicate of information
 * available in the httpd access log.
 *
 * Revision 1.9  2004/04/22 18:29:36  gpowers
 * removed echo order_by , ^M's, //user_id=1
 *
 * Revision 1.8  2004/04/20 12:53:48  braverock
 * - add direct link to activity
 * - add owner in the list
 * - fix bug with sorting options
 *   - apply SF patch 938385 submitted by frenchman
 *
 * Revision 1.7  2004/04/14 22:48:28  maulani
 * - Add CSS2 positioning
 * - Fix minor HTML problems
 * - Update HTML so it will validate
 *
 * Revision 1.6  2004/04/09 21:11:42  braverock
 * - add check for activity_record_status = 'a'
 *   - fixes SF bug 932545 reported by Beth (maulani)
 *
 * Revision 1.5  2004/04/09 20:01:20  braverock
 * - display search results using adodb pager for consistency
 * - allow export of search results as CSV file
 *   - modified files submitted by Olivier Collonna of Fontaine Consulting
 */
?>
