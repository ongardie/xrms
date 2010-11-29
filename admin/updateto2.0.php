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
 * $Id: updateto2.0.php,v 1.27 2010/11/29 14:10:14 gopherit Exp $
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

//make sure that there is a start_delay column in the activity_templates table
//should put a test here, but alter table is non-destructive
$sql = "ALTER TABLE activity_templates ADD start_delay INT NOT NULL AFTER default_text;";
$rst = $con->execute($sql);
// end start_delay

// @TODO: FINAL STEP BEFORE WE ARE AT 2.0.0, SET XRMS VERSION TO 2.0.0 IN PREFERENCES TABLE
set_admin_preference($con, 'xrms_version', '1.99.4');

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


?>