<?php
/**
 * Form for creating a new folder
 *
 * $Id: new_folder.php,v 1.5 2006/01/09 21:38:24 daturaarutad Exp $
 */

require_once('../../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check('','Create');

$msg = isset($_GET['msg']) ? $_GET['msg'] : '';

// Inbound DB info
getGlobalVar($on_what_table, 'on_what_table');
getGlobalVar($on_what_id, 'on_what_id');
getGlobalVar($return_url, 'return_url');



$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);




// 'Should folders also be attached to something'????????????



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
} elseif ($on_what_table == 'company_division') {
    $sql = "select division_name as attached_to_name from company_division where division_id = $on_what_id";
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

$page_title = _("Attach Folder");
start_page($page_title, true, $msg);

$file_entered_at = '';

?>

<?php jscalendar_includes(); ?>

<div id="Main">
    <div id="Content">

        <form enctype="multipart/form-data" action=new_folder-2.php method=post>
        <input type=hidden name=on_what_table value="<?php  echo $on_what_table ?>">
        <input type=hidden name=on_what_id value="<?php  echo $on_what_id ?>">
        <input type=hidden name=return_url value="<?php  echo $return_url ?>">
        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=2><?php echo _("Folder Information"); ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Attached To"); ?></td>
                <td class=widget_content_form_element><?php echo $attached_to_name; ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Folder Name"); ?></td>
                <td class=widget_content_form_element><input type=text size=40 name=name></td>
            </tr>
<!-- Commented out for now
            <tr>
                <td class=widget_label_right_166px><?php echo _("Description"); ?></td>
                <td class=widget_content_form_element><textarea rows=10 cols=100 name=description></textarea></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Date"); ?></td>
                <td class=widget_content_form_element>
                    <input type=text ID="f_date_c" name=entered_at value="<?php  echo $file_entered_at; ?>">
                    <img ID="f_trigger_c" style="CURSOR: hand" border=0 src="../img/cal.gif">
                </td>
            </tr>
-->
            <tr>
                <td class=widget_content_form_element colspan=2><input class=button type=submit value="<?php echo _("Save");?>"></td>
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
 * $Log: new_folder.php,v $
 * Revision 1.5  2006/01/09 21:38:24  daturaarutad
 * add code to display division name when attached to division
 *
 * Revision 1.4  2005/12/14 04:29:02  daturaarutad
 * hide description and date fields for now
 *
 * Revision 1.3  2005/11/09 19:26:21  daturaarutad
 * use getGlobalVar instead of $_POST for CGI params
 *
 * Revision 1.2  2005/09/23 20:41:51  daturaarutad
 * tidy up comments
 *
 * Revision 1.1  2005/04/28 15:47:10  daturaarutad
 * new files
 *
 */
?>
