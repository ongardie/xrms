<?php

function upgrade_role($user_id, $role, $group='Users') {
    global $acl_options;
        
    if (!$acl) $acl = new xrms_acl($acl_options);
    if ($group) {
        $group_id=get_group_id($acl, $group);
        if (!$group_id) { echo "No Group Specified"; return false; }
    }

    if ($role) {
        switch ($role) {
            default:
            case 'User':
                break;
            case 'Admin':
                $role='Administrator';
                break;
        }
        $role_id=get_role_id($acl, $role);
    } else $role_id=false;
    if (!$role_id) { echo "No Role Specified"; return false; }

    $ret=add_user_group(false, $group, $user_id, $role);
    if (is_array($ret)) return true;
    else return false;
}

function install_upgrade_acl($con=false) {
    if (!$con) return false;
    global $acl_options;
    $sql = "SELECT * from ControlledObject";
    $rst = $con->execute($sql);
    if (!$rst) $inst_ret=install_acl($con);
    else $inst_ret=true;
    
    if (!$inst_ret) { echo "ACL Install Failed<br>"; return false; }
    
    $sql = "SELECT * from GroupUser";
    $rst = $con->execute($sql);
    if (!$rst) db_error_handler($con, $sql);
    if ($rst->numRows()==0) {
        $inst_ret=install_acl_users($con);
   } else $inst_ret=true;
    if (!$inst_ret) { echo "ACL User Update Failed<br>"; return false; }
    
    $sql = "SELECT * FROM ControlledObjectRelationship";
    $rst = $con->execute($sql);
    if (!$rst) db_error_handler($con, $sql);
    if ($rst->numRows()>0) {
        if ($rst->fields['CORelationship_id']) {
            $sql = "ALTER TABLE `RolePermission` CHANGE `CORelationship_id` `CORelationship_id` INT( 10 ) UNSIGNED DEFAULT '0' NOT NULL";
            $rst=$con->execute($sql);
            if (!$rst) db_error_handler($con, $sql);
            $sql = "ALTER TABLE `ControlledObjectRelationship` CHANGE `CORelationship_id` `CORelationship_id` INT( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT";
            $rst=$con->execute($sql);
            if (!$rst) db_error_handler($con, $sql);
        }
    }
    return true;   
}

function install_acl_users($con) {

    $sql = "SELECT * from users, roles WHERE users.role_id=roles.role_id";
    $rst = $con->execute($sql);
    $install_status=true;
    while (!$rst->EOF) {
        $ret=upgrade_role($rst->fields['user_id'],$rst->fields['role_short_name']);
        $rst->movenext();
        if (!$ret AND $install_status) {
            $install_status=false;
        }
    }
    return $install_status;
}

function install_acl($con) {
    $return9=install_data_sources($con);
    $return1=install_controlled_objects($con);
    $return2=install_controlled_object_relationships($con);
    $return3=install_groups($con);
    $return4=install_roles($con);
    $return5=install_permissions($con);
    $return6=install_role_permissions($con);
    $return7=install_group_users($con);
    $return8=install_group_members($con);
    return ($return1 AND $return2 AND $return3 AND $return4 AND $return5 AND $return6 AND $return7 AND $return8 AND $return9);
}


function install_role_permissions($con) {
    $csql = "SELECT * FROM RolePermission";
    $crst=$con->execute($csql);
    $return=true;
    if (!$crst) {
        $sql=<<<TILLEND
CREATE TABLE `RolePermission` (
  `RolePermission_id` int(10) unsigned NOT NULL auto_increment,
  `Role_id` int(10) unsigned NOT NULL default '0',
  `CORelationship_id` int(10) unsigned NOT NULL default '0',
  `Scope` enum('World','Group','User') NOT NULL default 'World',
  `Permission_id` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`RolePermission_id`),
  KEY `Role_id` (`Role_id`),
  KEY `CORelationship_id` (`CORelationship_id`),
  KEY `Permission_id` (`Permission_id`)
)
TILLEND;
    
        $rst=$con->execute($sql);
        if (!$rst) { db_error_handler($con, $sql); return false;}
        $crst=$con->execute($csql);
    }
    if ($crst->numRows()==0) {
        $sql=<<<TILLEND
INSERT INTO `RolePermission` (`RolePermission_id`, `Role_id`, `CORelationship_id`, `Scope`, `Permission_id`) VALUES (1, 2, 1, 'World', 1);
INSERT INTO `RolePermission` (`RolePermission_id`, `Role_id`, `CORelationship_id`, `Scope`, `Permission_id`) VALUES (2, 2, 1, 'World', 2);
INSERT INTO `RolePermission` (`RolePermission_id`, `Role_id`, `CORelationship_id`, `Scope`, `Permission_id`) VALUES (3, 2, 1, 'World', 3);
INSERT INTO `RolePermission` (`RolePermission_id`, `Role_id`, `CORelationship_id`, `Scope`, `Permission_id`) VALUES (4, 2, 1, 'World', 4);
INSERT INTO `RolePermission` (`RolePermission_id`, `Role_id`, `CORelationship_id`, `Scope`, `Permission_id`) VALUES (5, 1, 1, 'World', 1);
INSERT INTO `RolePermission` (`RolePermission_id`, `Role_id`, `CORelationship_id`, `Scope`, `Permission_id`) VALUES (6, 1, 1, 'World', 2);
INSERT INTO `RolePermission` (`RolePermission_id`, `Role_id`, `CORelationship_id`, `Scope`, `Permission_id`) VALUES (7, 1, 1, 'World', 3);
INSERT INTO `RolePermission` (`RolePermission_id`, `Role_id`, `CORelationship_id`, `Scope`, `Permission_id`) VALUES (8, 1, 2, 'World', 1);
INSERT INTO `RolePermission` (`RolePermission_id`, `Role_id`, `CORelationship_id`, `Scope`, `Permission_id`) VALUES (9, 1, 2, 'World', 2);
INSERT INTO `RolePermission` (`RolePermission_id`, `Role_id`, `CORelationship_id`, `Scope`, `Permission_id`) VALUES (10, 1, 2, 'World', 3);
INSERT INTO `RolePermission` (`RolePermission_id`, `Role_id`, `CORelationship_id`, `Scope`, `Permission_id`) VALUES (11, 2, 2, 'World', 1);
INSERT INTO `RolePermission` (`RolePermission_id`, `Role_id`, `CORelationship_id`, `Scope`, `Permission_id`) VALUES (12, 2, 2, 'World', 2);
INSERT INTO `RolePermission` (`RolePermission_id`, `Role_id`, `CORelationship_id`, `Scope`, `Permission_id`) VALUES (13, 2, 2, 'World', 3);
INSERT INTO `RolePermission` (`RolePermission_id`, `Role_id`, `CORelationship_id`, `Scope`, `Permission_id`) VALUES (14, 2, 2, 'World', 4);
INSERT INTO `RolePermission` (`RolePermission_id`, `Role_id`, `CORelationship_id`, `Scope`, `Permission_id`) VALUES (15, 2, 21, 'World', 1);
INSERT INTO `RolePermission` (`RolePermission_id`, `Role_id`, `CORelationship_id`, `Scope`, `Permission_id`) VALUES (16, 2, 21, 'World', 2);
INSERT INTO `RolePermission` (`RolePermission_id`, `Role_id`, `CORelationship_id`, `Scope`, `Permission_id`) VALUES (17, 2, 21, 'World', 3);
INSERT INTO `RolePermission` (`RolePermission_id`, `Role_id`, `CORelationship_id`, `Scope`, `Permission_id`) VALUES (18, 2, 21, 'World', 4);
TILLEND;
        $return=execute_batch_sql($con, $sql);
    }
    return $return;
}


function install_group_members($con) {
    $csql = "SELECT * FROM GroupMember";
    $crst=$con->execute($csql);
    $return=true;
    if (!$crst) {
        $sql=<<<TILLEND
CREATE TABLE `GroupMember` (
  `GroupMember_id` int(10) unsigned NOT NULL auto_increment,
  `Group_id` int(10) unsigned NOT NULL default '0',
  `ControlledObject_id` int(10) unsigned NOT NULL default '0',
  `on_what_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`GroupMember_id`),
  KEY `Group_id` (`Group_id`),
  KEY `ControlledObject_id` (`ControlledObject_id`)
)
TILLEND;
    
        $rst=$con->execute($sql);
        if (!$rst) { db_error_handler($con, $sql); return false;}
        $crst=$con->execute($csql);
    }
    if ($crst->numRows()==0) {
        $sql=<<<TILLEND

TILLEND;
        $return=execute_batch_sql($con, $sql);
    }
    return $return;
}

function install_data_sources($con) {
    $csql = "SELECT * FROM data_source";
    $crst=$con->execute($csql);
    $return=true;
    if (!$crst) {
        $sql=<<<TILLEND
CREATE TABLE `data_source` (
  `data_source_id` int(10) unsigned NOT NULL auto_increment,
  `data_source_name` varchar(128) NOT NULL default '',
  PRIMARY KEY  (`data_source_id`)
)
TILLEND;
    
        $rst=$con->execute($sql);
        if (!$rst) { db_error_handler($con, $sql); return false;}
        $crst=$con->execute($csql);
    }
    if ($crst->numRows()==0) {
        $sql=<<<TILLEND
INSERT INTO `data_source` VALUES (1, 'XRMS');
TILLEND;
        $return=execute_batch_sql($con, $sql);
    }
    return $return;
}

function install_group_users($con) {
    $csql = "SELECT * FROM GroupUser";
    $crst=$con->execute($csql);
    $return=true;
    if (!$crst) {
        $sql=<<<TILLEND
CREATE TABLE `GroupUser` (
  `GroupUser_id` int(10) unsigned NOT NULL auto_increment,
  `Group_id` int(10) unsigned NOT NULL default '0',
  `user_id` int(10) unsigned default NULL,
  `Role_id` int(10) unsigned default NULL,
  `ChildGroup_id` int(10) unsigned default NULL,
  PRIMARY KEY  (`GroupUser_id`),
  KEY `Group_id` (`Group_id`),
  KEY `Role_id` (`Role_id`),
  KEY `ChildGroup_id` (`ChildGroup_id`)
)
TILLEND;
    
        $rst=$con->execute($sql);
        if (!$rst) { db_error_handler($con, $sql); return false;}
        $crst=$con->execute($csql);
    }
    if ($crst->numRows()==0) {
        $sql=<<<TILLEND
TILLEND;
        $return=execute_batch_sql($con, $sql);
    }
    return $return;
}

function install_permissions($con) {
    $csql = "SELECT * FROM Permission";
    $crst=$con->execute($csql);
    $return=true;
    if (!$crst) {
        $sql=<<<TILLEND
CREATE TABLE `Permission` (
  `Permission_id` int(10) unsigned NOT NULL auto_increment,
  `Permission_name` varchar(64) NOT NULL default '',
  `Permission_abbr` varchar(5) NOT NULL default '',
  PRIMARY KEY  (`Permission_id`)
)
TILLEND;
    
        $rst=$con->execute($sql);
        if (!$rst) { db_error_handler($con, $sql); return false;}
        $crst=$con->execute($csql);
    }
    if ($crst->numRows()==0) {
        $sql=<<<TILLEND
INSERT INTO `Permission` (`Permission_id`, `Permission_name`, `Permission_abbr`) VALUES (1, 'Create', 'C');
INSERT INTO `Permission` (`Permission_id`, `Permission_name`, `Permission_abbr`) VALUES (2, 'Read', 'R');
INSERT INTO `Permission` (`Permission_id`, `Permission_name`, `Permission_abbr`) VALUES (3, 'Update', 'U');
INSERT INTO `Permission` (`Permission_id`, `Permission_name`, `Permission_abbr`) VALUES (4, 'Delete', 'D');
TILLEND;
        $return=execute_batch_sql($con, $sql);
    }
    return $return;
}

function install_roles($con) {
    $csql = "SELECT * FROM Role";
    $crst=$con->execute($csql);
    $return=true;
    if (!$crst) {
        $sql=<<<TILLEND
CREATE TABLE `Role` (
  `Role_id` int(10) unsigned NOT NULL auto_increment,
  `Role_name` varchar(128) NOT NULL default '',
  PRIMARY KEY  (`Role_id`)
)
TILLEND;
    
        $rst=$con->execute($sql);
        if (!$rst) { db_error_handler($con, $sql); return false;}
        $crst=$con->execute($csql);
    }
    if ($crst->numRows()==0) {
        $sql=<<<TILLEND
INSERT INTO `Role` (`Role_id`, `Role_name`) VALUES (1, 'User');
INSERT INTO `Role` (`Role_id`, `Role_name`) VALUES (2, 'Administrator');
TILLEND;
        $return=execute_batch_sql($con, $sql);
    }
    return $return;
}


function install_groups($con) {
    $gsql = "SELECT * FROM Groups";
    $grst=$con->execute($gsql);
    if (!$grst) {
        
        $sql=<<<TILLEND
CREATE TABLE `Groups` (
`Group_id` int(10) unsigned NOT NULL auto_increment,
`Group_name` varchar(128) NOT NULL default '',
PRIMARY KEY  (`Group_id`)
)   
TILLEND;
    
        $rst=$con->execute($sql);
        if (!$rst) { db_error_handler($con, $sql); return false; }
        $grst=$con->execute($gsql);
    }
    $return=true;
    if ($grst->numRows()==0) {
        $sql="INSERT INTO `Groups` (`Group_id`, `Group_name`) VALUES (1, 'Users');";
        
        $rst=$con->execute($sql);
        if (!$rst) { db_error_handler($con, $sql); $return=false;}
    }
    return $return;
}

function install_controlled_object_relationships($con) {
    $csql = "SELECT * FROM ControlledObjectRelationship";
    $crst=$con->execute($csql);
    $return=true;
    if (!$crst) {
        $sql=<<<TILLEND
CREATE TABLE `ControlledObjectRelationship` (
  `CORelationship_id` int(10) unsigned NOT NULL auto_increment,
  `ChildControlledObject_id` int(10) unsigned NOT NULL default '0',
  `ParentControlledObject_id` int(10) unsigned default NULL,
  `on_what_child_field` varchar(128) default NULL,
  `on_what_parent_field` varchar(128) default NULL,
  `cross_table` varchar(128) default NULL,
  `singular` tinyint(4) default NULL,
  PRIMARY KEY  (`CORelationship_id`),
  KEY `ParentControlledObject_id` (`ParentControlledObject_id`),
  KEY `ChildControlledObject_id` (`ChildControlledObject_id`)
)
TILLEND;
    
        $rst=$con->execute($sql);
        if (!$rst) { db_error_handler($con, $sql); return false;}
        $crst=$con->execute($csql);
    }
    if ($crst->numRows()==0) {
        $sql=<<<TILLEND
INSERT INTO `ControlledObjectRelationship` (`CORelationship_id`, `ChildControlledObject_id`, `ParentControlledObject_id`, `on_what_child_field`, `on_what_parent_field`, `cross_table`, `singular`) VALUES (1, 1, NULL, '', '', '', 0);
INSERT INTO `ControlledObjectRelationship` (`CORelationship_id`, `ChildControlledObject_id`, `ParentControlledObject_id`, `on_what_child_field`, `on_what_parent_field`, `cross_table`, `singular`) VALUES (2, 3, NULL, '', '', '', 0);
INSERT INTO `ControlledObjectRelationship` (`CORelationship_id`, `ChildControlledObject_id`, `ParentControlledObject_id`, `on_what_child_field`, `on_what_parent_field`, `cross_table`, `singular`) VALUES (3, 2, 1, '', '', '', 0);
INSERT INTO `ControlledObjectRelationship` (`CORelationship_id`, `ChildControlledObject_id`, `ParentControlledObject_id`, `on_what_child_field`, `on_what_parent_field`, `cross_table`, `singular`) VALUES (5, 4, 1, '', '', '', 0);
INSERT INTO `ControlledObjectRelationship` (`CORelationship_id`, `ChildControlledObject_id`, `ParentControlledObject_id`, `on_what_child_field`, `on_what_parent_field`, `cross_table`, `singular`) VALUES (6, 5, 1, '', '', '', 0);
INSERT INTO `ControlledObjectRelationship` (`CORelationship_id`, `ChildControlledObject_id`, `ParentControlledObject_id`, `on_what_child_field`, `on_what_parent_field`, `cross_table`, `singular`) VALUES (7, 6, 1, '', '', '', 0);
INSERT INTO `ControlledObjectRelationship` (`CORelationship_id`, `ChildControlledObject_id`, `ParentControlledObject_id`, `on_what_child_field`, `on_what_parent_field`, `cross_table`, `singular`) VALUES (8, 8, 1, '', '', '', 0);
INSERT INTO `ControlledObjectRelationship` (`CORelationship_id`, `ChildControlledObject_id`, `ParentControlledObject_id`, `on_what_child_field`, `on_what_parent_field`, `cross_table`, `singular`) VALUES (9, 6, 2, '', '', '', 1);
INSERT INTO `ControlledObjectRelationship` (`CORelationship_id`, `ChildControlledObject_id`, `ParentControlledObject_id`, `on_what_child_field`, `on_what_parent_field`, `cross_table`, `singular`) VALUES (10, 6, 3, '', '', '', 1);
INSERT INTO `ControlledObjectRelationship` (`CORelationship_id`, `ChildControlledObject_id`, `ParentControlledObject_id`, `on_what_child_field`, `on_what_parent_field`, `cross_table`, `singular`) VALUES (11, 6, 4, '', '', '', 1);
INSERT INTO `ControlledObjectRelationship` (`CORelationship_id`, `ChildControlledObject_id`, `ParentControlledObject_id`, `on_what_child_field`, `on_what_parent_field`, `cross_table`, `singular`) VALUES (12, 6, 8, '', '', '', 1);
INSERT INTO `ControlledObjectRelationship` (`CORelationship_id`, `ChildControlledObject_id`, `ParentControlledObject_id`, `on_what_child_field`, `on_what_parent_field`, `cross_table`, `singular`) VALUES (13, 6, 5, '', '', '', 1);
INSERT INTO `ControlledObjectRelationship` (`CORelationship_id`, `ChildControlledObject_id`, `ParentControlledObject_id`, `on_what_child_field`, `on_what_parent_field`, `cross_table`, `singular`) VALUES (14, 6, 2, '', '', '', 1);
INSERT INTO `ControlledObjectRelationship` (`CORelationship_id`, `ChildControlledObject_id`, `ParentControlledObject_id`, `on_what_child_field`, `on_what_parent_field`, `cross_table`, `singular`) VALUES (15, 7, 6, '', '', '', 1);
INSERT INTO `ControlledObjectRelationship` (`CORelationship_id`, `ChildControlledObject_id`, `ParentControlledObject_id`, `on_what_child_field`, `on_what_parent_field`, `cross_table`, `singular`) VALUES (16, 7, 1, '', '', '', 1);
INSERT INTO `ControlledObjectRelationship` (`CORelationship_id`, `ChildControlledObject_id`, `ParentControlledObject_id`, `on_what_child_field`, `on_what_parent_field`, `cross_table`, `singular`) VALUES (17, 7, 4, NULL, NULL, NULL, 1);
INSERT INTO `ControlledObjectRelationship` (`CORelationship_id`, `ChildControlledObject_id`, `ParentControlledObject_id`, `on_what_child_field`, `on_what_parent_field`, `cross_table`, `singular`) VALUES (18, 7, 3, '', '', '', 1);
INSERT INTO `ControlledObjectRelationship` (`CORelationship_id`, `ChildControlledObject_id`, `ParentControlledObject_id`, `on_what_child_field`, `on_what_parent_field`, `cross_table`, `singular`) VALUES (19, 7, 2, NULL, NULL, NULL, 1);
INSERT INTO `ControlledObjectRelationship` (`CORelationship_id`, `ChildControlledObject_id`, `ParentControlledObject_id`, `on_what_child_field`, `on_what_parent_field`, `cross_table`, `singular`) VALUES (20, 7, 5, '', '', '', 1);
INSERT INTO `ControlledObjectRelationship` (`CORelationship_id`, `ChildControlledObject_id`, `ParentControlledObject_id`, `on_what_child_field`, `on_what_parent_field`, `cross_table`, `singular`) VALUES (21, 9, NULL, '', '', '', 0);
TILLEND;
        $return=execute_batch_sql($con, $sql);
    }
    return $return;
}

function install_controlled_objects($con) {
    $csql = "SELECT * FROM ControlledObject";
    $crst=$con->execute($csql);
    $return=true;
    if (!$crst) {
        $sql=<<<TILLEND
CREATE TABLE `ControlledObject` (
  `ControlledObject_id` int(10) unsigned NOT NULL auto_increment,
  `ControlledObject_name` varchar(128) NOT NULL default '',
  `on_what_table` varchar(128) default NULL,
  `on_what_field` varchar(128) default NULL,
  `user_field` varchar(32) default NULL,
  `data_source_id` int(10) unsigned default NULL,
  PRIMARY KEY  (`ControlledObject_id`),
  KEY `data_source_id` (`data_source_id`)
)
TILLEND;
    
        $rst=$con->execute($sql);
        if (!$rst) { db_error_handler($con, $sql); return false;}
        $crst=$con->execute($csql);
    }
    if ($crst->numRows()==0) {
        $sql=<<<TILLEND
INSERT INTO `ControlledObject` (`ControlledObject_id`, `ControlledObject_name`, `on_what_table`, `on_what_field`, `user_field`, `data_source_id`) VALUES (1, 'Company', 'companies', 'company_id', '', 1);
INSERT INTO `ControlledObject` (`ControlledObject_id`, `ControlledObject_name`, `on_what_table`, `on_what_field`, `user_field`, `data_source_id`) VALUES (2, 'Contact', 'contacts', 'contact_id', '', 1);
INSERT INTO `ControlledObject` (`ControlledObject_id`, `ControlledObject_name`, `on_what_table`, `on_what_field`, `user_field`, `data_source_id`) VALUES (3, 'Campaign', 'campaigns', 'campaign_id', '', 1);
INSERT INTO `ControlledObject` (`ControlledObject_id`, `ControlledObject_name`, `on_what_table`, `on_what_field`, `user_field`, `data_source_id`) VALUES (4, 'Case', 'cases', 'case_id', '', 1);
INSERT INTO `ControlledObject` (`ControlledObject_id`, `ControlledObject_name`, `on_what_table`, `on_what_field`, `user_field`, `data_source_id`) VALUES (5, 'Opportunity', 'opportunities', 'opportunity_id', '', 1);
INSERT INTO `ControlledObject` (`ControlledObject_id`, `ControlledObject_name`, `on_what_table`, `on_what_field`, `user_field`, `data_source_id`) VALUES (6, 'Activity', 'activities', 'activity_id', '', 1);
INSERT INTO `ControlledObject` (`ControlledObject_id`, `ControlledObject_name`, `on_what_table`, `on_what_field`, `user_field`, `data_source_id`) VALUES (7, 'File', 'files', 'file_id', '', 1);
INSERT INTO `ControlledObject` (`ControlledObject_id`, `ControlledObject_name`, `on_what_table`, `on_what_field`, `user_field`, `data_source_id`) VALUES (8, 'Division', 'company_division', 'division_id', '', 1);
INSERT INTO `ControlledObject` (`ControlledObject_id`, `ControlledObject_name`, `on_what_table`, `on_what_field`, `user_field`, `data_source_id`) VALUES (9, 'Administration', '', '', '', 1);
TILLEND;
        $return=execute_batch_sql($con, $sql);
    }
    return $return;
}

function execute_batch_sql($con, $sql) {
    $sql_array=explode(";",$sql);
    $return=true;
    foreach ($sql_array AS $sql_str) {
        if ($sql_str=trim($sql_str)) {
            $rst=$con->execute($sql_str);
            if (!$rst) { db_error_handler($con, $sql_str); $return=false; }
        }
    }
    return $return;
}

?>