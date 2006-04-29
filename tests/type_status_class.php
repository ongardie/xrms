<?php
/**
 * Test harness for the XRMS types and statuses API
 *
 * Copyright (c) 2006 Explorer Fund Advisors, LLC
 * All Rights Reserved.
 *
 * @todo
 * $Id: type_status_class.php,v 1.6 2006/04/29 01:52:05 vanmer Exp $
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


    function test_add_entity_type($entity_type='case', $entity_type_short_name='TESTTYPE', $entity_type_pretty_name='TESTCASE TYPE: IGNORE', $entity_type_pretty_plural='TESTCASE TYPES: IGNORE', $entity_type_display_html=false) {
    
        $ret=add_entity_type($this->con, $entity_type, $entity_type_short_name, $entity_type_pretty_name, $entity_type_pretty_plural, $entity_type_display_html);
    
        $this->assertTrue($ret, "Failed to add entity type for type $entity_type short name $entity_type_short_name pretty $entity_type_pretty_name plural $entity_type_pretty_plural display ".htmlspecialchars($entity_type_display_html));
    
        return $ret;
    }
    
    function test_add_entity_status($entity_type='case', $entity_type_id=NULL, $entity_status_short_name='TESTSTATUS', $entity_status_pretty_name='TESTCASE STATUS: IGNORE', $entity_status_pretty_plural='TESTCASE STATUSES: IGNORE', $entity_status_display_html=false, $entity_status_long_desc="THIS STATUS WAS ADDED BY THE PHPUnit TEST SYSTEM, SHOULD BE IGNORED, AND SHOULD HAVE BEEN AUTOMATICALLY DELETED.", $sort_order=1, $status_open_indicator='o') {
    
        if ($entity_type_id===NULL) {
            $entity_type_data=$this->test_get_entity_type($entity_type);
            $entity_type_id=$entity_type_data["{$entity_type}_type_id"];
        }

        $ret=add_entity_status($this->con, $entity_type, $entity_type_id, $entity_status_short_name, $entity_status_pretty_name, $entity_status_pretty_plural, $entity_status_display_html, $entity_status_long_desc, $sort_order, $status_open_indicator);
        
        $this->assertTrue($ret, "Failed to add entity status for type $entity_type, type_id $entity_type_id, short $entity_status_short_name,  pretty $entity_status_pretty_name, plurar $entity_status_pretty_plural, display " . htmlspecialchars($entity_status_display_html).", desc $entity_status_long_desc, sort $sort_order,  open $status_open_indicator");
    
        return $ret;
    }

    
    function test_find_entity_type($entity_type='case', $entity_type_short_name='TESTTYPE', $entity_type_pretty_name=false, $show_all=false, $success=true) {
    
        $ret=find_entity_type($this->con, $entity_type, $entity_type_short_name, $entity_type_pretty_name, $show_all);
    
        if ($success)
            $this->assertTrue($ret, "Failed to find entity matching type $entity_type, short $entity_type_short_name, pretty $entity_type_pretty_name showing all: $show_all");
        else
            $this->assertTrue(!$ret, "Found entity when intending to fail matching type $entity_type, short $entity_type_short_name, pretty $entity_type_pretty_name showing all: $show_all");
    
        return $ret;
    }
    
    function test_find_entity_status($entity_type='case', $entity_type_id=NULL, $entity_status_short_name='TESTSTATUS', $entity_status_pretty_name=false, $entity_status_long_desc=false, $sort_order=false, $success=true) {

        if ($entity_type_id===NULL) {
            $entity_type_data=$this->test_get_entity_type($entity_type);
            $entity_type_id=$entity_type_data["{$entity_type}_type_id"];
        }
    
        $ret=find_entity_status($this->con, $entity_type, $entity_type_id, $entity_status_short_name, $entity_status_pretty_name, $entity_status_desc, $sort_order);
    
        if ($success)
            $this->assertTrue($ret, "Failed to find entity matching type $entity_type, type_id $entity_type_id, short $entity_status_short_name,  pretty $entity_status_pretty_name, desc $entity_status_long_desc, sort $sort_order");
        else
            $this->assertTrue(!$ret, "Found entity when intending to fail matching type $entity_type, short $entity_type_short_name, pretty $entity_type_pretty_name, desc $entity_status_long_desc, sort $sort_order");
    
        return $ret;
    }
    
    function test_get_entity_type($entity_type='case', $entity_type_id=false, $entity_type_short_name='TESTTYPE') {
    
        $ret=get_entity_type($this->con, $entity_type, $entity_type_id, $entity_type_short_name);
    
        $this->assertTrue($ret, "Failed to get entity type for type $entity_type, type_id $entity_type_id, short $entity_type_short_name");
    
        return $ret;
    
    }
    
    function test_get_entity_status($entity_type='case', $entity_status_id=false, $entity_type_id=NULL, $entity_status_short_name='TESTSTATUS') {

        if ($entity_type_id===NULL AND $entity_status_id==false) {
            $entity_type_data=$this->test_get_entity_type($entity_type);
            $entity_type_id=$entity_type_data["{$entity_type}_type_id"];
        }

        $ret=get_entity_status($this->con, $entity_type, $entity_status_id, $entity_type_id, $entity_status_short_name);
    
        $this->assertTrue($ret, "Failed to get status with type $entity_type, status_id $entity_status_id, type_id $entity_type_id, short $entity_status_short_name");
    
        return $ret;
    }
    
    function test_update_entity_type($entity_type='case', $entity_type_id=NULL, $entity_type_short_name='TESTTYPE', $entity_type_pretty_name='TESTCASE TYPE:PLEASE IGNORE', $entity_type_pretty_plural='TESTCASE TYPES: PLEASE IGNORE', $entity_type_display_html=false) {

        if ($entity_type_id===NULL) {
            $entity_type_data=$this->test_get_entity_type($entity_type);
            $entity_type_id=$entity_type_data["{$entity_type}_type_id"];
        }
    
        $ret=update_entity_type($this->con, $entity_type, $entity_type_id, $entity_type_short_name, $entity_type_pretty_name, $entity_type_pretty_plural, $entity_type_display_html);
    
        $this->assertTrue($ret, "Failed to update entity type for type $entity_type short name $entity_type_short_name pretty $entity_type_pretty_name plural $entity_type_pretty_plural display ".htmlspecialchars($entity_type_display_html));
    
        $entity_type_data=$this->test_get_entity_type($entity_type, $entity_type_id);
        if (!$entity_type_data) { $this->fail("Failed to get entity type data for update comparison, skipping further tests");  }
        else {
            $this->assertTrue($entity_type_data["{$entity_type}_type_short_name"]==$entity_type_short_name,"Failed to update field type_short_name to $entity_type_short_name: is {$entity_type_data["{$entity_type}_type_short_name"]}");
            $this->assertTrue($entity_type_data["{$entity_type}_type_pretty_name"]==$entity_type_pretty_name,"Failed to update field type_pretty_name to $entity_type_pretty_name: is {$entity_type_data["{$entity_type}_type_pretty_name"]}");
            $this->assertTrue($entity_type_data["{$entity_type}_type_pretty_plural"]==$entity_type_pretty_plural,"Failed to update field type_pretty_plural to $entity_type_pretty_plural: is {$entity_type_data["{$entity_type}_type_pretty_plural"]}");
            $this->assertTrue($entity_type_data["{$entity_type}_type_display_html"]==$entity_type_display_html,"Failed to update field type_display_html to $entity_type_display_html: is {$entity_type_data["{$entity_type}_type_display_html"]}");
        }

        return $ret;
    }
    
    function test_update_entity_status($entity_type='case', $entity_status_id=NULL, $entity_type_id=NULL, $entity_status_short_name='TESTSTATUS', $entity_status_pretty_name='TESTCASE STATUS: PLEASE IGNORE', $entity_status_pretty_plural='TESTCASE STATUSES: PLEASE IGNORE', $entity_status_display_html=false, $entity_status_long_desc="THIS STATUS WAS ADDED BY THE PHPUnit TEST SYSTEM, SHOULD BE IGNORED, AND SHOULD HAVE BEEN AUTOMATICALLY DELETED ALREADY.", $sort_order=3, $status_open_indicator='r') {
    
        if ($entity_status_id===NULL) {
            $entity_status_data=$this->test_get_entity_status($entity_type);
            $entity_status_id=$entity_status_data["{$entity_type}_status_id"];
        }

        if ($entity_type_id===NULL) {
            $entity_type_data=$this->test_get_entity_type($entity_type);
            $entity_type_id=$entity_type_data["{$entity_type}_type_id"];
        }

        $ret=update_entity_status($this->con, $entity_type, $entity_status_id, $entity_type_id, $entity_status_short_name, $entity_status_pretty_name, $entity_status_pretty_plural, $entity_status_display_html, $entity_status_long_desc, $sort_order, $status_open_indicator);
        
        $this->assertTrue($ret, "Failed to update entity status for type $entity_type, type_id $entity_type_id, short $entity_status_short_name,  pretty $entity_status_pretty_name, plurar $entity_status_pretty_plural, display " . htmlspecialchars($entity_status_display_html).", desc $entity_status_long_desc, sort $sort_order,  open $status_open_indicator");

        $entity_status_data=$this->test_get_entity_status($entity_type, $entity_status_id);
        if (!$entity_status_data) { $this->fail("Failed to get entity status data for update comparison, skipping further tests");  }
        else {
            $this->assertTrue($entity_status_data["{$entity_type}_type_id"]==$entity_type_id,"Failed to update field type_id to $entity_type_id: is {$entity_status_data["{$entity_type}_type_id"]}");
            $this->assertTrue($entity_status_data["{$entity_type}_status_short_name"]==$entity_status_short_name,"Failed to update field status_short_name to $entity_status_short_name: is {$entity_status_data["{$entity_type}_status_short_name"]}");
            $this->assertTrue($entity_status_data["{$entity_type}_status_pretty_name"]==$entity_status_pretty_name,"Failed to update field status_pretty_name to $entity_status_pretty_name: is {$entity_status_data["{$entity_type}_status_pretty_name"]}");
            $this->assertTrue($entity_status_data["{$entity_type}_status_pretty_plural"]==$entity_status_pretty_plural,"Failed to update field status_pretty_plural to $entity_status_pretty_plural: is {$entity_status_data["{$entity_type}_status_pretty_plural"]}");
            $this->assertTrue($entity_status_data["{$entity_type}_status_display_html"]==$entity_status_display_html,"Failed to update field status_display_html to $entity_status_display_html: is {$entity_status_data["{$entity_type}_status_display_html"]}");
            $this->assertTrue($entity_status_data["{$entity_type}_status_long_desc"]==$entity_status_long_desc,"Failed to update field status_long_desc to $entity_status_long_desc: is {$entity_status_data["{$entity_type}_status_long_desc"]}");
            $this->assertTrue($entity_status_data["status_open_indicator"]==$status_open_indicator,"Failed to update field status_open_indicator to $status_open_indicator: is {$entity_status_data["status_open_indicator"]}");
            $this->assertTrue($entity_status_data["sort_order"]==$sort_order,"Failed to update field sort_order to $sort_order: is {$entity_status_data['sort_order']}");
        }
        return $ret;
    }


    function test_delete_entity_status($entity_type='case', $entity_status_id=NULL, $delete_from_database=true) {
        if ($entity_status_id===NULL) { 
            $entity_status_data=$this->test_get_entity_status($entity_type); 
            $entity_status_id=$entity_status_data["{$entity_type}_status_id"]; 
        }
    
        $ret=delete_entity_status($this->con, $entity_type, $entity_status_id, $delete_from_database);
    
        $this->assertTrue($ret, "Failed to delete entity status for type $entity_type ID $entity_status_id from_db flag: $delete_from-database");
    
        return $ret;
    }

    function test_add_entity_status_closed_resolved($entity_type='case', $entity_type_id=NULL, $entity_status_short_name='TESTRESOLV', $entity_status_pretty_name='TESTCASE RESOLVED STATUS: IGNORE', $entity_status_pretty_plural='TESTCASE RESOLVED STATUSES: IGNORE', $entity_status_display_html=false, $entity_status_long_desc="THIS RESOLVED STATUS WAS ADDED BY THE PHPUnit TEST SYSTEM, SHOULD BE IGNORED, AND SHOULD HAVE BEEN AUTOMATICALLY DELETED.", $sort_order=2, $status_open_indicator='r') {
        $ret=$this->test_add_entity_status($entity_type, $entity_type_id, $entity_status_short_name, $entity_status_pretty_name, $entity_status_pretty_plural, $entity_status_display_html, $entity_status_long_desc, $sort_order, $status_open_indicator);
        return $ret;
    }

    function test_delete_entity_status_closed_resolved($entity_type='case', $entity_type_id=NULL, $entity_status_short_name='TESTRESOLV') {
        $ret=$this->test_find_entity_status($entity_type, $entity_type_id, $entity_status_short_name);
        if (!$ret) { $this->fail("Failed to delete closed resolved entity status $entity_status_short_name"); return false; }
        else { 
            $status_data=current($ret); 
            $status_id=$status_data["{$entity_type}_status_id"];
            return $this->test_delete_entity_status($entity_type, $status_id);
        }
    }

    function test_add_entity_status_closed_unresolved($entity_type='case', $entity_type_id=NULL, $entity_status_short_name='TESTUNRESO', $entity_status_pretty_name='TESTCASE RESOLVED STATUS: IGNORE', $entity_status_pretty_plural='TESTCASE RESOLVED STATUSES: IGNORE', $entity_status_display_html=false, $entity_status_long_desc="THIS RESOLVED STATUS WAS ADDED BY THE PHPUnit TEST SYSTEM, SHOULD BE IGNORED, AND SHOULD HAVE BEEN AUTOMATICALLY DELETED.", $sort_order=3, $status_open_indicator='u') {
        $ret=$this->test_add_entity_status($entity_type, $entity_type_id, $entity_status_short_name, $entity_status_pretty_name, $entity_status_pretty_plural, $entity_status_display_html, $entity_status_long_desc, $sort_order, $status_open_indicator);
        return $ret;
    }

    function test_delete_entity_status_closed_unresolved($entity_type='case', $entity_type_id=NULL, $entity_status_short_name='TESTUNRESO') {
        $ret=$this->test_find_entity_status($entity_type, $entity_type_id, $entity_status_short_name);
        if (!$ret) { $this->fail("Failed to delete closed resolved entity status $entity_status_short_name"); return false; }
        else { 
            $status_data=current($ret); 
            $status_id=$status_data["{$entity_type}_status_id"];
            return $this->test_delete_entity_status($entity_type, $status_id);
        }
    }

    function test_add_entity_status_closed_won($entity_type='opportunity', $entity_type_id=NULL, $entity_status_short_name='TESTWON', $entity_status_pretty_name='TESTCASE WON STATUS: IGNORE', $entity_status_pretty_plural='TESTCASE WON STATUSES: IGNORE', $entity_status_display_html=false, $entity_status_long_desc="THIS WON STATUS WAS ADDED BY THE PHPUnit TEST SYSTEM, SHOULD BE IGNORED, AND SHOULD HAVE BEEN AUTOMATICALLY DELETED.", $sort_order=2, $status_open_indicator='w') {
        $ret=$this->test_add_entity_status($entity_type, $entity_type_id, $entity_status_short_name, $entity_status_pretty_name, $entity_status_pretty_plural, $entity_status_display_html, $entity_status_long_desc, $sort_order, $status_open_indicator);
        return $ret;
    }

    function test_delete_entity_status_closed_won($entity_type='opportunity', $entity_type_id=NULL, $entity_status_short_name='TESTWON') {
        $ret=$this->test_find_entity_status($entity_type, $entity_type_id, $entity_status_short_name);
        if (!$ret) { $this->fail("Failed to delete closed resolved entity status $entity_status_short_name"); return false; }
        else { 
            $status_data=current($ret); 
            $status_id=$status_data["{$entity_type}_status_id"];
            return $this->test_delete_entity_status($entity_type, $status_id);
        }
    }

    function test_add_entity_status_closed_lost($entity_type='opportunity', $entity_type_id=NULL, $entity_status_short_name='TESTLOST', $entity_status_pretty_name='TESTCASE LOST STATUS: IGNORE', $entity_status_pretty_plural='TESTCASE LOST STATUSES: IGNORE', $entity_status_display_html=false, $entity_status_long_desc="THIS LOST STATUS WAS ADDED BY THE PHPUnit TEST SYSTEM, SHOULD BE IGNORED, AND SHOULD HAVE BEEN AUTOMATICALLY DELETED.", $sort_order=3, $status_open_indicator='l') {
        $ret=$this->test_add_entity_status($entity_type, $entity_type_id, $entity_status_short_name, $entity_status_pretty_name, $entity_status_pretty_plural, $entity_status_display_html, $entity_status_long_desc, $sort_order, $status_open_indicator);
        return $ret;
    }

    function test_delete_entity_status_closed_lost($entity_type='opportunity', $entity_type_id=NULL, $entity_status_short_name='TESTLOST') {
        $ret=$this->test_find_entity_status($entity_type, $entity_type_id, $entity_status_short_name);
        if (!$ret) { $this->fail("Failed to delete closed resolved entity status $entity_status_short_name"); return false; }
        else { 
            $status_data=current($ret); 
            $status_id=$status_data["{$entity_type}_status_id"];
            return $this->test_delete_entity_status($entity_type, $status_id);
        }
    }

    function test_delete_entity_type($entity_type='case', $entity_type_id=NULL, $delete_from_database=true) {
        if ($entity_type_id===NULL) { 
            $entity_type_data=$this->test_get_entity_type($entity_type); 
            $entity_type_id=$entity_type_data["{$entity_type}_type_id"]; 
        }
    
        $ret=delete_entity_type($this->con, $entity_type, $entity_type_id, $delete_from_database);
    
        $this->assertTrue($ret, "Failed to delete entity type for type $entity_type ID $entity_type_id from_db flag: $delete_from-database");
    
        return $ret;
    }
    
    function test_add_entity_type_opp($entity_type='opportunity') {
        return $this->test_add_entity_type($entity_type);
    }
    function test_add_entity_status_opp($entity_type='opportunity') {
        return $this->test_add_entity_status($entity_type);
    }
    function test_find_entity_type_opp($entity_type='opportunity') {
        return $this->test_find_entity_type($entity_type);
    }
    function test_find_entity_status_opp($entity_type='opportunity') {
        return $this->test_find_entity_status($entity_type);
    }
    function test_get_entity_type_opp($entity_type='opportunity') {
        return $this->test_get_entity_type($entity_type);
    }
    function test_get_entity_status_opp($entity_type='opportunity') {
        return $this->test_get_entity_status($entity_type);
    }
    function test_update_entity_type_opp($entity_type='opportunity') {
        return $this->test_update_entity_type($entity_type);
    }
    function test_update_entity_status_opp($entity_type='opportunity') {
        return $this->test_update_entity_status($entity_type);
    }
    function test_delete_entity_status_opp($entity_type='opportunity') {
        return $this->test_delete_entity_status($entity_type);
    }
    function test_delete_entity_type_opp($entity_type='opportunity') {
        return $this->test_delete_entity_type($entity_type);
    }

    function test_add_delete_add_entity_status($entity_type='case') {
        $type=$this->test_add_entity_type($entity_type);

        $ret=$this->test_add_entity_status($entity_type);
        if (!$ret) { $this->fail("Failed to add entity status for add/delete/add test, skipping further tests"); return false; }
        $ret2=$this->test_delete_entity_status($entity_type, $ret, false);
        if (!$ret2) { $this->fail("Failed to logically delete entity type for add/delete/test, skipping further tests"); return false; }

        $ret3=$this->test_find_entity_status($entity_type, $type, 'TESTTYPE', false, false, false, false);
        if ($ret3) { $this->fail("Found deleted record after logically deleting, should fail, skipping further tests"); return false; }

        $rec = $this->test_get_entity_status($entity_type, $ret);
        if (!$rec) { $this->fail("Could not retrieve deleted record for check on record status"); }
        else { $this->assertTrue($rec["{$entity_type}_status_record_status"]=='d', "Record status should be 'd', is '{$rec["{$entity_type}_status_record_status"]}'"); }

        $ret4=$this->test_add_entity_status($entity_type);
        if (!$ret) { $this->fail("Failed to re-add entity status for add/delete/add test, skipping further tests"); return false; }

        $this->assertTrue($ret==$ret4, "Failed to match existing (deleted) entity status $ret with newly added $ret4, should match");

        $this->test_delete_entity_status($entity_type, $ret4);
        $this->test_delete_entity_type($entity_type, $type);
    }

    function test_add_delete_add_entity_status_opp($entity_type='opportunity') {
        return $this->test_add_delete_add_entity_status($entity_type);
    }

    function test_add_delete_add_entity_type($entity_type='case') {
        $ret=$this->test_add_entity_type($entity_type);
        if (!$ret) { $this->fail("Failed to add entity type for add/delete/add test, skipping further tests"); return false; }
        $ret2=$this->test_delete_entity_type($entity_type, $ret, false);
        if (!$ret2) { $this->fail("Failed to logically delete entity type for add/delete/test, skipping further tests"); return false; }

        $ret3=$this->test_find_entity_type($entity_type, 'TESTTYPE', false, false, false);
        if ($ret3) { $this->fail("Found deleted record after logically deleting, should fail, skipping further tests"); return false; }

        $rec = $this->test_get_entity_type($entity_type, $ret);
        if (!$rec) { $this->fail("Could not retrieve deleted record for check on record status"); }
        else { $this->assertTrue($rec["{$entity_type}_type_record_status"]=='d', "Record status should be 'd', is '{$rec["{$entity_type}_type_record_status"]}'"); }

        $ret4=$this->test_add_entity_type($entity_type);
        if (!$ret) { $this->fail("Failed to re-add entity type for add/delete/add test, skipping further tests"); return false; }

        $this->assertTrue($ret==$ret4, "Failed to match existing (deleted) entity type $ret with newly added $ret4, should match");

        $this->test_delete_entity_type($entity_type, $ret4);
    }

    function test_add_delete_add_entity_type_opp($entity_type='opportunity') {
        return $this->test_add_delete_add_entity_type($entity_type);
    }



    function test_add_entity_priority($entity_type='case', $entity_priority_short_name='TESTPRIOR', $entity_priority_pretty_name='TESTCASE PRIORITY: IGNORE', $entity_priority_pretty_plural='TESTCASE PRIORITIES: IGNORE', $entity_priority_display_html=false, $entity_priority_score_adjustment=10) {
    
        $ret=add_entity_priority($this->con, $entity_type, $entity_priority_short_name, $entity_priority_pretty_name, $entity_priority_pretty_plural, $entity_priority_display_html, $entity_priority_score_adjustment);
    
        $this->assertTrue($ret, "Failed to add entity priority for priority $entity_type short name $entity_priority_short_name pretty $entity_priority_pretty_name plural $entity_priority_pretty_plural display ".htmlspecialchars($entity_priority_display_html));
    
        return $ret;
    }

    function test_find_entity_priority($entity_type='case', $entity_priority_short_name='TESTPRIOR', $entity_priority_pretty_name=false, $entity_priority_score_adjustment=10, $show_all=false, $success=true) {
    
        $ret=find_entity_priority($this->con, $entity_type, $entity_priority_short_name, $entity_priority_pretty_name, $entity_priority_score_adjustment, $show_all);
    
        if ($success)
            $this->assertTrue($ret, "Failed to find entity matching priority $entity_type, short $entity_priority_short_name, pretty $entity_priority_pretty_name showing all: $show_all");
        else
            $this->assertTrue(!$ret, "Found entity when intending to fail matching priority $entity_type, short $entity_priority_short_name, pretty $entity_priority_pretty_name showing all: $show_all");
    
        return $ret;
    }

    function test_get_entity_priority($entity_type='case', $entity_priority_id=false, $entity_priority_short_name='TESTPRIOR') {
    
        $ret=get_entity_priority($this->con, $entity_type, $entity_priority_id, $entity_priority_short_name);
    
        $this->assertTrue($ret, "Failed to get entity priority for priority $entity_type, priority_id $entity_priority_id, short $entity_priority_short_name");
    
        return $ret;
    
    }

    function test_update_entity_priority($entity_type='case', $entity_priority_id=NULL, $entity_priority_short_name='TESTPRIOR', $entity_priority_pretty_name='TESTCASE priority:PLEASE IGNORE', $entity_priority_pretty_plural='TESTCASE priorities: PLEASE IGNORE', $entity_priority_display_html=false, $entity_priority_score_adjustment=25) {

        if ($entity_priority_id===NULL) {
            $entity_priority_data=$this->test_get_entity_priority($entity_type);
            $entity_priority_id=$entity_priority_data["{$entity_type}_priority_id"];
        }
    
        $ret=update_entity_priority($this->con, $entity_type, $entity_priority_id, $entity_priority_short_name, $entity_priority_pretty_name, $entity_priority_pretty_plural, $entity_priority_display_html, $entity_priority_score_adjustment);
    
        $this->assertTrue($ret, "Failed to update entity priority for priority $entity_type short name $entity_priority_short_name pretty $entity_priority_pretty_name plural $entity_priority_pretty_plural display ".htmlspecialchars($entity_priority_display_html));
    
        $entity_priority_data=$this->test_get_entity_priority($entity_type, $entity_priority_id);
        if (!$entity_priority_data) { $this->fail("Failed to get entity priority data for update comparison, skipping further tests");  }
        else {
            $this->assertTrue($entity_priority_data["{$entity_type}_priority_short_name"]==$entity_priority_short_name,"Failed to update field priority_short_name to $entity_priority_short_name: is {$entity_priority_data["{$entity_type}_priority_short_name"]}");
            $this->assertTrue($entity_priority_data["{$entity_type}_priority_pretty_name"]==$entity_priority_pretty_name,"Failed to update field priority_pretty_name to $entity_priority_pretty_name: is {$entity_priority_data["{$entity_type}_priority_pretty_name"]}");
            $this->assertTrue($entity_priority_data["{$entity_type}_priority_pretty_plural"]==$entity_priority_pretty_plural,"Failed to update field priority_pretty_plural to $entity_priority_pretty_plural: is {$entity_priority_data["{$entity_type}_priority_pretty_plural"]}");
            $this->assertTrue($entity_priority_data["{$entity_type}_priority_display_html"]==$entity_priority_display_html,"Failed to update field priority_display_html to $entity_priority_display_html: is {$entity_priority_data["{$entity_type}_priority_display_html"]}");
            $this->assertTrue($entity_priority_data["{$entity_type}_priority_score_adjustment"]==$entity_priority_score_adjustment,"Failed to update field entity_priority_score_adjustment to $entity_priority_score_adjustment: is {$entity_priority_data["{$entity_type}_priority_score_adjustment"]}");

        }

        return $ret;
    }
    function test_delete_entity_priority($entity_type='case', $entity_priority_id=NULL, $delete_from_database=true) {
        if ($entity_priority_id===NULL) { 
            $entity_priority_data=$this->test_get_entity_priority($entity_type); 
            $entity_priority_id=$entity_priority_data["{$entity_type}_priority_id"]; 
        }
    
        $ret=delete_entity_priority($this->con, $entity_type, $entity_priority_id, $delete_from_database);
    
        $this->assertTrue($ret, "Failed to delete entity priority for priority $entity_type ID $entity_priority_id from_db flag: $delete_from-database");
    
        return $ret;
    }

}

/**
 * $Log: type_status_class.php,v $
 * Revision 1.6  2006/04/29 01:52:05  vanmer
 * - added tests for statuses for opportunities to reflect won/lost closed code
 * - updated opportunities test to use appropriate won/lost tests from statuses tests
 * - updated main test class to include proper file for workflow tests
 *
 * Revision 1.5  2006/04/28 23:08:47  vanmer
 * - added log to type/status class
 *
**/
?>