<?php
/**
 * Show the details for a single file
 *
 * $Id: one.php,v 1.12 2004/07/30 12:59:19 cpsource Exp $
 */

//include required files
require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();
// get call arguments
$msg = isset($_GET['msg']) ? $_GET['msg'] : '';

if ( isset($_GET['return_url']) ) {
    $return_url = $_GET['return_url'];
} else {
    $return_url = '';
}

$file_id = $_GET['file_id'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

update_recent_items($con, $session_user_id, "files", $file_id);

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

$page_title = _("File Details").': '. $file_pretty_name;
start_page($page_title, true, $msg);

?>

<?php jscalendar_includes(); ?>

<div id="Main">
    <div id="Content">

        <form enctype="multipart/form-data" action=edit-2.php onsubmit="javascript: return validate();" method=post>
        <input type=hidden name=return_url value="<?php  echo $return_url ?>">
        <input type=hidden name=file_id value="<?php  echo $file_id ?>">
        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=2><?php echo _("File Information");?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("File Name"); ?></td>
                <td class=widget_content_form_element><input type=text size=40 name=file_pretty_name value="<?php  echo $file_pretty_name ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Uploaded"); ?></td>
                <td class=widget_content_form_element><?php  echo $entered_at ?> by <?php echo $username; ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Size"); ?></td>
                <td class=widget_content_form_element><?php  echo $file_size ?></td>
            </tr>
            <tr>
                <td class=widget_label_right_166px><?php echo _("Description"); ?></td>
                <td class=widget_content_form_element><textarea rows=10 cols=100 name=file_description><?php  echo $file_description ?></textarea></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Change Date"); ?></td>
                <td class=widget_content_form_element>
                    <input type=text ID="f_date_c" name=file_entered_at value="<?php  echo $entered_at; ?>">
                    <img ID="f_trigger_c" style="CURSOR: hand" border=0 src="../img/cal.gif">
                </td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Change File"); ?></td>
                <td class=widget_content_form_element><input type=file name=file1></td>
            </tr>
            <tr>
                <td class=widget_content_form_element colspan=2><input type=submit class=button value="<?php echo _("Save Changes");?>"> <input type=button class=button onclick="javascript: location.href='download.php?file_id=<?php  echo $file_id ?>';" value="<?php echo _("Download"); ?>"> <input type=button class=button onclick="javascript: location.href='delete.php?return_url=<?php echo $return_url; ?>&file_id=<?php echo $file_id; ?>';" value="<?php echo _("Delete"); ?>"></td>
            </tr>
        </table>
        </form>

    </div>

        <!-- right column //-->
    <div id="Sidebar">

        &nbsp;

    </div>

</div>

<script language="JavaScript" type="text/javascript">

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

Calendar.setup({
        inputField     :    "f_date_c",      // id of the input field
        ifFormat       :    "%Y-%m-%d %H:%M:%S",       // format of the input field
        showsTime      :    true,            // will display a time selector
        button         :    "f_trigger_c",   // trigger for the calendar (button ID)
        singleClick    :    false,           // double-click mode
        step           :    1,                // show all years in drop-down boxes (instead of every other year as default)
        align          :    "Bl"           // alignment (defaults to "Bl")
    });

</script>

<?php

end_page();

/**
 *$Log: one.php,v $
 *Revision 1.12  2004/07/30 12:59:19  cpsource
 *- Handle $msg in the standard way
 *  Fix problem with Date field displaying garbage because
 *    date was undefined, and if E_ALL is turned on.
 *
 *Revision 1.11  2004/07/25 16:34:00  johnfawcett
 *- added gettext
 *
 *Revision 1.10  2004/07/10 13:30:16  braverock
 *- fixed unitialized variables errors
 *
 *Revision 1.9  2004/06/12 07:20:40  introspectshun
 *- Now use ADODB GetInsertSQL, GetUpdateSQL, date and Concat functions.
 *
 *Revision 1.8  2004/06/04 17:27:26  gpowers
 *Applied Patch [ 965012 ] Calendar replacement By: miguel Gonçves - mig77
 *w/minor changes: changed includes to function, used complete php tags
 *
 *Revision 1.7  2004/04/17 16:04:30  maulani
 *- Add CSS2 positioning
 *
 *Revision 1.6  2004/04/16 22:22:06  maulani
 *- Add CSS2 positioning
 *
 *Revision 1.5  2004/04/08 17:00:11  maulani
 *- Update javascript declaration
 *- Add phpdoc
 *
 *Revision 1.4  2004/03/24 12:29:56  braverock
 *- update recently viewed items (braverock)
 *- allow editing of file date
 *- updated code provided by Olivier Colonna of Fontaine Consulting
 *
 *Revision 1.3  2004/03/07 14:05:28  braverock
 *add phpdoc
 *
 */
?>
