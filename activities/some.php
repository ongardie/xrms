<?php
/**
 * /activities/some.php
 *
 * Search for and View a list of activities
 *
 * $Id: some.php,v 1.26 2004/07/02 15:22:44 maulani Exp $
 */

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb/adodb-pager.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();

$msg = $_GET['msg'];
$offset = $_POST['offset'];
$clear = ($_GET['clear'] == 1) ? 1 : 0;
$use_post_vars = ($_POST['use_post_vars'] == 1) ? 1 : 0;
$resort = $_POST['resort'];

if ($clear) {
    $sort_column = '';
    $current_sort_column = '';
    $sort_order = '';
    $current_sort_order = '';
    $title = '';
    $contact = '';
    $company = '';
    $owner = '';
    $date = '';
    $before_after = '';
    $activity_type_id = '';
    $completed = '';
    $user_id = '';
} elseif ($use_post_vars) {
    $sort_column = $_POST['sort_column'];
    $current_sort_column = $_POST['current_sort_column'];
    $sort_order = $_POST['sort_order'];
    $current_sort_order = $_POST['current_sort_order'];
    $title = $_POST['title'];
    $contact = $_POST['contact'];
    $company = $_POST['company'];
    $owner = $_POST['owner'];
    $search_date = $_POST['search_date'];
    $before_after = $_POST['before_after'];
    $activity_type_id = $_POST['activity_type_id'];
    $completed = $_POST['completed'];
    $user_id = $_POST['user_id'];
} else {
    $sort_column = $_SESSION['activities_sort_column'];
    $current_sort_column = $_SESSION['activities_current_sort_column'];
    $sort_order = $_SESSION['activities_sort_order'];
    $current_sort_order = $_SESSION['activities_current_sort_order'];
    $title = $_SESSION['activities_title'];
    $contact = $_SESSION['activities_contact'];
    $company = $_SESSION['activities_company'];
    $owner = $_SESSION['activities_owner'];
    $search_date = date('Y-m-d', time());
    //$search_date = $_SESSION['activities_date'];
    $before_after = $_SESSION['activities_before_after'];
    $activity_type_id = $_SESSION['activity_type_id'];
    $completed = $_SESSION['activities_completed'];
    $user_id = $_SESSION['activities_user_id'];
}

if (!strlen($sort_column) > 0) {
    $sort_column = 1;
    $current_sort_column = $sort_column;
    $sort_order = "asc";
}
if (!strlen($completed) > 0) {
    $completed ='o';
}

if (!($sort_column == $current_sort_column)) {
    $sort_order = "asc";
}

$opposite_sort_order = ($sort_order == "asc") ? "desc" : "asc";
$sort_order = (($resort) && ($current_sort_column == $sort_column)) ? $opposite_sort_order : $sort_order;
$ascending_order_image = ' <img border=0 height=10 width=10 src="../img/asc.gif" alt="">';
$descending_order_image = ' <img border=0 height=10 width=10 src="../img/desc.gif" alt="">';
$pretty_sort_order = ($sort_order == "asc") ? $ascending_order_image : $descending_order_image;

$_SESSION['activities_sort_column'] = $sort_column;
$_SESSION['activities_current_sort_column'] = $sort_column;
$_SESSION['activities_sort_order'] = $sort_order;
$_SESSION['activities_current_sort_order'] = $sort_order;
$_SESSION['activities_title'] = $title;
$_SESSION['activities_contact'] = $company;
$_SESSION['activities_owner'] = $owner;
$_SESSION['activities_date'] = $date;
$_SESSION['activities_before_after'] = $before_after;
$_SESSION['activities_type'] = $type;
$_SESSION['activities_completed'] = $completed;
$_SESSION['activities_user_id'] = $user_id;

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

//uncomment this to see what's gonig on with the database
//$con->debug=1;

/*********************************
//*** Include the sidebar boxes ***/
//include the browse sidebar

require_once("browse-sidebar.php");

/** End of the sidebar includes **
//*********************************/

$sql = "SELECT
  (CASE WHEN (activity_status = 'o') AND (ends_at < " . $con->DBTimeStamp(time()) . ") THEN 'Yes' ELSE '-' END) AS is_overdue,"
  . $con->Concat("'<a href=\"one.php?activity_id='", "activity_id", "'&amp;return_url=/activities/some.php\">'", "activity_title", "'</a>'")
  . " AS 'Title',
  at.activity_type_pretty_name AS 'Type'," .
  $con->Concat("'<a href=\"../contacts/one.php?contact_id='", "CAST(cont.contact_id AS CHAR)", "'\">'", "cont.first_names", "' '", "cont.last_name", "'</a>'") . "AS 'Contact'," .
  $con->Concat("'<a href=\"../companies/one.php?company_id='", "CAST(c.company_id AS CHAR)", "'\">'", "c.company_name", "'</a>'") . " AS 'Company',
  u.username AS 'Owner'," .
  $con->SQLDate('Y-m-d','a.scheduled_at') . " AS 'Scheduled'," .
  $con->SQLDate('Y-m-d','a.ends_at') . " AS 'Due'
  FROM companies c, users u, activity_types at, activities a
  LEFT OUTER JOIN contacts cont ON cont.contact_id = a.contact_id
  WHERE a.company_id = c.company_id
  AND a.activity_record_status = 'a'
  AND at.activity_type_id = a.activity_type_id
  AND a.user_id = u.user_id";

$criteria_count = 0;

if (strlen($title) > 0) {
    $criteria_count++;
    $sql .= " and a.activity_title like " . $con->qstr('%' . $title . '%', get_magic_quotes_gpc());
}

if (strlen($contact) > 0) {
    $criteria_count++;
    $sql .= " and cont.last_name like " . $con->qstr('%' . $contact . '%', get_magic_quotes_gpc());
}

if (strlen($company) > 0) {
    $criteria_count++;
    $sql .= " and c.company_name like " . $con->qstr('%' . $company . '%', get_magic_quotes_gpc());
}

if (strlen($user_id) > 0) {
    $criteria_count++;
    $sql .= " and a.user_id = $user_id ";
}

if (strlen($activity_type_id) > 0) {
    $criteria_count++;
    $sql .= " and a.activity_type_id like " . $con->qstr('%' . $activity_type_id . '%', get_magic_quotes_gpc());
}

if (strlen($completed) > 0 and $completed != "all") {
    $criteria_count++;
    $sql .= " and a.activity_status = " . $con->qstr($completed, get_magic_quotes_gpc());
}

if (strlen($search_date) > 0) {
    $criteria_count++;
    if (!$before_after) {
        $sql .= " and a.ends_at < " . $con->qstr($search_date, get_magic_quotes_gpc());
    } else {
        $sql .= " and a.ends_at > " . $con->qstr($search_date, get_magic_quotes_gpc());
    }
}

if (!$use_post_vars && (!$criteria_count > 0)) {
    $sql .= " and 1 = 2";
}


if ($sort_column == 1) {
    $order_by = "is_overdue";
} elseif ($sort_column == 2) {
    $order_by = "activity_title";
} elseif ($sort_column == 3) {
    $order_by = "activity_type_pretty_name";
} elseif ($sort_column == 4) {
    $order_by = "cont.last_name";
} elseif ($sort_column == 5) {
    $order_by = "c.company_name";
} elseif ($sort_column == 6) {
    $order_by = "owner";
} elseif ($sort_column == 7) {
    $order_by = "Scheduled";
} elseif ($sort_column == 8) {
    $order_by = "Due";

} else {
    $order_by = $sort_column;
}


$order_by .= " $sort_order";

$sql .= " order by $order_by"; // is_overdue desc, a.scheduled_at, a.entered_at desc";

$rst = $con->execute($sql);

if ($rst) {
    while (!$rst->EOF) {

        $open_p = $rst->fields['activity_status'];
        $scheduled_at = $rst->unixtimestamp($rst->fields['scheduled_at']);
        $is_overdue = $rst->fields['is_overdue'];

        if ($open_p == 'o') {
            if ($is_overdue) {
                $classname = 'overdue_activity';
            } else {
                $classname = 'open_activity';
            }
        } else {
            $classname = 'closed_activity';
        }

        $open_activities .= '<tr>';
        $open_activities .= '<td class=' . $classname . '><a href=one.php?activity_id=' . $rst->fields['activity_id'] . '>' . $rst->fields['Title'] . '</a></td>';
        $open_activities .= '<td class=' . $classname . '>' . $rst->fields['activity_type_pretty_name'] . '</td>';
        $open_activities .= '<td class=' . $classname . '>' . $rst->fields['Company'] . '</td>';
        $open_activities .= '<td class=' . $classname . '>' . $rst->fields['Contact'] . '</td>';
        $open_activities .= '<td class=' . $classname . '>' . $con->userdate($rst->fields['Scheduled']) . '</td>';
        $open_activities .= '<td class=' . $classname . '>' . $con->userdate($rst->fields['Due']) . '</td>';
        $open_activities .= '</tr>';
        $rst->movenext();
    }
    $rst->close();
}

//get menu for users
$sql2 = "select username, user_id from users where user_record_status = 'a' order by username";
$rst = $con->execute($sql2);
$user_menu = $rst->getmenu2('user_id', $user_id, true);
$rst->close();

//get activity type menu
$sql_type = "select activity_type_pretty_name, activity_type_id
from activity_types at
order by activity_type_pretty_name";
$rst = $con->execute($sql_type);
$type_menu = $rst->getmenu2('activity_type_id', $activity_type_id, true);
$rst->close();

//check to see if $open_activities record set is empty
if (!strlen($open_activities) > 0) {
    $open_activities = "<tr><td class=widget_content colspan=5>No open activities</td></tr>";
}
add_audit_item($con, $session_user_id, 'searched', 'activities', '', 4);

$page_title = "Open Activities";
start_page($page_title, true, $msg);

?>

<?php jscalendar_includes(); ?>

<div id="Main">
    <div id="Content">

    <form action=some.php method=post>
        <input type=hidden name=use_post_vars value=1>
        <input type=hidden name=activities_next_page value="<?php  echo $activities_next_page; ?>">
        <input type=hidden name=resort value="0">
        <input type=hidden name=current_sort_column value="<?php  echo $sort_column; ?>">
        <input type=hidden name=sort_column value="<?php  echo $sort_column; ?>">
        <input type=hidden name=current_sort_order value="<?php  echo $sort_order; ?>">
        <input type=hidden name=sort_order value="<?php  echo $sort_order; ?>">
        <table class=widget cellspacing=1 width="100%">
            <tr>
                <td class=widget_header colspan=4>Search Criteria</td>
            </tr>
        <tr>
            <td colspan="2" class=widget_label>Title</td>
            <td class=widget_label>Contact</td>
            <td class=widget_label>Company</td>
        </tr>
        <tr>
            <td colspan="2" class=widget_content_form_element> <input type=text name="title" size=24 value="<?php  echo $title; ?>">
            </td>
            <td class=widget_content_form_element><input type=text name="contact" size=12 value="<?php  echo $contact; ?>">
            </td>
            <td class=widget_content_form_element><input type=text name="company" size=15 value="<?php  echo $company; ?>">
            </td>
        </tr>
        <tr>
            <td class=widget_label>Owner</td>
            <td class=widget_label>End/Due Date</td>
            <td class=widget_label>Type</td>
            <td class=widget_label>Completed</td>
        </tr>
        <tr>
            <td class=widget_content_form_element>
                <?php  echo $user_menu; ?>
            </td>
            <td class=widget_content_form_element>
                <select name="before_after">
                    <option value=""<?php if (!$before_after) { print " selected"; } ?>>Before</option>
                    <option value="after"<?php if ($before_after == "after") { print " selected"; } ?>>After</option>
                </select>
                <input type=text ID="f_date_d" name="search_date" size=12 value="<?php  echo $search_date; ?>">
                <img ID="f_trigger_d" style="CURSOR: hand" border=0 src="../img/cal.gif" alt="">
            </td>
            <td class=widget_content_form_element>
                <?php  echo $type_menu; ?>
            </td>
            <td class=widget_content_form_element>
                <select name="completed">
                    <option value="all"<?php if ($completed == "all") { print " selected"; } ?>>All</option>
                    <option value="o"<?php if ($completed == "o" or !$completed) { print " selected"; } ?>>Non-Completed</option>
                    <option value="c"<?php if ($completed == "c") { print " selected"; } ?>>Completed</option>
                </select>
            </td>
        </tr>
        <tr>
            <td class=widget_content_form_element colspan=4><input name="submit" type=submit class=button value="Search">
                <input name="button" type=button class=button onClick="javascript: clearSearchCriteria();" value="Clear Search">
                <?php if ($company_count > 0) {print "<input class=button type=button onclick='javascript: bulkEmail()' value='Bulk E-Mail'>";}; ?>
            </td>
        </tr>
    </table>
    </form>

<?php

$pager = new ADODB_Pager($con,$sql, 'activities', false, $sort_column-1, $pretty_sort_order);
$pager->render($rows_per_page=$system_rows_per_page);
$con->close();

?>

    </div>

        <!-- right column //-->
    <div id="Sidebar">

        <!-- browse //-->
        <?php echo $browse_block; ?>

    </div>
</div>

<script language="JavaScript" type="text/javascript">

function initialize() {
    document.forms[0].title.focus();
}

initialize();

function bulkEmail() {
    document.forms[0].action = "../email/index.php";
    document.forms[0].submit();
}

function exportIt() {
    document.forms[0].action = "export.php";
    document.forms[0].submit();
    // reset the form so that post-export searches work
    document.forms[0].action = "some.php";
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

end_page();

/**
 * $Log: some.php,v $
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
 *
 */
?>