<?php
/**
 * install/update.php - Update the database from a previous version of xrms
 *
 * When coding this file, it is important that everything only happen after
 * a test.  This file must be non-destructive and only make the changes that
 * must be made.
 *
 * @author Beth Macknik
 * $Id: update.php,v 1.1 2004/03/18 01:07:18 maulani Exp $
 */

/**
 * Confirm that the table does not currently have any records.
 *
 */

// where do we include from
require_once('../include-locations.inc');

// get required common files
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

// vars.php sets all of the installation-specific variables
require_once($include_directory . 'vars.php');

// include the installation utility routines
require_once('install-utils.inc');

// make a database connection
$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);


// Make sure that there is an admin record in roles
$sql = "select count(*) as recCount from roles where role_short_name='Admin'";
$rst = $con->execute($sql);
$recCount = $rst->fields['recCount'];
if ($recCount == 0) {
    $sql ="insert into roles (role_short_name, role_pretty_name, role_pretty_plural, role_display_html) values ('Admin', 'Admin', 'Admin', 'Admin')";
    $rst = $con->execute($sql);
}


// Make sure that there is a user with admin privileges
$sql = "select role_id from roles where role_short_name='Admin'";
$rst = $con->execute($sql);
$role_id = $rst->fields['role_id'];
$sql = "select count(*) as recCount from users where role_id=$role_id";
$rst = $con->execute($sql);
$recCount = $rst->fields['recCount'];
if ($recCount == 0) {
    // none of the users have Admin access, so give the user with the lowest user_id Admin access
	$sql = "select min(user_id) as user_id from users";
	$rst = $con->execute($sql);
	if (!$user_id) {
	    // Oops.  The reald problem is that we have no users.  Create one with admin access
        $sql ="insert into users (role_id, username, password, last_name, first_names, email, language) values ($role_id, 'user1', '24c9e15e52afc47c225b757e7bee1f9d', 'One', 'User', 'user1@somecompany.com', 'english')";
        $rst = $con->execute($sql);
	} else {
		$user_id = $rst->fields['user_id'];
		$sql ="update users set role_id=$role_id where user_id=$user_id";
		$rst = $con->execute($sql);
	}
}


$page_title = "Update Complete";
start_page($page_title, false, $msg);

?>

<BR>
Your database has been updated.  
<BR><BR>
You may now <a href="../login.php">login</a> to get started.



<?php

end_page();

/**
 * $Log: update.php,v $
 * Revision 1.1  2004/03/18 01:07:18  maulani
 * - Create installation tests to check whether the include location and
 *   vars.php have been configured.
 * - Create PHP-based database installation to replace old SQL scripts
 * - Create PHP-update routine to update users to latest schema/data as
 *   XRMS evolves.
 *
 */
?>
