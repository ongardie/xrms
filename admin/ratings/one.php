<?php
/**
 * Show details of a single rating
 *
 * $Id: one.php,v 1.2 2004/02/14 15:40:44 braverock Exp $
 */
require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

$session_user_id = session_check();

$rating_id = $_GET['rating_id'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

$sql = "select * from ratings where rating_id = $rating_id";

$rst = $con->execute($sql);

if ($rst) {

    $rating_short_name = $rst->fields['rating_short_name'];
    $rating_pretty_name = $rst->fields['rating_pretty_name'];
    $rating_pretty_plural = $rst->fields['rating_pretty_plural'];
    $rating_display_html = $rst->fields['rating_display_html'];

    $rst->close();
}

$con->close();

$page_title = "One Rating : $rating_pretty_name";
start_page($page_title);

?>

<table border=0 cellpadding=0 cellspacing=0 width=100%>
    <tr>
        <td class=lcol width=25% valign=top>

        <form action=edit-2.php method=post>
        <input type=hidden name=rating_id value="<?php  echo $rating_id; ?>">
        <table class=widget cellspacing=1 width=100%>
            <tr>
                <td class=widget_header colspan=4>Edit Rating Information</td>
            </tr>
            <tr>
                <td class=widget_label_right>Short Name</td>
                <td class=widget_content_form_element><input type=text name=rating_short_name value="<?php  echo $rating_short_name; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right>Full Name</td>
                <td class=widget_content_form_element><input type=text name=rating_pretty_name value="<?php  echo $rating_pretty_name; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right>Full Plural</td>
                <td class=widget_content_form_element><input type=text name=rating_pretty_plural value="<?php  echo $rating_pretty_plural; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right>Display HTML</td>
                <td class=widget_content_form_element><input type=text name=rating_display_html value="<?php  echo $rating_display_html; ?>"></td>
            </tr>
            <tr>
                <td class=widget_content_form_element colspan=2><input class=button type=submit value="Save Changes"></td>
            </tr>
        </table>
        </form>

        <form action=delete.php method=post onsubmit="javascript: return confirm('Delete Rating?');">
        <input type=hidden name=rating_id value="<?php  echo $rating_id; ?>">
        <table class=widget cellspacing=1 width=100%>
            <tr>
                <td class=widget_header colspan=4>Delete Rating</td>
            </tr>
            <tr>
                <td class=widget_content>
                Click the button below to remove this rating from the system.
                <p>Note: This action CANNOT be undone!
                <p><input class=button type=submit value="Delete Rating">
                </td>
            </tr>
        </table>
        </form>

        </td>

        <!-- gutter //-->
        <td class=gutter width=2%>
        &nbsp;
        </td>

        <!-- right column //-->

        <td class=rcol width=73% valign=top>
        &nbsp;
        </td>

    </tr>
</table>

<?php

end_page();

/**
 * $Log: one.php,v $
 * Revision 1.2  2004/02/14 15:40:44  braverock
 * - change return target to some.php per a SF bug
 * - add phpdoc
 *
 */
?>
