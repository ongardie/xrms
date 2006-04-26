<?php
/**
 * Test harness for the XRMS types and statuses API
 *
 * Copyright (c) 2006 Explorer Fund Advisors, LLC
 * All Rights Reserved.
 *
 * @todo
 * $Id: type_status_class.php,v 1.1 2006/04/26 00:55:30 vanmer Exp $
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

require_once($include_directory.'utils-typestatus.php');

$options['xrms_db_dbtype'] = $xrms_db_dbtype;
$options['xrms_db_server'] = $xrms_db_server;
$options['xrms_db_username'] = $xrms_db_username;
$options['xrms_db_password'] = $xrms_db_password;
$options['xrms_db_dbname'] = $xrms_db_dbname;

Class XRMSTypeStatusTest extends XRMS_TestCase {


    function test_add_entity_type($entity_type='case', $entity_type_short_name='TESTCASETYPE', $entity_type_pretty_name='TESTCASE TYPE: IGNORE', $entity_type_pretty_plural='TESTCASE TYPES: IGNORE', $entity_type_display_html=false) {
    
        $ret=add_entity_type($this->con, $entity_type, $entity_type_short_name, $entity_type_pretty_name, $entity_type_pretty_plural, $entity_type_display_html);
    
        $this->assertTrue($ret, "Failed to add entity type for type $entity_type short name $entity_type_short_name pretty $entity_type_pretty_name plural $entity_type_pretty_plural display ".htmlspecialchars($entity_type_display_html));
    
        return $ret;
    }
    
    function test_add_entity_status($entity_type='case', $entity_type_id=NULL, $entity_status_short_name='TESTCASESTATUS', $entity_status_pretty_name='TESTCASE STATUS: IGNORE', $entity_status_pretty_plural='TESTCASE STATUSES: IGNORE', $entity_status_display_html=false, $entity_status_long_desc="THIS STATUS WAS ADDED BY THE PHPUnit TEST SYSTEM, SHOULD BE IGNORED, AND SHOULD HAVE BEEN AUTOMATICALLY DELETED.", $sort_order=1, $status_open_indicator='o') {
    
        if ($entity_type_id===NULL) {
            $entity_type_id=$this->test_get_entity_type();
        }

        $ret=add_entity_status($this->con, $entity_type, $entity_type_id, $entity_status_short_name, $entity_status_pretty_name, $entity_status_pretty_plural, $entity_status_display_html, $entity_status_long_desc, $sort_order, $status_open_indicator);
        
        $this->assertTrue($ret, "Failed to add entity status for type $entity_type, type_id $entity_type_id, short $entity_status_short_name,  pretty $entity_status_pretty_name, plurar $entity_status_pretty_plural, display " . htmlspecialchars($entity_status_display_html).", desc $entity_status_long_desc, sort $sort_order,  open $status_open_indicator");
    
        return $ret;
    }
    
    function test_find_entity_type($entity_type='case', $entity_type_short_name='TESTCASETYPE', $entity_type_pretty_name=false, $success=true) {
    
        $ret=find_entity_type($this->con, $entity_type, $entity_type_short_name, $entity_type_pretty_name);
    
        if ($success)
            $this->assertTrue($ret, "Failed to find entity matching type $entity_type, short $entity_type_short_name, pretty $entity_type_pretty_name");
        else
            $this->assertTrue(!$ret, "Found entity when intending to fail matching type $entity_type, short $entity_type_short_name, pretty $entity_type_pretty_name");
    
        return $ret;
    }
    
    function test_find_entity_status($entity_type='case', $entity_type_id=false, $entity_status_short_name='TESTCASESTATUS', $entity_status_pretty_name=false, $entity_status_long_desc=false, $sort_order=false, $success=true) {
    
        $ret=find_entity_status($this->con, $entity_type, $entity_type_id, $entity_status_short_name, $entity_status_pretty_name, $entity_status_desc, $sort_order);
    
        if ($success)
            $this->assertTrue($ret, "Failed to find entity matching type $entity_type, type_id $entity_type_id, short $entity_status_short_name,  pretty $entity_status_pretty_name, desc $entity_status_long_desc, sort $sort_order");
        else
            $this->assertTrue(!$ret, "Found entity when intending to fail matching type $entity_type, short $entity_type_short_name, pretty $entity_type_pretty_name, desc $entity_status_long_desc, sort $sort_order");
    
        return $ret;
    }
    
    function test_get_entity_type($entity_type='case', $entity_type_id=false, $entity_type_short_name='TESTCASETYPE') {
    
        $ret=get_entity_type($this->con, $entity_type, $entity_type_id, $entity_type_short_name);
    
        $this->assertTrue($ret, "Failed to get entity type for type $entity_type, type_id $entity_type_id, short $entity_type_short_name");
    
        return $ret;
    
    }
    
    function test_get_entity_status($entity_type='case', $entity_status_id=false, $entity_type_id=false, $entity_status_short_name='TESTCASESTATUS') {
        $ret=get_entity_status($this->con, $entity_type, $entity_status_id, $entity_type_id, $entity_status_short_name);
    
        $this->assertTrue($ret, "Failed to get status with type $entity_type, status_id $entity_status_id, type_id $entity_type_id, short $entity_status_short_name");
    
        return $ret;
    }
    
    function test_delete_entity_type($entity_type='case', $entity_type_id=NULL, $delete_from_database=true) {
        if ($entity_type_id===NULL) $entity_type_id=$this->test_get_entity_type();
    
        $ret=delete_entity_type($this->con, $entity_type, $entity_type_id, $delete_from_database);
    
        $this->assertTrue($ret, "Failed to delete entity type for type $entity_type ID $entity_type_id from_db flag: $delete_from-database");
    
        return $ret;
    }
    
    function test_delete_entity_status($entity_type='case', $entity_status_id=NULL, $delete_from_database=true) {
        if ($entity_status_id===NULL) $entity_status_id=$this->test_get_entity_status();
    
        $ret=delete_entity_status($this->con, $entity_type, $entity_status_id, $delete_from_database);
    
        $this->assertTrue($ret, "Failed to delete entity status for type $entity_type ID $entity_status_id from_db flag: $delete_from-database");
    
        return $ret;
    }
}
?>