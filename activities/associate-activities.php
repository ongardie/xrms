<?php
/**
 * Associate Activities with open Opportunity or Case
 *
 * @author Brian Peterson
 *
 * $Id: associate-activities.php,v 1.4 2006/01/02 21:23:18 vanmer Exp $
 */


//include required files
require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();

$con = get_xrms_dbconnection();
//$con->debug=1;

$color_counter = 0;

$page_title = _("Associate Activities");

start_page($page_title, true, $msg);
echo '<table>';
echo "\n".'<tr><td class=widget_header>' . _("Company") . '</td><td class=widget_header>' . _("Action") . '</td>';
//get list of active companies
$company_sql="select company_id, company_name from companies where company_record_status = 'a' order by company_name";

$company_rst = $con->execute($company_sql);
$company_array=array();

if ($company_rst) {
    while (!$company_rst->EOF) {
        $opp_arr = array();
        $case_arr = array();
        $arr_count = 0;


        $company_id   = $company_rst->fields['company_id'];
        $company_name = $company_rst->fields['company_name'];

        //get active opportunity ids for each company
        $opp_sql = "SELECT o.opportunity_id
                    FROM opportunities o
                    LEFT JOIN opportunity_statuses os ON (o.opportunity_status_id = os.opportunity_status_id)
                    WHERE o.company_id = $company_id
                      AND os.status_open_indicator = 'o'
                      AND o.opportunity_record_status='a'";
        $opp_rst = $con->execute($opp_sql);
        if ($opp_rst) {
            if ($opp_rst->RecordCount()>=1){
                while (!$opp_rst->EOF) {
                    $opp_arr[]=$opp_rst->fields['opportunity_id'];
                    $opp_rst->movenext();
                    $arr_count++;
                }
                //echo '<br><pre>Opp Arr:'.print_r($opp_arr).'</pre>';
            }
            $opp_rst->close();
        } else {
            db_error_handler ($con,$opp_sql);
        }

        //get active case ids for each company
        $case_sql = "SELECT c.case_id
                     FROM cases c
                     LEFT JOIN case_statuses cs ON (c.case_status_id = cs.case_status_id)
                     WHERE c.company_id = $company_id
                       AND cs.status_open_indicator = 'o'
                       AND c.case_record_status='a'";
        $case_rst = $con->execute($case_sql);
        if ($case_rst) {
            if ($case_rst->RecordCount()>=1){
                while (!$case_rst->EOF) {
                    $case_arr[]=$case_rst->fields['case_id'];
                    $case_rst->movenext();
                    $arr_count++;
                }
                //echo '<br><pre>Case Arr: '.print_r($case_arr).'</pre>';
            }
            $case_rst->close();
        } else {
            db_error_handler ($con,$case_sql);
        }

        //echo '<br>Array Count = '.$arr_count.'<br>';

        if ($arr_count==1) {
            //if only one item in list, associate open activities with the open opportunity/case
            $activity_count = 0;
            // get the activity list
            $activity_sql = "select activity_id from activities where
                             activity_record_status = 'a'
                             and company_id = $company_id
                             and (on_what_table = '' or on_what_table='contacts')";
            $activity_rst = $con->execute($activity_sql);
            if ($activity_rst) {
                $activity_count=0;
                while (!$activity_rst->EOF) {
                    $rec = array();
                    $activity_count++;
                    // do the updates
                    $rec['activity_id'] = $activity_rst->fields['activity_id'];
                    //reset($case_arr);
                    //reset($opp_arr);

                    if (count($case_arr)){
                        //echo '<pre>'.print_r($case_arr).'</pre>';
                        $rec['on_what_table'] = 'cases';
                        $rec['on_what_id']    = $case_arr[0];
                    }
                    if (count($opp_arr)){
                        //echo '<pre>'.print_r($opp_arr).'</pre>';
                        $rec['on_what_table'] = 'opportunities';
                        $rec['on_what_id']    = $opp_arr[0];
                    }
                    //echo '<pre>'.print_r($rec).'</pre>';
                    $con->debug=1;
                    $sql = "SELECT * FROM activities WHERE activity_id = " . $rec['activity_id'];
                    $rst = $con->execute($sql);
                    $upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
                    $upd_rst = $con->execute($upd);

                    //echo '<br>'.$upd;
                    $con->debug=0;

                    //if (strlen($upd)) {$rst = $con->execute($upd);}
                    if (!$upd_rst) { db_error_handler($con,$upd); };

                    $activity_rst->movenext();
                }
                $activity_rst->close();
            } else {
                db_error_handler ($con, $activity_sql);
            }
            //print some useful information
            $color_counter++;
            $classname = (($color_counter % 2) == 1) ? "widget_content" : "widget_content_alt";
            echo "\n<tr><td class=$classname>$company_name</td>";
            echo "<td class=$classname>" . _("Associated") . " $activity_count " . _("activities") . ".</td>";
            echo '</tr>';
        } elseif ($arr_count>1) {
            //print some useful information and move along
            $color_counter++;
            $classname = (($color_counter % 2) == 1) ? "widget_content" : "widget_content_alt";
            echo "\n<tr><td class=$classname>$company_name</td>";
            echo "<td class=$classname>" . _("Unable to associate Activities because there are multiple Cases or Opportunities") . "</td>";
            echo '</tr>';
        } else {
            //display notice if no active opportunity or case for a company
            $color_counter++;
            $classname = (($color_counter % 2) == 1) ? "widget_content" : "widget_content_alt";
            echo "\n<tr><td class=$classname>$company_name</td>";
            echo "<td class=$classname>" . _("Unable to associate Activities because there is no open Case or Opportunity") . "</td>";
            echo '</tr>';
        }

        //move to the next record and loop back to the beginning
        $company_rst->movenext();
    }
    $company_rst->close();
} else {
    db_error_handler ($con, $company_sql);
}

echo '</table>';

end_page();

/**
 * $Log: associate-activities.php,v $
 * Revision 1.4  2006/01/02 21:23:18  vanmer
 * - changed to use centralized database connection function
 *
 * Revision 1.3  2004/07/16 04:44:24  introspectshun
 * - Localized strings for i18n/translation support
 * - Altered LEFT JOINs to use standard ON syntax rather than USING
 *
 * Revision 1.2  2004/07/13 19:58:56  braverock
 * - fixed cut and paste error in case checking
 *
 * Revision 1.1  2004/07/13 19:51:29  braverock
 * - Initial Revision
 * - Administrative script to Accociate Activities with open Opportunities/Cases
 *
 */
?>