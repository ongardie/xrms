<?php
/**
 * One Contact Page
 *
 * This page allows for the viewing of the details for a single contact.
 *
 * $Id: one.php,v 1.20 2004/04/27 15:12:59 gpowers Exp $
 */
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
c.company_id, company_name, company_code,
line1, line2, addresses.city, province, addresses.postal_code, address_body, use_pretty_address, iso_code3, address_format_string,
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
    $division_id  = $rst->fields['division_id'];
    $crm_status_display_html = $rst->fields['crm_status_display_html'];
    $account_status_display_html = $rst->fields['account_status_display_html'];
    $account_owner = $rst->fields['account_owner'];
    $last_name = $rst->fields['last_name'];
    $first_names = $rst->fields['first_names'];
    $salutation = $rst->fields['salutation'];
    $date_of_birth = $con->userdate($rst->fields['date_of_birth']);
    $gender = $rst->fields['gender'];
    $summary = $rst->fields['summary'];
    $title = $rst->fields['title'];
    $description = $rst->fields['description'];
    $profile = $rst->fields['profile'];
    $email = $rst->fields['email'];
    $work_phone = $rst->fields['work_phone'];
    $cell_phone = $rst->fields['cell_phone'];
    $home_phone = $rst->fields['home_phone'];
    $fax = $rst->fields['fax'];
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


switch ($gender) {
    case 'f':
        $gender = 'Female';
        break;
    case 'm':
        $gender = 'Male';
        break;
    case 'u':
        $gender = 'Unknown';
        break;
    default:
        $gender = 'Unknown';
        break;
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
                if(activity_status = 'o' and ends_at < now(), 1, 0) as is_overdue
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
            $sql2 = "select opportunity_title as attached_to_name
                    from opportunities
                    where opportunity_id = $on_what_id";
        } elseif ($on_what_table == 'cases') {
            $attached_to_link = "<a href='$http_site_root/cases/one.php?case_id=$on_what_id'>";
            $sql2 = "select case_title as attached_to_name from cases where case_id = $on_what_id";
        } else {
            $attached_to_link = "N/A";
            $sql2 = "select * from companies where 1 = 2";
        }

        $rst2 = $con->execute($sql2);

       if ($rst2) {
            $attached_to_name = $rst2->fields['attached_to_name'];
            $attached_to_link .= $attached_to_name . "</a>";
            $rst2->close();
        }

        $activity_rows .= '<tr>';
        $activity_rows .= "<td class='$classname'>
                                <a href='$http_site_root/activities/one.php?return_url=/contacts/one.php?contact_id=$contact_id&activity_id="
                                . $rst->fields['activity_id'] . "'>"
                                . $rst->fields['activity_title']
                                . '</a></td>';

        $activity_rows .= '<td class=' . $classname . '>' . $rst->fields['username'] . '</td>';
        $activity_rows .= '<td class=' . $classname . '>' . $rst->fields['activity_type_pretty_name'] . '</td>';
        $activity_rows .= '<td class=' . $classname . ">$attached_to_link</td>";
        $activity_rows .= '<td colspan=2 class=' . $classname . '>' . $con->userdate($rst->fields['scheduled_at']) . '</td>';
        $activity_rows .= '</tr>';
        $rst->movenext();
    }
    $rst->close();
}

// division
if ($division_id != '') {
    $division_sql = "select division_id, division_name from company_division where division_id = $division_id";
    $div_rst = $con->execute($division_sql);

    if ($div_rst) {
        $division_row .= '<input type=hidden name=division_id value='.$div_rst->fields['division_id'].'>'
                       .  $div_rst->fields['division_name'];
    }
} //end division select

// associated with
$categories_sql = "select category_display_html
        from categories c, category_scopes cs, category_category_scope_map ccsm, entity_category_map ecm
        where ecm.on_what_table = 'contacts'
        and ecm.on_what_id = $contact_id
        and ecm.category_id = c.category_id
        and cs.category_scope_id = ccsm.category_scope_id
        and c.category_id = ccsm.category_id
        and cs.on_what_table = 'contacts'
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
$on_what_table = 'contacts';
$on_what_id = $contact_id;
$on_what_string = 'contact';

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

// we should allow users to delete this contact if there are others
/*
$sql = "select count(contact_id) as contact_count from contacts where company_id = $company_id and contact_record_status = 'a'";
$rst = $con->execute($sql);
if ($rst) {
    $contact_count = $rst->fields['contact_count'];
    $rst->close();
}
*/

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

if (strlen($categories) == 0) {
    $categories = "No categories";
}

$page_title = $first_names . ' ' . $last_name;
start_page($page_title, true, $msg);

?>

<object classid="clsid:FB7199AB-79BF-11d2-8D94-0000F875C541" codetype="application/x-oleobject" id="objMessengerApp" width="0" height="0"></object>

<script language="JavaScript" type="text/javascript">
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

<div id="Main">
    <div id="Content">

        <table class=widget cellspacing=1>
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
                                    <td class=clear><?php  echo $last_name . ', ' . $salutation . ' ' . $first_names; ?></td>
                                </tr>
                                <tr>
                                    <td class=sublabel>Gender</td>
                                    <td class=clear><?php  echo $gender; ?></td>
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
                                    <td class=sublabel>Date of Birth</td>
                                    <td class=clear><?php  echo $date_of_birth; ?></td>
                                </tr>
                                <tr>
                                    <td class=sublabel>E-Mail</td>
                                    <td class=clear><a href='mailto:<?php echo $email . "' onclick=\"location.href='../activities/new-2.php?user_id=$session_user_id&activity_type_id=3&on_what_id=$contact_id&contact_id=$contact_id&company_id=$company_id&email=$email&return_url=/contacts/one.php?contact_id=$contact_id'\" >" . htmlspecialchars($email); ?></a></td>
                                </tr>
                                <tr>
                                    <td class=sublabel>Work Phone</td>
                                    <td class=clear><?php  echo $work_phone; ?></td>
                                </tr>
                                <tr>
                                    <td class=sublabel>Home Phone</td>
                                    <td class=clear><?php  echo $home_phone; ?></td>
                                </tr>
                                <tr>
                                    <td class=sublabel>Cell Phone</td>
                                    <td class=clear><?php  echo $cell_phone; ?></td>
                                </tr>
                                <tr>
                                    <td class=sublabel>Fax</td>
                                    <td class=clear><?php  echo $fax; ?></td>
                                </tr>
                                <tr>
                                    <td class=sublabel>Interests</td>
                                    <td class=clear><?php  echo $interests; ?></td>
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
                                    <td class=sublabel>Division</td>
                                    <td class=clear><?php  echo $division_row; ?></td>
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
                                    <td class=sublabel>Address</td>
                                    <td class=clear><?php echo $address_to_display ?></td>
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
                <td class=widget_content_form_element><input class=button type=button value="<?php  echo $strCompaniesOneEditButton; ?>" onclick="javascript: location.href='edit.php?contact_id=<?php echo $contact_id; ?>';"></td>
            </tr>
        </table>
     <script language="javascript" src="<?php  echo $http_site_root; ?>/js/calendar1.js"></script>
        <!-- activities //-->
        <form action="<?php  echo $http_site_root; ?>/activities/new-2.php" method=post>
        <input type=hidden name=return_url value="/contacts/one.php?contact_id=<?php  echo $contact_id; ?>">
        <input type=hidden name=company_id value="<?php echo $company_id; ?>">
        <input type=hidden name=contact_id value="<?php echo $contact_id; ?>">
        <input type=hidden name=activity_status value="o">
        <table class=widget cellspacing=1>
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
                <td class=widget_content_form_element><input type=text name=activity_title></td>
                <td class=widget_content_form_element><?php  echo $user_menu; ?></td>
                <td class=widget_content_form_element><?php  echo $activity_type_menu; ?></td>
                <td class=widget_content_form_element>&nbsp;</td>
                <td colspan=2 class=widget_content_form_element><input type=text size=12 name=scheduled_at value="<?php echo date('Y-m-d H:i:s'); ?>">               <a href="javascript:cal1.popup();"><img class=date_picker border=0 src="../img/cal.gif"></a>
<input class=button type=submit value="Add"> <input class=button type=button onclick="javascript: markComplete();" value="Done"></td>
            </tr>
            <?php  echo $activity_rows; ?>
        </table>
        </form>

    </div>

        <!-- right column //-->
    <div id="Sidebar">

        <!-- categories //-->
        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header>Categories</td>
            </tr>
            <tr>
                <td class=widget_content><?php  echo $categories; ?></td>
            </tr>
            <tr>
                <td class=widget_content_form_element><input type=button class=button onclick="javascript: location.href='categories.php?contact_id=<?php  echo $contact_id; ?>';" value="Manage"></td>
            </tr>
        </table>

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

</script>

<?php

end_page();

/**
 * $Log: one.php,v $
 * Revision 1.20  2004/04/27 15:12:59  gpowers
 * added support for activity times
 *
 * Revision 1.19  2004/04/19 22:19:54  maulani
 * - Adjust table for CSS2 positioning
 *
 * Revision 1.18  2004/04/17 16:03:45  maulani
 * - Add CSS2 positioning
 *
 * Revision 1.17  2004/04/16 22:20:55  maulani
 * - Add CSS2 positioning
 *
 * Revision 1.16  2004/04/12 16:20:38  maulani
 * - Fix bug where check was erroneously made for $rst instead of $rst2
 *
 * Revision 1.15  2004/04/10 16:25:29  braverock
 * - add calendar pop-up to new activity
 *   - apply SF patch 927141 submitted by "s-t"
 *
 * Revision 1.14  2004/03/07 14:07:57  braverock
 * - use centralized side-bar code in advance of i18n conversion
 *
 * Revision 1.13  2004/02/06 22:47:37  maulani
 * Use ends_at to determine if activity is overdue
 *
 * Revision 1.12  2004/01/26 19:13:34  braverock
 * - added company division fields
 * - added phpdoc
 */
?>
