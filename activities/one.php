<?php

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

$session_user_id = session_check();
require_once($include_directory . 'lang/' . $_SESSION['language'] . '.php');

$activity_id = $_GET['activity_id'];
$return_url = $_GET['return_url'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

update_recent_items($con, $session_user_id, "activities", $activity_id);

$sql = "select a.*, c.company_id, c.company_name, cont.first_names, cont.last_name
from companies c, activities a left join contacts cont on a.contact_id = cont.contact_id
where a.company_id = c.company_id
and activity_id = $activity_id";

$rst = $con->execute($sql);

if ($rst) {
    $activity_type_id = $rst->fields['activity_type_id'];
    $activity_title = $rst->fields['activity_title'];
    $activity_description = $rst->fields['activity_description'];
    $user_id = $rst->fields['user_id'];
    $company_id = $rst->fields['company_id'];
    $company_name = $rst->fields['company_name'];
    $contact_id = $rst->fields['contact_id'];
    $on_what_table = $rst->fields['on_what_table'];
    $on_what_id = $rst->fields['on_what_id'];
    $scheduled_at = $con->userdate($rst->fields['scheduled_at']);
    $ends_at = $con->userdate($rst->fields['ends_at']);
    $activity_status = $rst->fields['activity_status'];
    $rst->close();
}

// since the activity can be attached to many things -- a company, contact, opportunity, or case -- we need to figure
// out a way to display the link... this is probably less than perfect, but it works ok

if ($on_what_table == 'opportunities') {
    $attached_to_link = "<a href='$http_site_root/opportunities/one.php?opportunity_id=$on_what_id'>";
    $sql = "select opportunity_title as attached_to_name from opportunities where opportunity_id = $on_what_id";
} elseif ($on_what_table == 'cases') {
    $attached_to_link = "<a href='$http_site_root/cases/one.php?case_id=$on_what_id'>";
    $sql = "select case_title as attached_to_name from cases where case_id = $on_what_id";
} else {
    $attached_to_link = "N/A";
    $sql = "select * from companies where 1 = 2";
}

$rst = $con->execute($sql);

if ($rst) {
    $attached_to_name = $rst->fields['attached_to_name'];
    $attached_to_link .= $attached_to_name . "</a>";
    $rst->close();
}

$sql = "select username, user_id from users where user_record_status = 'a'";
$rst = $con->execute($sql);
$user_menu = $rst->getmenu2('user_id', $user_id, false);
$rst->close();

$sql = "select activity_type_pretty_name, activity_type_id from activity_types where activity_type_record_status = 'a' order by activity_type_pretty_name";
$rst = $con->execute($sql);
$activity_type_menu = $rst->getmenu2('activity_type_id', $activity_type_id, false);
$rst->close();

$sql = "select concat(first_names, ' ', last_name) as contact_name, contact_id from contacts where company_id = $company_id and contact_record_status = 'a'";
$rst = $con->execute($sql);
if ($rst) {
    $contact_menu = $rst->getmenu2('contact_id', $contact_id, true);
    $rst->close();
}

$con->close();

$page_title = $activity_title;
start_page($page_title, true, $msg);

?>

<script language="javascript" src="<?php  echo $http_site_root; ?>/js/calendar1.js"></script>

<table border=0 cellpadding=0 cellspacing=0 width=100%>
    <tr>
        <td class=lcol width=45% valign=top>

        <form action=edit-2.php method=post>
        <input type=hidden name=return_url value="<?php  echo $return_url; ?>">
        <input type=hidden name=current_activity_status value="<?php  echo $activity_status; ?>">
        <input type=hidden name=activity_id value="<?php  echo $activity_id; ?>">
        <table class=widget cellspacing=1 width=100%>
            <tr>
                <td class=widget_header colspan=2>About This Activity</td>
            </tr>
            <tr>
                <td class=widget_label_right>Company</td>
                <td class=widget_content><a href="../companies/one.php?company_id=<?php echo $company_id; ?>"><?php  echo $company_name; ?></a></td>
            </tr>
            <tr>
                <td class=widget_label_right>Contact</td>
                <td class=widget_content><?php echo $contact_menu; ?></td>
            </tr>
            <tr>
                <td class=widget_label_right>Attached&nbsp;To</td>
                <td class=widget_content><?php  echo $attached_to_link; ?></td>
            </tr>
            <tr>
                <td class=widget_label_right>Activity&nbsp;Type</td>
                <td class=widget_content_form_element><?php  echo $activity_type_menu; ?></td>
            </tr>
            <tr>
                <td class=widget_label_right>Title</td>
                <td class=widget_content_form_element><input type=text size=50 name=activity_title value="<?php  echo $activity_title; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right>User</td>
                <td class=widget_content_form_element><?php  echo $user_menu; ?></td>
            </tr>
            <tr>
                <td class=widget_label_right_166px>Description</td>
                <td class=widget_content_form_element><textarea rows=10 cols=100 name=activity_description><?php  echo $activity_description; ?></textarea></td>
            </tr>
            <tr>
                <td class=widget_label_right>Starts</td>
                <td class=widget_content_form_element><input type=text name=scheduled_at value="<?php  echo $scheduled_at; ?>"> <a href="javascript:cal1.popup();"><img class=date_picker border=0 src="../img/cal.gif"></a></td>
            </tr>
            <tr>
                <td class=widget_label_right>Ends</td>
                <td class=widget_content_form_element><input type=text name=ends_at value="<?php  echo $ends_at; ?>"> <a href="javascript:cal2.popup();"><img class=date_picker border=0 src="../img/cal.gif"></a></td>
            </tr>
            <tr>
                <td class=widget_label_right>Completed?</td>
                <td class=widget_content_form_element><input type=checkbox name=activity_status value='on' <?php if ($activity_status == 'c') {print "checked";}; ?>></td>
            </tr>
            <tr>
                <td class=widget_content_form_element colspan=2>
                <input class=button type=submit value="Save Changes">
                <input type=button class=button onclick="javascript: location.href='delete.php?activity_id=<?php echo $activity_id; ?>&return_url=<?php echo urlencode($return_url); ?>';" value='Delete Activity' onclick="javascript: return confirm('Delete Activity?');"></td>
            </tr>
        </table>
        </form>

        </td>
        <!-- gutter //-->
        <td class=gutter width=2%>
        &nbsp;
        </td>
        <!-- right column //-->
        <td class=rcol width=53% valign=top>

        &nbsp;

        </td>
    </tr>
</table>

<script language="javascript">
<!--

// create calendar object(s) just after form tag closed
// specify form element as the only parameter (document.forms['formname'].elements['inputname']);
// note: you can have as many calendar objects as you need for your application

    var cal1 = new calendar1(document.forms[0].elements['scheduled_at']);
    cal1.year_scroll = false;
    cal1.time_comp = false;

    var cal2 = new calendar1(document.forms[0].elements['ends_at']);
    cal2.year_scroll = false;
    cal2.time_comp = false;

//-->
</script>

<?php end_page();; ?>
