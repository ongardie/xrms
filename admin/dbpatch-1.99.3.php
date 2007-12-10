<?php
/**
 * Update the database from xrms 1.99.2 to 1.99.3
 *
 * @author Randy Martinsen
 *
 * $Id: dbpatch-1.99.3.php,v 1.2 2007/12/10 14:46:08 randym56 Exp $
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
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check( 'Admin' );

// make a database connection
$con = get_xrms_dbconnection();

$msg = '';

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
    ADD COLUMN workflow_year_repeats INTEGER default NULL AFTER workflow_goto
    ";
    $rst = $con->execute($sql);

$sql ="INSERT INTO `user_preference_types` (`user_preference_type_id`,`user_preference_name`,`user_preference_pretty_name`,`user_preference_description`,`allow_multiple_flag`,`allow_user_edit_flag`,`user_preference_type_status`,`preference_type_created_on`,`user_preference_type_modified_on`,`form_element_type`,`read_only`,`skip_system_edit_flag`) VALUES
 (24,'printer_processor_email','Printer Processor Email','Where snail mail messages are sent for processing',0,1,'a',NULL,NULL,'text',1,0),
 (25,'show_opt_out','Show Opt-Out dialogs','Shows opt-out dialogs in system for e-mail activities',0,0,'a',NULL,NULL,'select',0,0);";
    $rst = $con->execute($sql);

$sql ="INSERT INTO `user_preference_type_options` (`up_option_id`,`user_preference_type_id`,`option_value`,`sort_order`,`option_record_status`,`option_display`) VALUES
 (37,23,'household',1,'a','New Company uses Contact Last Name + Household'),
 (38,25,'n',1,'a','No'),
 (39,25,'y',2,'a','Yes');";

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
 * $Log: dbpatch-1.99.3.php,v $
 * Revision 1.2  2007/12/10 14:46:08  randym56
 * - Updated fields for Workflow/Opportunities
 * - Insert system default table values for opt-in messages, default company name type and printer processor e-mail for snail mail functions
 *
 * Revision 1.1  2007/12/10 14:16:50  randym56
 * *** empty log message ***
 *
**/
?>
