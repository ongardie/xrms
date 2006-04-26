<?php
/**
 * Test harness for the XRMS company API
 *
 * Copyright (c) 2004 Explorer Fund Advisors, LLC
 * All Rights Reserved.
 *
 * @todo
 * $Id: companies_test_class.php,v 1.4 2006/04/26 00:56:08 vanmer Exp $
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

require_once($include_directory.'utils-companies.php');

$options['xrms_db_dbtype'] = $xrms_db_dbtype;
$options['xrms_db_server'] = $xrms_db_server;
$options['xrms_db_username'] = $xrms_db_username;
$options['xrms_db_password'] = $xrms_db_password;
$options['xrms_db_dbname'] = $xrms_db_dbname;

Class XRMSCompanyTest extends XRMS_TestCase {
    
    function XRMSCompanyTest( $name = "XRMSCompanyTest" ) {
        $this->PHPUnit_TestCase( $name );
    }
   function setUp() {
        parent::setUp();
       $this->session_user_id= session_check();
        $this->test_company_data= array(
                   'company_name'   => 'Test Suite Company: Ignore',
                   'profile' =>'This company was added automatically by the test suite.  It should not be visible, and can safely be ignored',
                    'tax_id' => '0987-12-2033'
            );


    }

   function teardown() {
        parent::teardown();
    }
    function test_XRMSTEST() {
	$this->assertTrue(true, "This should never fail.");
    }

   function test_add_company($company_data=false) {
        $con = $this->con;
        global $session_user_id;
        $session_user_id=$this->session_user_id;
        if (!$company_data) {
            $company_data=$this->test_company_data;
        }
        $company_result=add_company($con, $company_data);
        $this->assertTrue($company_result, "Failed to add company: {$company_data['company_name']}");
        return $company_result;
   }
   
    function test_find_company($company_data=false, $show_deleted=false, $return_recordset=false) {
        $con = $this->con;
        if (!$company_data) {
            $company_data=$this->test_company_data;
        }
        $company_result=find_company($con, $company_data, $show_deleted, $return_recordset);
        $this->assertTrue($company_result, "Failed to get information about company");
        if (!$return_recordset) {
            $this->assertTrue(is_array($company_result),"Company info is not an array, should be");
            if (is_array($company_result)) {
                $this->assertTrue(is_array(current($company_result)), "Individual company is not array, should be");
            }
        } else {
            $this->assertTrue(is_object($company_result), "Failed to match intended return of find_company to an object");
        }
        return $company_result;
    }
    
    function test_get_company($company_id=false, $return_rst=false) {
        $con = $this->con;
        if (!$company_data) {
            $company_data=$this->test_company_data;
        }
        $company_result=get_company($con, $company_id, $return_rst);
        if ($company_id) {
            $this->assertTrue($company_result, "Failed to get information about company");
            $this->assertTrue(is_array($company_result),"Company info is not an array, should be");
        } else { $this->assertTrue($company_result==false, "Expected to fail retreiving company, instead found a company"); }
        return $company_result;
    }

    function test_update_company($company_data=false, $company_id=false, $company_rst=false) {
        $con = $this->con;
	//if no company id or recordset is provided, use test company data	
	if (!$company_id AND !$company_rst) {
		$company=$this->test_find_company();
        $company=current($company);
		$company_id=$company['company_id'];
		$this->assertTrue($company_id, "Failed to identify company for update");		
	}
	//if no company data is provided, create test data
	if (!$company_data) {
		$company_data['profile'].=' Changed For Test';
                $company_data['tax_id']='123-45-6789';
	}
	$result = update_company($con, $company_data, $company_id, $company_rst);
	$this->assertTrue($result, "Update to company $company_id recordset $company_rst failed.");
	$new_company_data['company_id']=$company_id;
	$new_company=$this->test_get_company($company_id);
	$this->assertTrue($new_company, "Failed to get updated company");
	if ($new_company) {
		$new_company_data=$new_company;
		foreach ($company_data as $ckey=>$cval) {
			$this->assertTrue($new_company_data[$ckey]==$cval, "Update error: $ckey values do not match: {$new_company_data[$ckey]}!=$cval");
		}
	}
	return $result;
   }
    
    function test_delete_company($company_id=false, $delete_from_database=true) {
        $con = $this->con;
        $session_user_id=$this->session_user_id;
        if (!$company_id) {
            $company_data=$this->test_company_data;
	    //removed because update resets this variable
	    unset($company_data['profile']);
	    unset($company_data['tax_id']);
            $company_info=$this->test_find_company($company_data);
            $this->assertTrue($company_info,"Failed to look up test company to delete.");
            if ($company_info) {
                $company=current($company_info);
                $company_id=$company['company_id'];
            } else return false;
        }
        $this->assertTrue($company_id, "No company_id available, cannot delete company");
        $company_result=delete_company($con, $company_id, $delete_from_database);
        $this->assertTrue($company_result, "Failed to delete company $company_id from database");
        return $company_result;
    }

    
    function test_update_unknown_company($test_company_id=1) {
        $ret=update_unknown_company($this->con, $test_company_id);

        //two success conditions, '', and result string
        $ret_test=($ret=='');

        if (!$ret_test) $ret_test=($ret==_("Upgraded company entry for Unknown Company.  New contacts will be attached here if no company is specified.") . '<br>');
        $this->assertTrue($ret_test, "Failed to update unknown company into position $test_company_id");
        return $ret_test;
    }
    function test_update_unknown_company_extended($old_company_id=false) {
        if (!$old_company_id) {
            $old_company_id=$this->test_add_company();
            $company_data=$this->test_get_company($old_company_id);
            $created_company=true;
        }
        $ret=$this->test_update_unknown_company($old_company_id);

        if ($ret) {
            $old_company_data=$this->test_get_company($old_company_id);
            if ($old_company_data['company_name']=='Unknown Company') {
                $delete_old_company=true;
            } else { $this->fail("Failed to move company at $old_company_id and replace with unknown company: {$old_company_data['company_name']}"); $delete_old_company=false; }
            unset($company_data['company_id']);
            foreach ($company_data AS $ckey=>$cval) {
                if (!$cval) unset($company_data[$ckey]);
            }
            $ret=$this->test_find_company($company_data);
            if ($ret) {
                if (is_array(current($ret))) { $ret=current($ret); }
                $new_company_id=$ret['company_id'];
                //delete unknown company record
            } else $this->fail("Failed to find company after update with unknown company record");
            if ($delete_old_company) $this->test_delete_company($old_company_id);
        } else { $this->fail("Failed to update company $old_company_id with unknown company record, skipping further tests");  $new_company_id=false; }

        if ($created_company AND $new_company_id) {
            $this->test_delete_company($new_company_id);
        }

         return $new_company_id;
    }
    function test_change_company_key($old_company_id=false, $new_company_id=false, $company_rst=false) {
        if (!$old_company_id) {
            $old_company_id=$this->test_add_company();
            $created_company=true;
        }
        if (!$old_company_id) { $this->fail("No old company available for change company key test, failing"); return false; }

        $ret=change_company_key($this->con, $old_company_id, $new_company_id, $company_rst);

        $this->assertTrue($ret, "Company key failed to be change correctly");
        if ($created_company) {
            $this->test_delete_company($new_company_id);
        }
        return $ret;
     }

    function test_change_company_key_extended($old_company_id=false, $new_company_id=false, $company_rst=false) {

        //to import trades we need: an investible entity, so add one with company 1, division 2, do not delete after test, do cascade creation
        if (!$old_company_id) {
                $old_company_id=$this->test_add_company();
                $created_company=true;
        } else $created_company=false;
        $contact_test = new XRMSContactTest();
        $contact_test->_result =& $this->_result;
        $contact_test->setUp();
        $contact_data=$contact_test->test_contact_data;
        $contact_data['company_id']=$old_company_id;
        $new_contact=$contact_test->test_add_contact($contact_data);
        if (is_array($new_contact)) $new_contact=$new_contact['contact_id'];


        $activity_test=new XRMSActivityTest();
        $activity_test->_result =& $this->_result;
        $activity_test->setUp();
        $activity_data=$activity_test->test_activity_data;
        $activity_data['company_id']=$old_company_id;
        $new_activity=$activity_test->test_add_activity($activity_data);
        if (is_array($new_activity)) $new_activity=$new_activity['activity_id'];


        $ret=$this->test_change_company_key($old_company_id, $new_company_id, $company_rst);

        $this->assertTrue($ret, "Failed to change company key for extended test");
        $new_company_id=$ret;
        
        $contact = $contact_test->test_get_contact($new_contact);
        $this->assertTrue($contact['company_id']==$new_company_id, "Contact company id should be $new_company_id, is {$contact['company_id']}");
        $contact_test->test_delete_contact($new_contact);

        $activity_data=array();
        $activity_data['activity_id']=$new_activity;
        $activity = $activity_test->test_get_activity($activity_data);
        if ($activity) {
            $activity=current($activity);
            $this->assertTrue($activity['company_id']==$new_company_id, "Activity company id should be $new_company_id, is {$activity['company_id']}");
            $activity_test->test_delete_activity($new_activity);
        } else $this->fail("Failed to find activity to test for company key change");

        if ($created_company) {
            $this->test_delete_company($new_company_id);
        }

        return $ret;

    }

    function test_company_strange_characters($company_data=false, $delete_from_database=true) {
        if (!$company_data) $company_data= array(
                   'company_name'   => 'Test Suite Company O\'doole & Tomlin: Ignore',
                   'profile' =>'This company was added automatically by the test suite.  It shouldn\'t be visible, and can safely be ignored',
                    'tax_id' => '0987-12-2033'
            );

        $test_company_id=$this->test_add_company($company_data);
        $this->assertTrue($test_company_id, "Failed to add a new company with strange characters, failing other tests");
        if ($test_company_id) {
            $ret=$this->test_find_company($company_data);
            $this->assertTrue($ret, "Failed to find company with strange data");
        } else return false;
        
        $company_data['profile']="This company with added automatically, and can't be seen because it should've been deleted by &";
        $ret=$this->test_update_company($company_data, $test_company_id);
        $this->assertTrue($ret, "Failed to update company with strange data");
        $this->test_delete_company($test_company_id, $delete_from_database);
    }

}

/*
 * $Log: companies_test_class.php,v $
 * Revision 1.4  2006/04/26 00:56:08  vanmer
 * - added proper array key for companies when add fails
 *
 * Revision 1.3  2006/04/05 00:46:08  vanmer
 * - added test for strange characters in companies API
 *
 * Revision 1.2  2006/01/17 03:13:08  vanmer
 * - added extended test for updating an existing company to a new company_id
 * - added extended test for moving a company when adding the unknown company record
 *
 * Revision 1.1  2006/01/17 02:25:32  vanmer
 * - initial revision of a companies test class
 * - used to test company API
 *
 */
?>