<?php
/**
 * Test harness for the XRMS case API
 *
 * Copyright (c) 2004 Explorer Fund Advisors, LLC
 * All Rights Reserved.
 *
 * @todo
 * $Id: cases_test_class.php,v 1.4 2006/05/02 00:46:54 vanmer Exp $
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


global $test_types;
global $test_statuses;

Class XRMSCaseTest extends XRMS_TestCase {

    function XRMSCaseTest( $name = "XRMSCaseTest" ) {
        $this->PHPUnit_TestCase( $name );
        $this->type_status_test = new XRMSTypeStatusTest();
        $this->type_status_test->_result =& $this->_result;
        $this->type_status_test->setUp();
        $this->entity_type='case';
        $this->test_type_id=$this->type_status_test->test_add_entity_type($this->entity_type);
        $this->test_status_id=$this->type_status_test->test_add_entity_status($this->entity_type);

       if (isset($this->classname))
          $this->classname = get_parent_class($this->classname);
       else
          $this->classname = get_class($this);

       if (method_exists($this, "_".$this->classname)) {
          register_shutdown_function(array($this, "_".$this->classname));
       }
    }

    function _XRMSCaseTest() {
        $this->type_status_test->test_delete_entity_type($this->entity_type, $this->test_type_id);
        $this->type_status_test->test_delete_entity_status($this->entity_type, $this->test_status_id);
        $this->type_status_test->teardown();
    }

   function setUp() {
        parent::setUp();
       $this->session_user_id= session_check();

        $this->test_case_data= array(
                   'case_title'   => 'Test Suite Case: Ignore',
                   'case_description' =>'This case was added automatically by the test suite.  It should not be visible, and can safely be ignored',
                    'due_at' => '2006-04-20 12:01:01',
                    'case_type_id'=>$this->test_type_id,
                    'case_status_id'=>$this->test_status_id,
                    'company_id'=>1,
                    'contact_id'=>1
            );


    }

   function teardown() {
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
        $ret=$this->test_case_status_consistency($case_result, $case_data['case_status_id']);
        $this->assertTrue($ret, "Failed test_case_status_consistency check, should succeed");
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
        $case_result=get_case($con, $case_id, $return_rst);
        if ($case_id) {
            $this->assertTrue($case_result, "Failed to get information about case");
            if (!$return_rst) {
                $this->assertTrue(is_array($case_result),"Case info is not an array, should be");
            } else {
                $this->assertTrue(is_object($case_result),"Case info is not an object, should be");
            }
        } else { $this->assertTrue($case_result==false, "Expected to fail retreiving case, instead found a case"); }
        return $case_result;
    }

    function test_update_case($case_data=NULL, $case_id=false, $case_rst=false) {
        $con = $this->con;
	//if no case id or recordset is provided, use test case data	
	if (!$case_id AND !$case_rst) {
		$case=$this->test_find_case();
                if (!$case) { $this->fail("No case found to update using find_case function, failing further tests"); return false; }
                $case=current($case);
		$case_id=$case['case_id'];
		$this->assertTrue($case_id, "Failed to identify case for update.  Skipping further tests.");
                if (!$case_id) return false;		
	}
	//if no case data is provided, retrieve it
	if ($case_data===NULL) {
                $case_data=$this->test_get_case($case_id);
		$case_data['case_description'].=' Changed For Test';
                $case_data['due_at']='2006-04-21 00:00:00';
	}
	$result = update_case($con, $case_data, $case_id, $case_rst);
	$this->assertTrue($result, "Update to case $case_id recordset $case_rst failed.");
	$new_case_data['case_id']=$case_id;
	$new_case=$this->test_get_case($case_id);
	$this->assertTrue($new_case, "Failed to get updated case");
	if ($new_case) {
		$new_case_data=$new_case;
		foreach ($case_data as $ckey=>$cval) {
            switch ($ckey) {
                case 'last_modified_at':
                    //don't compare this one
                break;
                default: 
        			$this->assertTrue($new_case_data[$ckey]==$cval, "Update error: $ckey values do not match: {$new_case_data[$ckey]}!=$cval");
                break;
            }
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
            unset($case_data['case_description']);
            unset($case_data['due_at']);
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

    function test_case_strange_characters($case_data=NULL, $delete_from_database=true) {
        if ($case_data===NULL) $case_data= array(
                   'case_title'   => 'Test Suite Case O\'doole & Tomlin: Ignore',
                   'case_description' =>'This case was added automatically by the test suite.  It shouldn\'t be visible, and can safely be ignored',
                    'due_at' => '1987-12-30 00:00:00',
                    'case_type_id'=>$this->test_type_id,
                    'case_status_id'=>$this->test_status_id,
                    'company_id' => 1
            );

        $test_case_id=$this->test_add_case($case_data);
        $this->assertTrue($test_case_id, "Failed to add a new case with strange characters, failing other tests");
        if ($test_case_id) {
            $ret=$this->test_find_case($case_data);
            $this->assertTrue($ret, "Failed to find case with strange data");
        } else return false;
        
        $case_data['case_description']="This case was added automatically, and can't be seen because it should've been deleted by &";
        $ret=$this->test_update_case($case_data, $test_case_id);
        $this->assertTrue($ret, "Failed to update case with strange data");
        $this->test_delete_case($test_case_id, $delete_from_database);
    }

    function test_case_change_status($case_data=NULL, $new_status=NULL) {
        if ($case_data===NULL) {
            $case_data=$this->test_case_data;
        }

        $case_id=$this->test_add_case($case_data);

        if (!$case_id)  {
            $this->fail("Failed to add case for status change test, skipping further tests.");
            return false;
        }

        if ($new_status===NULL) {
            $this->type_status_test->_result =& $this->_result;
            $new_status=$this->type_status_test->test_add_entity_status_closed_resolved($this->entity_type);
            $delete_new_status=true;
        } else $delete_new_status=false;

        $case_data["{$this->entity_type}_status_id"]=$new_status;
        $ret=$this->test_update_case($case_data, $case_id);
        if (!$ret) {
            $this->fail("Failed to update case for status change test, skipping further tests");
        } else {
            $ret=$this->test_case_status_consistency($case_id, $new_status);
            $this->assertTrue($ret, "Failed test_case_status_consistency check, should succeed");
        }

        if ($delete_new_status) {
            $this->type_status_test->test_delete_entity_status($this->entity_type, $new_status);
        }

        $this->test_delete_case($case_id);

    }


    function test_case_change_status_unresolved_to_open($case_data=NULL) {
        if ($case_data===NULL) {
            $case_data=$this->test_case_data;
            $new_status=$case_data['case_status_id'];
            $ustatus=$this->type_status_test->test_add_entity_status_closed_unresolved($this->entity_type);
            $delete_ustatus=true;
            $case_data['case_status_id']=$ustatus;
        } else $delete_ustatus=false;

        $ret= $this->test_case_change_status($case_data, $new_status);
        if ($delete_ustatus) {
            $this->type_status_test->test_delete_entity_status_closed_unresolved($this->entity_type);
        }
        return $ret;
    }

    function test_case_status_consistency($case_id=false, $new_status=false) {
        if ((!$case_id) OR (!$new_status)) return false;
        $new_status_data=$this->type_status_test->test_get_entity_status($this->entity_type, $new_status);
        $open_indicator=$new_status_data['status_open_indicator'];

        $new_case_data=$this->test_get_case($case_id);
        switch ($open_indicator) {
            case 'o':
                $this->assertTrue(!$new_case_data['closed_at'], "Case is closed while status indicates open.");
                $ret=!$new_case_data['closed_at'];
            break;
            case 'r':
            case 'u':
                $this->assertTrue($new_case_data['closed_at'], "Case is open status indicates closed");
                $ret= ( $new_case_data['closed_at'] ) ? true: false;
            break;
            default:
                $ret=false;
            break;
        }
        return $ret;
    }
}

/*
 * $Log: cases_test_class.php,v $
 * Revision 1.4  2006/05/02 00:46:54  vanmer
 * - added company_id to contact record for update of weird characters
 * - changed to delete contact from the database after test
 *
 * Revision 1.3  2006/04/28 02:47:59  vanmer
 * - added tests for status on a case matching case closed_by/closed_at fields
 * - update tests to reflect integration of cases API into UI
 *
 * Revision 1.2  2006/04/27 03:23:11  vanmer
 * - updated cases tests to use auto-generated types/statuses from types/statuses API
 *
 * Revision 1.1  2006/04/26 00:55:30  vanmer
 * - added new test classes for cases, statuses and types and integration tests
 *
 *
 */
?>