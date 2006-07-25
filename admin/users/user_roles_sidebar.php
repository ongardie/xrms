<?php
/**
 * Roles sidebar, used to display/edit roles for a user
 *
 * $Id: user_roles_sidebar.php,v 1.9 2006/07/25 20:25:54 vanmer Exp $
**/
if (!$edit_user_id) {
    $edit_user_id=$session_user_id;
    $action='readonly';
} else $action='edit';
if (!$user_roles_con) $user_roles_con=$con;
if (!$user_roles_handler) $user_roles_handler='/admin/acl/edit_GroupUser.php';
if (!$acl_datasource_name) $acl_datasource_name='default';
if (!$user_roles_returnvar) $user_roles_returnvar='return_url';
if (!$user_roles_returnform) $user_roles_returnform="document.forms[0]";

if (!$acl) $acl=get_acl_object($acl_options);
//hack to show ACL roles
$role_menu=get_role_list($acl, true, 'role_id', $role_id);

$user_roles=get_user_roles_with_groups($acl, $edit_user_id);
$this_return_url=urlencode($http_site_root.current_page());

$group_menu=get_group_select($user_roles_con, 'group', 1, '', false, $attributes='style="font-size: x-small; border: outset; width: 80px;"', 0);

if ($action=='edit') {
$role_rows="
<script language=javascript>
<!---
    function deleteRole(GroupUser_id) {
        $user_roles_returnform.$user_roles_returnvar.value='{$http_site_root}$user_roles_handler?acl_datasource_name=$acl_datasource_name&edit_user_id=$edit_user_id&userAction=deleteRole&GroupUser_id='+GroupUser_id + '&return_url=$this_return_url';
        $user_roles_returnform.submit();
    };
    function addRole() {
        var Role_id=document.user_role_sidebar_form.role_id.value;
        var Group_id=document.user_role_sidebar_form.group.value;
        var return_url='{$http_site_root}$user_roles_handler?acl_datasource_name=$acl_datasource_name&edit_user_id=$edit_user_id&userAction=addRole&role_id='+Role_id+'&group='+Group_id+'&return_url=$this_return_url';
        //alert(return_url);
        $user_roles_returnform.$user_roles_returnvar.value=return_url;
        $user_roles_returnform.submit();
    };
</script>";
}

if ($user_roles) {
    foreach ($user_roles as $gkey=>$user_role_array) {
        foreach ($user_role_array as $guser_id=>$user_role) {
            $group_user_info=get_group_user($acl, $guser_id);
            if ($group_user_info) {
                $group_user_info=current($group_user_info);
                $group_info=$group_user_info['group_name'];
                $role_rows.="<tr><td>$group_info</td><td>$user_role</td>";
                if ($action=='edit') {
                    $role_rows.="<td><input type=button class=button onclick=\"deleteRole($guser_id);\" value=\""._("Delete") . "\"></td>";
                }
                $role_rows.="</tr>";
            } else { $role_rows.="<tr><td colspan=2>"._("No Group Membership Info")."</td></tr>"; }
        }
    }
}
if ($action=='edit') {
$role_rows.="<tr><td>$group_menu</td><td>$role_menu</td><td><input type=button onclick=\"addRole()\" class=button name=btAddRole value=\""._("Add Role") . "\"></td></tr>";
$user_role_sidebar="
    <form method=POST name=user_role_sidebar_form action='{$http_site_root}$user_roles_handler'>
        <input type=hidden name=userAction value=addRole>
        <input type=hidden name=acl_datasource_name value=" . $acl_datasource_name . ">
        <input type=hidden name=edit_user_id value=$edit_user_id>";
}
if ($action=='edit') $colspan=3;
else $colspan=2;
$user_role_sidebar.= "<table class=widget><tr><td class=widget_header colspan=$colspan>"._("User Roles") ."</td></tr>";
$user_role_sidebar.= "<tr><td class=widget_label>"._("Group") . "</td><td class=widget_label>"._("Role") . "</td>";
if ($action=='edit') $user_role_sidebar.="<td class=widget_label>"._("Action") . "</td>";
$user_role_sidebar.="</tr>";
$user_role_sidebar.="
        $role_rows
    </table>
   </form>";

/*************************************************************************/
/**
 * $Log: user_roles_sidebar.php,v $
 * Revision 1.9  2006/07/25 20:25:54  vanmer
 * - ensure that group membership information is available before attempting to render it
 *
 * Revision 1.8  2006/04/25 18:13:10  jnhayart
 * Syntax error after remove Tillend
 *
 * Revision 1.7  2006/04/18 14:34:48  braverock
 * - remove TILLEND assignments for better support of gettext i18n
 *
 * Revision 1.6  2005/09/26 01:40:57  vanmer
 * - changed user roles sidebar to submit form on page for which sidebar is attached.
 * - changed return_url to allow form to save its data and then redirect to the user roles action
 * - returns back to current page once user roles action is complete
 *
 * Revision 1.5  2005/09/07 23:42:18  vanmer
 * - added parameter for acl datasource name into sidebar, to allow datasource to be set properly when adding/deleting
 * users roles
 *
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