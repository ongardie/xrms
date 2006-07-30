<?php
/**
 * import-Activites-2.php - File importer for XRMS
 *
 *
 * $Id: import-activities-2.php,v 1.1 2006/07/30 11:11:14 jnhayart Exp $
 */
 
require_once('../../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check( 'Admin' );

$delimiter = $_POST['delimiter'];
$user_id = $_POST['user_id'];
$activity_type_id = $_POST['activity_type_id'];
$activity_title = $_POST['activity_title'];
$opportunity_description = $_POST['opportunity_description'];
$scheduled_at = $_POST['scheduled_at'];
$ends_at = $_POST['ends_at'];
$activity_status = $_POST['activity_status'];
$file_format=$_POST['file_format'];
$template='import-template-' . $file_format . '.php';
$campaign_id=$_POST['campaign_id'];

move_uploaded_file($_FILES['file1']['tmp_name'], $tmp_upload_directory . 'activities-to-import.txt');

$page_title = _("Preview Data");

start_page($page_title, true, $msg);
?>
<table border=0 cellpadding=0 cellspacing=0 width=100%>
    <tr>
        <td class=lcol width=65% valign=top>

        <form action="import-activities-3.php" method="post">
        <input type=hidden name=file_format value="<?php echo $file_format; ?>">
        <input type=hidden name=delimiter value="<?php echo $delimiter; ?>">
        <input type=hidden name=user_id value="<?php echo $user_id; ?>">
        <input type=hidden name=activity_type_id value="<?php echo $activity_type_id; ?>">
        <input type=hidden name=activity_title value="<?php echo $activity_title; ?>">
        <input type=hidden name=opportunity_description value="<?php echo $opportunity_description; ?>">
        <input type=hidden name=scheduled_at value="<?php echo $scheduled_at; ?>">
        <input type=hidden name=ends_at value="<?php echo $ends_at; ?>">
        <input type=hidden name=activity_status value="<?php echo $activity_status; ?>">
        <input type=hidden name=campaign_id value="<?php echo $campaign_id; ?>">
        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=8><?php echo _("Preview Data"); ?></td>
            </tr>

        <tr>
            <!-- base company info //-->
            <td class=widget_header colspan=3><?php echo _("Company"); ?></td>

            <!-- contact info //-->
            <td class=widget_header colspan=3><?php echo _("Contact Info"); ?></td>

            <!-- Activites info //-->
            <td class=widget_header colspan=2><?php echo _("Activities"); ?></td>
       </tr>
       <tr>
           <td class=widget_content><?php echo _("Row Number"); ?></td>

           <!-- base company info //-->
           <td class=widget_content><?php echo _("Company ID"); ?></td>
           <td class=widget_content><?php echo _("Company Name"); ?></td>

           <!-- contact info //-->
           <td class=widget_content><?php echo _("Contact ID"); ?></td>
           <td class=widget_content><?php echo _("First Names"); ?></td>
           <td class=widget_content><?php echo _("Last Name"); ?></td>

           <!-- Activities info //-->
           <td class=widget_content><?php echo _("Summary"); ?></td>
           <td class=widget_content><?php echo _("Activity Notes"); ?></td>
 
       </tr>
<?php
switch ($delimiter) {
    case 'comma':
        $delimiter = ",";
        break;
    case 'tab':
        $delimiter = "\t";
        break;
    case 'pipe':
        $delimiter = "|";
        break;
    case 'semi-colon':
        $delimiter = ";";
        break;
}


$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
//$con->debug=1;

$row_number = 1;

//get the data array
$filearray = CSVtoArray($tmp_upload_directory . 'activities-to-import.txt', true , $delimiter, $enclosure);

//debug line to view the array
//echo "\n<br><pre>". print_r ($filearray). "\n</pre>";

//fill up our variables from the array, where they exist
foreach ($filearray as $row) {
    //debug line to view the array
    //echo "\n<br><pre>". print_r ($row). "\n</pre>";
$company_id =0;
$contact_id =0;

    //assign array values to variables

    require($template);

    // does this company exist,
    // $company_id = fetch_company_id($con, $company_name);

// select comp.company_id,cont.contact_id from companies comp left join contacts cont using (company_id) where
// comp.company_name = "vallourec" and
// cont.first_names = "Jean" and cont.contact_record_status='a' and comp.company_record_status='a' 

	$company_id = 0;
	$contact_id = 0;

    // does this company exist,
    
    $sql_fetch_company_id = "select comp.company_id from companies comp where
                             comp.company_name = '" . addslashes($company_name) ."' and comp.company_record_status='a' " ;
                            
                            

//   echo "\n<br><pre> Recherche Complete ". $sql_fetch_company_id . "\n</pre>" ;
    
    $rst_company_id = $con->execute($sql_fetch_company_id);

    if ( $rst_company_id AND !$rst_company_id->EOF )
    {
        $company_id = $rst_company_id->fields['company_id'];
        $rst_company_id->close();
        // we have compagny search contact 
		
		$sql_fetch_company_id = "select cont.contact_id from contacts cont where ";        
	    if ( $contact_first_name = '' )
	    {
	        $sql_fetch_company_id .= "cont.first_names = '" . addslashes($contact_first_names) . "' and ";
	    }
	    $sql_fetch_company_id .= " cont.last_name = '" . addslashes($contact_last_name) . "' and
	                            cont.contact_record_status='a' and cont.company_id =" . $company_id;
        $rst_company_id = $con->execute($sql_fetch_company_id);
      
        if ( $rst_company_id AND !$rst_company_id->EOF ) 
        {
           $contact_id = $rst_company_id->fields['contact_id'];
           $rst_company_id->close();
       } 
    } 

    //now show the row
    echo <<<TILLEND
       <tr>
           <td class=widget_content>$row_number</td>

           <!-- base company info //-->
           <td class=widget_content>$company_id</td>
           <td class=widget_content>$company_name</td>

           <!-- contact info //-->
           <td class=widget_content>$contact_id</td>
           <td class=widget_content>$contact_first_names</td>
           <td class=widget_content>$contact_last_name</td>

           <!-- address info //-->
           <td class=widget_content>$activity_title</td>
           <td class=widget_content>$opportunity_description</td>
       </tr>
TILLEND;


    $row_number = $row_number + 1;
} //end foreach, loop back to do the next row in the file

//fclose($handle);
$con->close();
?>
            <tr>
                <td class=widget_content><input class=button type=submit value="<?php echo _("Import"); ?>"></td>
            </tr>
        </table>
        </form>

        </td>
        <!-- gutter //-->
        <td class=gutter width=2%>
        &nbsp;
        </td>
        <!-- right column //-->
        <td class=rcol width=33% valign=top>

        </td>
    </tr>
</table>

<?php
end_page();

/**
 * $Log: import-activities-2.php,v $
 * Revision 1.1  2006/07/30 11:11:14  jnhayart
 * Add files for import activities
 * First release based on import-companies
 *
 *
 */
?>