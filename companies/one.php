<?php
/**
 * Details about One Company
 *
 * Usually called from companies/some.php, but also linked to from many
 * other places in the XRMS UI.
 *
 * $Id: one.php,v 1.40 2004/06/04 14:30:13 braverock Exp $
 *
 * @todo create a categories sidebar and centralize the category handling
 * @todo create a centralized left-pane handler for activities (in companies, contacts,cases, opportunities, campaigns)
 */

//include required files
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
    $phone = $rst->fields['phone'];
    $phone2 = $rst->fields['phone2'];
    $fax = $rst->fields['fax'];
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
    //$rst->close();
}

$credit_limit = number_format($credit_limit, 2);
$current_credit_limit = fetch_current_customer_credit_limit($extref1);

$address_to_display = get_formatted_address($con, $address_id);

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
if(activity_status = 'o' and ends_at < now(), 1, 0) as is_overdue
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
}

// contacts

$sql = "select * from contacts where company_id = $company_id
        and contact_record_status = 'a'
        order by last_name";

$rst = $con->execute($sql);

if ($rst) {
    while (!$rst->EOF) {
        $contact_rows .= "\n<tr>";
        $contact_rows .= "<td class=widget_content><a href='../contacts/one.php?contact_id="
                        . $rst->fields['contact_id'] . "'>"
                        . $rst->fields['last_name'] . ', ' . $rst->fields['first_names']
                        . '</a></td>';
        $contact_rows .= '<td class=widget_content>' . $rst->fields['summary'] . '</td>';
        $contact_rows .= '<td class=widget_content>' . $rst->fields['title'] . '</td>';
        $contact_rows .= '<td class=widget_content>' . $rst->fields['description'] . '</td>';
        $contact_rows .= '<td class=widget_content>' . $rst->fields['work_phone'] . '</td>';
        $contact_rows .= "\n\t<td class=widget_content><a href='mailto:"
                        . $rst->fields['email']
                        . "' onclick=\"location.href='../activities/new-2.php?user_id=$session_user_id&activity_type_id=3&contact_id=$contact_id&activity_title=email to "
                        . $rst->fields['first_names']. " " .$rst->fields['last_name']
                        . "&company_id=$company_id&contact_id="
                        . $rst->fields['contact_id']
                        ."&email=true&return_url=$http_site_root/companies/one.php?company_id=$company_id'\" >"
                        . htmlspecialchars($rst->fields['email'])
                        . '</a></td>';
        $contact_rows .= "\n</tr>";
        $rst->movenext();
    }
    $rst->close();
}

// former names

$sql = "select * from company_former_names where company_id = $company_id order by namechange_at desc";

$rst = $con->execute($sql);

if ($rst) {
    while (!$rst->EOF) {
        $former_name_rows .= '<tr><td class=sublabel>Former Name</td>';
        $former_name_rows .= '<td class=clear>' . $rst->fields['former_name'] . '</td>';
        $former_name_rows .= '</tr>';
        $rst->movenext();
    }
    $rst->close();
}

// related companies

$sql = "select r.relationship_type, r.established_at, r.company_to_id, c.company_name from company_relationship r, companies c where r.company_from_id = $company_id and r.company_to_id=c.company_id order by r.established_at desc";

$rst = $con->execute($sql);

$linecounter = 0;
if ($rst) {
    while (!$rst->EOF) {
        $linecounter +=1;
        $established_at = $con->userdate($rst->fields['established_at']);
        $relationship_rows .= ($linecounter == '1') ? '<tr><td class=sublabel>Relationship</td>' : '<tr><td class=sublabel>&nbsp;</td>';
        $relationship_rows .= '<td class=clear>' . $rst->fields['relationship_type'] . ' '
            . '<a href="one.php?company_id=' . $rst->fields['company_to_id']
            . '">' . $rst->fields['company_name'] . '</a> '
            . $established_at . '</td>';
        $relationship_rows .= '</tr>';
        $rst->movenext();
    }
    $rst->close();
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
}

$categories = implode($categories, ", ");

/*********************************/
/*** Include the sidebar boxes ***/

//set up our substitution variables for use in the siddebars
$on_what_table = 'companies';
$on_what_id = $company_id;
$on_what_string = 'company';

//include the Cases sidebar
$case_limit_sql = "and cases.".$on_what_string."_id = $on_what_id";
require_once("../cases/sidebar.php");

//include the opportunities sidebar
$opportunity_limit_sql = "and opportunities.".$on_what_string."_id = $on_what_id";
require_once("../opportunities/sidebar.php");

//include the files sidebar
require_once("../files/sidebar.php");

//include the notes sidebar
require_once("../notes/sidebar.php");

/** End of the sidebar includes **/
/*********************************/

$sql = "select concat(first_names, ' ', last_name) as contact_name, contact_id from contacts where company_id = $company_id and contact_record_status = 'a'";
$rst = $con->execute($sql);
if ($rst) {
    $contact_menu = $rst->getmenu2('contact_id', '', true);
    $rst->close();
}

$sql = "select username, user_id from users where user_record_status = 'a' order by username";
$rst = $con->execute($sql);
$user_menu = $rst->getmenu2('user_id', $session_user_id, false);
$rst->close();

$sql = "select activity_type_pretty_name, activity_type_id from activity_types where activity_type_record_status = 'a' order by activity_type_pretty_name";
$rst = $con->execute($sql);
$activity_type_menu = $rst->getmenu2('activity_type_id', '', false);
$rst->close();

$con->close();

if (strlen($activity_rows) == 0) {
    $activity_rows = "<tr><td class=widget_content colspan=7>No activities</td></tr>";
}

if (strlen($contact_rows) == 0) {
    $contact_rows = "<tr><td class=widget_content colspan=6>$strCompaniesOneNoContactsMessage</td></tr>";
}

if (strlen($former_name_rows) == 0) {
    $former_name_rows = "";
}

if (strlen($relationship_rows) == 0) {
    $relationship_rows = "";
}

if (strlen($categories) == 0) {
    $categories = $strCompaniesOneNoCategoriesMessage;
}

$page_title = (strlen($strCompaniesOnePageTitle) > 0) ? $strCompaniesOnePageTitle . ' : ' . $company_name : $company_name;
start_page($page_title, true, $msg);

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
                <td class=widget_header><?php  echo $strCompaniesOneCompanyDetailsTitle; ?></td>
            </tr>
            <tr>
                <td class=widget_content>

                    <table border=0 cellpadding=0 cellspacing=0 width=100%>
                        <tr>
                            <td width=50% class=clear align=left valign=top>
                                <table border=0 cellpadding=0 cellspacing=0 width=100%>
                                <tr>
                                    <td width=1% class=sublabel>Company Name</td>
                                    <td class=clear><?php  echo $company_name; ?></td>
                                </tr>
                                <tr>
                                    <td width=1% class=sublabel>Legal Name</td>
                                    <td class=clear><?php  echo $legal_name; ?></td>
                                </tr>
                                <?php  echo $former_name_rows; ?>
                                <tr>
                                    <td class=sublabel>Code</td>
                                    <td class=clear><?php  echo $company_code; ?></td>
                                </tr>
                                <tr>
                                    <td class=sublabel>Industry</td>
                                    <td class=clear><?php  echo $industry_pretty_name; ?></td>
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
                                <tr>
                                    <td class=sublabel>&nbsp;</td>
                                    <td class=clear>&nbsp;</td>
                                </tr>
                                    <?php echo $relationship_rows; ?>
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
                <input class=button type=button value="Relationships" onclick="javascript: location.href='relationships.php?company_id=<?php echo $company_id; ?>';">
                <input class=button type=button value="Addresses" onclick="javascript: location.href='addresses.php?company_id=<?php echo $company_id; ?>';">
                <input class=button type=button value="Divisions" onclick="javascript: location.href='divisions.php?company_id=<?php echo $company_id; ?>';">
                </td>
            </tr>
        </table>

        <!-- contacts //-->
        <table class=widget cellspacing=1>
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

        <script language="JavaScript" type="text/javascript" src="<?php  echo $http_site_root; ?>/js/calendar1.js"></script>
        <!-- activities //-->
        <form action="<?php  echo $http_site_root; ?>/activities/new-2.php" method=post>
        <input type=hidden name=return_url value="/companies/one.php?company_id=<?php  echo $company_id; ?>">
        <input type=hidden name=company_id value="<?php echo $company_id ?>">
        <input type=hidden name=activity_status value="o">
        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=7><?php  echo $strCompaniesOneActivitiesTitle; ?></td>
            </tr>
            <tr>
                <td class=widget_label>Title</td>
                <td class=widget_label>User</td>
                <td class=widget_label>Type</td>
                <td class=widget_label>Contact</td>
                <td class=widget_label>About</td>
                <td colspan=2 class=widget_label>Starts</td>
            </tr>
            <tr>
                <td class=widget_content_form_element><input type=text name=activity_title></td>
                <td class=widget_content_form_element><?php  echo $user_menu; ?></td>
                <td class=widget_content_form_element><?php  echo $activity_type_menu; ?></td>
                <td class=widget_content_form_element><?php  echo $contact_menu; ?></td>
                <td class=widget_content_form_element>&nbsp;</td>
                <td colspan=2 class=widget_content_form_element><input type=text size=10 name=scheduled_at value="<?php echo date('Y-m-d H:i:s'); ?>">
              <a href="javascript:cal1.popup();"><img class=date_picker border=0 src="../img/cal.gif"></a>
<input class=button type=submit value="Add"> <input class=button type=button onclick="javascript: markComplete();" value="Done"></td>
            </tr>
            <?php  echo $activity_rows; ?>
        </table>
        </form>

    </div>

        <!-- right column //-->
    <div id="Sidebar">

        <!-- categories //-->
        <div id='category_sidebar'>
        <table class=widget cellspacing=1>
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
        </div>

        <!-- opportunities //-->
        <?php echo $opportunity_rows; ?>

        <!-- cases //-->
        <?php echo $case_rows; ?>

        <!-- notes //-->
        <?php echo $note_rows; ?>

        <!-- files //-->
        <?php echo $file_rows; ?>

    </div>

</div>

<script>
<!--

// create calendar object(s) just after form tag closed
// specify form element as the only parameter (document.forms['formname'].elements['inputname']);
// note: you can have as many calendar objects as you need for your application

    var cal1 = new calendar1(document.forms[0].elements['scheduled_at']);
    cal1.year_scroll = false;
    cal1.time_comp = false;

//-->
</script>

<?php

end_page();

/**
 * $Log: one.php,v $
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
