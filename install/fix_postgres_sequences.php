<?php
/**
 * fix_postgres_sequences.php - This page fixes the postgres sequences for all tables, and should be run direct
 * after installing on a postgres db
 *
 * $Id: fix_postgres_sequences.php,v 1.1 2006/04/25 20:20:17 vanmer Exp $
 */

if (!defined('IN_XRMS')) {
    define('IN_XRMS', true);
}

// include the installation utility routines
require_once('install-utils.inc');
require_once('database.php');
require_once('data.php');

// make sure that the file does not end with whitespace
// check_extra_whitespace("../", "include-locations.inc");

// where do we include from
require_once('../include-locations.inc');

$structure_only=$_GET['structure_only'];
if ($structure_only) {
    $execute_data=false;
} else $execute_data='true';

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


// now check to make sure that the include_directory variable ends with a slash
if (substr($include_directory, -1) != "/") {
    // Oops!  The include directory variable does not end with a slash
    // Now instruct the user in how to set this value
    $problem = 'The include directory variable has not been set correctly.<BR><BR>';
    $problem .= 'Please open the include-locations.inc file which is located ';
    $problem .= 'at the top level of the xrms installation.  Change the value ';
    $problem .= 'of the $include_directory variable to the correct path. A slash ';
    $problem .= 'should be the last character of the path.<BR><BR>';
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

// now check to make sure that the include-directory actually has include files
if (!file_exists ($include_directory . 'vars.php') ) {
    // Oops!  The include directory does not have an expected file in it!
    // Now instruct the user in how to set this value
    $problem = 'The include directory variable has not been set correctly.<BR><BR>';
    $problem .= "It is currently set to '$include_directory', but vars.php could not ";
    $problem .= "be found in this directory.<BR><BR>";
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

// make sure that the file does not end with whitespace
// check_extra_whitespace($include_directory, "vars.php");

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

// Is register_globals on.  This should be off--it is a security hole and causes a large
// number of problems for XRMS
$rg = ini_get ('register_globals' );
if ($rg) {
    // Oops!  Register globals is on
    // Now instruct the user in how to turn it off
    $problem = 'Register_globals is currently on for your server.  It must be turned off for XRMS.<BR><BR>';
    $problem .= "(It is obsolete, and a security hole that causes problems for XRMS.)<BR><BR>";
    $problem .= 'Modify your php.ini file to turn off register_globals.<BR><BR>';

    install_fatal_error($problem);
}

// more tests on the vars.php variables should exist here
// optimally, all variables would be tested.


// get required common files
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'utils-activities.php');
require_once($include_directory . 'adodb/adodb.inc.php');


// can we make a database connection?
$con = get_xrms_dbconnection();
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


// if you can make a database connection make sure that you are running at least version 4 of MYSQL
// otherwise alert the user to potential problems
if($xrms_db_dbtype=="mysql"){
        // dont use adodb bcos i need the link identifier for mysql_get_server_info
        $link = mysql_connect($xrms_db_server, $xrms_db_username, $xrms_db_password);
        $ver  = mysql_get_server_info($link);
        mysql_close($link);//close the link when you are done with it.
  if (version_compare($ver,"4.0.2")<0){
    // Ooops! you are not running a compliant version of mysql
    // Now instruct the user in how to fix this problem
    $install_notes .= "<p>WARNING: XRMS strongly recommends MySQL version 4.0.2 or above. <BR>\n";
    $install_notes .='You are currently running MySQL Server version ' . $ver . "<BR>\n";
    $install_notes .= "At the moment, MySQL version 3.23.x seems to work, but is not supported by the XRMS development team.</p>\n";
    $install_notes .= "<p>Please install version 4.0.2 or greater of MySQL, available here: ";
    $install_notes .= "<a href=\"http://dev.mysql.com/downloads/\">MySQL Downloads</a></p>\n";

  };
};

$tables = list_db_tables($con);
foreach ($tables as $table) {
    $table_info=$con->MetaColumns($table);
    foreach ($table_info as $key=>$field_info) {
      $default=$field_info->default_value;
      if (strpos($default,'nextval')!==false) {
          $str_arr=explode("'",$default);
	  $max_sql = "SELECT MAX({$field_info->name}) as seq_max FROM $table";
	  $max_rst=$con->execute($max_sql);
	  if (!$max_rst) { db_error_handler($con, $max_sql); }
	  else $seq_max = $max_rst->fields['seq_max'];
	  if ($seq_max) {
             $new_max=$seq_max+1;
	     $sql = "SELECT setval('{$str_arr[1]}',$new_max);";
	     $rst=$con->execute($sql);
	     if (!$rst) db_error_handler($con, $sql);
	  }
	  echo "$table: {$field_info->name} - " . $str_arr[1] . " $seq_max<br>";
      }
    }
}
/*
//use extracted XML file to create and execute the table structure SQL statements
require_once($include_directory . 'adodb/adodb-xmlschema.inc.php' );

$schemaFile='xrms-schema.xml';
$schema = new adoSchema($con);
$schema->seperateDataSQL=true;
$schema->ParseSchemaFile( $schemaFile );
$structure_sql=$schema->sqlArray;
$data_sql=$schema->getDataSQL();

//INSTALL STRUCTURE
foreach ($structure_sql as $sql) {
	$rst=$con->execute($sql);
	if (!$rst) db_error_handler($con, $sql);
}

//INSTALL DATA
if ($execute_data=='true') {
    foreach ($data_sql as $sql) {
	$rst=$con->execute($sql);
	if (!$rst) db_error_handler($con, $sql);
    }
}

//run plugin installation, pass adodb database connection
do_hook_function('xrms_install', $con);

//close the connection
$con->close();

// get message
$msg = isset($_GET['msg']) ? $_GET['msg'] : '';

$page_title = "Installation Complete";
start_page($page_title, false, $msg);
echo $install_notes;

<BR>
All of the tables have been created, and initial data has been populated.
<BR><BR>
The initial user available is "user1" with a password of "user1".  You should change this
as soon as you login.  (It can be changed in Users within the Administration section.)
<BR><BR>
You may now <a href="sample.php">create sample data</a> to try out the system, or
<a href="../login.php">login</a> to get started entering your own data.
*/



/**
 *$Log: fix_postgres_sequences.php,v $
 *Revision 1.1  2006/04/25 20:20:17  vanmer
 *- Initial revision of a script to fix the sequences in postgres after install
 *
 *Revision 1.22  2006/01/10 23:00:41  vanmer
 *- added flag to allow only structure to be installed, no data
 *
 *Revision 1.21  2006/01/06 21:40:02  vanmer
 *- fixed broker connection test on install
 *
 *Revision 1.20  2006/01/02 23:05:45  vanmer
 *- changed to use centralized dbconnection function
 *
 *Revision 1.19  2005/11/30 00:42:11  vanmer
 *- changed install to use adodb xml schema file instead of database and data functions with
 *hardcoded SQL
 *- installs structure and then data from xrms-schema.xml in the install directory
 *
 *Revision 1.18  2005/09/29 14:42:39  vanmer
 *- changed to install default partipant positions on install
 *
 *Revision 1.17  2005/09/22 13:09:18  braverock
 *- remove trailing ?> from vars.php and include-locations.inc
 *  *trailing text on vars and include-locations file will now be parsed as php
 *- remove whitespace check from install, it causes too many problems
 *
 *Revision 1.16  2005/05/23 19:45:41  vanmer
 *- moved create database data to after ACL tables are installed
 *
 *Revision 1.15  2005/04/17 15:11:30  maulani
 *- Add additional install checks
 *
 *Revision 1.14  2005/01/28 15:34:43  braverock
 *- fix problem with some PHP versions where register_globals check may return 0
 *  (others return an empty string) - new test catches either
 *  credit Ingo Hoff for reporting the problem
 *
 *Revision 1.13  2005/01/25 06:04:39  vanmer
 *- added hook for plugins to run at xrms install
 *
 *Revision 1.12  2005/01/13 17:32:43  vanmer
 **** empty log message ***
 *
 *Revision 1.11  2005/01/12 12:43:32  braverock
 *- replace erroneous assignment operator in xrms_db_dbtype check
 *
 *Revision 1.10  2004/08/02 08:51:24  maulani
 *- Add test to check register_globals
 *
 *Revision 1.9  2004/08/02 08:49:55  maulani
 *- Add test to check register_globals
 *
 *Revision 1.8  2004/08/02 01:31:19  maulani
 *- Add ending whitespace check to include-locations.inc and vars.php.
 *- Fix bugs involving dependency on general system includes
 *
 *Revision 1.7  2004/07/28 17:25:33  braverock
 *- turned MySQL version check into a non-fatal warning
 *
 *Revision 1.6  2004/07/19 21:14:24  maulani
 *- Add check to make sure that IN_XRMS is defined only once
 *- Fix database connection test with solution posed by Brian Peterson (braverock)
 *  in RFE 946911
 *
 *Revision 1.5  2004/07/14 19:14:40  braverock
 *- add IN_XRMS to support secure use in installation.
 *
 *Revision 1.4  2004/07/13 12:57:10  cpsource
 *Make sure $msg is always defined.
 *
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
