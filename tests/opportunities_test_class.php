<?php
/**
 * Test harness for the XRMS opportunity API
 *
 * Copyright (c) 2004 Explorer Fund Advisors, LLC
 * All Rights Reserved.
 *
 * @todo
 * $Id: opportunities_test_class.php,v 1.2 2006/04/29 01:52:05 vanmer Exp $
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

require_once($include_directory.'utils-opportunities.php');

$options['xrms_db_dbtype'] = $xrms_db_dbtype;
$options['xrms_db_server'] = $xrms_db_server;
$options['xrms_db_username'] = $xrms_db_username;
$options['xrms_db_password'] = $xrms_db_password;
$options['xrms_db_dbname'] = $xrms_db_dbname;


global $test_types;
global $test_statuses;

Class XRMSOpportunityTest extends XRMS_TestCase {

    function XRMSOpportunityTest( $name = "XRMSOpportunityTest" ) {
        $this->PHPUnit_TestCase( $name );
        $this->type_status_test = new XRMSTypeStatusTest();
        $this->type_status_test->_result =& $this->_result;
        $this->type_status_test->setUp();
        $this->entity_type='opportunity';
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

    function _XRMSOpportunityTest() {
        $this->type_status_test->test_delete_entity_type($this->entity_type, $this->test_type_id);
        $this->type_status_test->test_delete_entity_status($this->entity_type, $this->test_status_id);
        $this->type_status_test->teardown();
    }

   function setUp() {
        parent::setUp();
       $this->session_user_id= session_check();

        $this->test_opportunity_data= array(
                   'opportunity_title'   => 'Test Suite Opportunity: Ignore',
                   'opportunity_description' =>'This opportunity was added automatically by the test suite.  It should not be visible, and can safely be ignored',
                    'close_at' => '2006-04-20 12:01:01',
                    'opportunity_type_id'=>$this->test_type_id,
                    'opportunity_status_id'=>$this->test_status_id,
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

   function test_add_opportunity($opportunity_data=false) {
        $con = $this->con;
        global $session_user_id;
        $session_user_id=$this->session_user_id;
        if (!$opportunity_data) {
            $opportunity_data=$this->test_opportunity_data;
        }
        $opportunity_result=add_opportunity($con, $opportunity_data);
        $this->assertTrue($opportunity_result, "Failed to add opportunity: {$opportunity_data['title']}");
        $ret=$this->test_opportunity_status_consistency($opportunity_result, $opportunity_data['opportunity_status_id']);
        $this->assertTrue($ret, "Failed test_opportunity_status_consistency check, should succeed");
        return $opportunity_result;
   }
   
    function test_find_opportunity($opportunity_data=false, $show_deleted=false, $return_recordset=false) {
        $con = $this->con;
        if (!$opportunity_data) {
            $opportunity_data=$this->test_opportunity_data;
        }
        $opportunity_result=find_opportunity($con, $opportunity_data, $show_deleted, $return_recordset);
        $this->assertTrue($opportunity_result, "Failed to get information about opportunity");
        if (!$return_recordset) {
            $this->assertTrue(is_array($opportunity_result),"Opportunity info is not an array, should be");
            if (is_array($opportunity_result)) {
                $this->assertTrue(is_array(current($opportunity_result)), "Individual opportunity is not array, should be");
            }
        } else {
            $this->assertTrue(is_object($opportunity_result), "Failed to match intended return of find_opportunity to an object");
        }
        return $opportunity_result;
    }
    
    function test_get_opportunity($opportunity_id=false, $return_rst=false) {
        $con = $this->con;
        $opportunity_result=get_opportunity($con, $opportunity_id, $return_rst);
        if ($opportunity_id) {
            $this->assertTrue($opportunity_result, "Failed to get information about opportunity");
            if (!$return_rst) {
                $this->assertTrue(is_array($opportunity_result),"Opportunity info is not an array, should be");
            } else {
                $this->assertTrue(is_object($opportunity_result),"Opportunity info is not an object, should be");
            }
        } else { $this->assertTrue($opportunity_result==false, "Expected to fail retreiving opportunity, instead found a opportunity"); }
        return $opportunity_result;
    }

    function test_update_opportunity($opportunity_data=NULL, $opportunity_id=false, $opportunity_rst=false) {
        $con = $this->con;
	//if no opportunity id or recordset is provided, use test opportunity data	
	if (!$opportunity_id AND !$opportunity_rst) {
		$opportunity=$this->test_find_opportunity();
                if (!$opportunity) { $this->fail("No opportunity found to update using find_opportunity function, failing further tests"); return false; }
                $opportunity=current($opportunity);
		$opportunity_id=$opportunity['opportunity_id'];
		$this->assertTrue($opportunity_id, "Failed to identify opportunity for update.  Skipping further tests.");
                if (!$opportunity_id) return false;		
	}
	//if no opportunity data is provided, retrieve it
	if ($opportunity_data===NULL) {
                $opportunity_data=$this->test_get_opportunity($opportunity_id);
		$opportunity_data['opportunity_description'].=' Changed For Test';
                $opportunity_data['close_at']='2006-04-21 00:00:00';
	}
	$result = update_opportunity($con, $opportunity_data, $opportunity_id, $opportunity_rst);
	$this->assertTrue($result, "Update to opportunity $opportunity_id recordset $opportunity_rst failed.");
	$new_opportunity_data['opportunity_id']=$opportunity_id;
	$new_opportunity=$this->test_get_opportunity($opportunity_id);
	$this->assertTrue($new_opportunity, "Failed to get updated opportunity");
	if ($new_opportunity) {
		$new_opportunity_data=$new_opportunity;
		foreach ($opportunity_data as $ckey=>$cval) {
            switch ($ckey) {
                case 'last_modified_at':
                    //don't compare this one
                break;
                default: 
        			$this->assertTrue($new_opportunity_data[$ckey]==$cval, "Update error: $ckey values do not match: {$new_opportunity_data[$ckey]}!=$cval");
                break;
            }
		}
	}
	return $result;
   }
    
    function test_delete_opportunity($opportunity_id=false, $delete_from_database=true) {
        $con = $this->con;
        $session_user_id=$this->session_user_id;
        if (!$opportunity_id) {
            $opportunity_data=$this->test_opportunity_data;
	    //removed because update resets this variable
            unset($opportunity_data['opportunity_description']);
            unset($opportunity_data['close_at']);
            $opportunity_info=$this->test_find_opportunity($opportunity_data);
            $this->assertTrue($opportunity_info,"Failed to look up test opportunity to delete.");
            if ($opportunity_info) {
                $opportunity=current($opportunity_info);
                $opportunity_id=$opportunity['opportunity_id'];
            } else return false;
        }
        $this->assertTrue($opportunity_id, "No opportunity_id available, cannot delete opportunity");
        $opportunity_result=delete_opportunity($con, $opportunity_id, $delete_from_database);
        $this->assertTrue($opportunity_result, "Failed to delete opportunity $opportunity_id from database");
        return $opportunity_result;
    }

    function test_opportunity_strange_characters($opportunity_data=NULL, $delete_from_database=true) {
        if ($opportunity_data===NULL) $opportunity_data= array(
                   'opportunity_title'   => 'Test Suite Opportunity O\'doole & Tomlin: Ignore',
                   'opportunity_description' =>'This opportunity was added automatically by the test suite.  It shouldn\'t be visible, and can safely be ignored',
                    'close_at' => '1987-12-30 00:00:00',
                    'opportunity_type_id'=>$this->test_type_id,
                    'opportunity_status_id'=>$this->test_status_id
            );

        $test_opportunity_id=$this->test_add_opportunity($opportunity_data);
        $this->assertTrue($test_opportunity_id, "Failed to add a new opportunity with strange characters, failing other tests");
        if ($test_opportunity_id) {
            $ret=$this->test_find_opportunity($opportunity_data);
            $this->assertTrue($ret, "Failed to find opportunity with strange data");
        } else return false;
        
        $opportunity_data['opportunity_description']="This opportunity was added automatically, and can't be seen because it should've been deleted by &";
        $ret=$this->test_update_opportunity($opportunity_data, $test_opportunity_id);
        $this->assertTrue($ret, "Failed to update opportunity with strange data");
        $this->test_delete_opportunity($test_opportunity_id, $delete_from_database);
    }

    function test_opportunity_change_status($opportunity_data=NULL, $new_status=NULL) {
        if ($opportunity_data===NULL) {
            $opportunity_data=$this->test_opportunity_data;
        }

        $opportunity_id=$this->test_add_opportunity($opportunity_data);

        if (!$opportunity_id)  {
            $this->fail("Failed to add opportunity for status change test, skipping further tests.");
            return false;
        }

        if ($new_status===NULL) {
            $this->type_status_test->_result =& $this->_result;
            $new_status=$this->type_status_test->test_add_entity_status_closed_won($this->entity_type);
            $delete_new_status=true;
        } else $delete_new_status=false;

        $opportunity_data["{$this->entity_type}_status_id"]=$new_status;
        $ret=$this->test_update_opportunity($opportunity_data, $opportunity_id);
        if (!$ret) {
            $this->fail("Failed to update opportunity for status change test, skipping further tests");
        } else {
            $ret=$this->test_opportunity_status_consistency($opportunity_id, $new_status);
            $this->assertTrue($ret, "Failed test_opportunity_status_consistency check, should succeed");
        }

        if ($delete_new_status) {
            $this->type_status_test->test_delete_entity_status($this->entity_type, $new_status);
        }

        $this->test_delete_opportunity($opportunity_id);

    }


    function test_opportunity_change_status_lost_to_open($opportunity_data=NULL) {
        if ($opportunity_data===NULL) {
            $opportunity_data=$this->test_opportunity_data;
            $new_status=$opportunity_data['opportunity_status_id'];
            $ustatus=$this->type_status_test->test_add_entity_status_closed_lost($this->entity_type);
            $delete_ustatus=true;
            $opportunity_data['opportunity_status_id']=$ustatus;
        } else $delete_ustatus=false;

        $ret= $this->test_opportunity_change_status($opportunity_data, $new_status);
        if ($delete_ustatus) {
            $this->type_status_test->test_delete_entity_status($this->entity_type, $ustatus);
        }
        return $ret;
    }

    function test_opportunity_status_consistency($opportunity_id=false, $new_status=false) {
        if ((!$opportunity_id) OR (!$new_status)) return false;
        $new_status_data=$this->type_status_test->test_get_entity_status($this->entity_type, $new_status);
        $open_indicator=$new_status_data['status_open_indicator'];

        $new_opportunity_data=$this->test_get_opportunity($opportunity_id);
        switch ($open_indicator) {
            case 'o':
                $this->assertTrue(!$new_opportunity_data['closed_at'], "Opportunity is closed while status indicates open.");
                $ret=!$new_opportunity_data['closed_at'];
            break;
            case 'w':
            case 'l':
                $this->assertTrue($new_opportunity_data['closed_at'], "Opportunity is open status indicates closed");
                $ret= ( $new_opportunity_data['closed_at'] ) ? true: false;
            break;
            default:
                $ret=false;
            break;
        }
        return $ret;
    }
}

/*
 * $Log: opportunities_test_class.php,v $
 * Revision 1.2  2006/04/29 01:52:05  vanmer
 * - added tests for statuses for opportunities to reflect won/lost closed code
 * - updated opportunities test to use appropriate won/lost tests from statuses tests
 * - updated main test class to include proper file for workflow tests
 *
 * Revision 1.1  2006/04/28 04:29:37  vanmer
 * - Initial revision of the opportunities API and complete test suite
 * - still TODO is to update the PHPDoc for opportunities (still refects cases origins)
 *
 *
 */
?>