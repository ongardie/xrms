<?php
/**
 * Test harness for the XRMS ACL system
 *
 * Copyright (c) 2004 Explorer Fund Advisors, LLC
 * All Rights Reserved.
 *
 * @todo
 * $Id: xrms_test.php,v 1.11 2006/04/28 04:30:09 vanmer Exp $
 */

require_once('../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'utils-preferences.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

require_once("PHPUnit/GUI/HTML.php");

global $in_xrms_tests;
$in_xrms_tests=true;

require_once($include_directory.'../tests/xrms_test_class.php');
require_once($include_directory.'classes/acl/tests/xrms_acl_test.php');
require_once($include_directory.'../tests/contacts_test_class.php');
require_once($include_directory.'../tests/companies_test_class.php');
require_once($include_directory.'../tests/integration_test_class.php');
require_once($include_directory.'../tests/type_status_class.php');
require_once($include_directory.'../tests/cases_test_class.php');
require_once($include_directory.'../tests/opportunities_test_class.php');
require_once($include_directory.'../tests/activities/xrms_activity_test.php');
require_once($include_directory.'classes/File/tests/files_test_class.php');
require_once($include_directory.'classes/File/tests/fixed_width_parser_test.php');


//global $options;
//$options=array();


$options['xrms_db_dbtype'] = $xrms_db_dbtype;
$options['xrms_db_server'] = $xrms_db_server;
$options['xrms_db_username'] = $xrms_db_username;
$options['xrms_db_password'] = $xrms_db_password;
$options['xrms_db_dbname'] = $xrms_db_dbname;


$suite= new PHPUnit_TestSuite( "XRMSTest" );

//add XRMS tests to the list of suites to run
$suite_array=array ($suite);

//add ACL tests to the list of suites to run
$suite_array[]= new PHPUnit_TestSuite( "ACLTest" );
$suite_array[]= new PHPUnit_TestSuite( "XRMSCompanyTest" );
$suite_array[]= new PHPUnit_TestSuite( "XRMSContactTest" );
$suite_array[]= new PHPUnit_TestSuite( "XRMSTypeStatusTest" );
$suite_array[]= new PHPUnit_TestSuite( "XRMSCaseTest" );
$suite_array[]= new PHPUnit_TestSuite( "XRMSOpportunityTest" );
$suite_array[]= new PHPUnit_TestSuite( "XRMSActivityTest" );
$suite_array[] = new PHPUnit_TestSuite( "XRMSIntegrationTest" );
$suite_array[] = new PHPUnit_TestSuite( "FilesStaticTest" );
$suite_array[] = new PHPUnit_TestSuite( "FilesPropertiesTest" );
$suite_array[] = new PHPUnit_TestSuite( "FilesFailuresTest" );
$suite_array[] = new PHPUnit_TestSuite( "FilesObjectDisplay" );
$suite_array[] = new PHPUnit_TestSuite( "FilesManipulationTest" );
$suite_array[] = new PHPUnit_TestSuite( "FixedWidthParserTest" );

$ret=do_hook_function('xrms_test_suite', $suite_array);

$display = new PHPUnit_GUI_HTML($suite_array);
$display->show();


//$suite = new PHPUnit_TestSuite( "get_object_groups_object_inherit");
/*
$test = new ACLTest( "test_get_object_groups_object_inherit");
$display = new PHPUnit_GUI_HTML($test);
$display->show();
*/
//$result = PHPUnit::run($suite);
//print $result->toHTML();

//$testRunner = new TestRunner();
//$testRunner->run($suite);
/*
 $test = new ACLTest( "test_get_object_groups_object_inherit");
 $testRunner = new TestRunner();
 $testRunner->run( $test );
 */
/*
 * $Log: xrms_test.php,v $
 * Revision 1.11  2006/04/28 04:30:09  vanmer
 * - added opportunities test suite to main tests
 *
 * Revision 1.10  2006/04/26 00:55:30  vanmer
 * - added new test classes for cases, statuses and types and integration tests
 *
 * Revision 1.9  2006/01/17 02:26:02  vanmer
 * - added companies test to xrms tests
 * - added activities tests to main xrms tests
 *
 * Revision 1.8  2005/11/18 20:08:28  vanmer
 * - removed test class definitions (now in xrms_test_class.php), added contact tests to list of available tests
 *
 * Revision 1.7  2005/10/04 03:23:46  vanmer
 * - added fixed width parser tests to xrms test suite
 *
 * Revision 1.6  2005/10/01 08:24:41  vanmer
 * - changed to instantiate a new dbconnection for tests
 * - added ACL and file tests to list of tests
 *
 * Revision 1.5  2005/09/30 22:11:04  vanmer
 * - added hook to allow XRMS to run any tests that are provided by a plugin
 *
 * Revision 1.4  2005/05/18 21:49:29  vanmer
 * - added test for adding workflow history
 *
 * Revision 1.3  2005/05/09 22:42:10  vanmer
 * - added test for new session caching functions
 *
 * Revision 1.2  2005/05/06 00:51:18  vanmer
 * - added tests for preferences system options
 *
 * Revision 1.1  2005/03/09 16:16:52  vanmer
 * - moving test suite from admin/tests to basedir/tests
 *
 * Revision 1.1  2005/01/13 17:08:20  vanmer
 * - Initial Revision of the ACL PHPUnit test class
 *
 * Revision 1.16  2005/01/03 18:31:21  ke
 * - New test function for checking permission on an object class instead of a particular object
 *
 * Revision 1.15  2004/12/14 22:50:19  ke
 * - removed unneeded options from tests, now exist entirely in config
 *
 * Revision 1.14  2004/12/13 16:36:55  ke
 * - added unneeded newline
 * - added commented section for running only one test at a time
 *
 * Revision 1.13  2004/12/02 07:03:23  ke
 * - changed to use standard xrms_acl_config.php file for database connection information
 * - changed standard object names to include prepended set name, to avoid conflicts with existing data
 * - added test for field list function
 * - added test for restricted object list function
 * Bug 64
 *
 * Revision 1.12  2004/12/01 19:52:13  ke
 * - fixed controlled object relationship when testing object inheritance
 * - changed on_what_id's in some tests to reflect different XRMS dataset
 *
 * Revision 1.11  2004/11/24 23:27:20  ke
 * - changed test to reflect new return method from role list function
 * - changed test of object permissions without real data in tables to restrict scope search to only world
 *
 * Revision 1.10  2004/11/09 04:14:33  ke
 * - Added prefix to allow tests to succeed in non-empty databases
 * - Added function to test group inheritance for permission checks
 * Bug 64
 *
 * Revision 1.9  2004/11/09 01:55:08  ke
 * - added test for recursive object permissions inheritance (passes successfully)
 * Bug 64
 *
 * Revision 1.8  2004/11/08 21:50:19  ke
 * - updated tests on recursive objects to call recursive group list function
 * - added test for basic permissions check
 * - altered earlier tests to allow them to be called properly
 * Bug 64
 *
 * Revision 1.7  2004/11/05 19:13:36  ke
 * - fixed bug with db authentication parameters
 *
 * Revision 1.6  2004/11/05 09:33:11  ke
 * - updated to use new PHPUnit output, classes and assert methods
 * Bug 64
 *
 * Revision 1.5  2004/11/05 01:15:25  ke
 * - added tests for new low-level ACL functions
 * - added test to ensure group inheritance between objects
 * Bug 64
 *
 *
 */
 ?>