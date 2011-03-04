<?php
/**
*
*
* $Id: bulkassignment-1.php,v 1.4 2011/03/04 14:44:52 gopherit Exp $
*
*/

require_once('include-locations-location.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'utils-files.php');
require_once($include_directory . 'utils-activities.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();
$msg = $_GET['msg'];

$return_url = $_POST['return_url'];

$array_of_companies = $_POST['array_of_companies'];

$crm_status_id = $_POST['crm_status_id'];
$company_type_id = $_POST['company_type_id'];
$company_source_id = $_POST['company_source_id'];
$credit_limit = $_POST['credit_limit'];
$industry_id = $_POST['industry_id'];
$account_status_id = $_POST['account_status_id'];
$user_id = $_POST['user_id'];
$rating_id = $_POST['rating_id'];

$company_category_id = $_POST['company_category_id'];
$unlink_category = $_POST['unlink_category'];

$campaign_id = $_POST['campaign_id'];
$unlink_campaign = $_POST['unlink_campaign'];

//$custom1 = $_POST['custom1'];
//$custom2 = $_POST['custom2'];
//$custom3 = $_POST['custom3'];
//$custom4 = $_POST['custom4'];

// Did the user supply any values?
$update_company_record =   ($crm_status_id ||
                            $company_type_id ||
                            $company_source_id ||
                            $credit_limit || 
                            $industry_id || 
                            $account_status_id || 
                            $user_id || 
                            $rating_id ||
                            $company_category_id ||
                            $campaign_id ||
                            $custom1 || 
                            $custom2 || 
                            $custom3 || 
                            $custom4);
if (!$update_company_record){
    $msg = urlencode(_("No changes selected!"));
    header("Location: bulkassignment.php?return_url=$return_url&msg=$msg");
    exit;
}

// Get the database connection
$con = get_xrms_dbconnection();
//$con->debug = 1;

if (is_array($array_of_companies)) {
    $imploded_contacts = implode(',', $array_of_companies);
} elseif (is_numeric($array_of_companies)) {
    $imploded_contacts= $array_of_companies;
}else {
    $msg = urlencode(_("No companies selected!"));
    header("Location: bulkassignment.php?return_url=$return_url&msg=$msg");
    exit;
}

// loop through the contacts and send each one a copy of the message
$sql = "SELECT * FROM companies WHERE company_id IN (" . $imploded_contacts . ")";
$rst = $con->execute($sql);

if ($rst) {

    while (!$rst->EOF) {

        $company_id = $rst->fields['company_id'];
        if (($campaign_id)&&(!$unlink_campaign)){
            // Make sure this company isn't already attached to the campaign
            $chk_sql = "SELECT 1
                        FROM company_campaign_map
                        WHERE company_id = $company_id
                        AND campaign_id = $campaign_id
                        LIMIT 1";
            $chk = $con->Execute($chk_sql);
            if ( $chk->EOF ) {
                $ins_sql = "INSERT INTO company_campaign_map
                            (company_id, campaign_id)
                            VALUES ($company_id, $campaign_id)";
                $con->Execute($ins_sql);
            }
        }
        if (($campaign_id)&&($unlink_campaign)){
           $del ="DELETE FROM company_campaign_map WHERE campaign_id = $campaign_id AND company_id = $company_id LIMIT 1";
           //echo $del; echo "<br>";
           $con->execute($del);
           add_audit_item($con, $session_user_id, 'deleted', 'company_campaign_map', $campaign_id, $company_id);
        }

        $sql = "SELECT * FROM companies WHERE company_id = $company_id";
        $rst1 = $con->execute($sql);

        $rec = array();
        $rec['last_modified_by'] = $session_user_id;
        $rec['last_modified_at'] = mktime();
        if ($crm_status_id) $rec['crm_status_id'] = $crm_status_id;
        if ($company_source_id) $rec['company_source_id'] = $company_source_id;
        if ($industry_id) $rec['industry_id'] = $industry_id;
        if ($rating_id) $rec['rating_id'] = $rating_id;
        if ($user_id) $rec['user_id'] = $user_id;
        if ($credit_limit) $rec['credit_limit'] = $credit_limit;
        if ($account_status_id) $rec['account_status_id'] = $account_status_id;
        if ($custom1) $rec['custom1'] = $custom1;
        if ($custom2) $rec['custom2'] = $custom2;
        if ($custom3) $rec['custom3'] = $custom3;
        if ($custom4) $rec['custom4'] = $custom4;
        if ($company_type_id)  $rec['company_type_id'] = $company_type_id;
        if (($company_category_id)&&(!$unlink_category)){

            $rec1 = array();
            $rec1['category_id'] = $company_category_id;
            $rec1['on_what_table'] = 'companies';
            $rec1['on_what_id'] = $company_id;
            $tbl = 'entity_category_map';
            $ins = $con->GetInsertSQL($tbl, $rec1, get_magic_quotes_gpc());
            //echo $ins; exit;

            $con->execute($ins);
            add_audit_item($con, $session_user_id, 'created', 'entity_category_map', $company_category_id, $company_id);

        }
        if (($company_category_id)&&($unlink_category)){
            $del ="DELETE FROM entity_category_map WHERE on_what_table ='companies' AND category_id = $company_category_id AND on_what_id = $company_id LIMIT 1";
            //echo $del; echo "<br>";
            $con->execute($del);
            add_audit_item($con, $session_user_id, 'deleted', 'entity_category_map', $company_category_id, $company_id);
        }
        if ($update_company_record){
            $upd = $con->GetUpdateSQL($rst1, $rec, false, get_magic_quotes_gpc());
            //echo $upd; exit;
            $sysst = $con->execute($upd);
            if (!$sysst){
                 //there was a problem, notify the user
                 db_error_handler ($con, $upd);
            }
            $param = array($rst1, $rec);
            do_hook_function('company_edit_2', $param);
            $accounting_rows = do_hook_function('company_accounting_inline_edit_2', $accounting_rows);
            add_audit_item($con, $session_user_id, 'updated', 'companies', $company_id, 1);
        }
        $rst->movenext();
    }   // WHILE

    $rst->close();
    $feedback = '<p /><b>' . _("Bulk update done") . '.</b>';
} else {
    // Failed to create contact list
    db_error_handler($con, $sql);
}

$con->close();

header("Location: " . $http_site_root . $return_url);

/**
 * $Log: bulkassignment-1.php,v $
 * Revision 1.4  2011/03/04 14:44:52  gopherit
 * FIXED Bug Artifact #1848983: The script now checks whether a company has already been attached to a campaign before inserting the record.
 *
 * Revision 1.3  2010/12/17 21:29:05  gopherit
 * General code cleanup.
 *
 * Revision 1.2  2007/10/19 18:31:34  randym56
 * - Fixed bugs preventing bulk updates to companies
 *
 * Revision 1.1  2006/10/01 00:15:06  braverock
 * - Initial Revision of Bulk Activity and Bulk Assignment contributed by Danielle Baudone
 *
 *
 */
?>