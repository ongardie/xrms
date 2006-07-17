<?php
/**
 * Details about One Company
 *
 * Usually called from companies/some.php, but also linked to from many
 * other places in the XRMS UI.
 *
 * $Id: one.php,v 1.143 2006/07/17 06:25:24 vanmer Exp $
 *
 * @todo create a centralized left-pane handler for activities (in companies, contacts,cases, opportunities, campaigns)
 */

//include required files
require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-companies.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');
require_once($include_directory . 'utils-accounting.php');
require_once($include_directory . 'classes/Pager/Pager_Columns.php');
require_once($include_directory . 'classes/Pager/GUP_Pager.php');
require_once('../activities/activities-widget.php');

$company_id = $_GET['company_id'];
if (isset($_GET['division_id'])) {
    $division_id = $_GET['division_id'];
} else $division_id=false;

$con = get_xrms_dbconnection();

if ($division_id AND !$company_id) {
    $company_id=fetch_company_id_for_division($con, $division_id);
}

if (!$company_id) { echo _("Failed to provide a company identifier, failing"); exit; }

$on_what_id=$company_id;

$session_user_id = session_check();

$msg = isset($_GET['msg']) ? $_GET['msg'] : '';

//$con->debug = 1;

$activities_form_name = 'Company_Activities';
$contacts_form_name = 'Company_Contacts';


// make sure $accounting_rows is defined
if ( !isset($accounting_rows) ) {
  $accounting_rows = '';
}
//call the accounting hook
$accounting_rows = do_hook_function('company_accounting_inline_display', $accounting_rows);

update_recent_items($con, $session_user_id, "companies", $company_id);

$rst = get_company($con, $company_id, $return_rst=true);


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
    $employees = $rst->fields['employees'];
    $revenue = $rst->fields['revenue'];
    if ((substr($url, 0, 4)!=='http') and (strlen(trim($url)) >0)) {
        $url = 'http://'.$url;
    }
    if ( $rst->fields['account_status_short_name'] != 'N/A' ) {
        $account_status = $rst->fields['account_status_display_html'];
    }
    if ($rst->fields['credit_limit'] > 0 ) {
        $credit_limit = number_format($rst->fields['credit_limit'], 2);
        $current_credit_limit = fetch_current_customer_credit_limit($extref1);
    } else {
        $credit_limit = '';
    }
    if ( $rst->fields['rating_short_name'] != 'N/A' ) {
        $rating = $rst->fields['rating_display_html'];
    }
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



if (strlen($url) > 0) {
    $url = "<a target='_blank' href='" . $url . "'>$url</a>";
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

    $division_select.=$division_rst->getmenu2('division_id',$division_id, true, false, 1, "id=division_id onchange=\"javascript:restrictByDivision();\"");
    if ($division_id) {
        $div_return_url=urlencode("one.php?company_id=$company_id&division_id=$division_id");
        $division_select.="&nbsp; <input class=button type=button value=\"". _("Administer Division")."\" onclick=\"javascript:location.href='".$http_site_root."/companies/edit-division.php?company_id=$company_id&division_id=$division_id&return_url=$div_return_url';\">";
    }
} else { $division_select=false; }

if (get_system_parameter($con, 'Display Item Technical Details') == 'y') {
    $history_text = '<tr> <td class=widget_content colspan=2>';
    $history_text .= _("Company ID:") . '  ' . $company_id ;
    $history_text .= '</td> </tr>';
} else {
$history_text = '';
}

// New Activities Widget
$return_url = "/companies/one.php?company_id=$company_id" .  ($division_id ? "&division_id=" . $division_id : '');

if ($division_id) {
    $on_what_table='company_division';
    $on_what_id=$division_id;
}

$new_activity_widget = GetNewActivityWidget($con, $session_user_id, $return_url, $on_what_table, $on_what_id, $company_id, $contact_id);


// Activities Widget

// Pass search terms to GetActivitiesWidget
$search_terms = array( 'company_id'            => $company_id,
                       'division_id'           => $division_id);


$extra_where ="";

if ($division_id) {
    $extra_where .=" AND (a.on_what_table='company_division' AND a.on_what_id=$division_id";
    $extra_where .=" OR a.on_what_table='opportunities' AND o.division_id=$division_id";
    $extra_where .=" OR a.on_what_table='cases' AND cas.division_id=$division_id)";

}
$default_columns = array('title', 'owner', 'type', 'contact', 'activity_about', 'scheduled', 'due');

$activities_widget =  GetActivitiesWidget($con, $search_terms, $activities_form_name, _('Activities'), $session_user_id, $return_url, $extra_where, null, $default_columns);


// contacts query
$sql = "select " .
        $con->Concat($con->qstr('<a id="'), 'last_name', $con->qstr(' '), 'first_names', $con->qstr('" href="' . $http_site_root . '/contacts/one.php?contact_id='), "contact_id", $con->qstr('&amp;return_url=/companies/one.php%3Fcompany_id=' . $company_id . '">'), "last_name", $con->qstr(', '), "first_names", $con->qstr('</a>')) . " AS name, summary, title, description, email, contact_id, first_names, last_name, address_id, work_phone, work_phone_ext
from contacts where company_id = $company_id and contact_record_status = 'a'";


if ($division_id) {
    $sql .=" AND division_id=$division_id";
}

//echo htmlentities($sql);

// begin Contacts Pager
$columns = array();
$columns[] = array('name' => _('Name'), 'index_sql' => 'name', 'sql_sort_column' => 'last_name,first_names');
$columns[] = array('name' => _('Summary'), 'index_sql' => 'summary');
$columns[] = array('name' => _('Title'), 'index_sql' => 'title');
$columns[] = array('name' => _('Description'), 'index_sql' => 'description');
$columns[] = array('name' => _('Phone'), 'index_calc' => 'work_phone');
$columns[] = array('name' => _('Extension'), 'index_calc' => 'work_phone_ext');
$columns[] = array('name' => _('E-Mail'), 'index_calc' => 'email', 'sql_sort_column' => 'email');

// no reason to set this if you don't want all by default
$default_columns = null;
$default_columns = array('name','summary','title','description','work_phone','email');


// selects the columns this user is interested in
$pager_columns = new Pager_Columns('ContactsPager', $columns, $default_columns, $contacts_form_name);
$pager_columns_button = $pager_columns->GetSelectableColumnsButton();
$contacts_pager_columns_selects = $pager_columns->GetSelectableColumnsWidget();

$columns = $pager_columns->GetUserColumns('default');

$new_contact_location="../contacts/new.php?company_id=$company_id";
if ($division_id) $new_contact_location.= "&division_id=$division_id";

    // this is the callback function that the pager uses to fill in the calculated data.
    function getContactDetails($row) {
        global $con;
        global $session_user_id;
        global $company_id;
        global $address_id;

        // this is for the CTI dialing bit
        global $contact_id;
        $contact_id = $row['contact_id'];

        $row['work_phone'] = get_formatted_phone($con, $address_id, $row['work_phone']);
        $row['email'] = "<a href='mailto:{$row['email']}' onclick=\"location.href='../activities/new-2.php?user_id=$session_user_id&activity_type_id=3&company_id=$company_id&contact_id={$row['contact_id']}&email=true&return_url=/companies/one.php%3Fcompany_id=$company_id&activity_title=email%20to%20{$row['first_names']}%20{$row['last_name']}'\" >" . htmlspecialchars($row['email']) . '</a>';

        return $row;
    }


$pager = new GUP_Pager($con, $sql, 'getContactDetails', _('Contacts'), $contacts_form_name, 'ContactsPager', $columns, false, true);
$contacts_export_button=$pager->GetAndUseExportButton();
$endrows = "<tr><td class=widget_content_form_element colspan=10>
            $pager_columns_button $contacts_export_button
            <input class=button type=button value=\"" .  _('Mail Merge') . "\" onclick=\"javascript: location.href='../email/email.php?scope=company&company_id=$company_id'\">" .
            render_create_button("New",'button',"location.href='$new_contact_location';") .  "</td></tr>";

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

if ($division_id) { $relationships['company_division']=$division_id; }

require_once("../relationships/sidebar.php");

if ($division_id) {
    $on_what_table='company_division';
    $on_what_id=$division_id;
}
// include the files sidebar
require_once("../files/sidebar.php");

$on_what_table = 'companies';
$on_what_id = $company_id;

// include the notes sidebar
require_once("../notes/sidebar.php");

// make sure $sidebar_rows_* are defined
if ( !isset($sidebar_rows_top) ) {
  $sidebar_rows_top = '';
}
if ( !isset($sidebar_rows_bottom) ) {
  $sidebar_rows_bottom = '';
}

//call the sidebar hooks
$sidebar_rows_top = do_hook_function('company_sidebar_top', $sidebar_rows_top);
$sidebar_rows_bottom = do_hook_function('company_sidebar_bottom', $sidebar_rows_bottom);
if ($division_id) {
    // add division sidebars
    $sidebar_rows_bottom_extra='';
    $sidebar_rows_bottom = do_hook_function('division_sidebar_bottom', $sidebar_rows_bottom_extra);
    $sidebar_rows_bottom .=$sidebar_rows_bottom_extra;
}

// make sure $bottom_rows is defined
if ( !isset($bottom_rows) ) {
  $bottom_rows = '';
}
//call the sidebar hook
$bottom_rows = do_hook_function('company_content_bottom', $bottom_rows);
if ($division_id) {
    // add division sidebars
    $bottom_rows .= do_hook_function('company_division_bottom', $bottom_rows);
}


// make sure $activity_rows is defined
if ( !isset($activity_rows) ) {
  $activity_rows = '';
}
//call the hook
do_hook_function('company_activities', $activity_rows);



// make sure $company_buttons is defined
if ( !isset($company_buttons) ) {
  $company_button = '';
}
//call the copmany_buttons hook
$company_buttons = do_hook_function('company_buttons', $company_buttons);

/** End of the sidebar includes **/
/*********************************/

add_audit_item($con, $session_user_id, 'viewed', 'companies', $company_id, 3);

//close the database connection, we don't need it anymore
//$con->close();

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
    document.forms[1].activity_status.value = "c";
    document.forms[1].submit();
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
                                    <td class=clear><?php echo $company_name; ?></td>
                                </tr>
                                <?php if ($legal_name) { ?>
                                <tr>
                                    <td width="1%" class=sublabel><?php echo _("Legal Name"); ?></td>
                                    <td class=clear><?php echo $legal_name; ?></td>
                                </tr>
                                <?php }; ?>
                                <?php echo $former_name_rows; ?>
                                <?php if ($company_code) { ?>
                                <tr>
                                    <td class=sublabel><?php echo _("Code"); ?></td>
                                    <td class=clear><?php echo $company_code; ?></td>
                                </tr>
                                <?php }; ?>
                                <?php if ($industry_pretty_name) { ?>
                                <tr>
                                    <td class=sublabel><?php echo _("Industry"); ?></td>
                                    <td class=clear><?php echo $industry_pretty_name; ?></td>
                                </tr>
                                <?php }; ?>
                                <?php if ($crm_status_pretty_name) { ?>
                                <tr>
                                    <td class=sublabel><?php echo _("CRM Status"); ?></td>
                                    <td class=clear><?php echo $crm_status_pretty_name; ?></td>
                                </tr>
                                <?php }; ?>
                                <?php if ($owner_username) { ?>
                                <tr>
                                    <td class=sublabel><?php echo _("Account Owner"); ?></td>
                                    <td class=clear><?php echo $owner_username; ?></td>
                                </tr>
                                <?php }; ?>
                                <?php if ($phone) { ?>
                                <tr>
                                    <td class=sublabel><?php echo _("Phone"); ?></td>
                                    <td class=clear><?php echo $phone; ?></td>
                                </tr>
                                <?php }; ?>
                                <?php if (trim($phone2)) { ?>
                                <tr>
                                    <td class=sublabel><?php echo _("Alt. Phone"); ?></td>
                                    <td class=clear><?php echo $phone2; ?></td>
                                </tr>
                                <?php }; ?>
                                <?php if ($fax) { ?>
                                <tr>
                                    <td class=sublabel><?php echo _("Fax"); ?></td>
                                    <td class=clear><?php echo $fax; ?></td>
                                </tr>
                                <?php }; ?>
                                <?php if (trim($url)) { ?>
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
                                    <td class=clear><?php echo $division_select; ?></td>
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
                                <?php if (trim($tax_id)) { ?>
                                <tr>
                                    <td class=sublabel><?php echo _("Tax ID"); ?></td>
                                    <td class=clear><?php echo $tax_id; ?></td>
                                </tr>
                                <?php }; ?>
                                <?php if (trim($credit_limit) OR trim($current_credit_limit)) { ?>
                                <tr>
                                    <td class=sublabel><?php echo _("Credit Limit"); ?></td>
                                    <td class=clear>$<?php echo $credit_limit.' '.$current_credit_limit; ?></td>
                                </tr>
                                <?php }; ?>
                                <?php if ($rating) { ?>
                                <tr>
                                    <td class=sublabel><?php echo _("Rating"); ?></td>
                                    <td class=clear><?php echo $rating; ?></td>
                                </tr>
                                <?php }; ?>
                                <?php if (trim($terms)) { ?>
                                <tr>
                                    <td class=sublabel><?php echo _("Terms"); ?></td>
                                    <td class=clear><?php echo $terms.' '. _("days"); ?></td>
                                </tr>
                                <tr>
                                    <td class=sublabel>&nbsp;</td>
                                    <td class=clear>&nbsp;</td>
                                </tr>
                                <?php }; ?>
                                <!-- accounting plugin -->
                                <?php echo $accounting_rows; ?>
                                <?php if ($company_source) { ?>
                                <tr>
                                    <td width=1% class=sublabel><?php echo _("Company Source"); ?></td>
                                    <td class=clear><?php echo $company_source; ?></td>
                                </tr>
                                <?php }; ?>
                                <?php if (trim($employees)) { ?>
                                <tr>
                                    <td class=sublabel><?php echo _("Employees"); ?></td>
                                    <td class=clear><?php echo $employees; ?></td>
                                </tr>
                                <?php }; ?>
                                <?php if (trim($revenue)) { ?>
                                <tr>
                                    <td class=sublabel><?php echo _("Revenue"); ?></td>
                                    <td class=clear><?php echo $revenue; ?></td>
                                </tr>
                                <?php }; ?>
                                <tr>
                                    <td class=sublabel>&nbsp;</td>
                                    <td class=clear>&nbsp;</td>
                                </tr>
                                <?php if (trim($custom1) AND $company_custom1_label!='(Custom 1)') { ?>
                                <tr>
                                    <td width=1% class=sublabel><?php echo _($company_custom1_label); ?></td>
                                    <td class=clear><?php echo $custom1; ?></td>
                                </tr>
                                <?php }; ?>
                                <?php if (trim($custom2) AND $company_custom2_label!='(Custom 2)') { ?>
                                <tr>
                                    <td class=sublabel><?php echo _($company_custom2_label); ?></td>
                                    <td class=clear><?php echo $custom2; ?></td>
                                </tr>
                                <?php }; ?>
                                <?php if (trim($custom3) AND $company_custom3_label!='(Custom 3)') { ?>
                                <tr>
                                    <td class=sublabel><?php echo _($company_custom3_label); ?></td>
                                    <td class=clear><?php echo $custom3; ?></td>
                                </tr>
                                <?php }; ?>
                                <?php if (trim($custom4) AND $company_custom4_label!='(Custom 4)') { ?>
                                <tr>
                                    <td class=sublabel><?php echo _($company_custom4_label); ?></td>
                                    <td class=clear><?php echo $custom4; ?></td>
                                </tr>
                                <?php }; ?>
                                <tr>
                                    <td class=sublabel>&nbsp;</td>
                                    <td class=clear>&nbsp;</td>
                                </tr>
                                    <?php echo $relationship_rows; ?>
                                <tr>
                                    <td class=sublabel><?php echo _("Created"); ?></td>
                                    <td class=clear><?php echo $entered_at .' '. _("by") .' '. $entered_by; ?></td>
                                </tr>
                                <tr>
                                    <td class=sublabel><?php echo _("Last Modified"); ?></td>
                                    <td class=clear><?php echo $last_modified_at .' '. _("by") .' '. $last_modified_by; ?></td>
                                </tr>
                            </table>

                            </td>
                        </tr>
                    </table>
                    <?php if ( strlen(trim($profile)) > 0 ) { ?>
                        <p id="profile" class="hidden"><?php if(strlen($profile) >= 500) { echo substr($profile, 0, 500); ?><span><?php echo substr($profile, 500); ?></span><a href="#" onclick="document.getElementById('profile').className = (document.getElementById('profile').className == '') ? 'hidden' : ''; return false">...</a><?php } else { echo $profile; } ?></p>
                    <?php } ?>
                </td>
            </tr>
            <tr>
                <td class=widget_content_form_element>
                <?php echo render_edit_button("Edit", 'button', "javascript: location.href='edit.php?company_id=$company_id';"); ?>
                <input class=button type=button value="<?php echo _("Clone"); ?>" onclick="javascript: location.href='new.php?clone_id=<?php echo $company_id ?>';">
                <input class=button type=button value="<?php echo _("Addresses"); ?>" onclick="javascript: location.href='addresses.php?company_id=<?php echo $company_id; ?>';">
                <?php
                    if (!$division_id) {
                        //only show the Division button if we are not already scoped by Division
                ?>
                <input class=button type=button value="<?php echo _("Divisions"); ?>" onclick="javascript: location.href='divisions.php?company_id=<?php echo $company_id; ?>';">
                <?php } //end Division button check ?>
                <?php echo $company_buttons; ?>
                </td>
            </tr>
            <?php  echo $history_text; ?>
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
    do_hook_function ('company_detail', $con);

?>

        <?php echo $new_activity_widget; ?>

        <!-- activities list //-->
        <form name="<?php echo $activities_form_name; ?>" method=post>
            <?php
                // activities pager
                echo $activities_widget['content'];
                echo $activities_widget['sidebar'];
                echo $activities_widget['js'];
            ?>
            <?php echo $activity_rows; ?>
        </form>


        <!-- company content bottom plugins //-->
        <?php echo $bottom_rows; ?>

    </div>

        <!-- right column //-->
    <div id="Sidebar">

        <!-- sidebar plugins - top //-->
        <?php echo $sidebar_rows_top; ?>

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

        <!-- sidebar plugins - bottom //-->
        <?php echo $sidebar_rows_bottom; ?>

    </div>

</div>

<script>
function bulkEmail() {
    document.forms[0].action = "../email/email.php";
    document.forms[0].submit();
}

</script>

<?php

end_page();

/**
 * $Log: one.php,v $
 * Revision 1.143  2006/07/17 06:25:24  vanmer
 * *** empty log message ***
 *
 * Revision 1.142  2006/07/14 03:51:50  vanmer
 * - added hook for output within the companies activities form
 *
 * Revision 1.141  2006/05/27 19:48:58  ongardie
 * - if( ! $var = 'value') will always evaluate to false when 'value' can be stored in $var.
 *
 * Revision 1.140  2006/05/01 17:22:26  braverock
 * - remove old embedded SQL comment
 *
 * Revision 1.139  2006/05/01 17:20:36  braverock
 * - update to use get_company API function
 *
 * Revision 1.138  2006/04/28 23:31:51  jnhayart
 * fix display Work_phone with get_formatted_phone
 *
 * Revision 1.137  2006/04/26 20:03:53  braverock
 * - do a better job of not showing empty fields in Company Details
 * - remove 'Admin' button, fields moved to edit.php
 *
 * Revision 1.136  2006/04/21 00:00:40  vanmer
 * - ensure that plugin output for division bottom works the same for company and division pages
 *
 * Revision 1.135  2006/04/17 19:26:55  vanmer
 * - added export button to contacts pager on single company page
 *
 * Revision 1.134  2006/03/21 02:41:39  ongardie
 * - Added plugin hook for top of sidebar.
 *
 * Revision 1.133  2006/02/21 14:49:48  braverock
 * - add division hooks for display of division information when division is selected
 *
 * Revision 1.132  2006/01/20 19:05:45  braverock
 * - filter files sidebar by division_id if division_id is set
 *
 * Revision 1.131  2005/12/12 21:54:57  vanmer
 * - added extension to list of available fields in contact summary pager for single company view
 * - changed default columns to reflect work phone number being displayed in pager of contacts
 *
 * Revision 1.130  2005/08/28 15:51:20  braverock
 * - add missing td tag for compliant HTML
 *
 * Revision 1.129  2005/08/28 15:47:13  braverock
 * - quote onchange property
 *
 * Revision 1.128  2005/08/28 15:25:43  braverock
 * - change _new to _blank for broader browser compatibility
 *
 * Revision 1.127  2005/08/19 19:27:09  braverock
 * - take out insecure use of 'global'
 *
 * Revision 1.126  2005/08/13 22:57:00  vanmer
 * - altered to hide custom company fields unless their labels have been changed in vars.php
 *
 * Revision 1.125  2005/08/04 19:32:09  vanmer
 * - changed administer division button to redirect to companies/one after editing or managing division page
 *
 * Revision 1.124  2005/07/24 20:39:10  maulani
 * - Add display of item id for development troubleshooting in production
 *
 * Revision 1.123  2005/07/19 16:11:49  braverock
 * - properly trim URL before displaying an empty URL
 *
 * Revision 1.122  2005/07/07 20:19:29  braverock
 * - remove obsolete menu queries replaced by new activities-widget code
 *
 * Revision 1.121  2005/07/07 18:55:45  daturaarutad
 * add division_id to search_terms for GetActivitiesWidget
 *
 * Revision 1.120  2005/07/07 18:51:50  vanmer
 * - added lookup of company_id if division_id is not specified
 * - moved database connection to occur in time to make this lookup
 * - added error message is company_id is not provided
 *
 * Revision 1.119  2005/07/07 16:34:54  daturaarutad
 * removed Calendar.setup code (it moved to activities-widget.php)
 *
 * Revision 1.118  2005/07/07 03:58:03  daturaarutad
 * updated to use activities-widget for New Activity widget
 *
 * Revision 1.117  2005/06/29 20:58:56  daturaarutad
 * add default column "due" to activities widget
 *
 * Revision 1.116  2005/06/29 17:14:57  daturaarutad
 * change username->owner in activities widget
 *
 * Revision 1.115  2005/06/28 22:19:07  daturaarutad
 * updated to use consolidated GetActivitiesWidget function
 *
 * Revision 1.114  2005/06/21 12:37:56  braverock
 * - fix activities 'Done' button
 *   patch provided by Jean-Noël Hayart
 *
 * Revision 1.113  2005/06/06 18:39:25  vanmer
 * - changed to only show division relationships when scoped to division
 *
 * Revision 1.112  2005/06/03 22:57:17  braverock
 * - revert previous on_what_table change, as it caused ACL problems
 *   correction for related activities pager made in activities/one.php instead
 *
 * Revision 1.111  2005/06/01 21:39:38  braverock
 * - fix reset of on_what_table so activities are not erroneously linked to each other
 * - add division as a ligit option in relationships sidebar
 *
 * Revision 1.110  2005/05/10 23:44:59  vanmer
 * - added needed parenthesis around trim statements
 *
 * Revision 1.109  2005/05/10 13:44:22  braverock
 * - localized string patches provided by Alan Baghumian (alanbach)
 * - added trim for optional not always used fields
 *
 * Revision 1.108  2005/05/04 14:27:30  braverock
 * - change Activity 'Title' to 'Summary' for consistency
 *
 * Revision 1.107  2005/05/04 13:32:32  braverock
 * - remove spurious 'About' column from new Activity row
 * - change Start to 'Scheduled' for consistenct of activity start time labels
 *
 * Revision 1.106  2005/04/19 21:14:58  neildogg
 * - Profile bug if short
 *
 * Revision 1.105  2005/04/19 21:07:15  neildogg
 * - Company profile shrunken by default. Can be enlarged
 *
 * Revision 1.104  2005/04/07 14:21:43  maulani
 * - Replace username with actual name in display
 *   RFE 933629 by sdavey
 *
 * Revision 1.103  2005/04/05 16:53:30  ycreddy
 * Added trim to custom fields
 *
 * Revision 1.102  2005/04/04 14:54:05  gpowers
 * - moved company_buttons plugin hook to sidebar hook area
 *   - $company_name needs to be defined for the weblinks plugin
 *
 * Revision 1.101  2005/03/22 21:51:07  gpowers
 * - moved up company_buttons hook
 *   - it's now called before the db connection is closed
 *   - now it's in the same area as the company_accounting hook
 *
 * Revision 1.100  2005/03/18 20:53:29  gpowers
 * - added hooks for inline info plugin
 *
 * Revision 1.99  2005/03/15 22:46:26  daturaarutad
 * pager tuning sql_sort_column
 *
 * Revision 1.98  2005/03/15 21:18:37  daturaarutad
 * fixed mail merge for contacts and activities
 *
 * Revision 1.97  2005/03/07 17:15:32  daturaarutad
 * updated to speed up sql sorts in the pagers using sql_sort_column
 *
 * Revision 1.96  2005/03/07 12:14:44  maulani
 * - pass the db connection to the plugin
 *
 * Revision 1.95  2005/03/05 00:58:02  daturaarutad
 * set default sort column to scheduled for activities pager
 *
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
 * Applied Patch [ 965012 ] Calendar replacement By: miguel GonÃ§ves - mig77
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
