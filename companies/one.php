<?php
/**
 * Details about One Company
 *
 * Usually called from companies/some.php, but also linked to from many
 * other places in the XRMS UI.
 *
 * $Id: one.php,v 1.94 2005/02/25 03:34:08 daturaarutad Exp $
 *
 * @todo create a centralized left-pane handler for activities (in companies, contacts,cases, opportunities, campaigns)
 */

//include required files
require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');
require_once($include_directory . 'utils-accounting.php');
require_once($include_directory . 'classes/Pager/Pager_Columns.php');
require_once($include_directory . 'classes/Pager/GUP_Pager.php');
require_once('../activities/activities-pager-functions.php');


global $company_id;
$company_id = $_GET['company_id'];
if (isset($_GET['division_id'])) {
    $division_id = $_GET['division_id'];
} else $division_id=false;

global $on_what_id;
$on_what_id=$company_id;
global $session_user_id;
$session_user_id = session_check();

$msg = isset($_GET['msg']) ? $_GET['msg'] : '';

$company_id = $_GET['company_id'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
// $con->debug = 1;

$activities_form_name = 'Company_Activities';
$contacts_form_name = 'Company_Contacts';


// make sure $accounting_rows is defined
if ( !isset($accounting_rows) ) {
  $accounting_rows = '';
}
//call the accounting hook
$accounting_rows = do_hook_function('company_accounting', $accounting_rows);

update_recent_items($con, $session_user_id, "companies", $company_id);

$sql = "select cs.*, c.*, account_status_display_html, rating_display_html, company_source_display_html, i.industry_pretty_name, u1.username as owner_username, u2.username as entered_by, u3.username as last_modified_by, c.default_primary_address
        from crm_statuses cs, companies c, account_statuses as1, ratings r, company_sources cs2, industries i, users u1, users u2, users u3
        where c.account_status_id = as1.account_status_id
        and c.industry_id = i.industry_id
        and c.rating_id = r.rating_id
        and c.company_source_id = cs2.company_source_id
        and c.crm_status_id = cs.crm_status_id
        and c.user_id = u1.user_id
        and c.entered_by = u2.user_id
        and c.last_modified_by = u3.user_id
        and c.company_id = $company_id";

$rst = $con->execute($sql);

if ($rst) {
  if ( !$rst->EOF ) {

    // record found
    $company_name = $rst->fields['company_name'];
    $legal_name = $rst->fields['legal_name'];
    $address_id = $rst->fields['default_primary_address'];
    $tax_id = $rst->fields['tax_id'];
    $company_code = $rst->fields['company_code'];
    $industry_pretty_name = $rst->fields['industry_pretty_name'];
    $crm_status_pretty_name = $rst->fields['crm_status_pretty_name'];
    $company_source = $rst->fields['company_source_display_html'];
    $industry_pretty_name = $rst->fields['industry_pretty_name'];
    $user_id = $rst->fields['user_id'];
    $owner_username = $rst->fields['owner_username'];
    $phone = get_formatted_phone($con, $address_id, $rst->fields['phone']);
    $phone2 = get_formatted_phone($con, $address_id, $rst->fields['phone2']);
    $fax = get_formatted_phone($con, $address_id, $rst->fields['fax']);
    $url = $rst->fields['url'];
    if ((substr($url, 0, 4)!=='http') and (strlen($url) >0)) {
        $url = 'http://'.$url;
    }
    $employees = $rst->fields['employees'];
    $revenue = $rst->fields['revenue'];
    $account_status = $rst->fields['account_status_display_html'];
    $credit_limit = $rst->fields['credit_limit'];
    $rating = $rst->fields['rating_display_html'];
    $terms = $rst->fields['terms'];
    $profile = $rst->fields['profile'];
    $profile = str_replace ("\n","<br>\n",htmlspecialchars($profile));
    $entered_by = $rst->fields['entered_by'];
    $entered_at = $con->userdate($rst->fields['entered_at']);
    $last_modified_by = $rst->fields['last_modified_by'];
    $last_modified_at = $con->userdate($rst->fields['last_modified_at']);
    $custom1 = $rst->fields['custom1'];
    $custom2 = $rst->fields['custom2'];
    $custom3 = $rst->fields['custom3'];
    $custom4 = $rst->fields['custom4'];
    $extref1 = $rst->fields['extref1'];
    $extref2 = $rst->fields['extref2'];

  } else {
    // record not found

    $company_name = '';
    $legal_name = '';
    $address_id = '';
    $tax_id = '';
    $company_code = '';
    $industry_pretty_name = '';
    $crm_status_pretty_name = '';
    $company_source = '';
    $industry_pretty_name = '';
    $user_id = '';
    $owner_username = '';
    $phone = '';
    $phone2 = '';
    $fax = '';
    $url = '';
    $employees = '';
    $revenue = '';
    $account_status = '';
    $credit_limit = '';
    $rating = '';
    $terms = '';
    $profile = '';
    $profile = '';
    $entered_by = '';
    $entered_at = '';
    $last_modified_by = '';
    $last_modified_at = '';
    $custom1 = '';
    $custom2 = '';
    $custom3 = '';
    $custom4 = '';
    $extref1 = '';
    $extref2 = '';

  }

  // close the result set
  $rst->close();

} else {
    db_error_handler ($con, $sql);
}

$credit_limit = number_format($credit_limit, 2);
$current_credit_limit = fetch_current_customer_credit_limit($extref1);


if (strlen($url) > 0) {
    $url = "<a target='_new' href='" . $url . "'>$url</a>";
}

//if division_id is specified, look up the name
if ($division_id) {
    $sql = "SELECT division_name, address_id
            FROM company_division
            WHERE division_id=$division_id";
    $rst=$con->execute($sql);
    if (!$rst) {
        db_error_handler($con, $sql);
    } else {
        $division_name=$rst->fields['division_name'];
        if ($rst->fields['address_id']) $address_id=$rst->fields['address_id'];
    }
}

//show address (of division if specified, main if not specified)
$address_to_display = get_formatted_address($con, $address_id);


//list and create division select box
$sql = "SELECT division_name, division_id
        FROM company_division
        WHERE company_id=$company_id
        AND division_record_status='a'";
$division_rst=$con->execute($sql);
if (!$division_rst) { db_error_handler($con, $sql); }
elseif ($division_rst->numRows()>0) {
    $division_select=<<<TILLEND
    <script language=JavaScript>
    <!--
        function restrictByDivision() {
            select=document.getElementById('division_id');
            location.href = 'one.php?company_id=$company_id&division_id=' + select.value;
        }
     //-->
    </script>
TILLEND;

    $division_select.=$division_rst->getmenu2('division_id',$division_id, true, false, 1, "id=division_id onchange=javascript:restrictByDivision();");
    if ($division_id) {
        $division_select.="&nbsp; <input class=button type=button value=\"". _("Administer Division")."\" onclick=\"javascript:location.href='".$http_site_root."/companies/edit-division.php?company_id=$company_id&division_id=$division_id';\">";
    }
} else { $division_select=false; }

//
//  list of most recent activities (note that the order of sql fields is important for the GUP_Pager)
//
$sql_activities = "SELECT " . 
$con->Concat("'<a id=\"'", "activity_title", "'\" href=\"$http_site_root/activities/one.php?activity_id='", "a.activity_id", "'&amp;return_url=/companies/one.php%3Fcompany_id=$company_id\">'", "activity_title", "'</a>'") .  " AS  activity_title," . 
"u.username, at.activity_type_pretty_name, " . 
$con->Concat($con->qstr('<a id="'), 'cont.last_name', $con->qstr('_'), 'cont.first_names', $con->qstr('" href="../contacts/one.php?contact_id='), 'cont.contact_id', $con->qstr('">'), 'cont.first_names', $con->qstr(' '), 'cont.last_name', $con->qstr('</a>')) . ' AS contact_name, ' . 
"a.scheduled_at, a.on_what_table, a.on_what_id, a.entered_at, a.activity_status,     
(CASE WHEN ((a.activity_status = 'o') AND (a.scheduled_at < " . $con->SQLDate('Y-m-d') . ")) THEN 1 ELSE 0 END) AS is_overdue
FROM activity_types at, users u, activities a
LEFT JOIN contacts cont ON cont.contact_id = a.contact_id
LEFT JOIN opportunities o ON o.opportunity_id=a.on_what_id
LEFT JOIN cases cas ON cas.case_id=a.on_what_id
WHERE a.company_id = $company_id
  AND a.user_id = u.user_id
  AND a.activity_type_id = at.activity_type_id
  AND a.activity_record_status = 'a'";
    
    $list=acl_get_list($session_user_id, 'Read', false, 'activities');
    
    if ($list) {
        if ($list!==true) {
            $list=implode(",",$list);
            $sql_activities .= " and a.activity_id IN ($list) ";
        }
    } else { $sql_activities .= ' AND 1 = 2 '; }

if ($division_id) {
    $sql_activities.=" AND (a.on_what_table='company_division' AND a.on_what_id=$division_id";
    $sql_activities.=" OR a.on_what_table='opportunities' AND o.division_id=$division_id";
    $sql_activities.=" OR a.on_what_table='cases' AND cas.division_id=$division_id)";
    
}

// begin Activities Pager
$columns = array();
$columns[] = array('name' => _('Title'), 'index_sql' => 'activity_title');
$columns[] = array('name' => _('User'), 'index_sql' => 'username');
$columns[] = array('name' => _('Type'), 'index_sql' => 'activity_type_pretty_name');
$columns[] = array('name' => _('Contact'), 'index_sql' => 'contact_name');
$columns[] = array('name' => _('About'), 'index_calc' => 'activity_about');
$columns[] = array('name' => _('Scheduled'), 'index_sql' => 'scheduled_at');

$default_columns = array('activity_title', 'username','activity_type_pretty_name','contact_name','activity_about','scheduled_at');


// selects the columns this user is interested in
$pager_columns = new Pager_Columns('CompanyActivitiesPager', $columns, $default_columns, $activities_form_name);
$pager_columns_button = $pager_columns->GetSelectableColumnsButton();
$activities_pager_columns_selects = $pager_columns->GetSelectableColumnsWidget();

$columns = $pager_columns->GetUserColumns('default');

$endrows = "<tr><td class=widget_content_form_element colspan=10>
            $pager_columns_button
            <input type=button class=button onclick=\"javascript: bulkEmail();\" value=" . _('Mail Merge') . "></td></tr>";

$pager = new GUP_Pager($con, $sql_activities, 'GetActivitiesPagerData', _('Activities'), $activities_form_name, 'CompanyActivitiesPager', $columns, false, true);
$pager->AddEndRows($endrows);

$activity_rows = $pager->Render($system_rows_per_page);
// end Activities Pager


// contacts query
$sql = "select " . 
		$con->Concat($con->qstr('<a id="'), 'last_name', $con->qstr(' '), 'first_names', $con->qstr('" href="' . $http_site_root . '/contacts/one.php?contact_id='), "contact_id", $con->qstr('&amp;return_url=/companies/one.php%3Fcompany_id=' . $company_id . '">'), "last_name", $con->qstr(', '), "first_names", $con->qstr('</a>')) . " AS name, summary, title, description, email, contact_id, first_names, last_name, address_id, work_phone
from contacts where company_id = $company_id and contact_record_status = 'a'";


if ($division_id) {
    $sql .=" AND division_id=$division_id";
}

//echo htmlentities($sql);

// begin Contacts Pager
$columns = array();
$columns[] = array('name' => _('Name'), 'index_sql' => 'name');
$columns[] = array('name' => _('Summary'), 'index_sql' => 'summary');
$columns[] = array('name' => _('Title'), 'index_sql' => 'title');
$columns[] = array('name' => _('Description'), 'index_sql' => 'description');
$columns[] = array('name' => _('Phone'), 'index_calc' => 'phone');
$columns[] = array('name' => _('E-Mail'), 'index_calc' => 'email');

$default_columns = array('name','summary','title','description','phone','email');


// selects the columns this user is interested in
$pager_columns = new Pager_Columns('ContactsPager', $columns, $default_columns, $contacts_form_name);
$pager_columns_button = $pager_columns->GetSelectableColumnsButton();
$contacts_pager_columns_selects = $pager_columns->GetSelectableColumnsWidget();

$columns = $pager_columns->GetUserColumns('default');

$new_contact_location="../contacts/new.php?company_id=$company_id";
if ($division_id) $new_contact_location.= "&division_id=$division_id"; 
	

$endrows = "<tr><td class=widget_content_form_element colspan=10>
            $pager_columns_button
            <input type=button class=button onclick=\"javascript: bulkEmail();\" value=" . _('Mail Merge') . ">" .
			render_create_button("New",'button',"location.href='$new_contact_location';") .  "</td></tr>";

    // this is the callback function that the pager uses to fill in the calculated data.
    function getContactDetails($row) {
        global $con;
		global $session_user_id;
		global $company_id;

		// this is for the CTI dialing bit
		global $contact_id;
		$contact_id = $row['contact_id'];

        $row['phone'] = get_formatted_phone($con, $row['address_id'], $row['work_phone']);
        $row['email'] = "<a href='mailto:{$row['email']}' onclick=\"location.href='../activities/new-2.php?user_id=$session_user_id&activity_type_id=3&company_id=$company_id&contact_id={$row['contact_id']}&email=true&return_url=/companies/one.php%3Fcompany_id=$company_id&activity_title=email%20to%20{$row['first_names']}%20{$row['last_name']}'\" >" . htmlspecialchars($row['email']) . '</a>';

        return $row;
    }


$pager = new GUP_Pager($con, $sql, 'getContactDetails', _('Contacts'), $contacts_form_name, 'ContactsPager', $columns, false, true);
$pager->AddEndRows($endrows);

$contact_rows = $pager->Render($system_rows_per_page);
// end Contacts Pager


// former names

$sql = "select * from company_former_names where company_id = $company_id order by namechange_at desc";

$rst = $con->execute($sql);

$former_name_rows = '';
if ($rst) {
    while (!$rst->EOF) {
        $former_name_rows .= '<tr><td class=sublabel>'._("Former Name").'</td>';
        $former_name_rows .= '<td class=clear>' . $rst->fields['former_name'] . '</td>';
        $former_name_rows .= '</tr>';
        $rst->movenext();
    }
    $rst->close();
} else {
    db_error_handler ($con, $sql);
}

/*********************************/
/*** Include the sidebar boxes ***/

//set up our substitution variables for use in the sidebars
$on_what_table = 'companies';
$on_what_id = $company_id;

//include the categories sidebar
require_once($include_directory . 'categories-sidebar.php');

//include the Cases sidebar
$case_limit_sql = "and cases.".make_singular($on_what_table)."_id = $on_what_id";
if ($division_id) { $case_limit_sql .=" AND cases.division_id=$division_id"; }
require_once("../cases/sidebar.php");

//include the opportunities sidebar
$opportunity_limit_sql = "and opportunities.".make_singular($on_what_table)."_id = $on_what_id";
if ($division_id) { $opportunity_limit_sql .=" AND opportunities.division_id=$division_id"; }
require_once("../opportunities/sidebar.php");

//include the contacts-companies sidebar
$relationships = array('companies' => $company_id);
require_once("../relationships/sidebar.php");

// include the files sidebar
require_once("../files/sidebar.php");

// include the notes sidebar
require_once("../notes/sidebar.php");

// make sure $sidebar_rows is defined
if ( !isset($sidebar_rows) ) {
  $sidebar_rows = '';
}
//call the sidebar hook
$sidebar_rows = do_hook_function('company_sidebar_bottom', $sidebar_rows);

// make sure $bottom_rows is defined
if ( !isset($bottom_rows) ) {
  $bottom_rows = '';
}
//call the sidebar hook
$bottom_rows = do_hook_function('company_content_bottom', $bottom_rows);

/** End of the sidebar includes **/
/*********************************/

$sql = "SELECT " . $con->Concat("first_names", "' '", "last_name") . " AS contact_name, contact_id
        FROM contacts
        WHERE company_id = $company_id
        AND contact_record_status = 'a'
        ORDER BY last_name";

$rst = $con->execute($sql);
if ($rst) {
    $contact_menu = $rst->getmenu2('contact_id', '', true, false, 0, 'style="font-size: x-small; border: outset; width: 80px;"');
    $rst->close();
} else {
    db_error_handler ($con, $sql);
}

$sql = "select username, user_id from users where user_record_status = 'a' order by username";
$rst = $con->execute($sql);
if ($rst) {
    $user_menu = $rst->getmenu2('user_id', $session_user_id, false, false, 0, 'style="font-size: x-small; border: outset; width: 80px;"');
    $rst->close();
} else {
    db_error_handler ($con, $sql);
}

$sql = "SELECT activity_type_pretty_name, activity_type_id
        FROM activity_types
        WHERE activity_type_record_status = 'a'
        ORDER BY sort_order, activity_type_pretty_name";
$rst = $con->execute($sql);
if ($rst) {
    $activity_type_menu = $rst->getmenu2('activity_type_id', '', false, false, 0, 'style="font-size: x-small; border: outset; width: 80px;"');
    $rst->close();
} else {
    db_error_handler ($con, $sql);
}

add_audit_item($con, $session_user_id, 'viewed', 'companies', $company_id, 3);

//close the database connection, we don't need it anymore
$con->close();

if (!$former_name_rows) {
    $former_name_rows = "";
}

if (!isset($relationship_rows)) {
    $relationship_rows = "";
}

if (!isset($division_name)) {
    $page_title = _("Company Details") . ' : ' . $company_name;
} else {
    $page_title = $company_name . ' : ' . $division_name;
}
start_page($page_title, true, $msg);

?>

<script language="JavaScript" type="text/javascript">
<!--
function markComplete() {
    document.forms[0].activity_status.value = "c";
    document.forms[0].submit();
}

//-->
</script>

<div id="Main">
    <div id="Content">

        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header><?php echo _("Company Details"); ?></td>
            </tr>
            <tr>
                <td class=widget_content>

                    <table border=0 cellpadding=0 cellspacing=0 width="100%">
                        <tr>
                            <td width="50%" class=clear align=left valign=top>
                                <table border=0 cellpadding=0 cellspacing=0 width="100%">
                                <tr>
                                    <td width="1%" class=sublabel><?php echo _("Company Name"); ?></td>
                                    <td class=clear><?php  echo $company_name; ?></td>
                                </tr>
                                <?php if ($legal_name) { ?>
                                <tr>
                                    <td width="1%" class=sublabel><?php echo _("Legal Name"); ?></td>
                                    <td class=clear><?php  echo $legal_name; ?></td>
                                </tr>
                                <?php }; ?>
                                <?php  echo $former_name_rows; ?>
                                <?php if ($company_code) { ?>
                                <tr>
                                    <td class=sublabel><?php echo _("Code"); ?></td>
                                    <td class=clear><?php  echo $company_code; ?></td>
                                </tr>
                                <?php }; ?>
                                <?php if ($industry_pretty_name) { ?>
                                <tr>
                                    <td class=sublabel><?php echo _("Industry"); ?></td>
                                    <td class=clear><?php  echo $industry_pretty_name; ?></td>
                                </tr>
                                <?php }; ?>
                                <?php if ($crm_status_pretty_name) { ?>
                                <tr>
                                    <td class=sublabel><?php echo _("CRM Status"); ?></td>
                                    <td class=clear><?php  echo $crm_status_pretty_name; ?></td>
                                </tr>
                                <?php }; ?>
                                <?php if ($owner_username) { ?>
                                <tr>
                                    <td class=sublabel><?php echo _("Account Owner"); ?></td>
                                    <td class=clear><?php  echo $owner_username; ?></td>
                                </tr>
                                <?php }; ?>
                                <?php if ($phone) { ?>
                                <tr>
                                    <td class=sublabel><?php echo _("Phone"); ?></td>
                                    <td class=clear><?php  echo $phone; ?></td>
                                </tr>
                                <?php }; ?>
                                <?php if ($phone2) { ?>
                                <tr>
                                    <td class=sublabel><?php echo _("Alt. Phone"); ?></td>
                                    <td class=clear><?php  echo $phone2; ?></td>
                                </tr>
                                <?php }; ?>
                                <?php if ($fax) { ?>
                                <tr>
                                    <td class=sublabel><?php echo _("Fax"); ?></td>
                                    <td class=clear><?php  echo $fax; ?></td>
                                </tr>
                                <?php }; ?>
                                <?php if ($url) { ?>
                                <tr>
                                    <td class=sublabel><?php echo _("URL"); ?></td>
                                    <td class=clear><?php echo $url; ?></td>
                                </tr>
                                <?php }; ?>
                                <tr>
                                    <td class=sublabel>&nbsp;</td>
                                    <td class=clear>&nbsp;</td>
                                </tr>
                                <?php if ($address_to_display) { ?>
                                <tr>
                                    <td class=sublabel><?php echo _("Address"); ?></td>
                                    <td class=clear><?php echo $address_to_display ?></td>
                                </tr>
                                <?php }; ?>
                                <tr>
                                    <td class=sublabel>&nbsp;</td>
                                    <td class=clear>&nbsp;</td>
                                </tr>
                                <?php if ($division_select) { ?>
                                <tr>
                                    <td class=sublabel><?php echo _("Restrict by Division"); ?></td>
                                    <td class=clear><?php echo $division_select; ?>
                                </tr>
                                <?php }; ?>
                                </table>

                            </td>

                            <td width=50% class=clear align=left valign=top>

                                <table border=0 cellpadding=0 cellspacing=0 width=100%>
                                <?php if ($account_status) { ?>
                                <tr>
                                    <td class=sublabel><?php echo _("Account Status"); ?></td>
                                    <td class=clear><?php echo $account_status; ?></td>
                                </tr>
                                <?php }; ?>
                                <?php if ($tax_id) { ?>
                                <tr>
                                    <td class=sublabel><?php echo _("Tax ID"); ?></td>
                                    <td class=clear><?php echo $tax_id; ?></td>
                                </tr>
                                <?php }; ?>
                                <?php if ($credit_limit OR $current_credit_limit) { ?>
                                <tr>
                                    <td class=sublabel><?php echo _("Credit Limit"); ?></td>
                                    <td class=clear>$<?php echo $credit_limit; ?> <?php echo $current_credit_limit; ?></td>
                                </tr>
                                <?php }; ?>
                                <?php if ($rating) { ?>
                                <tr>
                                    <td class=sublabel><?php echo _("Rating"); ?></td>
                                    <td class=clear><?php echo $rating; ?></td>
                                </tr>
                                <?php }; ?>
                                <?php if ($terms) { ?>
                                <tr>
                                    <td class=sublabel><?php echo _("Terms"); ?></td>
                                    <td class=clear><?php echo $terms; ?> <?php echo _("days"); ?></td>
                                </tr>
                                <?php }; ?>
                                <!-- accounting plugin -->
                                <?php echo $accounting_rows; ?>
                                <tr>
                                    <td class=sublabel>&nbsp;</td>
                                    <td class=clear>&nbsp;</td>
                                </tr>
                                <?php if ($company_source) { ?>
                                <tr>
                                    <td width=1% class=sublabel><?php echo _("Company Source"); ?></td>
                                    <td class=clear><?php echo $company_source; ?></td>
                                </tr>
                                <?php }; ?>
                                <?php if ($employees) { ?>
                                <tr>
                                    <td class=sublabel><?php echo _("Employees"); ?></td>
                                    <td class=clear><?php  echo $employees; ?></td>
                                </tr>
                                <?php }; ?>
                                <?php if ($revenue) { ?>
                                <tr>
                                    <td class=sublabel><?php echo _("Revenue"); ?></td>
                                    <td class=clear><?php echo $revenue; ?></td>
                                </tr>
                                <?php }; ?>
                                <tr>
                                    <td class=sublabel>&nbsp;</td>
                                    <td class=clear>&nbsp;</td>
                                </tr>
                                <?php if ($custom1) { ?>
                                <tr>
                                    <td width=1% class=sublabel><?php echo _($company_custom1_label); ?></td>
                                    <td class=clear><?php  echo $custom1; ?></td>
                                </tr>
                                <?php }; ?>
                                <?php if ($custom2) { ?>
                                <tr>
                                    <td class=sublabel><?php echo _($company_custom2_label); ?></td>
                                    <td class=clear><?php  echo $custom2; ?></td>
                                </tr>
                                <?php }; ?>
                                <?php if ($custom3) { ?>
                                <tr>
                                    <td class=sublabel><?php echo _($company_custom3_label); ?></td>
                                    <td class=clear><?php  echo $custom3; ?></td>
                                </tr>
                                <?php }; ?>
                                <?php if ($custom4) { ?>
                                <tr>
                                    <td class=sublabel><?php echo _($company_custom4_label); ?></td>
                                    <td class=clear><?php  echo $custom4; ?></td>
                                </tr>
                                <?php }; ?>
                                <tr>
                                    <td class=sublabel>&nbsp;</td>
                                    <td class=clear>&nbsp;</td>
                                </tr>
                                    <?php echo $relationship_rows; ?>
                                <tr>
                                    <td class=sublabel><?php echo _("Created"); ?></td>
                                    <td class=clear><?php  echo $entered_at; ?> by <?php echo $entered_by; ?></td>
                                </tr>
                                <tr>
                                    <td class=sublabel><?php echo _("Last Modified"); ?></td>
                                    <td class=clear><?php  echo $last_modified_at; ?> by <?php echo $last_modified_by; ?></td>
                                </tr>                                    
                            </table>

                            </td>
                        </tr>
                    </table>

                    <p><?php echo $profile; ?>

                </td>
            </tr>
            <tr>
                <td class=widget_content_form_element>
                <?php echo render_edit_button("Edit", 'button', "javascript: location.href='edit.php?company_id=$company_id';"); ?>
                <input class=button type=button value="<?php echo _("Admin"); ?>" onclick="javascript:location.href='admin.php?company_id=<?php echo $company_id; ?>';">
                <input class=button type=button value="<?php echo _("Clone"); ?>" onclick="javascript: location.href='new.php?clone_id=<?php echo $company_id ?>';">
                <input class=button type=button value="<?php echo _("Mail Merge"); ?>" onclick="javascript: location.href='../email/email.php?scope=company&company_id=<?php echo $company_id; ?>';">
                <input class=button type=button value="<?php echo _("Addresses"); ?>" onclick="javascript: location.href='addresses.php?company_id=<?php echo $company_id; ?>';">
                <?php
                    if (!$division_id) {
                        //only show the Division button if we are not already scoped by Division
                ?>    
                <input class=button type=button value="<?php echo _("Divisions"); ?>" onclick="javascript: location.href='divisions.php?company_id=<?php echo $company_id; ?>';">
                <?php } //end Division button check ?>
                <?php do_hook('company_buttons'); ?>
                </td>
            </tr>
        </table>

        <!-- contacts //-->
        <form name="<?php echo $contacts_form_name; ?>" method=post>
            <?php 
				// contacts pager
				echo $contacts_pager_columns_selects; 
				echo $contact_rows; 
			?>
		</form>


<?php
    jscalendar_includes();

    //place the plug-in hook before the Activities
    do_hook ('company_detail');

?>

        <!-- new activity //-->
        <form action="<?php  echo $http_site_root; ?>/activities/new-2.php" method=post>

        <input type=hidden name=return_url value="/companies/one.php?company_id=<?php  echo $company_id; ?><?php echo ($division_id) ? "%26division_id=" . $division_id : ''; ?>">
        <input type=hidden name=company_id value="<?php echo $company_id ?>">
        <input type=hidden name=activity_status value="o">
        <input type=hidden name=use_post_vars value="1">
        <?php if ($division_id) { $on_what_table='company_division'; $on_what_id=$division_id; } ?>
        <input type=hidden name=on_what_table        value="<?php echo $on_what_table; ?>">
        <input type=hidden name=on_what_id           value="<?php echo $on_what_id; ?>">
        <input type=hidden name=activity_description value="">
        <input type=hidden name=email                value="">
        <input type=hidden name=followup             value="">
        <input type=hidden name=on_what_status       value="">
        <input type=hidden name=ends_at              value="">

        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=6><?php echo _("Add New Activity"); ?></td>
            </tr>
            <tr>
                <td class=widget_label><?php echo _("Title"); ?></td>
                <td class=widget_label><?php echo _("User"); ?></td>
                <td class=widget_label><?php echo _("Type"); ?></td>
                <td class=widget_label><?php echo _("Contact"); ?></td>
                <td class=widget_label><?php echo _("About"); ?></td>
                <td class=widget_label><?php echo _("Starts"); ?></td>
            </tr>
            <tr>
                <td class=widget_content_form_element><input type=text name=activity_title></td>
                <td class=widget_content_form_element><?php echo $user_menu; ?></td>
                <td class=widget_content_form_element><?php echo $activity_type_menu; ?></td>
                <td class=widget_content_form_element><?php echo $contact_menu; ?></td>
                <td class=widget_content_form_element>&nbsp;</td>
                <td class=widget_content_form_element>
                    <input type=text size=10 ID="f_date_d" name=scheduled_at value="<?php  echo date('Y-m-d H:i:s'); ?>">
                    <img ID="f_trigger_d" style="CURSOR: hand" border=0 src="../img/cal.gif">
                    <?php echo render_create_button("Add"); ?>
                    <?php echo render_create_button("Done",'button',"javascript: markComplete();"); ?>
                </td>
            </tr>
        </table>
        </form>

        <!-- activities list //-->
        <form name="<?php echo $activities_form_name; ?>" method=post>
            <?php 
				// activities pager
				echo $activities_pager_columns_selects; 
				echo $activity_rows; 
			?>
		</form>


        <!-- company content bottom plugins //-->
        <?php echo $bottom_rows; ?>

    </div>

        <!-- right column //-->
    <div id="Sidebar">

        <!-- categories //-->
        <?php echo $category_rows; ?>

        <!-- opportunities //-->
        <?php echo $opportunity_rows; ?>

        <!-- cases //-->
        <?php echo $case_rows; ?>

        <!-- contact/company //-->
        <?php echo $relationship_link_rows; ?>

        <!-- notes //-->
        <?php echo $note_rows; ?>

        <!-- files //-->
        <?php echo $file_rows; ?>

        <!-- sidebar plugins //-->
        <?php echo $sidebar_rows; ?>

    </div>

</div>

<script>
Calendar.setup({
        inputField     :    "f_date_d",      // id of the input field
        ifFormat       :    "%Y-%m-%d %H:%M:%S",       // format of the input field
        showsTime      :    true,            // will display a time selector
        button         :    "f_trigger_d",   // trigger for the calendar (button ID)
        singleClick    :    false,           // double-click mode
        step           :    1,                // show all years in drop-down boxes (instead of every other year as default)
        align          :    "Bl"           // alignment (defaults to "Bl")
    });


function bulkEmail() {
    document.forms[0].action = "../email/email.php";
    document.forms[0].submit();
}

</script>

<?php

end_page();

/**
 * $Log: one.php,v $
 * Revision 1.94  2005/02/25 03:34:08  daturaarutad
 * contacts and activities now using GUP_Pager class
 *
 * Revision 1.93  2005/02/17 08:00:28  daturaarutad
 * updated to use GUP Pager class for activities
 *
 * Revision 1.92  2005/02/14 21:43:45  vanmer
 * - updated to reflect speed changes in ACL operation
 *
 * Revision 1.91  2005/02/10 21:16:45  maulani
 * - Add audit trail entries
 *
 * Revision 1.90  2005/02/01 00:26:07  braverock
 * - remove 'News' button, as this functionaltiy has been moved to the weblinks plugin
 *
 * Revision 1.89  2005/01/31 17:22:43  braverock
 * - Administer Divisions link hardcode to localhost - oops, fixed now
 *
 * Revision 1.88  2005/01/28 23:05:57  braverock
 * - show the correct button for editing divisions depending on division scoping
 *
 * Revision 1.87  2005/01/25 21:56:01  daturaarutad
 * fixed bug when adding an activity to a company
 *
 * Revision 1.86  2005/01/24 02:49:35  braverock
 * - properly urlencode return_url strings
 * - add check for division_id before includign it in return_url
 *
 * Revision 1.85  2005/01/22 15:07:25  braverock
 * - add sort order to activity_types menu
 *
 * Revision 1.84  2005/01/21 14:48:53  braverock
 * - only add 'active' divisions to the select list
 *
 * Revision 1.83  2005/01/13 18:20:54  vanmer
 * - ACL restriction on activity list
 *
 * Revision 1.82  2005/01/13 16:49:36  vanmer
 * - updated to use $division_id instead of isset $divison_id, because isset will not check if the field is blank
 *
 * Revision 1.81  2005/01/12 02:38:50  introspectshun
 * - Added tests for undefined division_id
 *
 * Revision 1.80  2005/01/10 20:47:48  neildogg
 * - Changed to support new relationship sidebar variable requirement
 *
 * Revision 1.79  2005/01/09 00:27:17  braverock
 * - change company_content_bottom hook to us $bottom rows instead of the incorrect $sidebar_rows
 *
 * Revision 1.78  2005/01/06 21:55:30  vanmer
 * - moved address lookup to below division lookup to allow division address to be displayed instead of main address
 * - added logic to optionally set address to address of division, if set
 *
 * Revision 1.77  2005/01/06 20:41:45  vanmer
 * - added division scoping of activities to include cases/opportunities which match the division specified
 * - removed on_what_string hack, changed to use standard make_singular function
 *
 * Revision 1.76  2005/01/06 18:37:13  vanmer
 * - added restriction by division to one company page
 * - added code to hide company fields which have not been set
 * - added code to restrict contacts and activities by division_id, when set
 * - added code to add contact with correct division, when restricted by division
 * - added code to add activity setting table/id to company_division/division_id
 *
 * Revision 1.75  2005/01/06 15:47:22  vanmer
 * - added style entries to activity top row in one company view, to shrink activities so that they do not overlap the sidebars
 * - replaced edit buttons with new render button functions (allows for ACL control of the display of buttons)
 *
 * Revision 1.74  2005/01/05 23:08:38  braverock
 * - changed incorrect second occurance of sidebar_rows to bottom_rows
 *   this was causing plugins to not display in the sidebar
 *
 * Revision 1.73  2005/01/03 16:42:45  gpowers
 * - added company_content_bottom plugin hook
 *
 * Revision 1.72  2005/01/03 16:26:46  gpowers
 * - added company_accounting plugin hook
 *
 * Revision 1.71  2004/12/31 22:31:33  vanmer
 * - forced menu text in activities section to use small text
 * - removed extraneous column from activity list
 * - limited size of start date input to allow activities to not spill over into sidebar
 *
 * Revision 1.70  2004/12/30 20:09:40  vanmer
 * - moved company_id above session_check (prelude to ACL)
 * - removed relationship information from main company section (now all included in sidebar)
 *
 * Revision 1.69  2004/11/09 00:06:53  gpowers
 * - Corrected display of newlines in profile
 *
 * Revision 1.68  2004/10/22 21:06:15  introspectshun
 * - Centralized category handling as sidebar
 *
 * Revision 1.67  2004/09/15 15:45:01  neildogg
 * - Added hook for more company buttons
 *
 * Revision 1.66  2004/08/05 22:53:16  introspectshun
 * - Localized 'Former Name'
 * - Contacts table now shows singular label ("Contact") when 1 record returned
 *
 * Revision 1.65  2004/08/03 11:18:35  cpsource
 * - Bug 993235 - industry repeat deleted
 *
 * Revision 1.64  2004/07/29 23:52:08  maulani
 * -refine html to validate
 *
 * Revision 1.63  2004/07/28 13:15:49  maulani
 * - Fixed bug 999352 where new activity was always created as completed.
 *   Multiple variables were passed incorrectly on creation.
 *
 * Revision 1.62  2004/07/26 03:59:23  braverock
 * - sort contact list in Activities menu by last name
 *   - implements SF feature request 925618 submitted by gpowers
 *
 * Revision 1.61  2004/07/25 12:43:25  braverock
 * - remove lang file require_once, as it is no longer used
 *
 * Revision 1.60  2004/07/22 14:50:38  cpsource
 * - Allow for possibility that company won't be found.
 *
 * Revision 1.59  2004/07/21 21:04:52  neildogg
 * - Added get_formatted_phone
 *
 * Revision 1.58  2004/07/21 18:52:09  introspectshun
 * - Fixed broken </td>
 *
 * Revision 1.57  2004/07/20 14:02:39  cpsource
 * - Beagle bites sqirrel - got rid of getGlobalVars and
 *     upgraded to arr_vars sub-system.
 *   Fixed bug whereby companies/one.php couldn't create
 *     activities.
 *
 * Revision 1.56  2004/07/17 13:15:03  braverock
 * - localize all strings for i18n/translation
 * - add db_error_handler on all queries
 * - fixed email URL link bug reported by twistymcgee
 *
 * Revision 1.55  2004/07/15 17:39:13  cpsource
 * - Fix undefines: former_name_rows, relationship_rows, activity_rows,
 *   sidebar_rows
 *
 * Revision 1.54  2004/07/15 17:29:08  cpsource
 * - Fix $contact_id undefines
 *
 * Revision 1.53  2004/07/15 13:05:08  cpsource
 * - Add arr_vars sub-system for passing variables between code streams.
 *
 * Revision 1.52  2004/07/14 23:19:47  neildogg
 * - Mistyped
 *
 * Revision 1.51  2004/07/14 22:12:43  neildogg
 * - Now uses $overall_id
 *
 * Revision 1.50  2004/07/10 12:40:07  braverock
 * - initialize $contact_rows
 *   - applies SF patch 977476 submitted by cpsource
 *
 * Revision 1.49  2004/07/09 15:41:14  neildogg
 * - Uses the new, generic relationship sidebar
 *
 * Revision 1.48  2004/07/08 19:42:32  neildogg
 * Added Contacts count
 *
 * Revision 1.47  2004/07/01 19:48:10  braverock
 * - add new configurable relationships code
 *   - adapted from patches submitted by Neil Roberts
 *
 * Revision 1.46  2004/06/28 20:04:21  maulani
 * - Add plug-in hook similar to hook on opportunities page
 *
 * Revision 1.45  2004/06/16 20:41:07  gpowers
 * - removed $this from session_check()
 *   - it is incompatible with PHP5
 *
 * Revision 1.44  2004/06/12 05:03:16  introspectshun
 * - Now use ADODB GetInsertSQL, GetUpdateSQL, date and Concat functions.
 * - Corrected order of arguments to implode() function.
 *
 * Revision 1.43  2004/06/10 18:54:14  braverock
 * - fixed typo in hook call and added parameter to pass in the string
 *
 * Revision 1.42  2004/06/10 13:23:22  braverock
 * - added company_sidebar_bottom hook
 *
 * Revision 1.41  2004/06/04 16:54:38  gpowers
 * Applied Patch [ 965012 ] Calendar replacement By: miguel Gonçves - mig77
 * w/minor changes: changed includes to function, used complete php tags
 *
 * Revision 1.40  2004/06/04 14:30:13  braverock
 * - add contact_id field to mailto link
 *
 * Revision 1.39  2004/06/04 13:46:00  braverock
 * - update email link to improve activity tracking
 *
 * Revision 1.38  2004/05/28 14:00:56  gpowers
 * removed "viewed" audit log entry. this is redundant, as this data is
 * already stored in httpd access logs.
 *
 * Revision 1.37  2004/05/27 18:44:13  gpowers
 * Added a link to other companies in relationships
 *
 * Revision 1.36  2004/05/21 13:06:10  maulani
 * - Create get_formatted_address function which centralizes the address
 *   formatting code into one routine in utils-misc.
 *
 * Revision 1.35  2004/05/10 13:09:14  maulani
 * - add level to audit trail
 *
 * Revision 1.34  2004/05/06 13:55:49  braverock
 * -add industry search to Companies
 *  - modified form of SF patch 949147 submitted by frenchman
 *
 * Revision 1.33  2004/05/04 16:41:35  gpowers
 * Removed duplicate "Relationship." Oops.
 *
 * Revision 1.32  2004/05/04 16:19:23  gpowers
 * Enabled the display of relationships
 *
 * Revision 1.31  2004/05/04 15:41:25  gpowers
 * Removed "Types" ($company_type_list) which was undefined and depreciated.
 * It will be replaced with "Relationships" and/or "Industry".
 *
 * Revision 1.30  2004/04/27 13:20:29  gpowers
 * added support for activity times.
 * start and end time default to current time.
 *
 * Revision 1.29  2004/04/26 13:32:28  braverock
 * break \n's into <br> tags in profile
 *
 * Revision 1.28  2004/04/19 22:19:54  maulani
 * - Adjust table for CSS2 positioning
 *
 * Revision 1.27  2004/04/19 14:10:45  braverock
 * - sort list by last name and display lastname, firstname
 *  - apply SF patch 926962 submitted by Glenn Powers
 *
 * Revision 1.26  2004/04/17 16:02:41  maulani
 * - Add CSS2 positioning
 *
 * Revision 1.25  2004/04/16 22:19:38  maulani
 * - Add CSS2 positioning
 *
 * Revision 1.24  2004/04/10 16:12:29  braverock
 * - add calendar pop-up to date entry in new activity
 *   - apply SF patch 927141 submitted by "s-t"
 *
 * Revision 1.23  2004/04/08 17:00:59  maulani
 * - Update javascript declaration
 *
 * Revision 1.22  2004/04/07 13:50:54  maulani
 * - Set CSS2 positioning for the home page
 *
 * Revision 1.21  2004/03/22 02:45:15  braverock
 * - added result set check around contact list lines 311-314 (formerly line 308)
 *   addresses multiple SF bugs when no contacts exist for a company
 *
 * Revision 1.20  2004/03/22 02:42:54  braverock
 * - add http:// in front of url's that don't have them
 *   - fixes SF bug 906413
 *
 * Revision 1.19  2004/03/07 14:07:31  braverock
 * - use centralized side-bar code in advance of i18n conversion
 *
 * Revision 1.18  2004/02/10 20:56:47  maulani
 * Add company former name and relationship tracking
 *
 * Revision 1.17  2004/02/06 22:47:37  maulani
 * Use ends_at to determine if activity is overdue
 *
 * Revision 1.16  2004/01/26 19:18:02  braverock
 * - added company division pages and fields
 * - added phpdoc
 */
?>
