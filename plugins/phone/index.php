<?php
/**
 * Search for and Display Multiple Contacts
 *
 * This is the main interface for locating Contacts in XRMS
 *
 * $Id: index.php,v 1.4 2005/08/28 18:12:43 braverock Exp $
 */

//include the standard files
require_once('../../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb/adodb-phone-pager.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();

// get call arguments
if ( isset($_GET['msg']) ) {
    $msg = $_GET['msg'];
} else {
    $msg = '';
}

if ( isset($_POST['offset']) ) {
    $offset = $_POST['offset'];
} else {
    $offset = '';
}

if ( isset($_GET['clear']) ) {
    $clear = ($_GET['clear'] == 1) ? 1 : 0;
} else {
    $clear = 0;
}

if ( isset($_POST['use_post_vars']) ) {
    $use_post_vars = ($_POST['use_post_vars'] == 1) ? 1 : 0;
} else {
    $use_post_vars = 0;
}

if ( isset($_POST['resort']) ) {
    $resort = $_POST['resort'];
} else {
    $resort = '';
}

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

$ascending_order_image = ' <img border=0 height=10 width=10 src="../img/asc.gif" alt="">';
$descending_order_image = ' <img border=0 height=10 width=10 src="../img/desc.gif" alt="">';
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
$_SESSION['category_id'] = $category_id;
$_SESSION['contacts_user_id'] = $user_id;

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
// $con->debug = 1;
// $con->execute("update users set last_hit = " . $con->dbtimestamp(mktime()) . " where user_id = $session_user_id");


$sql = "SELECT " . $con->Concat("'<a href=\"contacts_one.php?contact_id='", "cont.contact_id", "'\">'", "cont.last_name", "', '", "cont.first_names", "'</a>'") . " AS 'Name', "
       . $con->Concat("'<a href=\"companies_one.php?company_id='", "c.company_id", "'\">'", "c.company_name", "'</a>'") . " AS 'Company',
         cont.work_phone AS 'Phone' ";
   //    company_code AS 'Code',
    //   title AS 'Title',
     //  description AS 'Description',
      // u.username AS 'Owner' ";

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
    $where .= " and c.company_name like " . $con->qstr(company_search_string($company_name), get_magic_quotes_gpc());
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
        $recently_viewed_table_rows .= '<td class=widget_content><a href="one.php?contact_id=' . $rst->fields['contact_id'] . '">' . $rst->fields['first_names'] . ' ' . $rst->fields['last_name'] . '</a></td>';
        $recently_viewed_table_rows .= '<td class=widget_content>' . $rst->fields['company_name'] . '</td>';
        $recently_viewed_table_rows .= '<td class=widget_content>' . $rst->fields['work_phone'] . '</td>';
        $recently_viewed_table_rows .= '</tr>'."\n";
        $rst->movenext();
    }
    $rst->close();
}

if (strlen($recently_viewed_table_rows) == 0) {
    $recently_viewed_table_rows = "\n".'<tr><td class=widget_content colspan=4>'._("No recently viewed contacts").'</td></tr>'."\n";
}

$user_menu = get_user_menu($con, $user_id, true);

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
    add_audit_item($con, $session_user_id, 'searched', 'contacts', '', 4);
}

$page_title = 'Contacts';
//start_page($page_title, true, $msg);

?>
<html>
<title><?=$page_title?></title>
<body>
<div id="Main">
    <div id="Content">

        <form action=index.php method=post>
        <input type=hidden name=use_post_vars value=1>
        <input type=hidden name=contacts_next_page value="<?php  echo $contacts_next_page; ?>">
        <input type=hidden name=resort value="0">
        <input type=hidden name=current_sort_column value="<?php  echo $sort_column; ?>">
        <input type=hidden name=sort_column value="<?php  echo $sort_column; ?>">
        <input type=hidden name=current_sort_order value="<?php  echo $sort_order; ?>">
        <input type=hidden name=sort_order value="<?php  echo $sort_order; ?>">
        <table class=widget cellspacing=1 width="100%">
            <tr>
                <td class=widget_header colspan=8>Search Criteria</td>
            </tr>
            <tr>
                <td class=widget_label>Last Name</td>
        </tr>
        <tr>
        <td class=widget_content_form_element><input type=text name="last_name" size=18 maxlength=100 value="<?php  echo $last_name; ?>"></td>
        </tr>
        <tr>
                <td class=widget_label>First Names</td>
        </tr>
        <tr>
            <td class=widget_content_form_element><input type=text name="first_names" size=12 maxlength=100 value="<?php  echo $first_names; ?>"></td>
        </tr>
        <!--<tr>
                <td class=widget_label>Title</td>
        </tr>
        <tr>
            <td class=widget_content_form_element><input type=text name="title" size=12 maxlength=100 value="<?php  echo $title; ?>"></td>
        </tr>-->
        <tr>
                <td class=widget_label>Company</td>
        </tr>
        <tr>
            <td class=widget_content_form_element><input type=text name="company_name" size=18 maxlength=100 value="<?php  echo $company_name; ?>"></td>
        </tr>
<!--        <tr>
                <td class=widget_label>Code</td>
        </tr>
        <tr>
            <td width="25%" class=widget_content_form_element>
<input type=text name="company_code" size=4 maxlength=10 value="<?php  echo $company_code; ?>"></td>
    </tr>-->
    <!--<tr>
                <td class=widget_label>Description</td>
        </tr>
        <tr>
            <td width="25%" class=widget_content_form_element>
<input type=text name="description" size=12 maxlength=100 value="<?php  echo $description; ?>"></td>
    </tr>-->
    <tr>
                <td class=widget_label>Category</td>
        </tr>
        <tr>
            <td width="25%" class=widget_content_form_element>
                <?php  echo $contact_category_menu; ?>
            </td>
    </tr>
    <!--<tr>
                <td class=widget_label>Owner</td>
        </tr>
        <tr>
            <td width="25%" class=widget_content_form_element>
                <?php  echo $user_menu; ?>
            </td>
        </tr>-->
        <tr>
          <td class=widget_content_form_element colspan=4><input name="submit" type=submit class=button value="Search">
            <input name="button" type=button class=button onClick="javascript: clearSearchCriteria();" value="Clear Search">
            <?php if ($company_count > 0) {print "<input class=button type=button onclick='javascript: bulkEmail()' value='Bulk E-Mail'>";}; ?>
          </td>
        </tr>
        </table>
        </form>

<?php

$pager = new ADODB_Phone_Pager($con, $sql, 'contacts', false, $sort_column-1, $pretty_sort_order);
$pager->render($rows_per_page=$system_rows_per_page);
$con->close();

?>

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
    location.href = "index.php?clear=1";
}

function exportIt() {
    document.forms[0].action = "export.php";
    document.forms[0].submit();
    // reset the form so that post-export searches work
    document.forms[0].action = "index.php";
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
 * $Log: index.php,v $
 * Revision 1.4  2005/08/28 18:12:43  braverock
 * - fix localization
 * - fix colspan on recently viewed
 *
 * Revision 1.3  2005/08/05 21:59:19  vanmer
 * - added search string function for companies search
 *
 * Revision 1.2  2005/03/21 13:40:57  maulani
 * - Remove redundant code by centralizing common user menu call
 *
 * Revision 1.1  2004/08/23 01:44:58  d2uhlman
 * very basic screens to access contact, company, search by phone plugin, need feedback, no entry possible yet
 *
 * Revision 1.20  2004/07/10 13:02:52  braverock
 * - applied undefined variables patch
 *   - applies SF patch 976204 submitted by cpsource
 *
 * Revision 1.19  2004/07/09 18:43:33  introspectshun
 * - Removed CAST(x AS CHAR) for wider database compatibility
 * - The modified MSSQL driver overrides the default Concat function to cast all datatypes as strings
 *
 * Revision 1.18  2004/06/26 15:42:57  braverock
 * - change search layout to two rows to improve CSS positioning
 *   - applied modified version of SF patch #971474 submitted by s-t
 *
 * Revision 1.17  2004/06/20 19:44:22  braverock
 * - change CAST to CAST as CHAR for broader compatibility
 *
 * Revision 1.16  2004/06/15 17:26:21  introspectshun
 * - Add adodb-params.php include for multi-db compatibility.
 * - Corrected order of arguments to implode() function.
 * - Now use ADODB GetInsertSQL, GetUpdateSQL and Concat functions.
 *
 * Revision 1.15  2004/05/13 12:07:40  braverock
 * - fix a category_id bug
 *   - fixes SF bug 952536
 *
 * Revision 1.14  2004/05/10 13:07:22  maulani
 * - Add level to audit trail
 * - Clean up audit trail text
 *
 * Revision 1.13  2004/04/20 12:32:43  braverock
 * - add export function for contacts
 *   - apply SF patch 938388 submitted by frenchman
 *
 * Revision 1.12  2004/04/18 14:29:46  braverock
 * - change display to show last name before first name
 *   - in response to SF patch 926962 submitted by Glenn Powers
 *
 * Revision 1.11  2004/04/15 22:04:39  maulani
 * - Change to CSS2 positioning
 * - Clean HTML to achieve validation
 *
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
