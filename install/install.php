<?php
/**
 * install/install.php - This page begins the installation process 
 *
 * The installation files should insure that items are setup
 * and guide users on how to change items that are needed.
 *
 * $Id: install.php,v 1.1 2004/03/18 01:07:18 maulani Exp $
 */

// include the installation utility routines
require_once('install-utils.inc');
require_once('database.php');
require_once('data.php');

// where do we include from
require_once('../include-locations.inc');

// now check to make sure that the include-locations file has been setup for use
if ($include_directory == "/full/path/to/xrms/include/") {
    // Oops!  The include directory is still set to it's default value
    // Now instruct the user in how to set this value
    $problem = 'The include directory variable has not been set.<BR><BR>';
    $problem .= 'Please open the include-locations.inc file which is located ';
    $problem .= 'at the top level of the xrms installation.  Change the value ';
    $problem .= 'of the $include_directory variable to the correct path.<BR><BR>';
    $problem .= 'Then run this installation again.<BR><BR>';
    
    install_fatal_error($problem);
}

// more tests on the $include_directory variable should exist here
// such as making sure it ends with a slash, is valid, etc.

// get required common files
// vars.php sets all of the installation-specific variables
require_once($include_directory . 'vars.php');

// now check to make sure that the vars.php file has been setup for use

// has a database username been set?
if ($xrms_db_username == "your_mysql_username") {
    // Oops!  The database username does not have a valid value
    // Now instruct the user in how to set this value
    $problem = 'The database username variable has not been set.<BR><BR>';
    $problem .= 'Please open the vars.php file which is located ';
    $problem .= 'in the xrms include directory.  Change the value ';
    $problem .= 'of the $xrms_db_username variable to the database username and ';
    $problem .= 'set all of the other variables in the vars.php file.<BR><BR>';
    $problem .= 'Then run this installation again.<BR><BR>';
    
    install_fatal_error($problem);
}

// more tests on the vars.php variables should exist here
// optimally, all variables would be tested.


// get required common files
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');


// can we make a database connection?
$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
if (!$con) {
    // Oops!  We do not have a valid database connection
    // Now instruct the user in how to fix this problem
    $problem = 'We cannot connect to the database.  Check the database ';
    $problem .= 'parameters in include/vars.php to make sure they are correct.';
    $problem .= 'Also make sure the database is running and can accept a connection ';
    $problem .= 'from this server. <BR><BR>';
    $problem .= 'Then run this installation again.<BR><BR>';
    
    install_fatal_error($problem);
}

// create the database tables
create_db_tables($con);

// create the database data
create_db_data($con);

//close the connection
$con->close();

$page_title = "Installation Complete";
start_page($page_title, false, $msg);

?>

<BR>
All of the tables have been created, and initial data has been populated.  
<BR><BR>
The initial user available is "user1" with a password of "user1".  You should change this 
as soon as you login.  (It can be changed in Users within the Administration section.)
<BR><BR>
You may now <a href="sample.php">create sample data</a> to try out the system, or
<a href="../login.php">login</a> to get started entering your own data.



<?php

end_page();

/**
 *$Log: install.php,v $
 *Revision 1.1  2004/03/18 01:07:18  maulani
 *- Create installation tests to check whether the include location and
 *  vars.php have been configured.
 *- Create PHP-based database installation to replace old SQL scripts
 *- Create PHP-update routine to update users to latest schema/data as
 *  XRMS evolves.
 *
 *
 */
?>