<?php
/**
 * Search for and Display Multiple Contacts
 *
 * This is the main interface for locating Contacts in XRMS
 *
 * $Id: some.php,v 1.41 2005/01/06 17:14:43 braverock Exp $
 */

//include the standard files
require_once('include-locations-location.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once('pager.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();

// declare passed in variables
$arr_vars = array ( // local var name             // session variable name, flag
                    'sort_column'        => array ( 'contacts_sort_column', arr_vars_SESSION ),
                    'current_sort_column'=> array ( 'contacts_current_sort_column', arr_vars_SESSION ),
                    'sort_order'         => array ( 'contacts_sort_order', arr_vars_SESSION ),
                    'current_sort_order' => array ( 'contacts_current_sort_order', arr_vars_SESSION ),
                    'last_name'          => array ( 'contacts_last_name', arr_vars_SESSION ),
                    'first_names'        => array ( 'contacts_first_names', arr_vars_SESSION ),
                    'title'              => array ( 'contacts_title', arr_vars_SESSION ),
                    'description'        => array ( 'contacts_description', arr_vars_SESSION ),
                    'category_id'        => array ( 'category_id', arr_vars_SESSION ),
                    'user_id'            => array ( 'contacts_user_id', arr_vars_SESSION ),
                    'company_name'       => array ( 'contacts_company_name', arr_vars_GET_SESSION ),
                    'company_code'       => array ( 'contacts_company_code', arr_vars_GET_SESSION ),
                    'email'              => array ( 'contacts_email', arr_vars_GET_SESSION )
                   );

// get all passed in variables
arr_vars_get_all ( $arr_vars );

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

// set all session variables
arr_vars_session_set ( $arr_vars );

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
// $con->debug = 1;
// $con->execute("update users set last_hit = " . $con->dbtimestamp(mktime()) . " where user_id = $session_user_id");


$sql = "SELECT " . $con->Concat("'<a href=\"one.php?contact_id='", "cont.contact_id", "'\">'", "cont.last_name", "', '", "cont.first_names", "'</a>'") . " AS " . $con->qstr(_("Name"),get_magic_quotes_gpc())
       .' ,' . $con->Concat("'<a href=\"../companies/one.php?company_id='", "c.company_id", "'\">'", "c.company_name", "'</a>'") . " AS " . $con->qstr(_("Company"),get_magic_quotes_gpc());
if (strlen($email) > 0) {
    $sql .= "\n, cont.email AS " . $con->qstr(_("Email"),get_magic_quotes_gpc());
}
if (strlen($company_code) > 0) {
    $sql .= "\n, company_code AS " . $con->qstr(_("Code"),get_magic_quotes_gpc());
}
if (strlen($title) > 0) {
    $sql .= "\n, title AS " . $con->qstr(_("Title"),get_magic_quotes_gpc());
}
if (strlen($description) > 0) {
    $sql .= "\n, description AS " . $con->qstr(_("Description"),get_magic_quotes_gpc());
}

$sql .= "\n ,u.username AS " . $con->qstr(_("Owner"),get_magic_quotes_gpc());

$from = "from contacts cont, companies c, users u ";

$where  = "where c.company_id = cont.company_id ";
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

if (strlen($email) > 0) {
    $criteria_count++;
    $where .= " and cont.email like " . $con->qstr('%' . $email . '%', get_magic_quotes_gpc());
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

//gorup by shouldn't be needed, contact_id is already unique
//$group_by = " group by contact_id";

if ($sort_column == 1) {
    $order_by = "cont.last_name";
} elseif ($sort_column == 2) {
    $order_by = "c.company_name";
} else {
    $order_by = $sort_column;
}

if(strlen($last_name)) {
    $order_by .= ", (CASE WHEN (cont.last_name = " . $con->qstr($last_name, get_magic_quotes_gpc()) . ") THEN 0 ELSE 1 END) ";
}
if(strlen($first_names)) {
    $order_by .= ", (CASE WHEN (cont.first_names = " . $con->qstr($first_names, get_magic_quotes_gpc()) . ") THEN 0 ELSE 1 END) ";
}

$order_by .= " $sort_order";

$sql .= $from . $where . " order by $order_by";

$sql_recently_viewed = "select
cont.contact_id,
cont.first_names,
cont.last_name,
c.company_id,
c.company_name,
cont.address_id,
cont.work_phone,
max(r.recent_item_timestamp) as lasttime
from recent_items r, contacts cont, companies c
where r.user_id = $session_user_id
and r.on_what_table = 'contacts'
and r.recent_action = ''
and c.company_id = cont.company_id
and r.on_what_id = cont.contact_id
and contact_record_status = 'a'
group by cont.contact_id,
cont.first_names,
cont.last_name,
c.company_id,
c.company_name,
cont.address_id,
cont.work_phone
order by lasttime desc";

$rst = $con->selectlimit($sql_recently_viewed, $recent_items_limit);

$recently_viewed_table_rows = '';
if ($rst) {
    while (!$rst->EOF) {
        $recently_viewed_table_rows .= '<tr>';
        $recently_viewed_table_rows .= '<td class=widget_content><a href="one.php?contact_id=' . $rst->fields['contact_id'] . '">';
        $recently_viewed_table_rows .= $rst->fields['first_names'] . ' ' . $rst->fields['last_name'] . '</a></td>';
        $recently_viewed_table_rows .= '<td class=widget_content><a href="../companies/one.php?company_id=' . $rst->fields['company_id'] . '">' . $rst->fields['company_name'] . '</a></td>';
        $recently_viewed_table_rows .= '<td class=widget_content>' . get_formatted_phone($con, $rst->fields['address_id'], $rst->fields['work_phone']) . '</td>';
        $recently_viewed_table_rows .= '</tr>';
        $rst->movenext();
    }
    $rst->close();
}

if ( !$recently_viewed_table_rows ) {
    $recently_viewed_table_rows = '<tr><td class=widget_content colspan=5>' . _("No recently viewed contacts") . '</td></tr>';
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
    add_audit_item($con, $session_user_id, 'searched', 'contacts', '', 4);
}

$page_title = _("Contacts");
start_page($page_title, true, $msg);
if(!isset($contacts_next_page)) {
    $contacts_next_page = '';
}

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

        <table class=widget cellspacing=1 width="100%">
            <tr>
                <td class=widget_header colspan=8><?php echo _("Search Criteria"); ?></td>
            </tr>
            <tr>
                <td class=widget_label colspan="2"><?php echo _("Last Name"); ?></td>
                <td class=widget_label><?php echo _("First Names"); ?></td>
                <td class=widget_label><?php echo _("Title"); ?></td>
                <td class=widget_label><?php echo _("Company"); ?></td>
            </tr>
            <tr>
                <td width="25%" class=widget_content_form_element colspan="2">
                    <input type=text name="last_name" size=18 maxlength=100 value="<?php  echo $last_name; ?>">
                </td>
                <td width="25%" class=widget_content_form_element>
                    <input type=text name="first_names" size=12 maxlength=100 value="<?php  echo $first_names; ?>">
                </td>
                <td width="25%" class=widget_content_form_element>
                    <input type=text name="title" size=12 maxlength=100 value="<?php  echo $title; ?>">
                </td>
                <td width="25%" class=widget_content_form_element>
                    <input type=text name="company_name" size=18 maxlength=100 value="<?php  echo $company_name; ?>">
                </td>
            </tr>
            <tr>
                <td class=widget_label><?php echo _("Email"); ?></td>
                <td class=widget_label><?php echo _("Code"); ?></td>
                <td class=widget_label><?php echo _("Description"); ?></td>
                <td class=widget_label><?php echo _("Category"); ?></td>
                <td class=widget_label><?php echo _("Owner"); ?></td>
            </tr>
            <tr>
                <td width="25%" class=widget_content_form_element>
                    <input type=text name="email" size=12 maxlength=40 value="<?php  echo $email; ?>">
                </td>
                <td width="10%" class=widget_content_form_element>
                    <input type=text name="company_code" size=4 maxlength=10 value="<?php  echo $company_code; ?>">
                </td>
                <td width="25%" class=widget_content_form_element>
                    <input type=text name="description" size=12 maxlength=50 value="<?php  echo $description; ?>">
                </td>
                <td width="25%" class=widget_content_form_element>
                    <?php  echo $contact_category_menu; ?>
                </td>
                <td width="15%" class=widget_content_form_element>
                    <?php  echo $user_menu; ?>
                </td>
            </tr>
            <tr>
                <td class=widget_content_form_element colspan=5>
                    <input name="submitted" type=submit class=button value="<?php echo _("Search"); ?>">
                    <input name="button" type=button class=button onClick="javascript: clearSearchCriteria();" value="<?php echo _("Clear Search"); ?>">
                </td>
            </tr>
        </table>
    </form>

<?php
      if ( $use_self_contacts ) {
        echo '<table class=widget cellspacing=1 width="100%">
                    <tr>
                      <td class=widget_content_form_element colspan=4>
                       <input class=button type=button onclick="javascript: createContact();" value="' . _('Create Contact for \'Self\'') . '">
                      </td>
                    </tr>
                  </table>';
      }
$_SESSION["search_sql"]=$sql;
$pager = new Contacts_Pager($con, $sql, $sort_column-1, $pretty_sort_order);
$pager->render($rows_per_page=$system_rows_per_page);
$con->close();

?>

    </div>

        <!-- right column //-->
    <div id="Sidebar">
        <!-- recently viewed support items //-->
        <table class=widget cellspacing=1 width="100%">
            <tr>
                <td class=widget_header colspan=5><?php echo _("Recently Viewed"); ?></td>
            </tr>
            <tr>
                <td class=widget_label><?php echo _("Contact"); ?></td>
                <td class=widget_label><?php echo _("Company"); ?></td>
                <td class=widget_label><?php echo _("Work Phone"); ?></td>
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
    document.forms[0].action = "../email/email.php";
    document.forms[0].submit();
}

function clearSearchCriteria() {
    location.href = "some.php?clear=1";
}

function createContact() {
    location.href = "new.php";
}

function exportIt() {
    document.forms[0].action = "export.php";
    document.forms[0].submit();
    // reset the form so that post-export searches work
    document.forms[0].action = "some.php";
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
 * Revision 1.41  2005/01/06 17:14:43  braverock
 * - remove group by clause, as contact_id is already unique
 *   improves MS SQL Server compatibility
 *
 * Revision 1.40  2004/11/29 12:31:04  braverock
 * - fixed i18n localization quoting problems in SQL
 * - modified sql to not contain seldom used search fields if search term not present
 *    - this modification should cut down on IE DIV positioning errors
 * - added email as search criteria based on patch by Ignatius Reilly
 *
 * Revision 1.39  2004/10/26 18:40:54  introspectshun
 * - Fixed Recent Items query for db compatibility
 *
 * Revision 1.38  2004/09/21 18:34:15  introspectshun
 * - Recommitting revised include of include-locations-location.inc
 *
 * Revision 1.37  2004/08/22 23:54:03  niclowe
 * Fixed blown edit caused by niclowe merged changes since 1.34 into this version
 * it should restore the following functionality:
 *
 * 1. Localisation
 * 2. Arr_vars
 *
 * See thread http://sourceforge.net/forum/forum.php?thread_id=1131805&forum_id=305411
 *
 * Revision 1.34  2004/08/14 00:41:46  gpowers
 * - made Company Name a link, under "Recently Viewed"
 *
 * Revision 1.33  2004/08/06 15:58:25  neildogg
 * - Now adds exact match sort AFTER chosen sort
 *
 * Revision 1.32  2004/08/06 14:21:16  neildogg
 * - Now puts exact name matches at the top of the array
 *  - Removed some undefined variables
 *
 * Revision 1.31  2004/08/03 20:31:06  maulani
 * - Fix recently viewed items to remove duplicates
 * - Optimize SQL for recently viewed items to remove unused columns
 *
 * Revision 1.30  2004/07/28 20:43:49  neildogg
 * - Added field recent_action to recent_items
 *  - Same function works transparently
 *  - Current items have recent_action=''
 *  - update_recent_items has new optional parameter
 *
 * Revision 1.29  2004/07/22 11:21:13  cpsource
 * - All paths now relative to include-locations-location.inc
 *   Code cleanup for Create Contact for 'Self'
 *
 * Revision 1.28  2004/07/21 21:06:08  neildogg
 * - Added get_formatted_phone
 *
 * Revision 1.27  2004/07/21 15:20:04  introspectshun
 * - Localized strings for i18n/translation support
 * - Removed include of lang file
 *
 * Revision 1.26  2004/07/15 13:49:53  cpsource
 * - Added arr_vars sub-system.
 *
 * Revision 1.25  2004/07/15 13:05:09  cpsource
 * - Add arr_vars sub-system for passing variables between code streams.
 *
 * Revision 1.24  2004/07/14 12:51:50  cpsource
 * - Removed company_type_id handling as it was unused
 *   Session variables are cleared the first time in, as they were unset.
 *
 * Revision 1.23  2004/07/13 21:09:29  braverock
 * -removed obsolete Bulk Email code. this code was moved to the adodb pager file long ago
 *
 * Revision 1.22  2004/07/13 18:05:59  cpsource
 * - Add feature use_self_contacts
 *   fix misc unitialized variables
 *
 * Revision 1.21  2004/07/13 14:18:58  neildogg
 * - Changed submit button name to another name
 *   - resolves SF bug 9888931 reported by braverock
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
