<?php
/**
 * admin/users/some.php
 *
 * List system users.
 *
 * $Id: some.php,v 1.13 2005/01/09 15:53:05 braverock Exp $
 */

require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check( 'Admin' );

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

$sql = "select * from users, roles
where users.role_id = roles.role_id
and user_record_status = 'a' order by last_name, first_names";
$rst = $con->execute($sql);

if ($rst) {
    while (!$rst->EOF) {
        $table_rows .= '<tr>';
        $table_rows .= '<td class=widget_content>' . $rst->fields['last_name'] . ', ' . $rst->fields['first_names'] . '</td>';
        $table_rows .= '<td class=widget_content>' . $rst->fields['email'] . '</td>';
        $table_rows .= '<td class=widget_content>' . $rst->fields['role_pretty_name'] . '</td>';
        $table_rows .= '<td class=widget_content><a href="one.php?edit_user_id=' . $rst->fields['user_id'] . '">' . $rst->fields['username'] . '</a></td>';
        $table_rows .= '</tr>';
        $rst->movenext();
    }
    $rst->close();
} else {
    $table_rows = '<tr><td>'._("Unable to get data from database").'</td> </tr>';
}

$sql2 = "select role_pretty_name, role_id from roles where role_record_status = 'a' order by role_id";
$rst = $con->execute($sql2);
$role_menu = $rst->getmenu2('role_id', '', false);
$rst->close();

$default_gst = get_system_parameter($con, 'Default GST Offset');
$con->close();

$page_title = _("Manage Users");
start_page($page_title);

?>

<div id="Main">
    <div id="Content">

        <table class=widget cellspacing=1 width="100%">
            <tr>
                <td class=widget_header colspan=4><?php echo _("Users"); ?></td>
            </tr>
            <tr>
                <td class=widget_label><?php echo _("Full Name"); ?></td>
                <td class=widget_label><?php echo _("E-Mail"); ?></td>
                <td class=widget_label><?php echo _("Role"); ?></td>
                <td class=widget_label><?php echo _("Username"); ?></td>
            </tr>
            <?php echo $table_rows;; ?>
        </table>
    </div>

    <!-- right column //-->
    <div id="Sidebar">

        <!-- right column //-->
        <form action="add-2.php" onsubmit="javascript: return validate();" method=post>
        <table class=widget cellspacing=1 width="100%">
            <tr>
                <td class=widget_header colspan=2><?php echo _("Add New User"); ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Role"); ?></td>
                <td class=widget_content_form_element><?php  echo $role_menu; ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Last Name"); ?></td>
                <td class=widget_content_form_element><input type=text name=last_name> <?php echo $required_indicator; ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("First Names"); ?></td>
                <td class=widget_content_form_element><input type=text name=first_names></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Username"); ?></td>
                <td class=widget_content_form_element><input type=text name=new_username> <?php echo $required_indicator; ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Password"); ?></td>
                <td class=widget_content_form_element><input type=password name=password> <?php echo $required_indicator; ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("E-Mail"); ?></td>
                <td class=widget_content_form_element><input type=text name=email></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Language"); ?></td>
                <td class=widget_content_form_element><?php echo _("English"); ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("GMT Offset"); ?></td>
                <td class=widget_content_form_element><input type=text size=5 name=gmt_offset value=<?php  echo $default_gst; ?>></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Allow Access"); ?></td>
                <td class=widget_content_form_element><input type=checkbox name=allowed_p checked></td>
            </tr>
            <tr>
                <td class=widget_content_form_element colspan=2><input class=button type=submit value="<?php echo _("Add"); ?>"></td>
            </tr>
        </table>
        </form>

    </div>

</div>

<script language=javascript type="text/javascript" >

function validate() {

    var numberOfErrors = 0;
    var msgToDisplay = '';

    if (document.forms[0].new_username.value == '') {
        numberOfErrors ++;
        msgToDisplay += '<?php echo _("You must enter a username."); ?>';
    }

    if (document.forms[0].last_name.value == '') {
        numberOfErrors ++;
        msgToDisplay += '\n<?php echo _("You must enter a last name."); ?>';
    }

    if (document.forms[0].password.value == '') {
        numberOfErrors ++;
        msgToDisplay += '\n<?php echo _("You must enter a password."); ?>';
    }

    if (numberOfErrors > 0) {
        alert(msgToDisplay);
        return false;
    } else {
        return true;
    }

}

</script>

<?php

end_page();

/**
 * $Log: some.php,v $
 * Revision 1.13  2005/01/09 15:53:05  braverock
 * - fix missing .= in $table_rows
 *
 * Revision 1.12  2005/01/09 15:27:52  braverock
 * - fix JS bug where checks are not on correct fields
 *   resolves SF bug 1035378 reported by pnobrept
 *
 * Revision 1.11  2005/01/08 01:31:34  introspectshun
 * - Fixed $table_rows undefined error
 *
 * Revision 1.10  2004/12/09 22:29:12  braverock
 * - rearrange output to order by name and place the userid link in the last column of the table
 *
 * Revision 1.9  2004/07/16 23:51:38  cpsource
 * - require session_check ( 'Admin' )
 *
 * Revision 1.8  2004/07/16 13:55:08  braverock
 * - localize strings for i18n translation support
 *   - applies modified patches from Sebastian Becker (hyperpac)
 *
 * Revision 1.7  2004/06/29 15:32:38  maulani
 * - Make username, lastname, and password required fields for new users.
 *
 * Revision 1.6  2004/06/14 22:50:14  introspectshun
 * - Add adodb-params.php include for multi-db compatibility.
 * - Now use ADODB GetInsertSQL, GetUpdateSQL functions.
 *
 * Revision 1.5  2004/05/17 17:23:43  braverock
 * - change $username to not conflict when register_globals is on (?!?)
 *   - fixed SF bug 952670 - credit to jmaguire123 and sirjo for troubleshooting
 *
 * Revision 1.4  2004/05/13 16:36:46  braverock
 * - modified to work safely even when register_globals=on
 *   (!?! == dumb administrators ?!?)
 * - changed $user_id to $edit_user_id to avoid security collisions
 *   - fixes multiple reports of user role switching on user edits.
 *
 * Revision 1.3  2004/05/10 20:55:11  maulani
 * - Auto-fill the default GST offset
 *
 * Revision 1.2  2004/04/16 14:44:32  maulani
 * - Add CSS2 positioning
 * - Cleanup HTML so page validates
 * - Add phpdoc
 */
?>