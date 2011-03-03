<?php
/**
 * data_purge.php - Purge all records with a record status of 'd' from the database
 *
 * The purge schema must be kept up-to-date to reflect changes to the database:
 * - adding/removal of database tables
 * - adding/removal/renaming of record status fields
 *
 * The purge schema is presently synched to database version 1.99.8
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

/**
 * ************************** DATABASE PURGE SCHEMA **************************
 * 
 * The database table and field naming convention is inconsistent throughout the XRMS database
 * so we'll need to create the purge schema manually.  Commented out lines represent tables
 * that do not have a 'record_status' field.  Purging records from these tables using the XRMS
 * convention is not possible at this time.
 * 
 * @todo The database schema should be changed to strictly follow the naming convention where 
 * there is a 'record_status' field for every table.  No, I am not kidding!
 */

$purge_schema = array();
$purge_schema['account_statuses']               = 'account_status_record_status';
$purge_schema['activities']                     = 'activity_record_status';
// $purge_schema['activities_recurrence']          = 'activity_recurrence_record_status';
$purge_schema['activity_participants']          = 'ap_record_status';
// $purge_schema['activity_participant_positions'] = 'activity_participant_position_record_status';
$purge_schema['activity_resolution_types']      = 'resolution_type_record_status';
$purge_schema['activity_templates']             = 'activity_template_record_status';
$purge_schema['activity_types']                 = 'activity_type_record_status';
$purge_schema['addresses']                      = 'address_record_status';
$purge_schema['address_format_strings']         = 'address_format_string_record_status';
// $purge_schema['address_types']                  = 'address_type_record_status';
$purge_schema['audit_items']                    = 'audit_item_record_status';
$purge_schema['campaigns']                      = 'campaign_record_status';
$purge_schema['campaign_lists']                 = 'campaign_list_record_status';
$purge_schema['campaign_statuses']              = 'campaign_status_record_status';
$purge_schema['campaign_types']                 = 'campaign_type_record_status';
$purge_schema['cases']                          = 'case_record_status';
$purge_schema['case_priorities']                = 'case_priority_record_status';
$purge_schema['case_statuses']                  = 'case_status_record_status';
$purge_schema['case_types']                     = 'case_type_record_status';
$purge_schema['categories']                     = 'category_record_status';
// $purge_schema['category_category_scope_map']    = 'category_category_scope_map_record_status';
$purge_schema['category_scopes']                = 'category_scope_record_status';
$purge_schema['companies']                      = 'company_record_status';
// $purge_schema['company_campaign_map']           = 'company_campaign_map_record_status';
$purge_schema['company_division']               = 'division_record_status';
// $purge_schema['company_former_names']           = 'company_former_name_record_status';
// $purge_schema['company_relationship']           = 'company_relationship_record_status';
$purge_schema['company_sources']                = 'company_source_record_status';
$purge_schema['company_types']                  = 'company_type_record_status';
$purge_schema['contacts']                       = 'contact_record_status';
// $purge_schema['contact_former_companies']       = 'contact_former_company_record_status';
// $purge_schema['ControlledObject']               = 'ControlledObject_record_status';
// $purge_schema['ControlledObjectRelationship']   = 'ControlledObjectRelationship_record_status';
$purge_schema['countries']                      = 'country_record_status';
$purge_schema['crm_statuses']                   = 'crm_status_record_status';
// $purge_schema['data_source']                    = 'data_source_record_status';
$purge_schema['email_templates']                = 'email_template_record_status';
// $purge_schema['email_template_type']            = 'email_template_type_record_status';
// $purge_schema['entity_category_map']            = 'entity_category_map_record_status';
$purge_schema['files']                          = 'file_record_status';
// $purge_schema['GroupMember']                    = 'GroupMember_record_status';
// $purge_schema['GroupMemberCriteria']            = 'GroupMemberCriteria_record_status';
// $purge_schema['Groups']                         = 'Groups_record_status';
// $purge_schema['GroupUser']                      = 'GroupUser_record_status';
$purge_schema['industries']                     = 'industry_record_status';
$purge_schema['notes']                          = 'note_record_status';
$purge_schema['opportunities']                  = 'opportunity_record_status';
$purge_schema['opportunity_statuses']           = 'opportunity_status_record_status';
$purge_schema['opportunity_types']              = 'opportunity_type_record_status';
// $purge_schema['pager_saved_view']               = 'pager_saved_view_record_status';
// $purge_schema['Permission']                     = 'Permission_record_status';
$purge_schema['ratings']                        = 'rating_record_status';
// $purge_schema['recent_items']                   = 'recent_item_record_status';
$purge_schema['relationships']                  = 'relationship_status';
$purge_schema['relationship_types']             = 'relationship_status';
// $purge_schema['Role']                           = 'Role_record_status';
// $purge_schema['RolePermission']                 = 'RolePermission_record_status';
// $purge_schema['salutations']                    = 'salutation_record_status';
$purge_schema['saved_actions']                  = 'saved_status';
// $purge_schema['sessions']                       = 'session_record_status';
// $purge_schema['time_daylight_savings']          = 'time_daylight_savings_record_status';
// $purge_schema['time_zones']                     = 'time_zone_record_status';
$purge_schema['users']                          = 'user_record_status';
$purge_schema['user_preferences']               = 'user_preference_status';
$purge_schema['user_preference_types']          = 'user_preference_type_status';
$purge_schema['user_preference_type_options']   = 'option_record_status';
// $purge_schema['workflow_history']               = 'workflow_history_record_status';


// Make a database connection
$con = get_xrms_dbconnection();
//$con->debug = 1;

// Purge all records with a record_status of 'd'
foreach ($purge_schema as $table => $record_status_field) {
    $sql = "DELETE FROM $table WHERE $record_status_field = 'd'";
    $rst = $con->execute($sql);
    }
		
// Provide a hook to plugins if they need to do their own data purging
do_hook_function ('xrms_db_purge', $con);

// Close the database connection, because we don't need it anymore
$con->close();

$msg = _("Your database has been purged of all records with a status of 'd'.");
$page_title = _("Database Purge Complete");
start_page($page_title, true, $msg);

end_page();

/**
 * $Log: data_purge.php,v $
 * Revision 1.3  2011/03/03 17:01:14  gopherit
 * FIXED Bug Artifact #2801231  Refactored the database purge schema to cover all tables with record_status fields adjusting for deviations from the XRMS database naming convention
 * Added the 'xrms_db_purge' plugin hook to allow plugins to do their own data purges.
 * CAUTION: IT IS STRONGLY RECOMMENDED TO BACKUP THE XRMS DATABASE BEFORE DOING DATA PURGES!  PURGED DATA CANNOT BE RECOVERED IN A TRIVIAL WAY!
 *
 * Revision 1.2  2009/06/04 16:00:12  gopherit
 * Fixed misspelled table name 'opportunity_types' on line 54.
 *
 * Revision 1.1  2008/02/19 23:28:00  randym56
 * Purge all records from all files that contain {table_singular}_record_status = 'd'
 */
?>