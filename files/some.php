<?php
/**
 * Search for and display a summary of multiple files
 *
 * $Id: some.php,v 1.56 2006/01/20 20:08:42 daturaarutad Exp $
 */

//include required files
require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'utils-saved-search.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');
require_once($include_directory . 'classes/Pager/GUP_Pager.php');
require_once($include_directory . 'classes/Pager/Pager_Columns.php');


$con = get_xrms_dbconnection();
// $con->debug = 1;

$on_what_table='files';
$session_user_id = session_check();

getGlobalVar($browse,'browse');
getGlobalVar($saved_id, 'saved_id');
getGlobalVar($saved_title, 'saved_title');
getGlobalVar($group_item, 'group_item');
getGlobalVar($delete_saved, 'delete_saved');

global $msg;

/*********** SAVED SEARCH BEGIN **********************/
load_saved_search_vars($con, $on_what_table, $saved_id, $delete_saved);

/*********** SAVED SEARCH END **********************/


// declare passed in variables
$arr_vars = array ( // local var name       // session variable name
           'file_id'               => array ( 'file_id', arr_vars_SESSION ),
           'file_summary'          => array ( 'file_summary', arr_vars_SESSION ),
           'file_description'      => array ( 'file_description', arr_vars_SESSION ),
           'file_filesystem_name'  => array ( 'file_filesystem_name', arr_vars_SESSION ),
           'file_on_what'          => array ( 'file_on_what', arr_vars_SESSION ),
           'file_on_what_name'     => array ( 'file_on_what_name', arr_vars_SESSION ),
           'file_date'             => array ( 'file_date', arr_vars_SESSION ),
           'user_id'               => array ( 'file_user_id', arr_vars_SESSION ),
           );

// get all passed in variables
arr_vars_get_all ( $arr_vars );

// set all session variables
arr_vars_session_set ( $arr_vars );



// $con->execute("update users set last_hit = " . $con->dbtimestamp(mktime()) . " where user_id = $session_user_id");

$f_contact      = false;
$f_campaign     = false;
$f_company      = false;
$f_opportunity  = false;
$f_case         = false;
$f_activity     = false;

$sql = "SELECT "
      . $con->Concat($con->qstr('<a id="'), 'file_pretty_name', $con->qstr('" href="' . $http_site_root . '/files/one.php?return_url=/private/home.php&amp;file_id='), 'file_id', $con->qstr('">'), "file_pretty_name", "'</a>'")
      . " AS summary, file_description as description,
      file_pretty_name,
      file_filesystem_name,
      file_size as size,
      u.username AS owner, ";

$sql .= concat_hook_function('file_get_search_fields_sql');

switch ($file_on_what) {
    case "contacts" : {
        $sql .= $con->Concat("'<a href=\"$http_site_root/contacts/one.php?return_url=/private/home.php&amp;contact_id='", "contact_id", "'\">'", "cont.last_name", "' '", "cont.first_names", "'</a>'")
              . " AS contact,"
              . $con->Concat("'<a href=\"$http_site_root/companies/one.php?return_url=/private/home.php&amp;company_id='", "c.company_id", "'\">'", "c.company_name", "'</a>'")
              . " AS company,";
        $f_contact = true;
        $f_company = true;
        break;
    }
    case "contacts_of_companies" : {
        $sql .= $con->Concat("'<a href=\"$http_site_root/contacts/one.php?return_url=/private/home.php&amp;contact_id='", "contact_id", "'\">'", "cont.last_name", "' '", "cont.first_names", "'</a>'")
              . " AS contact,"
              . $con->Concat("'<a href=\"$http_site_root/companies/one.php?return_url=/private/home.php&amp;company_id='", "c.company_id", "'\">'", "c.company_name", "'</a>'")
              . " AS company,";
        $f_contact = true;
        $f_company = true;
        break;
    }
    case "companies" : {
        $sql .= $con->Concat("'<a href=\"$http_site_root/companies/one.php?return_url=/private/home.php&amp;company_id='", "c.company_id", "'\">'", "c.company_name", "'</a>'")
              . " AS company,";
        $f_company = true;
        break;
    }
    case "campaigns" : {
        $sql .= $con->Concat("'<a href=\"$http_site_root/campaigns/one.php?return_url=/private/home.php&amp;campaign_id='", "camp.campaign_id", "'\">'", "camp.campaign_title", "'</a>'")
              . " AS campaign,";
        $f_campaign = true;
        break;
    }
    case "opportunities" : {
        $sql .= $con->Concat("'<a href=\"$http_site_root/opportunities/one.php?return_url=/private/home.php&amp;opportunity_id='", "opportunity_id", "'\">'", "opp.opportunity_title", "'</a>'")
              . " AS opportunity,"
              . $con->Concat("'<a href=\"$http_site_root/companies/one.php?return_url=/private/home.php&amp;company_id='", "c.company_id", "'\">'", "c.company_name", "'</a>'")
              . " AS company,";
        $f_opportunity  = true;
        $f_company      = true;
        break;
    }
    case "cases" : {
        $sql .= ' '.$con->Concat("'<a href=\"$http_site_root/cases/one.php?return_url=/private/home.php&amp;case_id='", "cases.case_id", "'\">'", "cases.case_title", "'</a>'")
              . " AS case_name, "
              . $con->Concat("'<a href=\"$http_site_root/companies/one.php?return_url=/private/home.php&amp;company_id='", "c.company_id", "'\">'", "c.company_name", "'</a>'")
              . " AS company, ";
        $f_case     = true;
        $f_company  = true;
        break;
    }
    case "activities" : {
        $sql .= ' '.$con->Concat("'<a href=\"$http_site_root/activities/one.php?return_url=/private/home.php&amp;activity_id='", "activity_id", "'\">'", "act.activity_title", "'</a>'")
              . " AS activity, "
              . $con->Concat("'<a href=\"$http_site_root/contacts/one.php?return_url=/private/home.php&amp;contact_id='", "cont.contact_id", "'\">'", "cont.last_name", "' '", "cont.first_names", "'</a>'")
              . " AS contact, "
              . $con->Concat("'<a href=\"$http_site_root/companies/one.php?return_url=/private/home.php&amp;company_id='", "c.company_id", "'\">'", "c.company_name", "'</a>'")
              . " AS company, ";
        $f_activity = true;
        $f_contact  = true;
        $f_company  = true;
        break;
    }
    default : {
        $sql .= "";
    }
}

$sql .= $con->SQLDate('Y-m-d','f.entered_at') . " AS date," .
        $con->Concat("'<a href=\"$http_site_root/files/one.php?return_url=/private/home.php&amp;file_id='", "file_id", "'\">'", "file_id", "'</a>'") . " AS ID ";

$from = "from ";
switch ($file_on_what) {
    case "contacts" : { $from .= "contacts cont, companies c, "; break; }
    case "contacts_of_companies" : { $from .= "contacts cont, companies c, "; break; }
    case "companies" : { $from .= "companies c, "; break; }
    case "campaigns" : { $from .= "campaigns camp, "; break; }
    case "opportunities" : { $from .= "opportunities opp, companies c, "; break; }
    case "cases" : { $from .= " cases, companies c, "; break; }
    case "activities" : { $from .= "activities act, contacts cont, companies c, "; break; }
}
$from .= "files f, users u ";

$where = "where f.entered_by = u.user_id ";
switch ($file_on_what) {
    case "contacts" : { $where .= "and f.on_what_table = 'contacts' and f.on_what_id = cont.contact_id and cont.company_id = c.company_id "; break; }
    case "contacts_of_companies" : { $where .= "and f.on_what_table = 'contacts' and f.on_what_id = cont.contact_id and cont.company_id = c.company_id "; break; }
    case "companies" : { $where .= "and f.on_what_table = 'companies' and f.on_what_id = c.company_id  "; break; }
    case "campaigns" : { $where .= "and f.on_what_table = 'campaigns' and f.on_what_id = camp.campaign_id  "; break; }
    case "opportunities" : { $where .= "and f.on_what_table = 'opportunities' and f.on_what_id = opp.opportunity_id and opp.company_id = c.company_id "; break; }
    case "cases" : { $where .= "and f.on_what_table = 'cases' and f.on_what_id = cases.case_id and cases.company_id = c.company_id "; break; }
    case "activities" : { $where .= "and f.on_what_table = 'activities' and f.on_what_id = act.activity_id and act.contact_id = cont.contact_id and act.company_id = c.company_id "; break; }
}
$where .= "and file_record_status = 'a'";

$criteria_count = 0;

if (strlen($file_id) > 0 and is_numeric($file_id)) {
    $criteria_count++;
    $where .= " and f.file_id = " . $con->qstr($file_id, get_magic_quotes_gpc());
}

if (strlen($file_summary) > 0) {
    $criteria_count++;
    $where .= " and f.file_pretty_name like " . $con->qstr('%' . $file_summary . '%', get_magic_quotes_gpc());
}

if (strlen($file_filesystem_name) > 0) {
    $criteria_count++;
    $where .= " and f.file_filesystem_name like " . $con->qstr('%' . $file_filesystem_name . '%', get_magic_quotes_gpc());
}

if (strlen($file_description) > 0) {
    $criteria_count++;
    $where .= " and f.file_description like " . $con->qstr('%' . $file_description . '%', get_magic_quotes_gpc());
}

switch ($file_on_what) {
    case "contacts" : {
            $criteria_count++;
            $where .= " and cont.last_name like " . $con->qstr('%' . $file_on_what_name . '%', get_magic_quotes_gpc());
            break;
        }

    case "contacts_of_companies" : {
            $criteria_count++;
            $where .= " and c.company_name like " . $con->qstr(company_search_string($file_on_what_name), get_magic_quotes_gpc());
            break;
        }

    case "companies" : {
            $criteria_count++;
            $where .= " and c.company_name like " . $con->qstr(company_search_string($file_on_what_name), get_magic_quotes_gpc());
            break;
        }

    case "campaigns" : {
            $criteria_count++;
            $where .= " and camp.campaign_title like " . $con->qstr('%' . $file_on_what_name . '%', get_magic_quotes_gpc());
            break;
        }

    case "opportunities" : {
            $criteria_count++;
            $where .= " and opp.opportunity_title like " . $con->qstr('%' . $file_on_what_name . '%', get_magic_quotes_gpc());
            break;
        }

    case "cases" : {
            $criteria_count++;
            $where .= " and cases.case_title like " . $con->qstr('%' . $file_on_what_name . '%', get_magic_quotes_gpc());
            break;
        }
    case "activities" : {
            $criteria_count++;
            $where .= " and act.activity_title like " . $con->qstr('%' . $file_on_what_name . '%', get_magic_quotes_gpc());
            break;
    }
}

if (strlen($file_date) > 0) {
    $criteria_count++;
    $where .= " and f.entered_at like " . $con->qstr($file_date . '%', get_magic_quotes_gpc());
}

if (strlen($user_id) > 0) {
    $criteria_count++;
    $where .= " and f.entered_by = $user_id";
}

if (!$use_post_vars && (!$criteria_count > 0)) {
    $where .= " and 1 = 2";
} else {
    $list=acl_get_list($session_user_id, 'Read', false, $on_what_table);
    //print_r($list);
    if ($list) {
        if ($list!==true) {
            $list=implode(",",$list);
            $where .= " and f.file_id IN ($list) ";
        }
    } else { $where .= ' AND 1 = 2 '; }
}

$sql .= $from . $where;

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


$sql_recently_viewed = "select * from recent_items r, files f
where r.user_id = $session_user_id
and r.on_what_table = 'files'
and r.recent_action = ''
and r.on_what_id = f.file_id
and file_record_status = 'a'
order by r.recent_item_timestamp desc";

//$con->debug=1;

$recently_viewed_table_rows = '';
$rst = $con->selectlimit($sql_recently_viewed, $recent_items_limit);

if ($rst) {
    while (!$rst->EOF) {
        $recently_viewed_table_rows .= '<tr>';
        $recently_viewed_table_rows .= '<td class=widget_content><a href="one.php?file_id=' . $rst->fields['file_id'] . '" title="'. $rst->fields['file_pretty_name']. '">' . substr( $rst->fields['file_pretty_name'], 0, 20) . '</a></td>';
        $recently_viewed_table_rows .= '<td class=widget_content>' . pretty_filesize($rst->fields['file_size']) . '</td>';
        $recently_viewed_table_rows .= '<td class=widget_content>' . $con->userdate($rst->fields['entered_at']) . '</td>';
        $recently_viewed_table_rows .= '<td class=widget_content>' . $rst->fields['file_id'] . '</td>';
        $recently_viewed_table_rows .= '</tr>';
        $rst->movenext();
    }
    $rst->close();
}

if (strlen($recently_viewed_table_rows) == 0) {
    $recently_viewed_table_rows = '<tr><td class=widget_content colspan=4>' . _("No recently viewed file") . '</td></tr>';
}

$user_menu = get_user_menu($con, $user_id, true);

if ($criteria_count > 0) {
    add_audit_item($con, $session_user_id, 'searched', 'files', '', 4);
}




if (get_system_parameter($con, 'Use Owl') == 'y') {
  echo "<input class=button type=button onclick='javascript: owl()' value='"._("Owl File Management")."'><br><br>";
}

$owner_query_list = "select " . $con->Concat("u.username", "' ('", "count(u.user_id)", "')'") . ", u.user_id $from $where group by u.username order by u.username";

$owner_query_select = $sql . 'AND u.user_id = XXX-value-XXX';

// selects the columns this user is interested in
// no reason to set this if you don't want all by default
if(!$file_default_columns) $file_default_columns =  array('summary', 'size','owner', 'date');

// Set up $pager_widget
$columns = array();
$columns[] = array('name' => _("Summary"), 'index_sql' => 'summary', 'sql_sort_column' => 'file_pretty_name');
$columns[] = array('name' => _("Size"), 'index_calc' => 'size', 'type' => 'filesize');
$columns[] = array('name' => _("Owner"), 'index_sql' => 'owner', 'group_query_list' => $owner_query_list, 'group_query_select' => $owner_query_select);
$columns[] = array('name' => _("ID"), 'index_sql' => 'ID');
$columns[] = array('name' => _("Date"), 'index_sql' => 'date');
$columns[] = array('name' => _("Description"), 'index_sql' => 'description');

if($f_contact) $columns[] = array('name' => _("Contact"), 'index_sql' => 'contact');
if($f_campaign) $columns[] = array('name' => _("Campaign"), 'index_sql' => 'campaign');
if($f_opportunity) $columns[] = array('name' => _("Opportunity"), 'index_sql' => 'opportunity');
if($f_case) $columns[] = array('name' => _("Case"), 'index_sql' => 'case_name');
if($f_company) $columns[] = array('name' => _("Company"), 'index_sql' => 'company');
if($f_activity) $columns[] = array('name' => _("Activity"), 'index_sql' => 'activity');



$pager_columns = new Pager_Columns('FilePager', $columns, $file_default_columns, 'FileForm');
$pager_columns_button = $pager_columns->GetSelectableColumnsButton();
$pager_columns_selects = $pager_columns->GetSelectableColumnsWidget();

$columns = $pager_columns->GetUserColumns('default');

$pager = new GUP_Pager($con, $sql, null, _('Search Results'), 'FileForm', 'FilePager', $columns, false, true);

$file_plugin_params = array('pager' => $pager);
do_hook_function('file_search_files_callback', $file_plugin_params);
$pager = $file_plugin_params['pager'];


if($file_plugin_params['error_status']) {
   $msg .= $file_plugin_params['error_text'];
}

$pager_widget = $pager_columns_selects;
$endrows = "<tr><td class=widget_content_form_element colspan=10> "
            . $pager_columns_button;

$pager->AddEndRows($endrows);
$pager_widget .= $pager->Render($system_rows_per_page);

$con->close();



$page_title = _("Files");
start_page($page_title, true, $msg);

$plugin_search_rows = concat_hook_function('file_get_search_fields_html');

?>

<div id="Main">
    <div id="Content">

        <form action=some.php class="print" method=post name="FileForm">
        <input type=hidden name=use_post_vars value=1>
        <table class=widget cellspacing=1>
        <tr>
                <td class=widget_header colspan=7><?php echo _("Search Criteria"); ?></td>
        </tr>
        <tr>
                <td class=widget_label><?php echo _("File ID"); ?></td>
                <td class=widget_label><?php echo _("File Summary"); ?></td>
                <td class=widget_label><?php echo _("File Description"); ?></td>
                <td class=widget_label><?php echo _("File Name"); ?></td>
        </tr>
        <tr>
                <td class=widget_content_form_element><input type=text name="file_id" size=5 value="<?php  echo $file_id ?>"></td>
                <td class=widget_content_form_element><input type=text name="file_summary" size=12 value="<?php  echo $file_summary ?>"></td>
                <td class=widget_content_form_element><input type=text name="file_description" size=24 value="<?php  echo $file_description ?>"></td>
                <td class=widget_content_form_element><input type=text name="file_filesystem_name" size=12 value="<?php  echo $file_filesystem_name ?>"></td>
        </tr>
        <tr>
                <td class=widget_label><?php echo _("On What"); ?></td>
                <td class=widget_label><?php echo _("On what Name"); ?></td>
                <td class=widget_label><?php echo _("Date"); ?></td>
                <td class=widget_label><?php echo _("Owner"); ?></td>
        </tr>
        <tr>
                <td class=widget_content_form_element>
            <select name="file_on_what">
                        <option value="default"<?php if ($file_on_what == "") { echo " selected"; } ?>></option>
                        <option value="contacts"<?php if ($file_on_what == "contacts" ) { echo " selected"; } ?>><?php echo _("Contacts"); ?></option>
                        <option value="contacts_of_companies"<?php if ($file_on_what == "contacts_of_companies") { echo " selected"; } ?>><?php echo _("Contacts of Companies"); ?></option>
                        <option value="companies"<?php if ($file_on_what == "companies") { echo " selected"; } ?>><?php echo _("Companies"); ?></option>
                        <option value="campaigns"<?php if ($file_on_what == "campaigns") { echo " selected"; } ?>><?php echo _("Campaigns"); ?></option>
                        <option value="opportunities"<?php if ($file_on_what == "opportunities") { echo " selected"; } ?>><?php echo _("Opportunities"); ?></option>
                        <option value="cases"<?php if ($file_on_what == "cases") { echo " selected"; } ?>><?php echo _("Cases"); ?></option>
                        <option value="activities"<?php if ($file_on_what == "activities") { echo " selected"; } ?>><?php echo _("Activities"); ?></option
                    </select>
                </td>
                <td class=widget_content_form_element><input type=text name="file_on_what_name" size=12 value="<?php echo $file_on_what_name; ?>"></td>
                <td class=widget_content_form_element><input type=text name="file_date" size=8 value="<?php echo $file_date; ?>"></td>
                <td class=widget_content_form_element><?php echo $user_menu; ?></td>
        </tr>
        <?php echo $plugin_search_rows; ?>
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
                <td colspan=4 class=widget_content_form_element>
                    <input class=button type=submit value="<?php echo _("Search"); ?>">
                    <input class=button type=button onclick="javascript: clearSearchCriteria();" value="<?php echo _("Clear Search"); ?>">
          </td>
        </tr>
      </table>
        <p>


<?php echo $pager_widget; ?>


    </form>
    </div>

        <!-- right column //-->
    <div id="Sidebar">

        <!-- recently viewed support items //-->
        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=4><?php echo _("Recently Viewed"); ?></td>
            </tr>
            <tr>
                <td class=widget_label><?php echo _("Summary"); ?></td>
                <td class=widget_label><?php echo _("Size"); ?></td>
                <td class=widget_label><?php echo _("Date"); ?></td>
                <td class=widget_label><?php echo _("File ID"); ?></td>
            </tr>
            <?php  echo $recently_viewed_table_rows; ?>
        </table>

    </div>
</div>

<script language="JavaScript" type="text/javascript">
<!--

function initialize() {
    document.forms[0].file_id.focus();
}

initialize();

function owl() {
    document.forms[0].action = "../owl/index.php";
    document.forms[0].submit();
}

function bulkEmail() {
    document.forms[0].action = "../email/index.php";
    document.forms[0].submit();
}

function clearSearchCriteria() {
    location.href = "some.php?clear=1";
}

function submitForm(adodbNextPage) {
    document.forms[0].files_next_page.value = adodbNextPage;
    document.forms[0].submit();
}

function resort(sortColumn) {
    document.forms[0].sort_column.value = sortColumn + 1;
    document.forms[0].files_next_page.value = '';
    document.forms[0].resort.value = 1;
    document.forms[0].submit();
}

//-->
</script>

<?php

end_page();

/**
 * $Log: some.php,v $
 * Revision 1.56  2006/01/20 20:08:42  daturaarutad
 * remove the export button once again
 *
 * Revision 1.55  2006/01/05 14:56:54  braverock
 * - add summary back to default columns
 *
 * Revision 1.54  2006/01/05 14:55:01  braverock
 * - normalize the use of 'Summary' and 'Name' to reflect correct usage
 *
 * Revision 1.53  2006/01/05 14:50:04  braverock
 * *** empty log message ***
 *
 * Revision 1.52  2006/01/05 14:47:57  braverock
 * - add filesystem name to search criteria
 *
 * Revision 1.51  2006/01/05 14:37:58  braverock
 * - remove Mail Merge Button
 *
 * Revision 1.50  2006/01/05 14:34:58  braverock
 * - missing comma causes problems in some queries
 *
 * Revision 1.49  2006/01/05 14:32:53  braverock
 * - add sql for size and owner
 *
 * Revision 1.48  2006/01/05 14:14:45  braverock
 * - rearrange and set default columns
 *
 * Revision 1.47  2006/01/05 14:11:24  braverock
 * - add size and owner columns
 *
 * Revision 1.46  2006/01/05 14:00:18  braverock
 * - remove duplicate selectable columns lines, unecessary
 *
 * Revision 1.45  2006/01/05 13:55:15  braverock
 * - add id to sidebar
 * - add selectable columns widget to search
 *
 * Revision 1.44  2006/01/05 13:37:46  braverock
 * - remove obsolete pager.php
 *
 * Revision 1.43  2006/01/02 23:03:52  vanmer
 * - changed to use centralized dbconnection function
 *
 * Revision 1.42  2005/12/05 20:45:19  daturaarutad
 * removed export and mail merge buttons
 *
 * Revision 1.41  2005/12/05 16:55:08  daturaarutad
 * add activities to on_what_table searching
 *
 * Revision 1.40  2005/11/09 22:36:48  daturaarutad
 * add hooks for files plugin
 *
 * Revision 1.39  2005/10/24 22:04:25  daturaarutad
 * add hook for file_get_search_fields_sql
 *
 * Revision 1.38  2005/08/28 16:37:25  braverock
 * - fix colspan on recently viewed table
 *
 * Revision 1.37  2005/08/05 21:48:17  vanmer
 * - changed files to use centralized company name search function
 *
 * Revision 1.36  2005/08/05 01:50:50  vanmer
 * - added saved search functionality to files
 *
 * Revision 1.35  2005/06/01 16:40:55  ycreddy
 * Adding title attribute to the name html element in the pager and side bar for files
 *
 * Revision 1.34  2005/05/23 01:59:35  maulani
 * - Access system parameters for Use Owl parameter
 *
 * Revision 1.33  2005/04/29 17:56:44  daturaarutad
 * fixed printing of form/search results
 *
 * Revision 1.32  2005/04/28 18:46:18  daturaarutad
 * added files plugin hook
 *
 * Revision 1.31  2005/03/21 13:40:56  maulani
 * - Remove redundant code by centralizing common user menu call
 *
 * Revision 1.30  2005/03/15 22:54:38  daturaarutad
 * pager tuning sql_sort_column
 *
 * Revision 1.29  2005/03/02 22:57:37  daturaarutad
 * updated to use the GUP_Pager class
 *
 * Revision 1.28  2005/02/14 21:46:59  vanmer
 * - updated to reflect speed changes in ACL operation
 *
 * Revision 1.27  2005/02/10 02:31:40  braverock
 * - add is_numeric check for file_id search
 *   - this should be an advanced search field
 *
 * Revision 1.26  2005/01/13 18:47:30  vanmer
 * - Basic ACL changes to allow display functionality to be restricted
 *
 * Revision 1.25  2004/11/26 17:31:38  braverock
 * - fix syntax error where f.description should have been f.file_description
 *
 * Revision 1.24  2004/08/19 13:14:05  maulani
 * - Add specific type pager to ease overriding of layout function
 *
 * Revision 1.23  2004/08/05 14:34:19  introspectshun
 * - Localized remaining option/button strings for i18n/l10n support
 *
 * Revision 1.22  2004/08/04 13:05:18  cpsource
 * - Add hook to OWL.
 *
 * Revision 1.21  2004/07/28 20:44:06  neildogg
 * - Added field recent_action to recent_items
 *  - Same function works transparently
 *  - Current items have recent_action=''
 *  - update_recent_items has new optional parameter
 *
 * Revision 1.20  2004/07/16 03:14:55  introspectshun
 * - Localized SQL aliias strings for i18n/translation support
 *
 * Revision 1.19  2004/07/15 16:43:36  introspectshun
 * - Fixed errant CVS Commit. Updated s-t's code to reflect recent HTML tweaks.
 *
 * Revision 1.18  2004/07/15 13:49:54  cpsource
 * - Added arr_vars sub-system.
 *
 * Revision 1.17  2004/07/14 20:19:50  cpsource
 * - Resolved $company_count not being set properly
 *   opportunities/some.php tried to set $this which can't be done in PHP V5
 *
 * Revision 1.16  2004/07/14 13:14:37  cpsource
 * - Fixed numerous undefined variable usages
 *
 * Revision 1.15  2004/07/14 02:04:12  s-t
 * cvs commit some.php
 *
 * Revision 1.14  2004/07/09 18:44:50  introspectshun
 * - Removed CAST(x AS CHAR) for wider database compatibility
 * - The modified MSSQL driver overrides the default Concat function to cast all datatypes as strings
 *
 * Revision 1.13  2004/06/24 15:54:23  maulani
 * - Fix html errors so search button displays and page validates
 *
 * Revision 1.12  2004/06/21 20:41:37  introspectshun
 * - Now use CAST AS CHAR to convert integers to strings in Concat function calls.
 *
 * Revision 1.11  2004/06/21 14:25:32  braverock
 * - localized strings for i18n/internationalization/translation support
 *
 * Revision 1.10  2004/06/12 18:23:51  braverock
 * - remove CAST, as it is not standard across databases
 *   - database should explicitly convert number to string for CONCAT
 *
 * Revision 1.9  2004/06/12 07:20:40  introspectshun
 * - Now use ADODB GetInsertSQL, GetUpdateSQL, date and Concat functions.
 *
 * Revision 1.8  2004/05/10 13:07:21  maulani
 * - Add level to audit trail
 * - Clean up audit trail text
 *
 * Revision 1.7  2004/04/16 22:22:06  maulani
 * - Add CSS2 positioning
 *
 * Revision 1.6  2004/04/16 14:46:28  maulani
 * - Clean HTML so page will validate
 *
 * Revision 1.5  2004/04/15 22:04:39  maulani
 * - Change to CSS2 positioning
 * - Clean HTML to achieve validation
 *
 * Revision 1.4  2004/04/08 17:00:11  maulani
 * - Update javascript declaration
 * - Add phpdoc
 *
 * Revision 1.3  2004/03/24 12:31:52  braverock
 * - improve search
 * - display recently viewed items
 * - modified from code provided by Olivier Colonna of Fontaine Consulting
 * - add phpdoc
 * ***** NOTE: BUG on long File Descriptions *****
 *
 */
?>
