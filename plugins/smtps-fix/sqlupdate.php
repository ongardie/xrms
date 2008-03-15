<?php
/**
 * install/update.php - Update the database to allow for individual users to have SMTP e-mails
 *
 * @author Randy Martinsen
 *
 * $Id: sqlupdate.php,v 1.1 2008/03/15 16:54:31 randym56 Exp $
 */

// where do we include from
require_once('../../include-locations.inc');

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

//make each user have his/her own smtp address for sending email
$sql ="ALTER TABLE users
    ADD COLUMN smtpsID VARCHAR(100) default NULL AFTER last_hit
    ";
    $rst = $con->execute($sql);
$sql ="ALTER TABLE users
    ADD COLUMN smtpsPW VARCHAR(100) default NULL AFTER smtpsID
    ";
    $rst = $con->execute($sql);
$sql ="ALTER TABLE users
    ADD COLUMN smtpsHost VARCHAR(100) default NULL AFTER smtpsPW
    ";
    $rst = $con->execute($sql);
$sql ="ALTER TABLE users
    ADD COLUMN smtpsPort INTEGER default NULL AFTER smtpsHost
    ";
    $rst = $con->execute($sql);
	
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
 * $Log: sqlupdate.php,v $
 * Revision 1.1  2008/03/15 16:54:31  randym56
 * Updated SMTPs to allow for individual user SMTP addressing - requires installation and activation of mcrypt in PHP - follow README.txt instructions
 *
**/
?>
