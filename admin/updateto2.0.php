<?php
/**
 * install/updateto2.0.php - Update the database from a previous version of xrms
 *
 * When coding this file, it is important that everything only happen after
 * a test.  This file must be non-destructive and only make the changes that
 * must be made.
 *
 * @author Ivaylo Boiadjiev <iboiadjiev@360team.ca>, 360 TEAM Ltd.
 * @author XRMS Development Team
 *
 * $Id: updateto2.0.php,v 1.30 2011/01/14 15:51:28 gopherit Exp $
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
//this file isn't in the $include_directory, so navigate to it directly
require_once('../install/data.php');


$session_user_id = session_check( 'Admin' );

// make a database connection
$con = get_xrms_dbconnection();

$msg = '';

// Make sure that there is a start_delay column in the activity_templates table
// Should put a test here, but alter table is non-destructive
$sql = "ALTER TABLE activity_templates ADD start_delay INT NOT NULL AFTER default_text;";
$rst = $con->execute($sql);
// end start_delay

// Make sure that there is a campaign_type_id column in the campaign_statuses table
// Should put a test here, but alter table is non-destructive
$sql = "ALTER TABLE campaign_statuses ADD campaign_type_id INT NOT NULL AFTER campaign_status_id;";
$rst = $con->execute($sql);
// end campaign_type_id

// Make sure that the campaign_type_id in the campaign_statuses table is not a zero value.  If it is, we'll set it at 1
$sql = "UPDATE campaign_statuses SET campaign_type_id = 1 WHERE campaign_status_record_status = 'a' AND campaign_type_id = 0;";
$rst = $con->execute($sql);
// end campaign_type_id non-zero value

//make sure that there is a campaign_status_long_desc column in the campaign_statuses table
//should put a test here, but alter table is non-destructive
$sql = "ALTER TABLE campaign_statuses ADD campaign_status_long_desc VARCHAR(200) AFTER campaign_status_display_html;";
$rst = $con->execute($sql);
// end campaign_status_long_desc

// Create the campaign_list table to store campaign targets
$sql ="CREATE TABLE campaign_lists (
                campaign_list_id INT(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
                user_id INT(11) NOT NULL,
                campaign_id INT(11),
                campaign_list_title VARCHAR(100),
                campaign_list_description TEXT,
                target_contact_ids TEXT,
                list_created_on DATETIME,
                list_created_by INT(11),
                list_processing_started_on DATETIME,
                list_processing_started_by INT(11),
                list_processing_ended_on DATETIME,
                campaign_list_record_status CHAR(1) NOT NULL DEFAULT 'a'
                )";
//execute
$rst = $con->execute($sql);


// @TODO: FINAL STEP BEFORE WE ARE AT 2.0.0, SET XRMS VERSION TO 2.0.0 IN PREFERENCES TABLE
set_admin_preference($con, 'xrms_version', '1.99.7');

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
 * $Log: updateto2.0.php,v $
 * Revision 1.30  2011/01/14 15:51:28  gopherit
 * Implemented the Campaign Lists functionality to allow launching of campaign workflows on lists of contacts created with /contacts/some.php
 *
 *
 *
 */

?>