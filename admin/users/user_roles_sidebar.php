<?php
/**
 * Roles sidebar, used to display/edit roles for a user
 *
 * $Id: user_roles_sidebar.php,v 1.1 2005/06/07 21:34:35 vanmer Exp $
**/

//hack to show ACL roles
$role_menu=get_role_list(false, true, 'role_id', $role_id); 

$user_roles=get_user_roles_with_groups(false, $edit_user_id);
$return_url=$http_site_root.current_page();
$sql = "SELECT Group_name, Group_id FROM Groups";
$group_rst=$con->execute($sql);
$group_menu=$group_rst->getMenu2('group', 1, false, false, 0, 'style="font-size: x-small; border: outset; width: 80px;"');
$role_rows=<<<TILLEND
<script language=javascript>
<!---
    function deleteRole(GroupUser_id) {
        location.href='{$http_site_root}/admin/acl/edit_GroupUser.php?return_url=$return_url&edit_user_id=$edit_user_id&userAction=deleteRole&GroupUser_id='+GroupUser_id;
    };
</script>
TILLEND;
foreach ($user_roles as $gkey=>$user_role_array) {
    foreach ($user_role_array as $guser_id=>$user_role) {
        $group_user_info=get_group_user(false, $guser_id);
        $group_user_info=current($group_user_info);
        $group_info=$group_user_info['Group_name'];
        $role_rows.="<tr><td>$group_info</td><td>$user_role</td><td><input type=button class=button onclick=\"deleteRole($guser_id);\" value=\""._("Delete") . "\"></td></tr>";
    }
}
$role_rows.="<tr><td>$group_menu</td><td>$role_menu</td><td><input type=submit class=button name=btAddRole value=\""._("Add Role") . "\"></td></tr>";

$user_role_sidebar=<<<TILLEND
    <form method=POST action='edit-2.php'>
        <input type=hidden name=userAction value=addRole>
        <input type=hidden name=edit_user_id value=$edit_user_id>
TILLEND;
$user_role_sidebar.= "<table class=widget><tr><td class=widget_header colspan=3>"._("User Roles") ."</td></tr>";
$user_role_sidebar.= "<tr><td class=widget_label>"._("Group") . "</td><td class=widget_label>"._("Role") . "</td><td class=widget_label>"._("Action") . "</td></tr>";
$user_role_sidebar.=<<<TILLEND
        $role_rows
    </table>
   </form>
TILLEND;
/**
 * $Log: user_roles_sidebar.php,v $
 * Revision 1.1  2005/06/07 21:34:35  vanmer
 * - new sidebar to show and manage user roles
 *
**/
?>