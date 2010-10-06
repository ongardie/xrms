<?php
/**
 * install/update.php - Update the database from xrms 1.99.2 to 2.1.1
 *
 * When coding this file, it is important that everything only happen after
 * a test.  This file must be non-destructive and only make the changes that
 * must be made.
 *
 * @author Randy Martinsen
 *
 * $Id: updateto2.1.php,v 1.8 2010/10/06 16:35:59 gopherit Exp $
 */

// where do we include from
require_once('../include-locations.inc');

// get required common files
// vars.php sets all of the installation-specific variables
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'utils-activities.php');
require_once($include_directory . 'utils-companies.php');
require_once($include_directory . 'utils-contacts.php');
require_once($include_directory . 'utils-preferences.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check( 'Admin' );

// make a database connection
$con = get_xrms_dbconnection();

$msg = '';

$sql ="ALTER TABLE activities
    ADD COLUMN bill_rate DECIMAL(10,2) default NULL AFTER resolution_description
    ";
    $rst = $con->execute($sql);
$sql ="ALTER TABLE activities
    ADD COLUMN extra_amount DECIMAL(10,2) default NULL AFTER bill_rate
    ";
    $rst = $con->execute($sql);
$sql ="ALTER TABLE activities
    ADD COLUMN mileage DECIMAL(10,2) default NULL AFTER extra_amount
    ";
    $rst = $con->execute($sql);
$sql ="ALTER TABLE activities
    ADD COLUMN taxable INTEGER default NULL AFTER mileage
    ";
    $rst = $con->execute($sql);
$sql ="ALTER TABLE activities
        ADD COLUMN division_id INTEGER AFTER company_id
    ";
    $rst = $con->execute($sql);
$sql ="ALTER TABLE activities
        ADD COLUMN workflow INTEGER AFTER activity_template_id
    ";
    $rst = $con->execute($sql);

$sql ="ALTER TABLE activity_templates
    ADD COLUMN email_template_id INTEGER default NULL AFTER workflow_entity_type
    ";
    $rst = $con->execute($sql);
$sql ="ALTER TABLE activity_templates
    ADD COLUMN fixed_date DATETIME default NULL AFTER email_template_id
    ";
    $rst = $con->execute($sql);
$sql ="ALTER TABLE activity_templates
    ADD COLUMN group_status INTEGER default '0' AFTER fixed_date
    ";
    $rst = $con->execute($sql);

$sql ="ALTER TABLE activity_templates
    ADD COLUMN fixed_cost DECIMAL(10,2) default '0' AFTER group_status
    ";
    $rst = $con->execute($sql);

$sql ="ALTER TABLE campaigns
    ADD COLUMN campaign_sql TEXT AFTER campaign_description
    ";
    $rst = $con->execute($sql);

$sql ="ALTER TABLE opportunity_statuses
    ADD COLUMN status_workflow_type INTEGER default NULL AFTER opportunity_status_long_desc
    ";
    $rst = $con->execute($sql);
$sql ="ALTER TABLE opportunity_statuses
    ADD COLUMN workflow_goto INTEGER default NULL AFTER status_workflow_type
    ";
    $rst = $con->execute($sql);
$sql ="ALTER TABLE opportunity_statuses
    ADD COLUMN hopper_type INTEGER default NULL AFTER workflow_goto
    ";
    $rst = $con->execute($sql);

$sql ="ALTER TABLE opportunities
    ADD COLUMN workflow_starts_at DATETIME default NULL AFTER close_at
    ";
    $rst = $con->execute($sql);

$sql ="ALTER TABLE activity_templates
    ADD COLUMN user_id INTEGER NOT NULL DEFAULT 0 AFTER role_id
    ";
    $rst = $con->execute($sql);

//Add records for admin default settings for new functions - if these are already added they won't add again (dup key)
$sql ="INSERT INTO `user_preference_types` (`user_preference_type_id`,`user_preference_name`,`user_preference_pretty_name`,`user_preference_description`,`allow_multiple_flag`,`allow_user_edit_flag`,`user_preference_type_status`,`preference_type_created_on`,`user_preference_type_modified_on`,`form_element_type`,`read_only`,`skip_system_edit_flag`) VALUES
 (24,'printer_processor_email','Printer Processor Email','Where snail mail messages are sent for processing',0,1,'a',NULL,NULL,'text',1,0),
 (25,'show_opt_out','Show Opt-Out dialogs','Shows opt-out dialogs in system for e-mail activities',0,0,'a',NULL,NULL,'select',0,0);";
    $rst = $con->execute($sql);

$sql ="INSERT INTO `user_preference_type_options` (`up_option_id`,`user_preference_type_id`,`option_value`,`sort_order`,`option_record_status`,`option_display`) VALUES
 (37,23,'household',1,'a','New Company uses Contact Last Name + Household'),
 (38,25,'n',1,'a','No'),
 (39,25,'y',2,'a','Yes');";
    $rst = $con->execute($sql);

$sql ="SELECT * FROM user_preferences WHERE user_preference_type_id = '24'";
$rst = $con->execute($sql);
if ($rst->EOF) {
        $sql ="INSERT INTO user_preferences
        (user_id,user_preference_type_id,user_preference_value,user_preference_status,user_preference_modified_on,user_preference_created_by,
                user_preference_modified_by)
        VALUES (0,24,'you@yourmail.com','a',now(),0,0)";
    $rst = $con->execute($sql);
        }

$sql ="SELECT * FROM user_preferences WHERE user_preference_type_id = '25'";
$rst = $con->execute($sql);
if ($rst->EOF) {
        $sql ="INSERT INTO user_preferences
        (user_id,user_preference_type_id,user_preference_value,user_preference_status,user_preference_modified_on,user_preference_created_by,
                user_preference_modified_by)
        VALUES (0,25,'y','a',now(),0,0)";
    $rst = $con->execute($sql);
        }

$sql ="SELECT * FROM user_preference_type_options WHERE user_preference_type_id = '25' AND option_value = 'y'";
$rst = $con->execute($sql);
if ($rst->EOF) {
        $sql ="INSERT INTO user_preference_type_options
        (user_preference_type_id,option_value,sort_order,option_record_status,option_display)
        VALUES (25,'y',1,'a','Yes')";
    $rst = $con->execute($sql);
        }

$sql ="SELECT * FROM user_preference_type_options WHERE user_preference_type_id = '25' AND option_value = 'n'";
$rst = $con->execute($sql);
if ($rst->EOF) {
        $sql ="INSERT INTO user_preference_type_options
        (user_preference_type_id,option_value,sort_order,option_record_status,option_display)
        VALUES (25,'n',2,'a','No')";
    $rst = $con->execute($sql);
        }

//use functions to add rather than direct sql statements    (added by Glenn Powers 12/11/07
    $html_activity_notes=add_user_preference_type($con, 'html_activity_notes', "Allow HTML Activity Notes", "Use a HTML Editor on Activity Notes", false, false, 'select');
    add_preference_option($con, $html_activity_notes, 'y', 'Yes');
    add_preference_option($con, $html_activity_notes, 'n', 'No');
    $ret=get_admin_preference($con, $html_activity_notes);
    if (!$ret) {
        set_admin_preference($con, $html_activity_notes, 'n');
    }

$sql = "SELECT * FROM addresses";
	$rst = $con->execute($sql);
	if (!$rst->fields['on_what_table']) {
	$sql = "ALTER TABLE addresses CHANGE company_id on_what_id INTEGER;";
	$rst = $con->execute($sql);
	$sql = "ALTER TABLE addresses ADD COLUMN on_what_table VARCHAR(100) DEFAULT 'companies' AFTER address_id;";
	$rst = $con->execute($sql);
	}

//add new datetime_format option for displaying different formats system selectable
    $datetime_format=add_user_preference_type($con, 'datetime_format', "Date and Time format", "Allows selection of different date/time formats", false, true, 'select');
    add_preference_option($con, $datetime_format, 'Y-m-d H:i:s', 'YYYY-MM-DD (24 hour clock = HH-mm-ss)');
    add_preference_option($con, $datetime_format, 'Y-m-d h:i A', 'YYYY-MM-DD (12 hour clock AM/PM = hh-mm XM)');
    $ret=get_admin_preference($con, $datetime_format);
    if (!$ret) {
        set_admin_preference($con, $datetime_format, 'Y-m-d h:i a', 'datetime_format');
    }

// Change gmt_offset field type in the users table.  Was INT, changed to VARCHAR(50) to allow storage of Region/Locale data
$sql = "ALTER TABLE users CHANGE gmt_offset gmt_offset VARCHAR(50) NOT NULL DEFAULT '0'";
$rst = $con->execute($sql);

//FINAL STEP SET XRMS VERSION IN PREFERENCES TABLE
set_admin_preference($con, 'xrms_version', '1.99.3');

do_hook_function('xrms_update', $con);

//close the database connection, because we don't need it anymore
$con->close();

$page_title = _("Update Complete");
start_page($page_title, true, $msg);

?>

<BR>
<?php echo _("Your database has been updated."); ?>
<BR><BR>



<?php

end_page();
/**
 * $Log: updateto2.1.php,v $
 * Revision 1.8  2010/10/06 16:35:59  gopherit
 * Fixed Bug Artifacts:
 * * 3082298 - Inconsistent Date/Time Formatting
 * * 3082300 - Followup Activity Type
 * * 3082302 - Editing Activiies Wipes the completed_at/completed_by fields
 *
 * Revision 1.7  2009/05/01 16:43:51  gopherit
 * Change gmt_offset field type in the users table.  Was INT, changed to VARCHAR(50) to allow storage of Region/Locale data
 *
 * Revision 1.6  2009/02/14 18:01:47  randym56
 * - Update $datetime_format - removed from vars.php - installed with updateto2.1.php into system/user prefs
 *
 * Revision 1.5  2008/10/10 00:29:48  randym56
 * Added addresses table change to accomodate code changes by  developer ongardie
 *
 * Revision 1.4  2008/08/14 14:59:50  randym56
 * Fixed system e-mail add.
 *
 * Revision 1.3  2008/02/27 02:00:20  randym56
 * Set version to 1.99.3
 *
 * Revision 1.2  2008/02/27 01:43:21  randym56
 * DB updates necessary for some changes made to Opportunity / Case workflow tables in preparation for new scripts.
 *
 *
**/
?>
