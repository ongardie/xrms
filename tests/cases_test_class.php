<?php
/**
 * Test harness for the XRMS case API
 *
 * Copyright (c) 2004 Explorer Fund Advisors, LLC
 * All Rights Reserved.
 *
 * @todo
 * $Id: cases_test_class.php,v 1.1 2006/04/26 00:55:30 vanmer Exp $
 */

require_once('../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'utils-preferences.php');
require_once($include_directory . 'utils-recurrence.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

require_once("PHPUnit.php");
require_once("PHPUnit/GUI/HTML.php");

require_once($include_directory.'utils-cases.php');

$options['xrms_db_dbtype'] = $xrms_db_dbtype;
$options['xrms_db_server'] = $xrms_db_server;
$options['xrms_db_username'] = $xrms_db_username;
$options['xrms_db_password'] = $xrms_db_password;
$options['xrms_db_dbname'] = $xrms_db_dbname;

Class XRMSCaseTest extends XRMS_TestCase {
    
    function XRMSCaseTest( $name = "XRMSCaseTest" ) {
        $this->PHPUnit_TestCase( $name );
    }
   function setUp() {
        parent::setUp();
       $this->session_user_id= session_check();
        $this->type_status_test = new XRMSTypeStatusTest();
        $this->type_status_test->_result =& $this->_result;
        $this->type_status_test->setUp();

        $this->test_type_id=$this->type_status_test->test_get_entity_type();
        $this->test_status_id=$this->type_status_test->test_get_entity_status();

        $this->test_case_data= array(
                   'case_title'   => 'Test Suite Case: Ignore',
                   'case_description' =>'This case was added automatically by the test suite.  It should not be visible, and can safely be ignored',
                    'due_at' => '2006-04-20',
                    'case_type_id'=>$this->test_type_id,
                    'case_status_id'=>$this->test_status_id,
                    'company_id'=>1,
                    'contact_id'=>1
            );


    }

   function teardown() {
        $this->type_status_test->test_delete_entity_type($this->test_type_id);
        $this->type_status_test->test_delete_entity_status($this->test_status_id);
        $this->type_status_test->teardown();
        parent::teardown();
    }
    function test_XRMSTEST() {
	$this->assertTrue(true, "This should never fail.");
    }

   function test_add_case($case_data=false) {
        $con = $this->con;
        global $session_user_id;
        $session_user_id=$this->session_user_id;
        if (!$case_data) {
            $case_data=$this->test_case_data;
        }
        $case_result=add_case($con, $case_data);
        $this->assertTrue($case_result, "Failed to add case: {$case_data['title']}");
        return $case_result;
   }
   
    function test_find_case($case_data=false, $show_deleted=false, $return_recordset=false) {
        $con = $this->con;
        if (!$case_data) {
            $case_data=$this->test_case_data;
        }
        $case_result=find_case($con, $case_data, $show_deleted, $return_recordset);
        $this->assertTrue($case_result, "Failed to get information about case");
        if (!$return_recordset) {
            $this->assertTrue(is_array($case_result),"Case info is not an array, should be");
            if (is_array($case_result)) {
                $this->assertTrue(is_array(current($case_result)), "Individual case is not array, should be");
            }
        } else {
            $this->assertTrue(is_object($case_result), "Failed to match intended return of find_case to an object");
        }
        return $case_result;
    }
    
    function test_get_case($case_id=false, $return_rst=false) {
        $con = $this->con;
        if (!$case_data) {
            $case_data=$this->test_case_data;
        }
        $case_result=get_case($con, $case_id, $return_rst);
        if ($case_id) {
            $this->assertTrue($case_result, "Failed to get information about case");
            $this->assertTrue(is_array($case_result),"Case info is not an array, should be");
        } else { $this->assertTrue($case_result==false, "Expected to fail retreiving case, instead found a case"); }
        return $case_result;
    }

    function test_update_case($case_data=false, $case_id=false, $case_rst=false) {
        $con = $this->con;
	//if no case id or recordset is provided, use test case data	
	if (!$case_id AND !$case_rst) {
		$case=$this->test_find_case();
                if (!$case) { $this->fail("No case found to update using find_case function, failing further tests"); return false; }
                $case=current($case);
		$case_id=$case['case_id'];
		$this->assertTrue($case_id, "Failed to identify case for update");		
	}
	//if no case data is provided, create test data
	if (!$case_data) {
		$case_data['profile'].=' Changed For Test';
                $case_data['tax_id']='123-45-6789';
	}
	$result = update_case($con, $case_data, $case_id, $case_rst);
	$this->assertTrue($result, "Update to case $case_id recordset $case_rst failed.");
	$new_case_data['case_id']=$case_id;
	$new_case=$this->test_get_case($case_id);
	$this->assertTrue($new_case, "Failed to get updated case");
	if ($new_case) {
		$new_case_data=$new_case;
		foreach ($case_data as $ckey=>$cval) {
			$this->assertTrue($new_case_data[$ckey]==$cval, "Update error: $ckey values do not match: {$new_case_data[$ckey]}!=$cval");
		}
	}
	return $result;
   }
    
    function test_delete_case($case_id=false, $delete_from_database=true) {
        $con = $this->con;
        $session_user_id=$this->session_user_id;
        if (!$case_id) {
            $case_data=$this->test_case_data;
	    //removed because update resets this variable
	    unset($case_data['profile']);
	    unset($case_data['tax_id']);
            $case_info=$this->test_find_case($case_data);
            $this->assertTrue($case_info,"Failed to look up test case to delete.");
            if ($case_info) {
                $case=current($case_info);
                $case_id=$case['case_id'];
            } else return false;
        }
        $this->assertTrue($case_id, "No case_id available, cannot delete case");
        $case_result=delete_case($con, $case_id, $delete_from_database);
        $this->assertTrue($case_result, "Failed to delete case $case_id from database");
        return $case_result;
    }

    function test_case_strange_characters($case_data=false, $delete_from_database=true) {
        if (!$case_data) $case_data= array(
                   'case_name'   => 'Test Suite Company O\'doole & Tomlin: Ignore',
                   'profile' =>'This case was added automatically by the test suite.  It shouldn\'t be visible, and can safely be ignored',
                    'tax_id' => '0987-12-2033'
            );

        $test_case_id=$this->test_add_case($case_data);
        $this->assertTrue($test_case_id, "Failed to add a new case with strange characters, failing other tests");
        if ($test_case_id) {
            $ret=$this->test_find_case($case_data);
            $this->assertTrue($ret, "Failed to find case with strange data");
        } else return false;
        
        $case_data['profile']="This case with added automatically, and can't be seen because it should've been deleted by &";
        $ret=$this->test_update_case($case_data, $test_case_id);
        $this->assertTrue($ret, "Failed to update case with strange data");
        $this->test_delete_case($test_case_id, $delete_from_database);
    }

}

/*
 * $Log: cases_test_class.php,v $
 * Revision 1.1  2006/04/26 00:55:30  vanmer
 * - added new test classes for cases, statuses and types and integration tests
 *
 *
 */
?>