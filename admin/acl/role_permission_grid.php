<?php
/**
 * Administration interface for managing permissions for one role
 *
 * $Id: role_permission_grid.php,v 1.4 2005/08/11 22:53:53 vanmer Exp $
 *
 */

require_once('../../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb/adodb-pager.inc.php');

global $http_site_root;

$session_user_id = session_check();

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
    $acl = new xrms_acl($acl_options);
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
                    $rolePerm=$acl->get_role_permission($gridrole_id, $cor, $scope, $perm);
                    if ($rolePerm) {
                        $current_permissions[$scope][$cor][$perm]=1;
                    } else $current_permissions[$scope][$cor][$perm]=0;
                }
            }
        
        }
    }

}
$css_theme='basic-left';
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
  //                  echo "PROCESSING $scope $cor $perm<br>";
                    if (array_key_exists("$scope,$cor,$perm",$_POST)) {
                        if ($current_permissions[$scope][$cor][$perm]!=1) {                        
//                            echo "acl->add_role_permission($gridrole_id,$cor,$scope,$perm);<br>";
                            $ret=$acl->add_role_permission($gridrole_id,$cor,$scope,$perm);
 //                           if ($ret) echo "PERMISSION SET $scope,$cor,$perm";
                            $current_permissions[$scope][$cor][$perm]=1;
                        }
                    } else {
                        if ($current_permissions[$scope][$cor][$perm]!=0) {
                            $ret=$acl->get_role_permission($gridrole_id,$cor,$scope,$perm);
                            $role_permission_id=$ret['RolePermission_id'];
                            $delret=$acl->delete_role_permission($role_permission_id);
//                            if ($delret) echo "DELETED $role_permission_id SUCCESSFULLY";
                            $current_permissions[$scope][$cor][$perm]=0;
                        }
                    }
                }
            }
        }                    
/*        foreach ($relationships as $cor=>$rel) {
            $ret=$acl->get_role_permission($gridrole_id, $cor, false, false);
        }
*/
    case 'showGrid':
        echo <<<TILLEND
                <input type=hidden name=grid_action value="assignPerms">
                <input type=hidden name=gridrole_id value="$gridrole_id">                
                <tr><td class=widget_header>Manage Permissions for $role_name</td></tr>
                <tr><td class=widget_content>
                <table class=widget><tr><th>&nbsp;</th>
TILLEND;
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
                            echo "<td><input type=checkbox name=\"{$scope},{$cor},{$perm}\"";
                            if ($current_permissions[$scope][$cor][$perm]) echo " CHECKED";
                            echo "></td>";
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