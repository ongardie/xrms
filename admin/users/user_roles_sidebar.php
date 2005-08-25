<?php
/**
 * Roles sidebar, used to display/edit roles for a user
 *
 * $Id: user_roles_sidebar.php,v 1.4 2005/08/25 04:35:24 vanmer Exp $
**/
if (!$edit_user_id) {
    $edit_user_id=$session_user_id;
    $action='readonly';
} else $action='edit';
if (!$user_roles_con) $user_roles_con=$con;
if (!$user_roles_handler) $user_roles_handler='/admin/acl/edit_GroupUser.php';

if (!$acl) $acl=get_acl_object($acl_options);
//hack to show ACL roles
$role_menu=get_role_list($acl, true, 'role_id', $role_id); 

$user_roles=get_user_roles_with_groups($acl, $edit_user_id);
$return_url=$http_site_root.current_page();

$group_menu=get_group_select($user_roles_con, 'group', 1, '', false, $attributes='style="font-size: x-small; border: outset; width: 80px;"', 0);

if ($action=='edit') {
$role_rows=<<<TILLEND
<script language=javascript>
<!---
    function deleteRole(GroupUser_id) {
        location.href='{$http_site_root}$user_roles_handler?return_url=$return_url&edit_user_id=$edit_user_id&userAction=deleteRole&GroupUser_id='+GroupUser_id;
    };
</script>
TILLEND;
}

if ($user_roles) {
    foreach ($user_roles as $gkey=>$user_role_array) {
        foreach ($user_role_array as $guser_id=>$user_role) {
            $group_user_info=get_group_user($acl, $guser_id);
            $group_user_info=current($group_user_info);
            $group_info=$group_user_info['Group_name'];
            $role_rows.="<tr><td>$group_info</td><td>$user_role</td>";
            if ($action=='edit') {
                $role_rows.="<td><input type=button class=button onclick=\"deleteRole($guser_id);\" value=\""._("Delete") . "\"></td>";
            }
            $role_rows.="</tr>";
        }
    }
}
if ($action=='edit') {
$role_rows.="<tr><td>$group_menu</td><td>$role_menu</td><td><input type=submit class=button name=btAddRole value=\""._("Add Role") . "\"></td></tr>";
$user_role_sidebar=<<<TILLEND
    <form method=POST action='{$http_site_root}$user_roles_handler'>
        <input type=hidden name=userAction value=addRole>
        <input type=hidden name=edit_user_id value=$edit_user_id>
TILLEND;
}
if ($action=='edit') $colspan=3;
else $colspan=2;
$user_role_sidebar.= "<table class=widget><tr><td class=widget_header colspan=$colspan>"._("User Roles") ."</td></tr>";
$user_role_sidebar.= "<tr><td class=widget_label>"._("Group") . "</td><td class=widget_label>"._("Role") . "</td>";
if ($action=='edit') $user_role_sidebar.="<td class=widget_label>"._("Action") . "</td>";
$user_role_sidebar.="</tr>";
$user_role_sidebar.=<<<TILLEND
        $role_rows
    </table>
   </form>
TILLEND;
/**
 * $Log: user_roles_sidebar.php,v $
 * Revision 1.4  2005/08/25 04:35:24  vanmer
 * - changed sidebar to allow inclusion from multiple places, providing a db connection and ACL object and handler
 * - changed paths to use full xrms path for all actions
 *
 * Revision 1.3  2005/06/21 14:23:56  vanmer
 * - added check to ensure that any roles have been provided by the wrapper functions
 *
 * Revision 1.2  2005/06/07 21:45:21  vanmer
 * - altered to allow sidebar to be included from self, and be readonly
 *
 * Revision 1.1  2005/06/07 21:34:35  vanmer
 * - new sidebar to show and manage user roles
 *
**/
?>