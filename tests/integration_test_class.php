<?php
/**
 * Test harness for XRMS, integrated tests for XRMS entities (companies, contacts, addresses)
 *
 * Copyright (c) 2004 Explorer Fund Advisors, LLC
 * All Rights Reserved.
 *
 * @todo
 * $Id: integration_test_class.php,v 1.1 2006/04/26 00:55:30 vanmer Exp $
 */

require_once('../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'utils-preferences.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

require_once("PHPUnit.php");
require_once("PHPUnit/GUI/HTML.php");

require_once($include_directory.'utils-companies.php');
require_once($include_directory.'utils-contacts.php');

$options['xrms_db_dbtype'] = $xrms_db_dbtype;
$options['xrms_db_server'] = $xrms_db_server;
$options['xrms_db_username'] = $xrms_db_username;
$options['xrms_db_password'] = $xrms_db_password;
$options['xrms_db_dbname'] = $xrms_db_dbname;

Class XRMSIntegrationTest extends XRMS_TestCase {
    
    function XRMSIntegrationTest( $name = "XRMSIntegrationTest" ) {
        $this->PHPUnit_TestCase( $name );
    }
   function setUp() {
        parent::setUp();
       $this->session_user_id= session_check();
        $this->companies_test = new XRMSCompanyTest();
        $this->companies_test->_result =& $this->_result;
        $this->companies_test->setUp();

        $this->companies_to_delete=array();

        $this->contacts_to_delete=array();
        $this->contacts_test = new XRMSContactTest();
        $this->contacts_test->_result =& $this->_result;
        $this->contacts_test->setUp();

    }

   function teardown() {
        foreach ($this->companies_to_delete as $cid) {
            $this->companies_test->test_delete_company($cid);
        }
        foreach ($this->contacts_to_delete as $cid) {
            $this->contacts_test->test_delete_contact($cid);
        }

        $this->companies_test->teardown();
        $this->contacts_test->teardown();

        parent::teardown();
    }

    function test_add_multiple_contacts_similar_fields($contact1=false, $contact2=false) {

        if (!$contact1) {
            $contact1=$this->create_contact_array_with_new_company(1);
            if (!$contact1) { $this->fail("No contact record created, failing further tests"); return false; }
        }

        $ret1=$this->contacts_test->test_add_contact($contact1);
        if (!$ret1) {
             $this->fail("Failed to add first contact for multiple contacts test, failing further tests");
             return false;
        }
        $this->contacts_to_delete[]=$ret1;

        if (!$contact2) {
            $contact2=$this->create_contact_array_with_new_company(2);
            if (!$contact2) { $this->fail("No contact record created, failing further tests"); return false; }
        }

        $ret2=$this->contacts_test->test_add_contact($contact2);
        if (!$ret2) {
             $this->fail("Failed to add first contact for multiple contacts test, failing further tests");
             return false;
        }
        $this->contacts_to_delete[]=$ret2;

        $this->assertTrue($ret2!=$ret1, "Contacts have the same ID, should be different");

        return array($ret1, $ret2);

    }

    function test_add_multiple_contacts_same_company_different_email($contact1=false, $contact2=false) {
        if (!$contact1) {
            $contact1=$this->create_contact_array_with_new_company(1);
            if (!$contact1) { $this->fail("No contact record created, failing further tests"); return false; }
        }
        $contact2=$contact1;
        $contact1['email']='testuser1@test.com';
        $contact2['email']='testuser2@test.com';
        return $this->test_add_multiple_contacts_similar_fields($contact1, $contact2);
    }
    
    function create_contact_array_with_new_company($test_iterator=0) {
        $company_record=$this->companies_test->test_company_data;
        $contact_record=$this->contacts_test->test_contact_data;

        if ($test_iterator) $company_record['company_name'].=$test_iterator;
        $ret=$this->companies_test->test_add_company($company_record);

        if ($ret) { $this->companies_to_delete[]=$ret; }
        else { $this->fail("Failed to create company for new contact array");return false; }

        $contact_record['company_id']=$ret;

        return $contact_record;
    }
}

/*
 * $Log: integration_test_class.php,v $
 * Revision 1.1  2006/04/26 00:55:30  vanmer
 * - added new test classes for cases, statuses and types and integration tests
 *
 *
 */
?>