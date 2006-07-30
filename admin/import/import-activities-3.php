<?php
/**
 * import-activities-3.php - File importer for XRMS
 *
 *
 * $Id: import-activities-3.php,v 1.2 2006/07/30 11:13:17 jnhayart Exp $
 */

require_once('../../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

/**
 * function importFailedMessage - debug function for import
 *
 * @author Mark Spoorendonk
 *
 * @param string  $str
 * @param optional boolean $success default=false
 *
 * @todo Add adodb error handler here... pass &$con as a parameter by reference
 */
function importFailedMessage($str) {
    return importMessage($str, false);
}

/**
 * function debugSQL - debug function for import
 *
 * @author Mark Spoorendonk
 *
 * @param string  $sql
 */
function debugSql($sql) {
    return; // comment out this line for debuging
    echo "<code><pre>";
    print_r($sql);
    echo "</pre></code>\n";
}

/**
 * function importMessage - debug function for import
 *
 * @author Mark Spoorendonk
 *
 * @param string  $str
 * @param optional boolean $success default=true
 */
function importMessage($str, $success=true) {
    return; // comment out this line for debuging
    $color="#ffb0b0"; // red
    if($success) $color="#b0ffb0"; // green
    echo "<div style=\"background-color: $color\">$str</div>\n";
}

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
 
$pointer = (strlen($_POST['pointer']) > 0) ? $_POST['pointer'] : 0;

$page_title = _("Import Data");

start_page($page_title, true, $msg);
?>

<table border=0 cellpadding=0 cellspacing=0 width=100%>
   <tr>
     <td>
       <table class=widget cellspacing=1>
           <tr>
               <td class=widget_header colspan=8><?php echo _("Imported Data"); ?></td>
           </tr>

       <tr>
           <!-- base company info //-->
           <td class=widget_header colspan=3><?php echo _("Company"); ?></td>

           <!-- contact info //-->
           <td class=widget_header colspan=3><?php echo _("Contact Info"); ?></td>

           <!-- address info //-->
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
           <td class=widget_content><?php echo _("summary"); ?></td>
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

$con = get_xrms_dbconnection();

$row_number = 1;

//this will create a huge ammount of debug data
//$con->debug=1;

//get the data array
$filearray = CSVtoArray($tmp_upload_directory . 'activities-to-import.txt', true , $delimiter, $enclosure);
// @todo could better accomodate microsoft outlook by looking for outlook field names

//debug line to view the array
//echo "\n<br><pre>". print_r ($filearray). "\n</pre>";

//fill up our variables from the array, where they exist
foreach ($filearray as $row) 
{
    //clear our working variables
$company_id =0;
$contact_id =0;

    //assign array values to variables

    require($template);

    // does this company exist,
    // $company_id = fetch_company_id($con, $company_name);

    // does this company exist,
    
    $sql_fetch_company_id = "select comp.company_id,cont.contact_id from companies comp, contacts cont where
                            cont.company_id =  comp.company_id and 
                            comp.company_name = '" . addslashes($company_name) ."' and ";
    if ( $contact_first_name = '' )
    {
        $sql_fetch_company_id .= "cont.first_names = '" . addslashes($contact_first_names) . "' and";
    }
    $sql_fetch_company_id .= " cont.last_name = '" . addslashes($contact_last_name) . "' and
                            cont.contact_record_status='a' and
                            comp.company_record_status='a' " ;

//   echo "\n<br><pre> Recherche Complete ". $sql_fetch_company_id . "\n</pre>" ;
    
    $rst_company_id = $con->execute($sql_fetch_company_id);

    if ( $rst_company_id AND !$rst_company_id->EOF )
    {
        $company_id = $rst_company_id->fields['company_id'];
        $contact_id = $rst_company_id->fields['contact_id'];
        
        $rst_company_id->close();
    } 
    else 
    {
        $company_id = 0;
          $sql_fetch_company_id = "select comp.company_id from companies comp where
                                  comp.company_name =  '" . addslashes($company_name) ."' and
                                  comp.company_record_status='a' " ;
//   echo "\n<br><pre> Recherche Société uniquement ". $sql_fetch_company_id . "\n</pre>" ;
               
          $rst_company_id = $con->execute($sql_fetch_company_id);
      
          if ($rst_company_id) 
          {
              $company_id = $rst_company_id->fields['company_id'];
              $contact_id = 0;
              $rst_company_id->close();
          } 
          else 
          {
              $company_id = 0;
            $contact_id = 0;
      
      
          }



    }
    if ( $company_id <> 0 )
    {
        $sql_insert_activity = "insert into activities set
                        activity_type_id = $activity_type_id,
                        user_id = $user_id,
                        company_id = $company_id,
                        contact_id = $contact_id,
                        activity_title = '".addslashes($activity_title)."',
                        activity_description = '".addslashes($opportunity_description)."',
                        entered_at = ".$con->dbtimestamp(mktime()).",
                        last_modified_at = ".$con->dbtimestamp(mktime()).",
                        last_modified_by = $session_user_id,";

               if ($scheduled_at)
               {
                  $sql_insert_activity .= " scheduled_at='" . $scheduled_at . "', ";
               }
               else
               {
                  $sql_insert_activity .= " scheduled_at=".$con->dbtimestamp(mktime()).", ";
               }

               if ($ends_at)
               {
                  $sql_insert_activity .= " ends_at='" . $ends_at . "', ";
               }
               else
               {
                  $sql_insert_activity .= " ends_at=".$con->dbtimestamp(mktime()).", ";
               }

            if ($activity_status)
            {
                $sql_insert_activity .=" activity_status = 'c', ";
             }
             else
            {
                $sql_insert_activity .=" activity_status = 'o', ";
             }
             
             if ($campaign_id) {
                $sql_insert_activity .=" on_what_table = 'campaigns', ";
                $sql_insert_activity .=" on_what_id = $campaign_id, ";
             }
             	
                        
        $sql_insert_activity .=" entered_by = $session_user_id;";
                        $act_rst = $con->execute($sql_insert_activity);
                        if ( ! $act_rst )
                        {
                            db_error_handler( $con, $sql_insert_activity );
                        }
      }

     // end company_name insert/update check

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
}; //end foreach, loop back and do the next row.

$con->close();
?>

        </table>

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
<form action=../../../admin/data_clean.php method=get>
    <input class=button type=submit value="<?php echo _("Run Data Cleanup"); ?>">
</form>

<?php
end_page();

/**
 * $Log: import-activities-3.php,v $
 * Revision 1.2  2006/07/30 11:13:17  jnhayart
 * use centralized get_xrms_dbconnection();
 *
 * Revision 1.1  2006/07/30 11:11:14  jnhayart
 * Add files for import activities
 * First release based on import-companies
 *
 *
 */
?>
