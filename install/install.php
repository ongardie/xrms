<?php
/**
 * install/install.php - This page begins the installation process 
 *
 * The installation files should insure that items are setup
 * and guide users on how to change items that are needed.
 *
 * $Id: install.php,v 1.3 2004/07/02 18:58:49 maulani Exp $
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

// now check to make sure that the include-directory actually exists
if (!file_exists ($include_directory) ) {
    // Oops!  The include directory does not exist
    // Now instruct the user in how to set this value
    $problem = 'The include directory variable has not been set correctly.<BR><BR>';
    $problem .= "It is currently set to '$include_directory', which does not exist.<BR><BR>";
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

// has a database password been set?
if ($xrms_db_password == "your_mysql_password") {
    // Oops!  The database username does not have a valid value
    // Now instruct the user in how to set this value
    $problem = 'The database password variable has not been set.<BR><BR>';
    $problem .= 'Please open the vars.php file which is located ';
    $problem .= 'in the xrms include directory.  Change the value ';
    $problem .= 'of the $xrms_db_password variable to the database password and ';
    $problem .= 'set all of the other variables in the vars.php file.<BR><BR>';
    $problem .= 'Then run this installation again.<BR><BR>';
    
    install_fatal_error($problem);
}

// has a database name been set?
if ($xrms_db_dbname == "your_mysql_database") {
    // Oops!  The database username does not have a valid value
    // Now instruct the user in how to set this value
    $problem = 'The database name variable has not been set.<BR><BR>';
    $problem .= 'Please open the vars.php file which is located ';
    $problem .= 'in the xrms include directory.  Change the value ';
    $problem .= 'of the $xrms_db_dbname variable to the database name and ';
    $problem .= 'set all of the other variables in the vars.php file.<BR><BR>';
    $problem .= 'Then run this installation again.<BR><BR>';
    
    install_fatal_error($problem);
}

// has a web root url been set?
if ($http_site_root == "http://www.yoursitename.com/xrms") {
    // Oops!  The database username does not have a valid value
    // Now instruct the user in how to set this value
    $problem = 'The http site root variable has not been set.<BR><BR>';
    $problem .= 'Please open the vars.php file which is located ';
    $problem .= 'in the xrms include directory.  Change the value ';
    $problem .= 'of the $http_site_root variable to the correct URL and ';
    $problem .= 'set all of the other variables in the vars.php file.<BR><BR>';
    $problem .= 'Then run this installation again.<BR><BR>';
    
    install_fatal_error($problem);
}

// has a file root been set?
if ($xrms_file_root == "/full/path/to/xrms") {
    // Oops!  The database username does not have a valid value
    // Now instruct the user in how to set this value
    $problem = 'The application file root variable has not been set.<BR><BR>';
    $problem .= 'Please open the vars.php file which is located ';
    $problem .= 'in the xrms include directory.  Change the value ';
    $problem .= 'of the $xrms_file_root variable to the correct path and ';
    $problem .= 'set all of the other variables in the vars.php file.<BR><BR>';
    $problem .= 'Then run this installation again.<BR><BR>';
    
    install_fatal_error($problem);
}

// now check to make sure that the file root actually exists
if (!file_exists ($xrms_file_root) ) {
    // Oops!  The application file root directory does not exist
    // Now instruct the user in how to set this value
    $problem = 'The application file root directory variable has not been set correctly.<BR><BR>';
    $problem .= "It is currently set to '$xrms_file_root', which does not exist.<BR><BR>";
    $problem .= 'Please open the vars.php file which is located ';
    $problem .= 'in the include folder.  Change the value ';
    $problem .= 'of the $xrms_file_root variable to the correct path.<BR><BR>';
    $problem .= 'Then run this installation again.<BR><BR>';
    
    install_fatal_error($problem);
}

// has a temporary upload directory been set?
if ($tmp_upload_directory == "/full/path/to/xrms/tmp/") {
    // Oops!  The database username does not have a valid value
    // Now instruct the user in how to set this value
    $problem = 'The temporary upload directory variable has not been set.<BR><BR>';
    $problem .= 'Please open the vars.php file which is located ';
    $problem .= 'in the xrms include directory.  Change the value ';
    $problem .= 'of the $tmp_upload_directory variable to the correct path and ';
    $problem .= 'set all of the other variables in the vars.php file.<BR><BR>';
    $problem .= 'Then run this installation again.<BR><BR>';
    
    install_fatal_error($problem);
}

// now check to make sure that the temporary upload directory actually exists
if (!file_exists ($tmp_upload_directory) ) {
    // Oops!  The application file root directory does not exist
    // Now instruct the user in how to set this value
    $problem = 'The temporary upload directory variable has not been set correctly.<BR><BR>';
    $problem .= "It is currently set to '$tmp_upload_directory', which does not exist.<BR><BR>";
    $problem .= 'Please open the vars.php file which is located ';
    $problem .= 'in the include folder.  Change the value ';
    $problem .= 'of the $tmp_upload_directory variable to the correct path.<BR><BR>';
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
//if you can make a database connection make sure that you are running at least version 4 of MYSQL
// otherwise alert the user to potential problems
if($xrms_db_dbtype="mysql"){
	//dont use adodb bcos i need the link identifier for mysql_get_server_info
	$link = mysql_connect($xrms_db_server, $xrms_db_username, $xrms_db_password);
	$ver=mysql_get_server_info($link);
	mysql_close($link);//close the link when you are done with it.
  if (version_compare($ver,"4.0.2")<0){
		 //Ooops you are not running a compliant version of mysql
    // Now instruct the user in how to fix this problem
    $problem = 'XRMS requires Mysql version 4.0.2 or above to work. <BR>';
		$problem .='You are currently running MySql Server version '.$ver.'<BR>';
    $problem .= 'At the moment, previous versions are not supported.';
		
    $problem .= 'Please install version 4.0.2 or greater of MySql, available here <a href="http://dev.mysql.com/downloads/">Mysql Downloads</a><BR>';
    install_fatal_error($problem);
		
  };
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
 *Revision 1.3  2004/07/02 18:58:49  maulani
 *- add mySQL version check patch #980507 submitted by Nic Lowe
 *  Modified to check for version 4.0.2
 *
 *Revision 1.2  2004/03/19 23:48:43  maulani
 *- Add additional tests to insure that include-locations
 *  and vars.php are setup correctly
 *
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