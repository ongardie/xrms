<?php
/**
 * The user's personal home page.
 *
 * @todo make the user's home page configurable,
 *       to create a 'personal dashboard'
 *
 *
 * $Id: home.php,v 1.43 2005/01/11 23:21:10 vanmer Exp $
 */

// include the common files
require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

//see if we are logged in
$session_user_id = session_check();

// get call arguments
$msg = isset($_GET['msg']) ? $_GET['msg'] : '';


//if phone browser detected redirect to phone plugin
if (stristr($_SERVER['HTTP_USER_AGENT'], "MMP")) {
    header("Location: ../plugins/phone");
    exit;
}

//connect to the database
$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
//$con->debug=1;

/*********************************/
/*** Include the sidebar boxes ***/
//include the Cases sidebar
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

//uncomment the debug line to see what's going on with the query
//$con->debug = 1;

$sql_activities = "
SELECT
  a.activity_id, a.activity_title, a.scheduled_at, a.ends_at, a.on_what_table, a.on_what_id,
  a.entered_at, a.activity_status, a.activity_description, at.activity_type_pretty_name, c.company_id,
  c.company_name, cont.contact_id, cont.first_names as contact_first_names,
  cont.last_name as contact_last_name,
CASE
  WHEN ((a.activity_status = 'o') AND (a.ends_at <  ". $con->DBTimeStamp(time()) . " )) THEN 1
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

$activity_rows = '';

$rst = $con->selectlimit($sql_activities, $display_how_many_activities_on_home_page);

$_SESSION['browse_start']=time();
$_SESSION["search_sql"]=$sql_activities;
$pos=1;
$_SESSION['pos']=$pos;
$next_to_check=array();
if ($rst) {
    while (!$rst->EOF) {

        $company_id = $rst->fields['company_id'];
        $activity_id = $rst->fields['activity_id'];
        $company_name = $rst->fields['company_name'];
        $activity_title = $rst->fields['activity_title'];
        $activity_description = $rst->fields['activity_description'];
        $on_what_table = $rst->fields['on_what_table'];
        $on_what_id = $rst->fields['on_what_id'];
        $scheduled_at = $con->userdate($rst->fields['scheduled_at']);
        $ends_at = $con->userdate($rst->fields['ends_at']);
        $activity_status = $rst->fields['activity_status'];
        $next_to_check[]=$activity_id;        

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
        $activity_rows .= "<td class='$classname'><a href='$http_site_root/activities/one.php?save_and_next=true&activity_id=" . $rst->fields['activity_id']."&return_url=/private/home.php" . "'>" . $rst->fields['activity_title'] . '</a></td>';
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

$_SESSION['next_to_check']=$next_to_check;

///////////////////////////////////
// Show contacts non-uploaded files
$sql_files = "select * from files f, contacts cont where file_size = 0 and f.entered_by = ".$session_user_id . " and f.on_what_id = cont.contact_id and f.on_what_table = 'contacts' order by file_id asc";

$rst = $con->selectlimit($sql_files, $display_how_many_activities_on_company_page);

$classname = 'non_uploaded_file';

$files_rows = '';
if ($rst) {
    if ($rst->rowcount()>0) {
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
}

////////////////////////////////////
// Show companies non-uploaded files
$sql_files = "select * from files f, companies c where file_size = 0 and f.entered_by = ".$session_user_id . " and f.on_what_id = c.company_id and f.on_what_table = 'companies' order by file_id asc";

$rst = $con->selectlimit($sql_files, $display_how_many_activities_on_company_page);

$classname = 'non_uploaded_file';
if ($rst) {
    if ($rst->rowcount()>0) {

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
}
////////////////////////////////////
// Show campaigns non-uploaded files
$sql_files = "select * from files f where file_size = 0 and f.entered_by = ". $session_user_id . " order by file_id asc";

$rst = $con->selectlimit($sql_files, $display_how_many_activities_on_company_page);

$classname = 'non_uploaded_file';
if ($rst->rowcount()>0) {
    $nu_file_rows = "
        <table class=widget cellspacing=1 width='100%'>
            <tr>
                <td class=widget_header colspan=6>" . _("Non Uploaded Files") . "</td>
            </tr>
            <tr>
                <td class=widget_label>" . _("Name") . "</td>
                <td class=widget_label>" . _("Description") . "</td>
                <td class=widget_label>" . _("On What") . "</td>
                <td class=widget_label>" . _("Company") . "</td>
                <td class=widget_label>" . _("Date") . "</td>
                <td class=widget_label>" . _("File ID") . "</td>
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
                       . " AS '" . _("Name") . "',"
                       . $con->Concat("'<a href=\"$http_site_root/companies/one.php?return_url=/private/home.php&company_id='", "c.company_id", "'\">'", "c.company_name", "'</a>'")
                       . " AS '" . _("Company") . "',";
                break;
            }
            case "contacts_of_companies" : {
                $fsql .= $con->Concat("'<a href=\"$http_site_root/contacts/one.php?return_url=/private/home.php&contact_id='", "contact_id", "'\">'", "cont.last_name", "' '", "cont.first_names", "'</a>'")
                      . " AS '" . _("Name") . "',"
                      . $con->Concat("'<a href=\"$http_site_root/companies/one.php?return_url=/private/home.php&company_id='", "c.company_id", "'\">'", "c.company_name", "'</a>'")
                      . " AS '" . _("Company") . "',";
                break;
            }
            case "companies" : {
                $fsql .= $con->Concat("'<a href=\"$http_site_root/companies/one.php?return_url=/private/home.php&company_id='", "c.company_id", "'\">'", "c.company_name", "'</a>'")
                       . " AS '" . _("Name") . "',"
                       . $con->Concat("'<a href=\"$http_site_root/companies/one.php?return_url=/private/home.php&company_id='", "c.company_id", "'\">'", "c.company_name", "'</a>'")
                       . " AS '" . _("Company") . "',";
                break;
            }
            case "campaigns" : {
                $fsql .= $con->concat("'<a href=\"$http_site_root/campaigns/one.php?return_url=/private/home.php&campaign_id='", "camp.campaign_id", "'\">'", "camp.campaign_title", "'</a>'")
                       . " AS '" . _("Campaign") . "',";
                break;
            }
            case "opportunities" : {
                $fsql .= $con->Concat("'<a href=\"$http_site_root/opportunities/one.php?return_url=/private/home.php&opportunity_id='", "opportunity_id", "'\">'", "opp.opportunity_title", "'</a>'")
                       . " AS '" . _("Name") . "',"
                       . $con->Concat("'<a href=\"$http_site_root/companies/one.php?return_url=/private/home.php&company_id='", "c.company_id", "'\">'", "c.company_name", "'</a>'")
                       . " AS '" . _("Company") . "',";
                break;
            }
            case "cases" : {
                $fsql .= $con->Concat("'<a href=\"$http_site_root/cases/one.php?return_url=/private/home.php&case_id='", "case_id", "'\">'", "cases.case_title", "'</a>'")
                       . " AS '" . _("Name") . "',"
                       . $con->Concat("'<a href=\"$http_site_root/companies/one.php?return_url=/private/home.php&company_id='", "c.company_id", "'\">'", "c.company_name", "'</a>'")
                       . " AS '" . _("Company") . "',";
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
} else {
  // else code for "if ($rst->rowcount()>0) ..." above. Make sure $nu_file_rows is always defined.
  $nu_file_rows = '';
}

////////////////////////////////////
// Show opportunities non-uploaded files
$sql_files = "select * from files f, opportunities opp where file_size = 0 and f.entered_by = ".$session_user_id . " and f.on_what_id = opp.opportunity_id and f.on_what_table = 'opportunities' order by file_id asc";

$rst = $con->selectlimit($sql_files, $display_how_many_activities_on_company_page);

$classname = 'non_uploaded_file';

if ($rst->rowcount()>0) {
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

if ($rst->rowcount()>0) {
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
    $files_rows = "<tr><td class=widget_content colspan=7>" . _("No open files") . "</td></tr>";
}

//close the database connection, as we are done with it.
$con->close();

if (!strlen($activity_rows) > 0) {
    $activity_rows = "<tr><td class=widget_content colspan=7>" . _("No open activities") . "</td></tr>";
}

$page_title = _("Home");
start_page($page_title,true,$msg);

?>

<div id="Main">
    <div id="Content">

        <!-- Activity Rows //-->
        <table class=widget cellspacing=1 width="100%">
            <tr>
                <td class=widget_header colspan=7><?php echo _("Open Activities"); ?></td>
            </tr>
            <tr>
                <td class=widget_label><?php echo _("Activity"); ?></td>
                <td class=widget_label><?php echo _("Type"); ?></td>
                <td class=widget_label><?php echo _("Company"); ?></td>
                <td class=widget_label><?php echo _("Contact"); ?></td>
                <td class=widget_label><?php echo _("About"); ?></td>
                <td class=widget_label><?php echo _("Scheduled"); ?></td>
                <td class=widget_label><?php echo _("Due"); ?></td>
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
                <td class=widget_header><?php echo _("Documentation"); ?></td>
            </tr>
            <tr>
                <td><a href="../doc/users/XRMS_User_Manual.pdf"><?php echo _("User Manual"); ?></a> (PDF)</td>
            </tr>
<?php echo do_hook_function('home_docs'); ?>
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
 * - Make sure $nu_file_rows is always defined as something
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
