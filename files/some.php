<?php
/**
 * Search for and display a summary of multiple files
 *
 * $Id: some.php,v 1.25 2004/11/26 17:31:38 braverock Exp $
 */

//include required files
require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once('pager.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();

// declare passed in variables
$arr_vars = array ( // local var name       // session variable name
           'sort_column'        => array ( 'files_sort_column', arr_vars_SESSION ),
           'current_sort_column'=> array ( 'files_current_sort_column', arr_vars_SESSION ),
           'sort_order'         => array ( 'files_sort_order', arr_vars_SESSION ),
           'current_sort_order' => array ( 'files_current_sort_order', arr_vars_SESSION ),
           'file_id'            => array ( 'file_id', arr_vars_SESSION ),
           'file_name'          => array ( 'file_name', arr_vars_SESSION ),
           'file_description'   => array ( 'file_description', arr_vars_SESSION ),
           'file_on_what'       => array ( 'file_on_what', arr_vars_SESSION ),
           'file_on_what_name'  => array ( 'file_on_what_name', arr_vars_SESSION ),
           'file_date'          => array ( 'file_date', arr_vars_SESSION ),
           'user_id'            => array ( 'file_user_id', arr_vars_SESSION ),
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

$sql = "SELECT "
      . $con->Concat("'<a href=\"$http_site_root/files/one.php?return_url=/private/home.php&amp;file_id='","file_id","'\">'","file_pretty_name", "'</a>'")
      . " AS '" . _("Name") . "', file_description as '" . _("Description") . "',";

switch ($file_on_what) {
    case "contacts" : {
        $sql .= $con->Concat("'<a href=\"$http_site_root/contacts/one.php?return_url=/private/home.php&amp;contact_id='", "contact_id", "'\">'", "cont.last_name", "' '", "cont.first_names", "'</a>'")
              . " AS '" . _("Contact") . "',"
              . $con->Concat("'<a href=\"$http_site_root/companies/one.php?return_url=/private/home.php&amp;company_id='", "c.company_id", "'\">'", "c.company_name", "'</a>'")
              . " AS '" . _("Company") . "',";
        break;
    }
    case "contacts_of_companies" : {
        $sql .= $con->Concat("'<a href=\"$http_site_root/contacts/one.php?return_url=/private/home.php&amp;contact_id='", "contact_id", "'\">'", "cont.last_name", "' '", "cont.first_names", "'</a>'")
              . " AS '" . _("Contact") . "',"
              . $con->Concat("'<a href=\"$http_site_root/companies/one.php?return_url=/private/home.php&amp;company_id='", "c.company_id", "'\">'", "c.company_name", "'</a>'")
              . " AS '" . _("Company") . "',";
        break;
    }
    case "companies" : {
        $sql .= $con->Concat("'<a href=\"$http_site_root/companies/one.php?return_url=/private/home.php&amp;company_id='", "c.company_id", "'\">'", "c.company_name", "'</a>'")
              . " AS '" . _("Company") . "',";
        break;
    }
    case "campaigns" : {
        $sql .= $con->Concat("'<a href=\"$http_site_root/campaigns/one.php?return_url=/private/home.php&amp;campaign_id='", "camp.campaign_id", "'\">'", "camp.campaign_title", "'</a>'")
              . " AS '" . _("Campaign") . "',";
        break;
    }
    case "opportunities" : {
        $sql .= $con->Concat("'<a href=\"$http_site_root/opportunities/one.php?return_url=/private/home.php&amp;opportunity_id='", "opportunity_id", "'\">'", "opp.opportunity_title", "'</a>'")
              . " AS '" . _("Opportunity") . "',"
              . $con->Concat("'<a href=\"$http_site_root/companies/one.php?return_url=/private/home.php&amp;company_id='", "c.company_id", "'\">'", "c.company_name", "'</a>'")
              . " AS '" . _("Company") . "',";
        break;
    }
    case "cases" : {
        $sql .= $con->Concat("'<a href=\"$http_site_root/cases/one.php?return_url=/private/home.php&amp;case_id='", "case_id", "'\">'", "cases.case_title", "'</a>'")
              . " AS '" . _("Case") . "',"
              . $con->Concat("'<a href=\"$http_site_root/companies/one.php?return_url=/private/home.php&amp;company_id='", "c.company_id", "'\">'", "c.company_name", "'</a>'")
              . " AS '" . _("Company") . "',";
        break;
    }
    default : {
        $sql .= "";
        }
}

$sql .= $con->SQLDate('Y-m-d','f.entered_at') . " AS '" . _("Date") . "'," .
        $con->Concat("'<a href=\"$http_site_root/files/one.php?return_url=/private/home.php&amp;file_id='", "file_id", "'\">'", "file_id", "'</a>'") . " AS '" . _("ID") . "' ";

$from = "from ";
switch ($file_on_what) {
    case "contacts" : { $from .= "contacts cont, companies c, "; break; }
    case "contacts_of_companies" : { $from .= "contacts cont, companies c, "; break; }
    case "companies" : { $from .= "companies c, "; break; }
    case "campaigns" : { $from .= "campaigns camp, "; break; }
    case "opportunities" : { $from .= "opportunities opp, companies c, "; break; }
    case "cases" : { $from .= "cases cases, companies c, "; break; }
}
$from .= "files f, users u ";

$where = "where f.entered_by = u.user_id ";
switch ($file_on_what) {
    case "contacts" : { $where .= "and f.on_what_table = 'contacts' and f.on_what_id = cont.contact_id and cont.company_id = c.company_id "; break; }
    case "contacts_of_companies" : { $where .= "and f.on_what_table = 'contacts' and f.on_what_id = cont.contact_id and cont.company_id = c.company_id "; break; }
    case "companies" : { $where .= "and f.on_what_table = 'companies' and f.on_what_id = c.company_id  "; break; }
    case "campaigns" : { $where .= "and f.on_what_table = 'campaigns' and f.on_what_id = camp.campaign_id  "; break; }
    case "opportunities" : { $where .= "and f.on_what_table = 'opportunities' and f.on_what_id = opp.opportunity_id and opp.company_id = c.company_id "; break; }
    case "cases" : { $where .= "and f.on_what_table = 'cases' and f.on_what_id = cases.case_id and cases.company_id = c.company_id "; break; }
}
$where .= "and file_record_status = 'a'";

$criteria_count = 0;

if (strlen($file_id) > 0) {
    $criteria_count++;
    $where .= " and f.file_id = " . $con->qstr($file_id, get_magic_quotes_gpc());
}

if (strlen($file_name) > 0) {
    $criteria_count++;
    $where .= " and f.file_pretty_name like " . $con->qstr('%' . $file_name . '%', get_magic_quotes_gpc());
}

if (strlen($file_description) > 0) {
    $criteria_count++;
    $where .= " and f.file_description like " . $con->qstr('%' . $file_description . '%', get_magic_quotes_gpc());
}

switch ($file_on_what) {
    case "contacts" : {
            $criteria_count++;
            $where .= " and cont.last_name like " . $con->qstr('%' . $file_on_what_name . '%', get_magic_quotes_gpc());
            break;
        }

    case "contacts_of_companies" : {
            $criteria_count++;
            $where .= " and c.company_name like " . $con->qstr('%' . $file_on_what_name . '%', get_magic_quotes_gpc());
            break;
        }

    case "companies" : {
            $criteria_count++;
            $where .= " and c.company_name like " . $con->qstr('%' . $file_on_what_name . '%', get_magic_quotes_gpc());
            break;
        }

    case "campaigns" : {
            $criteria_count++;
            $where .= " and camp.campaign_title like " . $con->qstr('%' . $file_on_what_name . '%', get_magic_quotes_gpc());
            break;
        }

    case "opportunities" : {
            $criteria_count++;
            $where .= " and opp.opportunity_title like " . $con->qstr('%' . $file_on_what_name . '%', get_magic_quotes_gpc());
            break;
        }

    case "cases" : {
            $criteria_count++;
            $where .= " and cases.case_title like " . $con->qstr('%' . $file_on_what_name . '%', get_magic_quotes_gpc());
            break;
        }
}

if (strlen($file_date) > 0) {
    $criteria_count++;
    $where .= " and f.entered_at like " . $con->qstr($file_date . '%', get_magic_quotes_gpc());
}

if (strlen($user_id) > 0) {
    $criteria_count++;
    $where .= " and f.entered_by = $user_id";
}

if (!$use_post_vars && (!$criteria_count > 0)) {
    $where .= " and 1 = 2";
}

if ($sort_column == 6) {
    $order_by = "f.file_id";
} elseif ($sort_column == 1) {
    $order_by = "f.file_pretty_name";
} elseif ($sort_column == 2) {
    $order_by = "f.file_description";
} elseif ($sort_column == 5) {
    $order_by = "f.entered_at";
} else {
    $order_by = $sort_column;
}

$order_by .= " $sort_order";

$sql .= $from . $where . " order by $order_by";

$sql_recently_viewed = "select * from recent_items r, files f
where r.user_id = $session_user_id
and r.on_what_table = 'files'
and r.recent_action = ''
and r.on_what_id = f.file_id
and file_record_status = 'a'
order by r.recent_item_timestamp desc";

//$con->debug=1;

$recently_viewed_table_rows = '';
$rst = $con->selectlimit($sql_recently_viewed, $recent_items_limit);

if ($rst) {
    while (!$rst->EOF) {
        $recently_viewed_table_rows .= '<tr>';
        $recently_viewed_table_rows .= '<td class=widget_content><a href="one.php?file_id=' . $rst->fields['file_id'] . '">' . $rst->fields['file_pretty_name'] . '</a></td>';
        $recently_viewed_table_rows .= '<td class=widget_content>' . pretty_filesize($rst->fields['file_size']) . '</td>';
        $recently_viewed_table_rows .= '<td class=widget_content>' . $con->userdate($rst->fields['entered_at']) . '</td>';
        $recently_viewed_table_rows .= '<td class=widget_content>' . $rst->fields['file_id'] . '</td>';
        $recently_viewed_table_rows .= '</tr>';
        $rst->movenext();
    }
    $rst->close();
}

if (strlen($recently_viewed_table_rows) == 0) {
    $recently_viewed_table_rows = '<tr><td class=widget_content colspan=5>' . _("No recently viewed file") . '</td></tr>';
}

$sql2 = "select username, user_id from users where user_record_status = 'a' order by username";
$rst = $con->execute($sql2);
$user_menu = $rst->getmenu2('user_id', $user_id, true);
$rst->close();

if ($criteria_count > 0) {
    add_audit_item($con, $session_user_id, 'searched', 'files', '', 4);
}

// get company_count
$rst = $con->execute($sql);
$company_count = 0;
if ( $rst ) {
  while (!$rst->EOF) {
    $company_count += 1;
    break;                // we only care if we have more than 0, so stop here
    $rst->movenext();
  }
  $rst->close();
}

$page_title = _("Files");
start_page($page_title, true, $msg);

?>

<div id="Main">
    <div id="Content">

        <form action=some.php method=post>
        <input type=hidden name=use_post_vars value=1>
        <input type=hidden name=files_next_page value="<?php  echo $files_next_page; ?>">
        <input type=hidden name=resort value="0">
        <input type=hidden name=current_sort_column value="<?php  echo $sort_column; ?>">
        <input type=hidden name=sort_column value="<?php  echo $sort_column; ?>">
        <input type=hidden name=current_sort_order value="<?php  echo $sort_order; ?>">
        <input type=hidden name=sort_order value="<?php  echo $sort_order; ?>">
        <table class=widget cellspacing=1>
        <tr>
                <td class=widget_header colspan=7><?php echo _("Search Criteria"); ?></td>
        </tr>
        <tr>
                <td class=widget_label><?php echo _("File ID"); ?></td>
                <td class=widget_label><?php echo _("File Name"); ?></td>
                <td class=widget_label><?php echo _("File Description"); ?></td>
                <td class=widget_label><?php echo _("On What"); ?></td>
        </tr>
        <tr>
                <td class=widget_content_form_element><input type=text name="file_id" size=5 value="<?php  echo $file_id ?>"></td>
                <td class=widget_content_form_element><input type=text name="file_name" size=12 value="<?php  echo $file_name ?>"></td>
                <td class=widget_content_form_element><input type=text name="file_description" size=12 value="<?php  echo $file_description ?>"></td>
                <td class=widget_content_form_element>
            <select name="file_on_what">
                        <option value="default"<?php if ($file_on_what == "") { echo " selected"; } ?>></option>
                        <option value="contacts"<?php if ($file_on_what == "contacts" ) { echo " selected"; } ?>><?php echo _("Contacts"); ?></option>
                        <option value="contacts_of_companies"<?php if ($file_on_what == "contacts_of_companies") { echo " selected"; } ?>><?php echo _("Contacts of Companies"); ?></option>
                        <option value="companies"<?php if ($file_on_what == "companies") { echo " selected"; } ?>><?php echo _("Companies"); ?></option>
                        <option value="campaigns"<?php if ($file_on_what == "campaigns") { echo " selected"; } ?>><?php echo _("Campaigns"); ?></option>
                        <option value="opportunities"<?php if ($file_on_what == "opportunities") { echo " selected"; } ?>><?php echo _("Opportunities"); ?></option>
                        <option value="cases"<?php if ($file_on_what == "cases") { echo " selected"; } ?>><?php echo _("Cases"); ?></option>
                    </select>
                </td>
        </tr>
        <tr>
                <td colspan=2 class=widget_label><?php echo _("On what Name"); ?></td>
                <td class=widget_label><?php echo _("Date"); ?></td>
                <td class=widget_label><?php echo _("Owner"); ?></td>
        </tr>
        <tr>
                <td colspan=2 class=widget_content_form_element><input type=text name="file_on_what_name" size=12 value="<?php echo $file_on_what_name; ?>"></td>
                <td class=widget_content_form_element><input type=text name="file_date" size=8 value="<?php echo $file_date; ?>"></td>
                <td class=widget_content_form_element><?php echo $user_menu; ?></td>
        </tr>
        <tr>
                <td colspan=4 class=widget_content_form_element>
                    <input class=button type=submit value="<?php echo _("Search"); ?>">
                    <input class=button type=button onclick="javascript: clearSearchCriteria();" value="<?php echo _("Clear Search"); ?>">
                    <?php if ($company_count > 0) {echo "<input class=button type=button onclick='javascript: bulkEmail()' value='"._("Bulk E-Mail")."'>";}; ?>
          </td>
        </tr>
      </table>
        </form>
        <p>
<?php

if ( $use_owl ) {
  echo "<input class=button type=button onclick='javascript: owl()' value='"._("Owl File Management")."'><br><br>";
}

$pager = new Files_Pager($con, $sql, $sort_column-1, $pretty_sort_order);
$pager->render($rows_per_page=$system_rows_per_page);
$con->close();

?>

    </div>

        <!-- right column //-->
    <div id="Sidebar">

        <!-- recently viewed support items //-->
        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=4><?php echo _("Recently Viewed"); ?></td>
            </tr>
            <tr>
                <td class=widget_label><?php echo _("Name"); ?></td>
                <td class=widget_label><?php echo _("Size"); ?></td>
                <td class=widget_label><?php echo _("Date"); ?></td>
                <td class=widget_label><?php echo _("File ID"); ?></td>
            </tr>
            <?php  echo $recently_viewed_table_rows; ?>
        </table>

    </div>
</div>

<script language="JavaScript" type="text/javascript">
<!--

function initialize() {
    document.forms[0].file_id.focus();
}

initialize();

function owl() {
    document.forms[0].action = "../owl/index.php";
    document.forms[0].submit();
}

function bulkEmail() {
    document.forms[0].action = "../email/index.php";
    document.forms[0].submit();
}

function clearSearchCriteria() {
    location.href = "some.php?clear=1";
}

function submitForm(adodbNextPage) {
    document.forms[0].files_next_page.value = adodbNextPage;
    document.forms[0].submit();
}

function resort(sortColumn) {
    document.forms[0].sort_column.value = sortColumn + 1;
    document.forms[0].files_next_page.value = '';
    document.forms[0].resort.value = 1;
    document.forms[0].submit();
}

//-->
</script>

<?php

end_page();

/**
 * $Log: some.php,v $
 * Revision 1.25  2004/11/26 17:31:38  braverock
 * - fix syntax error where f.description should have been f.file_description
 *
 * Revision 1.24  2004/08/19 13:14:05  maulani
 * - Add specific type pager to ease overriding of layout function
 *
 * Revision 1.23  2004/08/05 14:34:19  introspectshun
 * - Localized remaining option/button strings for i18n/l10n support
 *
 * Revision 1.22  2004/08/04 13:05:18  cpsource
 * - Add hook to OWL.
 *
 * Revision 1.21  2004/07/28 20:44:06  neildogg
 * - Added field recent_action to recent_items
 *  - Same function works transparently
 *  - Current items have recent_action=''
 *  - update_recent_items has new optional parameter
 *
 * Revision 1.20  2004/07/16 03:14:55  introspectshun
 * - Localized SQL aliias strings for i18n/translation support
 *
 * Revision 1.19  2004/07/15 16:43:36  introspectshun
 * - Fixed errant CVS Commit. Updated s-t's code to reflect recent HTML tweaks.
 *
 * Revision 1.18  2004/07/15 13:49:54  cpsource
 * - Added arr_vars sub-system.
 *
 * Revision 1.17  2004/07/14 20:19:50  cpsource
 * - Resolved $company_count not being set properly
 *   opportunities/some.php tried to set $this which can't be done in PHP V5
 *
 * Revision 1.16  2004/07/14 13:14:37  cpsource
 * - Fixed numerous undefined variable usages
 *
 * Revision 1.15  2004/07/14 02:04:12  s-t
 * cvs commit some.php
 *
 * Revision 1.14  2004/07/09 18:44:50  introspectshun
 * - Removed CAST(x AS CHAR) for wider database compatibility
 * - The modified MSSQL driver overrides the default Concat function to cast all datatypes as strings
 *
 * Revision 1.13  2004/06/24 15:54:23  maulani
 * - Fix html errors so search button displays and page validates
 *
 * Revision 1.12  2004/06/21 20:41:37  introspectshun
 * - Now use CAST AS CHAR to convert integers to strings in Concat function calls.
 *
 * Revision 1.11  2004/06/21 14:25:32  braverock
 * - localized strings for i18n/internationalization/translation support
 *
 * Revision 1.10  2004/06/12 18:23:51  braverock
 * - remove CAST, as it is not standard across databases
 *   - database should explicitly convert number to string for CONCAT
 *
 * Revision 1.9  2004/06/12 07:20:40  introspectshun
 * - Now use ADODB GetInsertSQL, GetUpdateSQL, date and Concat functions.
 *
 * Revision 1.8  2004/05/10 13:07:21  maulani
 * - Add level to audit trail
 * - Clean up audit trail text
 *
 * Revision 1.7  2004/04/16 22:22:06  maulani
 * - Add CSS2 positioning
 *
 * Revision 1.6  2004/04/16 14:46:28  maulani
 * - Clean HTML so page will validate
 *
 * Revision 1.5  2004/04/15 22:04:39  maulani
 * - Change to CSS2 positioning
 * - Clean HTML to achieve validation
 *
 * Revision 1.4  2004/04/08 17:00:11  maulani
 * - Update javascript declaration
 * - Add phpdoc
 *
 * Revision 1.3  2004/03/24 12:31:52  braverock
 * - improve search
 * - display recently viewed items
 * - modified from code provided by Olivier Colonna of Fontaine Consulting
 * - add phpdoc
 * ***** NOTE: BUG on long File Descriptions *****
 *
 */
?>
