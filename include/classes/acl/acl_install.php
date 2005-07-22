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
                $role='User';
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
    
    update_acl($con);
    
    if (!$inst_ret) { echo "ACL Install Failed<br>"; return false; }
    
    $sql = "SELECT * from GroupUser";
    $rst = $con->execute($sql);
    if (!$rst) db_error_handler($con, $sql);
    if ($rst->numRows()==0) {
        $inst_ret=upgrade_acl_users($con);
   } else $inst_ret=true;
    if (!$inst_ret) { echo "ACL User Update Failed<br>"; return false; }
    
    $sql = "SELECT * FROM ControlledObjectRelationship";
    $rst = $con->SelectLimit($sql,1);
    if (!$rst) db_error_handler($con, $sql);
    if ($rst->numRows()>0) {
        if ($rst->fields['ControlledObjectRelationship_id']) {
            $sql = "ALTER TABLE RolePermission CHANGE ControlledObjectRelationship_id CORelationship_id INT( 10 ) UNSIGNED DEFAULT '0' NOT NULL";
            $rst=$con->execute($sql);
            if (!$rst) db_error_handler($con, $sql);
            $sql = "ALTER TABLE ControlledObjectRelationship CHANGE ControlledObjectRelationship_id CORelationship_id INT( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT";
            $rst=$con->execute($sql);
            if (!$rst) db_error_handler($con, $sql);
        }
    }
    $sql = "SELECT * FROM RolePermission";
    $rst = $con->SelectLimit($sql,1);
    if (!$rst) db_error_handler($con, $sql);
    if (!array_key_exists('Inheritable_flag',$rst->fields)) {
        $sql = "ALTER TABLE RolePermission ADD Inheritable_flag TINYINT DEFAULT '1' NOT NULL ";
        $rst=$con->execute($sql);
    }
    return true;   
}

function upgrade_acl_users($con) {

    $sql = "SELECT * from users, roles WHERE users.role_id=roles.role_id";
    $rst = $con->execute($sql);
    $install_status=true;
    if ($rst) {
        while (!$rst->EOF) {
            $ret=upgrade_role($rst->fields['user_id'],$rst->fields['role_short_name']);
            $rst->movenext();
            if (!$ret AND $install_status) {
                $install_status=false;
            }
        }
    } else return true;
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

function update_acl($con) {
    global $acl_options;
    global $include_directory;
    require_once($include_directory.'classes/acl/acl_wrapper.php');
    $acl = new xrms_acl($acl_options);
    $data_source=$acl->get_data_source('XRMS');
    $data_source_id=$data_source['data_source_id'];
    $admin_object_id=get_object_id($acl, 'Administration');
    $file_object_id=get_object_id($acl, 'File');
    $email_template_id=$acl->add_controlled_object("Email Template", 'email_templates','email_template_id', false, $data_source_id);
    $ret2=$acl->add_controlled_object_relationship($admin_object_id, $email_template_id);
    $ret3=$acl->add_controlled_object_relationship($email_template_id, $file_object_id, false, false, false, true);
    
        $csql = "SELECT * FROM Permission";
        $crst=$con->execute($csql);
        if ($crst->numRows()==4) {
            $sql.="insert into Permission (Permission_id, Permission_name, Permission_abbr) values (5, 'Export', 'E')";
            $sql .=";\n";
            $export_add=$con->execute($sql);
            $Role_id=get_role_id($acl, 'Administrator');
            $Scope='World';
            $Permission_id=5;
            
            $company_object_id=get_object_id($acl, 'Company');
            $CORelationship=$acl->get_controlled_object_relationship(NULL, $company_object_id);
            $CORelationship_id=$CORelationship['CORelationship_id'];
            $ret4=$acl->add_role_permission ($Role_id, $CORelationship_id, $Scope, $Permission_id, true);
            
            $CORelationship=$acl->get_controlled_object_relationship(NULL, $admin_object_id);
            $CORelationship_id=$CORelationship['CORelationship_id'];
            $ret5=$acl->add_role_permission ($Role_id, $CORelationship_id, $Scope, $Permission_id, true);
            
            $campaign_object_id=get_object_id($acl, 'Campaign');
            $CORelationship=$acl->get_controlled_object_relationship(NULL, $campaign_object_id);
            $CORelationship_id=$CORelationship['CORelationship_id'];
            $ret6=$acl->add_role_permission ($Role_id, $CORelationship_id, $Scope, $Permission_id, true);
        }
    
    return ($email_template_id AND $ret2 AND $ret3 AND $ret4 AND$ret5 AND $ret6 AND $export_add);
}

function install_role_permissions($con) {
    $csql = "SELECT * FROM RolePermission";
    $crst=$con->execute($csql);
    $return=true;
    if (!$crst) {
        $sql=<<<TILLEND
CREATE TABLE RolePermission (
  RolePermission_id int(10) unsigned NOT NULL auto_increment,
  Role_id int(10) unsigned NOT NULL default '0',
  CORelationship_id int(10) unsigned NOT NULL default '0',
  Scope enum('World','Group','User') NOT NULL default 'World',
  Permission_id int(10) unsigned NOT NULL default '0',
  Inheritable_flag TINYINT DEFAULT '1' NOT NULL,
  PRIMARY KEY  (RolePermission_id),
  KEY Role_id (Role_id),
  KEY CORelationship_id (CORelationship_id),
  KEY Permission_id (Permission_id)
)
TILLEND;
    
        $rst=$con->execute($sql);
        if (!$rst) { db_error_handler($con, $sql); return false;}
        $crst=$con->execute($csql);
    }
    if ($crst->numRows()==0) {
        $sql=<<<TILLEND
insert into RolePermission (Role_id, CORelationship_id, Scope, Permission_id) values (2, 1, 'World', 1);
insert into RolePermission (Role_id, CORelationship_id, Scope, Permission_id) values (2, 1, 'World', 2);
insert into RolePermission (Role_id, CORelationship_id, Scope, Permission_id) values (2, 1, 'World', 3);
insert into RolePermission (Role_id, CORelationship_id, Scope, Permission_id) values (2, 1, 'World', 4);
insert into RolePermission (Role_id, CORelationship_id, Scope, Permission_id) values (2, 1, 'World', 5);
insert into RolePermission (Role_id, CORelationship_id, Scope, Permission_id) values (1, 1, 'World', 1);
insert into RolePermission (Role_id, CORelationship_id, Scope, Permission_id) values (1, 1, 'World', 2);
insert into RolePermission (Role_id, CORelationship_id, Scope, Permission_id) values (1, 1, 'World', 3);
insert into RolePermission (Role_id, CORelationship_id, Scope, Permission_id) values (1, 2, 'World', 1);
insert into RolePermission (Role_id, CORelationship_id, Scope, Permission_id) values (1, 2, 'World', 2);
insert into RolePermission (Role_id, CORelationship_id, Scope, Permission_id) values (1, 2, 'World', 3);
insert into RolePermission (Role_id, CORelationship_id, Scope, Permission_id) values (2, 2, 'World', 1);
insert into RolePermission (Role_id, CORelationship_id, Scope, Permission_id) values (2, 2, 'World', 2);
insert into RolePermission (Role_id, CORelationship_id, Scope, Permission_id) values (2, 2, 'World', 3);
insert into RolePermission (Role_id, CORelationship_id, Scope, Permission_id) values (2, 2, 'World', 4);
insert into RolePermission (Role_id, CORelationship_id, Scope, Permission_id) values (2, 2, 'World', 5);
insert into RolePermission (Role_id, CORelationship_id, Scope, Permission_id) values (2, 21, 'World', 1);
insert into RolePermission (Role_id, CORelationship_id, Scope, Permission_id) values (2, 21, 'World', 2);
insert into RolePermission (Role_id, CORelationship_id, Scope, Permission_id) values (2, 21, 'World', 3);
insert into RolePermission (Role_id, CORelationship_id, Scope, Permission_id) values (2, 21, 'World', 4);
insert into RolePermission (Role_id, CORelationship_id, Scope, Permission_id) values (2, 21, 'World', 5);
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
CREATE TABLE GroupMember (
  GroupMember_id int(10) unsigned NOT NULL auto_increment,
  Group_id int(10) unsigned NOT NULL default '0',
  ControlledObject_id int(10) unsigned NOT NULL default '0',
  on_what_id int(11) NOT NULL default '0',
  PRIMARY KEY  (GroupMember_id),
  KEY Group_id (Group_id),
  KEY ControlledObject_id (ControlledObject_id)
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
CREATE TABLE data_source (
  data_source_id int(10) unsigned NOT NULL auto_increment,
  data_source_name varchar(128) NOT NULL default '',
  PRIMARY KEY  (data_source_id)
)
TILLEND;
    
        $rst=$con->execute($sql);
        if (!$rst) { db_error_handler($con, $sql); return false;}
        $crst=$con->execute($csql);
    }
    if ($crst->numRows()==0) {
        $sql=<<<TILLEND
insert into data_source values (1, 'XRMS');
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
CREATE TABLE GroupUser (
  GroupUser_id int(10) unsigned NOT NULL auto_increment,
  Group_id int(10) unsigned NOT NULL default '0',
  user_id int(10) unsigned default NULL,
  Role_id int(10) unsigned default NULL,
  ChildGroup_id int(10) unsigned default NULL,
  PRIMARY KEY  (GroupUser_id),
  KEY Group_id (Group_id),
  KEY Role_id (Role_id),
  KEY ChildGroup_id (ChildGroup_id)
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
CREATE TABLE Permission (
  Permission_id int(10) unsigned NOT NULL auto_increment,
  Permission_name varchar(64) NOT NULL default '',
  Permission_abbr varchar(5) NOT NULL default '',
  PRIMARY KEY  (Permission_id)
)
TILLEND;
    
        $rst=$con->execute($sql);
        if (!$rst) { db_error_handler($con, $sql); return false;}
        $crst=$con->execute($csql);
    }
    if ($crst->numRows()==0) {
        $sql='';
$sql.="insert into Permission (Permission_id, Permission_name, Permission_abbr) values (1, 'Create', 'C')";
$sql .=";\n";
$sql.="insert into Permission (Permission_id, Permission_name, Permission_abbr) values (2, 'Read', 'R')";
$sql .=";\n";
$sql.="insert into Permission (Permission_id, Permission_name, Permission_abbr) values (3, 'Update', 'U')";
$sql .=";\n";
$sql.="insert into Permission (Permission_id, Permission_name, Permission_abbr) values (4, 'Delete', 'D')";
$sql .=";\n";
$sql.="insert into Permission (Permission_id, Permission_name, Permission_abbr) values (5, 'Export', 'E')";
$sql .=";\n";
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
CREATE TABLE Role (
  Role_id int(10) unsigned NOT NULL auto_increment,
  Role_name varchar(128) NOT NULL default '',
  PRIMARY KEY  (Role_id)
)
TILLEND;
    
        $rst=$con->execute($sql);
        if (!$rst) { db_error_handler($con, $sql); return false;}
        $crst=$con->execute($csql);
    }
    if ($crst->numRows()==0) {
        $sql='';
        $sql.=
"insert into Role (Role_id, Role_name) values (1, 'User')";
$sql .=";\n";
        $sql.=
"insert into Role (Role_id, Role_name) values (2, 'Administrator')";
$sql .=";\n";
        $return=execute_batch_sql($con, $sql);
    }
    return $return;
}


function install_groups($con) {
    $gsql = "SELECT * FROM Groups";
    $grst=$con->execute($gsql);
    if (!$grst) {
        
        $sql=<<<TILLEND
CREATE TABLE Groups (
Group_id int(10) unsigned NOT NULL auto_increment,
Group_name varchar(128) NOT NULL default '',
PRIMARY KEY  (Group_id)
)   
TILLEND;
    
        $rst=$con->execute($sql);
        if (!$rst) { db_error_handler($con, $sql); return false; }
        $grst=$con->execute($gsql);
    }
    $return=true;
    if ($grst->numRows()==0) {
        $sql="insert into Groups (Group_id, Group_name) values (1, 'Users');";
        
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
CREATE TABLE ControlledObjectRelationship (
  CORelationship_id int(10) unsigned NOT NULL auto_increment,
  ChildControlledObject_id int(10) unsigned NOT NULL default '0',
  ParentControlledObject_id int(10) unsigned default NULL,
  on_what_child_field varchar(128) default NULL,
  on_what_parent_field varchar(128) default NULL,
  cross_table varchar(128) default NULL,
  singular tinyint(4) default NULL,
  PRIMARY KEY  (CORelationship_id),
  KEY ParentControlledObject_id (ParentControlledObject_id),
  KEY ChildControlledObject_id (ChildControlledObject_id)
)
TILLEND;
    
        $rst=$con->execute($sql);
        if (!$rst) { db_error_handler($con, $sql); return false;}
        $crst=$con->execute($csql);
    }
    if ($crst->numRows()==0) {
        $sql=<<<TILLEND
insert into ControlledObjectRelationship (CORelationship_id, ChildControlledObject_id, ParentControlledObject_id, on_what_child_field, on_what_parent_field, cross_table, singular) values (1, 1, NULL, '', '', '', 0);
insert into ControlledObjectRelationship (CORelationship_id, ChildControlledObject_id, ParentControlledObject_id, on_what_child_field, on_what_parent_field, cross_table, singular) values (2, 3, NULL, '', '', '', 0);
insert into ControlledObjectRelationship (CORelationship_id, ChildControlledObject_id, ParentControlledObject_id, on_what_child_field, on_what_parent_field, cross_table, singular) values (3, 2, 1, '', '', '', 0);
insert into ControlledObjectRelationship (CORelationship_id, ChildControlledObject_id, ParentControlledObject_id, on_what_child_field, on_what_parent_field, cross_table, singular) values (5, 4, 1, '', '', '', 0);
insert into ControlledObjectRelationship (CORelationship_id, ChildControlledObject_id, ParentControlledObject_id, on_what_child_field, on_what_parent_field, cross_table, singular) values (6, 5, 1, '', '', '', 0);
insert into ControlledObjectRelationship (CORelationship_id, ChildControlledObject_id, ParentControlledObject_id, on_what_child_field, on_what_parent_field, cross_table, singular) values (7, 6, 1, '', '', '', 0);
insert into ControlledObjectRelationship (CORelationship_id, ChildControlledObject_id, ParentControlledObject_id, on_what_child_field, on_what_parent_field, cross_table, singular) values (8, 8, 1, '', '', '', 0);
insert into ControlledObjectRelationship (CORelationship_id, ChildControlledObject_id, ParentControlledObject_id, on_what_child_field, on_what_parent_field, cross_table, singular) values (9, 6, 2, '', '', '', 1);
insert into ControlledObjectRelationship (CORelationship_id, ChildControlledObject_id, ParentControlledObject_id, on_what_child_field, on_what_parent_field, cross_table, singular) values (10, 6, 3, '', '', '', 1);
insert into ControlledObjectRelationship (CORelationship_id, ChildControlledObject_id, ParentControlledObject_id, on_what_child_field, on_what_parent_field, cross_table, singular) values (11, 6, 4, '', '', '', 1);
insert into ControlledObjectRelationship (CORelationship_id, ChildControlledObject_id, ParentControlledObject_id, on_what_child_field, on_what_parent_field, cross_table, singular) values (12, 6, 8, '', '', '', 1);
insert into ControlledObjectRelationship (CORelationship_id, ChildControlledObject_id, ParentControlledObject_id, on_what_child_field, on_what_parent_field, cross_table, singular) values (13, 6, 5, '', '', '', 1);
insert into ControlledObjectRelationship (CORelationship_id, ChildControlledObject_id, ParentControlledObject_id, on_what_child_field, on_what_parent_field, cross_table, singular) values (14, 6, 2, '', '', '', 1);
insert into ControlledObjectRelationship (CORelationship_id, ChildControlledObject_id, ParentControlledObject_id, on_what_child_field, on_what_parent_field, cross_table, singular) values (15, 7, 6, '', '', '', 1);
insert into ControlledObjectRelationship (CORelationship_id, ChildControlledObject_id, ParentControlledObject_id, on_what_child_field, on_what_parent_field, cross_table, singular) values (16, 7, 1, '', '', '', 1);
insert into ControlledObjectRelationship (CORelationship_id, ChildControlledObject_id, ParentControlledObject_id, on_what_child_field, on_what_parent_field, cross_table, singular) values (17, 7, 4, NULL, NULL, NULL, 1);
insert into ControlledObjectRelationship (CORelationship_id, ChildControlledObject_id, ParentControlledObject_id, on_what_child_field, on_what_parent_field, cross_table, singular) values (18, 7, 3, '', '', '', 1);
insert into ControlledObjectRelationship (CORelationship_id, ChildControlledObject_id, ParentControlledObject_id, on_what_child_field, on_what_parent_field, cross_table, singular) values (19, 7, 2, NULL, NULL, NULL, 1);
insert into ControlledObjectRelationship (CORelationship_id, ChildControlledObject_id, ParentControlledObject_id, on_what_child_field, on_what_parent_field, cross_table, singular) values (20, 7, 5, '', '', '', 1);
insert into ControlledObjectRelationship (CORelationship_id, ChildControlledObject_id, ParentControlledObject_id, on_what_child_field, on_what_parent_field, cross_table, singular) values (21, 9, NULL, '', '', '', 0);
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
CREATE TABLE ControlledObject (
  ControlledObject_id int(10) unsigned NOT NULL auto_increment,
  ControlledObject_name varchar(128) NOT NULL default '',
  on_what_table varchar(128) default NULL,
  on_what_field varchar(128) default NULL,
  user_field varchar(32) default NULL,
  data_source_id int(10) unsigned default NULL,
  PRIMARY KEY  (ControlledObject_id),
  KEY data_source_id (data_source_id)
)
TILLEND;
    
        $rst=$con->execute($sql);
        if (!$rst) { db_error_handler($con, $sql); return false;}
        $crst=$con->execute($csql);
    }
    if ($crst->numRows()==0) {
    $sql='';
$sql.="insert into ControlledObject (ControlledObject_id, ControlledObject_name, on_what_table, on_what_field, user_field, data_source_id) values (1, 'Company', 'companies', 'company_id', '', 1)";
$sql.=";\n";
$sql.="insert into ControlledObject (ControlledObject_id, ControlledObject_name, on_what_table, on_what_field, user_field, data_source_id) values (2, 'Contact', 'contacts', 'contact_id', '', 1)";
$sql.=";\n";
$sql.="insert into ControlledObject (ControlledObject_id, ControlledObject_name, on_what_table, on_what_field, user_field, data_source_id) values (3, 'Campaign', 'campaigns', 'campaign_id', '', 1)";
$sql.=";\n";
$sql.="insert into ControlledObject (ControlledObject_id, ControlledObject_name, on_what_table, on_what_field, user_field, data_source_id) values (4, 'Case', 'cases', 'case_id', '', 1)";
$sql.=";\n";
$sql.="insert into ControlledObject (ControlledObject_id, ControlledObject_name, on_what_table, on_what_field, user_field, data_source_id) values (5, 'Opportunity', 'opportunities', 'opportunity_id', '', 1)";
$sql.=";\n";
$sql.="insert into ControlledObject (ControlledObject_id, ControlledObject_name, on_what_table, on_what_field, user_field, data_source_id) values (6, 'Activity', 'activities', 'activity_id', '', 1)";
$sql.=";\n";
$sql.="insert into ControlledObject (ControlledObject_id, ControlledObject_name, on_what_table, on_what_field, user_field, data_source_id) values (7, 'File', 'files', 'file_id', '', 1)";
$sql.=";\n";
$sql.="insert into ControlledObject (ControlledObject_id, ControlledObject_name, on_what_table, on_what_field, user_field, data_source_id) values (8, 'Division', 'company_division', 'division_id', '', 1)";
$sql.=";\n";
$sql.="insert into ControlledObject (ControlledObject_id, ControlledObject_name, on_what_table, on_what_field, user_field, data_source_id) values (9, 'Administration', '', '', '', 1)";
$sql.=";\n";
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