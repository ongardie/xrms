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
require_once($include_directory . 'classes/Pager/GUP_Pager.php');
require_once($include_directory . 'classes/Pager/Pager_Columns.php');

$session_user_id = session_check();
/*
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
*/
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

$page_title = _("Companies - Possible Duplicates");
start_page($page_title, true, $msg);

// $con->debug = 1;

$user_menu = get_user_menu($con, $user_id, true);

/* This sql retreives possible duplicates from your database. It uses these methods to find duplicates
1. If the company_name is the same, it could be a duplicate
2. If the a.company_name is LIKE b.company_name and a.company_id<>b.company_id then it could be a duplicate 
*/
$sql = "SELECT  
CONCAT('<a href=../companies/one.php?company_id=',c1.company_id,'>',c1.company_name,'</a>') as company_name,
c1.company_code,CONCAT('Possible Dupe with ','<a href=../companies/one.php?company_id=',c2.company_id,'>',c2.company_name,' ',c2.company_code,'</a>') as 'Possible Dupe Name',
c1.profile,
c1.phone,
c1.url,
c1.entered_at
FROM `companies` c1, companies c2 
WHERE c1.company_name LIKE c2.company_name AND c1.company_id<>c2.company_id AND c1.company_record_status='a' and c2.company_record_status='a'"; 
//ORDER BY c1.company_name";
				
?>
<div id="report"><form name="CompanyForm">

<?	 
//$pager = new ADODB_Pager($con,$sql);
//$pager->Render(); 

$pager_id='CompanyPager';
$form_id='CompanyForm';
$columns = array();
$columns[] = array('name' => _("Company Name"), 'index_sql' => 'company_name','default_sort' => 'asc');
$columns[] = array('name' => _("Possible Dupe Name"), 'index_sql' => 'Possible Dupe Name');
$columns[] = array('name' => _("Company Code"), 'index_sql' => 'company_code');
$columns[] = array('name' => _("Profile"), 'index_sql' => 'profile');
$columns[] = array('name' => _("Phone"), 'index_sql' => 'phone');
$columns[] = array('name' => _("URL"), 'index_sql' => 'url', 'type' => 'html');
$columns[] = array('name' => _("Entered at"), 'index_sql' => 'entered_at');
$pager2 = new GUP_Pager($con, $sql, null, _('Duplicate Search Results'), $form_id, $pager_id, $columns);
$pager2->Render(100);
?>
</form>
<?
end_page();
exit;

/**
 * $Log: companies-duplicates.php,v $
 * Revision 1.2  2006/11/02 14:19:25  niclowe
 * initial upload of de-dupe reports
 *
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
