<?php
echo '<div id=Sidebar>';
if ($xrms_acl_nav_extra) echo $xrms_acl_nav_extra;
echo '<table  name=mainNav class=widget>
<tr><td class=widget_header>'._("ACL Administration").'</td></tr>
<tr><td class=widget_content><a href="Role_list.php">'._("Manage Roles").'</a><br></li></td></tr>
<tr><td class=widget_content><a href="ControlledObject_list.php">'._("Manage Controlled Objects").'</a><br></li></td></tr>
<tr><td class=widget_content><a href="ControlledObjectRelationship_list.php">'._("Manage Controlled Object Relationships").'</a><br></li></td></tr>
<tr><td class=widget_content><a href="Groups_list.php">'._("Manage Groups").'</a><br></li></td></tr>
<tr><td class=widget_content><a href="GroupUser_list.php">'._("Manage Group Users").'</a><br></li></td></tr>
<tr><td class=widget_content><a href="GroupGroup_list.php">'._("Manage Group Groups").'</a><br></li></td></tr>
<tr><td class=widget_content><a href="GroupMember_list.php">'._("Manage Group Members").'</a><br></li></td></tr>
<tr><td class=widget_content><a href="Permission_list.php">'._("Manage Permissions").'</a><br></li></td></tr>
<tr><td class=widget_content><a href="RolePermission_list.php">'._("Manage Role Permissions").'</a><br></li></td></tr>
<tr><td class=widget_content><a href="role_permission_grid.php">'._("Manage Role Permissions in Grid").'</a><br></li></td></tr>
<tr><td class=widget_content><a href="data_source_list.php">'._("Manage Data Sources").'</a><br></li></td></tr>
<tr><td class=widget_content><a href="acl_results.php">'._("Test ACL Results").'</a><br></li></td></tr>
</table></div>';
?>
