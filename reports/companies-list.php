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

$session_user_id = session_check();
$msg = $_GET['msg'];
$go = $_REQUEST['go'];
$pdf = $_REQUEST['pdf'];
$name = $_REQUEST['name'];
$user_id = $_REQUEST['user_id'];
$company_category_id = $_REQUEST['company_category_id'];
$crm_status_id = $_REQUEST['crm_status_id'];
$city = $_REQUEST['city'];
$state = $_REQUEST['state'];
$country = $_REQUEST['country'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

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
	$pdf->WriteHTML(companies_list($con,$pdf,$name,$city,$state,$country,$user_id,$company_category_id,$crm_status_id));
	$pdf->Output();
	exit;
}

// if we are here then its for an html page

$page_title = _("Companies List");
start_page($page_title, true, $msg);

// $con->debug = 1;

// set up the user selection menu
$sql = "select username, user_id from users where user_record_status = 'a' order by username";
$rst = $con->execute($sql);
$user_menu = $rst->getmenu2('user_id', $user_id, true);
$rst->close();

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

// set up CRM Status selection menu
$sql = "select crm_status_pretty_name, crm_status_id from 
crm_statuses where crm_status_record_status = 'a' order by crm_status_id";
$rst = $con->execute($sql);
$crm_status_menu = $rst->getmenu2('crm_status_id', $crm_status_id, true);
$rst->close();
?>

<table>
	<tr>
		<th><?php echo _("Company Name"); ?></th>
		<th><?php echo _("Owner"); ?></th>
		<th><?php echo _("Category"); ?></th>
		<th><?php echo _("CRM Status"); ?></th>
		<th><?php echo _("City"); ?></th>
		<th><?php echo _("State"); ?></th>
		<th><?php echo _("Country"); ?></th>
		<th>&nbsp;</th>
		<th>&nbsp;</th>
	</tr>
	<tr>
		<form action="companies-list.php" method=get>
			<td><input type=text name=name value="<?php echo $name; ?>"></td>
			<td><?php echo $user_menu; ?></td>
			<td><?php echo $company_category_menu; ?></td>
			<td><?php echo $crm_status_menu; ?></td>
			<td><input type=text name=city value="<?php echo $city; ?>"></td>
			<td><input type=text name=state value="<?php echo $state; ?>"></td>
			<td><input type=text name=country value="<?php echo $country; ?>"></td>
			<td><input class=button type=submit name="go" value="<?php echo ("Go"); ?>"></td>
			<td><input class=button type=submit name="pdf" value="<?php echo ("PDF"); ?>"></td>
		</form>
	</tr>
</table>
<?php
if ($go) 
{
	echo companies_list($con,$pdf,$name,$city,$state,$country,$user_id,$company_category_id,$crm_status_id);
}
end_page();
exit;

// function that returns the html to be printed or converted to pdf

function companies_list($con,$pdf,$name,$city,$state,$country,$user_id,$company_category_id,$crm_status_id)
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
	}
	$output = "<table border=0>";
	$output .= "<tr>";
	$output .= "<th$w1>" . _("Code") . "</th>";
	$output .= "<th$w2>" . _("Company Name") . "</th>";
	$output .= "<th$w3>" . _("Address") . "</th>";
	$output .= "<th$w4>" . _("Postal Code") . "</th>";
	$output .= "<th$w5>" . _("City") . "</th>";
	$output .= "<th$w6>" . _("Country") . "</th>";
	$output .= "<th$w7>" . _("Tel") . "</th>";
	$output .= "<th$w8>" . _("Categories") . "</th>";
	$output .= "<th$w9>" . _("User") . "</th>";
	$output .= "<th$w10>" . _("Notes") . "</th>";
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
	if($crm_status_id)
	{
		$sql2 .= "and c.crm_status_id = $crm_status_id ";
	}
	if($company_category_id)
	{
		$sql2 .= "and ecm.on_what_table = 'companies' and ecm.on_what_id = c.company_id ";
		$sql2 .= "and ecm.category_id = ca.category_id and cs.category_scope_id = ccsm.category_scope_id ";
		$sql2 .= "and ca.category_id = ccsm.category_id and cs.on_what_table = 'companies' ";
		$sql2 .= "and category_record_status = 'a' and ca.category_id=$company_category_id ";
	}
	$sql2 .= "order by 1";
	$rst = $con->execute($sql2);
	if ($rst) 
	{
		while (!$rst->EOF) 
		{
            $output .= "<tr><td$w1>" . nbsp($rst->fields['company_code']) . "</td>";
            $output .= "<td$w2>" . nbsp($rst->fields['company_name']) . "</td>";
            $output .= "<td$w3>" . nbsp($rst->fields['line1'].' '.$rst->fields['line2']) . "</td>";
            $output .= "<td$w4>" . nbsp($rst->fields['postal_code']) . "</td>";
            $output .= "<td$w5>" . nbsp($rst->fields['city']) . "</td>";
            $output .= "<td$w6>" . nbsp($rst->fields['country_name']) . "</td>";
            $output .= "<td$w7>" . nbsp($rst->fields['phone']) . "</td>";
		    $company_id = $rst->fields['company_id'];
    		$sql3  = "select note_description from notes where on_what_id=$company_id and ";
			$sql3 .= "on_what_table='companies' and note_record_status='a' order by entered_at desc limit 1";
            $categories_sql  = "select category_display_html ";
			$categories_sql .= "from categories c, category_scopes cs, category_category_scope_map ccsm, ";
			$categories_sql .= "entity_category_map ecm ";
			$categories_sql .= "where ecm.on_what_table = 'companies' ";
			$categories_sql .= "and ecm.on_what_id = $company_id ";
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
			$output .= "<td$w8>" . nbsp( $categories)  . "</td>";
			$output .= "<td$w9>" . nbsp($rst->fields['username']) . "</td>";
			$rst2 = $con->execute($sql3);
			if ($rst2)
			{
				$output .= "<td$w10>" . nbsp($rst2->fields['note_description']) . "</td>";
			}
			else
			{
				$output .= "<td$w10>&nbsp;</td>";
			}
			$rst2->close();
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

// return a condiation on field based on boolean combination of AND or OR
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
	if(trim($in) != '') return $in;
	return '&nbsp;';
}
?>
