<?php
/**
 * Test harness for the XRMS ACL system
 *
 * Copyright (c) 2004 Explorer Fund Advisors, LLC
 * All Rights Reserved.
 *
 * @todo
 * $Id: xrms_activity_test.php,v 1.3 2005/06/06 16:57:09 daturaarutad Exp $
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

require_once($include_directory.'utils-activities.php');

$options['xrms_db_dbtype'] = $xrms_db_dbtype;
$options['xrms_db_server'] = $xrms_db_server;
$options['xrms_db_username'] = $xrms_db_username;
$options['xrms_db_password'] = $xrms_db_password;
$options['xrms_db_dbname'] = $xrms_db_dbname;

Class XRMSActivityTest extends PHPUnit_TestCase { 
    
    function XRMSActivityTest( $name = "XRMSActivityTest" ) {
        $this->PHPUnit_TestCase( $name );
    }
   function setUp() {   
       global $options;
       $this->session_user_id= session_check();
       $this->options = $options;
       $this->con = &adonewconnection($options['xrms_db_dbtype']);
       //connect to the xrms database
       $this->con->nconnect($options['xrms_db_server'], $options['xrms_db_username'], $options['xrms_db_password'], $options['xrms_db_dbname']);
        $this->test_activity_data= array(
                   'activity_type_id' => 1,
                   'on_what_table'    => 'opportunities',
                   'on_what_id'       => 3,
                   'on_what_status'   => 1,
                   'activity_title'   => 'Test Suite Activity: Ignore',
                   'activity_description' =>'This activity was added automatically by the test suite.  It should not be visible, and can safely be ignored',
                   'activity_status'  => 'c',
                   'scheduled_at'     => '2005-01-01 12:18',
                   'ends_at'          => '2005-01-01 16:23',
                   'company_id'       => 4,
                   'contact_id'       => 13,
                   'user_id'          => 1
            );


    }

   function teardown() {
       $this->con=NULL;
    }
    function test_XRMSTEST() {
	$this->assertTrue(true, "This should never fail.");
    }

   function test_add_activity($activity_data=false, $participants=false) {
        $con = $this->con;
        global $session_user_id;
        $session_user_id=$this->session_user_id;        
        if (!$activity_data) {
            $activity_data=$this->test_activity_data;
        }
        $activity_result=add_activity($con, $activity_data, $participants);            
        $this->assertTrue($activity_result, "Failed to add activity: {$activity_data['activity_title']}");
        return $activity_result;
   }
   
    function test_get_activity($activity_data=false) {
        $con = $this->con;
        if (!$activity_data) {
            $activity_data=$this->test_activity_data;
        }
        $activity_result=get_activity($con, $activity_data);
        $this->assertTrue($activity_result, "Failed to get information about activity");
        $this->assertTrue(is_array($activity_result),"Activity info is not an array, should be");
        if (is_array($activity_result)) {
            $this->assertTrue(is_array(current($activity_result)), "Individual activity is not array, should be");
        }
        return $activity_result;
    }
    
    function test_update_activity($activity_data=false, $activity_id=false, $activity_rst=false) {
        $con = $this->con;
	//if no activity id or recordset is provided, use test activity data	
	if (!$activity_id AND !$activity_rst) {
		$activity=$this->test_get_activity();;
		$one_activity=current($activity);
		$activity_id=$one_activity['activity_id'];
		$this->assertTrue($activity_id, "Failed to identify activity for update");		
	}
	//if no activity data is provided, create test data
	if (!$activity_data) {
		$activity_data['on_what_status']=2;
                $activity_data['contact_id']=8;
	}
	$result = update_activity($con, $activity_data, $activity_id, $activity_rst);
	$this->assertTrue($result, "Update to activity $activity_id recordset $activity_rst failed.");
	$new_activity_data['activity_id']=$activity_id;
	$new_activity=$this->test_get_activity($new_activity_data);
	$this->assertTrue($new_activity, "Failed to get updated activity");
	if ($new_activity) {
		$new_activity_data=current($new_activity);
		foreach ($activity_data as $ckey=>$cval) {
			$this->assertTrue($new_activity_data[$ckey]==$cval, "Update error: $ckey values do not match: {$new_activity_data[$ckey]}!=$cval");
		}
	}
	return $result;
   }
    
    function test_delete_activity($activity_id=false, $delete_from_database=true) {
        $con = $this->con;
        $session_user_id=$this->session_user_id;
        if (!$activity_id) {
            $activity_data=$this->test_activity_data;
	    //removed because update resets this variable
	    unset($activity_data['on_what_status']);
	    unset($activity_data['contact_id']);
            $activity_info=$this->test_get_activity($activity_data);
            $this->assertTrue($activity_info,"Failed to look up test activity to delete.");
            if ($activity_info) {
                $activity=current($activity_info);
                $activity_id=$activity['activity_id'];
            } else return false;
        }
        $this->assertTrue($activity_id, "No activity_id available, cannot delete activity");
        $activity_result=delete_activity($con, $activity_id, $delete_from_database);
        $this->assertTrue($activity_result, "Failed to delete activity $activity_id from database");
        return $activity_result;   
    }

    function test_delete_activites($where_cluase=false, $delete_from_database=true) {
        $con = $this->con;
        $session_user_id=$this->session_user_id;

        $activity_data=$this->test_activity_data;

		$test_title = 'test_delete_activites test';

	    $activity_data['activity_title'] = $test_title;

        $activity_info1=$this->test_add_activity($activity_data);
        $this->assertTrue($activity_info1,"Failed to add test activity 1 to delete.");
        $activity_info2=$this->test_add_activity($activity_data);
        $this->assertTrue($activity_info2,"Failed to add test activity 2 to delete.");

        if($activity_info1 && $activity_info2) {
			$delete_status = delete_activities($con, "activity_title='$test_title'", true, true);
        } else return false;
        $this->assertTrue($delete_status, "Failed to delete activities from database");
        return $delete_status;   
    }
    
    function test_activity_participants($activity_id=false, $contact_id=false, $activity_participant_position_id=1) {
        if (!$activity_id) {
            $new_act=$this->test_add_activity();
            $activity_id=$new_act;
        } else $new_act=false;
        if (!$contact_id) {
            $contact_id=12;
        }
        $ret=add_activity_participant($this->con, $activity_id, $contact_id, $activity_participant_position_id);
        $this->assertTrue($ret, "Failed to add activity participant $contact_id to activity $activity_id with position  $activity_participant_position_id");
        if ($ret) {
            $get_result=get_activity_participants($this->con, $activity_id);
            $this->assertTrue($get_result, "Failed to get activity participants for $activity_id");
            if ($get_result) {
                $foundmatch=false;
                foreach ($get_result as $participant) {
                    if ($participant['contact_id']=$contact_id AND $participant['activity_participant_position_id']=$activity_participant_position_id) {
                        $foundmatch=true;
                    }
                }
                $this->assertTrue($foundmatch, "Failed to find contact $contact_id with position $activity_participant_position_id in activity $activity_id");
            }
        }
        $ret=delete_activity($this->con, $activity_id, true);
        $get_result=get_activity_participants($this->con, $activity_id);
        $this->assertTrue(!$get_result, "Failed to delete activity participants for activity on activity delete");    
    }
        
     function test_get_activity_type($short_name=false,$pretty_name=false, $type_id=false) {
        if (!$short_name) $short_name='CTO';
        
        $ret = get_activity_type($this->con, $short_name, $pretty_name, $type_id);
        $this->assertTrue($ret, "Activity type failed to return properly for short name $short_name pretty $pretty_name id $type_id");
        
        return $ret;
    }
    

}

$suite= new PHPUnit_TestSuite( "XRMSActivityTest" );
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
 * $Log: xrms_activity_test.php,v $
 * Revision 1.3  2005/06/06 16:57:09  daturaarutad
 * added delete_activities test
 *
 * Revision 1.2  2005/05/06 20:51:13  vanmer
 * - new test for activity type retrieval
 *
 * Revision 1.1  2005/04/15 08:00:32  vanmer
 * -Initial revision of tests for the activities participant and position API
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