<?php
/**
 * Form for creating a new file
 *
 * $Id: new.php,v 1.12 2004/08/03 18:05:56 cpsource Exp $
 */

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();

$msg = isset($_GET['msg']) ? $_GET['msg'] : '';

$on_what_table = $_POST['on_what_table'];
$on_what_id    = $_POST['on_what_id'];
$return_url    = $_POST['return_url'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

if ($on_what_table == 'opportunities') {
    $sql = "select opportunity_title as attached_to_name from opportunities where opportunity_id = $on_what_id";
} elseif ($on_what_table == 'cases') {
    $sql = "select case_title as attached_to_name from cases where case_id = $on_what_id";
} elseif ($on_what_table == 'companies') {
    $sql = "select company_name as attached_to_name from companies where company_id = $on_what_id";
} elseif ($on_what_table == 'contacts') {
    $sql = "SELECT " . $con->Concat("first_names", "' '", "last_name") . " AS attached_to_name FROM contacts WHERE contact_id = $on_what_id";
} elseif ($on_what_table == 'campaigns') {
    $sql = "select campaign_title as attached_to_name from campaigns where campaign_id = $on_what_id";
}

$rst = $con->execute($sql);

if ($rst) {
  if ( !$rst->EOF ) {
    $attached_to_name = $rst->fields['attached_to_name'];
  } else {
    $attached_to_name = '';
  }
  $rst->close();
}

$con->close();

$page_title = _("Attach File");
start_page($page_title, true, $msg);

$file_entered_at = '';

?>

<?php jscalendar_includes(); ?>

<div id="Main">
    <div id="Content">

        <form enctype="multipart/form-data" action=new-2.php method=post>
        <input type="hidden" name="MAX_FILE_SIZE" value="<?php echo $max_file_size; ?>">
        <input type=hidden name=on_what_table value="<?php  echo $on_what_table ?>">
        <input type=hidden name=on_what_id value="<?php  echo $on_what_id ?>">
        <input type=hidden name=return_url value="<?php  echo $return_url ?>">
        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=2><?php echo _("File Information"); ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Attached To"); ?></td>
                <td class=widget_content_form_element><?php echo $attached_to_name; ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("File Name"); ?></td>
                <td class=widget_content_form_element><input type=text size=40 name=file_pretty_name></td>
            </tr>
            <tr>
                <td class=widget_label_right_166px><?php echo _("Description"); ?></td>
                <td class=widget_content_form_element><textarea rows=10 cols=100 name=file_description></textarea></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Date"); ?></td>
                <td class=widget_content_form_element>
                    <input type=text ID="f_date_c" name=file_entered_at value="<?php  echo $file_entered_at; ?>">
                    <img ID="f_trigger_c" style="CURSOR: hand" border=0 src="../img/cal.gif">
                </td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Upload"); ?></td>
                <td class=widget_content_form_element><input type=file name=file1></td>
            </tr>
            <tr>
                <td class=widget_content_form_element colspan=2><input class=button type=submit value="<?php echo _("Upload");?>"></td>
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
 * $Log: new.php,v $
 * Revision 1.12  2004/08/03 18:05:56  cpsource
 * - Set mime type when database entry is created
 *
 * Revision 1.11  2004/07/30 12:59:19  cpsource
 * - Handle $msg in the standard way
 *   Fix problem with Date field displaying garbage because
 *     date was undefined, and if E_ALL is turned on.
 *
 * Revision 1.10  2004/07/25 16:40:31  johnfawcett
 * - added gettext calls
 *
 * Revision 1.9  2004/06/15 14:24:44  gpowers
 * - placed calendar setup code inside <script> tag
 *
 * Revision 1.8  2004/06/12 07:20:40  introspectshun
 * - Now use ADODB GetInsertSQL, GetUpdateSQL, date and Concat functions.
 *
 * Revision 1.7  2004/06/04 17:28:03  gpowers
 * Applied Patch [ 965012 ] Calendar replacement By: miguel Gonçves - mig77
 * w/minor changes: changed includes to function, used complete php tags
 *
 * Revision 1.6  2004/04/17 16:04:30  maulani
 * - Add CSS2 positioning
 *
 * Revision 1.5  2004/04/16 22:22:06  maulani
 * - Add CSS2 positioning
 *
 * Revision 1.4  2004/04/08 17:00:11  maulani
 * - Update javascript declaration
 * - Add phpdoc
 *
 * Revision 1.3  2004/03/24 12:28:01  braverock
 * - allow editing of more file proprerties
 * - updated code provided by Olivier Colonna of Fontaine Consulting
 * - add phpdoc
 *
 */
?>
