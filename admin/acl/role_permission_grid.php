<?php
/**
 * Administration interface for managing permissions for one role
 *
 * $Id: role_permission_grid.php,v 1.12 2006/07/13 00:47:20 vanmer Exp $
 *
 */

require_once('../../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb/adodb-pager.inc.php');

global $http_site_root;

$session_user_id = session_check('Admin');

require_once ($include_directory.'classes/acl/xrms_acl_config.php');

$con = get_acl_dbconnection();



getGlobalVar($gridrole_id,'gridrole_id');

getGlobalVar($grid_action,'grid_action');
/*
echo "<pre>";
print_r($_POST);
print_r($_SESSION);
echo "</pre>";
*/
if (!$grid_action) { $grid_action="chooseRole"; }
$page_title = _("Manage Permissions for Role");

if ($grid_action=='showGrid' && !$gridrole_id) { $grid_action="chooseRole"; $msg="Please select a role"; }

if ($grid_action=='chooseRole') {
    $rst=$con->execute("SELECT Role_name, Role_id FROM Role ORDER BY Role_name");
    if (!$rst) db_error_handler($con, $sql);
    $role_menu=$rst->getMenu2('gridrole_id','');
}
if ($gridrole_id) {
    $sql="SELECT Role_name FROM Role WHERE Role_id=$gridrole_id";
    $rst=$con->execute($sql);
    if (!$rst) db_error_handler($con, $sql);
    if ($rst->numRows()>0) {
        $role_name=$rst->fields['Role_name'];
    }
    $acl_options=$options;
    $acl = get_acl_object($acl_options, $con);
    $relationships = $acl->get_controlled_object_relationship(false, false, false,true);
    if (!is_array($relationships)) { $msg="Error, relationships failed";} else {
        if (!is_array(current($relationships))) { $relationships=array($relationships); }
        $permissions = $acl->get_permissions_list();
        $permcount = count($permissions);
        foreach ($permissions as $pkey=>$perm) {
            $Perm_data=$acl->get_permission(false, $perm);
            $permission_names[$pkey]=$Perm_data['Permission_name'];
        }
        $scopes = $acl->get_scope_list();
        foreach ($scopes as $scope) {
            foreach ($permissions as $perm) {
                foreach ($relationships as $cor => $relationship) {
                    $rolePerm=$acl->get_role_permission($gridrole_id, $cor, $scope, $perm, false, false);
                    if ($rolePerm) {
                        $rolePerm=current($rolePerm);
//                        print_r($rolePerm);
                        if ($rolePerm['Inheritable_flag']) {
                            $current_permissions[$scope][$cor][$perm]=2;
                        } else {
                            $current_permissions[$scope][$cor][$perm]=1;
                        }
                    } else $current_permissions[$scope][$cor][$perm]=0;
                }
            }
        
        }
    }

}
if ($grid_action=='assignPerms') { $msg.=_("Permissions Assigned"); }
start_page($page_title, true, $msg);
 echo '<div id="Main">';
 include('xrms_acl_nav.php');
echo "        <div id=\"Content\">
            <table class=widget>
            <form action=\"role_permission_grid.php\" method=\"POST\">";

switch ($grid_action) {
    case 'assignPerms':
//        echo "<pre>"; print_r($_POST); echo "</pre>";
        foreach ($current_permissions as $scope =>$scopedata) {
            foreach ($scopedata as $cor=>$permdata) {
                foreach ($permdata as $perm=>$value) {
//                 echo "PROCESSING $scope $cor $perm $value<br>";
                    if (array_key_exists("$scope,$cor,$perm",$_POST)) {
                        if ($current_permissions[$scope][$cor][$perm]!=$_POST["$scope,$cor,$perm"]) {
//                            echo "CHANGING PERMISSION FROM {$current_permissions[$scope][$cor][$perm]} TO {$_POST["$scope,$cor,$perm"]}<br>";
                            if ($current_permissions[$scope][$cor][$perm] OR !$_POST["$scope,$cor,$perm"]) {
//                                echo "DELETING PERMISSION {$current_permissions[$scope][$cor][$perm]} FOR $scope,$cor,$perm<br>";
                                $ret=$acl->get_role_permission($gridrole_id,$cor,$scope,$perm);
                                $ret=current($ret);
                                $role_permission_id=$ret['RolePermission_id'];
                                $delret=$acl->delete_role_permission($role_permission_id);
                            }
                            if ($_POST["$scope,$cor,$perm"]) {
                                if ($_POST["$scope,$cor,$perm"]==1) $inheritable=false;
                                if ($_POST["$scope,$cor,$perm"]==2) $inheritable=true;
//                                echo "ADDING PERMISSION {$_POST["$scope,$cor,$perm"]} FOR $scope,$cor,$perm<br>";
                                $ret=$acl->add_role_permission($gridrole_id,$cor,$scope,$perm,$inheritable);
                            }
                            $current_permissions[$scope][$cor][$perm]=$_POST["$scope,$cor,$perm"];
                        }
                    }
                }
            }
        }
    case 'showGrid':
        echo <<<TILLEND
                <input type=hidden name=grid_action value="assignPerms">
                <input type=hidden name=gridrole_id value="$gridrole_id">                
                <tr><td class=widget_header>Manage Permissions for $role_name</td></tr>
                <tr><td class=widget_content>
TILLEND;
                echo _("Role Permissions are used to manage the intersection between roles, permissions, and controlled object relationships.  Permissions for a role are assigned on individual Controlled Object Relationships.  Each permission assigned can be considered inheritable or non-inheritable.  Inheritable permissions will propagate through to all controlled objects related to the child object in the relationship for which the permission is being defined.  For example, inheritable World Read on the top level Company relationship will extend to all contacts, activities, files, cases, opportunities and other entities which are linked to all companies.  Non-inheritable permissions mean that this permission is only valid for the current level of Controlled Object Relationship, and will not extend to child relationships.");
                echo "<p><b>". _("Permission Codes") . "</b><br>";
                echo "<ul><li>" . _("I - Permission is Inheritable") . '<br>';
                echo "<li>" . _("N - Permission is Non-Inheritable") . '<br>';
                echo "<li>" . _("empty - No Permission assigned") . '<br></ul>';
                echo "<table class=widget><tr><th>&nbsp;</th>\n";
                foreach ($scopes as $scope) {
                    echo "<th colspan=$permcount>$scope</th>";
                }
                echo "</tr><tr><th>&nbsp;</th>";
                
               foreach ($scopes as $scope) {
                foreach ($permission_names as $perm) { 
                    echo "<th>$perm</th>";
                }
               }
               echo "</tr>";
            
                foreach ($relationships as $cor=>$rel) {
                    if ($rel['ParentControlledObject_id']) {
                        $parentObject=$acl->get_controlled_object(false,$rel['ParentControlledObject_id']);
                        $parent_name=" -> {$parentObject['ControlledObject_name']}";
                    } else $parent_name="";
                    echo "<tr><td class=widget_label_right>{$rel['ControlledObject_name']}{$parent_name}</td>";
                    foreach ($scopes as $scope) {
                        foreach ($permissions as $perm) {
                            $inherit_selected=($current_permissions[$scope][$cor][$perm]==2) ? 'SELECTED' : '';
                            $noninherit_selected=($current_permissions[$scope][$cor][$perm]==1) ? 'SELECTED' : '';
                            echo "<td><select name=\"{$scope},{$cor},{$perm}\">";
                            echo "<option value=0></option>";
                            echo "<option value=2 $inherit_selected>"._("I")."</option>";
                            echo "<option value=1 $noninherit_selected>"._("N")."</option>";
                            echo "</select>";
//                            if ($current_permissions[$scope][$cor][$perm]) echo " CHECKED";
                            echo "</td>";
                        }
                    }
                    echo "</tr>";
                }
//                print_r($relationships);
                echo "</table></td></tr>";
                echo "<tr><td class=widget_content><input type=submit class=button value=\"Assign Permissions\"></td></tr>";
    break;       
    case 'chooseRole':
        echo <<<TILLEND
                <input type=hidden name=grid_action value="showGrid">
                <tr><td colspan=2 class=widget_header>Select a Role</td></tr>
                <tr><td class=widget_label_right>Role:</td>
                <td class=widget_content>
                    $role_menu
                </td></tr>
                <tr><td colspan=2 class=widget_content>
                    <input type=submit value="Manage Permission for Role" class=button>
                </td></tr>
TILLEND;
    break;
}
echo "            </form>
            </table>
</div></div>";
end_page();
?>