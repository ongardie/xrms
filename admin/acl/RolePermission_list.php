<?php
/**
 * Manage list of RolePermissions
 *
 * Copyright (c) 2004 Explorer Fund Advisors, LLC
 * All Rights Reserved.
 *
 * @todo
 * $Id: RolePermission_list.php,v 1.8 2006/07/13 00:47:20 vanmer Exp $
 */

require_once('../../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'classes/Pager/GUP_Pager.php');
require_once($include_directory . 'classes/Pager/Pager_Columns.php');

global $http_site_root;

$session_user_id = session_check('Admin');

require_once ($include_directory.'classes/acl/xrms_acl_config.php');

$con = get_acl_dbconnection();


$page_title = _("Manage Role Permissions");

$select_sql="SELECT " . $con->Concat($con->qstr("<input type=\"button\" class=\"button\" value=\"Edit\" onclick=\"javascript: location.href='one_RolePermission.php?form_action=edit&return_url=RolePermission_list.php&RolePermission_id="), 'RolePermission_id', $con->qstr("'\">")) . "AS LINK,
Role_name as 'Role', Child.ControlledObject_name as 'ChildObject', Parent.ControlledObject_name as 'ParentObject', Scope, Permission_name as 'Permission', RolePermission.*, ControlledObjectRelationship.ChildControlledObject_id, ControlledObjectRelationship.ParentControlledObject_id";

$inheritance_select="CASE WHEN (Inheritable_flag=1) THEN " .$con->qstr(_("Yes")). " ELSE " . $con->qstr(_("No")) . " END";
$CORelationship_select="CASE WHEN (Parent.ControlledObject_id IS NOT NULL) THEN " . $con->Concat('Child.ControlledObject_name',$con->qstr(' -> '), 'Parent.ControlledObject_name') . " ELSE " . $con->Concat('Child.ControlledObject_name',$con->qstr(' -> '._("TOP"))). " END";
$select_sql .=", $inheritance_select as Inheritable, $CORelationship_select as CORelationship";

$from_sql="FROM RolePermission JOIN Permission ON Permission.Permission_id=RolePermission.Permission_id 
JOIN Role on Role.Role_id=RolePermission.Role_id
JOIN ControlledObjectRelationship ON ControlledObjectRelationship.CORelationship_id=RolePermission.CORelationship_id
JOIN ControlledObject as Child ON Child.ControlledObject_id=ControlledObjectRelationship.ChildControlledObject_id
LEFT OUTER JOIN ControlledObject as Parent ON Parent.ControlledObject_id=ControlledObjectRelationship.ParentControlledObject_id";

$sql = "$select_sql $from_sql";
$form_name="RolePermissionForm";


$role_list="SELECT " . $con->Concat('Role.Role_name', $con->qstr(' ('), 'count(RolePermission.Role_id)',$con->qstr(')')) . " AS 'RoleName', RolePermission.Role_id $from_sql GROUP BY RolePermission.Role_id ";
$role_select=$sql . " WHERE RolePermission.Role_id= XXX-value-XXX";

$permission_list="SELECT " . $con->Concat('Permission.Permission_name', $con->qstr(' ('), 'count(RolePermission.Permission_id)',$con->qstr(')')) . " AS 'PermissionName', RolePermission.Permission_id $from_sql GROUP BY RolePermission.Permission_id ";
$permission_select=$sql . " WHERE RolePermission.Permission_id= XXX-value-XXX";

$scope_list="SELECT " . $con->Concat('RolePermission.Scope', $con->qstr(' ('), 'count(RolePermission.Scope)',$con->qstr(')')) . " AS 'ScopeName', RolePermission.Scope $from_sql GROUP BY RolePermission.Scope ";
$scope_select=$sql . " WHERE RolePermission.Scope= ".$con->qstr('XXX-value-XXX');

$child_list="SELECT " . $con->Concat('Child.ControlledObject_name', $con->qstr(' ('), 'count(ControlledObjectRelationship.ChildControlledObject_id)',$con->qstr(')')) . " AS 'RoleName', ControlledObjectRelationship.ChildControlledObject_id $from_sql GROUP BY ControlledObjectRelationship.ChildControlledObject_id ";
$child_select=$sql . " WHERE ControlledObjectRelationship.ChildControlledObject_id= XXX-value-XXX";

$cor_list="SELECT " . $con->Concat($CORelationship_select,$con->qstr(' ('),'count(RolePermission.CORelationship_id)',$con->qstr(')')). ", RolePermission.CORelationship_id $from_sql GROUP BY RolePermission.CORelationship_id ";
$cor_select=$sql . " WHERE RolePermission.CORelationship_id= XXX-value-XXX";

$inherit_list="SELECT ".$con->Concat($inheritance_select,"' ('",'count(Inheritable_flag)',"')'").", Inheritable_flag $from_sql GROUP BY RolePermission.Inheritable_flag ";
$inherit_select=$sql . " WHERE Inheritable_flag = XXX-value-XXX";

    $columns = array();
    $columns[] = array('name' => _("Action"), 'index_sql' => 'LINK');
    $columns[] = array('name' => _("Role"), 'index_sql' => 'Role','group_query_list'=>$role_list, 'group_query_select'=>$role_select);
    $columns[] = array('name' => _("Relationship"), 'index_sql' => 'CORelationship', 'group_query_list'=>$cor_list, 'group_query_select'=>$cor_select);
    $columns[] = array('name' => _("Child Object"), 'index_sql' => 'ChildObject', 'group_query_list'=>$child_list, 'group_query_select'=>$child_select);
    $columns[] = array('name' => _("Parent Object"), 'index_sql' => 'ParentObject', 'group_query_list'=>$parent_list, 'group_query_select'=>$parent_select);    
    $columns[] = array('name' => _("Permission"), 'index_sql' => 'Permission', 'group_query_list'=>$permission_list, 'group_query_select'=>$permission_select );
    $columns[] = array('name' => _("Scope"), 'index_sql' => 'Scope','group_query_list'=>$scope_list, 'group_query_select'=>$scope_select);
    $columns[] = array('name' => _("Inheritable"), 'index_sql' => 'Inheritable','group_query_list'=>$inherit_list, 'group_query_select'=>$inherit_select);
    $columns[] = array('name' => _("Child Object ID"), 'index_sql' => 'ChildControlledObject_id');
    $columns[] = array('name' => _("Parent Object ID"), 'index_sql' => 'ParentControlledObject_id');
    $columns[] = array('name' => _("Permission ID"), 'index_sql' => 'Permission_id');
    $columns[] = array('name' => _("Relationship ID"), 'index_sql' => 'CORelationship_id');
    $columns[] = array('name' => _("Role ID"), 'index_sql' => 'Role_id');


    $default_columns=array('LINK','Role', 'ChildObject','ParentObject', 'Permission','Scope');
    
    $pager_columns = new Pager_Columns('RolePermissionPager', $columns, $default_columns, $form_name);
    $pager_columns_button = $pager_columns->GetSelectableColumnsButton();
    $pager_columns_selects = $pager_columns->GetSelectableColumnsWidget();

    $columns = $pager_columns->GetUserColumns('default');
    $colspan = count($columns);

        $endrows =  "
            <tr>
                <td colspan=$colspan class=widget_content_form_element>
                    $pager_columns_button<input type=\"button\" class=\"button\" value=\"". _("Add New"). "\" onclick=\"javascript: location.href='one_RolePermission.php?form_action=new&return_url=RolePermission_list.php'\">
                </td>
            </tr>";

   $pager = new GUP_Pager($con, $sql,null, _("Role Permissions"), $form_name, 'RolePermissionPager', $columns, false);

    $pager->AddEndRows($endrows);

start_page($page_title);


echo "<div id='Main'>";
require_once('xrms_acl_nav.php');
echo '<div id=Content>';
echo "<form method=\"POST\" name=\"$form_name\">";
echo $pager_columns_selects;
$pager->Render();

?>

</div></div></form>

<?php
end_page();

/**
 * $Log: RolePermission_list.php,v $
 * Revision 1.8  2006/07/13 00:47:20  vanmer
 * - changed all columns/pager combinations to reference the same pager name, to allow saved views to operate properly
 *
 * Revision 1.7  2006/07/09 05:04:03  vanmer
 * - patched ACL interface to check for admin access
 *
 * Revision 1.6  2005/07/28 19:55:48  vanmer
 * - changed to use new GUP_Pager for role permission list
 * - added grouping functionality on all applicable fields
 *
 * Revision 1.5  2005/05/18 06:24:51  vanmer
 * - added Inheritable flag to list of role permissions
 *
 * Revision 1.4  2005/05/10 13:28:14  braverock
 * - localized strings patches provided by Alan Baghumian (alanbach)
 *
 * Revision 1.3  2005/02/15 19:49:35  vanmer
 * - changes to reflect new fieldnames
 *
 * Revision 1.2  2005/02/14 23:35:36  vanmer
 * - added qstr and removed single quote slashes
 *
 * Revision 1.1  2005/01/13 17:16:15  vanmer
 * - Initial Commit for ACL Administration interface
 *
 * Revision 1.4  2004/12/27 23:48:50  ke
 * - adjusted to reflect new stylesheet
 *
 * Revision 1.3  2004/12/02 21:07:53  ke
 * - updated list to display useful information for roles and object relationships
 *
 * Revision 1.2  2004/12/02 09:34:24  ke
 * - added navigation sidebar to all list pages
 *
 * Revision 1.1  2004/12/02 05:15:17  ke
 * - Initial revision of role permission list and individual page
 *
 * Revision 1.1  2004/12/02 04:39:10  ke
 * - Initial revision of group user list and individual page
 *
 *
 */
?>
