<?php
/**
 * /admin/account-types/one.php
 *
 * Edit account-types
 *
 * $Id: one.php,v 1.3 2004/04/16 22:18:23 maulani Exp $
 */

require_once('../../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

$session_user_id = session_check();

$activity_type_id = $_GET['activity_type_id'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

$sql = "select * from activity_types where activity_type_id = $activity_type_id";

$rst = $con->execute($sql);

if ($rst) {
    
    $activity_type_short_name = $rst->fields['activity_type_short_name'];
    $activity_type_pretty_name = $rst->fields['activity_type_pretty_name'];
    $activity_type_pretty_plural = $rst->fields['activity_type_pretty_plural'];
    $activity_type_display_html = $rst->fields['activity_type_display_html'];
    $activity_type_score_adjustment = $rst->fields['activity_type_score_adjustment'];
    
    $rst->close();
}

$con->close();

$page_title = "One Activity Type : $activity_type_pretty_name";
start_page($page_title);

?>

<div id="Main">
    <div id="Content">

        <form action="edit-2.php" method=post>
        <input type=hidden name=activity_type_id value="<?php  echo $activity_type_id; ?>">
        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=4>Edit Activity Type Information</td>
            </tr>
            <tr>
                <td class=widget_label_right>Short Name</td>
                <td class=widget_content_form_element><input type=text name=activity_type_short_name value="<?php  echo $activity_type_short_name; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right>Full Name</td>
                <td class=widget_content_form_element><input type=text name=activity_type_pretty_name value="<?php  echo $activity_type_pretty_name; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right>Full Plural</td>
                <td class=widget_content_form_element><input type=text name=activity_type_pretty_plural value="<?php  echo $activity_type_pretty_plural; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right>Display HTML</td>
                <td class=widget_content_form_element><input type=text name=activity_type_display_html value="<?php  echo $activity_type_display_html; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right>Score Adjustment</td>
                <td class=widget_content_form_element><input type=text size=5 name=activity_type_score_adjustment value="<?php  echo $activity_type_score_adjustment; ?>"></td>
            </tr>
            <tr>
                <td class=widget_content_form_element colspan=2><input class=button type=submit value="Save Changes"></td>
            </tr>
        </table>
        </form>

        <form action="delete.php" method=post onsubmit="javascript: return confirm('Delete Activity Type?');">
        <input type=hidden name=activity_type_id value="<?php  echo $activity_type_id; ?>">
        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=4>Delete Activity Type</td>
            </tr>
            <tr>
                <td class=widget_content>
                Click the button below to remove this activity type from the system.
                <p>Note: This action CANNOT be undone!
                <p><input class=button type=submit value="Delete Activity Type">
                </td>
            </tr>
        </table>
        </form>

    </div>

        <!-- right column //-->
    <div id="Sidebar">

        &nbsp;

    </div>
</div>

<?php

end_page();

/**
 * $Log: one.php,v $
 * Revision 1.3  2004/04/16 22:18:23  maulani
 * - Add CSS2 Positioning
 *
 * Revision 1.2  2004/03/24 18:12:45  maulani
 * - add phpdoc
 *
 */
?>
