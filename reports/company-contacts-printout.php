<?php
/**
 * Search and view summary information on multiple companies and thier contacts for printing.
 *
 * @author Brian Peterson
 *
 * $Id: company-contacts-printout.php,v 1.13 2006/01/02 23:46:52 vanmer Exp $
 */

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb/adodb-pager.inc.php');
require_once($include_directory . 'adodb-params.php');

//set the language
//$_SESSION['language'] = 'english';

$session_user_id = session_check();

$msg = $_GET['msg'];
$offset = $_POST['offset'];
$clear = ($_GET['clear'] == 1) ? 1 : 0;
$use_post_vars = ($_POST['use_post_vars'] == 1) ? 1 : 0;
$resort = $_POST['resort'];

$sort_column = '';
$current_sort_column = '';
$sort_order = '';
$current_sort_order = '';
$company_name = '';
$user_id = '';
$city = '';
$state = '';

if ($use_post_vars) {
    $sort_column = $_POST['sort_column'];
    $current_sort_column = $_POST['current_sort_column'];
    $sort_order = $_POST['sort_order'];
    $current_sort_order = $_POST['current_sort_order'];
    $company_name = $_POST['company_name'];
    $city = $_POST ['city'];
    $state = $_POST ['state'];
    $user_id = $_POST['user_id'];
    $printer_friendly= $_POST['printer_friendly'];
    $company_category_id = $_REQUEST['company_category_id'];
} else {
    $sort_column = $_SESSION['campaigns_sort_column'];
    $current_sort_column = $_SESSION['campaigns_current_sort_column'];
    $sort_order = $_SESSION['campaigns_sort_order'];
    $current_sort_order = $_SESSION['campaigns_current_sort_order'];
    $company_name = $_SESSION['companies_company_name'];
    $user_id = $_SESSION['companies_user_id'];
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

$ascending_order_image = ' <img border=0 height=10 width=10 alt="" src=../img/asc.gif>';
$descending_order_image = ' <img border=0 height=10 width=10 alt="" src=../img/desc.gif>';

$pretty_sort_order = ($sort_order == "asc") ? $ascending_order_image : $descending_order_image;

$_SESSION['companies_sort_column'] = $sort_column;
$_SESSION['companies_current_sort_column'] = $sort_column;
$_SESSION['companies_sort_order'] = $sort_order;
$_SESSION['companies_current_sort_order'] = $sort_order;
$_SESSION['companies_company_name'] = $company_name;
$_SESSION['companies_company_category_id'] = $company_category_id;
$_SESSION['companies_company_code'] = $company_code;
$_SESSION['companies_user_id'] = $user_id;
$_SESSION['companies_crm_status_id'] = $crm_status_id;

$con = get_xrms_dbconnection();

//uncomment this line if you suspect a problem with the SQL query
//$con->debug = 1;

$sql = "select " . $con->Concat("'<a href=\"../companies/one.php?company_id='", "c.company_id", "'\">'", "c.company_name", "'</a>'") . " AS "
        .$con->qstr(_("Company"),get_magic_quotes_gpc()).", c.company_id, c.default_primary_address \n" ;

$criteria_count = 0;

if ($company_category_id > 0) {
    $criteria_count++;
    $from = "from companies c, addresses addr, industries i, users u, entity_category_map ecm ";
} else {
    $from = "from companies c, addresses addr, industries i, users u ";
}

$where .= "where c.industry_id = i.industry_id ";
$where .= "and c.default_primary_address = addr.address_id ";
$where .= "and c.user_id = u.user_id ";
$where .= "and company_record_status = 'a'";

if ($company_category_id > 0) {
    $where .= " and ecm.on_what_table = 'companies' and ecm.on_what_id = c.company_id and ecm.category_id = $company_category_id ";
}

if (strlen($company_name) > 0) {
    $criteria_count++;
    $where .= " and c.company_name like " . $con->qstr(company_search_string($company_name), get_magic_quotes_gpc());
}

if (strlen($user_id) > 0) {
    $criteria_count++;
    $where .= " and c.user_id = $user_id";
}

if (strlen($city) > 0) {
    $criteria_count++;
    $sql   .= ", addr.city as '"._("City")."' \n";
    if (!strlen($state) > 0) {
        $sql   .= ", addr.province as '"._("State")."' \n";
    }
    $where .= " and addr.city LIKE " . $con->qstr($city . '%' , get_magic_quotes_gpc()) ;
}

if (strlen($state) > 0) {
    $criteria_count++;
    if (!strlen($city) > 0) {
        $sql   .= ", addr.city as '"._("City")."' \n";
    }
    $sql   .= ", addr.province as '"._("State")."' \n";
    $where .= " and addr.province LIKE " . $con->qstr($state, get_magic_quotes_gpc());
}

if (!$use_post_vars && (!$criteria_count > 0)) {
    $where .= " and 1 = 2";
}

if ($sort_column == 1) {
    $order_by = "company_name";
} else {
    $order_by = $sort_column;
}

$order_by .= " $sort_order";

$sql .= $from . $where . " order by $order_by";

$user_menu = get_user_menu($con, $user_id, true);

$sql2 = "select category_pretty_name, c.category_id
from categories c, category_scopes cs, category_category_scope_map ccsm
where c.category_id = ccsm.category_id
and cs.on_what_table =  'companies'
and ccsm.category_scope_id = cs.category_scope_id
and category_record_status =  'a'
order by category_pretty_name";
$rst = $con->execute($sql2);
$company_category_menu = $rst->getmenu2('company_category_id', $company_category_id, true);
$rst->close();

$sql2 = "select industry_pretty_name, industry_id from industries where industry_record_status = 'a' order by industry_id";
$rst = $con->execute($sql2);
$industry_menu = $rst->getmenu2('industry_id', $industry_id, true);
$rst->close();

if ($criteria_count > 0) {
    add_audit_item($con, $session_user_id, 'searched', 'companies', '', 4);
}

$page_title = _("Contact Call List");


if ($printer_friendly) {
    $show_navbar = false;
} else {
    $show_navbar = true;
}

start_page($page_title, $show_navbar, $msg);

?>

<div id="Main">

<?php if ($show_navbar) { ?>

        <form action=company-contacts-printout.php method=post>
        <input type=hidden name=use_post_vars value=1>
        <input type=hidden name=resort value="0">
        <input type=hidden name=current_sort_column value="<?php  echo $sort_column; ?>">
        <input type=hidden name=sort_column value="<?php  echo $sort_column; ?>">
        <input type=hidden name=current_sort_order value="<?php  echo $sort_order; ?>">
        <input type=hidden name=sort_order value="<?php  echo $sort_order; ?>">
        <input type=hidden name=companies_next_page>
        <table class=widget cellspacing=1 width="100%">
            <tr>
                <td class=widget_header colspan=8><?php  echo _("Search Criteria"); ?></td>
            </tr>
            <tr>
                <td class=widget_label><?php  echo _("Company Name"); ?></td>
                <td class=widget_label><?php  echo _("Company User"); ?></td>
                <td class=widget_label><?php  echo _("Company Category"); ?></td>
                <td class=widget_label><?php  echo _("Company City"); ?></td>
                <td class=widget_label><?php  echo _("Company State"); ?></td>

            </tr>
            <tr>
                <td class=widget_content_form_element><input type=text name="company_name" size=15 value="<?php  echo $company_name; ?>"></td>
                <td class=widget_content_form_element><?php  echo $user_menu; ?></td>
                <td class=widget_content_form_element><?php  echo $company_category_menu; ?></td>
                <td class=widget_content_form_element><input type=text name="city" size=10 value="<?php  echo $city; ?>"></td>
                <td class=widget_content_form_element><input type=text name="state" size=5 value="<?php echo $state; ?>"></td>
            </tr>
            <tr>
                <td class=widget_content_form_element colspan=4>
                    <input class=button type=submit value="<?php echo _("Search"); ?>">
                </td>
                <td class=widget_content_form_element>
                    <input type="checkbox" name="printer_friendly" value="true" checked><?php echo _("Format for Printer"); ?>
                </td>
            </tr>

        </table>
        </form>

<?php } //end printer friendly check ?>
    <div id="report">
        <table class=widget cellspacing=1 width="100%">
            <tr>
                <td class=widget_header colspan=3><?php echo _("Contacts for Companies"); ?></td>
            </tr>
            <tr>
                <td class=widget_label><?php echo _("Company Name"); ?></td>
                <td class=widget_label><?php echo _("Contacts"); ?></td>
                <td class=widget_label><?php echo _("Primary Address"); ?></td>
            </tr>

<?php
if ($use_post_vars) {
    $rst = $con->execute($sql);
    if ($rst) {
        while (!$rst->EOF) {
            $company_id = $rst->fields['company_id'];

            $contact_sql = "select * from contacts where company_id = $company_id
                and contact_record_status = 'a'
                order by last_name";

            $contact_rst = $con->execute($contact_sql);

            if ($contact_rst) {
                $contact_rows = "\n<table class=widget cellspacing=1 width=\"100%\">";
                while (!$contact_rst->EOF) {
                    $contact_rows .= "\n<tr>";
                    $contact_rows .= "<td class=\"widget_content\"><a href='../contacts/one.php?contact_id="
                                    . $contact_rst->fields['contact_id'] . "'>"
                                    . $contact_rst->fields['last_name'] . ', ' . $contact_rst->fields['first_names']
                                    . '</a></td>';
                    $contact_rows .= '<td class="widget_content_right">' . $contact_rst->fields['work_phone'] . '</td>';
                    $contact_rows .= "\n</tr>";
                    $contact_rst->movenext();
                }

                $contact_rst->close();

                $contact_rows .= "\n</table>";
            } else {
                // database error, return some useful information.
                //ob_start();
                db_error_handler ($con,$contact_sql);
                //$contact_rows .= "\n<table>".ob_get_contents()."\n</table>";
                //ob_end_clean();
            }

            $company_row = "\n<tr>\n\t<td class=widget_content>"
                            . $rst->fields[_("Company")]
                            . "\n\t</td>"
                            . "<td>".$contact_rows."</td>"
                            . "\n\t<td class=widget_content>"
                            . get_formatted_address ($con, $rst->fields['default_primary_address'])
                            . "\n\t</td>\n</tr>";

            echo $company_row;

            $rst->movenext();

        } //end while

    } else {
        // database error, return some useful information.
        db_error_handler ($con,$sql);
    }

    $rst->close();
}

?>

    </table>

    </div>

</div>

<script language="JavaScript" type="text/javascript">
<!--

function initialize() {
    document.forms[0].company_name.focus();
}

initialize();

function submitForm(companiesNextPage) {
    document.forms[0].companies_next_page.value = companiesNextPage;
    document.forms[0].submit();
}

function clearSearchCriteria() {
    location.href = "some.php?clear=1";
}
//-->
</script>

<?php

end_page();

/**
 * $Log: company-contacts-printout.php,v $
 * Revision 1.13  2006/01/02 23:46:52  vanmer
 * - changed to use centralized dbconnection function
 *
 * Revision 1.12  2005/08/05 21:58:02  vanmer
 * - changed to use centralized company search function
 *
 * Revision 1.11  2005/03/21 13:40:58  maulani
 * - Remove redundant code by centralizing common user menu call
 *
 * Revision 1.10  2005/01/03 06:37:19  ebullient
 * update reports - graphs centered on page, reports surrounded by divs
 *
 * Revision 1.9  2004/12/30 21:43:46  braverock
 * - localize strings
 *
 * Revision 1.8  2004/12/21 19:36:14  braverock
 * - improved display of screen table
 * - fixed code formatting
 *
 * Revision 1.7  2004/07/25 13:15:28  braverock
 * - remove lang file require_once, as it is no longer used
 *
 * Revision 1.6  2004/07/24 15:47:14  braverock
 * - remove lang/english.php variables
 * - fixed other localized strings for i18n
 *
 * Revision 1.5  2004/07/20 18:38:04  introspectshun
 * - Localized strings for i18n/translation support
 * - Now use ADODB Concat function
 * - Fixed URL to ../companies/one.php in report output
 *
 * Revision 1.4  2004/06/16 20:40:23  gpowers
 * - removed $this from session_check()
 *   - it is incompatible with PHP5
 *
 * Revision 1.3  2004/06/12 05:35:58  introspectshun
 * - Add adodb-params.php include for multi-db compatibility.
 * - Corrected order of arguments to implode() function.
 *
 * Revision 1.2  2004/06/05 16:03:16  braverock
 * - added print friendly formatting check
 *
 * Revision 1.1  2004/06/04 23:16:26  braverock
 * - add company contact summary printable report
 *
 */
?>
