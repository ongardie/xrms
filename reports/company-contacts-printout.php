<?php
/**
 * Search and view summary information on multiple companies and thier contacts for printing.
 *
 * $Id: company-contacts-printout.php,v 1.4 2004/06/16 20:40:23 gpowers Exp $
 */

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb/adodb-pager.inc.php');
require_once($include_directory . 'adodb-params.php');

//set the language
$_SESSION['language'] = 'english';

$session_user_id = session_check();

require_once($include_directory . 'lang/' . $_SESSION['language'] . '.php');

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
    $company_name = '';
    $user_id = '';
} elseif ($use_post_vars) {
    $sort_column = $_POST['sort_column'];
    $current_sort_column = $_POST['current_sort_column'];
    $sort_order = $_POST['sort_order'];
    $current_sort_order = $_POST['current_sort_order'];
    $company_name = $_POST['company_name'];
    $city = $_POST ['city'];
    $state = $_POST ['state'];
    $user_id = $_POST['user_id'];
    $printer_friendly= $_POST['printer_friendly'];
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

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

//uncomment this line if you suspect a problem with the SQL query
//$con->debug = 1;

$sql = "select concat('<a href=\"one.php?company_id=', c.company_id, '\">', c.company_name, '</a>') as Company,c.company_id,c.default_primary_address \n" ;

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
    $where .= " and c.company_name like " . $con->qstr('%'. $company_name . '%', get_magic_quotes_gpc());
}

if (strlen($user_id) > 0) {
    $criteria_count++;
    $where .= " and c.user_id = $user_id";
}

if (strlen($city) > 0) {
    $criteria_count++;
    $sql   .= ", addr.city as '$strCompaniesSomeCompanyCityLabel' \n";
    if (!strlen($state) > 0) {
        $sql   .= ", addr.province as '$strCompaniesSomeCompanyStateLabel' \n";
    }
    $where .= " and addr.city LIKE " . $con->qstr($city . '%' , get_magic_quotes_gpc()) ;
}

if (strlen($state) > 0) {
    $criteria_count++;
    if (!strlen($city) > 0) {
        $sql   .= ", addr.city as '$strCompaniesSomeCompanyCityLabel' \n";
    }
    $sql   .= ", addr.province as '$strCompaniesSomeCompanyStateLabel' \n";
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


$sql2 = "select username, user_id from users where user_record_status = 'a' order by username";
$rst = $con->execute($sql2);
$user_menu = $rst->getmenu2('user_id', $user_id, true);
$rst->close();

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

$page_title = "Contact Call List";


if ($printer_friendly) {
    $show_navbar = false;
} else {
    $show_navbar = true;
}

start_page($page_title, $show_navbar, $msg);

?>

<div id="Main">
    <div>

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
                <td class=widget_header colspan=8><?php  echo $strCompaniesSomeSearchCriteriaTitle; ?></td>
            </tr>
            <tr>
                <td class=widget_label><?php  echo $strCompaniesSomeCompanyNameLabel; ?></td>
                <td class=widget_label><?php  echo $strCompaniesSomeCompanyUserLabel; ?></td>
                <td class=widget_label><?php  echo $strCompaniesSomeCompanyCategoryLabel; ?></td>
                <td class=widget_label><?php  echo $strCompaniesSomeCompanyCityLabel; ?></td>
                <td class=widget_label><?php  echo $strCompaniesSomeCompanyStateLabel; ?></td>

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
                    <input class=button type=submit value="Search">
                </td>
                <td class=widget_content_form_element>
                    <input type="checkbox" name="printer_friendly" value="true" checked>Format for Printer
                </td>
            </tr>

        </table>
        </form>

<?php } //end printer friendly check ?>

        <table class=widget cellspacing=1 width="100%">
            <tr>
                <td class=widget_header colspan=3>Contacts for Companies</td>
            </tr>
            <tr>
                <td class=widget_label>Company Name</td>
                <td class=widget_label>Contacts</td>
                <td class=widget_label>Primary Address</td>
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
                            . $rst->fields['Company']
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
