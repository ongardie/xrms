<?php
/**
 * data_purge.php - Cleanup the database by removing all records with a status of 'd'
 *
 * @author Randy Martinsen
 *
 */

// where do we include from
require_once('../include-locations.inc');

// get required common files
// vars.php sets all of the installation-specific variables
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check( 'Admin' );

// make a database connection
$con = get_xrms_dbconnection();
//$con->debug = 1;

$tables_array = array();
$tables_array[] = 'accounts';
$tables_array[] = 'account_statuses';
$tables_array[] = 'activities';
$tables_array[] = 'activity_participants';
$tables_array[] = 'activity_templates';
$tables_array[] = 'addresses';
$tables_array[] = 'audit_items';
$tables_array[] = 'campaigns';
$tables_array[] = 'campaign_statuses';
$tables_array[] = 'cases';
$tables_array[] = 'case_priorities';
$tables_array[] = 'case_statuses';
$tables_array[] = 'case_types';
$tables_array[] = 'categories';
$tables_array[] = 'category_scopes';
$tables_array[] = 'companies';
$tables_array[] = 'company_division';
$tables_array[] = 'company_sources';
$tables_array[] = 'company_types';
$tables_array[] = 'contacts';
$tables_array[] = 'crm_statuses';
$tables_array[] = 'email_templates';
$tables_array[] = 'files';
$tables_array[] = 'industries';
$tables_array[] = 'notes';
$tables_array[] = 'opportunities';
$tables_array[] = 'opportunity_statuses';
$tables_array[] = 'opportunitity_types';
$tables_array[] = 'relationships';
$tables_array[] = 'users';
$tables_array[] = 'user_preferences';

//each table with a "record_status" field is touched and all records deleted with 'd' status
foreach ($tables_array as $table) {
	$table_singular = make_singular($table);
	$sql = "DELETE FROM $table WHERE {$table_singular}_record_status = 'd'";
	$rst = $con->execute($sql);
	}
		
//close the database connection, because we don't need it anymore
$con->close();

$page_title = _("Database Purge Complete");
start_page($page_title, true, $msg);

// echo $msg;
?>

<BR>
<?php echo _("Your database has been purged of all records with a status of 'd'."); ?>
<BR><BR>


<?php

end_page();

/**
 * $Log: data_purge.php,v $
 * Revision 1.1  2008/02/19 23:28:00  randym56
 * Purge all records from all files that contain {table_singular}_record_status = 'd'
 *
 *
 */
?>
