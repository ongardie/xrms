<?php

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

$session_user_id = session_check();
$msg = $_GET['msg'];

$case_id = $_GET['case_id'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
// $con->debug = 1;

update_recent_items($con, $session_user_id, "cases", $case_id);

$sql = "select * from cases where case_id = $case_id";

$rst = $con->execute($sql);

if ($rst) {
    $company_id = $rst->fields['company_id'];
    $contact_id = $rst->fields['contact_id'];
    $case_status_id = $rst->fields['case_status_id'];
    $case_priority_id = $rst->fields['case_priority_id'];
    $case_type_id = $rst->fields['case_type_id'];
    $user_id = $rst->fields['user_id'];
    $case_title = $rst->fields['case_title'];
    $case_description = $rst->fields['case_description'];
    $due_at = $con->userdate($rst->fields['due_at']);
    $rst->close();
}

$company_name = fetch_company_name($con, $company_id);


// associated with

/*

$sql = "select category_id, category_pretty_name
from categories
where category_record_status = 'a'
and category_id in (select ccsm.category_id from category_category_scope_map ccsm, category_scopes cs where ccsm.category_scope_id = cs.category_scope_id and cs.on_what_table = 'cases')
and category_id in (select category_id from entity_category_map where on_what_table = 'cases' and on_what_id = $case_id)
order by category_pretty_name";

*/

$sql = "select c.category_id, category_pretty_name
from categories c, category_scopes cs, category_category_scope_map ccsm, entity_category_map ecm
where ecm.on_what_table = 'cases'
and ecm.on_what_id = $case_id
and ecm.category_id = c.category_id
and cs.category_scope_id = ccsm.category_scope_id
and c.category_id = ccsm.category_id
and cs.on_what_table = 'cases'
and category_record_status = 'a'
order by category_pretty_name";

$rst = $con->execute($sql);
$array_of_categories = array();
array_push($array_of_categories, 0);

if ($rst) {
    while (!$rst->EOF) {
        $associated_with .= "<a href='remove-category.php?case_id=$case_id&category_id=" . $rst->fields['category_id'] . "'>" . $rst->fields['category_pretty_name'] . "</a><br>";
        array_push($array_of_categories, $rst->fields['category_id']);
        $rst->movenext();
    }
    $rst->close();
}

// not associated with

/*

$sql = "select category_id, category_pretty_name
from categories
where category_record_status = 'a'
and category_id in (select ccsm.category_id from category_category_scope_map ccsm, category_scopes cs where ccsm.category_scope_id = cs.category_scope_id and cs.on_what_table = 'cases')
and category_id not in (select category_id from entity_category_map where on_what_table = 'cases' and on_what_id = $case_id)
order by category_pretty_name";

*/

$sql = "select c.category_id, category_pretty_name
from categories c, category_scopes cs, category_category_scope_map ccsm
where cs.category_scope_id = ccsm.category_scope_id
and c.category_id = ccsm.category_id
and cs.on_what_table = 'cases'
and c.category_id not in (" . implode($array_of_categories, ',') . ")
and category_record_status = 'a'
order by category_pretty_name";

$rst = $con->execute($sql);

if ($rst) {
    while (!$rst->EOF) {
        $not_associated_with .= "<a href='add-category.php?case_id=$case_id&category_id=" . $rst->fields['category_id'] . "'>" . $rst->fields['category_pretty_name'] . "</a><br>";
        $rst->movenext();
    }
    $rst->close();
}

$sql = "select concat(first_names, ' ', last_name) as contact_name, contact_id from contacts where company_id = $company_id and contact_record_status = 'a'";
$rst = $con->execute($sql);
$contact_menu = $rst->getmenu2('contact_id', $contact_id, false);
$rst->close();

$sql = "select username, user_id from users where user_record_status = 'a' order by username";
$rst = $con->execute($sql);
$user_menu = $rst->getmenu2('user_id', $user_id, false);
$rst->close();

$sql2 = "select case_priority_pretty_name, case_priority_id from case_priorities where case_priority_record_status = 'a' order by case_priority_id";
$rst = $con->execute($sql2);
$case_priority_menu = $rst->getmenu2('case_priority_id', $case_priority_id, false);
$rst->close();

$sql2 = "select case_type_pretty_name, case_type_id from case_types where case_type_record_status = 'a' order by case_type_id";
$rst = $con->execute($sql2);
$case_type_menu = $rst->getmenu2('case_type_id', $case_type_id, false);
$rst->close();

$sql2 = "select case_status_pretty_name, case_status_id from case_statuses where case_status_record_status = 'a' order by case_status_id";
$rst = $con->execute($sql2);
$case_status_menu = $rst->getmenu2('case_status_id', $case_status_id, false);
$rst->close();

$con->close();

$page_title = "One Case : $case_title";
start_page($page_title, true, $msg);

?>

<script language="javascript" src="<?php  echo $http_site_root; ?>/js/calendar1.js"></script>

<table border=0 cellpadding=0 cellspacing=0 width=100%>
    <tr>
        <td class=lcol width=55% valign=top>

        <form action=edit-2.php onsubmit="javascript: return validate();" method=post>
        <input type=hidden name=case_id value=<?php  echo $case_id; ?>>
        <table class=widget cellspacing=1 width=100%>
            <tr>
                <td class=widget_header colspan=2>Case Details</td>
            </tr>
            <tr>
                <td class=widget_label_right>Case&nbsp;Title</td>
                <td class=widget_content_form_element><input type=text size=40 name=case_title value="<?php  echo $case_title; ?>"> <?php  echo $required_indicator; ?></td>
            </tr>
            <tr>
                <td class=widget_label_right>Company</td>
                <td class=widget_content_form_element><a href="<?php  echo $http_site_root; ?>/companies/one.php?company_id=<?php  echo $company_id; ?>"><?php  echo $company_name; ?></a></td>
            </tr>
            <tr>
                <td class=widget_label_right>Contact</td>
                <td class=widget_content_form_element><?php  echo $contact_menu; ?></td>
            </tr>
            <tr>
                <td class=widget_label_right>Type</td>
                <td class=widget_content_form_element><?php  echo $case_type_menu; ?></td>
            </tr>
            <tr>
                <td class=widget_label_right>Status</td>
                <td class=widget_content_form_element><?php  echo $case_status_menu; ?></td>
            </tr>
            <tr>
                <td class=widget_label_right>Priority</td>
                <td class=widget_content_form_element><?php  echo $case_priority_menu; ?></td>
            </tr>
            <tr>
                <td class=widget_label_right>Owner</td>
                <td class=widget_content_form_element><?php  echo $user_menu; ?></td>
            </tr>
            <tr>
                <td class=widget_label_right>Due By</td>
                <td class=widget_content_form_element><input type=text size=12 name=due_at value="<?php  echo $due_at; ?>">&nbsp;<a href="javascript:cal1.popup();"><img class=date_picker border=0 src="../img/cal.gif"></a></td>
            </tr>
            <tr>
                <td class=widget_label_right_166px>Description</td>
                <td class=widget_content_form_element><textarea rows=10 cols=100 name=case_description><?php  echo $case_description; ?></textarea></td>
            </tr>
            <tr>
                <td class=widget_content_form_element colspan=2><input class=button type=submit value="Save Changes"> <input type=button class=button onclick="javascript: location.href='delete.php?company_id=$company_id&contact_id=$contact_id';" value='Delete' onclick="javascript: return confirm('Delete Contact?');"></td>
            </tr>
        </table>
        </form>

        </td>
        <!-- gutter //-->
        <td class=gutter width=2%>
        &nbsp;
        </td>
        <!-- right column //-->
        <td class=rcol width=43% valign=top>

        </td>
    </tr>
</table>

<script language=javascript>

function initialize() {
    document.forms[0].case_title.focus();
}

function validate() {

    var numberOfErrors = 0;
    var msgToDisplay = '';

    if (document.forms[0].case_title.value == '') {
        numberOfErrors ++;
        msgToDisplay += '\nYou must enter a case title.';
    }

    if (numberOfErrors > 0) {
        alert(msgToDisplay);
        return false;
    } else {
        return true;
    }

}

initialize();
<!--

// create calendar object(s) just after form tag closed
// specify form element as the only parameter (document.forms['formname'].elements['inputname']);
// note: you can have as many calendar objects as you need for your application

    var cal1 = new calendar1(document.forms[0].elements['due_at']);
    cal1.year_scroll = true;
    cal1.time_comp = false;

//-->
</script>

<?php end_page(); ?>