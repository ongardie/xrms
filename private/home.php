<?php
/**
 * The user's personal home page.
 *
 * @todo make the user's home page configurable,
 *       to create a 'personal dashboard'
 *
 *
 * $Id: home.php,v 1.19 2004/06/12 16:46:55 braverock Exp $
 */

// include the common files
require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

//set target and see if we are logged in
$this = $_SERVER['REQUEST_URI'];
$session_user_id = session_check( $this );

$msg = $_GET['msg'];

//connect to the database
$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

/*********************************/
/*** Include the sidebar boxes ***/
//include the Cases sidebar
$case_limit_sql = "and cases.user_id = $session_user_id";
require_once("../cases/sidebar.php");

//include the opportunities sidebar
$opportunity_limit_sql = "and opportunities.user_id = $session_user_id \nand status_open_indicator = 'o'";

require_once("../opportunities/sidebar.php");

//include the files sidebar
require_once("../files/sidebar.php");

//include the notes sidebar
require_once("../notes/sidebar.php");

/** End of the sidebar includes **/
/*********************************/

//uncomment the debug line to see what's going on with the query
//$con->debug = 1;

$sql_activities = "
SELECT
  a.activity_id, a.activity_title, a.scheduled_at, a.ends_at, a.on_what_table, a.on_what_id,
  a.entered_at, a.activity_status, at.activity_type_pretty_name, c.company_id,
  c.company_name, cont.contact_id, cont.first_names as contact_first_names,
  cont.last_name as contact_last_name,
CASE
  WHEN ((a.activity_status = 'o') AND (a.ends_at < " . $con->DBTimeStamp(time()) . ")) THEN 1
  ELSE 0
END AS is_overdue
FROM activity_types at, companies c, activities a
LEFT JOIN contacts cont on a.contact_id = cont.contact_id
WHERE a.user_id = $session_user_id
  AND a.activity_type_id = at.activity_type_id
  AND a.company_id = c.company_id
  AND a.activity_status = 'o'
  AND a.activity_record_status = 'a'
ORDER BY is_overdue DESC, a.scheduled_at, a.entered_at
";

$rst = $con->selectlimit($sql_activities, $display_how_many_activities_on_home_page);

if ($rst) {
    while (!$rst->EOF) {

        $company_id = $rst->fields['company_id'];
        $company_name = $rst->fields['company_name'];
        $activity_title = $rst->fields['activity_title'];
        $activity_description = $rst->fields['activity_description'];
        $on_what_table = $rst->fields['on_what_table'];
        $on_what_id = $rst->fields['on_what_id'];
        $scheduled_at = $con->userdate($rst->fields['scheduled_at']);
        $ends_at = $con->userdate($rst->fields['ends_at']);
        $activity_status = $rst->fields['activity_status'];

        $attached_to_link = '';
        $attached_to_name = '';

        if ($on_what_table == 'opportunities') {
            $attached_to_link = "<a href='$http_site_root/opportunities/one.php?opportunity_id=$on_what_id'>";
            $sql = "select opportunity_title as attached_to_name from opportunities where opportunity_id = $on_what_id";
        } elseif ($on_what_table == 'cases') {
            $attached_to_link = "<a href='$http_site_root/cases/one.php?case_id=$on_what_id'>";
            $sql = "select case_title as attached_to_name from cases where case_id = $on_what_id";
        } else {
            $attached_to_link = "";
            $sql = "select * from users where 1 = 2";
        }

        $rst2 = $con->execute($sql);

        if ($rst2) {
            $attached_to_name = $rst2->fields['attached_to_name'];
            $rst2->close();
        }

        $attached_to_link .= $attached_to_name . "</a>";

        $open_p = $rst->fields['activity_status'];
        $scheduled_at = $rst->unixtimestamp($rst->fields['scheduled_at']);
        $is_overdue = $rst->fields['is_overdue'];

        if ($open_p == 'o') {
            if ($is_overdue) {
                $classname = 'overdue_activity';
            } else if (mktime() < $scheduled_at){
                $classname = 'scheduled_activity';
            } else {
                $classname = 'open_activity';
                }
        } else {
            $classname = 'closed_activity';
        }

        $activity_rows .= '<tr>';
        $activity_rows .= "<td class='$classname'><a href='$http_site_root/activities/one.php?return_url=/private/home.php&activity_id=" . $rst->fields['activity_id'] . "'>" . $rst->fields['activity_title'] . '</a></td>';
        $activity_rows .= '<td class=' . $classname . '>' . $rst->fields['activity_type_pretty_name'] . '</td>';
        $activity_rows .= '<td class=' . $classname . "><a href='../companies/one.php?company_id=" . $rst->fields['company_id'] . "'>" . $rst->fields['company_name'] . '</a></td>';
        $activity_rows .= '<td class=' . $classname . "><a href='../contacts/one.php?contact_id=" . $rst->fields['contact_id'] . "'>" . $rst->fields['contact_first_names'] . ' ' .  $rst->fields['contact_last_name'] . '</a></td>';
        $activity_rows .= '<td class=' . $classname . '>' . $attached_to_link . '</td>';
        $activity_rows .= '<td class=' . $classname . '>' . $con->userdate($rst->fields['scheduled_at']) . '</td>';
        $activity_rows .= '<td class=' . $classname . '>' . $con->userdate($rst->fields['ends_at']) . '</td>';
        $activity_rows .= '</tr>';
        $rst->movenext();
    }
    $rst->close();
}

///////////////////////////////////
// Show contacts non-uploaded files
$sql_files = "select * from files f, contacts cont where file_size = 0 and f.entered_by = ".$session_user_id . " and f.on_what_id = cont.contact_id and f.on_what_table = 'contacts' order by file_id asc";

$rst = $con->selectlimit($sql_files, $display_how_many_activities_on_company_page);

$classname = 'non_uploaded_file';

if ($rst) {
    while (!$rst->EOF) {

        $file_id = $rst->fields['file_id'];
        $file_name = $rst->fields['file_pretty_name'];
        $file_description = $rst->fields['file_description'];
        $file_on_what_table = $rst->fields['on_what_table'];
        $file_on_what_name = $rst->fields['last_name']." ".$rst->fields['first_names'];
        $file_on_what_name_id = $rst->fields['contact_id'];
        $file_date = $rst->fields['entered_at'];

        $files_rows .= '<tr>';
        $files_rows .= "<td class='$classname'><a href='$http_site_root/files/one.php?return_url=/private/home.php&file_id=" . $rst->fields['file_id'] . "'>" . $rst->fields['file_id'] . '</a></td>';
        $files_rows .= '<td class=' . $classname . '>' . $file_name . '</td>';
        $files_rows .= '<td class=' . $classname . '>' . $file_description . '</td>';
        $files_rows .= '<td class=' . $classname . '>' . $file_on_what_table . '</td>';
        $files_rows .= '<td class=' . $classname . '><a href="'.$http_site_root.'/contacts/one.php?contact_id='.$file_on_what_name_id.'">' . $file_on_what_name . '</a></td>';
        $files_rows .= '<td class=' . $classname . '>' . $file_date . '</td>';
        $files_rows .= '</tr>';
        $rst->movenext();
    }
    $rst->close();
}

////////////////////////////////////
// Show companies non-uploaded files
$sql_files = "select * from files f, companies c where file_size = 0 and f.entered_by = ".$session_user_id . " and f.on_what_id = c.company_id and f.on_what_table = 'companies' order by file_id asc";

$rst = $con->selectlimit($sql_files, $display_how_many_activities_on_company_page);

$classname = 'non_uploaded_file';

if ($rst) {

    while (!$rst->EOF) {

        $file_id = $rst->fields['file_id'];
        $file_name = $rst->fields['file_pretty_name'];
        $file_description = $rst->fields['file_description'];
        $file_on_what_table = $rst->fields['on_what_table'];
        $file_on_what_name = $rst->fields['company_name'];
        $file_on_what_name_id = $rst->fields['company_id'];
        $file_date = $rst->fields['entered_at'];

        $files_rows .= '<tr>';
        $files_rows .= "<td class='$classname'><a href='$http_site_root/files/one.php?return_url=/private/home.php&file_id=" . $rst->fields['file_id'] . "'>" . $rst->fields['file_id'] . '</a></td>';
        $files_rows .= '<td class=' . $classname . '>' . $file_name . '</td>';
        $files_rows .= '<td class=' . $classname . '>' . $file_description . '</td>';
        $files_rows .= '<td class=' . $classname . '>' . $file_on_what_table . '</td>';
        $files_rows .= '<td class=' . $classname . '><a href="'.$http_site_root.'/companies/one.php?company_id='.$file_on_what_name_id.'">' . $file_on_what_name . '</a></td>';
        $files_rows .= '<td class=' . $classname . '>' . $file_date . '</td>';
        $files_rows .= '</tr>';
        $rst->movenext();
    }
    $rst->close();
}

////////////////////////////////////
// Show campaigns non-uploaded files
$sql_files = "select * from files f, where file_size = 0 and f.entered_by = ".$session_user_id . " order by file_id asc";

$rst = $con->selectlimit($sql_files, $display_how_many_activities_on_company_page);

$classname = 'non_uploaded_file';

if ($rst) {
    $nu_file_rows = "
        <table class=widget cellspacing=1 width='100%'>
            <tr>
                <td class=widget_header colspan=6>Non Uploaded Files</td>
            </tr>
            <tr>
                <td class=widget_label>Name</td>
                <td class=widget_label>Description</td>
                <td class=widget_label>On What</td>
                <td class=widget_label>Company</td>
                <td class=widget_label>Date</td>
                <td class=widget_label>File ID</td>
            </tr>
        </table>";



    while (!$rst->EOF) {

        $file_id = $rst->fields['file_id'];
        $file_name = $rst->fields['file_pretty_name'];
        $file_description = $rst->fields['file_description'];
        $file_on_what = $rst->fields['on_what_table'];
        $on_what_id = $rst->fields['on_what_id'];
        $file_date = $rst->fields['entered_at'];


        //add switches for 'on what' here
        $fsql = "select ";
        $fsql .= "from files f, users u ";
        switch ($file_on_what) {
            case "contacts" : { $fsql .= "contacts cont, companies c, "; break; }
            case "contacts_of_companies" : { $fsql .= "contacts cont, companies c, "; break; }
            case "companies" : { $fsql .= "companies c, "; break; }
            case "campaigns" : { $fsql .= "campaigns camp, "; break; }
            case "opportunities" : { $fsql .= "opportunities opp, companies c, "; break; }
            case "cases" : { $fsql .= "cases cases, companies c, "; break; }
        }
        switch ($file_on_what) {
            case "contacts" : {
                $fsql .= $con->Concat("'<a href=\"$http_site_root/contacts/one.php?return_url=/private/home.php&contact_id='", "contact_id", "'\">'", "cont.first_names", "' '", "cont.last_name", "'</a>'")
                       . " AS 'Name',"
                       . $con->Concat("'<a href=\"$http_site_root/companies/one.php?return_url=/private/home.php&company_id='", "c.company_id", "'\">'", "c.company_name", "'</a>'")
                       . " AS 'Company',";
                break;
            }
            case "contacts_of_companies" : {
                $fsql .= $con->Concat("'<a href=\"$http_site_root/contacts/one.php?return_url=/private/home.php&contact_id='", "contact_id", "'\">'", "cont.last_name", "' '", "cont.first_names", "'</a>'")
                      . " AS 'Name',"
                      . $con->Concat("'<a href=\"$http_site_root/companies/one.php?return_url=/private/home.php&company_id='", "c.company_id", "'\">'", "c.company_name", "'</a>'")
                      . " AS 'Company',";
                break;
            }
            case "companies" : {
                $fsql .= $con->Concat("'<a href=\"$http_site_root/companies/one.php?return_url=/private/home.php&company_id='", "c.company_id", "'\">'", "c.company_name", "'</a>'")
                       . " AS 'Name',"
                       . $con->Concat("'<a href=\"$http_site_root/companies/one.php?return_url=/private/home.php&company_id='", "c.company_id", "'\">'", "c.company_name", "'</a>'")
                       . " AS 'Company',";
                break;
            }
            case "campaigns" : {
                $fsql .= $con->concat("'<a href=\"$http_site_root/campaigns/one.php?return_url=/private/home.php&campaign_id='", "camp.campaign_id ", "'\">'", "camp.campaign_title", "'</a>'")
                       . " AS 'Campaign',";
                break;
            }
            case "opportunities" : {
                $fsql .= $con->Concat("'<a href=\"$http_site_root/opportunities/one.php?return_url=/private/home.php&opportunity_id='", "opportunity_id", "'\">'", "opp.opportunity_title", "'</a>'")
                       . " AS 'Name',"
                       . $con->Concat("'<a href=\"$http_site_root/companies/one.php?return_url=/private/home.php&company_id='", "c.company_id", "'\">'", "c.company_name", "'</a>'")
                       . " AS 'Company',";
                break;
            }
            case "cases" : {
                $fsql .= $con->Concat("'<a href=\"$http_site_root/cases/one.php?return_url=/private/home.php&case_id='", "case_id", "'\">'", "cases.case_title", "'</a>'")
                       . " AS 'Name',"
                       . $con->Concat("'<a href=\"$http_site_root/companies/one.php?return_url=/private/home.php&company_id='", "c.company_id", "'\">'", "c.company_name", "'</a>'")
                       . " AS 'Company',";
                break;
            }
            default : {
                $fsql .= "";
                }
        }
        $where = "where f.entered_by = $session_user_id ";
        switch ($file_on_what) {
            case "contacts" : { $where .= "and f.on_what_table = 'contacts' and cont.contact_id = $on_what_id and cont.company_id = c.company_id "; break; }
            case "contacts_of_companies" : { $where .= "and f.on_what_table = 'contacts' and cont.contact_id = $on_what_id and cont.company_id = c.company_id "; break; }
            case "companies" : { $where .= "and f.on_what_table = 'companies' and c.company_id = $on_what_id "; break; }
            case "campaigns" : { $where .= "and f.on_what_table = 'campaigns' and camp.campaign_id = $on_what_id  "; break; }
            case "opportunities" : { $where .= "and f.on_what_table = 'opportunities' and opp.opportunity_id = $on_what_id and opp.company_id = c.company_id "; break; }
            case "cases" : { $where .= "and f.on_what_table = 'cases' and cases.case_id = $on_what_id and cases.company_id = c.company_id "; break; }
        }
        $where .= "and file_record_status = 'a'";

        $fsql .= $where;
        $frst = $con->execute($fsql);

        //now build the file row
        $nu_file_rows .= '<tr>';
        $nu_file_rows .= "<td class=non_uploaded_file><a href='$http_site_root/files/one.php?return_url=/contacts/one.php?contact_id=$contact_id&file_id=" . $rst->fields['file_id'] . "'>" . $rst->fields['file_pretty_name'] . '</a></b></td>';
        $nu_file_rows .= '<td class=' . $classname . '>' . $file_name . '</td>';
        $nu_file_rows .= '<td class=' . $classname . '>' . $file_description . '</td>';
        if ($frst) {
           $nu_file_rows .= '<td class=' . $classname . '>' . $frst->fields['Name'] . '</td>';
           $nu_file_rows .= '<td class=' . $classname . '>' . $frst->fields['Company'] . '</td>';
        } else {
           $nu_file_rows .= "<td></td>\n<td></td>\n";
        }
        $nu_file_rows .= '<td class=' . $classname . '>' . $file_date . '</td>';
        $nu_file_rows .= "<td class='$classname'><a href='$http_site_root/files/one.php?return_url=/private/home.php&file_id=" . $rst->fields['file_id'] . "'>" . $rst->fields['file_id'] . '</a></td>';
        $nu_file_rows .= '</tr>';
        $rst->movenext();
    }
    $rst->close();
}

////////////////////////////////////
// Show opportunities non-uploaded files
$sql_files = "select * from files f, opportunities opp where file_size = 0 and f.entered_by = ".$session_user_id . " and f.on_what_id = opp.opportunity_id and f.on_what_table = 'opportunities' order by file_id asc";

$rst = $con->selectlimit($sql_files, $display_how_many_activities_on_company_page);

$classname = 'non_uploaded_file';

if ($rst) {
    while (!$rst->EOF) {

        $file_id = $rst->fields['file_id'];
        $file_name = $rst->fields['file_pretty_name'];
        $file_description = $rst->fields['file_description'];
        $file_on_what_table = $rst->fields['on_what_table'];
        $file_on_what_name = $rst->fields['opportunity_title'];
        $file_on_what_name_id = $rst->fields['opportunity_id'];
        $file_date = $rst->fields['entered_at'];

        $files_rows .= '<tr>';
        $files_rows .= "<td class='$classname'><a href='$http_site_root/files/one.php?return_url=/private/home.php&file_id=" . $rst->fields['file_id'] . "'>" . $rst->fields['file_id'] . '</a></td>';
        $files_rows .= '<td class=' . $classname . '>' . $file_name . '</td>';
        $files_rows .= '<td class=' . $classname . '>' . $file_description . '</td>';
        $files_rows .= '<td class=' . $classname . '>' . $file_on_what_table . '</td>';
        $files_rows .= '<td class=' . $classname . '><a href="'.$http_site_root.'/opportunities/one.php?opportunity_id='.$file_on_what_name_id.'">' . $file_on_what_name . '</a></td>';
        $files_rows .= '<td class=' . $classname . '>' . $file_date . '</td>';
        $files_rows .= '</tr>';
        $rst->movenext();
    }
    $rst->close();
}

////////////////////////////////////
// Show cases non-uploaded files
$sql_files = "select * from files f, cases where file_size = 0 and f.entered_by = ".$session_user_id . " and f.on_what_id = cases.case_id and f.on_what_table = 'cases' order by file_id asc";

$rst = $con->selectlimit($sql_files, $display_how_many_activities_on_company_page);

$classname = 'non_uploaded_file';

if ($rst) {
    while (!$rst->EOF) {

        $file_id = $rst->fields['file_id'];
        $file_name = $rst->fields['file_pretty_name'];
        $file_description = $rst->fields['file_description'];
        $file_on_what_table = $rst->fields['on_what_table'];
        $file_on_what_name = $rst->fields['case_title'];
        $file_on_what_name_id = $rst->fields['case_id'];
        $file_date = $rst->fields['entered_at'];

        $files_rows .= '<tr>';
        $files_rows .= "<td class='$classname'><a href='$http_site_root/files/one.php?return_url=/private/home.php&file_id=" . $rst->fields['file_id'] . "'>" . $rst->fields['file_id'] . '</a></td>';
        $files_rows .= '<td class=' . $classname . '>' . $file_name . '</td>';
        $files_rows .= '<td class=' . $classname . '>' . $file_description . '</td>';
        $files_rows .= '<td class=' . $classname . '>' . $file_on_what_table . '</td>';
        $files_rows .= '<td class=' . $classname . '><a href="'.$http_site_root.'/cases/one.php?case_id='.$file_on_what_name_id.'">' . $file_on_what_name . '</a></td>';
        $files_rows .= '<td class=' . $classname . '>' . $file_date . '</td>';
        $files_rows .= '</tr>';
        $rst->movenext();
    }
    $rst->close();
}

$con->close();

if (!strlen($files_rows) > 0) {
    $files_rows = "<tr><td class=widget_content colspan=7>No open files</td></tr>";
}

//close the database connection, as we are done with it.
$con->close();

if (!strlen($activity_rows) > 0) {
    $activity_rows = "<tr><td class=widget_content colspan=7>No open activities</td></tr>";
}

$page_title = "Home";
start_page($page_title);

?>

<div id="Main">
    <div id="Content">

        <!-- Activity Rows //-->
        <table class=widget cellspacing=1 width="100%">
            <tr>
                <td class=widget_header colspan=7>Open Activities</td>
            </tr>
            <tr>
                <td class=widget_label>Activity</td>
                <td class=widget_label>Type</td>
                <td class=widget_label>Company</td>
                <td class=widget_label>Contact</td>
                <td class=widget_label>About</td>
                <td class=widget_label>Scheduled</td>
                <td class=widget_label>Due</td>
            </tr>
            <?php  echo $activity_rows; ?>
        </table>
        <!-- Non-Uploaded Files //-->
            <?php  echo $nu_file_rows; ?>

    </div>

        <!-- right column //-->
    <div id="Sidebar">


        <table class=widget cellspacing=1 width="100%">
            <tr>
                <td class=widget_header>Documentation</td>
            </tr>
            <tr>
                <td class=widget_label><a href="../doc/users/xrms_user_manual.pdf">User Manual</a> (PDF)</td>
            </tr>
        </table>

            <!-- opportunities //-->
            <?php  echo $opportunity_rows; ?>

            <!-- cases //-->
            <?php  echo $case_rows; ?>

            <!-- files //-->
            <?php  echo $file_rows; ?>

            <!-- notes //-->
            <?php  echo $note_rows; ?>
            <form action="../notes/new.php" method="post">
                <input type="hidden" name="on_what_table" value="users">
                <input type="hidden" name="on_what_id" value=<?php echo $session_user_id ?>>
                <input type="hidden" name="return_url" value="/private/home.php">
                <input type="submit" class=button value="New Personal Note">
            </form>

    </div>
</div>

<?php

end_page();

/**
 * $Log: home.php,v $
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
