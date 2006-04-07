<?php
/**
 * Show the details for a single file
 *
 * $Id: one.php,v 1.24 2006/04/07 04:31:00 maulani Exp $
 */

//include required files
require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

getGlobalVar($file_id, 'file_id');

$on_what_id=$file_id;

$session_user_id = session_check();

// get call arguments
$msg = isset($_GET['msg']) ? $_GET['msg'] : '';
getGlobalVar($return_url, 'return_url');
$out_return_url=urlencode($return_url);

$con = get_xrms_dbconnection();

update_recent_items($con, $session_user_id, "files", $file_id);


// SQL Query (Note: the files.* may be selecting fields in use by a plugin such as OWL)
$sql = "select files.*, users.username, users.user_id from files, users where files.entered_by = users.user_id and file_id = $file_id";

$rst = $con->execute($sql);

//echo $sql;

$file_info = array();

if ($rst) {
    $file_info = $rst->fields;
    $file_info['entered_at'] = $con->userdate($rst->fields['entered_at']);
    $file_info['file_size'] = pretty_filesize($rst->fields['file_size']);
    $on_what_table = $rst->fields['on_what_table'];
    $on_what_id = $rst->fields['on_what_id'];

    $rst->close();
}



// add selection of attached_to entity
$table_name = table_name($on_what_table);
$table_name = $con->Concat(implode(", ' ', ", table_name($on_what_table)));
$table_singular = make_singular($on_what_table);

if ($table_singular AND $table_name)
{
   $sql1 = "SELECT $table_name
           AS attached_to_name from $on_what_table
           WHERE {$table_singular}_id=$on_what_id";
}

$rst1 = $con->execute($sql1);

if ($rst1) {
  if ( !$rst1->EOF ) {
        $attached_to_name = $rst1->fields['attached_to_name'];
     } else {
        $attached_to_name = '';
     }
  $rst1->close();
}



$file_plugin_params = array('file_info' => $file_info);
do_hook_function('file_get_file_info', $file_plugin_params);
$file_info =  $file_plugin_params['file_info'];


if($file_plugin_params['error_status']) {
    $error = true;
    $msg = $file_plugin_params['error_text'];
} else {
	$file_plugin_params = array('file_info' => $file_info);
	do_hook_function('file_get_one_file_html', $file_plugin_params);
	$file_one_html = $file_plugin_params['file_one_html'];
	$file_one_html_post = $file_plugin_params['file_one_html_post'];
	$extra_download_args = $file_plugin_params['file_one_extra_download_args'];
}	


$con->close();

$page_title = _("File Details").': '. $file_info['file_pretty_name'];
start_page($page_title, true, $msg);

?>

<?php jscalendar_includes(); ?>

<div id="Main">
    <div id="Content">

        <form enctype="multipart/form-data" action=edit-2.php onsubmit="javascript: return validate();" name="Files_One" method=post>
        <input type=hidden name=return_url value="<?php  echo $return_url; ?>">
        <input type=hidden name=file_id value="<?php  echo $file_info['file_id']; ?>">
        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=2><?php echo _("File Information");?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("ID"); ?></td>
                <td class=widget_content_form_element><?php  echo $file_info['file_id']; ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Filename"); ?></td>
                <td class=widget_content_form_element><?php  echo $file_info['file_name']; ?></td>
            </tr>

            <tr>
                <td class="widget_label_right">
                    <?php echo _("Attached to"); ?>
                    <?php  echo $table_singular ?>
                </td>
                <td class=clear><a href="<?php  echo $http_site_root?>/<?php  echo $on_what_table?>/one.php?<?php  echo $table_singular?>_id=<?php echo $on_what_id; ?>"><?php echo $attached_to_name; ?></td>
                </td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Summary"); ?></td>
                <td class=widget_content_form_element><input type=text size=40 name=file_pretty_name value="<?php  echo $file_info['file_pretty_name']; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Uploaded"); ?></td>
                <td class=widget_content_form_element><?php  echo $file_info['entered_at']; ?> by <?php echo $file_info['username']; ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Size"); ?></td>
                <td class=widget_content_form_element><?php  echo $file_info['file_size']; ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Description"); ?></td>
                <td class=widget_content_form_element><textarea rows=10 cols=100 name=file_description><?php  echo $file_info['file_description']; ?></textarea></td>
            </tr>
			<?php echo $file_one_html; ?>
            <tr>
                <td class=widget_label_right><?php echo _("Change Date"); ?></td>
                <td class=widget_content_form_element>
                    <input type=text ID="f_date_c" name=file_entered_at value="<?php  echo $file_info['entered_at']; ?>">
                    <img ID="f_trigger_c" style="CURSOR: hand" border=0 src="../img/cal.gif">
                </td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Change File"); ?></td>
                <td class=widget_content_form_element><input type=file name=file1></td>
            </tr>
            <tr>
                <td class=widget_content_form_element colspan=2> <?php echo render_edit_button("Save Changes", 'submit'); ?>
 <?php echo render_read_button("Download",'button',"javascript: window.open('download.php?file_id={$file_info['file_id']}&$extra_download_args');") ?> 
 <?php echo render_delete_button("Delete",'button',"javascript: location.href='delete.php?return_url=$out_return_url&file_id={$file_info['file_id']}';") ?></td>
            </tr>
        </table>
        </form>

			<?php echo $file_one_html_post; ?>
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
 *Revision 1.24  2006/04/07 04:31:00  maulani
 *- Remove extra semicolon
 *
 *Revision 1.23  2006/03/21 20:33:38  maulani
 *- Remove erroneous call-by-reference tag.  Function already defined with
 *  call-by-reference
 *
 *Revision 1.22  2006/01/02 23:03:52  vanmer
 *- changed to use centralized dbconnection function
 *
 *Revision 1.21  2005/12/14 05:05:02  daturaarutad
 *change File Name to Summary, show true filename
 *
 *Revision 1.20  2005/11/17 16:47:52  daturaarutad
 *added patch by dbaudone which shows attached to: field
 *
 *Revision 1.19  2005/11/09 22:36:32  daturaarutad
 *add hooks for files plugin
 *
 *Revision 1.18  2005/09/23 19:47:03  daturaarutad
 *updated for file plugin (owl support)
 *
 *Revision 1.17  2005/06/24 23:27:00  vanmer
 *- return url is now accepted from either post or get
 *- return url is now urlencoded when passed to delete
 *
 *Revision 1.16  2005/06/22 20:39:31  vanmer
 *- now downloads occur in a new window
 *
 *Revision 1.15  2005/05/04 14:36:53  braverock
 *- removed obsolete CSS widget_label_right_166px, replaced with widget_label_right
 *
 *Revision 1.14  2005/04/10 16:42:19  maulani
 *- RFE 1107920 (maulani) Display file_id on one.php screen
 *
 *Revision 1.13  2005/01/13 18:47:28  vanmer
 *- Basic ACL changes to allow display functionality to be restricted
 *
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
