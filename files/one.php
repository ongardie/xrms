<?php
/**
 * Show the details for a single file
 *
 * $Id: one.php,v 1.3 2004/03/07 14:05:28 braverock Exp $
 */

//include required files
require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

$session_user_id = session_check();
$msg = $_GET['msg'];

$return_url = $_GET['return_url'];
$file_id = $_GET['file_id'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

$sql = "select * from files, users where files.entered_by = users.user_id and file_id = $file_id";

$rst = $con->execute($sql);

if ($rst) {
    $file_pretty_name = $rst->fields['file_pretty_name'];
    $file_description = $rst->fields['file_description'];
    $entered_at = $con->userdate($rst->fields['entered_at']);
    $username = $rst->fields['username'];
    $file_size = pretty_filesize($rst->fields['file_size']);
    $rst->close();
}

$con->close();

$page_title = "One File : $file_pretty_name";
start_page($page_title, true, $msg);

?>

<table border=0 cellpadding=0 cellspacing=0 width=100%>
    <tr>
        <td class=lcol width=55% valign=top>

        <form action=edit-2.php onsubmit="javascript: return validate();" method=post>
        <input type=hidden name=return_url value="<?php  echo $return_url ?>">
        <input type=hidden name=file_id value="<?php  echo $file_id ?>">
        <table class=widget cellspacing=1 width=100%>
            <tr>
                <td class=widget_header colspan=2>File Information</td>
            </tr>
            <tr>
                <td class=widget_label_right>File&nbsp;Name</td>
                <td class=widget_content_form_element><input type=text size=40 name=file_pretty_name value="<?php  echo $file_pretty_name ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right>Uploaded</td>
                <td class=widget_content_form_element><?php  echo $entered_at ?> by <?php echo $username; ?></td>
            </tr>
            <tr>
                <td class=widget_label_right>Size</td>
                <td class=widget_content_form_element><?php  echo $file_size ?></td>
            </tr>
            <tr>
                <td class=widget_label_right_166px>Description</td>
                <td class=widget_content_form_element><textarea rows=10 cols=100 name=file_description><?php  echo $file_description ?></textarea></td>
            </tr>
            <tr>
                <td class=widget_content_form_element colspan=2><input type=submit class=button value="Save Changes"> <input type=button class=button onclick="javascript: location.href='download.php?file_id=<?php  echo $file_id ?>';" value="Download"> <input type=button class=button onclick="javascript: location.href='delete.php?return_url=<?php echo $return_url; ?>&file_id=<?php echo $file_id; ?>';" value="Delete"></td>
            </tr>
        </table>
        </form>

        </td>
        <!-- gutter //-->
        <td class=gutter width=2%>
        &nbsp;
        </td>
        <!-- right column //-->
        <td class=rcol width=43% valign=top>

        </td>
    </tr>
</table>

<script language=javascript>

function initialize() {
    document.forms[0].file_pretty_name.focus();
}

function validate() {

    var numberOfErrors = 0;
    var msgToDisplay = '';

    if (document.forms[0].file_pretty_name.value == '') {
        numberOfErrors ++;
        msgToDisplay += '\nYou must enter a file name.';
    }

    if (numberOfErrors > 0) {
        alert(msgToDisplay);
        return false;
    } else {
        return true;
    }

}

initialize();

</script>

<?php

end_page();

/**
 *$Log: one.php,v $
 *Revision 1.3  2004/03/07 14:05:28  braverock
 *add phpdoc
 *
 */
?>