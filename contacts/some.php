<?php
/**
 * Search for and Display Multiple Contacts
 *
 * This is the main interface for locating Contacts in XRMS
 *
 * $Id: some.php,v 1.10 2004/04/12 16:24:41 maulani Exp $
 */

//include the standard files
require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb/adodb-pager.inc.php');

$session_user_id = session_check();
$msg = $_GET['msg'];
$offset = $_POST['offset'];
$clear = ($_GET['clear'] == 1) ? 1 : 0;
$use_post_vars = ($_POST['use_post_vars'] == 1) ? 1 : 0;
$resort = $_POST['resort'];

if ($clear) {
    $sort_column = '';
    $current_sort_column = '';
    $sort_order = '';
    $current_sort_order = '';
    $last_name = '';
    $first_names = '';
    $title = '';
    $description = '';
    $company_name = '';
    $company_code = '';
    $company_type_id = '';
    $category_id = '';
    $user_id = '';
} elseif ($use_post_vars) {
    $sort_column = $_POST['sort_column'];
    $current_sort_column = $_POST['current_sort_column'];
    $sort_order = $_POST['sort_order'];
    $current_sort_order = $_POST['current_sort_order'];
    $last_name = $_POST['last_name'];
    $first_names = $_POST['first_names'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $company_name = $_POST['company_name'];
    $company_code = $_POST['company_code'];
    $company_type_id = $_POST['company_type_id'];
    $category_id = $_POST['category_id'];
    $user_id = $_POST['user_id'];
} else {
    $sort_column = $_SESSION['contacts_sort_column'];
    $current_sort_column = $_SESSION['contacts_current_sort_column'];
    $sort_order = $_SESSION['contacts_sort_order'];
    $current_sort_order = $_SESSION['contacts_current_sort_order'];
    $last_name = $_SESSION['contacts_last_name'];
    $first_names = $_SESSION['contacts_first_names'];
    $title = $_SESSION['contacts_title'];
    $description = $_SESSION['contacts_description'];
    $company_name = (strlen($_GET['company_name']) > 0) ? $_GET['company_name'] : $_SESSION['contacts_company_name'];
    $company_code = (strlen($_GET['company_code']) > 0) ? $_GET['company_code'] : $_SESSION['contacts_company_code'];
    $company_type_id = $_SESSION['contacts_company_type_id'];
    $category_id = $_SESSION['category_id'];
    $user_id = $_SESSION['contacts_user_id'];
}

if (!strlen($sort_column) > 0) {
    $sort_column = 1;
    $current_sort_column = $sort_column;
    $sort_order = "asc";
}

if (!($sort_column == $current_sort_column)) {
    $sort_order = "asc";
}

$opposite_sort_order = ($sort_order == "asc") ? "desc" : "asc";
$sort_order = (($resort) && ($current_sort_column == $sort_column)) ? $opposite_sort_order : $sort_order;

$ascending_order_image = " <img border=0 height=10 width=10 src=../img/asc.gif>";
$descending_order_image = " <img border=0 height=10 width=10 src=../img/desc.gif>";
$pretty_sort_order = ($sort_order == "asc") ? $ascending_order_image : $descending_order_image;

$_SESSION['contacts_sort_column'] = $sort_column;
$_SESSION['contacts_current_sort_column'] = $sort_column;
$_SESSION['contacts_sort_order'] = $sort_order;
$_SESSION['contacts_current_sort_order'] = $sort_order;
$_SESSION['contacts_company_name'] = $company_name;
$_SESSION['contacts_company_code'] = $company_code;
$_SESSION['contacts_last_name'] = $last_name;
$_SESSION['contacts_first_names'] = $first_names;
$_SESSION['contacts_title'] = $title;
$_SESSION['contacts_description'] = $description;
$_SESSION['category_id'] = $description;
$_SESSION['contacts_user_id'] = $user_id;

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
// $con->debug = 1;
// $con->execute("update users set last_hit = " . $con->dbtimestamp(mktime()) . " where user_id = $session_user_id");


$sql = "select concat('<a href=one.php?contact_id=', cont.contact_id, '>', cont.first_names, ' ', cont.last_name, '</a>') as 'Name',
               concat('<a href=../companies/one.php?company_id=', c.company_id, '>', c.company_name, '</a>') as 'Company',
               company_code as 'Code',
               title as 'Title',
               description as 'Description',
               u.username as 'Owner' ";

$from = "from contacts cont, companies c, users u ";

$where .= "where c.company_id = cont.company_id ";
$where .= "and c.user_id = u.user_id ";
$where .= "and contact_record_status = 'a'";

$criteria_count = 0;

if (strlen($last_name) > 0) {
    $criteria_count++;
    $where .= " and cont.last_name like " . $con->qstr('%' . $last_name . '%', get_magic_quotes_gpc());
}

if (strlen($first_names) > 0) {
    $criteria_count++;
    $where .= " and cont.first_names like " . $con->qstr('%' . $first_names . '%', get_magic_quotes_gpc());
}

if (strlen($title) > 0) {
    $criteria_count++;
    $where .= " and cont.title like " . $con->qstr($title . '%', get_magic_quotes_gpc());
}

if (strlen($description) > 0) {
    $criteria_count++;
    $where .= " and cont.description like " . $con->qstr($description . '%', get_magic_quotes_gpc());
}

if (strlen($company_name) > 0) {
    $criteria_count++;
    $where .= " and c.company_name like " . $con->qstr($company_name . '%', get_magic_quotes_gpc());
}

if (strlen($company_code) > 0) {
    $criteria_count++;
    $where .= " and c.company_code like " . $con->qstr($company_code, get_magic_quotes_gpc());
}

if (strlen($category_id) > 0) {
    $criteria_count++;
    $from .= ", entity_category_map ecm ";
    $where .= " and ecm.on_what_table = 'contacts' and cont.contact_id = ecm.on_what_id and ecm.category_id = $category_id ";

}

if (strlen($user_id) > 0) {
    $criteria_count++;
    $where .= " and c.user_id = $user_id";
}

if (!$use_post_vars && (!$criteria_count > 0)) {
    $where .= " and 1 = 2";
}

$group_by .= " group by contact_id";

if ($sort_column == 1) {
    $order_by = "cont.last_name";
} elseif ($sort_column == 2) {
    $order_by = "c.company_name";
} else {
    $order_by = $sort_column;
}

$order_by .= " $sort_order";

$sql .= $from . $where . $group_by . " order by $order_by";

$sql_recently_viewed = "select * from recent_items r, contacts cont, companies c
where r.user_id = $session_user_id
and r.on_what_table = 'contacts'
and c.company_id = cont.company_id
and r.on_what_id = cont.contact_id
and contact_record_status = 'a'
order by r.recent_item_timestamp desc";

$rst = $con->selectlimit($sql_recently_viewed, $recent_items_limit);

if ($rst) {
    while (!$rst->EOF) {
        $recently_viewed_table_rows .= '<tr>';
        $recently_viewed_table_rows .= '<td class=widget_content><a href=one.php?contact_id=' . $rst->fields['contact_id'] . '>' . $rst->fields['first_names'] . ' ' . $rst->fields['last_name'] . '</a></td>';
        $recently_viewed_table_rows .= '<td class=widget_content>' . $rst->fields['company_name'] . '</td>';
        $recently_viewed_table_rows .= '<td class=widget_content>' . $rst->fields['work_phone'] . '</td>';
        $recently_viewed_table_rows .= '</tr>';
        $rst->movenext();
    }
    $rst->close();
}

if (strlen($recently_viewed_table_rows) == 0) {
    $recently_viewed_table_rows = '<tr><td class=widget_content colspan=5>No recently viewed contacts</td></tr>';
}

$sql2 = "select username, user_id from users where user_record_status = 'a' order by username";
$rst = $con->execute($sql2);
$user_menu = $rst->getmenu2('user_id', $user_id, true);
$rst->close();

$sql_category = "select category_pretty_name, c.category_id
from categories c, category_scopes cs, category_category_scope_map ccsm
where c.category_id = ccsm.category_id
and cs.on_what_table =  'contacts'
and ccsm.category_scope_id = cs.category_scope_id
and category_record_status =  'a'
order by category_pretty_name";
$rst = $con->execute($sql_category);
$contact_category_menu = $rst->getmenu2('category_id', $category_id, true);
$rst->close();

if ($criteria_count > 0) {
    add_audit_item($con, $session_user_id, 'search contacts', '', '');
}

$page_title = 'Contacts';
start_page($page_title, true, $msg);

?>

<div id="Main">
    <div id="Content">

        <form action=some.php method=post>
        <input type=hidden name=use_post_vars value=1>
        <input type=hidden name=contacts_next_page value="<?php  echo $contacts_next_page; ?>">
        <input type=hidden name=resort value="0">
        <input type=hidden name=current_sort_column value="<?php  echo $sort_column; ?>">
        <input type=hidden name=sort_column value="<?php  echo $sort_column; ?>">
        <input type=hidden name=current_sort_order value="<?php  echo $sort_order; ?>">
        <input type=hidden name=sort_order value="<?php  echo $sort_order; ?>">
        <table class=widget cellspacing=1 width=100%>
            <tr>
                <td class=widget_header colspan=8>Search Criteria</td>
            </tr>
            <tr>
                <td class=widget_label>Last Name</td>
                <td class=widget_label>First Names</td>
                <td class=widget_label>Title</td>
                <td class=widget_label>Company</td>
                <td class=widget_label>Code</td>
                <td class=widget_label>Description</td>
                <td class=widget_label>Category</td>
                <td class=widget_label>Owner</td>
            </tr>
            <tr>
                <td class=widget_content_form_element><input type=text name="last_name" size=9 maxlength=100 value="<?php  echo $last_name; ?>"></td>
                <td class=widget_content_form_element><input type=text name="first_names" size=9 maxlength=100 value="<?php  echo $first_names; ?>"></td>
                <td class=widget_content_form_element><input type=text name="title" size=4 maxlength=100 value="<?php  echo $title; ?>"></td>
                <td class=widget_content_form_element><input type=text name="company_name" size=9 maxlength=100 value="<?php  echo $company_name; ?>"></td>
                <td class=widget_content_form_element><input type=text name="company_code" size=4 maxlength=10 value="<?php  echo $company_code; ?>"></td>
                <td class=widget_content_form_element><input type=text name="description" size=4 maxlength=100 value="<?php  echo $description; ?>"></td>
                <td class=widget_content_form_element><?php  echo $contact_category_menu; ?></td>
                <td class=widget_content_form_element><?php  echo $user_menu; ?></td>
            </tr>
            <tr>
                <td class=widget_content_form_element colspan=8><input class=button type=submit value="Search"> <input class=button type=button onclick="javascript: clearSearchCriteria();" value="Clear Search"> <?php if ($company_count > 0) {print "<input class=button type=button onclick='javascript: bulkEmail()' value='Bulk E-Mail'>";}; ?> </td>
            </tr>
        </table>
        </form>

<?php

$pager = new ADODB_Pager($con, $sql, 'contacts', false, $sort_column-1, $pretty_sort_order);
$pager->render($rows_per_page=$system_rows_per_page);
$con->close();

?>

    </div>

        <!-- right column //-->
    <div id="Sidebar">
        <!-- recently viewed support items //-->
        <table class=widget cellspacing=1 width=100%>
            <tr>
                <td class=widget_header colspan=5>Recently Viewed</td>
            </tr>
            <tr>
                <td class=widget_label>Contact</td>
                <td class=widget_label>Company</td>
                <td class=widget_label>Work Phone</td>
            </tr>
            <?php  echo $recently_viewed_table_rows; ?>
        </table>

    </div>
</div>

<script language="JavaScript" type="text/javascript">
<!--

function initialize() {
    document.forms[0].last_name.focus();
}

initialize();

function bulkEmail() {
    document.forms[0].action = "../email/index.php";
    document.forms[0].submit();
}

function clearSearchCriteria() {
    location.href = "some.php?clear=1";
}

function submitForm(adodbNextPage) {
    document.forms[0].contacts_next_page.value = adodbNextPage;
    document.forms[0].submit();
}

function resort(sortColumn) {
    document.forms[0].sort_column.value = sortColumn + 1;
    document.forms[0].contacts_next_page.value = '';
    document.forms[0].resort.value = 1;
    document.forms[0].submit();
}

//-->
</script>

<?php

end_page();

/**
 * $Log: some.php,v $
 * Revision 1.10  2004/04/12 16:24:41  maulani
 * - Adjust sizing to fit screen in IE6
 *
 * Revision 1.9  2004/04/08 15:41:01  maulani
 * - Fix width problem
 *
 * Revision 1.8  2004/04/07 22:53:15  maulani
 * - Update layout to use CSS2
 * - Make HTML validate
 *
 * Revision 1.7  2004/03/18 12:48:42  braverock
 * - patch for Category search provided by Fontaine Consulting (France)
 *
 * Revision 1.6  2004/03/12 11:43:27  braverock
 * - added search for category_id
 *   - patch provided by Thibaut Midon (SF: tjm-fc)
 * - cleaned up some sql formatting to avoid line wrapping in some text editors
 *
 * Revision 1.5  2004/03/09 21:45:34  braverock
 * - added search for company code
 * - patch provided by Thibaut Midon (SF: tjm-fc)
 *
 */
?>