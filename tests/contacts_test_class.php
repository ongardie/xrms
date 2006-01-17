<?php
/**
 * Test harness for the XRMS contact API
 *
 * Copyright (c) 2004 Explorer Fund Advisors, LLC
 * All Rights Reserved.
 *
 * @todo
 * $Id: contacts_test_class.php,v 1.2 2006/01/17 02:26:56 vanmer Exp $
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

require_once($include_directory.'utils-contacts.php');

$options['xrms_db_dbtype'] = $xrms_db_dbtype;
$options['xrms_db_server'] = $xrms_db_server;
$options['xrms_db_username'] = $xrms_db_username;
$options['xrms_db_password'] = $xrms_db_password;
$options['xrms_db_dbname'] = $xrms_db_dbname;

Class XRMSContactTest extends XRMS_TestCase { 
    
    function XRMSContactTest( $name = "XRMSContactTest" ) {
        $this->PHPUnit_TestCase( $name );
    }
   function setUp() {   
        parent::setUp();
       $this->session_user_id= session_check();
        $this->test_contact_data= array(
                   'first_names' => 'Joe',
                   'last_name'    => 'tester',
                   'company_id'       => 1,
                   'title'   => 'Test Suite Contact: Ignore',
                   'profile' =>'This contact was added automatically by the test suite.  It should not be visible, and can safely be ignored',
            );


    }

   function teardown() {
        parent::teardown();
    }
    function test_XRMSTEST() {
	$this->assertTrue(true, "This should never fail.");
    }

   function test_add_contact($contact_data=false) {
        $con = $this->con;
        global $session_user_id;
        $session_user_id=$this->session_user_id;        
        if (!$contact_data) {
            $contact_data=$this->test_contact_data;
        }
        $contact_result=add_contact($con, $contact_data);
        $this->assertTrue($contact_result, "Failed to add contact: {$contact_data['title']}");

        return $contact_result;
   }
   
    function test_find_contact($contact_data=false, $show_deleted=false, $return_recordset=false) {
        $con = $this->con;
        if (!$contact_data) {
            $contact_data=$this->test_contact_data;
        }
        $contact_result=find_contact($con, $contact_data, $show_deleted, $return_recordset);
        $this->assertTrue($contact_result, "Failed to get information about contact");
        if (!$return_recordset) {
            $this->assertTrue(is_array($contact_result),"Contact info is not an array, should be");
            if (is_array($contact_result)) {
                $this->assertTrue(is_array(current($contact_result)), "Individual contact is not array, should be");
            }
        } else {
            $this->assertTrue(is_object($contact_result), "Failed to match intended return of find_contact to an object");
        }
        return $contact_result;
    }
    
    function test_get_contact($contact_id=false, $return_rst=false) {
        $con = $this->con;
        if (!$contact_data) {
            $contact_data=$this->test_contact_data;
        }
        $contact_result=get_contact($con, $contact_id, $return_rst);
        if ($contact_id) {
            $this->assertTrue($contact_result, "Failed to get information about contact");
            $this->assertTrue(is_array($contact_result),"Contact info is not an array, should be");
        } else { $this->assertTrue($contact_result==false, "Expected to fail retreiving contact, instead found a contact"); }
        return $contact_result;
    }

    function test_update_contact($contact_data=false, $contact_id=false, $contact_rst=false) {
        $con = $this->con;
	//if no contact id or recordset is provided, use test contact data	
	if (!$contact_id AND !$contact_rst) {
		$contact=$this->test_find_contact();
        $contact=current($contact);
		$contact_id=$contact['contact_id'];
		$this->assertTrue($contact_id, "Failed to identify contact for update");		
	}
	//if no contact data is provided, create test data
	if (!$contact_data) {
		$contact_data['title'].=' Changed For Test';
                $contact_data['tax_id']='123-45-6789';
	}
	$result = update_contact($con, $contact_data, $contact_id, $contact_rst);
	$this->assertTrue($result, "Update to contact $contact_id recordset $contact_rst failed.");
	$new_contact_data['contact_id']=$contact_id;
	$new_contact=$this->test_get_contact($contact_id);
	$this->assertTrue($new_contact, "Failed to get updated contact");
	if ($new_contact) {
		$new_contact_data=$new_contact;
		foreach ($contact_data as $ckey=>$cval) {
			$this->assertTrue($new_contact_data[$ckey]==$cval, "Update error: $ckey values do not match: {$new_contact_data[$ckey]}!=$cval");
		}
	}
	return $result;
   }
    
    function test_delete_contact($contact_id=false, $delete_from_database=true) {
        $con = $this->con;
        $session_user_id=$this->session_user_id;
        if (!$contact_id) {
            $contact_data=$this->test_contact_data;
	    //removed because update resets this variable
	    unset($contact_data['title']);
	    unset($contact_data['tax_id']);
            $contact_info=$this->test_find_contact($contact_data);
            $this->assertTrue($contact_info,"Failed to look up test contact to delete.");
            if ($contact_info) {
                $contact=current($contact_info);
                $contact_id=$contact['contact_id'];
            } else return false;
        }
        $this->assertTrue($contact_id, "No contact_id available, cannot delete contact");
        $contact_result=delete_contact($con, $contact_id, $delete_from_database);
        $this->assertTrue($contact_result, "Failed to delete contact $contact_id from database");
        return $contact_result;
    }

    function test_contact_strange_characters($contact_data=false, $delete_from_database=true) {
        if (!$contact_data) $contact_data=array(
                   'first_names' => 'Joe',
                   'last_name'    => "O'tester",
                   'company_id'       => 1,
                   'title'   => 'Test &Suite Contact: Ignore',
                   'profile' =>'This contact was added automatically by the test suite.  It should not be visible, and can safely be ignored',
            );
        $test_contact_id=$this->test_add_contact($contact_data);
        $this->assertTrue($test_contact_id, "Failed to add a new contact with strange characters, failing other tests");
        if ($test_contact_id) {
            $ret=$this->test_find_contact($contact_data);
            $this->assertTrue($ret, "Failed to find contact with strange data");
        } else return false;
        
        $contact_data['profile']="This contact with added automatically, and can't be seen because it should've been deleted by &";
        $ret=$this->test_update_contact($contact_data, $test_contact_id);
        $this->assertTrue($ret, "Failed to update contact with strange data");
        $this->test_delete_contact($test_contact_id, $delete_from_database);
    }
}
?>