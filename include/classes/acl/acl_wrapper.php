<?php

define('ACL_PATH',$include_directory.'classes/acl/');
require_once(ACL_PATH.'xrms_acl.php');
require_once(ACL_PATH.'xrms_acl_config.php');
require_once(ACL_PATH.'acl_install.php');
$acl_options=$options;

function get_acl_object($access_info=false, $con=false, $callbacks=false, $context='default') {
    if (!$callbacks) {
        $callbacks='xrms_acl_auth_callback';
    }
      $acl = new xrms_acl($access_info, $con, $callbacks, $context );
      return $acl;
}

function get_acl_dbconnection($datasource=false) {
    getGlobalVar($acl_datasource_name,'acl_datasource_name');
    $_SESSION['acl_datasource_name']=$acl_datasource_name;
    if (!$datasource) $datasource=$acl_datasource_name;
    if (!$datasource) $datasource='default';
    
    $acl=get_acl_object();
    return $acl->get_object_adodbconnection($datasource);
}

function get_acl_group_member_criteria($con=false, $GroupMember_id) {
    global $acl_options;
    if (!$GroupMember_id) return false;
    $acl = get_acl_object($acl_options, $con);
    $ret=$acl->get_group_member_criteria($GroupMember_id);
    return $ret;
}

function remove_group_member_criteria($con=false, $GroupMemberCriteria_id) {
    global $acl_options;
    if (!$GroupMemberCriteria_id) return false;
    $acl = get_acl_object($acl_options, $con);
    $ret=$acl->delete_group_member_criteria(false, false, $GroupMemberCriteria_id);
    return $ret;
}

function add_group_member_criteria($con, $GroupMember_id, $criteria_fieldname, $criteria_value, $criteria_operator='=') {
    global $acl_options;
    $acl = get_acl_object($acl_options, $con);
    if ($GroupMember_id AND $criteria_fieldname AND $criteria_value AND $criteria_operator) {
        $ret = $acl->add_group_member_criteria($GroupMember_id, $criteria_fieldname, $criteria_value, $criteria_operator);   
        return $ret;
    }  else return false;
}

function delete_group_member($con, $GroupMember_id) {
   global $acl_options;
    if (!$GroupMember_id) return false;
    $acl = get_acl_object($acl_options, $con);
    $ret=$acl->delete_group_object($GroupMember_id);
    return $ret;
}

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

function check_acl_object_recursion($con, $ParentControlledObject_id, $ChildControlledObject_id) {
    global $acl_options;
    $acl = get_acl_object($acl_options, $con);
    //get list of objects above the parent
    $ControlledObjectRelationships = $acl->get_controlled_object_relationship(false, $ParentControlledObject_id, false, true);
    if ($ControlledObjectRelationships) {
	    if (!is_array(current($ControlledObjectRelationships))) {   
        	$ControlledObjectRelationships=array($ControlledObjectRelationships['CORelationship_id']=>$ControlledObjectRelationships);
    	}
    	foreach ($ControlledObjectRelationships as $cor_id => $cor) {
        	if ($cor['ParentControlledObject_id']) {
//            echo "<pre>"; print_r($cor); echo "</pre>";
            	if ($cor['ParentControlledObject_id']==$ChildControlledObject_id) {
                	return false;
            	}
            	$ret = check_acl_object_recursion($con, $cor['ParentControlledObject_id'],$ChildControlledObject_id);
            	if (!$ret) return false;
        	}
    	}
    }
    return true;
}
function check_acl_group_recursion($Group_id, $ChildGroup_id) {
    global $acl_options;
    $acl = get_acl_object($acl_options);
    
    $groupList = $acl->get_group_user(false, false, false, $Group_id);
    if ($Group_id==$ChildGroup_id) return false;
     if ($groupList and is_array($groupList)) {
        foreach ($groupList as $gkey=>$group) {
            if ($group['Group_id']==$ChildGroup_id) return false;
            $recurse=check_acl_group_recursion($group['Group_id'], $ChildGroup_id);
            if (!$recurse) return false;
        }
    }
    return true;
}

function check_user_role($acl, $user_id, $role) {
    global $acl_options;
    if (!$acl)
        $acl = get_acl_object($acl_options);
        
    if (!$user_id) return false;
    if (!$role) return false;
    $role_id=get_role_id($acl, $role);
    if (!$role_id) return false;
    
    
    $roles=get_user_roles($acl, $user_id,false, false);
    
    if (in_array($role_id, $roles)) return true;
    return false;
}

function check_role_access($acl=false, $user_id, $check_callback='xrms_role_access_check_bool') {
    global $acl_options;
    global $on_what_table;
    
    if (!$user_id) return false;
    
    if (!$acl) $acl = get_acl_object($acl_options);
    
    $roles=get_user_roles($acl, $user_id);
    $eval_str="\$ret=$check_callback(\$acl, \$user_id, \$roles);";
    
    eval($eval_str);
    return $ret;    
}

function xrms_role_access_check_bool($acl=false, $user_id, $roles) {
    global $on_what_table;
    global $acl_options;
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
    return true;
}

function check_permission($user_id, $action=false, $object=false,  $on_what_id, $table=false, $role=false, $db_connection=false) {
    global $acl_options;
    if ($db_connection) {
        $acl=get_acl_object($acl_options, $db_connection);
    } else {
        $acl = get_acl_object($acl_options);
    }
    
    $object_id=get_object_id($acl, $object, $table, $role);
    //no object id, returning true to allow access to uncontrolled area
     if (!$object_id) { return array($action); }
     
    $permissions = $acl->get_permissions_user($object_id, $on_what_id, $user_id, false, $action);
    if (!$permissions) return false;
    if (!is_array($permissions)) { $permissions=array($permissions); }
    $ret=array();
    foreach ($permissions as $perm) {
            $permData=$acl->get_permission(false, $perm);
            $ret[]=$permData['Permission_name'];
    }
    return $ret;
}

function check_permission_bool($user_id, $object=false, $on_what_id, $action='Read',$table=false, $role=false, $db_connection=false) {
    $permissions=check_permission($user_id, $action, $object, $on_what_id, $table, $role, $db_connection);
    if (!$permissions) return false;
    if (!is_array($permissions)) return false;
    if (array_search($action,$permissions)===false) return false;
    else return true;
}

function check_object_permission($user_id, $object, $action, $table, $role=false, $db_connection=false) {
    global $acl_options;
    if ($db_connection) {
        $acl = get_acl_object($acl_options, $db_connection);
    } else {
        $acl = get_acl_object($acl_options);
    }
    $object_id=get_object_id($acl, $object, $table, $role);
    //no object id, returning true to allow access to uncontrolled area
     if (!$object_id) { return array($action); }
    
    $permissions = $acl->get_permission_user_object($object_id, $user_id, false, $action);
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

function check_object_permission_bool($user_id, $object=false, $action='Read',$table=false, $role=false, $db_connection=false) {
    $permissions=check_object_permission($user_id, $object, $action, $table, $role, $db_connection);
    if (!$permissions) return false;
    if (!is_array($permissions)) return false;
    if (array_search($action,$permissions)===false) return false;
    else return true;
}

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

function delete_group($acl=false, $Group) {
    global $acl_options;
    
    if (!$acl) $acl = get_acl_object($acl_options);
    $group_id= get_group_id($acl, $Group);
    if (!$group_id) { echo "Failed to delete group $Group."; return false; }
    return $acl->delete_group($Group);
}
function get_acl_group($acl, $groupName, $group_id) {
    global $acl_options;
    
    if (!$acl) $acl = get_acl_object($acl_options);
    $ret=$acl->get_group($groupName, $group_id);
    return $ret;
}
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
function get_group_user($acl, $GroupUser_id) {
    global $acl_options;
    if (!$acl) $acl = get_acl_object($acl_options);
    $group_user = $acl->get_group_user(false, false, false, false, $GroupUser_id, true);
    return $group_user;
}
function delete_user_group($acl, $GroupUser_id=false, $group=false, $user_id=false, $role=false) {
    global $acl_options;
        
    if (!$acl) $acl = get_acl_object($acl_options);
    if ($group) {
        $group_id=get_group_id($acl, $group);
        if (!$group_id) { echo "No Group Specified"; return false; }
    }
    if ($role) {
        $role_id=get_role_id($acl, $role);
        if (!$role_id) { echo "No Role Specified"; return false; }
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

?>
