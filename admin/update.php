<?php
/**
 * install/update.php - Update the database from a previous version of xrms
 *
 * When coding this file, it is important that everything only happen after
 * a test.  This file must be non-destructive and only make the changes that
 * must be made.
 *
 * @author Beth Macknik
 * @author XRMS Development Team
 *
 * $Id: update.php,v 1.111 2006/04/26 02:39:00 vanmer Exp $
 */

// where do we include from
require_once('../include-locations.inc');

// get required common files
// vars.php sets all of the installation-specific variables
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'utils-activities.php');
require_once($include_directory . 'utils-database.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');
require_once($include_directory . 'adodb/adodb-datadict.inc.php');

//this file isn't in the $include_directory, so navigate to it directly
require_once('../install/data.php');


/**
 * NOTE FOR UPGRADES IN XRMS > 2.0:
 *
 * We have changed our database structure update policy in order to allow for generic databases using ADODB's data dictionary.
 * Examples for most basic types of updates are included below.  Upgrades should try to do the following:
 *
 * 1) Check to see that upgrade needs to be done before executing update, so as to not run upgrades more than once
 * 2) Use ADODB's functionality to create SQL statement to execute
 * 3) Comments above each upgrade line to indicate what purpose it serves
 * 4) A new INTERNATIONALIZED upgrade message appended to the upgrade message array, when successful update occurs
 * 
**/



// make a database connection
$con = get_xrms_dbconnection();
//USE DATA DICTIONARY AND DB CONNECTION FOR UPGRADES
$dict = NewDataDictionary( $con );

//CHECK FOR VERSION HERE, REDIRECT TO OLD UPDATE IF VERSION IS NOT CORRECT
$ret=get_admin_preference($con, 'xrms_version');
$allow_old_upgrade=true;
if ((!$ret OR $ret=='1.0' OR $ret=='1.99' OR $ret=='1.99.1') AND $allow_old_upgrade) {
    $con->close();
    Header('Location: updateto2.0.php');
    exit();
}

$session_user_id = session_check( 'Admin' );


$msg = '';
global $upgrade_msgs;
$upgrade_msgs=array();

//get a list of tables currently existing in the system
$table_list = list_db_tables($con);

//RUN UPGRADES IN ORDER, 

/** 
 * Example 1:
 * Creating a table TEST with 3 fields, one of which is the primary key and auto-incremented
 *, another is a text field and the third is a float:
**/
/*
    //Upgrade to add the TEST table, used to demonstrate CreateTableSQL call    
    $table_name='TEST';
    $table_fields=array();
    $table_fields[]=array('NAME'=>'TEST_ID','TYPE'=>'I','SIZE'=>'','NOTNULL'=>'NOTNULL','KEY'=>'KEY','AUTOINCREMENT'=>'AUTOINCREMENT');
    $table_fields[]=array('NAME'=>'TEST_NAME','TYPE'=>'C','SIZE'=>32);
    $table_fields[]=array('NAME'=>'TEST_VALUE','TYPE'=>'F');
    $table_opts=false;
    create_table($con, $table_name, $table_fields, $table_opts, $upgrade_msgs);
*/

/** 
 * Example 2:
 * Rename a field that already exists in a table
**/
/*
    //Upgrade to add the TEST table, used to demonstrate CreateTableSQL call    
    $table_name='TEST';
    $old_field_name='TEST_NAME';
    $new_field_name='TEST_STRING';
    rename_fieldname($con, $table_name, $old_field_name, $new_field_name, $upgrade_msgs);
*/

/** 
 * Example 3:
 * Adding a boolean field to a table
**/
/*
    //Upgrade to add a field to the TEST table
    $table_name='TEST';
    $field_name='TEST_FLAG';
    $field_definition=array();
    //add a boolean flag
    $field_definition[]=array('NAME'=>$field_name,'TYPE'=>'L','SIZE'=>1);

    $table_opts='';
    add_field($con, $table_name, $field_definition, $table_opts, &$upgrade_msgs);
*/
/** 
 * Example 4:
 * Inserting a row into a table, after ensuring row TEST does not exist
**/
/*
    $table_name='TEST';
    $sql = "SELECT * FROM $table_name WHERE TEST_STRING=".$con->qstr('TEST');
    $rst=$con->execute($sql);
    if (!$rst) { db_error_handler($con, $sql); }
    else {
        //no row, so add string
        if ($rst->EOF) {
            $data=array('TEST_STRING'=>'TEST', 'TEST_VALUE'=>12.22234, 'TEST_FLAG'=>1);
            $sql = $con->GetInsertSQL($table_name, $data);
            if ($sql) {
                $rst=$con->execute($sql);
                $upgrade_msgs[]="Added TEST row in table $table_name<br>";
            }
        }
    }
*/
/** 
 * Example 5:
 * Dropping the TEST table
**/
/*
    //Drop the TEST table, used in previous examples
    $table_name='TEST';

    drop_table($con, $table_name, &$upgrade_msgs);
*/

//LET PLUGINS RUN THEIR UPDATES
do_hook_function('xrms_update', $con);

if (count($upgrade_msgs)>0) {
    $msg.=implode("\n", $upgrade_msgs);
}

//close the database connection, because we don't need it anymore
$con->close();

$page_title = _("Update Complete");
start_page($page_title, true, $msg);

echo $msg;
?>

<BR>
<?php echo _("Your database has been updated."); ?>
<BR><BR>



<?php

end_page();


/**
 * $Log: update.php,v $
 * Revision 1.111  2006/04/26 02:39:00  vanmer
 * - ensure that 1.99.1 release also runs updateto2.0.php
 *
 * Revision 1.110  2006/04/26 02:12:37  vanmer
 * - ensure that version 1.99 will still run updateto2.0.php
 *
 * Revision 1.109  2006/04/05 01:06:40  vanmer
 * - updated demo SQL to use wrapper functions for creating SQL
 *
 * Revision 1.108  2005/12/03 00:23:19  vanmer
 * - Initial revision of the new upgrade requirements
 * - Redirects to updateto2.0.php for pre-2.0 upgrades
 * - Added examples for different types of upgrades on tables that might be needed
 *
 * Revision 1.107  2005/11/22 17:49:48  jswalter
 *  - added new address type - "shipping"
 *
 * Revision 1.106  2005/11/22 17:21:39  jswalter
 *  - added 'extref1' thru 3 to 'contacts' table
 *
 * Revision 1.105  2005/10/16 19:52:38  maulani
 * - Add additional countries to list
 *
 * Revision 1.104  2005/10/06 04:30:06  vanmer
 * - updated log entries to reflect addition of code by Diego Ongaro at ETSZONE
 *
 * Revision 1.103  2005/10/04 23:21:43  vanmer
 * Patch to allow sort_order on the company CRM status field, thanks to Diego Ongaro at ETSZONE
 *
 * Revision 1.102  2005/10/03 21:20:43  vanmer
 * - added upgrade lines to change timestamp field into a datetime field
 *
 * Revision 1.101  2005/09/29 14:55:11  vanmer
 * - added system activity type if it does not exist (since install was broken)
 * - added workflow entity and type fields for activity templates, to control new process entity creation
 *
 * Revision 1.100  2005/09/26 12:29:59  braverock
 * - don't use $include_directory on include of install/data.php
 *   - corrects problem where include_directory has been moved outside the webroot
 *   - credit SF user Markos151 with the patch
 *   https://sourceforge.net/tracker/?func=detail&atid=588128&aid=1304777&group_id=88850
 *
 * Revision 1.99  2005/09/21 20:39:05  vanmer
 * - added address_id to activity, to track activity location
 *
 * Revision 1.98  2005/08/26 11:58:46  braverock
 * - remove unnecessary semicolons from create table commands,
 *   as they are reported to cause problems in some installations
 *
 * Revision 1.97  2005/08/10 22:42:48  vanmer
 * - moved opportunity type addition outside of conditionals
 *
 * Revision 1.96  2005/08/05 21:32:47  vanmer
 * - moved all user preference initialization functions to same place, used for upgrade and install
 *
 * Revision 1.95  2005/08/04 18:57:38  vanmer
 * - added table to track contact company changes
 *
 * Revision 1.94  2005/07/28 20:27:35  vanmer
 * - added new Closed/Duplicate resolution to list of standard activity resolutions
 *
 * Revision 1.93  2005/07/16 00:01:13  vanmer
 * - added needed sort_order, unused as of yet, needed for workflow
 *
 * Revision 1.92  2005/07/08 20:29:51  vanmer
 * - moved display field addition to before addition of new options
 *
 * Revision 1.91  2005/07/08 18:48:38  vanmer
 * - added new preferences to hide and disable the sourceforge logo at the bottom of every page
 *
 * Revision 1.90  2005/07/06 21:49:14  vanmer
 * - added field to track which template an activity was spawned from
 *
 * Revision 1.89  2005/07/06 21:28:50  braverock
 * - add opportunity types
 *
 * Revision 1.88  2005/07/06 20:02:38  vanmer
 * - updated to reflect more standard fieldname
 *
 * Revision 1.87  2005/07/06 19:55:09  vanmer
 * - added needed fields to the files table
 *
 * Revision 1.86  2005/07/06 17:26:19  vanmer
 * - added message when activity resolution types are added
 * - added option display field if not available for user preferences
 * - added upgrade of system parameters into system preferences
 *
 * Revision 1.85  2005/06/30 04:34:47  vanmer
 * - added handling of activity resolution types and activity fields for resolution handling
 * - added priority field to activities
 *
 * Revision 1.84  2005/06/21 15:27:57  vanmer
 * - added strings to translate user preference information
 * - added section to make default activity types non-user editable
 *
 * Revision 1.83  2005/06/03 18:26:01  daturaarutad
 * add activity_recurrence_id to activities
 *
 * Revision 1.82  2005/05/25 05:39:19  vanmer
 * - added field to control user editability of activity types
 * - added field for determining which user completed an activity
 *
 * Revision 1.81  2005/05/25 05:24:13  daturaarutad
 * added activities_recurrence
 *
 * Revision 1.80  2005/05/24 23:03:31  braverock
 * - add email_tepplate_type table in advance of support for email template types in core
 *
 * Revision 1.79  2005/05/23 01:58:47  maulani
 * - Add Use Owl system parameter.  Move from vars.php
 *
 * Revision 1.78  2005/05/19 20:04:43  daturaarutad
 * changed thread_id and followup_from_id to follow activities convention
 *
 * Revision 1.77  2005/05/19 19:55:24  daturaarutad
 * added thread_id and followup_from_id to activities
 *
 * Revision 1.76  2005/05/18 21:40:58  vanmer
 * - added workflow_history table to update
 *
 * Revision 1.75  2005/05/18 06:20:18  vanmer
 * - removed reference to roles table
 * - removed reference to role_id in users table
 *
 * Revision 1.74  2005/05/16 21:30:49  vanmer
 * - added tax_id field to contacts table
 *
 * Revision 1.73  2005/05/06 00:30:46  vanmer
 * - added table for tracking user preference options
 * - moved fields to user preferences
 * - added automatic creation of user_language and css_theme user preferences
 * - added html form type field for preference types
 *
 * Revision 1.72  2005/05/01 01:27:37  braverock
 * - remove InnoDB requirement from install and update scripts as
 *   it causes problems in non-MySQL env. or MySQL env w/o InnoDB support
 *
 * Revision 1.71  2005/04/28 15:39:28  braverock
 * - fixed alter table command for work_phone_ext
 *   patch supplied by Miguel Gonçalves (mig77)
 *
 * Revision 1.70  2005/04/26 18:33:55  gpowers
 * - changed contacts.work_phone_ext to NOT null default '' to match other columns in table
 *
 * Revision 1.69  2005/04/26 17:55:41  vanmer
 * - added system parameter to control display of logo
 * - added better upgrade for field name change in activity participants
 *
 * Revision 1.68  2005/04/26 17:33:07  gpowers
 * - added contacts.work_phone_ext column
 *
 * Revision 1.67  2005/04/23 17:48:42  vanmer
 * - changed activity_participant_record_status field to ap_record_status field to work around 30 character limit for adodb mssql driver
 *
 * Revision 1.66  2005/04/15 07:37:12  vanmer
 * - added tables for handling multiple contacts in activities, and positions for different activity types
 *
 * Revision 1.65  2005/04/11 00:26:53  maulani
 * - Add address_types
 *
 * Revision 1.64  2005/04/07 13:57:03  maulani
 * - Add salutation table to allow installation configurable list.  Also add
 *   many more default entries.
 *   RFE 913526 by algon.
 *
 * Revision 1.63  2005/03/20 16:56:23  maulani
 * - add new system parameters
 *
 * Revision 1.62  2005/03/07 18:34:38  vanmer
 * - moved connection close to after hook function for upgrade script
 * - changed upgrade hook to reflect standard naming 'xrms_update' and 'xrms_install'
 *
 * Revision 1.61  2005/02/10 20:07:28  braverock
 * - add home_address_id to contacts table
 *
 * Revision 1.60  2005/02/10 14:29:29  maulani
 * - Add last modified timestamp and user fields to activities
 *
 * Revision 1.59  2005/02/07 17:36:35  maulani
 * - Test if variable exists before using it.  Will allow removal from vars.php
 *   without breaking update.
 *
 * Revision 1.58  2005/02/05 16:44:18  maulani
 * - Change report options to use system parameters
 *
 * Revision 1.57  2005/01/30 18:28:21  maulani
 * - Add system parameters descriptions
 *
 * Revision 1.56  2005/01/30 12:52:01  maulani
 * - Add from email address to emailed reports
 *
 * Revision 1.55  2005/01/29 19:40:05  vanmer
 * - added errorneously missing user_id field to user preferences table
 *
 * Revision 1.54  2005/01/25 05:55:58  vanmer
 * - added tables for user preferences
 * - added hook for plugins to run updates after all other updates are completed
 *
 * Revision 1.53  2005/01/24 14:10:15  maulani
 * - Fix system_parameters_options update
 *
 * Revision 1.52  2005/01/24 00:17:18  maulani
 * - Add description to system parameters
 *
 * Revision 1.51  2005/01/23 18:48:57  maulani
 * - Add system parameters required for RSS feeds
 *
 * Revision 1.50  2005/01/13 21:55:48  vanmer
 * - altered currency SQL with IS NULL to simply check for the first record, and update only if it is blank
 *
 * Revision 1.49  2005/01/13 17:26:18  vanmer
 * - added ACL install to update script
 *
 * Revision 1.48  2005/01/11 17:08:39  maulani
 * - Added parameter for LDAP Version.  Some LDAP Version 3 installations
 *   require this option to be set.  Initial parameter setting is version 2
 *   since most current installations probably use v2.
 *
 * Revision 1.47  2005/01/10 21:47:12  braverock
 * - make activity_description a nullable field
 *
 * Revision 1.46  2005/01/06 21:48:19  vanmer
 * - added address_id to company_division table, for use in specifying addresses for divisions
 *
 * Revision 1.45  2005/01/06 20:44:24  vanmer
 * - added optional division_id to cases and opportunities
 *
 * Revision 1.44  2004/12/31 17:57:20  braverock
 * - added description column to case_statuses to match opportunity_statuses
 *
 * Revision 1.43  2004/12/07 22:24:02  vanmer
 * - added missing fields to relationship_types
 *
 * Revision 1.42  2004/12/07 21:27:13  vanmer
 * - added field relationship_status to relationship_type table, since it is missing on older installs
 * - added currency_code field to keep track of currencies for a country
 * - added currencies for known countries
 *
 * Revision 1.41  2004/09/16 21:52:54  vanmer
 * -removed ALTER table to add a key in time_zones, as this is done differently later in the code
 *
 * Revision 1.40  2004/09/16 19:49:23  vanmer
 * -added ALTER sql to add missing KEY for province in time_zones
 *
 * Revision 1.39  2004/09/06 12:23:00  braverock
 * - add sort_order to case statuses
 *
 * Revision 1.38  2004/09/02 22:27:08  maulani
 * - Add status_open_indicator to opportunity_statuses and case_statuses tables
 * - Correct spelling
 *
 * Revision 1.37  2004/09/02 18:29:02  maulani
 * - Add status_open_indicator to opportunity_statuses table.  Field was
 *   in install but never added to update.
 *
 * Revision 1.36  2004/09/02 15:09:53  neildogg
 * - Index added on update
 *
 * Revision 1.35  2004/09/02 14:51:13  neildogg
 * - Removed additional indexes
 *  - as some were unused and some were duplicates
 *  - added province index to time_zones
 *  - as it is utilized in updated misc util
 *
 * Revision 1.34  2004/09/02 14:21:31  maulani
 * - Add indexes to speed up time zone assignment
 * - Reduce scope of selection to speed up time zone assignment
 *
 * Revision 1.33  2004/08/23 13:49:57  neildogg
 * - Properly updates daylight savings
 *  - in addresses
 *  - May take a long time on large systems
 *
 * Revision 1.32  2004/08/19 21:49:53  neildogg
 * - Adds field to activity templates for default text
 *
 * Revision 1.31  2004/08/16 16:08:45  neildogg
 * - Updates addresses to daylight savings
 *  - (will work for future time_zone database additions)
 *
 * Revision 1.30  2004/08/04 20:46:06  introspectshun
 * - Pass table name to GetInsertSQL
 *
 * Revision 1.29  2004/08/03 15:47:06  neildogg
 * - Now changes are actually being executed
 *
 * Revision 1.28  2004/08/03 15:14:45  neildogg
 * - Added initial time zone/daylight savings information
 *
 * Revision 1.27  2004/08/02 08:31:31  maulani
 * - Create Activities Default Behavior system parameter.  Replaces vars.php
 *   variable $activities_default_behavior
 *
 * Revision 1.26  2004/07/28 20:40:45  neildogg
 * - Added field recent_action to recent_items
 *  - Same function works transparently
 *  - Current items have recent_action=''
 *  - update_recent_items has new optional parameter
 *
 * Revision 1.25  2004/07/28 11:50:19  braverock
 * - add sort order to opportunity statuses
 *
 * Revision 1.24  2004/07/21 20:30:18  neildogg
 * - Added saved_actions table
 *
 * Revision 1.23  2004/07/16 18:52:43  cpsource
 * - Add role check inside of session_check
 *
 * Revision 1.22  2004/07/16 13:52:00  braverock
 * - localize strings for i18n translation support
 *   - applies modified patches from Sebastian Becker (hyperpac)
 *
 * Revision 1.21  2004/07/15 21:26:20  maulani
 * - Add Audit Level as a system parameter
 *
 * Revision 1.20  2004/07/13 18:15:44  neildogg
 * - Add database entries to allow a contact to be tied to the user
 *
 * Revision 1.19  2004/07/07 20:48:16  neildogg
 * - Added database structure changes
 *
 * Revision 1.18  2004/07/01 20:14:28  braverock
 * - changed relationship update script to avoid duplicate entries and correct  from/to order
 *
 * Revision 1.17  2004/07/01 19:48:09  braverock
 * - add new configurable relationships code
 *   - adapted from patches submitted by Neil Roberts
 *
 * Revision 1.16  2004/07/01 15:23:06  braverock
 * - update default data for relationship_types table
 * - use NAMES -> VALUES SQL construction to be safe
 *
 * Revision 1.15  2004/07/01 12:56:33  braverock
 * - add relationships and relationship_types tables and data to install and update
 *
 * Revision 1.14  2004/06/28 14:30:01  maulani
 * - add address format strings for many countries
 *
 * Revision 1.13  2004/06/26 13:11:29  braverock
 * - execute sql for sort order on activity types
 *   - applies SF patch #979564 by Marc Spoorendonk (grmbl)
 *
 * Revision 1.12  2004/06/14 18:13:51  introspectshun
 * - Add adodb-params.php include for multi-db compatibility.
 * - Now use ADODB GetInsertSQL, GetUpdateSQL functions.
 *
 * Revision 1.11  2004/06/13 09:13:20  braverock
 * - add sort_order to activity_types
 *
 * Revision 1.10  2004/06/04 14:53:48  braverock
 * - change activity_templates duration to varchar for advanced date functionality
 *
 * Revision 1.9  2004/06/03 16:14:56  braverock
 * - add functionality to support workflow and activity templates
 *   - functionality contributed by Brad Marshall
 *
 * Revision 1.8  2004/05/04 23:48:02  maulani
 * - Added a system parameters table to the database.  This table can be used
 *   for items that would otherwise be dumped into the vars.php file. These
 *   include config items that are not required for database connectivity nor
 *   have access speed performance implications.  Accessor and setor functions
 *   added to utils-misc.
 * - Still need to create editing screen in admin section
 *
 * Revision 1.7  2004/04/25 23:09:56  braverock
 * add division_id alter table command to resolve problems from upgrading from 12Jan
 *
 * Revision 1.6  2004/04/23 17:11:41  gpowers
 * Removed http_user_agent from audit_items table. It is space consuming and
 * redundant, as most httpd servers can be configured to log this information.
 *
 * If anyone has run the previsous version of this script, no harm will be
 * done, they will just have an extra column in the audit table. But, I wanted
 * to patch this ASAP, to minize the number of people who might run it.
 *
 * Revision 1.5  2004/04/23 16:00:53  gpowers
 * Removed addresses.line3 - this was not an approved change
 * Added comments telling the reasons for the changes
 *
 * Revision 1.4  2004/04/23 15:07:29  gpowers
 * added addresses.line, campaign_statuses.status_open_indicator, audit_items.remote_addr, audit_items.remote_port, audit_items.session_id, audit_items.http_user_agent
 *
 * Revision 1.3  2004/04/13 15:47:12  maulani
 * - add data integrity check so all companies have addresses
 *
 * Revision 1.2  2004/04/13 15:06:41  maulani
 * - Add active contact data integrity check to database cleanup
 *
 * Revision 1.1  2004/04/12 18:59:01  maulani
 * - Make database structure and data cleanup available withing Admin interface
 *
 * Revision 1.7  2004/04/13 12:29:20  maulani
 * - Move the data clean and update files into the admin section of XRMS
 *
 * Revision 1.6  2004/04/12 14:34:02  maulani
 * - Add indexes for foreign key company_id
 *
 * Revision 1.5  2004/03/26 16:17:00  maulani
 * - Cleanup formatting
 *
 * Revision 1.3  2004/03/23 14:34:05  braverock
 * - add check for result set before closing rst
 *
 * Revision 1.2  2004/03/22 02:05:08  braverock
 * - add case_priority_score_adjustment to fix SF bug 906413
 *
 * Revision 1.1  2004/03/18 01:07:18  maulani
 * - Create installation tests to check whether the include location and
 *   vars.php have been configured.
 * - Create PHP-based database installation to replace old SQL scripts
 * - Create PHP-update routine to update users to latest schema/data as
 *   XRMS evolves.
 *
 */
?>