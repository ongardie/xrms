<?php
/**
 * Test harness for the XRMS ACL system
 *
 * Copyright (c) 2004 Explorer Fund Advisors, LLC
 * All Rights Reserved.
 *
 * @todo
 * $Id: xrms_test.php,v 1.1 2005/03/09 16:16:52 vanmer Exp $
 */

require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'utils-preferences.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

require_once("PHPUnit.php");
require_once("PHPUnit/GUI/HTML.php");

$options['xrms_db_dbtype'] = $xrms_db_dbtype;
$options['xrms_db_server'] = $xrms_db_server;
$options['xrms_db_username'] = $xrms_db_username;
$options['xrms_db_password'] = $xrms_db_password;
$options['xrms_db_dbname'] = $xrms_db_dbname;

Class XRMSTest extends PHPUnit_TestCase { 
    
    function XRMSTest( $name = "XRMSTest" ) {
        $this->PHPUnit_TestCase( $name );
    }
   function setUp() {   
       global $options;
       $this->options = $options;
       $this->con = &adonewconnection($options['xrms_db_dbtype']);
       //connect to the xrms database
       $this->con->nconnect($options['xrms_db_server'], $options['xrms_db_username'], $options['xrms_db_password'], $options['xrms_db_dbname']);

    }

   function teardown() {
       $this->con=NULL;
    }
    function test_XRMSTEST() {
	$this->assertTrue(true, "This should never fail.");
    }
    
    function test_set_user_preference($user_id=1, $preference_type='user_language', $preference_value='en_US', $preference_name=false, $set_default=false, $delete=true) {
        $con = $this->con;
        $ret=set_user_preference($con, $user_id, $preference_type, $preference_value, $preference_name, $set_default);
        $this->assertTrue($ret, "Failed to set preference $preference_type to $preference_value for user $user_id");
        $newret = get_user_preference($con, $user_id, $preference_type, $preference_name);
        $this->assertTrue($newret, "Failed to find user preference just set");
        $success=($newret==$preference_value);
        $this->assertTrue($success, "Failed to set preference $preference_type to $preference_value != $newret");
        if ($delete) {
            $dret = delete_user_preference($con, $user_id, $preference_type, $preference_name, false, true);
        }
        return $success;
    }
    
    function test_set_multi_user_preference($user_id=1, $preference_type='random_multi_option', $preference_values=false) {
        $con = $this->con;    
        if (!$preference_values) { $preference_values=array('size'=>'1','shape'=>'two and two is four','texture'=>'goddamn'); }
        foreach ($preference_values as $pkey=>$pval) {
            $ret = $this->test_set_user_preference($user_id, $preference_type, $pval, $pkey, true, false);
            $this->assertTrue($ret, "Failed to assign multi-select for preference $preference_type, $pkey=$pval");
            $default_value=$pval;
        }
        $prefs = get_user_preference($con, $user_id, $preference_type, false, true);
//        print_r($prefs);
        foreach ($preference_values as $pkey=>$pval) {
            $this->assertTrue(array_key_exists($pkey, $prefs), "Failed to find newly set preference $preference_type $pkey=$pval");
            $this->assertTrue($pval==$prefs[$pkey], "Newly set option $preference_type $pkey={$prefs[$pkey]} not equal to stored option $pval");
        }
        foreach ($prefs as $pkey=>$pval) {
            $ret = get_user_preference($con, $user_id, $preference_type, $pkey);
            $this->assertTrue($pval==$ret, "Failed match user preference for $preference_type name $pkey: $pval!=$ret");
        }
        $default_pref_value=get_user_preference($con, $user_id, $preference_type);
        $this->assertTrue($default_value==$default_pref_value, "Failed to set default value correctly, expected $default_value but returned $default_pref_value");
        $dret = delete_user_preference($con, $user_id, $preference_type, false, true, true);
    }
    function test_xrms_insert() {
	$con=$this->con;
	$sql = "INSERT INTO Role ( ROLE_NAME ) VALUES ( 'blah' )";
	$rs=$con->execute($sql);
	if (!$rs) db_error_handler($con, $sql);
	$sql = "SELECT * FROM Role";
	$newrs=$con->execute($sql);
	echo "<pre>";
	while (!$newrs->EOF) {
		print_r($newrs->fields);
		$newrs->movenext();
	}
	echo "</pre>";
    }
}

$suite= new PHPUnit_TestSuite( "XRMSTest" );
$display = new PHPUnit_GUI_HTML($suite);
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