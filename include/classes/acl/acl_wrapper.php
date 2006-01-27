<?php
/**
 * ACL wrapper functions for XRMS
 * These are the preferred methods for interfacing with the XRMS ACL
 *
 * Copyright (c) 2005 Foundation Technology Services, Inc.
 * All Rights Reserved.
 *
 * @package ACL
 * $Id: acl_wrapper.php,v 1.29 2006/01/27 13:37:05 vanmer Exp $
 */

define('ACL_PATH',$include_directory.'classes/acl/');
require_once(ACL_PATH.'xrms_acl.php');
require_once(ACL_PATH.'xrms_acl_config.php');
require_once(ACL_PATH.'acl_install.php');
$acl_options=$options;

/*****************************************************************************/
/**
  *
  * Returns an xrms_acl object with provided parameters set
  *
  * @param array $access_info with array of datasource auth info, indexed by datasource name
  * @param adbdbconnection $con with connection to database where base ACL tables exist
  * @param $callbacks optionally providing an array of strings with names of callback functions to provide ACL database or authentication
  * @param $context optionally providing context to use as default datasource when datasource is not provided on an object
  * @return xrms_acl object configured with passed in parameters
  *
**/
function get_acl_object($access_info=false, $con=false, $callbacks=false, $context='default') {
    if (!$callbacks) {
        $callbacks='xrms_acl_auth_callback';
    }
      $acl = new xrms_acl($access_info, $con, $callbacks, $context );
      return $acl;
}

/*****************************************************************************/
/**
  *
  * Returns an adodbconnection based on a datasource name, or currently set ACL database
  * This function is intended to be called in the ACL administration pages, allowing them to control whichever ACL has been selected with the acl_datasource_name
  * request variable.
  *
  * @param string $datasource with datasource name to use for dbconnection
  * @return adodbconnection handle for database requested
  *
**/
function get_acl_dbconnection($datasource=false) {
    //see if acl_datasource_name has been added to the request variables or the session
    getGlobalVar($acl_datasource_name,'acl_datasource_name');

    //if it has been explicitly set in get or post, then use those preferentially over the session variable
    if ($_GET['acl_datasource_name']) $acl_datasource_name=$_GET['acl_datasource_name'];
    if ($_POST['acl_datasource_name']) $acl_datasource_name=$_POST['acl_datasource_name'];

    //set the datasource name into the session
    $_SESSION['acl_datasource_name']=$acl_datasource_name;

    //Check to see if datasource has been set as a parameter, if not then use requested datasource from session/get/post
    if (!$datasource) $datasource=$acl_datasource_name;

    //if we have nothing yet, use the default datasource (XRMS)
    if (!$datasource) $datasource='default';
    
    //get the ACL object
    $acl=get_acl_object();
    //return a database handle to the given datasource
    return $acl->get_object_adodbconnection($datasource);
}

/*****************************************************************************/
/**
  *
  * Retrieves the list of Group names in an array, keyed by Group_id
  *
  * @param adodbconnection $con with handle to database with ACL tables
  * @return array with group names, keyed by Group_id
  *
**/
function get_group_list($con=false) {
   if (!$con) $con=get_acl_dbconnection();
   $sql = "SELECT Group_name, Group_id FROM Groups";
   $rst = $con->execute($sql);
   if (!$rst) { db_error_handler($con, $sql); return false; }
   if ($rst->EOF) return false;
   while (!$rst->EOF) {
	$group_list[$rst->fields['Group_id']]=$rst->fields['Group_name'];
	$rst->movenext();
   }
   return $group_list;
}

/*****************************************************************************/
/**
  *
  * This function is intended to create an HTML widget with a select list of groups in the system
  *
  * @param adodbconnection $con with handle to database with ACL tables
  * @param string $fieldname with HTML form fieldname, defaults to 'Group_id'
  * @param integer $group with identifier of the group to show as selected initially
  * @param string $id with HTML element identifier, defaults to ''
  * @param boolean $blank_first determining if a blank entry should be displayed, defaults to false, do not add blank entry
  * @param string $attributes with extra HTML attributes, to be added inside the select element
  * @param integer $size with length of HTML element
  * @return string with HTML menu of groups
  *
**/
function get_group_select($con=false, $fieldname='Group_id', $group=false, $id='', $blank_first=false, $attributes='', $size=0) {
    if(!$con) $con=get_acl_dbconnection();
    $sql = "SELECT Group_name, Group_id FROM Groups";
    $rst = $con->execute($sql);
    $attr='';
    if ($id) $attr="id=\"$id\"";
    if ($attributes) $attr.=" $attributes";
    $menu = $rst->getMenu2($fieldname, $group, $blank_first, false, $size, $attr); 
    return $menu;
}

/*****************************************************************************/
/**
  *
  * This function retrieves criteria for identifying controlled object which are members of a group member, using the GroupMember db identifier
  *
  * @param adodbconnection $con with handle to database with ACL tables
  * @param integer $GroupMember_id specifying which GroupMember entry for which to retrieve criteria
  * @return array with group member criteria, intended for use in a SQL statement
  *
**/
function get_acl_group_member_criteria($con=false, $GroupMember_id) {
    global $acl_options;
    if (!$GroupMember_id) return false;
    $acl = get_acl_object($acl_options, $con);
    $ret=$acl->get_group_member_criteria($GroupMember_id);
    return $ret;
}

/*****************************************************************************/
/**
  *
  * This function removes criteria for identifying controlled object which are members of a group member, using the GroupMemberCriteria_id db identifier
  *
  * @param adodbconnection $con with handle to database with ACL tables
  * @param integer $GroupMemberCriteria_id specifying which GroupMemberCriteria entry to remove
  * @return boolean indicating success of removal
  *
**/
function remove_group_member_criteria($con=false, $GroupMemberCriteria_id) {
    global $acl_options;
    if (!$GroupMemberCriteria_id) return false;
    $acl = get_acl_object($acl_options, $con);
    $ret=$acl->delete_group_member_criteria(false, false, $GroupMemberCriteria_id);
    return $ret;
}

/*****************************************************************************/
/**
  *
  * This function is used to add criteria for identifying controlled object membership in a group
  * A GroupMember entry can have multiple group member criteria, which end up AND'd together to provide the WHERE clause for a sql statement
  *
  * @param adodbconnection $con with handle to database with ACL tables
  * @param integer $GroupMember_id specifying which GroupMember entry to add criteria for
  * @param string $criteria_fieldname specifying what field to restrict
  * @param string $criteria_value specifying the value to restrict fieldname to
  * @param string $criteria_operator with comparison operator, defaults to '='
  * @return integer $GroupMemberCriteria_id with database identifier of newly added crteria, or false add failed
  *
**/
function add_group_member_criteria($con, $GroupMember_id, $criteria_fieldname, $criteria_value, $criteria_operator='=') {
    global $acl_options;
    $acl = get_acl_object($acl_options, $con);
    if ($GroupMember_id AND $criteria_fieldname AND $criteria_value AND $criteria_operator) {
        $ret = $acl->add_group_member_criteria($GroupMember_id, $criteria_fieldname, $criteria_value, $criteria_operator);   
        return $ret;
    }  else return false;
}

/*****************************************************************************/
/**
  *
  * This function deletes a group member, with all related criteria items, using GroupMember_id database identifier
  *
  * @param adodbconnection $con with handle to database with ACL tables
  * @param integer $GroupMember_id specifying which GroupMember entry to remove
  * @return boolean indicating success of removal
  *
**/
function delete_group_member($con, $GroupMember_id) {
   global $acl_options;
    if (!$GroupMember_id) return false;
    $acl = get_acl_object($acl_options, $con);
    $ret=$acl->delete_group_object($GroupMember_id);
    return $ret;
}

/*****************************************************************************/
/**
  *
  * Retrieves a list of users who hold a particular role in the ACL
  *
  * @param adodbconnection $con with handle to database with ACL tables
  * @param string $role with Role_name (or Role_id) of role to retrieve users in
  * @return array of user_id's with users in the role specified
  *
**/
function get_users_in_role($con, $role) {
    global $session_user_id;
    global $acl_options;
    $acl = get_acl_object($acl_options, $con);
    $role_id=get_role_id($acl, $role);
    $ret = $acl->get_role_users($role_id);
    if (!$ret) return false;
    $users=array();
    foreach ($ret as $cur) {
        $users[]=$cur['user_id'];
    }
    return $users;
}

/*****************************************************************************/
/**
  *
  * Retrieve users entries from the ACL based on input role and/or group
  *
  * @param string $acl_group with Group_name (or Group_id) of group to retrieve users in
  * @param string $acl_role with Role_name (or Role_id) of role to retrieve users in
  * @param xrms_acl object optionally providing ACL object
  * @return array of user_id's with users in the role and/or group specified
  *
**/
function get_group_users($acl_group, $acl_role = false, $acl=false) {
    global $acl_options;
    if (!$acl) {
        $acl = get_acl_object($acl_options);
    }
    $group_id=get_group_id($acl, $acl_group);
    if (!$group_id) {
        echo "Failed to find group $acl_group in security system<br>"; return false;
    }
    if ($acl_role) {
        $role_id = get_role_id($acl, $acl_role);
        if (!$role_id) {
            echo "Failed to find role $role in security system<br>"; return false;
        }
    } else { $role_id=false; }
    
    $aryGroupList = $acl->get_group_user($group_id, false, $role_id, NULL);
    // Make sure we get something
    if ( $aryGroupList )
    {
        $aryUserList=array();
        // pull out just the user_ids for this list
        foreach ($aryGroupList as $key => $value)
            $aryUserList[] = $value['user_id'];
            
        return $aryUserList;
    }  
    return false;
}

/*****************************************************************************/
/**
  *
  * Checks to ensure that there are no recursive loops between parent and child controlled objects
  * This is done before adding Controlled Object Relationships, so that infite loops in the ACL can be avoided
  *
  * @param adodbconnection $con with handle to database with ACL tables
  * @param integer $ParentControlledObject_id with parent object in relationsihp
  * @param integer $ChildControlledObject_id with child object in relationsihp
  * @return boolean indicating if proposed object relationship successfully avoids recursive loops
  *
**/
function check_acl_object_recursion($con, $ParentControlledObject_id, $ChildControlledObject_id) {
    global $acl_options;
    $acl = get_acl_object($acl_options, $con);
    //get list of relationship in which the proposed parent object is a child
    $ControlledObjectRelationships = $acl->get_controlled_object_relationship(false, $ParentControlledObject_id, false, true);
    if ($ControlledObjectRelationships) {
	    if (!is_array(current($ControlledObjectRelationships))) {   
        	$ControlledObjectRelationships=array($ControlledObjectRelationships['CORelationship_id']=>$ControlledObjectRelationships);
    	}
        //loop on relationships
    	foreach ($ControlledObjectRelationships as $cor_id => $cor) {
        	if ($cor['ParentControlledObject_id']) {
//            echo "<pre>"; print_r($cor); echo "</pre>";
                //ensure that proposed child is does not exist as the parent in a relationship in which the proposed parent is already a child
            	if ($cor['ParentControlledObject_id']==$ChildControlledObject_id) {
                	return false;
            	}
                //recursively call same function to ensure that the parent object in the relationship is also an acceptable parent for the proposed child
            	$ret = check_acl_object_recursion($con, $cor['ParentControlledObject_id'],$ChildControlledObject_id);
            	if (!$ret) return false;
        	}
    	}
    }
    //all relationships have been checked and no problems were found, allow addition of relationship
    return true;
}

/*****************************************************************************/
/**
  *
  * Checks to ensure that there are no recursive loops within group relationships
  * This is done before adding child groups to groups, to ensure no infinite loops in the ACL
  *
  * @param integer $Group_id with ID of group which is the proposed parent
  * @param integer $ChildGroup_id with proposed child group in relationsihp
  * @param adodbconnection $con optional handle to database with ACL tables
  * @return boolean indicating if proposed group relationship successfully avoids recursive loops
  *
**/
function check_acl_group_recursion($Group_id, $ChildGroup_id, $con=false) {
    global $acl_options;
    $acl = get_acl_object($acl_options, $con);

    //if trying to add group to itself, fail
    if ($Group_id==$ChildGroup_id) return false;
    
    //get list of groups for which the proposed parent is a child
    $groupList = $acl->get_group_user(false, false, false, $Group_id);

    //if groups are returned, loop through them
     if ($groupList and is_array($groupList)) {
        foreach ($groupList as $gkey=>$group) {
            //if the proposed child group is already a parent group of proposed parent, then fail
            if ($group['Group_id']==$ChildGroup_id) return false;
            //recursively check that parent of proposed group is also an acceptable parent for the proposed child
            $recurse=check_acl_group_recursion($group['Group_id'], $ChildGroup_id);
            if (!$recurse) return false;
        }
    }
    //none of the group relationships would conflict, so allow addition
    return true;
}

/*****************************************************************************/
/**
  *
  * Confirms that a user hold a particular role in any group
  *
  * @param xrms_acl object optionally providing ACL object
  * @param integer $user_id with ID of the user to use for check
  * @param integer $role with role name or ID
  * @return boolean indicating if the user holds the specified role in any group
  *
**/
function check_user_role($acl, $user_id, $role) {
    global $acl_options;
    if (!$acl)
        $acl = get_acl_object($acl_options);
        
    if (!$user_id) return false;
    if (!$role) return false;
    $role_id=get_role_id($acl, $role);
    if (!$role_id) return false;
    
    //get list of roles the user holds
    $roles=get_user_roles($acl, $user_id,false, false);
    
    //look for specified role in the list
    if (in_array($role_id, $roles)) return true;
    return false;
}

/*****************************************************************************/
/**
  *
  * Runs custom check based on the roles a user holds
  * Intended to be used with a callback that is passed: an ACL object, the user_id requested, and an array of the users available roles
  *
  * @param xrms_acl object optionally providing ACL object
  * @param integer $user_id with ID of the user to use for check
  * @param string $check_callback with function name which is passed $acl, $user_id, $roles and returns boolean
  * @return boolean indicating if access should be provided to the user, based on return from callback
  *
**/
function check_role_access($acl=false, $user_id, $check_callback='xrms_role_access_check_bool') {
    global $acl_options;
    global $on_what_table;
    
    if (!$user_id) return false;
    
    if (!$acl) $acl = get_acl_object($acl_options);
    
    //get roles held by the user
    $roles=get_user_roles($acl, $user_id);
    //create callback eval string
    $eval_str="\$ret=$check_callback(\$acl, \$user_id, \$roles);";
    
    eval($eval_str);
    
    //return output from callback
    return $ret;    
}

/*****************************************************************************/
/**
  *
  * XRMS-specific callback which provides boolean access indicator based on user_id and roles
  * Intended to be used with as a callback to check_role_access, currently always allows access
  *
  * @param xrms_acl object optionally providing ACL object
  * @param integer $user_id with ID of the user to use for check
  * @param array $roles with roles available to the user
  * @return boolean indicating if access should be provided to the user
  *
**/
function xrms_role_access_check_bool($acl=false, $user_id, $roles) {
    return true;
/*
    //EXAMPLE CODE FOR A CALLBACK
    //compares current directory to a table name, so that major components one/some pages can could be access-controlled by role and controlled object
    global $on_what_table;
    global $acl_options;
    //get current executing path
    $path=$_SERVER["SCRIPT_FILENAME"];
    if (!$acl) $acl = get_acl_object($acl_options);
    if (!$on_what_table) {
        $dir = dirname($path);
        $dir_array=explode(DIRECTORY_SEPARATOR,$dir);
        $last_dir=array_pop($dir_array);
        //last directory is also table name
        $on_what_table=$last_dir;
    }
    $object_data = $acl->get_controlled_object(false, false, false, $on_what_table);
*/
}

/*****************************************************************************/
/**
  *
  * Primary permissions query function, retrieves permissions on an object for a user
  *
  * @param integer $user_id with ID of the user to use for check
  * @param string $action with action being attempted (defaults to false here, which becomes 'Read' in the ACL
  * @param string $object with name or ID of controlled object to query, required if table is not provided
  * @param integer $on_what_id with object identifier of controlled object
  * @param string $table with table name of controlled object, required if object is not provided
  * @param string $role with role name or ID to use
  * @param adodbconnection $db_connection with handle to ACL DB
  * @param xrms_acl object optionally providing ACL object
  * @return array of Permission_name's (Read, Create, Update, Delete, Export)
  *
**/
function check_permission($user_id, $action=false, $object=false,  $on_what_id, $table=false, $role=false, $db_connection=false, $acl=false) {
    global $acl_options;
    if (!$acl) { 
    if ($db_connection) {
        $acl=get_acl_object($acl_options, $db_connection);
    } else {
        $acl = get_acl_object($acl_options);
    }
    }
    
    $object_id=get_object_id($acl, $object, $table, $role);
    //no object id, returning true to allow access to uncontrolled area
     if (!$object_id) { return array($action); }
     
    $permissions = $acl->get_permissions_user($object_id, $on_what_id, $user_id, false, array($action));
    if (!$permissions) return false;
    if (!is_array($permissions)) { $permissions=array($permissions); }
    $ret=array();
    foreach ($permissions as $perm) {
            $permData=$acl->get_permission(false, $perm);
            $ret[]=$permData['Permission_name'];
    }
    return $ret;
}

/*****************************************************************************/
/**
  *
  * Primary permissions query function for database objects, retrieves permissions on a specific object for a user and return a true/false indicator for access
  *
  * @param integer $user_id with ID of the user to use for check
  * @param string $object with name or ID of controlled object to query, required if table is not provided
  * @param integer $on_what_id with object identifier of controlled object
  * @param string $action with action being attempted (defaults to false here, which becomes 'Read' in the ACL
  * @param string $table with table name of controlled object, required if object is not provided
  * @param string $role with role name or ID to use
  * @param adodbconnection $db_connection with handle to ACL DB
  * @param xrms_acl object optionally providing ACL object
  * @return boolean indicating if permission is granted or not
  *
**/
function check_permission_bool($user_id, $object=false, $on_what_id, $action='Read',$table=false, $role=false, $db_connection=false, $acl=false) {
    $permissions=check_permission($user_id, $action, $object, $on_what_id, $table, $role, $db_connection, $acl);
    if (!$permissions) return false;
    if (!is_array($permissions)) return false;
    if (array_search($action,$permissions)===false) return false;
    else return true;
}

/*****************************************************************************/
/**
  *
  * Primary permissions query function for non-database objects, retrieves permissions on a class of object for a user
  *
  * @param integer $user_id with ID of the user to use for check
  * @param string $object with name or ID of controlled object to query, required if table is not provided
  * @param string $action with action being attempted (defaults to false here, which becomes 'Read' in the ACL
  * @param string $table with table name of controlled object, required if object is not provided
  * @param string $role with role name or ID to use
  * @param adodbconnection $db_connection with handle to ACL DB
  * @param xrms_acl object optionally providing ACL object
  * @return array of Permission_name's (Read, Create, Update, Delete, Export)
  *
**/
function check_object_permission($user_id, $object, $action, $table, $role=false, $db_connection=false, $acl=false) {
    global $acl_options;
    if (!$acl) {
    if ($db_connection) {
        $acl = get_acl_object($acl_options, $db_connection);
    } else {
        $acl = get_acl_object($acl_options);
    }
    }
    $object_id=get_object_id($acl, $object, $table, $role);
    //no object id, returning true to allow access to uncontrolled area
     if (!$object_id) { return array($action); }
    
    $permissions = $acl->get_permission_user_object($object_id, $user_id, false, array($action));
//     echo "Checking $permissions=get_permission_user_object($object_id, $user_id, false, $action);<br>";
    if (!$permissions) return false;
    if (!is_array($permissions)) { $permissions=array($permissions); }
    $ret=array();
    foreach ($permissions as $perm) {
            $permData=$acl->get_permission(false, $perm);
            $ret[]=$permData['Permission_name'];
    }
    return $ret;
}

/*****************************************************************************/
/**
  *
  * Primary permissions query function for non-database objects, retrieves permissions on a class of object for a user, and return a boolen indicating if access is granted
  *
  * @param integer $user_id with ID of the user to use for check
  * @param string $object with name or ID of controlled object to query, required if table is not provided
  * @param string $action with action being attempted (defaults to false here, which becomes 'Read' in the ACL
  * @param string $table with table name of controlled object, required if object is not provided
  * @param string $role with role name or ID to use
  * @param adodbconnection $db_connection with handle to ACL DB
  * @param xrms_acl object optionally providing ACL object
  * @return array of Permission_name's (Read, Create, Update, Delete, Export)
  *
**/
function check_object_permission_bool($user_id, $object=false, $action='Read',$table=false, $role=false, $db_connection=false, $acl=false) {
    $permissions=check_object_permission($user_id, $object, $action, $table, $role, $db_connection, $acl);
    if (!$permissions) return false;
    if (!is_array($permissions)) return false;
    if (array_search($action,$permissions)===false) return false;
    else return true;
}

/*****************************************************************************/
/**
  *
  * Primary function to retrieve a list of objects for which a user has access
  *
  * @param integer $user_id with ID of the user to use for check
  * @param string $action with action being attempted (defaults to 'Read')
  * @param string $object with name or ID of controlled object for which to return ids
  * @param string $table with table name of controlled object, required if object is not provided
  * @param xrms_acl object optionally providing ACL object
  * @return array of object ids for which the user has specified permission
  *
**/
function acl_get_list($user_id, $action='Read', $object=false, $table=false, $acl=false) {
    global $acl_options;
//    echo "Getting list<br>";
    if (!$user_id) return false;
    
    if (!$acl) $acl = get_acl_object($acl_options);
    $object_id=get_object_id($acl, $object, $table);
//    echo "Getting object<br>";
    if (!$object_id) return false;
//    echo "Doing restriction<br>";
    $ret = $acl->get_restricted_object_list($object_id, $user_id, $action);
    if (!$ret) return false;
    if ($ret['ALL']) return true;
    else {
        $list = $ret['controlled_objects'];
        //    print_r($list);
        return $list;
    }
}

/*****************************************************************************/
/**
  *
  * Gets a roles name from the the role ID
  *
  * @param xrms_acl $acl object optionally providing ACL object
  * @param integer $role with ID of role to find name
  * @return string with name of the role
  *
**/
function get_role_name($acl=false, $role) {
    global $acl_options;
    
    if (!$acl) $acl = get_acl_object($acl_options);
    if (is_numeric($role)) {
        $roleData=$acl->get_role(false, $role);
        if ($roleData) {
            return $roleData['Role_name'];
        } else return false;
    } else return $role;
}

/*****************************************************************************/
/**
  *
  * Gets the roles available to a user
  *
  * @param xrms_acl $acl object optionally providing ACL object
  * @param integer $user_id identifying which user to get roles for
  * @param string $group optionally limiting role list to a single group
  * @param boolean $use_role_names indicating if roles should be return numericly or by name
  * @return array of roles held by the user
  *
**/
function get_user_roles($acl=false, $user_id, $group=false, $use_role_names=true) {
    global $acl_options;
    
    if (!$acl) $acl = get_acl_object($acl_options);
    if (!$user_id) return array();
    
    if ($group) {
        if (!is_array($group)) $group=array($group);
        foreach ($group as $gkey=>$gid) {
            $group[$gkey]=get_group_id($gid);
        }
    } else { $group=array(false); }
    
    $RoleList = $acl->get_user_roles_by_array($group, $user_id);
    $UserRoleList=$RoleList['Roles'];
    if ($UserRoleList) {
        if ($use_role_names) {
            foreach ($UserRoleList as $Role) {
                $ret[$Role]=get_role_name($acl, $Role);
            }
            return $ret;
        } else return $UserRoleList;
    }
    return array();
}

/*****************************************************************************/
/**
  *
  * Gets the roles available to a user, broken up by group
  *
  * @param xrms_acl $acl object optionally providing ACL object
  * @param integer $user_id identifying which user to get roles for
  * @param boolean $use_role_names indicating if roles should be return numericly or by name
  * @return array of roles held by the user
  *
**/
function get_user_roles_with_groups($acl, $user_id, $use_role_names=true) {
   global $acl_options;
    
    if (!$acl) $acl = get_acl_object($acl_options);
    if (!$user_id) return array();
    $RoleList = $acl->get_user_roles_by_array(array(false), $user_id);
    if ($RoleList) {
        if ($use_role_names) {
            foreach ($RoleList['GroupRoles'] as $gkey=>$garray) {
                foreach ($garray as $gid=>$role_id) {
                    $RoleList['GroupRoles'][$gkey][$gid]=get_role_name($acl, $role_id);
                }
            }
        }
        return $RoleList['GroupRoles']; 
    } else return false;
}

/*****************************************************************************/
/**
  *
  * Finds a controlled object object based on the current page, as well as the role requested
  *
  * @param xrms_acl $acl object optionally providing ACL object
  * @param string $role optionally providing an extra restriction on the page being accessed
  * @return integer with controlled object, or false if failure
  *
**/
function find_object_by_base($acl=false, $role=false) {
    global $acl_options;
    global $on_what_table;
    if (!$acl) $acl = get_acl_object($acl_options);
    
    if ($on_what_table) {
        $object_id=get_object_id($acl, false, $on_what_table);
    }
    if ($object_id) return $object_id;
    $path=$_SERVER['PHP_SELF'];
    
    $dir = dirname($path);
    $dir_array=explode('/',$dir);
    $last_dir=array_pop($dir_array);
    //last directory is also table name
    
    $object=false;
    $table=false;
    switch ($last_dir) {
        case 'admin':
            $object="Administration";
        break;
        default:
            if ($role) {
                switch ($role) {
                    case 'Admin':
                        $object="Administration";
                    break;
                    default:
                        $table=$last_dir;
                    break;
                }
            } else {
                $table=$last_dir;
            }
        break;
    }
    //print_r($dir_array);
//    echo "LAST DIR: $last_dir<br>";
    $object_data = $acl->get_controlled_object($object, false, false, $table);
    $object_id=$object_data['ControlledObject_id'];
    return $object_id;
}

/*****************************************************************************/
/**
  *
  * Get object id from object name or ID
  *
  * @param xrms_acl handle to ACL object
  * @param string $object with string or ID of controlled object
  * @param string $table with tablename of controlled object
  * @param string $role with string of role required to access an object
  * @return integer $object_id or false if object not found
  *
**/
function get_object_id($acl=false, $object=false, $table=false, $role=false) {
    global $acl_options;
    
    if (!$acl) $acl = get_acl_object($acl_options);
    if (!$object AND !$table) {
        $object_id=find_object_by_base($acl, $role);
    } else {
        if ($table) {
            $object_data = $acl->get_controlled_object(false, false, false, $table);
            $object_id=$object_data['ControlledObject_id'];
        } elseif (!is_numeric($object)) {
            $object_data = $acl->get_controlled_object($object);
            $object_id=$object_data['ControlledObject_id'];
        } else $object_id=$object;
    }
    return $object_id;
}

/*****************************************************************************/
/**
  *
  * Get group id from group name or ID
  *
  * @param xrms_acl handle to ACL object
  * @param string $group with string or ID of role
  * @return integer $group_id or false if role not found
  *
**/
function get_group_id($acl=false, $group=false) {
    global $acl_options;
    
    if (!$acl) $acl = get_acl_object($acl_options);
    if (!$group) return false;
    if (!is_numeric($group)) {
            $group_data = $acl->get_group($group);
            $group_id=$group_data['Group_id'];
    } else $group_id=$group;
    
    return $group_id;
}

/*****************************************************************************/
/**
  *
  * Get role id from role name or ID
  *
  * @param xrms_acl handle to ACL object
  * @param string $role with string or ID of role
  * @return integer $role_id or false if role not found
  *
**/
function get_role_id($acl=false, $role=false) {
    global $acl_options;
    
    if (!$acl) $acl = get_acl_object($acl_options);
    if (!$role) return false;
    if (!is_numeric($role)) {
            $role_data = $acl->get_role($role);
            $role_id=$role_data['Role_id'];
    } else $role_id=$role;
    
    return $role_id;
}

/*****************************************************************************/
/**
  *
  * Gets a list of possible roles that a user could have in the ACL
  *
  * @param xrms_acl handle to ACL object
  * @param boolean $return_menu indicating if an HTML select widget should be returned (true) or an array of roles by id/name
  * @param string $field_name HTML form field name, defaults to 'role_id'
  * @param integer $role_id with selected role for HTML select widget
  * @param boolean $show_blank_first to determine if HTML select widget should have initlal blank entry
  * @return string/array with HTML select widget or array of roles, or false if failed
  *
**/
function get_role_list($acl=false, $return_menu=true, $field_name='role_id', $role_id=false, $show_blank_first=true) {
    global $acl_options;
    if (!$acl) $acl = get_acl_object($acl_options);

    if ($return_menu) {
        $list_rst=$acl->get_role_list(false);
        if ($list_rst) {
            $role_menu=$list_rst->getmenu2('role_id', $role_id, $show_blank_first, false, 0, 'style="font-size: x-small; border: outset; width: 80px;"');
            return $role_menu;
        } else return false;
    } else {
        $list_array=$acl->get_role_list(true);
        return $list_array;
    }
}

/*****************************************************************************/
/**
  *
  * Removes a group from the ACL
  *
  * @param xrms_acl handle to ACL object
  * @param string $Group with name or ID of group to delete
  * @return boolean indicating success of delete
  *
**/
function delete_group($acl=false, $Group) {
    global $acl_options;
    
    if (!$acl) $acl = get_acl_object($acl_options);
    $group_id= get_group_id($acl, $Group);
    if (!$group_id) { echo "Failed to delete group $Group."; return false; }
    return $acl->delete_group($Group);
}

/*****************************************************************************/
/**
  *
  * Gets a record for an ACL group
  *
  * @param xrms_acl handle to ACL object
  * @param string $groupName with group name, required if group_id is not provided
  * @param integer $group_id with id of the group, required if groupName is not provided
  * @return array with group record
  *
**/
function get_acl_group($acl, $groupName, $group_id) {
    global $acl_options;
    
    if (!$acl) $acl = get_acl_object($acl_options);
    $ret=$acl->get_group($groupName, $group_id);
    return $ret;
}

/*****************************************************************************/
/**
  *
  * Adds a group to the ACL system, also adding a group member if provided
  *
  * @param xrms_acl handle to ACL object
  * @param string $group with group name
  * @param integer $object with name/id of controlled object to add to group
  * @param integer $on_what_id with database identifier of the specific controlled object to add to the group
  * @return integer with newly added Group_id, or false if failed
  *
**/
function add_group($acl=false, $groupName, $object=false, $on_what_id=false) {
    global $acl_options;
    
    if (!$acl) $acl = get_acl_object($acl_options);

    //if we can't add the group, check to see if it already exists
    if (!$ret = $acl->add_group($groupName)) {
        $ret=$acl->get_group($groupName);
        if ($ret) { echo "Cannot add group $groupName: already exists."; }
        $groupid=$ret['Group_id'];  
    } else {
        $groupid=$ret;
    }
    if ($groupid AND $object AND $on_what_id) {
        $ret = add_group_object($acl, $groupid, $object, $on_what_id);
        if ($ret) {
            if (is_array(current($ret))) $ret=current($ret);
        }
    }
    
    return $ret;
}

/*****************************************************************************/
/**
  *
  * Adds a group object entry
  *
  * @param xrms_acl handle to ACL object
  * @param string $group with group name/id to delete groups from
  * @param integer $object with name/id of controlled object
  * @param integer $on_what_id with database identifier of the specific controlled object to add to the group
  * @return integer with newly added GroupUser_id, or false if failed
  *
**/
function add_group_object($acl=false, $group, $object, $on_what_id) {
    global $acl_options;
    if (!$on_what_id) return false;    
    
    if (!$acl) $acl = get_acl_object($acl_options);
        
    $group_id=get_group_id($acl, $group);
    if (!$group_id) return false;
    
    $object_id=get_object_id($acl, $object);
    if (!$object_id) return false;
    
    $ret=$acl->add_group_object($group_id, $object_id, $on_what_id);
    if (!$ret) {
        $group_object=$acl->get_group_objects($group_id, $object_id, $on_what_id);
        if ($group_object) { 
            echo "Failed to add object $object id $on_what_id to group $group: already exists"; 
            return $group_object;
        }
    } else {
        $group_object = $acl->get_group_objects(false, false, false, $ret);
        return $group_object;
    }
}

/*****************************************************************************/
/**
  *
  * Removes a group object entry
  *
  * @param xrms_acl handle to ACL object
  * @param integer $GroupMember_id with specific group member to remove
  * @param string $group with group name/id to delete groups from
  * @param integer $object with name/id of controlled object
  * @param integer $on_what_id with database identifier of the specific controlled object to add to the group
  * @return boolean indicator of success of delete
  *
**/
function delete_group_object($acl, $GroupMember_id=false, $group=false, $object=false, $on_what_id=false) {
    global $acl_options;
    
    if (!$acl) $acl = get_acl_object($acl_options);
        
    if ($group) {
        $group_id=get_group_id($acl, $group);
        if (!$group_id) return false;
    } else { $group_id=false; }
    
    if ($object) {
        $object_id=get_object_id($acl, $object);
        if (!$object_id) return false;
    } else {$object_id=false; }
    
    if (!$GroupMember_id) {
        if(!$group_id AND !$object_id AND !$on_what_id) {
            echo "Cannot delete object $object id $on_what_id from group $group: not enough information";
            return false;
        }
        $group_object=$acl->get_group_objects($group_id, $object_id, $on_what_id);   
        if (!$group_object) {
            echo "Cannot delete object $object id $on_what_id from group $group: failed to find object in group";
            return false;
        }
        if (is_array(current($group_object))) { reset($group_object); $group_object=current($group_object); }
        $GroupMember_id=$group_object['GroupMember_id'];
    }
    return $acl->delete_group_object($GroupMember_id);
}

/*****************************************************************************/
/**
  *
  * Adds a group user entry
  *
  * @param xrms_acl handle to ACL object
  * @param string $group with group name/id to delete users/groups from
  * @param integer $user_id with user identifier
  * @param string $role with name/id of role to delete from group user
  * @param boolean $silent indicating if add should fail silently
  * @return integer with db identifier of newly added user group entry, or false for failure
  *
**/
function add_user_group($acl=false, $group, $user_id, $role, $silent=false) {
    global $acl_options;
    
    if (!$acl) $acl = get_acl_object($acl_options);
    
    $group_id=get_group_id($acl, $group);
    if (!$group_id) return false;

    $role_id=get_role_id($acl, $role);
    if (!$role_id) return false;
        
    $ret = $acl->add_group_user($group_id, $user_id, $role_id);
    if (!$ret) {
        $group_user=$acl->get_group_user($group_id, $user_id, $role_id, false);
        if ($group_user) { 
            if (!$silent) {
                echo "Failed to add user $user_id with role $role to group $group: already exists\n"; 
            }
            if (is_array(current($group_user))) $group_user=current($group_user);            
            return $group_user;
        }
    } else {
        $group_user = $acl->get_group_user(false, false, false, false, $ret);
        if ($group_user) {
            if (is_array(current($group_user))) $group_user=current($group_user);
            return $group_user;
        } else { if (!$silent)  { echo "Failing user group lookup";  } return false; }
    }
}

/*****************************************************************************/
/**
  *
  * Retrieves a group user entry
  *
  * @param xrms_acl handle to ACL object
  * @param integer $GroupUser_id with specific group user to remove
  * @return array with GroupUser record
  *
**/
function get_group_user($acl, $GroupUser_id) {
    global $acl_options;
    if (!$acl) $acl = get_acl_object($acl_options);
    $group_user = $acl->get_group_user(false, false, false, false, $GroupUser_id, true);
    return $group_user;
}

/*****************************************************************************/
/**
  *
  * Removes a group user entry
  *
  * @param xrms_acl handle to ACL object
  * @param integer $GroupUser_id with specific group user to remove
  * @param string $group with group name/id to delete users/groups from
  * @param integer $user_id with user identifier
  * @param string $role with name/id of role to delete from group user
  * @return boolean indicator of success of delete
  *
**/
function delete_user_group($acl, $GroupUser_id=false, $group=false, $user_id=false, $role=false) {
    global $acl_options;
        
    if (!$acl) $acl = get_acl_object($acl_options);
    if ($group) {
        $group_id=get_group_id($acl, $group);
        if (!$group_id) { echo "No Group Specified: $group"; return false; }
    }
    if ($role) {
        $role_id=get_role_id($acl, $role);
        if (!$role_id) { echo "No Role Specified: $role"; return false; }
    }
    
    if (!$GroupUser_id AND !($role_id AND $group_id AND $user_id)) { 
        echo "Cannot delete user $user_id from group $group_id with role $role: Not enough information<br>"; 
        return false; 
    }
    if (!$GroupUser_id) {
        //if we have no id, search for it
        $ret = $acl->get_group_user($group_id, $user_id, $role_id,false);
        if (!$ret) { 
            echo "Cannot delete user $user_id from group $group_id with role $role: User not found with role in group<br>"; 
            return false;
        } else {
            if (is_array(current($ret))) $ret=current($ret);
            $GroupUser_id=$ret['GroupUser_id'];
        }
    }
    return $acl->delete_group_user($GroupUser_id);
} 

/**
  * $Log: acl_wrapper.php,v $
  * Revision 1.29  2006/01/27 13:37:05  vanmer
  * - changed ACL to require array input for actions (permissions)
  * - removed check for array of permissions, now is always an array
  * - changed test and wrapper to properly pass permissions as an array to ACL object
  *
  * Revision 1.28  2005/09/21 21:03:18  vanmer
  * - added phpdoc blocks for all ACL install and wrapper functions
  *
**/
?>
