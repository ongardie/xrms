<?php
/**
 * Edit the details for one user
 *
 * $Id: one.php,v 1.3 2004/03/15 15:39:05 braverock Exp $
 */

//include required files
require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

$session_user_id = session_check();

$user_id = $_GET['user_id'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

$sql = "select * from users where user_id = $user_id";

$rst = $con->execute($sql);

if ($rst) {

    $user_type_id = $rst->fields['user_type_id'];
    $username = $rst->fields['username'];
    $first_names = $rst->fields['first_names'];
    $last_name = $rst->fields['last_name'];
    $email = $rst->fields['email'];
    $role_id = $rst->fields['role_id'];
    $gmt_offset = $rst->fields['gmt_offset'];
    $language = $rst->fields['language'];

    $rst->close();
}

$sql2 = "select role_pretty_name, role_id from roles where role_record_status = 'a' order by role_id";
$rst = $con->execute($sql2);
$role_menu = $rst->getmenu2('role_id', $role_id, false);
$rst->close();

$con->close();

$page_title = "One User : $first_names $last_name";
start_page($page_title);

?>

<table border=0 cellpadding=0 cellspacing=0 width=100%>
    <tr>
        <td class=lcol width=35% valign=top>

        <form action=edit-2.php method=post>
        <input type=hidden name=user_id value="<?php  echo $user_id; ?>">
        <table class=widget cellspacing=1 width=100%>
            <tr>
                <td class=widget_header colspan=4>Edit User Information</td>
            </tr>
            <tr>
                <td class=widget_label_right>Role</td>
                <td class=widget_content_form_element><?php  echo $role_menu; ?></td>
            </tr>
            <tr>
                <td class=widget_label_right>Last Name</td>
                <td class=widget_content_form_element><input type=text size=30 name=last_name value="<?php  echo $last_name; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right>First Names</td>
                <td class=widget_content_form_element><input type=text size=30 name=first_names value="<?php  echo $first_names; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right>Username</td>
                <td class=widget_content_form_element><input type=text name=username value="<?php  echo $username; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right>E-Mail</td>
                <td class=widget_content_form_element><input type=text size=40 name=email value="<?php  echo $email; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right>Language</td>
                <td class=widget_content_form_element>English</td></td>
            </tr>
            <tr>
                <td class=widget_label_right>GMT Offset</td>
                <td class=widget_content_form_element><input type=text size=5 name=gmt_offset value="<?php  echo $gmt_offset; ?>"></td>
            </tr>
            <tr>
                <td class=widget_content_form_element colspan=2><input class=button type=submit value="Save Changes"> <input class=button type=button onclick="javascript: location.href='change-password.php?user_id=<?php echo $user_id ?>';" value="Change Password"></td>
            </tr>
        </table>
        </form>

        <form action=delete.php method=post onsubmit="javascript: return confirm('Delete User?');">
        <input type=hidden name=user_id value="<?php  echo $user_id; ?>">
        <table class=widget cellspacing=1 width=100%>
            <tr>
                <td class=widget_header colspan=4>Delete User</td>
            </tr>
            <tr>
                <td class=widget_content>
                Click the button below to remove this user from the system.
                <p>Note: This action CANNOT be undone!
                <p><input class=button type=submit value="Delete User">
                </td>
            </tr>
        </table>
        </form>

        </td>

        <!-- gutter //-->
        <td class=gutter width=1%>
        &nbsp;
        </td>

        <!-- right column //-->

        <td class=rcol width=64% valign=top>
        &nbsp;
        </td>

    </tr>
</table>

<?php

end_page();

/**
 * $Log: one.php,v $
 * Revision 1.3  2004/03/15 15:39:05  braverock
 * - properly set role_id in user edit page
 *  - fixes SF bug 876781
 * - add phpdoc
 *
 */
?>