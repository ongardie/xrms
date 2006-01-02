<?php
/**
 * Create a list of companies.
 * Can select by:
 *     Company name
 *     Owner
 *     Category
 *     CRM Status
 *     City
 *     State
 *     Country
 * Ouput formats are:
 *     HTML table
 *     PDF file directly in browser which can be printed
 *     saved or emailed
 *
 * @author John Fawcett
 *
 */
session_cache_limiter('none');
require_once('../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb/adodb-pager.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();
$msg = $_GET['msg'];
$go = $_REQUEST['go'];
$pdf = $_REQUEST['pdf'];
$name = $_REQUEST['name'];
$user_id = $_REQUEST['user_id'];
$company_category_id = $_REQUEST['company_category_id'];
$company_source_id = $_REQUEST['company_source_id'];
$crm_status_id = $_REQUEST['crm_status_id'];
$city = $_REQUEST['city'];
$state = $_REQUEST['state'];
$country = $_REQUEST['country'];

$con = get_xrms_dbconnection();

// if pdf action was selected then output a pdf instead of html page
if ($pdf)
{
// set the fpdf variable and include fpdf library files
    define('FPDF_FONTPATH',$include_directory . 'fpdf152/font/');
    require($include_directory . 'fpdf152/fpdf.php');

// include html2pdf script
    require($include_directory . 'fpdf152/html2pdf.php');

// construct and output pdf
    $pdf=new PDF('L'); // landscape format
    $pdf->AddPage();
    $pdf->SetFont('Arial','',8); // font Arial 8
    $pdf->WriteHTML(companies_list($con,$pdf,$name,$city,$state,$country,$user_id,
                $company_category_id,$company_source_id,$crm_status_id));
    $pdf->Output();
    exit;
}

// if we are here then its for an html page

$page_title = _("Companies List");
start_page($page_title, true, $msg);

// $con->debug = 1;

$user_menu = get_user_menu($con, $user_id, true);

// set up the categories selection menu
$sql = "select category_pretty_name, c.category_id
from categories c, category_scopes cs, category_category_scope_map ccsm
where c.category_id = ccsm.category_id
and cs.on_what_table =  'companies'
and ccsm.category_scope_id = cs.category_scope_id
and category_record_status =  'a'
order by category_pretty_name";
$rst = $con->execute($sql);
$company_category_menu = $rst->getmenu2('company_category_id', $company_category_id, true);
$rst->close();

// set up Company Source selection menu
$sql = "select company_source_pretty_name, company_source_id from
company_sources where company_source_record_status = 'a' order by company_source_id";
$rst = $con->execute($sql);
$company_source_menu = $rst->getmenu2('company_source_id', $company_source_id, true);
$rst->close();

// set up CRM Status selection menu
$crm_status_menu = build_crm_status_menu($con, $crm_status_id, true);
?>
<form action="companies-list.php" method=get>
<table>
    <tr>
        <td><?php echo _("Company Name"); ?></td>
        <td><?php echo _("Owner"); ?></td>
        <td><?php echo _("Category"); ?></td>
        <td><?php echo _("Source"); ?></td>
        <td><?php echo _("CRM Status"); ?></td>
        <td><?php echo _("City"); ?></td>
        <td><?php echo _("State"); ?></td>
        <td><?php echo _("Country"); ?></td>
    </tr>
    <tr>
            <td><input type=text name=name value="<?php echo $name; ?>"></td>
            <td><?php echo $user_menu; ?></td>
            <td><?php echo $company_category_menu; ?></td>
            <td><?php echo $company_source_menu; ?></td>
            <td><?php echo $crm_status_menu; ?></td>
            <td><input type=text name=city value="<?php echo $city; ?>"></td>
            <td><input type=text name=state value="<?php echo $state; ?>"></td>
            <td><input type=text name=country value="<?php echo $country; ?>"></td>
    </tr>
    <tr>
        <td colspan="8" align="left">
            <input class=button type=submit name="go" value="<?php echo _("Go"); ?>">
            &nbsp;&nbsp;&nbsp;
            <input class=button type=submit name="pdf" value="<?php echo _("PDF"); ?>">
        </td>
    </tr>
</table>
</form>

<div id="report">
<?php
if ($go)
{
    echo companies_list($con,$pdf,$name,$city,$state,$country,$user_id,$company_category_id,
            $company_source_id,$crm_status_id);
}
echo '</div>';
end_page();
exit;

// function that returns the html to be printed or converted to pdf

function companies_list($con,$pdf,$name,$city,$state,$country,$user_id,$company_category_id,
        $company_source_id,$crm_status_id)
{
    if ($pdf)
    {
        $w1=' width="60"';
        $w2=' width="220"';
        $w3=' width="150"';
        $w4=' width="70"';
        $w5=' width="100"';
        $w6=' width="70"';
        $w7=' width="120"';
        $w8=' width="100"';
        $w9=' width="100"';
        $w10=' width="100"';
        $h='';
        $l='';
    } else {
        $w1=' class="widget_';
        $w2=$w1;
        $w3=$w1;
        $w4=$w1;
        $w5=$w1;
        $w6=$w1;
        $w7=$w1;
        $w8=$w1;
        $w9=$w1;
        $w10=$w1;
        $l='content"';
        $h='header"';
    }
    $output = "<table border=0>";
    $output .= "<tr>";
    $output .= "<td$w1$h>" . _("Code") . "</td>";
    $output .= "<td$w2$h>" . _("Company Name") . "</td>";
    $output .= "<td$w3$h>" . _("Address") . "</td>";
    $output .= "<td$w4$h>" . _("Postal Code") . "</td>";
    $output .= "<td$w5$h>" . _("City") . "</td>";
    $output .= "<td$w6$h>" . _("Country") . "</td>";
    $output .= "<td$w7$h>" . _("Tel") . "</td>";
    $output .= "<td$w8$h>" . _("Categories") . "</td>";
    $output .= "<td$w9$h>" . _("User") . "</td>";
    $output .= "<td$w10$h>" . _("Notes") . "</td>";
    $output .= "</tr>\n";
    $sql2 = "SELECT c.company_name, c.company_id, c.company_code, a.line1, a.line2, a.postal_code, a.city, ";
    $sql2 .= "a.province, c.phone, co.country_name, u.username from ";
    $sql2 .= "companies c, addresses a, countries co, users u ";
    if($company_category_id)
    {
        $sql2 .= ", categories ca, category_scopes cs, category_category_scope_map ccsm, entity_category_map ecm ";
    }
    $sql2 .= "where a.company_id=c.company_id and c.default_primary_address=a.address_id ";
    $sql2 .= "and c.company_record_status='a' and a.address_record_status='a' and co.country_id = a.country_id ";
    $sql2 .= "and co.country_record_status='a' and u.user_id = c.user_id ";
    $sql2 .= "and ". multi_cond('a.city',$city);
    $sql2 .= "and ". multi_cond('c.company_name',$name);
    $sql2 .= "and ". multi_cond('co.country_name',$country);
    $sql2 .= "and ". multi_cond('a.province',$state);
    if($user_id)
    {
        $sql2 .= "and u.user_id ='$user_id' ";
    }
    if($company_source_id)
    {
        $sql2 .= "and c.company_source_id = $company_source_id ";
    }
    if($crm_status_id)
    {
        $sql2 .= "and c.crm_status_id = $crm_status_id ";
    }
    if($company_category_id)
    {
        $sql2 .= "and ecm.on_what_table = 'companies' and ecm.on_what_id = c.company_id ";
        $sql2 .= "and ecm.category_id = ca.category_id and cs.category_scope_id = ccsm.category_scope_id ";
        $sql2 .= "and ca.category_id = ccsm.category_id and cs.on_what_table = 'companies' ";
        $sql2 .= "and category_record_status = 'a' and ca.category_id='$company_category_id' ";
    }
    $sql2 .= "order by 1";
    $rst = $con->execute($sql2);
    if ($rst)
    {
        while (!$rst->EOF)
        {
            $output .= "<tr><td$w1$l><a href=../companies/one.php?company_id=".$rst->fields['company_id'].">" . nbsp($rst->fields['company_code']) . "</a></td>";
            $output .= "<td$w2$l>" . nbsp($rst->fields['company_name']) . "</td>";
            $output .= "<td$w3$l>" . nbsp($rst->fields['line1'].' '.$rst->fields['line2']) . "</td>";
            $output .= "<td$w4$l>" . nbsp($rst->fields['postal_code']) . "</td>";
            $output .= "<td$w5$l>" . nbsp($rst->fields['city']) . "</td>";
            $output .= "<td$w6$l>" . nbsp($rst->fields['country_name']) . "</td>";
            $output .= "<td$w7$l>" . nbsp($rst->fields['phone']) . "</td>";
            $company_id = $rst->fields['company_id'];
            $sql3  = "select note_description from notes where on_what_id='$company_id' and ";
            $sql3 .= "on_what_table='companies' and note_record_status='a' order by entered_at desc";
            $categories_sql  = "select category_display_html ";
            $categories_sql .= "from categories c, category_scopes cs, category_category_scope_map ccsm, ";
            $categories_sql .= "entity_category_map ecm ";
            $categories_sql .= "where ecm.on_what_table = 'companies' ";
            $categories_sql .= "and ecm.on_what_id = '$company_id' ";
            $categories_sql .= "and ecm.category_id = c.category_id ";
            $categories_sql .= "and cs.category_scope_id = ccsm.category_scope_id ";
            $categories_sql .= "and c.category_id = ccsm.category_id ";
            $categories_sql .= "and cs.on_what_table = 'companies' ";
            $categories_sql .= "and category_record_status = 'a' ";
            $categories_sql .= "order by category_display_html";
            $rst3 = $con->execute($categories_sql);
            $categories = array();
            if ($rst3)
            {
                while (!$rst3->EOF)
                {
                    array_push($categories, $rst3->fields['category_display_html']);
                    $rst3->movenext();
                }
                $rst3->close();
            }
            $categories = implode($categories, ", ");
            $output .= "<td$w8$l>" . nbsp( $categories)  . "</td>";
            $output .= "<td$w9$l>" . nbsp($rst->fields['username']) . "</td>";
            $rst2 = $con->SelectLimit($sql3, 1, 0);
            if ($rst2)
            {
                $output .= "<td$w10$l>" . nbsp($rst2->fields['note_description']) . "</td>";
                $rst2->close();
            }
            else
            {
                $output .= "<td$w10>&nbsp;</td>";
            }
            $output .= "</tr>\n";
        $rst->movenext();
        }
        $numrows=$rst->RowCount();
        $rst->close();
    }
    $con->close();
    $output .= "</table>";
    $output .= "<p>" . _("Total records") . ": $numrows </p>";
    return $output;
}

// return a condition on field based on boolean combination of AND or OR
// This function should probably be extended to evalute more complex expression
// including brackets, >=. At the moment its fairly simple

function multi_cond ($field,$cond)
{
    global $con;
    $keywords = preg_split("/\s+/",$cond);
    $bool =0;
    $where ='(';
    foreach ($keywords as $k)
    {
        if ($bool == 1 && (strcasecmp('OR',$k)==0 || strcasecmp('AND',$k)==0))
        {
            $bool = 0;
            $where .= "$k ";
        }
        else
        {
            $bool = 1;

// replace * with % (allows user of wildcard *)
            $k = str_replace('*','%',$k);

// implicit % on the end of the string
            $k =  $con->qstr($k.'%', get_magic_quotes_gpc());
            $where .= "$field like $k ";
        }
    }
    $where .= ') ';
    return $where;
}

// if string is not empty or whitespace, leave unchanged
// else return nbsp html character

function nbsp($in)
{
    if (trim($in) !== '') return $in;
    return '&nbsp;';
}

/**
 * $Log: companies-list.php,v $
 * Revision 1.12  2006/01/02 23:46:52  vanmer
 * - changed to use centralized dbconnection function
 *
 * Revision 1.11  2005/10/06 04:30:07  vanmer
 * - updated log entries to reflect addition of code by Diego Ongaro at ETSZONE
 *
 * Revision 1.10  2005/10/04 23:21:44  vanmer
 * Patch to allow sort_order on the company CRM status field, thanks to Diego Ongaro at ETSZONE
 *
 * Revision 1.9  2005/03/21 13:40:58  maulani
 * - Remove redundant code by centralizing common user menu call
 *
 * Revision 1.8  2005/03/16 15:34:23  niclowe
 * added html link to company one.php so you can use this report to go to the company details.
 *
 * Revision 1.7  2005/01/03 06:37:19  ebullient
 * update reports - graphs centered on page, reports surrounded by divs
 *
 * Revision 1.6  2004/12/31 15:35:16  braverock
 * - add company source to search
 * - move buttons to be more visible
 * - patch provided by Ozgur Cayci
 *
 * Revision 1.5  2004/12/30 21:43:13  braverock
 * - localize strings
 *
 * Revision 1.4  2004/12/21 19:36:13  braverock
 * - improved display of screen table
 * - fixed code formatting
 *
 */
?>
