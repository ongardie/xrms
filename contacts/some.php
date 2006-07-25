<?php
/**
 * Search for and Display Multiple Contacts
 *
 * This is the main interface for locating Contacts in XRMS
 *
 * $Id: some.php,v 1.69 2006/07/25 19:51:44 vanmer Exp $
 */

//include the standard files
require_once('include-locations-location.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'utils-saved-search.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');
require_once($include_directory . 'classes/Pager/GUP_Pager.php');
require_once($include_directory . 'classes/Pager/Pager_Columns.php');

$on_what_table='contacts';
$session_user_id = session_check();

$con = get_xrms_dbconnection();
//$con->debug = 1;
// $con->execute("update users set last_hit = " . $con->dbtimestamp(mktime()) . " where user_id = $session_user_id");


getGlobalVar($browse,'browse');
getGlobalVar($saved_id, 'saved_id');
getGlobalVar($saved_title, 'saved_title');
getGlobalVar($group_item, 'group_item');
getGlobalVar($delete_saved, 'delete_saved');


/*********** SAVED SEARCH BEGIN **********************/
load_saved_search_vars($con, $on_what_table, $saved_id, $delete_saved);

/*********** SAVED SEARCH END **********************/

// declare passed in variables
$arr_vars = array ( // local var name             // session variable name, flag
                    'last_name'          => array ( 'contacts_last_name', arr_vars_SESSION ),
                    'first_names'        => array ( 'contacts_first_names', arr_vars_SESSION ),
                    'title'              => array ( 'contacts_title', arr_vars_SESSION ),
                    'description'        => array ( 'contacts_description', arr_vars_SESSION ),
                    'category_id'        => array ( 'category_id', arr_vars_SESSION ),
                    'user_id'            => array ( 'contacts_user_id', arr_vars_SESSION ),
                    'company_name'       => array ( 'contacts_company_name', arr_vars_GET_SESSION ),
                    'company_code'       => array ( 'contacts_company_code', arr_vars_GET_SESSION ),
                    'phone_search'       => array ( 'phone_search', arr_vars_GET_SESSION ),
                    'email'              => array ( 'contacts_email', arr_vars_GET_SESSION )
                   );

// get all passed in variables
arr_vars_get_all ( $arr_vars );

// set all session variables
arr_vars_session_set ( $arr_vars );


// Note: last_name and first_names are used by GUP_Pager to speed up the sorting.
$sql = "SELECT " .
    $con->Concat($con->qstr('<a href="one.php?contact_id='), "cont.contact_id", $con->qstr('">'), "cont.last_name", "', '", "cont.first_names", $con->qstr('</a>')) . " AS name," .
    $con->Concat($con->qstr('<a id="'), "c.company_name",  $con->qstr('" href="../companies/one.php?company_id='), "c.company_id", $con->qstr('">'), "c.company_name", $con->qstr('</a>')) . " AS company,".
    "company_code, title, description, u.username, cont.email, cont.contact_id, cont.last_name, cont.first_names, c.company_name";

$from = " from contacts cont, companies c, users u ";

$where  = "where c.company_id = cont.company_id ";
$where .= "and c.user_id = u.user_id ";
$where .= "and contact_record_status = 'a'";

$criteria_count = 0;
$extra_defaults=array();
$advanced_search_columns=array();

if (strlen($last_name) > 0) {
    $criteria_count++;
    $where .= " and cont.last_name like " . $con->qstr('%' . $last_name . '%', get_magic_quotes_gpc());
}

if (strlen($first_names) > 0) {
    $criteria_count++;
    $where .= " and cont.first_names like " . $con->qstr($first_names . '%', get_magic_quotes_gpc());
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

if (strlen($email) > 0) {
    $criteria_count++;
    $where .= " and cont.email like " . $con->qstr('%' . $email . '%', get_magic_quotes_gpc());
    $extra_defaults[]='email';
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

$phone_fields=array('work_phone'=>_("Work Phone"),'cell_phone'=>_("Cell Phone"),'home_phone'=>_("Home Phone"), 'work_phone_ext'=>_("Work Phone Ext"));
if ($phone_search) {
    $sql_phone_search=preg_replace("/[^\d]/", '', $phone_search);
    $phonewhere=array();
    foreach ($phone_fields as $phonefield => $phonelabel) {
        $criteria_count++;
        $sql .= ", $phonefield ";
        $phonewhere[] = "($phonefield LIKE " . $con->qstr('%'.$sql_phone_search.'%'). ")";
        $extra_defaults[]=$phonefield;
        $advanced_search_columns[] = array('name' => $phonelabel, 'index_sql' => $phonefield);
    }
    if (count($phonewhere)>0) {
        $where .= " AND (" . implode(' OR ', $phonewhere) . ")";
    }
}



if (!$use_post_vars && (!$criteria_count > 0)) {
    $where .= " and 1 = 2";
} else {
    $list=acl_get_list($session_user_id, 'Read', false, $on_what_table);
    //print_r($list);
    if ($list) {
        if ($list!==true) {
            $list=implode(",",$list);
            $where .= " and cont.contact_id IN ($list) ";
        }
    } else { $where .= ' AND 1 = 2 '; }
}

//gorup by shouldn't be needed, contact_id is already unique
//$group_by = " group by contact_id";

$sql .= $from . $where;

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
        $contact_id='';
        $company_id='';
        $contact_id=$rst->fields['contact_id'];
        $company_id=$rst->fields['company_id'];
        $recently_viewed_table_rows .= '<tr>';
        $recently_viewed_table_rows .= '<td class=widget_content><a href="one.php?contact_id=' . $rst->fields['contact_id'] . '">';
        $recently_viewed_table_rows .= $rst->fields['first_names'] . ' ' . $rst->fields['last_name'] . '</a></td>';
        $recently_viewed_table_rows .= '<td class=widget_content><a href="../companies/one.php?company_id=' . $rst->fields['company_id'] . '">' . $rst->fields['company_name'] . '</a></td>';
        $recently_viewed_table_rows .= '<td class=widget_content>' . get_formatted_phone($con, $rst->fields['address_id'], $rst->fields['work_phone']) . '</td>';
        $recently_viewed_table_rows .= '</tr>';
        $rst->movenext();
        $contact_id='';
        $company_id='';
    }
    $rst->close();
}

if ( !$recently_viewed_table_rows ) {
    $recently_viewed_table_rows = '<tr><td class=widget_content colspan=3>' . _("No recently viewed contacts") . '</td></tr>';
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

$page_title = _("Contacts");

/******* SAVED SEARCH BEGINS *****/
    $saved_data = $_POST;
    $saved_data["sql"] = $sql;
    $saved_data["day_diff"] = $day_diff;

    if(!$saved_title) {
        $saved_title = "Current";
        $group_item = 0;
    }
    if ($saved_title OR $browse) {
//        echo "adding saved search";
        $saved_id=add_saved_search_item($con, $saved_title, $group_item, $on_what_table, $saved_data);
//        echo "$saved_id=add_saved_search_item($con, $saved_title, $group_item, $on_what_table, $saved_data);";
    }

//get saved searches
$rst=get_saved_search_item($con, $on_what_table, $session_user_id, false,  false, true,'search', true);
if( $rst AND $rst->RowCount() ) {
    $saved_menu = $rst->getmenu2('saved_id', 0, true) . ' <input name="delete_saved" type=submit class=button value="' . _("Delete") . '">';
} else {
  $saved_menu = '';
}

/********** SAVED SEARCH ENDS ****/



start_page($page_title, true, $msg);
if(!isset($contacts_next_page)) {
    $contacts_next_page = '';
}

?>

<div id="Main">
    <div id="Content">

    <form action=some.php class="print" method=post name="ContactForm">
        <input type=hidden name=use_post_vars value=1>

        <table class=widget cellspacing=1 width="100%">
            <tr>
                <td class=widget_header colspan=5><?php echo _("Search Criteria"); ?></td>
            </tr>
            <tr>
                <td class=widget_label><?php echo _("Last Name"); ?></td>
                <td class=widget_label><?php echo _("First Names"); ?></td>
                <td class=widget_label><?php echo _("Company"); ?></td>
                <td class=widget_label><?php echo _("Email"); ?></td>
                <td class=widget_label><?php echo _("Phone"); ?></td>
            </tr>
            <tr>
                <td width="25%" class=widget_content_form_element>
                    <input type=text name="last_name" size=18 maxlength=100 value="<?php  echo $last_name; ?>">
                </td>
                <td width="25%" class=widget_content_form_element>
                    <input type=text name="first_names" size=12 maxlength=100 value="<?php  echo $first_names; ?>">
                </td>
                <td width="25%" class=widget_content_form_element>
                    <input type=text name="company_name" id="contactForm_company_name" size=18 maxlength=100 value="<?php  echo $company_name; ?>">
                </td>
                <td width="25%" class=widget_content_form_element>
                    <input type=text name="email" size=12 maxlength=40 value="<?php  echo $email; ?>">
                </td>
                <td width="10%" class=widget_content_form_element>
                    <input type=text name="phone_search" size=10 maxlength=40 value="<?php  echo $phone_search; ?>">
                </td>
            </tr>
            <tr>
                <td class=widget_label><?php echo _("Title"); ?></td>
                <td class=widget_label><?php echo _("Description"); ?></td>
                <td class=widget_label><?php echo _("Owner"); ?></td>
                <td class=widget_label colspan=2><?php echo _("Category"); ?></td>
            </tr>
            <tr>
                <td width="25%" class=widget_content_form_element>
                    <input type=text name="title" size=12 maxlength=100 value="<?php  echo $title; ?>">
                </td>
                <td width="25%" class=widget_content_form_element>
                    <input type=text name="description" size=12 maxlength=50 value="<?php  echo $description; ?>">
                </td>
                <td width="15%" class=widget_content_form_element>
                    <?php  echo $user_menu; ?>
                </td>
                <td width="25%" class=widget_content_form_element colspan=2>
                    <?php  echo $contact_category_menu; ?>
                </td>
            </tr>
            <tr>
                <td class=widget_label colspan="2"><?php echo _("Saved Searches"); ?></td>
                <td class=widget_label colspan="3"><?php echo _("Search Title"); ?></td>
            </tr>
            <tr>
                <td class=widget_content_form_element colspan="2">
                    <?php echo ($saved_menu) ? $saved_menu : _("No Saved Searches"); ?>
                </td>
                <td class=widget_content_form_element colspan="3">
                    <input type=text name="saved_title" size=24>
                    <?php
                        if(check_user_role(false, $_SESSION['session_user_id'], 'Administrator')) {
                            echo _("Add to Everyone").' <input type=checkbox name="group_item" value=1>';
                        }
                    ?>
                </td>
            </tr>
            <tr>
                <td class=widget_content_form_element colspan=5>
                    <input name="submitted" type=submit class=button value="<?php echo _("Search"); ?>">
                    <input name="button" type=button class=button onClick="javascript: clearSearchCriteria();" value="<?php echo _("Clear Search"); ?>">
                </td>
            </tr>
        </table>

<?php
      $undefined_company_method=get_user_preference($con, $session_user_id, 'undefined_company_method');
      switch ($undefined_company_method) {
        case 'reject':
            $use_self_contacts=false;
        break;
        case 'unknown':
        case 'contact_name':
        default:
            $use_self_contacts=true;
        break;
      }
      if ( $use_self_contacts ) {
        $self_contacts = "\n\t".'<tr>
                      <td class=widget_content_form_element colspan=4>
                       <input class=button type=button onclick="javascript: createContact();" value="' . _("Create Contact without a Company") . '">
                      </td>
                    </tr>';
      } else { $self_contacts = ''; }


$_SESSION["search_sql"]=$sql;
    "company_code, title, description, u.username, cont.email, cont.contact_id, cont.last_name, cont.first_names, c.company_name";

$columns = array();
$columns[] = array('name' => _("Name"), 'index_sql' => 'name', 'sql_sort_column' => 'cont.last_name,cont.first_names', 'type' => 'url');
$columns[] = array('name' => _("Company"), 'index_sql' => 'company', 'sql_sort_column' => 'c.company_name', 'type' => 'url');
$columns[] = array('name' => _("Code"), 'index_sql' => 'company_code');
$columns[] = array('name' => _("Title"), 'index_sql' => 'title');
$columns[] = array('name' => _("Description"), 'index_sql' => 'description');
$columns[] = array('name' => _("Owner"), 'index_sql' => 'username');
$columns[] = array('name' => _("Email"), 'index_sql' => 'email');
$columns=array_merge($columns, $advanced_search_columns);


// selects the columns this user is interested in
// no reason to set this if you don't want all by default
$default_columns = null;
$default_columns =  array('name','company','company_code','title','description','username');
$default_columns=array_merge($default_columns, $extra_defaults);

$pager_columns = new Pager_Columns('ContactPager', $columns, $default_columns, 'ContactForm');
$pager_columns_button = $pager_columns->GetSelectableColumnsButton();
$pager_columns_selects = $pager_columns->GetSelectableColumnsWidget();

$columns = $pager_columns->GetUserColumns('default');


$pager = new GUP_Pager($con, $sql, null, _('Search Results'), 'ContactForm', 'ContactPager', $columns, false);

$endrows = "<tr><td class=widget_content_form_element colspan=10>
            $pager_columns_button
            " . $pager->GetAndUseExportButton() .  "
            <input type=button class=button onclick=\"javascript: bulkEmail();\" value=\""._("Mail Merge")."\">
            <input type=button class=button onclick=\"javascript: bulkSnailMail();\" value=\""._("Snail Mail Merge")."\">
</td></tr>";

echo $pager_columns_selects;

$newContact_return_url=$http_site_root.current_page();

$pager->AddEndRows($endrows);
$pager->Render($system_rows_per_page);

$con->close();

$new_contact_button=render_create_button(_("New Contact"), 'submit', false, false, false, 'contacts');
?>

    </form>
    </div>

        <!-- right column //-->
    <div id="Sidebar">
        <form action="new_contact_company_select.php" method=POST onSubmit="return setNewContact_company_name()" name=newContact>
            <input type=hidden name=company_name id='newContact_company_name'>
            <input type=hidden name=return_url value="<?php echo $newContact_return_url; ?>">
<?php if ($new_contact_button) { ?>
        <table class=widget cellspacing=1 width="100%">
            <tr>
                <td class=widget_header><?php echo _("Contact Options"); ?></td>
            </tr>
            <tr>
                <td class=widget_content_form_element>
		   <?php echo $new_contact_button; ?>
                </td>
            </tr>
            <?php echo $self_contacts; ?>
        </table>
        </form>
<?php } ?>
        <!-- recently viewed support items //-->
        <table class=widget cellspacing=1 width="100%">
            <tr>
                <td class=widget_header colspan=3><?php echo _("Recently Viewed"); ?></td>
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
function bulkSnailMail() {
    document.forms[0].action = "../snailmail/snailmail-1.php?scope=contacts";
    document.forms[0].submit();
}
function clearSearchCriteria() {
    location.href = "some.php?clear=1";
}

function createContact() {
    location.href = "new.php";
}

function getContact_company_name() {
    var cname;
    cname=document.getElementById('contactForm_company_name');
    return cname.value;
}

function setNewContact_company_name() {
    var cname;
    cname = document.getElementById('newContact_company_name');
    cname.value = getContact_company_name();
    return true;
}

//-->
</script>

<?php

end_page();


/**
 * $Log: some.php,v $
 * Revision 1.69  2006/07/25 19:51:44  vanmer
 * - ensure new contact button only appears when proper permissions exist
 *
 * Revision 1.68  2006/04/26 13:15:59  braverock
 * - don't close the table twice
 *
 * Revision 1.67  2006/04/26 13:02:32  braverock
 * - update unknown or contact_name new contact string
 * - move all 'new contact' buttons to the same place on the screen for consistency
 *
 * Revision 1.66  2006/04/26 02:13:54  vanmer
 * - removed deprecated use_self_contacts option, now uses system preference controlling behavior
 *
 * Revision 1.65  2006/01/02 23:00:00  vanmer
 * - changed to use centralized dbconnection function
 *
 * Revision 1.64  2005/08/28 16:35:06  braverock
 * - fix colspan on Recently Viewed table
 *
 * Revision 1.63  2005/08/16 00:15:21  vanmer
 * - changed all phone searches to be contains instead of starts with
 * - added code to strip all formatting off phone searches
 * - added work extension to fields searched on a contact
 *
 * Revision 1.62  2005/08/15 18:13:00  vanmer
 * - added phone to contacts search
 * - added ability to show extra columns when searching on those fields
 *
 * Revision 1.61  2005/08/05 21:44:50  vanmer
 * - changed contact company searches to use centralized company search string function
 *
 * Revision 1.60  2005/08/05 01:22:10  vanmer
 * - changed to use centralized functions for saved searches
 *
 * Revision 1.59  2005/07/08 20:14:19  vanmer
 * - added saved search capability to contacts search page
 *
 * Revision 1.58  2005/06/20 18:48:04  niclowe
 * added snail mail merge functionality
 *
 * Revision 1.57  2005/05/11 16:28:46  braverock
 * - explicitly set contact_id and company_id in recently viewed list for cti integration
 *
 * Revision 1.56  2005/05/06 23:03:03  vanmer
 * - added sidebar for adding new contact from some.php page
 * - added javascript to add company_name from some.php search to use as parameter to searching for company before
 * adding contact
 * - added id on company_name element in search form to allow javascript to properly identify field
 *
 * Revision 1.55  2005/04/29 17:55:58  daturaarutad
 * fixed printing of form/search results
 *
 * Revision 1.54  2005/04/29 16:26:53  daturaarutad
 * updated to use GUP_Pager for export
 *
 * Revision 1.53  2005/03/21 13:40:56  maulani
 * - Remove redundant code by centralizing common user menu call
 *
 * Revision 1.52  2005/03/15 22:51:59  daturaarutad
 * pager tuning sql_sort_column
 *
 * Revision 1.51  2005/03/04 20:13:37  daturaarutad
 * tweaked the main query to speed things up when sorting on columns that use concat()
 *
 * Revision 1.50  2005/02/28 22:42:32  daturaarutad
 * changed columns to be index_sql so that the pager knows it doesnt have to get the whole data set
 *
 * Revision 1.49  2005/02/25 03:37:59  daturaarutad
 * updated to use GUP_Pager, removed unused JS for sorting
 *
 * Revision 1.48  2005/02/14 21:44:11  vanmer
 * - updated to reflect speed changes in ACL operation
 *
 * Revision 1.47  2005/02/09 23:58:15  braverock
 * - quote the Mail MErge button so both words show
 *
 * Revision 1.46  2005/02/09 23:54:54  braverock
 * - fix missing . concatenate operator in Mail Merge button
 *
 * Revision 1.45  2005/02/09 22:25:20  braverock
 * - localized pager column headers
 * - de-localized AS clauses in SQL
 *
 * Revision 1.44  2005/01/25 22:04:16  daturaarutad
 * updated to use new XRMS_Pager and Pager_Columns to implement selectable columns
 *
 * Revision 1.43  2005/01/25 17:25:40  daturaarutad
 * fixed broken query (needed a whitespace)
 *
 * Revision 1.42  2005/01/13 18:46:38  vanmer
 * - ACL restriction on search
 *
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
