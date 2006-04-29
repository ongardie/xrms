<?php
/**
 * Test harness for the XRMS ACL system
 *
 * Copyright (c) 2004 Explorer Fund Advisors, LLC
 * All Rights Reserved.
 *
 * @todo
 * $Id: xrms_test_class.php,v 1.3 2006/04/29 01:52:05 vanmer Exp $
 */
require_once('../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'utils-workflow.php');
require_once($include_directory . 'utils-preferences.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

require_once("PHPUnit.php");



Class XRMS_TestCase extends PHPUnit_TestCase { 
    
    function XRMSTest( $name = "XRMSTest" ) {
        $this->PHPUnit_TestCase( $name );
    }
   function setUp() {   
       global $con;
       if (!$con) $this->con=get_xrms_dbconnection();
       else $this->con=$con;
       $this->user_id=1;
/*
       $this->options = $options;
       $this->con = &adonewconnection($options['xrms_db_dbtype']);
       //connect to the xrms database
       $this->con->nconnect($options['xrms_db_server'], $options['xrms_db_username'], $options['xrms_db_password'], $options['xrms_db_dbname']);
*/
    }

   function teardown() {
        $this->con->close();
       $this->con=NULL;
    }

    function test_XRMSTEST() {
    $this->assertTrue(true, "This should never fail.");
    }

}    
Class XRMSTest extends XRMS_TestCase { 

    function test_add_user_preference_type($preference_type_name='user_language',
                                                                         $user_preference_pretty_name=false,
                                                                    $user_preference_description=false, 
                                                                    $allow_multiple=false,
                                                                    $allow_user_edit=false) {
        
        $ret=add_user_preference_type($this->con, $preference_type_name, $user_preference_pretty_name, $user_preference_description, $allow_multiple, $allow_user_edit);
        $this->assertTrue($ret, "Failed to add or re-add user preference for language");
        return $ret;        
                                                                    
                                                                                                                                        
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
    
    function test_get_user_preference_type($user_preference_type=false, $user_preference_type_id=false, $return_all=true) {
        $ret=get_user_preference_type($this->con, $user_preference_type, $user_preference_type_id, $return_all);
        $this->assertTrue($ret, "Failed to get user preferences for type $user_preference_type id $user_preference_type_id return all: $return_all");
    }
    
    function test_user_preference_options($user_preference_type='user_language', $option_value='zz_QQ', $option_display=false, $sort_order=1) {
        if (!is_numeric($user_preference_type)) {
            $type_info=get_user_preference_type($this->con, $user_preference_type);
            $user_preference_type=$type_info['user_preference_type_id'];
        }
        $ret=add_preference_option($this->con, $user_preference_type, $option_value,$option_display, $sort_order);
        $this->assertTrue($ret, "Failed to add option for preference $user_preference_type value $option_value order $sort_order");
        if ($ret) {
            $options=get_preference_options($this->con, $user_preference_type);
            $this->assertTrue($options, "Failed to get options to check preference option addition for type $user_preference_type");
            if ($options) {
                $option_display=get_preference_options($this->con,$user_preference_type, false, true);
                $option_values=array_keys($option_display);
                $this->assertTrue(in_array($option_value, $option_values), "Failed to find option in keys for possible value output");
                $key=array_search($option_value, $options);
                $this->assertTrue($key, "Failed to find value $option_value in options for type $user_preference_type");
                $this->assertTrue($key==$ret, "Option ID is not equal to returned option ID from creation");
                $del=delete_preference_option($this->con, $user_preference_type, $option_value, true);
                $this->assertTrue($del, "Failed to delete option after this for type $user_preference_type with value $option_value");
                return $key;
            }
        }
    }
    
    function test_set_multi_user_preference($user_id=1, $preference_type=false, $preference_values=false) {
        $con = $this->con;
	//print_r($_SESSION['XRMS_function_cache']['get_user_preference']);
        if (!$preference_type) { $preference_type='random_multi_option'; $user_preference_type_id=$this->test_add_user_preference_type($preference_type, 'TEST MULTI OPTION', 'Test Type for Multiple Options: Ignore', true, false); $created=true; }
        if (!$preference_values) { $preference_values=array('size'=>'1','shape'=>'two and two is four','texture'=>'testTexture'); }
        foreach ($preference_values as $pkey=>$pval) {
            $ret = $this->test_set_user_preference($user_id, $preference_type, $pval, $pkey, true, false);
            $this->assertTrue($ret, "Failed to assign multi-select for preference $preference_type, $pkey=$pval");
            $default_value=$pval;
        }
        $prefs = get_user_preference($con, $user_id, $preference_type, false, true);
//        print_r($prefs);
        $this->assertTrue($prefs, "Failed to get preferences for user $user_id with type $preference_type for multi preference test.");
        if ($prefs) {
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
	    //print_r($_SESSION['XRMS_function_cache']['get_user_preference']);
        }
        $dret = delete_user_preference($con, $user_id, $preference_type, false, true, true);
        if ($created) {
            delete_user_preference_type($con, $user_preference_type_id, true);
        }
    }
    
    function test_create_form_element($element_type='test', $element_name='testing', $element_value=3, $extra_element_attributes=" onclick=\"javascript:alert('blah');\"", $element_length=false, $element_height=false, $possible_values=false, $show_blank_first=false) {
        if (!$possible_values){  $possible_values=array(1=>'blah',2=>'huzzah', 3=>'joe'); $show_blank_first=true; }
        $ret=create_form_element($element_type, $element_name, $element_value, $extra_element_attributes, $element_length, $element_height, $possible_values, $show_blank_first);
//        echo htmlspecialchars($ret).$ret;
        $this->assertTrue($ret, "Failed to get element of type $element_type name $element_name value $element_value");
        return $ret;
    }
    
    function test_function_cache($func_name='test_function_cache_set', $params=false, $ret='TEST', $this_request_only=false) {
        if (!$this_request_only) {
            session_start();
        }
        if (!$params) { 
            $params=func_get_args();
        }
        $key=implode('|',$params);
        
        function_cache_set($func_name, $params, $ret, $this_request_only);
        if ($this_request_only) {
            global $xrms_function_cache;
            $this->assertTrue(isset($xrms_function_cache[$func_name][$key]), "Failed to cache function into global variable for $func_name $key");
            $this->assertTrue($xrms_function_cache[$func_name][$key]==$ret, "Failed to properly cache $func_name $key $ret!={$xrms_function_cache[$func_name][$key]}"); 
        } else {
            $this->assertTrue(isset($_SESSION['XRMS_function_cache'][$func_name][$key]), "Failed to cache function into session for $func_name $key");
            $this->assertTrue($_SESSION['XRMS_function_cache'][$func_name][$key]==$ret, "Failed to properly cache $func_name $key $ret!={$_SESSION['XRMS_function_cache'][$func_name][$key]}");
        }
        
        $this->assertTrue(function_cache_bool($func_name, $params), "Failed function_cache_bool check for $func_name $key");
        
        $testRet = function_cache_get($func_name, $params);
        $this->assertTrue($testRet==$ret, "Failed to successfully fetch cached $func_name $key, got $testRet instead");
       return $testRet;
    }
    
    function test_add_workflow_history($on_what_table='TEST', $on_what_id=1, $old_status=1, $new_status=2, $delete_from_database=true) {
        $ret=add_workflow_history($this->con, $on_what_table, $on_what_id, $old_status, $new_status, $this->user_id);
        $this->assertTrue($ret, "Failed to add workflow history for $on_what_table $on_what_id, $old_status -> $new_status");
        if ($delete_from_database AND $ret) {
            $sql="DELETE FROM workflow_history WHERE on_what_table=".$this->con->qstr($on_what_table)." AND on_what_id=$on_what_id";
            $rst=$this->con->execute($sql);
            $this->assertTrue($rst, "Return from delete of workflow history failed");   
        }
        return $ret;
    }
    
}
/*
 * $Log: xrms_test_class.php,v $
 * Revision 1.3  2006/04/29 01:52:05  vanmer
 * - added tests for statuses for opportunities to reflect won/lost closed code
 * - updated opportunities test to use appropriate won/lost tests from statuses tests
 * - updated main test class to include proper file for workflow tests
 *
 * Revision 1.2  2006/04/05 00:46:37  vanmer
 * - added user_id parameter for workflow history
 *
 * Revision 1.1  2005/11/18 20:05:58  vanmer
 * - Moved XRMS tests into a seperate test class, includeable without the GUI
 *
 * Revision 1.7  2005/10/04 03:23:46  vanmer
 * - added fixed width parser tests to xrms test suite
 *
 * Revision 1.6  2005/10/01 08:24:41  vanmer
 * - changed to instantiate a new dbconnection for tests
 * - added ACL and file tests to list of tests
 *
 * Revision 1.5  2005/09/30 22:11:04  vanmer
 * - added hook to allow XRMS to run any tests that are provided by a plugin
 *
 * Revision 1.4  2005/05/18 21:49:29  vanmer
 * - added test for adding workflow history
 *
 * Revision 1.3  2005/05/09 22:42:10  vanmer
 * - added test for new session caching functions
 *
 * Revision 1.2  2005/05/06 00:51:18  vanmer
 * - added tests for preferences system options
 *
 * Revision 1.1  2005/03/09 16:16:52  vanmer
 * - moving test suite from admin/tests to basedir/tests
 *
*/
?>