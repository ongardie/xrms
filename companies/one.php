<?php

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'utils-accounting.php');

$this = $_SERVER['REQUEST_URI'];
$session_user_id = session_check( $this );
require_once($include_directory . 'lang/' . $_SESSION['language'] . '.php');

$msg = $_GET['msg'];

$company_id = $_GET['company_id'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
// $con->debug = 1;

update_recent_items($con, $session_user_id, "companies", $company_id);

$sql = "select cs.*, c.*, account_status_display_html, rating_display_html, company_source_display_html, i.industry_pretty_name, u1.username as owner_username, u2.username as entered_by, u3.username as last_modified_by, iso_code3, address_format_string
        from crm_statuses cs, companies c, account_statuses as1, ratings r, company_sources cs2, industries i, users u1, users u2, users u3, countries, address_format_strings afs
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
    $company_name = $rst->fields['company_name'];
    $company_legal_name = $rst->fields['company_legal_name'];
    $tax_id = $rst->fields['tax_id'];
    $company_code = $rst->fields['company_code'];
    $crm_status_pretty_name = $rst->fields['crm_status_pretty_name'];
    $company_source = $rst->fields['company_source_display_html'];
    $industry_pretty_name = $rst->fields['industry_pretty_name'];
    $user_id = $rst->fields['user_id'];
    $owner_username = $rst->fields['owner_username'];
    $phone = $rst->fields['phone'];
    $phone2 = $rst->fields['phone2'];
    $fax = $rst->fields['fax'];
    $url = $rst->fields['url'];
    $employees = $rst->fields['employees'];
    $revenue = $rst->fields['revenue'];
    $account_status = $rst->fields['account_status_display_html'];
    $credit_limit = $rst->fields['credit_limit'];
    $rating = $rst->fields['rating_display_html'];
    $terms = $rst->fields['terms'];
    $profile = $rst->fields['profile'];
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
    //$rst->close();
}

$sql = "select c.*, addresses.*, iso_code3, address_format_string
        from companies c, addresses, countries, address_format_strings afs
        where
            c.default_primary_address = addresses.address_id
        and addresses.country_id = countries.country_id
        and countries.address_format_string_id = afs.address_format_string_id
        and c.company_id = $company_id";

$rst = $con->execute($sql);

if ($rst) {
    $line1 = $rst->fields['line1'];
    $line2 = $rst->fields['line2'];
    $city = $rst->fields['city'];
    $province = $rst->fields['province'];
    $postal_code = $rst->fields['postal_code'];
    $address_body = $rst->fields['address_body'];
    $use_pretty_address = $rst->fields['use_pretty_address'];
    $country = $rst->fields['iso_code3'];
    $address_format_string = $rst->fields['address_format_string'];
    $rst->close();
}

$credit_limit = number_format($credit_limit, 2);
$current_credit_limit = fetch_current_customer_credit_limit($extref1);

if ($use_pretty_address == 't') {
    $address_to_display = $address_body;
} else {
    $lines = (strlen($line2) > 0) ? "$line1<br>$line2" : $line1;
    eval("\$address_to_display = \"$address_format_string\";");
    // eval ("\$str = \"$str\";");
}

if (strlen($url) > 0) {
    $url = "<a target='_new' href='" . $url . "'>$url</a>";
}

//
//  list of most recent activities
//

$sql_activities = "select activity_id,
activity_title,
scheduled_at,
on_what_table,
on_what_id,
a.entered_at,
activity_status,
at.activity_type_pretty_name,
cont.first_names as contact_first_names,
cont.last_name as contact_last_name,
u.username,
if(activity_status = 'o' and scheduled_at < now(), 1, 0) as is_overdue
from activity_types at, users u, activities a left join contacts cont on a.contact_id = cont.contact_id
where a.company_id = $company_id
and a.user_id = u.user_id
and a.activity_type_id = at.activity_type_id
and a.activity_record_status = 'a'
order by is_overdue desc, a.scheduled_at desc, a.entered_at desc";

$rst = $con->selectlimit($sql_activities, $display_how_many_activities_on_company_page);

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
}

$sql = "select * from contacts where company_id = $company_id and contact_record_status = 'a' order by first_names";

$rst = $con->execute($sql);

if ($rst) {
    while (!$rst->EOF) {
        $contact_rows .= '<tr>';
        $contact_rows .= "<td class=widget_content><a href='../contacts/one.php?contact_id=" . $rst->fields['contact_id'] . "'>" . $rst->fields['first_names'] . ' ' . $rst->fields['last_name'] . '</a></td>';
        $contact_rows .= '<td class=widget_content>' . $rst->fields['summary'] . '</td>';
        $contact_rows .= '<td class=widget_content>' . $rst->fields['title'] . '</td>';
        $contact_rows .= '<td class=widget_content>' . $rst->fields['description'] . '</td>';
        $contact_rows .= '<td class=widget_content>' . $rst->fields['work_phone'] . '</td>';
        $contact_rows .= "<td class=widget_content><a href='mailto:" . $rst->fields['email'] . " ' > " . htmlspecialchars($rst->fields['email']) . '</a></td>';
        $contact_rows .= '</tr>';
        $rst->movenext();
    }
    $rst->close();
}

// associated with

$categories_sql = "select category_pretty_name
from categories c, category_scopes cs, category_category_scope_map ccsm, entity_category_map ecm
where ecm.on_what_table = 'companies'
and ecm.on_what_id = $company_id
and ecm.category_id = c.category_id
and cs.category_scope_id = ccsm.category_scope_id
and c.category_id = ccsm.category_id
and cs.on_what_table = 'companies'
and category_record_status = 'a'
order by category_pretty_name";

$rst = $con->execute($categories_sql);
$categories = array();

if ($rst) {
    while (!$rst->EOF) {
        array_push($categories, $rst->fields['category_pretty_name']);
        $rst->movenext();
    }
    $rst->close();
}

$categories = implode($categories, ", ");

$sql = "select note_id, note_description, entered_by, entered_at, username from notes, users
where notes.entered_by = users.user_id
and on_what_table = 'companies' and on_what_id = $company_id
and note_record_status = 'a' order by entered_at desc";

$rst = $con->execute($sql);

if ($rst) {
    while (!$rst->EOF) {
        $note_rows .= "<tr>";
        $note_rows .= "<td class=widget_content><font class=note_label>" . $con->userdate($rst->fields['entered_at']) . " &bull; " . $rst->fields['username'] . " &bull; <a href='../notes/edit.php?note_id=" . $rst->fields['note_id'] . "&return_url=/companies/one.php?company_id=" . $company_id . "'>Edit</a></font><br>" . $rst->fields['note_description'] . "</td>";
        $note_rows .= "</tr>";
        $rst->movenext();
    }
    $rst->close();
}

$sql = "select * from opportunities opp, opportunity_statuses os, users u
where opp.opportunity_status_id = os.opportunity_status_id
and opp.company_id = $company_id
and opp.user_id = u.user_id
and opportunity_record_status = 'a'
order by close_at desc";

$rst = $con->execute($sql);

if ($rst) {
    while (!$rst->EOF) {
        $opportunity_rows .= '<tr>';
        $opportunity_rows .= "<td class=widget_content><a href='$http_site_root/opportunities/one.php?opportunity_id=" . $rst->fields['opportunity_id'] . "'>" . $rst->fields['opportunity_title'] . '</a></td>';
        $opportunity_rows .= '<td class=widget_content>' . $rst->fields['username'] . '</td>';
        $opportunity_rows .= '<td class=widget_content>' . $rst->fields['opportunity_status_pretty_name'] . '</td>';
        $opportunity_rows .= '<td class=widget_content>' . $con->userdate($rst->fields['close_at']) . '</td>';
        $opportunity_rows .= '</tr>';
        $rst->movenext();
    }
    $rst->close();
}

$sql = "select * from cases, case_priorities, users
where cases.case_priority_id = case_priorities.case_priority_id
and company_id = $company_id
and cases.user_id = users.user_id
and case_record_status = 'a'
order by due_at desc";

$rst = $con->execute($sql);

if ($rst) {
    while (!$rst->EOF) {
        $case_rows .= '<tr>';
        $case_rows .= "<td class=widget_content><a href='$http_site_root/cases/one.php?case_id=" . $rst->fields['case_id'] . "'>" . $rst->fields['case_title'] . '</a></td>';
        $case_rows .= '<td class=widget_content>' . $rst->fields['username'] . '</td>';
        $case_rows .= '<td class=widget_content>' . $rst->fields['case_priority_pretty_name'] . '</td>';
        $case_rows .= '<td class=widget_content>' . $con->userdate($rst->fields['due_at']) . '</td>';
        $case_rows .= '</tr>';
        $rst->movenext();
    }
    $rst->close();
}

$sql = "select * from files, users where files.entered_by = users.user_id and on_what_table = 'companies' and on_what_id = $company_id and file_record_status = 'a'";

$rst = $con->execute($sql);

if ($rst) {
    while (!$rst->EOF) {
        $file_rows .= '<tr>';
        $file_rows .= "<td class=widget_content><a href='$http_site_root/files/one.php?return_url=/companies/one.php?company_id=$company_id&file_id=" . $rst->fields['file_id'] . "'>" . $rst->fields['file_pretty_name'] . '</a></td>';
        $file_rows .= '<td class=widget_content>' . pretty_filesize($rst->fields['file_size']) . '</td>';
        $file_rows .= '<td class=widget_content>' . $rst->fields['username'] . '</td>';
        $file_rows .= '<td class=widget_content>' . $con->userdate($rst->fields['entered_at']) . '</td>';
        $file_rows .= '</tr>';
        $rst->movenext();
    }
    $rst->close();
}

$sql = "select concat(first_names, ' ', last_name) as contact_name, contact_id from contacts where company_id = $company_id and contact_record_status = 'a'";
$rst = $con->execute($sql);
$contact_menu = $rst->getmenu2('contact_id', '', true);
$rst->close();

$sql = "select username, user_id from users where user_record_status = 'a' order by username";
$rst = $con->execute($sql);
$user_menu = $rst->getmenu2('user_id', $session_user_id, false);
$rst->close();

$sql = "select activity_type_pretty_name, activity_type_id from activity_types where activity_type_record_status = 'a' order by activity_type_pretty_name";
$rst = $con->execute($sql);
$activity_type_menu = $rst->getmenu2('activity_type_id', '', false);
$rst->close();

add_audit_item($con, $session_user_id, 'view company', 'companies', $company_id);

$con->close();

if (strlen($activity_rows) == 0) {
    $activity_rows = "<tr><td class=widget_content colspan=7>No activities</td></tr>";
}

if (strlen($contact_rows) == 0) {
    $contact_rows = "<tr><td class=widget_content colspan=6>$strCompaniesOneNoContactsMessage</td></tr>";
}

if (strlen($categories) == 0) {
    $categories = $strCompaniesOneNoCategoriesMessage;
}

if (strlen($opportunity_rows) == 0) {
    $opportunity_rows = "<tr><td class=widget_content colspan=4>$strCompaniesOneNoOpportunitiesMessage</td></tr>";
}

if (strlen($note_rows) == 0) {
    $note_rows = "<tr><td class=widget_content colspan=4>$strCompaniesOneNoNotesMessage</td></tr>";
}

if (strlen($case_rows) == 0) {
    $case_rows = "<tr><td class=widget_content colspan=4>$strCompaniesOneNoCasesMessage</td></tr>";
}

if (strlen($file_rows) == 0) {
    $file_rows = "<tr><td class=widget_content colspan=4>$strCompaniesOneNoFilesMessage</td></tr>";
}

$page_title = (strlen($strCompaniesOnePageTitle) > 0) ? $strCompaniesOnePageTitle . ' : ' . $company_name : $company_name;
start_page($page_title, true, $msg);

?>

<script language=javascript>
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

<table border=0 cellpadding=0 cellspacing=0 width=100%>
    <tr>
        <td class=lcol width=70% valign=top>

        <table class=widget cellspacing=1 width=100%>
            <tr>
                <td class=widget_header><?php  echo $strCompaniesOneCompanyDetailsTitle; ?></td>
            </tr>
            <tr>
                <td class=widget_content>

                    <table border=0 cellpadding=0 cellspacing=0 width=100%>
                        <tr>
                            <td width=50% class=clear align=left valign=top>
                                <table border=0 cellpadding=0 cellspacing=0 width=100%>
                                <tr>
                                    <td width=1% class=sublabel>Legal Name</td>
                                    <td class=clear><?php  echo $company_legal_name; ?></td>
                                </tr>
                                <tr>
                                    <td class=sublabel>Code</td>
                                    <td class=clear><?php  echo $company_code; ?></td>
                                </tr>
                                <tr>
                                    <td class=sublabel>Types</td>
                                    <td class=clear><?php  echo $company_type_list; ?></td>
                                </tr>
                                <tr>
                                    <td class=sublabel>CRM Status</td>
                                    <td class=clear><?php  echo $crm_status_pretty_name; ?></td>
                                </tr>
                                <tr>
                                    <td class=sublabel>Acct. Owner</td>
                                    <td class=clear><?php  echo $owner_username; ?></td>
                                </tr>
                                <tr>
                                    <td class=sublabel>Phone</td>
                                    <td class=clear><?php  echo $phone; ?></td>
                                </tr>
                                <tr>
                                    <td class=sublabel>Alt. Phone</td>
                                    <td class=clear><?php  echo $phone2; ?></td>
                                </tr>
                                <tr>
                                    <td class=sublabel>Fax</td>
                                    <td class=clear><?php  echo $fax; ?></td>
                                </tr>
                                <tr>
                                    <td class=sublabel>URL</td>
                                    <td class=clear><?php  echo $url; ?></td>
                                </tr>
                                <tr>
                                    <td class=sublabel>&nbsp;</td>
                                    <td class=clear>&nbsp;</td>
                                </tr>
                                <tr>
                                    <td class=sublabel>Address</td>
                                    <td class=clear><?php echo $address_to_display ?></td>
                                </tr>
                                <tr>
                                    <td class=sublabel>&nbsp;</td>
                                    <td class=clear>&nbsp;</td>
                                </tr>
                                <tr>
                                    <td class=sublabel>Created</td>
                                    <td class=clear><?php  echo $entered_at; ?> by <?php echo $entered_by; ?></td>
                                </tr>
                                <tr>
                                    <td class=sublabel>Last Modified</td>
                                    <td class=clear><?php  echo $last_modified_at; ?> by <?php echo $last_modified_by; ?></td>
                                </tr>
                                </table>

                            </td>

                            <td width=50% class=clear align=left valign=top>

                                <table border=0 cellpadding=0 cellspacing=0 width=100%>
                                <tr>
                                    <td class=sublabel>Account&nbsp;Status</td>
                                    <td class=clear><?php echo $account_status; ?></td>
                                </tr>
                                <tr>
                                    <td class=sublabel>Tax ID</td>
                                    <td class=clear><?php echo $tax_id; ?></td>
                                </tr>
                                <tr>
                                    <td class=sublabel>Credit&nbsp;Limit</td>
                                    <td class=clear>$<?php echo $credit_limit; ?> <?php echo $current_credit_limit; ?></td>
                                </tr>
                                <tr>
                                    <td class=sublabel>Rating</td>
                                    <td class=clear><?php echo $rating; ?></td>
                                </tr>
                                <tr>
                                    <td class=sublabel>Terms</td>
                                    <td class=clear><?php echo $terms; ?> days</td>
                                </tr>
                                <tr>
                                    <td class=sublabel>&nbsp;</td>
                                    <td class=clear>&nbsp;</td>
                                </tr>
                                <tr>
                                    <td width=1% class=sublabel><?php  echo $strCompaniesOneCompanySourceLabel; ?></td>
                                    <td class=clear><?php  echo $company_source; ?></td>
                                </tr>
                                <tr>
                                    <td class=sublabel><?php  echo $strCompaniesOneCompanyIndustryLabel; ?></td>
                                    <td class=clear><?php  echo $industry_pretty_name; ?></td>
                                </tr>
                                <tr>
                                    <td class=sublabel><?php  echo $strCompaniesOneCompanyEmployeesLabel; ?></td>
                                    <td class=clear><?php  echo $employees; ?></td>
                                </tr>
                                <tr>
                                    <td class=sublabel><?php  echo $strCompaniesOneCompanyRevenueLabel; ?></td>
                                    <td class=clear><?php  echo $revenue; ?></td>
                                </tr>
                                <tr>
                                    <td class=sublabel>&nbsp;</td>
                                    <td class=clear>&nbsp;</td>
                                </tr>
                                <tr>
                                    <td width=1% class=sublabel><?php  echo $company_custom1_label; ?></td>
                                    <td class=clear><?php  echo $custom1; ?></td>
                                </tr>
                                <tr>
                                    <td class=sublabel><?php  echo $company_custom2_label; ?></td>
                                    <td class=clear><?php  echo $custom2; ?></td>
                                </tr>
                                <tr>
                                    <td class=sublabel><?php  echo $company_custom3_label; ?></td>
                                    <td class=clear><?php  echo $custom3; ?></td>
                                </tr>
                                <tr>
                                    <td class=sublabel><?php  echo $company_custom4_label; ?></td>
                                    <td class=clear><?php  echo $custom4; ?></td>
                                </tr>
                            </table>

                            </td>
                        </tr>
                    </table>

                    <p><?php  echo $profile; ?>

                </td>
            </tr>
            <tr>
                <td class=widget_content_form_element>
                <input class=button type=button value="Edit" onclick="javascript: location.href='edit.php?company_id=<?php echo $company_id; ?>';">
                <input class=button type=button value="Admin" onclick="javascript:location.href='admin.php?company_id=<?php echo $company_id; ?>';">
                <input class=button type=button value="Clone" onclick="javascript: location.href='new.php?clone_id=<?php echo $company_id ?>';">
                <input class=button type=button value="Mail Merge" onclick="javascript: location.href='../email/email.php?scope=company&company_id=<?php echo $company_id; ?>';">
                <input class=button type=button value="News" onclick="javascript: openNewsWindow();">
                <input class=button type=button value="Addresses" onclick="javascript: location.href='addresses.php?company_id=<?php echo $company_id; ?>';">
                </td>
            </tr>
        </table>

        <!-- contacts //-->
        <table class=widget cellspacing=1 width=100%>
            <tr>
                <td class=widget_header colspan=6><?php  echo $strCompaniesOneContactsTitle; ?></td>
            </tr>
            <tr>
                <td class=widget_label>Name</td>
                <td class=widget_label>Summary</td>
                <td class=widget_label>Title</td>
                <td class=widget_label>Description</td>
                <td class=widget_label>Phone</td>
                <td class=widget_label>E-Mail</td>
            </tr>
            <?php  echo $contact_rows; ?>
            <tr>
                <td class=widget_content_form_element colspan=6><input type=button class=button onclick="location.href='../contacts/new.php?company_id=<?php echo $company_id; ?>';" value="New"></td>
            </tr>
        </table>

        <!-- activities //-->
        <form action="<?php  echo $http_site_root; ?>/activities/new-2.php" method=post>
        <input type=hidden name=return_url value="/companies/one.php?company_id=<?php  echo $company_id; ?>">
        <input type=hidden name=company_id value="<?php echo $company_id ?>">
        <input type=hidden name=activity_status value="o">
        <table class=widget cellspacing=1 width=100%>
            <tr>
                <td class=widget_header colspan=7><?php  echo $strCompaniesOneActivitiesTitle; ?></td>
            </tr>
            <tr>
                <td class=widget_label>Activity</td>
                <td class=widget_label>User</td>
                <td class=widget_label>Type</td>
                <td class=widget_label>Contact</td>
                <td class=widget_label>About</td>
                <td colspan=2 class=widget_label>Starts</td>
            </tr>
            <tr>
                <td class=widget_content_form_element><input type=text name=activity_title size=40></td>
                <td class=widget_content_form_element><?php  echo $user_menu; ?></td>
                <td class=widget_content_form_element><?php  echo $activity_type_menu; ?></td>
                <td class=widget_content_form_element><?php  echo $contact_menu; ?></td>
                <td class=widget_content_form_element>&nbsp;</td>
                <td colspan=2 class=widget_content_form_element><input type=text size=10 name=scheduled_at value="<?php echo date('Y-m-d'); ?>"> <input class=button type=submit value="Add"> <input class=button type=button onclick="javascript: markComplete();" value="Done"></td>
            </tr>
            <?php  echo $activity_rows; ?>
        </table>
        </form>

        </td>

        <!-- gutter //-->
        <td class=gutter width=1%>
        &nbsp;
        </td>

        <!-- right column //-->
        <td class=rcol width=29% valign=top>

        <!-- categories //-->
        <table class=widget cellspacing=1 width=100%>
            <tr>
                <td class=widget_header><?php  echo $strCompaniesOneCategoriesTitle; ?></td>
            </tr>
            <tr>
                <td class=widget_content><?php  echo $categories; ?></td>
            </tr>
            <tr>
                <td class=widget_content_form_element><input type=button class=button onclick="javascript: location.href='categories.php?company_id=<?php  echo $company_id; ?>';" value="Manage"></td>
            </tr>
        </table>

        <!-- notes //-->
        <form action="../notes/new.php" method="post">
        <input type="hidden" name="on_what_table" value="companies">
        <input type="hidden" name="on_what_id" value="<?php echo $company_id ?>">
        <input type="hidden" name="return_url" value="/companies/one.php?company_id=<?php echo $company_id ?>">
        <table class=widget cellspacing=1 width=100%>
            <tr>
                <td class=widget_header>Notes</td>
            </tr>
            <?php echo $note_rows; ?>
            <tr>
                <td class=widget_content_form_element colspan=4><input type=submit class=button value="New"></td>
            </tr>
        </table>
        </form>

        <!-- opportunities //-->
        <form action="<?php  echo $http_site_root; ?>/opportunities/new.php" method="post">
        <input type="hidden" name="company_id" value="<?php  echo $company_id; ?>">
        <table class=widget cellspacing=1 width=100%>
            <tr>
                <td class=widget_header colspan=4><?php  echo $strCompaniesOneOpportunitiesTitle; ?></td>
            </tr>
            <tr>
                <td class=widget_label>Name</td>
                <td class=widget_label>Owner</td>
                <td class=widget_label>Status</td>
                <td class=widget_label>Due</td>
            </tr>
            <?php  echo $opportunity_rows; ?>
            <tr>
                <td class=widget_content_form_element colspan=4><input type=submit class=button value="New"> <input type=button class=button onclick="javascript: location.href='<?php  echo $http_site_root; ?>/opportunities/some.php?company_code=<?php $company_code ?>';" value="More"></td>
            </tr>
        </table>
        </form>

        <!-- cases //-->
        <form action="<?php  echo $http_site_root; ?>/cases/new.php" method="post">
        <input type="hidden" name="company_id" value="<?php  echo $company_id; ?>">
        <table class=widget cellspacing=1 width=100%>
            <tr>
                <td class=widget_header colspan=5><?php  echo $strCompaniesOneCasesTitle; ?></td>
            </tr>
            <tr>
                <td class=widget_label>Name</td>
                <td class=widget_label>Owner</td>
                <td class=widget_label>Priority</td>
                <td class=widget_label>Due</td>
            </tr>
            <?php  echo $case_rows; ?>
            <tr>
                <td class=widget_content_form_element colspan=5><input type=submit class=button value="New"> <input type=button class=button onclick="javascript: location.href='<?php  echo $http_site_root; ?>/cases/some.php?company_code=<?php $company_code ?>';" value="More"></td>
            </tr>
        </table>
        </form>

        <!-- files //-->
        <form action="<?php  echo $http_site_root; ?>/files/new.php" method="post">
        <input type=hidden name=on_what_table value="companies">
        <input type=hidden name=on_what_id value="<?php  echo $company_id; ?>">
        <input type=hidden name=return_url value="/companies/one.php?company_id=<?php  echo $company_id; ?>">
        <table class=widget cellspacing=1 width=100%>
            <tr>
                <td class=widget_header colspan=5><?php  echo $strCompaniesOneFilesTitle; ?></td>
            </tr>
            <tr>
                <td class=widget_label>Name</td>
                <td class=widget_label>Size</td>
                <td class=widget_label>Owner</td>
                <td class=widget_label>Date</td>

            </tr>
            <?php  echo $file_rows; ?>
            <tr>
                <td class=widget_content_form_element colspan=5><input type=submit class=button value="New"></td>
            </tr>
        </table>
        </form>

        </td>
    </tr>
</table>

<?php end_page(); ?>
