<?php

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

$session_user_id = session_check();
require_once($include_directory . 'lang/' . $_SESSION['language'] . '.php');

$contact_id = $_GET['contact_id'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
// $con->debug = 1;

update_recent_items($con, $session_user_id, "contacts", $contact_id);

$sql = "select cont.*,
c.company_id, company_name, company_code, line1, line2, addresses.city, province, addresses.postal_code, address_body, use_pretty_address, iso_code3, address_format_string, 
u1.username as entered_by_username, u2.username as last_modified_by_username, u3.username as account_owner, 
account_status_display_html, crm_status_display_html 
from contacts cont, companies c, users u1, users u2, users u3, account_statuses as1, crm_statuses crm, addresses, countries, address_format_strings afs 
where cont.company_id = c.company_id 
and cont.entered_by = u1.user_id 
and cont.last_modified_by = u2.user_id 
and c.user_id = u3.user_id 
and c.account_status_id = as1.account_status_id 
and c.crm_status_id = crm.crm_status_id 
and countries.country_id = addresses.country_id 
and countries.address_format_string_id = afs.address_format_string_id 
and addresses.address_id = cont.address_id 
and contact_id = $contact_id";

$rst = $con->execute($sql);

if ($rst) {
    $company_id = $rst->fields['company_id'];
	$address_id = $rst->fields['address_id'];
    $company_name = $rst->fields['company_name'];
    $company_code = $rst->fields['company_code'];
    $crm_status_display_html = $rst->fields['crm_status_display_html'];
    $account_status_display_html = $rst->fields['account_status_display_html'];
    $account_owner = $rst->fields['account_owner'];
    $last_name = $rst->fields['last_name'];
    $first_names = $rst->fields['first_names'];
    $summary = $rst->fields['summary'];
    $title = $rst->fields['title'];
    $description = $rst->fields['description'];
    $profile = $rst->fields['profile'];
    $email = $rst->fields['email'];
    $work_phone = $rst->fields['work_phone'];
    $cell_phone = $rst->fields['cell_phone'];
    $home_phone = $rst->fields['home_phone'];
    $aol_name = $rst->fields['aol_name'];
    $yahoo_name = $rst->fields['yahoo_name'];
    $msn_name = $rst->fields['msn_name'];
    $interests = $rst->fields['interests'];
    $custom1 = $rst->fields['custom1'];
    $custom2 = $rst->fields['custom2'];
    $custom3 = $rst->fields['custom3'];
    $custom4 = $rst->fields['custom4'];
	$line1 = $rst->fields['line1'];
	$line2 = $rst->fields['line2'];
	$city = $rst->fields['city'];
	$province = $rst->fields['province'];
	$postal_code = $rst->fields['postal_code'];
	$address_body = $rst->fields['address_body'];
	$use_pretty_address = $rst->fields['use_pretty_address'];
	$country = $rst->fields['iso_code3'];
	$address_format_string = $rst->fields['address_format_string'];
    $entered_at = $con->userdate($rst->fields['entered_at']);
    $last_modified_at = $con->userdate($rst->fields['last_modified_at']);
    $entered_by = $rst->fields['entered_by_username'];
    $last_modified_by = $rst->fields['last_modified_by_username'];
    $rst->close();
}

if ($use_pretty_address == 't') {
	$address_to_display = $address_body;
} else {
	$lines = (strlen($line2) > 0) ? "$line1<br>$line2" : $line1;
	eval("\$address_to_display = \"$address_format_string\";");
	// eval ("\$str = \"$str\";");
}

// most recent activities

$sql_activities = "select activity_id, 
activity_title, 
scheduled_at, 
a.entered_at, 
a.on_what_table,
a.on_what_id,
activity_status, 
at.activity_type_pretty_name, 
cont.first_names as contact_first_names, 
cont.last_name as contact_last_name, 
u.username, 
if(activity_status = 'o' and scheduled_at < now(), 1, 0) as is_overdue
from activity_types at, users u, activities a, contacts cont
where a.contact_id = $contact_id
and a.contact_id = cont.contact_id
and a.user_id = u.user_id
and a.activity_type_id = at.activity_type_id
and a.activity_record_status = 'a'
order by is_overdue desc, a.scheduled_at desc, a.entered_at desc";

$rst = $con->selectlimit($sql_activities, $display_how_many_activities_on_contact_page);

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
        $activity_rows .= "<td class='$classname'><a href='$http_site_root/activities/one.php?return_url=/contacts/one.php?contact_id=$contact_id&activity_id=" . $rst->fields['activity_id'] . "'>" . $rst->fields['activity_title'] . '</a></td>';
        $activity_rows .= '<td class=' . $classname . '>' . $rst->fields['username'] . '</td>';
        $activity_rows .= '<td class=' . $classname . '>' . $rst->fields['activity_type_pretty_name'] . '</td>';
        $activity_rows .= '<td class=' . $classname . ">$attached_to_link</td>";
        $activity_rows .= '<td colspan=2 class=' . $classname . '>' . $con->userdate($rst->fields['scheduled_at']) . '</td>';
        $activity_rows .= '</tr>';
        $rst->movenext();
    }
    $rst->close();
}

// associated with

$categories_sql = "select category_pretty_name
from categories
where category_record_status = 'a'
and category_id in (select category_id from entity_category_map where on_what_table = 'contacts' and on_what_id = $contact_id)
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

$sql = "select * from notes
where on_what_table = 'contacts' and on_what_id = $contact_id
and note_record_status = 'a' order by entered_at desc";

$rst = $con->execute($sql);

if ($rst) {
    while (!$rst->EOF) {
        $note_rows .= '<tr>';
        $note_rows .= '<td class=widget_content>' . $rst->fields['note_description'] . '</td>';
        $note_rows .= '</tr>';
        $rst->movenext();
    }
    $rst->close();
}

$sql = "select * from opportunities opp, opportunity_statuses os, users u
where opp.opportunity_status_id = os.opportunity_status_id
and opp.contact_id = $contact_id
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
and contact_id = $contact_id
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

$sql = "select * from files, users where files.entered_by = users.user_id and on_what_table = 'contacts' and on_what_id = $contact_id and file_record_status = 'a'";

$rst = $con->execute($sql);

if ($rst) {
    while (!$rst->EOF) {
        $file_rows .= '<tr>';
        $file_rows .= "<td class=widget_content><a href='$http_site_root/files/one.php?return_url=/contacts/one.php?contact_id=$contact_id&file_id=" . $rst->fields['file_id'] . "'>" . $rst->fields['file_pretty_name'] . '</a></td>';
        $file_rows .= '<td class=widget_content>' . pretty_filesize($rst->fields['file_size']) . '</td>';
        $file_rows .= '<td class=widget_content>' . $rst->fields['username'] . '</td>';
        $file_rows .= '<td class=widget_content>' . $con->userdate($rst->fields['entered_at']) . '</td>';
        $file_rows .= '</tr>';
        $rst->movenext();
    }
    $rst->close();
}

// we should allow users to delete this contact if there are others

$sql = "select count(contact_id) as contact_count from contacts where company_id = $company_id and contact_record_status = 'a'";
$rst = $con->execute($sql);
if ($rst) {
    $contact_count = $rst->fields['contact_count'];
    $rst->close();
}

$sql = "select username, user_id from users where user_record_status = 'a' order by username";
$rst = $con->execute($sql);
if ($rst) {
    $user_menu = $rst->getmenu2('user_id', $session_user_id, false);
    $rst->close();
}

$sql = "select activity_type_pretty_name, activity_type_id from activity_types where activity_type_record_status = 'a'";
$rst = $con->execute($sql);
if ($rst) {
    $activity_type_menu = $rst->getmenu2('activity_type_id', '', false);
    $rst->close();
}

add_audit_item($con, $session_user_id, 'view contact', 'contacts', $contact_id);

$con->close();

if (strlen($activity_rows) == 0) {
    $activity_rows = "<tr><td class=widget_content colspan=6>No activities</td></tr>";
}

if (strlen($opportunity_rows) == 0) {
    $opportunity_rows = "<tr><td class=widget_content colspan=4>No opportunities</td></tr>";
}

if (strlen($case_rows) == 0) {
    $case_rows = "<tr><td class=widget_content colspan=4>No cases</td></tr>";
}

if (strlen($categories) == 0) {
    $categories = "No categories";
}

if (strlen($note_rows) == 0) {
    $note_rows = "<tr><td class=widget_content colspan=4>No notes</td></tr>";
}

if (strlen($file_rows) == 0) {
    $file_rows = "<tr><td class=widget_content colspan=4>No files</td></tr>";
}

$page_title = $first_names . ' ' . $last_name;
start_page($page_title, true, $msg);

?>

<object classid="clsid:FB7199AB-79BF-11d2-8D94-0000F875C541" codetype="application/x-oleobject" id="objMessengerApp" width="0" height="0"></object>

<script language="javascript">
<!--

function openMsnSession(strIMAddress) {
    objMessengerApp.LaunchIMUI(strIMAddress);
}

function markComplete() {
    document.forms[0].activity_status.value = "c";
    document.forms[0].submit();
}

//-->
</script>

<table border=0 cellpadding=0 cellspacing=0 width=100%>
    <tr>
        <td class=lcol width=70% valign=top>

        <table class=widget cellspacing=1 width=100%>
            <tr>
                <td class=widget_header>Contact Details</td>
            </tr>
            <tr>
                <td class=widget_content>

                    <table border=0 cellpadding=0 cellspacing=0 width=100%>
                        <tr>
                            <td width=50% class=clear align=left valign=top>
                                <table border=0 cellpadding=0 cellspacing=0 width=100%>
                                <tr>
                                    <td width=1% class=sublabel>Name</td>
                                    <td class=clear><?php  echo $last_name . ', ' . $first_names; ?></td>
                                </tr>
                                <tr>
                                    <td class=sublabel>Summary</td>
                                    <td class=clear><?php  echo $summary; ?></td>
                                </tr>
                                <tr>
                                    <td class=sublabel>Title</td>
                                    <td class=clear><?php  echo $title; ?></td>
                                </tr>
                                <tr>
                                    <td class=sublabel>Description</td>
                                    <td class=clear><?php  echo $description; ?></td>
                                </tr>
                                <tr>
                                    <td class=sublabel>E-Mail</td>
                                    <td class=clear><a href='mailto:<?php echo $email . "'>" . htmlspecialchars($email); ?></a></td>
                                </tr>
                                <tr>
                                    <td class=sublabel>Work Phone</td>
                                    <td class=clear><?php  echo $work_phone;; ?></td>
                                </tr>
                                <tr>
                                    <td class=sublabel>Home Phone</td>
                                    <td class=clear><?php  echo $home_phone;; ?></td>
                                </tr>
                                <tr>
                                    <td class=sublabel>Cell Phone</td>
                                    <td class=clear><?php  echo $cell_phone;; ?></td>
                                </tr>
                                <tr>
                                    <td class=sublabel>Interests</td>
                                    <td class=clear><?php  echo $interests;; ?></td>
                                </tr>
                                <tr>
                                    <td class=sublabel>&nbsp;</td>
                                    <td class=clear>&nbsp;</td>
                                </tr>
                                <tr>
                                    <td class=sublabel>Yahoo! IM</td>
                                    <td class=clear>
                                    <?php if (strlen($yahoo_name) > 0) {echo("<a href='ymsgr:sendim?$yahoo_name'><img border=0 src='http://opi.yahoo.com/online?u=$yahoo_name&m=g&t=3'></a>");}; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class=sublabel>MSN IM</td>
                                    <td class=clear>
                                    <?php if (strlen($msn_name) > 0) {echo("<a href=\"javascript: openMsnSession('$msn_name');\">$msn_name</a>");}; ?></td>
                                </tr>
                                <tr>
                                    <td class=sublabel>AOL IM</td>
                                    <td class=clear>
                                    <?php if (strlen($aol_name) > 0) {echo("<a href='aim:goim?screenname=$aol_name'>$aol_name</a>");}; ?>
                                    </td>
                                </tr>
                                </table>

                            </td>

                            <td width=50% class=clear align=left valign=top>

                                <table border=0 cellpadding=0 cellspacing=0 width=100%>
                                <tr>
                                    <td class=sublabel>Company</td>
                                    <td class=clear><a href="<?php  echo $http_site_root?>/companies/one.php?company_id=<?php echo $company_id;; ?>"><?php echo $company_name; ?></a> (<?php echo $company_code;?>)</td>
                                </tr>
                                <tr>
                                    <td class=sublabel>Acct. Owner</td>
                                    <td class=clear><?php  echo $account_owner; ?></td>
                                </tr>
                                <tr>
                                    <td class=sublabel>CRM Status</td>
                                    <td class=clear><?php  echo $crm_status_display_html; ?></td>
                                </tr>
                                <tr>
                                    <td class=sublabel>Account Status</td>
                                    <td class=clear><?php  echo $account_status_display_html; ?></td>
                                </tr>
                                <tr>
                                    <td class=sublabel>&nbsp;</td>
                                    <td class=clear>&nbsp;</td>
                                </tr>
                                <tr>
                                    <td width=1% class=sublabel><?php  echo $contact_custom1_label; ?></td>
                                    <td class=clear><?php  echo $custom1; ?></td>
                                </tr>
                                <tr>
                                    <td class=sublabel><?php  echo $contact_custom2_label; ?></td>
                                    <td class=clear><?php  echo $custom2; ?></td>
                                </tr>
                                <tr>
                                    <td class=sublabel><?php  echo $contact_custom3_label; ?></td>
                                    <td class=clear><?php  echo $custom3; ?></td>
                                </tr>
                                <tr>
                                    <td class=sublabel><?php  echo $contact_custom4_label; ?></td>
                                    <td class=clear><?php  echo $custom4; ?></td>
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
                        </tr>
                    </table>

                    <p><?php  echo $profile; ?>

                </td>
            </tr>
            <tr>
                <td class=widget_content_form_element><input class=button type=button value="<?php  echo
$strCompaniesOneEditButton; ?>" onclick="javascript: location.href='edit.php?contact_id=<?php echo $contact_id; ?>';"></td>
            </tr>
        </table>

        <!-- activities //-->
        <form action="<?php  echo $http_site_root; ?>/activities/new-2.php" method=post>
        <input type=hidden name=return_url value="/contacts/one.php?contact_id=<?php  echo $contact_id; ?>">
        <input type=hidden name=company_id value="<?php echo $company_id; ?>">
        <input type=hidden name=contact_id value="<?php echo $contact_id; ?>">
        <input type=hidden name=activity_status value="o">
        <table class=widget cellspacing=1 width=100%>
            <tr>
                <td class=widget_header colspan=6>Activities</td>
            </tr>
            <tr>
                <td class=widget_label>Title</td>
                <td class=widget_label>User</td>
                <td class=widget_label>Type</td>
                <td class=widget_label>About</td>
                <td colspan=2 class=widget_label>On</td>
            </tr>
            <tr>
                <td class=widget_content_form_element><input type=text name=activity_title size=50></td>
                <td class=widget_content_form_element><?php  echo $user_menu; ?></td>
                <td class=widget_content_form_element><?php  echo $activity_type_menu; ?></td>
                <td class=widget_content_form_element>&nbsp;</td>
                <td colspan=2 class=widget_content_form_element><input type=text size=12 name=scheduled_at value="<?php echo date('Y-m-d'); ?>"> <input class=button type=submit value="Add"> <input class=button type=button onclick="javascript: markComplete();" value="Done"></td>
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
                <td class=widget_header>Categories</td>
            </tr>
            <tr>
                <td class=widget_content><?php  echo $categories; ?></td>
            </tr>
            <tr>
                <td class=widget_content_form_element><input type=button class=button onclick="javascript: location.href='categories.php?company_id=<?php  echo $company_id; ?>';" value="Manage"></td>
            </tr>
        </table>

        <!-- opportunities //-->
        <form action="<?php  echo $http_site_root; ?>/opportunities/new.php" method="post">
        <input type="hidden" name="company_id" value="<?php  echo $company_id; ?>">
        <input type="hidden" name="contact_id" value="<?php  echo $contact_id; ?>">
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
                <td class=widget_content_form_element colspan=4><input type=submit class=button value="New">
<input type=button class=button onclick="javascript: location.href='<?php  echo $http_site_root
?>/opportunities/some.php?company_code=<?php echo $company_code;; ?>';" value="More"></td>
            </tr>
        </table>
        </form>

        <!-- cases //-->
        <form action="<?php  echo $http_site_root; ?>/cases/new.php" method="post">
        <input type="hidden" name="company_id" value="<?php  echo $company_id; ?>">
        <input type="hidden" name="contact_id" value="<?php  echo $contact_id; ?>">
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
                <td class=widget_content_form_element colspan=5><input type=submit class=button value="New">
<input type=button class=button onclick="javascript: location.href='<?php  echo $http_site_root
?>/cases/some.php?company_code=<?php echo $company_code;; ?>';" value="More"></td>
            </tr>
        </table>
        </form>

        <!-- files //-->
        <form action="<?php  echo $http_site_root; ?>/files/new.php" method="post">
        <input type=hidden name=on_what_table value="contacts">
        <input type=hidden name=on_what_id value="<?php  echo $contact_id; ?>">
        <input type=hidden name=return_url value="/contacts/one.php?contact_id=<?php  echo $contact_id; ?>">
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

<?php end_page();; ?>
