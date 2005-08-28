<?php
/**
 * Details about One Company
 *
 * Usually called from companies/some.php, but also linked to from many
 * other places in the XRMS UI.
 *
 * $Id: companies_one.php,v 1.3 2005/08/28 15:28:02 braverock Exp $
 *
 * @todo create a categories sidebar and centralize the category handling
 * @todo create a centralized left-pane handler for activities (in companies, contacts,cases, opportunities, campaigns)
 */

//include required files
require_once('../../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');
require_once($include_directory . 'utils-accounting.php');

$session_user_id = session_check();

$msg = isset($_GET['msg']) ? $_GET['msg'] : '';

$company_id = $_GET['company_id'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
// $con->debug = 1;

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
    $profile = str_replace ("\n","<br>\n",$profile);
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

$address_to_display = get_formatted_address($con, $address_id);

if (strlen($url) > 0) {
    $url = "<a target='_blank' href='" . $url . "'>$url</a>";
}

//
//  list of most recent activities
//
$sql_activities = "
SELECT a.activity_id, a.activity_title, a.scheduled_at, a.on_what_table, a.on_what_id,
  a.entered_at, a.activity_status, at.activity_type_pretty_name,
  cont.first_names AS contact_first_names, cont.last_name AS contact_last_name, u.username,
  (CASE WHEN ((a.activity_status = 'o') AND (a.scheduled_at < " . $con->SQLDate('Y-m-d') . ")) THEN 1 ELSE 0 END) AS is_overdue
FROM activity_types at, users u, activities a
LEFT JOIN contacts cont ON cont.contact_id = a.contact_id
WHERE a.company_id = $company_id
  AND a.user_id = u.user_id
  AND a.activity_type_id = at.activity_type_id
  AND a.activity_record_status = 'a'
ORDER BY is_overdue DESC, a.scheduled_at DESC, a.entered_at DESC
";

$rst = $con->selectlimit($sql_activities, $display_how_many_activities_on_company_page);

$activity_rows = '';
if ($rst) {
    while (!$rst->EOF) {

        $open_p = $rst->fields['activity_status'];
        $scheduled_at = $rst->unixtimestamp($rst->fields['scheduled_at']);
        $is_overdue = $rst->fields['is_overdue'];
        $on_what_table = $rst->fields['on_what_table'];
        $on_what_id = $rst->fields['on_what_id'];

        if ($open_p == 'o') {
            if ($is_overdue) {
                $classname = 'overdue_activity';
            } else {
                $classname = 'open_activity';
            }
        } else {
            $classname = 'closed_activity';
        }

        if ($on_what_table == 'opportunities') {
            $attached_to_link = "<a href='$http_site_root/opportunities/one.php?opportunity_id=$on_what_id'>";
            $sql2 = "select opportunity_title as attached_to_name from opportunities where opportunity_id = $on_what_id";
        } elseif ($on_what_table == 'cases') {
            $attached_to_link = "<a href='$http_site_root/cases/one.php?case_id=$on_what_id'>";
            $sql2 = "select case_title as attached_to_name from cases where case_id = $on_what_id";
        } else {
            $attached_to_link = "N/A";
            $sql2 = "select * from companies where 1 = 2";
        }

        $rst2 = $con->execute($sql2);

        if ($rst) {
            $attached_to_name = $rst2->fields['attached_to_name'];
            $attached_to_link .= $attached_to_name . "</a>";
            $rst2->close();
        }

        $activity_rows .= '<tr>';
        $activity_rows .= "<td class='$classname'><a href='$http_site_root/activities/one.php?return_url=/companies/one.php?company_id=$company_id&activity_id=" . $rst->fields['activity_id'] . "'>" . $rst->fields['activity_title'] . '</a></td>';
        $activity_rows .= '<td class=' . $classname . '>' . $rst->fields['username'] . '</td>';
        $activity_rows .= '<td class=' . $classname . '>' . $rst->fields['activity_type_pretty_name'] . '</td>';
        $activity_rows .= '<td class=' . $classname . '>' . $rst->fields['contact_first_names'] . ' ' . $rst->fields['contact_last_name'] . '</td>';
        $activity_rows .= '<td class=' . $classname . ">$attached_to_link</td>";
        $activity_rows .= '<td class=' . $classname . '>' . $con->userdate($rst->fields['scheduled_at']) . '</td>';
        $activity_rows .= '</tr>';
        $rst->movenext();
    }
    $rst->close();
} else {
    db_error_handler ($con, $sql_activities);
}

// contacts

$sql = "select * from contacts where company_id = $company_id
        and contact_record_status = 'a'
        order by last_name";

$rst = $con->execute($sql);

$contact_rows = '';
if ($rst) {
    $num_contacts = $rst->rowcount();
    while (!$rst->EOF) {
        $contact_id = $rst->fields['contact_id'];
        $contact_rows .= "\n<tr>";
        $contact_rows .= "<td class=widget_content><a href='../phone/contacts_one.php?contact_id="
                        . $contact_id . "'>"
                        . $rst->fields['last_name'] . ', ' . $rst->fields['first_names']
                        . '</a></td></tr>';
        //$contact_rows .= '<td class=widget_content>' . $rst->fields['summary'] . '</td>';
        //$contact_rows .= '<td class=widget_content>' . $rst->fields['title'] . '</td>';
        //$contact_rows .= '<td class=widget_content>' . $rst->fields['description'] . '</td>';
        $contact_rows .= '<tr><td class=widget_content>' . get_formatted_phone($con, $rst->fields['address_id'], $rst->fields['work_phone']) . '</td>';
        /*$contact_rows .= "\n\t<td class=widget_content><a href='mailto:"
                        . $rst->fields['email']
                        . "' onclick=\"location.href='../activities/new-2.php?user_id=$session_user_id&activity_type_id=3&contact_id=$contact_id&activity_title=email to "
                        . $rst->fields['first_names']. " " .$rst->fields['last_name']
                        . "&company_id=$company_id&contact_id="
                        . $contact_id
                        ."&email=true&return_url=/companies/one.php?company_id=$company_id'\" >"
                        . htmlspecialchars($rst->fields['email'])
                        . '</a></td>';*/
        $contact_rows .= "\n</tr>";
        $rst->movenext();
    }
    $rst->close();
} else {
    db_error_handler ($con, $sql);
}

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

// related companies

$sql = "select rt.from_what_text, rt.to_what_text, r.established_at,
    r.to_what_id, r.from_what_id,
    c1.company_name as to_company_name, c1.company_id as to_company_id,
    c2.company_name as from_company_name, c2.company_id as from_company_id
    from relationships as r, companies as c1, companies as c2, relationship_types as rt
    where (r.from_what_id = $company_id or r.to_what_id = $company_id)
    and rt.from_what_table = 'companies'
    and rt.to_what_table = 'companies'
    and r.relationship_type_id=rt.relationship_type_id
    and r.to_what_id=c2.company_id
    and r.from_what_id=c1.company_id
    and r.relationship_status = 'a'
    order by r.established_at desc";

$rst = $con->execute($sql);

$relationship_rows = '';
$linecounter = 0;
if ($rst) {
    while (!$rst->EOF) {
        $linecounter +=1;
        if($rst->fields['from_what_id'] == $company_id) {
            $from_or_to = "from";
        }
        else {
            $from_or_to = "to";
        }
        $established_at = $con->userdate($rst->fields['established_at']);
        $relationship_rows .= ($linecounter == '1') ? '<tr><td class=sublabel>Relationship</td>' : '<tr><td class=sublabel>&nbsp;</td>';
        $relationship_rows .= '<td class=clear>' . $rst->fields[$from_or_to . '_what_text'] . ' '
            . '<a href="one.php?company_id=' . $rst->fields[$from_or_to . '_company_id']
            . '">' . $rst->fields[$from_or_to . '_company_name'] . '</a> '
            . $established_at . '</td>';
        $relationship_rows .= '</tr>';
        $rst->movenext();
    }
    $rst->close();
} else {
    db_error_handler ($con, $sql);
}

// associated with

$categories_sql = "select category_display_html
from categories c, category_scopes cs, category_category_scope_map ccsm, entity_category_map ecm
where ecm.on_what_table = 'companies'
and ecm.on_what_id = $company_id
and ecm.category_id = c.category_id
and cs.category_scope_id = ccsm.category_scope_id
and c.category_id = ccsm.category_id
and cs.on_what_table = 'companies'
and category_record_status = 'a'
order by category_display_html";

$rst = $con->execute($categories_sql);
$categories = array();

if ($rst) {
    while (!$rst->EOF) {
        array_push($categories, $rst->fields['category_display_html']);
        $rst->movenext();
    }
    $rst->close();
} else {
    db_error_handler ($con, $categories_sql);
}

$categories = implode(', ', $categories);

/*********************************/
/*** Include the sidebar boxes ***/

//set up our substitution variables for use in the sidebars
$on_what_table = 'companies';
$on_what_id = $company_id;
$on_what_string = 'company';

//include the Cases sidebar
$case_limit_sql = "and cases.".$on_what_string."_id = $on_what_id";
require_once("../../cases/sidebar.php");

//include the opportunities sidebar
$opportunity_limit_sql = "and opportunities.".$on_what_string."_id = $on_what_id";
require_once("../../opportunities/sidebar.php");

//include the contacts-companies sidebar
$relationship_name = "company link";
$working_direction = "to";
$overall_id = $company_id;
require_once("../../relationships/sidebar.php");

// include the files sidebar
require_once("../../files/sidebar.php");

// include the notes sidebar
require_once("../../notes/sidebar.php");

// make sure $sidebar_rows is defined
if ( !isset($sidebar_rows) ) {
  $sidebar_rows = '';
}
//call the sidebar hook
$sidebar_rows = do_hook_function('company_sidebar_bottom', $sidebar_rows);

/** End of the sidebar includes **/
/*********************************/

$sql = "SELECT " . $con->Concat("first_names", "' '", "last_name") . " AS contact_name, contact_id
        FROM contacts
        WHERE company_id = $company_id
        AND contact_record_status = 'a'
        ORDER BY last_name";

$rst = $con->execute($sql);
if ($rst) {
    $contact_menu = $rst->getmenu2('contact_id', '', true);
    $rst->close();
} else {
    db_error_handler ($con, $sql);
}

$user_menu = get_user_menu($con, $session_user_id);

$sql = "select activity_type_pretty_name, activity_type_id from activity_types where activity_type_record_status = 'a' order by activity_type_pretty_name";
$rst = $con->execute($sql);
if ($rst) {
    $activity_type_menu = $rst->getmenu2('activity_type_id', '', false);
    $rst->close();
} else {
    db_error_handler ($con, $sql);
}

//close the database connection, we don't need it anymore
$con->close();

if (!$activity_rows) {
    $activity_rows = '<tr><td class=widget_content colspan=7>'._("No Activities").'</td></tr>';
}

if (!$contact_rows) {
    $contact_rows = '<tr><td class=widget_content colspan=6>'._("No Contacts").'</td></tr>';
}

if (!$former_name_rows) {
    $former_name_rows = "";
}

if (!$relationship_rows) {
    $relationship_rows = "";
}

if (!$categories) {
    $categories = _("No associated categories");
}

$page_title = _("Company Details") . ' : ' . $company_name;
//start_page($page_title, true, $msg);

?>

<script language="JavaScript" type="text/javascript">
<!--
function markComplete() {
    document.forms[0].activity_status.value = "c";
    document.forms[0].submit();
}

function openNewsWindow() {
    window_url = "http://news.google.com/news?q=%22<?php  echo str_replace(' ', '+', $company_name); ?>%22";
    window_name = "News";
    window_attr = "";
    window.open(window_url, window_name, window_attr);
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

                                <tr>
                                    <td width="1%" class=sublabel><?php echo _("Company Name"); ?></td>
                                </tr>
                                <tr>
                                    <td class=clear><?php  echo $company_name; ?></td>
                                </tr>
                                <!--<tr>
                                    <td width="1%" class=sublabel><?php echo _("Legal Name"); ?></td>
                                </tr>
                                <tr>
                                    <td class=clear><?php  echo $legal_name; ?></td>
                                </tr>-->
                                <!--<?php  echo $former_name_rows; ?>-->

                                <!--<tr>
                                    <td class=sublabel><?php echo _("Code"); ?></td>
                                </tr>
                                <tr>
                                    <td class=clear><?php  echo $company_code; ?></td>
                                </tr>-->
                                <!--<tr>
                                    <td class=sublabel><?php echo _("Industry"); ?></td>
                                </tr>
                                <tr>
                                    <td class=clear><?php  echo $industry_pretty_name; ?></td>
                                </tr>-->
                                <tr>
                                    <td class=sublabel><?php echo _("CRM Status"); ?></td>
                                </tr>
                                <tr>
                                    <td class=clear><?php  echo $crm_status_pretty_name; ?></td>
                                </tr>
                                <!--<tr>
                                    <td class=sublabel><?php echo _("Account Owner"); ?></td>
                                </tr>
                                <tr>
                                    <td class=clear><?php  echo $owner_username; ?></td>
                                </tr>-->
                                <tr>
                                    <td class=sublabel><?php echo _("Phone"); ?></td>
                                </tr>
                                <tr>
                                    <td class=clear><?php  echo $phone; ?></td>
                                </tr>
                                <tr>
                                    <td class=sublabel><?php echo _("Alt. Phone"); ?></td>
                                </tr>
                                <tr>
                                    <td class=clear><?php  echo $phone2; ?></td>
                                </tr>
                                <tr>
                                    <td class=sublabel><?php echo _("Fax"); ?></td>
                                </tr>
                                <tr>
                                    <td class=clear><?php  echo $fax; ?></td>
                                </tr>
                                <tr>
                                    <td class=sublabel><?php echo _("URL"); ?></td>
                                </tr>
                                <tr>
                                    <td class=clear><?php echo $url; ?></td>
                                </tr>
                                <tr>
                                    <td class=sublabel><?php echo _("Address"); ?></td>
                                </tr>
                                <tr>
                                    <td class=clear><?php echo $address_to_display ?></td>
                                </tr>
                                <!--<tr>
                                    <td class=sublabel><?php echo _("Created"); ?></td>
                                </tr>
                                <tr>
                                    <td class=clear><?php  echo $entered_at; ?> by <?php echo $entered_by; ?></td>
                                </tr>
                                <tr>
                                    <td class=sublabel><?php echo _("Last Modified"); ?></td>
                                </tr>
                                <tr>
                                    <td class=clear><?php  echo $last_modified_at; ?> by <?php echo $last_modified_by; ?></td>
                                </tr>-->
                                <!--<tr>
                                    <td class=sublabel><?php echo _("Account Status"); ?></td>
                                </tr>
                                <tr>
                                    <td class=clear><?php echo $account_status; ?></td>
                                </tr>-->
                                <tr>
                                    <td class=sublabel><?php echo _("Tax ID"); ?></td>
                                </tr>
                                <tr>
                                    <td class=clear><?php echo $tax_id; ?></td>
                                </tr>
                                <tr>
                                    <td class=sublabel><?php echo _("Credit Limit"); ?></td>
                                </tr>
                                <tr>
                                    <td class=clear>$<?php echo $credit_limit; ?> <?php echo $current_credit_limit; ?></td>
                                </tr>
                                <!--<tr>
                                    <td class=sublabel><?php echo _("Rating"); ?></td>
                                </tr>
                                <tr>
                                    <td class=clear><?php echo $rating; ?></td>
                                </tr>-->
                                <tr>
                                    <td class=sublabel><?php echo _("Terms"); ?></td>
                                </tr>
                                <tr>
                                    <td class=clear><?php echo $terms; ?> <?php echo _("days"); ?></td>
                                </tr>
                                <!--<tr>
                                    <td width=1% class=sublabel><?php echo _("Company Source"); ?></td>
                                </tr>
                                <tr>
                                    <td class=clear><?php echo $company_source; ?></td>
                                </tr>-->
                                <tr>
                                    <td class=sublabel><?php echo _("Employees"); ?></td>
                                </tr>
                                <tr>
                                    <td class=clear><?php  echo $employees; ?></td>
                                </tr>
                                <tr>
                                    <td class=sublabel><?php echo _("Revenue"); ?></td>
                                </tr>
                                <tr>
                                    <td class=clear><?php echo $revenue; ?></td>
                                </tr>
                                <!--<tr>
                                    <td width=1% class=sublabel><?php echo _($company_custom1_label); ?></td>
                                </tr>
                                <tr>
                                    <td class=clear><?php  echo $custom1; ?></td>
                                </tr>
                                <tr>
                                    <td class=sublabel><?php echo _($company_custom2_label); ?></td>
                                </tr>
                                <tr>
                                    <td class=clear><?php  echo $custom2; ?></td>
                                </tr>
                                <tr>
                                    <td class=sublabel><?php echo _($company_custom3_label); ?></td>
                                </tr>
                                <tr>
                                    <td class=clear><?php  echo $custom3; ?></td>
                                </tr>
                                <tr>
                                    <td class=sublabel><?php echo _($company_custom4_label); ?></td>
                                </tr>
                                <tr>
                                    <td class=clear><?php  echo $custom4; ?></td>
                                </tr>-->
                                    <?php echo $relationship_rows; ?>

                    <!--<p><?php //echo htmlspecialchars($profile); ?>-->

                </td>
            </tr>
            <!--<tr>
                <td class=widget_content_form_element>
                <input class=button type=button value="<?php echo _("Edit"); ?>" onclick="javascript: location.href='edit.php?company_id=<?php echo $company_id; ?>';">
                <input class=button type=button value="<?php echo _("Admin"); ?>" onclick="javascript:location.href='admin.php?company_id=<?php echo $company_id; ?>';">
                <input class=button type=button value="<?php echo _("Clone"); ?>" onclick="javascript: location.href='new.php?clone_id=<?php echo $company_id ?>';">
                <input class=button type=button value="<?php echo _("Mail Merge"); ?>" onclick="javascript: location.href='../email/email.php?scope=company&company_id=<?php echo $company_id; ?>';">
                <input class=button type=button value="<?php echo _("News"); ?>" onclick="javascript: openNewsWindow();">
                <input class=button type=button value="<?php echo _("Relationships"); ?>" onclick="javascript: location.href='relationships.php?company_id=<?php echo $company_id; ?>';">
                <input class=button type=button value="<?php echo _("Addresses"); ?>" onclick="javascript: location.href='addresses.php?company_id=<?php echo $company_id; ?>';">
                <input class=button type=button value="<?php echo _("Divisions"); ?>" onclick="javascript: location.href='divisions.php?company_id=<?php echo $company_id; ?>';">
                </td>
            </tr>-->
        </table>

        <!-- contacts //-->
        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=6><br><br><?php echo $num_contacts; ?> <?php if ($num_contacts === 1) { echo _("Contact"); } else { echo _("Contacts"); } ?></td>
            </tr>
            <tr>
                <td class=widget_label><?php echo _("Name"); ?></td>
            </tr>
            <tr>
                <td class=widget_label><?php echo _("Phone"); ?></td>
            </tr>
            <?php  echo $contact_rows; ?>
            <!--<tr>
                <td class=widget_content_form_element colspan=6><input type=button class=button onclick="location.href='../contacts/new.php?company_id=<?php echo $company_id; ?>';" value="<?php echo _("New"); ?>"></td>
            </tr>-->
        </table>

        <?php //jscalendar_includes(); ?>
<?php
    //place the plug-in hook before the Activities
    do_hook ('company_detail');
?>

        <!-- activities //-->
        <!--<form action="<?php  echo $http_site_root; ?>/activities/new-2.php" method=post>

        <input type=hidden name=return_url value="/companies/one.php?company_id=<?php  echo $company_id; ?>">
        <input type=hidden name=company_id value="<?php echo $company_id ?>">
        <input type=hidden name=activity_status value="o">
        <input type=hidden name=use_post_vars value="1">

        <input type=hidden name=on_what_table        value="<?php echo $on_what_table; ?>">
        <input type=hidden name=on_what_id           value="<?php echo $on_what_id; ?>">
        <input type=hidden name=on_what_string       value="<?php echo $on_what_string; ?>">
        <input type=hidden name=activity_description value="">
        <input type=hidden name=email                value="">
        <input type=hidden name=followup             value="">
        <input type=hidden name=on_what_status       value="">
        <input type=hidden name=ends_at              value="">

        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=7><?php echo _("Activities"); ?></td>
            </tr>
            <tr>
                <td class=widget_label><?php echo _("Title"); ?></td>
                <td class=widget_label><?php echo _("User"); ?></td>
                <td class=widget_label><?php echo _("Type"); ?></td>
                <td class=widget_label><?php echo _("Contact"); ?></td>
                <td class=widget_label><?php echo _("About"); ?></td>
                <td colspan=2 class=widget_label><?php echo _("Starts"); ?></td>
            </tr>
            <tr>
                <td class=widget_content_form_element><input type=text name=activity_title></td>
                <td class=widget_content_form_element><?php echo $user_menu; ?></td>
                <td class=widget_content_form_element><?php echo $activity_type_menu; ?></td>
                <td class=widget_content_form_element><?php echo $contact_menu; ?></td>
                <td class=widget_content_form_element>&nbsp;</td>
                <td colspan=2 class=widget_content_form_element>
                    <input type=text ID="f_date_d" name=scheduled_at value="<?php  echo date('Y-m-d H:i:s'); ?>">
                    <img ID="f_trigger_d" style="CURSOR: hand" border=0 src="../../img/cal.gif">
                    <input class=button type=submit value="<?php echo _("Add"); ?>">
                    <input class=button type=button onclick="javascript: markComplete();" value="<?php echo _("Done"); ?>">
                </td>
            </tr>
            <?php  echo $activity_rows; ?>
        </table>
        </form>-->

    </div>

        <!-- right column //-->
    <!--<div id="Sidebar">-->

        <!-- categories //-->
        <!--<div id='category_sidebar'>
        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header><?php echo _("Categories"); ?></td>
            </tr>
            <tr>
                <td class=widget_content><?php  echo $categories; ?></td>
            </tr>
            <tr>
                <td class=widget_content_form_element><input type=button class=button onclick="javascript: location.href='categories.php?company_id=<?php  echo $company_id; ?>';" value="<?php echo _("Manage"); ?>"></td>
            </tr>
        </table>
        </div>-->

        <!-- opportunities //-->
        <?php //echo $opportunity_rows; ?>

        <!-- cases //-->
        <?php //echo $case_rows; ?>

        <!-- contact/company //-->
        <?php //echo $relationship_link_rows; ?>

        <!-- notes //-->
        <?php //echo $note_rows; ?>

        <!-- files //-->
        <?php //echo $file_rows; ?>

        <!-- sidebar plugins //-->
        <?php //echo $sidebar_rows; ?>

    <!--</div>-->

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
</script>

<?php

end_page();

/**
 * $Log: companies_one.php,v $
 * Revision 1.3  2005/08/28 15:28:02  braverock
 * - change _new to _blank for broader browser compatibility
 *
 * Revision 1.2  2005/03/21 13:40:57  maulani
 * - Remove redundant code by centralizing common user menu call
 *
 * Revision 1.1  2004/08/23 01:44:58  d2uhlman
 * very basic screens to access contact, company, search by phone plugin, need feedback, no entry possible yet
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
