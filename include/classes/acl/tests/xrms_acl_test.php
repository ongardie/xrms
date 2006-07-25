<?php
/**
 * Test harness for the XRMS ACL system
 *
 * Copyright (c) 2004 Explorer Fund Advisors, LLC
 * All Rights Reserved.
 *
 * @todo
 * $Id: xrms_acl_test.php,v 1.14 2006/07/25 20:39:22 vanmer Exp $
 */

if (!$include_directory) require_once('../../../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

require_once("PHPUnit.php");
require_once("PHPUnit/GUI/HTML.php");
require_once($include_directory."classes/acl/xrms_acl_config.php");
//require_once("../xrms_acl.php");


Class ACLTest extends PHPUnit_TestCase { 
    
    function ACLTest( $name = "ACLTest" ) {
        $this->PHPUnit_TestCase( $name );
    }
   function setUp() {
       //global $options;
       //$this->options = $options;
//       $this->acl = new xrms_acl ($this->options );
        $this->acl = new xrms_acl (false, false, 'xrms_acl_auth_callback' );
        $this->user = 1;
        $this->scope = "World";
        $this->permission=1;
        $this->on_what_id=1;
        $this->groupName="blah";
        $this->roleName="blah";
        $this->controlled_objectName="blah";
        $this->controlled_objectTable="blah";
        $this->controlled_objectField="blah";
        $this->controlled_objectDataSource=1;
        $this->data_sourceName="blah";
    }
 


   function teardown() {
       $this->options = NULL;
       $this->acl=NULL;
        $this->user = NULL;
        $this->scope = NULL;
        $this->permission=NULL;
        $this->on_what_id=NULL;
       $this->groupName=NULL;
       $this->roleName=NULL;
       $this->controlled_objectName=NULL;
       $this->controlled_objectTable=NULL;
       $this->controlled_objectField=NULL;
       $this->controlled_objectDataSource=NULL;
       $this->data_sourceName=NULL;
    }

    function test_assert() {
        $this->assertTrue(true,"This should never fail");
    }    
   function test_add_role($name=false) {
        if (!$name) { $name = $this->roleName; }
       $result = $this->acl->add_role( $name);
       $this->assertTrue($result!==false, "Role addition failed");
        return $result;
    }

    function test_get_role($role=false) {
        if (!$role) { $role=$this->roleName; }
       $result = $this->acl->get_role($role);
       $this->assertTrue($result!==false, "Role locate failed");
       $this->assertTrue(is_array($result),"Role returned is not an array.");
       return $result;
   } 

   function test_delete_role($role=false) {
        if (!$role) { $role=$this->roleName; }
       $result = $this->test_get_role($role);
       $roleid= $result['Role_id'];
       $result=$this->acl->delete_role($roleid);
       $this->assertTrue($result,"Delete of role $role: $roleid should succeed");
    }

      function test_add_data_source($name=false) {
        if (!$name) { $name = $this->data_sourceName; }
       $result = $this->acl->add_data_source( $name);
       $this->assertTrue($result!==false, "data_source addition failed");
        return $result;
    }

    function test_get_data_source($data_source=false) {
        if (!$data_source) { $data_source=$this->data_sourceName; }
       $result = $this->acl->get_data_source($data_source);
       $this->assertTrue($result!==false, "data_source locate failed");
       $this->assertTrue(is_array($result),"data_source returned is not an array.");
       return $result;
   } 

   function test_delete_data_source($data_source=false) {
        if (!$data_source) { $data_source=$this->data_sourceName; }
       $result = $this->test_get_data_source($data_source);
       $data_sourceid= $result['data_source_id'];
       $result=$this->acl->delete_data_source($data_sourceid);
       $this->assertTrue($result,"Delete of data_source $data_source: $data_sourceid should succeed");
    }

    
   
   function test_add_controlled_object($name=false, $table=false, $field=false, $user_field=false, $data_source=false) {
       if (!$name) { $name = $this->controlled_objectName; }
       if (!$table) { $table = $this->controlled_objectTable; }
       if (!$field) { $field = $this->controlled_objectField; }
       if (!$data_source) { $data_source = $this->controlled_objectDataSource; }
       $result = $this->acl->add_controlled_object( $name,$table,$field, $user_field, $data_source);
       $this->assertTrue($result!==false, "ControlledObject addition of $name failed");
       return $result;
    }
    function test_add_controlled_object_again($name=false, $table=false, $field=false, $user_field=false, $data_source=false) {
       if (!$name) { $name = $this->controlled_objectName; }
       if (!$table) { $table = $this->controlled_objectTable; }
       if (!$field) { $field = $this->controlled_objectField; }
       if (!$data_source) { $data_source = $this->controlled_objectDataSource; }       
        $result=$this->acl->add_controlled_object($name, $table, $field, $user_field, $data_source);
        $this->assertTrue($result, "Failed to add controlled object of $name for duplicate test");
        $result2=$this->acl->add_controlled_object($name, $table, $field, $user_field, $data_source);
        $this->assertTrue($result==$result2, "Failed to get already added controlled object of $name when adding duplicate");        
        return $result;
    }      
    function test_get_controlled_object($search=false) {
        if (!$search) { $search = $this->controlled_objectName; }
       $result = $this->acl->get_controlled_object($search);
       $this->assertTrue($result!==false, "ControlledObject locate failed");
       $this->assertTrue(is_array($result), "ControlledObject should be an array, failed");
       return $result;
   } 

   function test_delete_controlled_object($search=false, $objectid=false) {
        if (!$objectid) {
            if (!$search) { $search = $this->controlled_objectName; }
           $result = $this->test_get_controlled_object($search);
           $objectid = $result['ControlledObject_id'];
       }
       $result=$this->acl->delete_controlled_object($objectid);
       $this->assertTrue($result,"Delete of controlled_object should succeed");
    }

    
            
   function test_add_group($name=false) {
       if (!$name) { $name = $this->groupName; }
       $result = $this->acl->add_group( $name);
       $this->assertTrue($result!==false, "Group addition failed");
       return $result;
    }

    function test_get_group($search=false,$group_id=false) {
        if (!$search) { $search = $this->groupName; }
       $result = $this->acl->get_group($search,$group_id);
       $this->assertTrue($result!==false, "Group locate failed for $search: $result");
       $this->assertTrue(is_array($result),"Group return should be an array (failed)");
       return $result;
   } 

   function test_delete_group($search=false) {
        if (!$search) { $search = $this->groupName; }
       $result = $this->test_get_group($search);
       $groupid = $result['Group_id'];
       $result=$this->acl->delete_group($groupid);
       $this->assertTrue($result,"Delete of group should succeed");
    }

    function test_add_group_group($_group1=false, $_group2=false) {
        if (!$_group1) { $group1 = $this->groupName."Parent"; } 
        else { $group1 = $_group1; }
        if (!$_group2) { $group2= $this->groupName."Child"; } 
        else { $group2 = $_group2; }
        
        if (!$_group1) { $group1id = $this->test_add_group($group1); } 
        else { $group1data = $this->test_get_group($group1); $group1id=$group1data['Group_id']; }
        
        if (!$_group2) { $group2id = $this->test_add_group($group2); } 
        else { $group2data = $this->test_get_group($group2); $group2id=$group2data['Group_id']; }
        
        $result = $this->acl->add_group_group($group1id, $group2id);
        $this->assertTrue($result,"Adding group $group2: $group2id to $group1: $group1id failed.");
        return $result;
    }
   
   function test_delete_group_group($_parentGroupName=false,$_childGroupName=false) {
        if (!$_parentGroupName) { $parentGroupName=$this->groupName."Parent"; }
        else { $parentGroupName=$_parentGroupName;}
        if (!$_childGroupName) { $childGroupName = $this->groupName."Child"; }
        else { $childGroupName = $_childGroupName; }
        
       $result = $this->test_get_group_user($parentGroupName,false,false,$childGroupName);
       $this->assertTrue($result,"Locate of child group $childGroupName in group $parentGroupName failed.");
       $this->assertTrue(is_array($result),"Group user get should return array, failed.");
       
       $groupUserInfo=array_shift($result);

       $groupUser_id=$groupUserInfo['GroupUser_id'];
       $this->assertTrue($groupUser_id, "Find of group group failed, delete should also fail");

       
       $result=$this->acl->delete_group_user($groupUser_id);
       $this->assertTrue($result,"Delete of group $childGroupName from group $parentGroupName should succeed");
        
        if (!$_parentGroupName) { $this->test_delete_group($parentGroupName); }
        if (!$_childGroupName) { $this->test_delete_group($childGroupName); }

    }

    function test_add_group_user($group=false,$role=false,$user=false) {
        if (!$group) {
            $group = $this->test_add_group();
        } else { $groupData = $this->test_get_group($group); $group = $groupData['Group_id']; }
        if (!$role) {
            $role = $this->test_add_role();
        } else { $roleData = $this->test_get_role($role); $role = $roleData['Role_id']; }
        if (!$user) {
            $user = $this->user;
        }
        $result = $this->acl->add_group_user($group, $user, $role);
        $this->assertTrue($result,"Adding user $user to group $group in role $role failed.");
        return $result;
    }
    
    function test_get_group_user($group=false, $role=false, $user=false, $childGroup=false, $groupUser_id=false) {
        if (!$group) {
            $group=$this->groupName;
        }
        if (!$childGroup) {
            if (!$role) {
                $role = $this->roleName;
            }
            if (!$user) {
                $user = $this->user;
            }
       
            $rolearray=$this->test_get_role($role);
            $roleid=$rolearray['Role_id'];
            $this->assertTrue($roleid, "Find of role $role failed, delete should also fail");

        } else {
            $roleid=false;
            $childGroup = $this->test_get_group($childGroup);
            $childGroup=$childGroup['Group_id'];
            $this->assertTrue($group, "Find of child group $childGroup failed");
        }
        
        $grouparray=$this->test_get_group($group);
        $groupid=$grouparray['Group_id'];
        $this->assertTrue($group, "Find of group $group failed in test_get_group_user");
                
       $result = $this->acl->get_group_user($groupid,$user,$roleid,$childGroup,$groupUser_id);
       $this->assertTrue($result,"Locate of user $user in group $groupid with role $roleid or child $childGroup or id $groupUser_id failed.");
       $this->assertTrue(is_array($result),"Group user get should return array, failed.");
       if ($result) {
           $groupUserInfo=current($result);
           $this->assertTrue(is_array($groupUserInfo), "Individual group User record should be an array, failed");
       
            $groupUser_id=$groupUserInfo['GroupUser_id'];
            $this->assertTrue($groupUser_id, "Find of user $user in group $group with role $role failed");
       }
       return $result;
              
    }
    
   function test_delete_group_user($_group=false, $_role=false, $user=false) {       
        if (!$_group) {
            $group=$this->groupName;
        } else { $group = $_group; }
        if (!$_role) {
            $role = $this->roleName;
        } else { $role = $_role; }
        if (!$user) {
            $user = $this->user;
        }

       $result = $this->test_get_group_user($group,$role,$user);
       $this->assertTrue($result,"Locate of user $user in group $group with role $role failed.");
       $this->assertTrue(is_array($result),"Group user get should return array, failed.");
       if ($result) {
            $groupUserInfo=array_shift($result);
            
            $result=$groupUserInfo['GroupUser_id'];
            $this->assertTrue($result, "Find of user $user in group $group with role $role failed, delete should also fail");
            
            $result=$this->acl->delete_group_user($result);
            $this->assertTrue($result,"Delete of user from group should succeed");
     }
       if (!$_group) $this->test_delete_group($group);
       if (!$_role) $this->test_delete_role($role);

    }
    
    function test_add_group_object($Group=false, $ControlledObject=false, $on_what_id=false, $criteria_table=false, $criteria_resultfield=false) {
        if ($ControlledObject){ $controlled_objectData = $this->test_get_controlled_object($ControlledObject); $controlled_object=$controlled_objectData['ControlledObject_id']; } else { $controlled_object=false; }
        if (!$controlled_object)
            $controlled_object = $this->test_add_controlled_object($ControlledObject);
        
        if ($Group) { $groupData = $this->test_get_group($Group); $groupid=$groupData['Group_id']; } else { $groupid=false; }
        if (!$groupid)
            $groupid = $this->test_add_group($ControlledObject);
            
        if (!$on_what_id AND !$criteria_table AND !$criteria_resultfield)
            $on_what_id = $this->on_what_id;
        
        $result = $this->acl->add_group_member($groupid, $controlled_object, $on_what_id, $criteria_table, $criteria_resultfield);
        $this->assertTrue($result,"Adding controlled object ID $on_what_id table $criteria_table resultfield $criteria_resultfield of type $controlled_object to group $groupid failed.");
        return $result;
    }
    
    function test_get_group_member($searchGroup=false,$searchObject=false,$on_what_id=false, $criteria_table=false, $criteria_resultfield=false) {
        if (!$searchGroup) { $searchGroup = $this->groupName; }
        if (!$searchObject) { $searchObject = $this->controlled_objectName; }
        if (!$on_what_id AND !$criteria_table AND !$criteria_resultfield) { $on_what_id = $this->on_what_id; }
        
        $group = $this->test_get_group($searchGroup);
        $group = $group['Group_id'];
       $this->assertTrue($group!==false, "Group member group locate failed");
        
        $object = $this->test_get_controlled_object($searchObject);
        $object = $object['ControlledObject_id'];
       $this->assertTrue($object!==false, "Group member object locate failed");
       
       $result = $this->acl->get_group_member($group, $object, $on_what_id, $criteria_table, $criteria_resultfield);
       $this->assertTrue($result!==false, "Group member locate failed looking for group $group object $object id $on_what_id table $criteria_table field $criteria_resultfield");
       $this->assertTrue(is_array($result),"Group member return should be an array (failed)");
       if ($result) {
            $GroupMember=$result;
            $GroupMember=array_shift($GroupMember);
            $this->assertTrue(is_array($GroupMember),"Group Member record should be an array (failed)");
        }
       return $result;
    }
    
    function test_delete_group_object($isearchGroup=false,$isearchObject=false,$on_what_id=false, $criteria_table=false, $criteria_resultfield=false) {
        if (!$isearchGroup) { $searchGroup = $this->groupName; } else { $searchGroup = $isearchGroup; }
        if (!$isearchObject) { $searchObject = $this->groupName; } else { $searchObject  = $isearchObject; }
        if (!$on_what_id AND !$criteria_table AND !$criteria_resultfield) { $on_what_id = $this->on_what_id; }
//        $group = $this->test_get_group($searchGroup);
//        $object = $this->test_get_controlled_object($searchObject);
        
        $GroupMember = $this->test_get_group_member($searchGroup, $searchObject, $on_what_id, $criteria_table, $criteria_resultfield);
        if ($GroupMember) {
            $GroupMember = array_shift($GroupMember);
            $GroupMember_id = $GroupMember['GroupMember_id'];
            $this->assertTrue($GroupMember_id,"Failed to identify group object for delete");
        
            $result=$this->acl->delete_group_object($GroupMember_id);
            $this->assertTrue($result,"Failed to delete group object $searchObject in $searchGroup on $on_what_id id");
        }
                
        if (!$isearchGroup) $this->test_delete_group($searchGroup);
        if (!$isearchObject) $this->test_delete_controlled_object($searchObject);
    }

    function test_add_group_member_criteria($Group=false, $ControlledObject=false, $criteria_table=false, $criteria_resultfield=false, $criteria_fieldname=false, $criteria_value=false, $criteria_operator=false) {
        if (!$criteria_table AND !$criteria_fieldname and !$criteria_value) { 
            $criteria_table='activities';
            $criteria_resultfield='activity_id';
            $criteria_fieldname='company_id';
            $criteria_value=1;
            $criteria_operator='=';
        }
        $groupMember=$this->test_add_group_object(false, false, false, $criteria_table, $criteria_resultfield);
        $this->assertTrue($groupMember, "Failed to add group member with table $criteria_table resultfield $criteria_resultfield");
        $crit=$this->acl->add_group_member_criteria($groupMember, $criteria_fieldname, $criteria_value, $criteria_operator);
        $this->assertTrue($crit, "Failed to add criteria for group member $groupMember field $criteria_fieldname value $criteria_value operator $criteria_operator");
        return $crit;
    }

   function test_get_group_members_by_criteria($GroupMember_id=false, $GroupMember_data=false, $expected_results=false) {
      if (!$GroupMember_id AND !$GroupMember_data) {
            $criteria_table='activities';
            $criteria_resultfield='activity_id';
            $expected_results=array(1);
            $GroupMember_data=$this->test_get_group_member(false, false, false, $criteria_table, $criteria_resultfield);
            $this->assertTrue($GroupMember_data, "Failed to find group member for group member by criteria test, with table $criteria_table field $criteria_resultfield");
            if ($GroupMember_data) {
                $GroupMember_data=current($GroupMember_data);
                $GroupMember_id=$GroupMember_data['GroupMember_id'];
                $this->assertTrue($GroupMember_id, 'Failed to get Group Member ID from Group Member data');                
            }
      }
        $ret=$this->acl->get_group_members_by_criteria($GroupMember_id, $GroupMember_data);
        
        $this->assertTrue($ret, "Failed to retrieve group members by criteria with id $GroupMember_id data $GroupMember_data");
        $this->assertTrue(is_array($ret), "Group members criteria is_array test failed");
        if (is_array($ret)) {
            foreach ($ret as $data) {
                $this->assertTrue($data['ControlledObject_id'], "Failed to find controlled object ID for group member data");
                $this->assertTrue($data['on_what_id'], "Failed to find on_what_id for group member data");
                $ckey=array_search($data['on_what_id'], $expected_results);
                if ($ckey!==false) unset($expected_results[$ckey]);
            }
            $this->assertTrue(count($expected_results)==0, "Failed to find expected results: " . implode(", ",$expected_results));
        }        
        return $ret;
   
   }
   
    function test_get_object_groups_by_criteria($ControlledObject=false, $on_what_id=false, $searchGroup=false) {
        $controlled_objectData = $this->test_get_controlled_object($ControlledObject); 
        $controlled_object=$controlled_objectData['ControlledObject_id'];
        $this->assertTrue($controlled_object, "Failed to get controlled object $ControlledObject for object groups test");
        
        $group = $this->test_get_group($searchGroup);
        $group = $group['Group_id'];
        $this->assertTrue($group!==false, "Group member group locate failed");
        $on_what_id=1;
        
        $ret=$this->acl->get_object_groups_by_criteria($controlled_object, $on_what_id);
        $this->assertTrue($ret, "Failed to get group list for object $ControlledObject id $on_what_id");
        if ($ret) {
            $ckey=array_search($group, $ret);
            $this->assertTrue($ckey!==false, "Failed to find group $group in criteria return list");
        }
        return $ret;         
    }
    
    function test_delete_group_member_criteria($Group=false, $ControlledObject=false, $criteria_table=false, $criteria_resultfield=false, $criteria_fieldname=false, $criteria_value=false, $criteria_operator=false) {
        if (!$criteria_table AND !$criteria_fieldname and !$criteria_value) { 
            $criteria_table='activities';
            $criteria_resultfield='activity_id';
        }    
        return $this->test_delete_group_object($Group, $ControlledObject, false, $criteria_table, $criteria_resultfield);    
    }
    
    function test_add_controlled_object_relationship($_parentObject=false,$_childObject=false, $_childField=false) {
        if ($_parentObject===false) { $parentObject=$this->controlled_objectName."Parent"; } else { $parentObject=$_parentObject; }
        if ($_childObject===false) { $childObject=$this->controlled_objectName."Child"; } else { $childObject=$_childObject; }
        if (!$_childField) { $childField = false; } else { $childField=$_childField; }
        
        if ($_parentObject===false) { $controlled_object1 = $this->test_add_controlled_object($parentObject); }
        elseif ($_parentObject===NULL) { $controlled_object1 = 'null'; }
        else {$controlledObject = $this->test_get_controlled_object($parentObject); $controlled_object1=$controlledObject['ControlledObject_id']; }
        $this->assertTrue($controlled_object1, "Error adding parent $parentObject object for relationship test");
        
        if ($_childObject===false) { $controlled_object2 = $this->test_add_controlled_object($childObject); }
        elseif ($_childObject===NULL) { $controlled_object2 = 'null'; }
        else { $controlledObject = $this->test_get_controlled_object($childObject); $controlled_object2=$controlledObject['ControlledObject_id']; }
        $this->assertTrue($controlled_object2, "Error adding child $childObject object for relationship test");
        
        $result = $this->acl->add_controlled_object_relationship($controlled_object1, $controlled_object2, $childField);
        $this->assertTrue($result,"Adding controlled object $childObject to parent $parentObject failed.");
        return $result;
    }
    
    function test_add_controlled_object_relationship_again($_parentObject=false,$_childObject=false, $_childField=false) {
        $return1=$this->test_add_controlled_object_relationship($_parentObject, $_childObject, $_childField);
        $return2=$this->test_add_controlled_object_relationship($_parentObject, $_childObject, $_childField);
        $this->assertTrue($return1, "Failed to add initial relationship for duplicates");
        $this->assertTrue($return2, "Failed to add second relationship for duplicates");        
        $this->assertTrue($return1==$return2, "Failed to allow duplicate calls to add_controlled_object_relationship with same return ID");
        return $return1;
    }
        
    function test_get_controlled_object_relationship($parentObject=false,$childObject=false) {
        if ($parentObject===false) { $parentObject=$this->controlled_objectName."Parent"; }
        if (!$childObject) { $childObject=$this->controlled_objectName."Child"; }

        if ($parentObject!==NULL) {
            $parentid = $this->test_get_controlled_object($parentObject);
            $parentid=$parentid['ControlledObject_id'];
            $this->assertTrue($parentid,"Failed to find controlled object $parentObject for parent.");
        } else { $parentid = false; }
        
        $childid = $this->test_get_controlled_object($childObject);
        $childid = $childid['ControlledObject_id'];
        $this->assertTrue($childid,"Failed to find controlled object $childObject for child.");
        
       $result = $this->acl->get_controlled_object_relationship($parentid, $childid);
       $this->assertTrue($result,"Failed to find relationship with parent $parentObject to child $childObject");
       $this->assertTrue(is_array($result), "Controlled Object Relationship output should be array (failed)"); 
               
        return $result;
    }
    
    function test_delete_controlled_object_relationship($_parentObject=false,$_childObject=false) {
        if ($_parentObject===false) { $parentObject = $this->controlled_objectName."Parent"; } 
        else { $parentObject = $_parentObject; }
        if (!$_childObject) { $childObject = $this->controlled_objectName."Child"; } else { $childObject = $_childObject; }
//        $group = $this->test_get_group($searchGroup);
//        $object = $this->test_get_controlled_object($searchObject);
        
        $CORelationship_id = $this->test_get_controlled_object_relationship($parentObject, $childObject);
        $CORelationship_id = $CORelationship_id['CORelationship_id'];
        $this->assertTrue($CORelationship_id,"Failed to locate controlled object $parentObject parent to $childObject child for delete.");
        
        $result=$this->acl->delete_controlled_object_relationship($CORelationship_id);
        $this->assertTrue($result,"Failed to delete object relationship $parentObject to child $childObject");
        
        if ($_parentObject===false) $this->test_delete_controlled_object($parentObject);
        if (!$_childObject) $this->test_delete_controlled_object($childObject);
    }
    
    function test_add_controlled_object_toplevel_relationship($_Object=false) {
        $result = $this->test_add_controlled_object_relationship(NULL, $_Object);
        return $result;
    }
    
    function test_delete_controlled_object_toplevel_relationship($_Object=false) {
        $this->test_delete_controlled_object_relationship(NULL, $_Object);
    }

    function test_add_role_permission($Role=false, $ControlledObjectRelationship=false,$Scope=false,$Permission=false) {
        if (!$Role) { $Role = $this->test_add_role(); } else { $Role = $this->test_get_role($Role); $Role=$Role['Role_id']; }
        if (!$ControlledObjectRelationship) { $ControlledObjectRelationship = $this->test_add_controlled_object_relationship(); }
        if (!$Scope) { $Scope = $this->scope; }
        if (!$Permission) { $Permission = $this->permission; }
    
        $result=$this->acl->add_role_permission($Role, $ControlledObjectRelationship, $Scope, $Permission);
        $this->assertTrue($result,"Failed to add role permission $Permission to role $Role on Object $ControlledObjectRelationship with scope $Scope");
        return $result;
    
    }
    
    function test_get_role_permission($Role=false, $ControlledObjectRelationship=false,$Scope=false,$Permission=false, $RolePermission_id=false) {
            $Role = $this->test_get_role($Role);
            $Role_id=$Role['Role_id'];
            $Role_name=$Role['Role_name'];
        if (!$ControlledObjectRelationship) { 
            $ControlledObjectRelationship = $this->test_get_controlled_object_relationship(); 
            $CORelationship_id=$ControlledObjectRelationship['CORelationship_id'];
            $ControlledObjectRelationship_name= $CORelationship_id;
        }
        if (!$Scope) { $Scope = $this->scope; }
        if (!$Permission) { $Permission = $this->permission; }
        
        $result=$this->acl->get_role_permission($Role_id, $CORelationship_id, $Scope, $Permission, $RolePermission_id);
        $this->assertTrue($result,"Unable to find role permission with role $Role_name, obj $ControlledObjectRelationship_name scope $Scope perm $Permission id $RolePermission_id");
        $this->assertTrue(is_array($result), "Role Permission should be an array");
        return $result;
    }
    
    function test_delete_role_permission($Role=false, $_ControlledObjectRelationship=false,$Scope=false,$Permission=false) {
        if ($_ControlledObjectRelationship) { $ControlledObjectRelationship=$_ControlledObjectRelationship; } else { $ControlledObjectRelationship=false; }
        
        $RolePermission=$this->test_get_role_permission($Role, $ControlledObjectRelationship, $Scope, $Permission);
	$RolePermission=current($RolePermission);
        $result = $RolePermission['RolePermission_id'];
        $this->assertTrue($result, "Failed to find RolePermission with role $Role object rel $ControlledObjectRelationship scope $Scope Perms $Permission");
        
        $result = $this->acl->delete_role_permission($result);
        $this->assertTrue($result, "Failed to delete role permission.");
        
        if (!$Role) {
            $this->assertTrue($this->test_delete_role()!==false,"Should delete role");            
        }
        if (!$_ControlledObjectRelationship) {
            $dresult=$this->test_delete_controlled_object_relationship($ControlledObjectRelationship); 
            $this->assertTrue($dresult!==false,"Should delete controlled object relationship");
        }
        return $result;
    }
    
    function test_get_object_groups($ControlledObject=false, $on_what_id=false) {
        $Group1 = $this->groupName."object1";
        $Group2 = $this->groupName."object2";
        $ControlledObject = $this->controlled_objectName;
        $on_what_id=$this->on_what_id;

         $controlled_objectresult = $this->test_add_controlled_object($ControlledObject);
        $this->assertTrue($controlled_objectresult,"Failed to add controlled object $ControlledObject for group assignment");
        
        $groupresult1 = $this->test_add_group($Group1);
        $this->assertTrue($groupresult1, "Failed to add group $Group1 for object assignment");
        
        $result=$this->test_add_group_object($Group1, $ControlledObject, $on_what_id);
        $this->assertTrue($result, "Failed to add $ControlledObject to first group $Group1");
        
         $groupresult2 = $this->test_add_group($Group2);
        $this->assertTrue($groupresult2, "Failed to add group $Group2 for object assignment");
        
        $result=$this->test_add_group_object($Group2, $ControlledObject, $on_what_id);
        $this->assertTrue($result, "Failed to add $ControlledObject to second group $Group2");
        
        $ControlledObjectData=$this->test_get_controlled_object($ControlledObject);
        $this->assertTrue($ControlledObjectData, "Failed to find controlled object");
        $this->assertTrue(is_array($ControlledObjectData), "Controlled Object is not an array");
        
        $ControlledObjectID=$ControlledObjectData['ControlledObject_id'];
        
        $result=$this->acl->get_object_groups($ControlledObjectID, $on_what_id);
        $this->assertTrue($result, "Failed to find groups associated with object $ControlledObject");
        $this->assertTrue(is_array($result),"Group list is not an array, should be");
        $key = array_search($groupresult1,$result);
        $this->assertTrue($key!==false,"Group 1 $Group1 not found in group result");

        $key = array_search($groupresult2,$result);
        $this->assertTrue($key!==false,"Group 2 $Group2 not found in group result");
        
        
        $this->test_delete_group_object($Group1, $ControlledObject, $on_what_id);
        $this->test_delete_group_object($Group2, $ControlledObject, $on_what_id);
        
        $this->test_delete_controlled_object($ControlledObject);
        
        $this->test_delete_group($Group1);
        $this->test_delete_group($Group2);
        
        return $result;
        
    }
    //NOTE THAT THIS FUNCTION ASSUMES THAT ACTIVITY #1 IS ATTACHED TO COMPANY #1.  IF THIS IS NOT TRUE IN YOUR SYSTEM, THIS TEST WILL FAIL
    function test_get_object_groups_object_inherit() {
        $Group1 = $this->groupName."object1";
        $Group2 = $this->groupName."object2";
        $Group3 = $this->groupName."object3";
        $ControlledObject1 = $this->controlled_objectName."Company";
        $field1="company_id";
        $table1="companies";
        $data_source1=1;
        $ControlledObject2 = $this->controlled_objectName."Activity";
        $field2="activity_id";
        $table2="activities";
        $data_source2=1;

        $ChildField = $field1;
                
        $on_what_parent_id=1;
        $on_what_child_id = 1;

        $controlled_objectresult1 = $this->test_add_controlled_object($ControlledObject1,$table1,$field1,false, $data_source1);
        $this->assertTrue($controlled_objectresult1,"Failed to add controlled object $ControlledObject1 for group assignment");

        $controlled_objectresult2 = $this->test_add_controlled_object($ControlledObject2,$table2,$field2,false, $data_source2);
        $this->assertTrue($controlled_objectresult2,"Failed to add controlled object $ControlledObject2 for group assignment");
        
        $groupresult1 = $this->test_add_group($Group1);
        $this->assertTrue($groupresult1, "Failed to add group $Group1 for object assignment");
        
        $result=$this->test_add_group_object($Group1, $ControlledObject1, $on_what_parent_id);
        $this->assertTrue($result, "Failed to add $ControlledObject1 to first group $Group1");
        
        $groupresult2 = $this->test_add_group($Group2);
        $this->assertTrue($groupresult2, "Failed to add group $Group2 for object assignment");
        
        $groupresult3 = $this->test_add_group($Group3);
        $this->assertTrue($groupresult3, "Failed to add group $Group3 for object assignment");

        $result=$this->test_add_group_object($Group2, $ControlledObject1, $on_what_parent_id);
        $this->assertTrue($result, "Failed to add $ControlledObject1 to second group $Group2");

        $result=$this->test_add_group_object($Group3, $ControlledObject2, $on_what_child_id);
        $this->assertTrue($result, "Failed to add $ControlledObject2 to third group $Group3");
        
        $result = $this->test_add_controlled_object_relationship($ControlledObject1, $ControlledObject2,$ChildField);
        $this->assertTrue($result, "Failed to add $ControlledObject2 as child to $ControlledObject1");
        
                
        $ControlledObjectID=$controlled_objectresult2; 
        $result=$this->acl->get_object_groups_recursive($ControlledObjectID, $on_what_child_id);
	$this->assertTrue($result, "Failed to find groups associated with object $ControlledObject2");
        $this->assertTrue(is_array($result),"Group list is not an array, should be");
        $key = array_search($groupresult1,$result);
        $this->assertTrue($key!==false,"Group 1 $Group1 not found in group result");

        $key = array_search($groupresult2,$result);
        $this->assertTrue($key!==false,"Group 2 $Group2 not found in group result");
        
        $key = array_search($groupresult3,$result);
        $this->assertTrue($key!==false,"Group 3 $Group3 not found in group result");
                
        $this->test_delete_group_object($Group1, $ControlledObject1, $on_what_parent_id);
        $this->test_delete_group_object($Group2, $ControlledObject1, $on_what_parent_id);
        $this->test_delete_group_object($Group3, $ControlledObject2, $on_what_child_id);
        
        $this->test_delete_controlled_object_relationship($ControlledObject1, $ControlledObject2);
        
        $this->test_delete_controlled_object($ControlledObject1);
        $this->test_delete_controlled_object($ControlledObject2);
        
        $this->test_delete_group($Group1);
        $this->test_delete_group($Group2);
        $this->test_delete_group($Group3);
        
        return $result;
        
    }

    
    function test_get_group_objects($_Group=false) {
        $Object1 = $this->controlled_objectName."object1";
        $Object2 = $this->controlled_objectName."object2";
        if (!$_Group) { $Group = $this->controlled_objectName;} else { $Group = $_Group; }
        $on_what_id=$this->on_what_id;

        $controlled_objectresult1 = $this->test_add_controlled_object($Object1);
        $this->assertTrue($controlled_objectresult1,"Failed to add controlled object $Object1 for group assignment");

        $controlled_objectresult2 = $this->test_add_controlled_object($Object2);
        $this->assertTrue($controlled_objectresult2,"Failed to add controlled object $Object2 for group assignment");
        
        $groupresult = $this->test_add_group($Group);
        $this->assertTrue($groupresult, "Failed to add group $Group for object assignment");
                
        $result=$this->test_add_group_object($Group, $Object1, $on_what_id);
        $this->assertTrue($result, "Failed to add first object $Object1 to group $Group");

         $result=$this->test_add_group_object($Group, $Object2, $on_what_id);
        $this->assertTrue($result, "Failed to add second object $Object2 to group $Group");
                
        $result=$this->acl->get_group_objects($groupresult);
        $this->assertTrue($result, "Failed to find objects associated with group $Group");
        $this->assertTrue(is_array($result),"Object list is not an array, should be");
        
        $found1=false;
        $found2=false;
        foreach ($result as $key=>$GroupMember) {
            if ($GroupMember['ControlledObject_id']==$controlled_objectresult1) { $found1=true; }
            if ($GroupMember['ControlledObject_id']==$controlled_objectresult2) { $found2=true; }
        }
        $this->assertTrue($found1,"Object 1 $Object1 not found in group result");
        $this->assertTrue($found2,"Object 2 $Object2 not found in group result");
        
        
        $this->test_delete_group_object($Group, $Object1, $on_what_id);
        $this->test_delete_group_object($Group, $Object2, $on_what_id);
        
        $this->test_delete_controlled_object($Object1);
        $this->test_delete_controlled_object($Object2);
        
        $this->test_delete_group($Group);
        
        return $result;
        
    }

    function test_get_group_objects_inherit($_Group1=false,$_Group2=false) {
        $Object1 = $this->controlled_objectName."object1";
        $Object2 = $this->controlled_objectName."object2";
        if (!$_Group1) { $Group1 = $this->controlled_objectName."Parent";} else { $Group1 = $_Group1; }
        if (!$_Group2) { $Group2 = $this->controlled_objectName."Child";} else { $Group2 = $_Group2; }
        $on_what_id=$this->on_what_id;

        $controlled_objectresult1 = $this->test_add_controlled_object($Object1);
        $this->assertTrue($controlled_objectresult1,"Failed to add controlled object $Object1 for group assignment");

        $controlled_objectresult2 = $this->test_add_controlled_object($Object2);
        $this->assertTrue($controlled_objectresult2,"Failed to add controlled object $Object2 for group assignment");
        
        $groupresult1 = $this->test_add_group($Group1);
        $this->assertTrue($groupresult1, "Failed to add group $Group1 for object assignment");

        $groupresult2 = $this->test_add_group($Group2);
        $this->assertTrue($groupresult2, "Failed to add group $Group2 for object assignment");
                        
        $result=$this->test_add_group_object($Group1, $Object1, $on_what_id);
        $this->assertTrue($result, "Failed to add parent object $Object1 to group $Group1");

         $result=$this->test_add_group_object($Group2, $Object2, $on_what_id);
        $this->assertTrue($result, "Failed to add child object $Object2 to group $Group2");
        
        $result=$this->test_add_group_group($Group1, $Group2);
                
        $result=$this->acl->get_group_objects($groupresult1);
        $this->assertTrue($result, "Failed to find objects associated with group $Group1");
        $this->assertTrue(is_array($result),"Object list is not an array, should be");
        
        $found1=false;
        $found2=false;
        foreach ($result as $key=>$GroupMember) {
            if ($GroupMember['ControlledObject_id']==$controlled_objectresult1) { $found1=true; }
            if ($GroupMember['ControlledObject_id']==$controlled_objectresult2) { $found2=true; }
        }
        $this->assertTrue($found1,"Object parent $Object1 not found in group result");
        $this->assertTrue($found2,"Object child $Object2 not found in group result");

        $this->test_delete_group_group($Group1, $Group2);        
        
        $this->test_delete_group_object($Group1, $Object1, $on_what_id);
        $this->test_delete_group_object($Group2, $Object2, $on_what_id);
        
        $this->test_delete_controlled_object($Object1);
        $this->test_delete_controlled_object($Object2);
        
        $this->test_delete_group($Group1);
        $this->test_delete_group($Group2);
        
        return $result;
        
    }
    
    function test_get_role_users() {
        $user1 = $this->user;
        $user2 = $this->user+1;
        $role = $this->roleName;
        $group = $this->groupName;
        $roleresult = $this->test_add_role($role);
        $groupresult = $this->test_add_group($group);
        $add1 = $this->test_add_group_user($group, $role, $user1);
        $add2 = $this->test_add_group_user($group, $role, $user2);
        
        $result = $this->acl->get_role_users($roleresult, false);
        $this->assertTrue($result, "failed to find users for role $role");
        $this->assertTrue(is_array($result),"user list is not an array, should be");
        if (is_array($result)) {
            $ret=false;
            $ret2=false;
            foreach ($result as $cur) {
                if (!$ret)
                    $ret = ($cur['user_id']==$user1);
                if (!$ret2)
                    $ret2 = ($cur['user_id']==$user2);
             }
             $this->assertTrue($ret, "Failed to find user $user1 in role $role");
             $this->assertTrue($ret2, "Failed to find user $user2 in role $role");
        }
            
        $this->test_delete_group_user($group, $role, $user1);
        $this->test_delete_group_user($group, $role, $user2);
    
        $this->test_delete_role($role);
        
        $this->test_delete_group($group);
        return $result;
    }   
     
    function test_get_user_roles() {
        $user = $this->user;
        $role1 = $this->roleName . "Manager";
        $role2 = $this->roleName . "Associate";
        $group = $this->groupName;
        
        $roleresult1 = $this->test_add_role($role1);
        $this->assertTrue($roleresult1, "adding role $role1 for user assignment failed");

        $roleresult2 = $this->test_add_role($role2);
        $this->assertTrue($roleresult2, "adding role $role2 for user assignment failed");

        $groupresult = $this->test_add_group($group);
        $this->assertTrue($groupresult, "adding group $group for user assignment failed");
        
        $add1 = $this->test_add_group_user($group, $role1, $user);
        $this->assertTrue($add1, "adding user $user to group $group with role $role1 failed for search");
        
        $add2 = $this->test_add_group_user($group, $role2, $user);
        $this->assertTrue($add2, "adding user $user to group $group with role $role2 failed for search");
        
        $result = $this->acl->get_user_roles($groupresult, $user);
        $this->assertTrue($result, "failed to find roles for user $user in group $group");
        $this->assertTrue(is_array($result),"user role list is not an array, should be");
        
        $this->assertTrue(array_search($roleresult1,$result)!==false,"Failed to find role $role1 in list");
        $this->assertTrue(array_search($roleresult2,$result)!==false,"Failed to find role $role2 in list");
        
        $this->test_delete_group_user($group, $role1, $user);
        $this->test_delete_group_user($group, $role2, $user);
        
        $this->test_delete_role($role1);
        $this->test_delete_role($role2);
        
        $this->test_delete_group($group);
        return $result;
    }    
    
   function test_get_user_roles_inherit() {
        $user = $this->user;
        $role1 = $this->roleName . "Manager";
        $role2 = $this->roleName . "Associate";
        $role3 = $this->roleName . "TopManager";
        
        $group3 = $this->groupName . "Bottom";
        $group2 = $this->groupName . "Middle";
        $group1 = $this->groupName . "Top";
        
        $roleresult1 = $this->test_add_role($role1);
        $this->assertTrue($roleresult1, "adding role $role1 for user assignment failed");

        $roleresult2 = $this->test_add_role($role2);
        $this->assertTrue($roleresult2, "adding role $role2 for user assignment failed");

        $roleresult3 = $this->test_add_role($role3);
        $this->assertTrue($roleresult3, "adding role $role3 for user assignment failed");

        $groupresult1 = $this->test_add_group($group1);
        $this->assertTrue($groupresult1, "adding bottom group $group1 for user assignment failed");
        
        $groupresult2 = $this->test_add_group($group2);
        $this->assertTrue($groupresult2, "adding top group $group2 for user assignment failed");

        $groupresult3 = $this->test_add_group($group3);
        $this->assertTrue($groupresult2, "adding top group $group3 for user assignment failed");
        
        $add1 = $this->test_add_group_user($group1, $role1, $user);
        $this->assertTrue($add1, "adding user $user to group $group1 with role $role1 failed for search");
        
        $add2 = $this->test_add_group_user($group2, $role2, $user);
        $this->assertTrue($add2, "adding user $user to group $group2 with middle role $role2 failed for search");

        $add2 = $this->test_add_group_user($group3, $role3, $user);
        $this->assertTrue($add2, "adding user $user to group $group3 with super role $role3 failed for search");
        
        $gadd = $this->test_add_group_group($group1, $group2);
        $this->assertTrue($gadd, "adding group $group1 to group $group2 failed");

        $gadd2 = $this->test_add_group_group($group2, $group3);
        $this->assertTrue($gadd2, "adding group $group3 to group $group2 failed");
                
        $result = $this->acl->get_user_roles($groupresult3, $user);
        $this->assertTrue($result, "failed to find roles for user $user in group $group3");
        $this->assertTrue(is_array($result),"user role list is not an array, should be");
        
        $this->assertTrue(array_search($roleresult1,$result)!==false,"Failed to find role $role1 in list");
        $this->assertTrue(array_search($roleresult2,$result)!==false,"Failed to find middle inhereted role $role2 in list");
        $this->assertTrue(array_search($roleresult3,$result)!==false,"Failed to find top inherented role $role3 in list");
        
        $this->test_delete_group_user($group1, $role1, $user);
        $this->test_delete_group_user($group2, $role2, $user);
        $this->test_delete_group_user($group3, $role3, $user);
                
        $this->test_delete_group_group($group1, $group2);
        $this->test_delete_group_group($group2, $group3);
        
        $this->test_delete_role($role1);
        $this->test_delete_role($role2);
        $this->test_delete_role($role3);
        
        $this->test_delete_group($group1);
        $this->test_delete_group($group2);
        $this->test_delete_group($group3);
        return $result;
    }    

   function test_get_user_roles_by_array() {
        $user = $this->user;
        $role1 = $this->roleName . "Manager";
        $role2 = $this->roleName . "Associate";
        $role3 = $this->roleName . "TopManager";
        
        $group3 = $this->groupName . "Bottom";
        $group2 = $this->groupName . "Middle";
        $group1 = $this->groupName . "Top";
        
        $roleresult1 = $this->test_add_role($role1);
        $this->assertTrue($roleresult1, "adding role $role1 for user assignment failed");

        $roleresult2 = $this->test_add_role($role2);
        $this->assertTrue($roleresult2, "adding role $role2 for user assignment failed");

        $roleresult3 = $this->test_add_role($role3);
        $this->assertTrue($roleresult3, "adding role $role3 for user assignment failed");

        $groupresult1 = $this->test_add_group($group1);
        $this->assertTrue($groupresult1, "adding bottom group $group1 for user assignment failed");
        
        $groupresult2 = $this->test_add_group($group2);
        $this->assertTrue($groupresult2, "adding top group $group2 for user assignment failed");

        $groupresult3 = $this->test_add_group($group3);
        $this->assertTrue($groupresult2, "adding top group $group3 for user assignment failed");
        
        $add1 = $this->test_add_group_user($group1, $role1, $user);
        $this->assertTrue($add1, "adding user $user to group $group1 with role $role1 failed for search");
        
        $add2 = $this->test_add_group_user($group2, $role2, $user);
        $this->assertTrue($add2, "adding user $user to group $group2 with middle role $role2 failed for search");

        $add2 = $this->test_add_group_user($group3, $role3, $user);
        $this->assertTrue($add2, "adding user $user to group $group3 with super role $role3 failed for search");
        
        $groups = array( $groupresult1, $groupresult2, $groupresult3);
        $result = $this->acl->get_user_roles_by_array($groups, $user);
        $this->assertTrue($result, "failed to find roles for user $user in group $group3");
        $this->assertTrue(is_array($result),"user role list is not an array, should be");
        $ret=$result;
        $result = $result['Roles'];
        $this->assertTrue(array_search($roleresult1,$result)!==false,"Failed to find role $role1 in list");
        $this->assertTrue(array_search($roleresult2,$result)!==false,"Failed to find middle inhereted role $role2 in list");
        $this->assertTrue(array_search($roleresult3,$result)!==false,"Failed to find top inherented role $role3 in list");
        
        $this->test_delete_group_user($group1, $role1, $user);
        $this->test_delete_group_user($group2, $role2, $user);
        $this->test_delete_group_user($group3, $role3, $user);
        
        $this->test_delete_role($role1);
        $this->test_delete_role($role2);
        $this->test_delete_role($role3);
        
        $this->test_delete_group($group1);
        $this->test_delete_group($group2);
        $this->test_delete_group($group3);
        return $ret;
    }
        
    //NOTE THAT THIS FUNCTION ASSUMES THAT ACTIVITY #1 EXISTS IN THE SYSTEM.  IF THIS IS NOT TRUE IN YOUR SYSTEM, THIS TEST WILL FAIL
    function test_get_object_relationship_parent($_ParentControlledObject=false, $_ControlledObject=false, $on_what_id=false, $_ControlledObjectRelationship=false, $_ChildField=false) {
        if (!$_ParentControlledObject) { 
            $ParentControlledObject=$this->controlled_objectName."Company"; 
            $ParentControlledObject_id=$this->test_add_controlled_object($ParentControlledObject,"companies","company_id",false, 1); 
        } else { 
            $ParentControlledObject=$_ParentControlledObject; 
            $ParentControlledObjectData = $this->test_get_controlled_object($ParentControlledObject);
            $ParentControlledObject_id=$ParentControlledObjectData['ControlledObject_id']; 
        }
        if (!$on_what_id) { $on_what_id=1; }
        if (!$_ControlledObject) { 
            $ControlledObject=$this->controlled_objectName."Activity";
            $ControlledObject_id=$this->test_add_controlled_object($ControlledObject,"activities","activity_id",false, 1); 
        } else { 
            $ControlledObject=$_ControlledObject; 
            $ControlledObjectData = $this->test_get_controlled_object($ControlledObject);
            $ControlledObject_id=$ControlledObjectData['ControlledObject_id']; 
        }
        if (!$_ChildField) { 
            $ChildField="company_id";
        } else { $ChildField=$_ChildField; }
        if (!$_ControlledObjectRelationship) { 
            $CORelationship_id=$this->test_add_controlled_object_relationship($ParentControlledObject, $ControlledObject, $ChildField); 
        } else {
            $ControlledObjectRelationshipData = $this->test_get_controlled_object_relationship($ParentControlledObject, $ControlledObject);
            $CORelationship_id = $ControlledObjectRelationshipData['CORelationship_id'];
        }

        $result = $this->acl->get_object_relationship_parent($ControlledObject_id, $on_what_id);
        $this->assertTrue($result, "Failed to find parent information for object $ControlledObject id $on_what_id");
        $this->assertTrue(is_array($result), "Parent information should be an array");
         if (is_array($result)) {  
            $relationshipParent = current($result);    
            $this->assertTrue(is_array($relationshipParent), "Parent information record should be an array");
            $this->assertTrue($relationshipParent['ControlledObject_id']==$ParentControlledObject_id,"Parent controlled object not found in record");
        }
        $this->test_delete_controlled_object_relationship($ParentControlledObject, $ControlledObject);
       $this->test_delete_controlled_object($ParentControlledObject);
       $this->test_delete_controlled_object($ControlledObject);
        return $result;
    }

    function test_get_permissions_user_basic() {
        $ControlledObject = $this->controlled_objectName;
        $ControlledObject_id = $this->test_add_controlled_object($ControlledObject);

        $Role = $this->roleName;
        $Role_id = $this->test_add_role($Role);
        
        $Group = $this->groupName;
        $Group_id = $this->test_add_group($Group);

        $User_id = $this->user;
        
        $on_what_id = $this->on_what_id;
        
        $Scope = $this->scope;
        
        $Permission = $this->permission;
        $Permission2 = $this->permission+1;
        $Permission3 = $this->permission+2;
        
        
        $CORelationship_id = $this->test_add_controlled_object_relationship(NULL, $ControlledObject);
        $this->assertTrue($CORelationship_id, "Failed to add top level controoled object relationship for permission");
        
        $GroupMember_id=$this->test_add_group_object($Group, $ControlledObject, $on_what_id);
        $this->assertTrue($GroupMember_id, "Failed to add $ControlledObject to group $Group on $on_what_id");
        
        
        //Add permissions 1 (Read) and 2 (Create) to test roles/objects with world scope
        $RolePermission_id=$this->test_add_role_permission($Role, $CORelationship_id,$Scope,$Permission);
        $this->assertTrue($RolePermission_id, "Failed to add $CORelationship_id to role $Role with permission $Permission scoped at $Scope");

        $RolePermission_id=$this->test_add_role_permission($Role, $CORelationship_id,$Scope,$Permission2);
        $this->assertTrue($RolePermission_id, "Failed to add $CORelationship_id to role $Role with permission $Permission2 scoped at $Scope");
        
        //Add user to role in group
        $GroupUser_id = $this->test_add_group_user($Group,$Role,$User_id);
        $this->assertTrue($GroupUser_id, "Failed to add user $User_id to group $Group with role $Role for permission test");

        //check for permissions on test object with test id, for user, check for permissions 1 (assigned) and 3 (not assigned)
        // shows the behavior of returning all permissions on the object for the user if one of the searched for permissions //is not found
        $result = $this->acl->get_permissions_user($ControlledObject_id, $on_what_id, $User_id,false,array($Permission,$Permission3), false, 'World'); //, $Permission);
        $this->assertTrue($result, "Failed to get correct permission on object");

        $this->assertTrue(is_array($result), "Failed to get a list of permissions");
        if ($result) {
            $this->assertTrue(array_search($Permission, $result)!==false,"Failed to find searched for permission in list");
    
            //finds second permissions even though it is not searched for, since all permissions available are returned
            $this->assertTrue(array_search($Permission2, $result)!==false,"Failed to find searched for permission2 in list");
        }                
        $this->test_delete_group_user($Group, $Role, $User_id);        
        $this->test_delete_role_permission($Role, $CORelationship_id, $Scope, $Permission);
        $this->test_delete_role_permission($Role, $CORelationship_id, $Scope, $Permission2);
        $this->test_delete_group_object($Group, $ControlledObject, $on_what_id);
        $this->test_delete_controlled_object_relationship(NULL, $ControlledObject);
        $this->test_delete_group($Group);
        $this->test_delete_role($Role);
        $this->test_delete_controlled_object($ControlledObject);
        
        
    }
        
    function test_get_field_list() {
        $ControlledObject = $this->controlled_objectName.'Company';
        $ControlledObject_id = $this->test_add_controlled_object($ControlledObject, 'companies','company_id',false, 1);        
    
        $ret=$this->acl->get_field_list($ControlledObject_id, array());
//        print_r($ret);
        $this->assertTrue($ret, "Failed to get return from get_field_list for $ControlledObject_id with no restrictions");
        $this->assertTrue(is_array($ret), "Field list does not return array, should do so");

        $this->test_delete_controlled_object($ControlledObject);
        
    }
    
    function test_get_restricted_object_list_basic() {
        $ControlledObject = $this->controlled_objectName.'Company';
        $ControlledObject_id = $this->test_add_controlled_object($ControlledObject, 'companies','company_id',false, 1);

        $Role = $this->roleName;
        $Role_id = $this->test_add_role($Role);
        
        $Group = $this->groupName;
        $Group_id = $this->test_add_group($Group);

        $User_id = $this->user;
        
        $on_what_id = $this->on_what_id;
        
        $Scope = 'World';
        
        $Permission = $this->permission;
        $Permission2 = $this->permission+1;
        $Permission3 = $this->permission+2;
        
        
        $CORelationship_id = $this->test_add_controlled_object_relationship(NULL, $ControlledObject);
        $this->assertTrue($CORelationship_id, "Failed to add top level controlled object relationship for permission");
        
        $GroupMember_id=$this->test_add_group_object($Group, $ControlledObject, $on_what_id);
        $this->assertTrue($GroupMember_id, "Failed to add $ControlledObject to group $Group on $on_what_id");
        
        
        //Add permissions 1 (Read) and 2 (Create) to test roles/objects with world scope
        $RolePermission_id=$this->test_add_role_permission($Role, $CORelationship_id,$Scope,$Permission);
        $this->assertTrue($RolePermission_id, "Failed to add $CORelationship_id to role $Role with permission $Permission scoped at $Scope");

        $RolePermission_id=$this->test_add_role_permission($Role, $CORelationship_id,$Scope,$Permission2);
        $this->assertTrue($RolePermission_id, "Failed to add $CORelationship_id to role $Role with permission $Permission2 scoped at $Scope");
        
        //Add user to role in group
        $GroupUser_id = $this->test_add_group_user($Group,$Role,$User_id);
        $this->assertTrue($GroupUser_id, "Failed to add user $User_id to group $Group with role $Role for permission test");

        //check for permissions on test object with test id, for user, check for permissions 1 (assigned) and 3 (not assigned)
        // shows the behavior of returning all permissions on the object for the user if one of the searched for permissions //is not found
        $result = $this->acl->get_restricted_object_list($ControlledObject_id, $User_id);
        $this->assertTrue($result, "Failed to correctly list objects for $ControlledObject_id user $User_id");
        $this->assertTrue(is_array($result), "Failed to get an array as return");
        $this->assertTrue(is_array($result['controlled_objects']) OR $result['ALL'], "Failed to get a list of objects");
//        echo "<pre>"; print_r($result); echo "</pre>";
//        $this->assertTrue(array_search($Permission, $result)!==false,"Failed to find searched for permission in list");

        //finds second permissions even though it is not searched for, since all permissions available are returned
//        $this->assertTrue(array_search($Permission2, $result)!==false,"Failed to find searched for permission2 in list");
                
        $this->test_delete_group_user($Group, $Role, $User_id);        
        $this->test_delete_role_permission($Role, $CORelationship_id, $Scope, $Permission);
        $this->test_delete_role_permission($Role, $CORelationship_id, $Scope, $Permission2);
        $this->test_delete_group_object($Group, $ControlledObject, $on_what_id);
        $this->test_delete_controlled_object_relationship(NULL, $ControlledObject);
        $this->test_delete_group($Group);
        $this->test_delete_role($Role);
        $this->test_delete_controlled_object($ControlledObject);
        
        
    }

    function test_get_permission_user_object($object=false, $User_id=false, $Permission='Read') {
        $ControlledObject = $this->controlled_objectName.'Company';
        $ControlledObject_id = $this->test_add_controlled_object($ControlledObject, 'companies','company_id',false, 1);        
        $Role = $this->roleName;
        $Role_id = $this->test_add_role($Role);
        
        $Group = $this->groupName;
        $Group_id = $this->test_add_group($Group);

        if (!$User_id)
            $User_id = $this->user;
    
        $Scope = 'World';
        if (!is_numeric($Permission)) { $PermissionData=$this->acl->get_permission($Permission); $Permission=$PermissionData['Permission_id']; }
        
        $CORelationship_id = $this->test_add_controlled_object_relationship(NULL, $ControlledObject);
        $this->assertTrue($CORelationship_id, "Failed to add top level controlled object relationship for permission");
                
        //Add user to role in group
        $GroupUser_id = $this->test_add_group_user($Group,$Role,$User_id);
        $this->assertTrue($GroupUser_id, "Failed to add user $User_id to group $Group with role $Role for permission test");
        
        //Add permissions 1 (Read) and 2 (Create) to test roles/objects with world scope
        $RolePermission_id=$this->test_add_role_permission($Role, $CORelationship_id,$Scope,$Permission);
        $this->assertTrue($RolePermission_id, "Failed to add $CORelationship_id to role $Role with permission $Permission scoped at $Scope");
        $ret=$this->acl->get_permission_user_object($ControlledObject_id, $User_id, false, array($Permission));
        $this->assertTrue($ret, "Failed to find any permissions on object $ControlledObject for user $User_id");
        $this->assertTrue(array_search($Permission, $ret)!==false,"Failed to find permission $Permission on object $ControlledObject for user $User_id");
               
        $this->test_delete_group_user($Group, $Role, $User_id);        
        $this->test_delete_role_permission($Role, $CORelationship_id, $Scope, $Permission);
        $this->test_delete_controlled_object_relationship(NULL, $ControlledObject);
        $this->test_delete_group($Group);
        $this->test_delete_role($Role);
        $this->test_delete_controlled_object($ControlledObject);
    
    }
     
    
    function test_acl_callback_setup($authCallbacks=false, $secondCallback=false) {
        if ($authCallbacks) {
            $authCallbacks=array('test_xrms_acl_auth');
            $secondCallback='second_test_xrms_acl';
        }
        $test_acl=new xrms_acl(false, false, false, $authCallbacks);
        $ret=$test_acl->add_authCallback($secondCallback);
        $authCallbacks[]=$secondCallback;
        $this->assertTrue($ret, "Failed to add $secondCallback auth callback to ACL object");
        
        $callbacks=$test_acl->get_authCallbacks();
        foreach ($authCallbacks as $ac) {
            $ck=array_search($ac, $callbacks);
            $this->assertTrue($ck!==false, "Failed to find callback $ac in ACL authCallbacks collection");
            if ($ck!==false) {
                unset($callbacks[$ck]);
            }
            $ret=$test_acl->remove_authCallback($ac);
            $this->assertTrue($ret, "Failed to remove auth callback from ACL object");
        }
        $this->assertTrue(count($callbacks)==0, "Found more callback options than passed in");
        unset($test_acl);
    }

    function test_get_group_list() {
	$grouplist=get_group_list($this->con);
	$this->assertTrue($grouplist, "Failed to get a group list from wrapper function");
    }
    
 }

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
 * $Log: xrms_acl_test.php,v $
 * Revision 1.14  2006/07/25 20:39:22  vanmer
 * - ensure ACL tests succeed on default install
 *
 * Revision 1.13  2006/01/27 13:37:06  vanmer
 * - changed ACL to require array input for actions (permissions)
 * - removed check for array of permissions, now is always an array
 * - changed test and wrapper to properly pass permissions as an array to ACL object
 *
 * Revision 1.12  2005/10/03 18:25:45  vanmer
 * - changed to only call ACL test from central xrms tests
 *
 * Revision 1.11  2005/08/18 19:49:43  vanmer
 * - added test for group list
 *
 * Revision 1.10  2005/07/30 01:31:04  vanmer
 * - added test for list of groups on group member criteria search
 *
 * Revision 1.9  2005/07/30 00:53:32  vanmer
 * - part of addition of ACL's Group Member by Criteria functionality
 * - added tests for new ACL group member by criteria add/retrieve/query
 * - changed group member addition to allow new group member fields
 *
 * Revision 1.8  2005/07/22 23:37:56  vanmer
 * - altered tests to assume activity_id 1 is attached to company_id 1
 *
 * Revision 1.7  2005/07/22 23:14:14  vanmer
 * - added tests for new method of passing database connections to the ACl
 *
 * Revision 1.6  2005/07/07 19:38:57  vanmer
 * - added test for newly created function to query for users in a role
 *
 * Revision 1.5  2005/06/24 23:51:57  vanmer
 * - added tests to add objects and relationships twice to ensure that second addition returns same
 * object/relationship
 * - added checks before running tests which will result in PHP errors
 *
 * Revision 1.4  2005/05/13 21:22:37  vanmer
 * - added checks in tests to ensure that some code/subtests only get run when initial test succeeds
 *
 * Revision 1.3  2005/02/15 19:42:51  vanmer
 * - updated to reflect new output of restricted object list
 * - updated to reflect new fieldnames
 *
 * Revision 1.2  2005/01/25 05:28:20  vanmer
 * - added tests for newly added data source manipulation functions
 * - added parameters for changed ACL controlled object functions
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