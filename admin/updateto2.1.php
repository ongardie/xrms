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
 * $Id: updateto2.1.php,v 1.3 2008/02/27 02:00:20 randym56 Exp $
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
        VALUES (0,24,'orders@budgetworks.com','a',now(),0,0)";
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
 * Revision 1.3  2008/02/27 02:00:20  randym56
 * Set version to 1.99.3
 *
 * Revision 1.2  2008/02/27 01:43:21  randym56
 * DB updates necessary for some changes made to Opportunity / Case workflow tables in preparation for new scripts.
 *
 *
**/
?>
