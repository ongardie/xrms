<?php
/**
 * Form for creating a new file
 *
 * $Id: new.php,v 1.5 2004/04/16 22:22:06 maulani Exp $
 */

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

$session_user_id = session_check();
$msg = $_GET['msg'];

$on_what_table = $_POST['on_what_table'];
$on_what_id = $_POST['on_what_id'];
$return_url = $_POST['return_url'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

if ($on_what_table == 'opportunities') {
    $sql = "select opportunity_title as attached_to_name from opportunities where opportunity_id = $on_what_id";
} elseif ($on_what_table == 'cases') {
    $sql = "select case_title as attached_to_name from cases where case_id = $on_what_id";
} elseif ($on_what_table == 'companies') {
    $sql = "select company_name as attached_to_name from companies where company_id = $on_what_id";
} elseif ($on_what_table == 'contacts') {
    $sql = "select concat(first_names, ' ', last_name) as attached_to_name from contacts where contact_id = $on_what_id";
} elseif ($on_what_table == 'campaigns') {
    $sql = "select campaign_title as attached_to_name from campaigns where campaign_id = $on_what_id";
}

$rst = $con->execute($sql);

if ($rst) {
    $attached_to_name = $rst->fields['attached_to_name'];
    $rst->close();
}

$con->close();

$page_title = "Attach File";
start_page($page_title, true, $msg);

?>

<script language="javascript" src="<?php  echo $http_site_root; ?>/js/calendar1.js"></script>
<table border=0 cellpadding=0 cellspacing=0 width=100%>
    <tr>
        <td class=lcol width=55% valign=top>

        <form enctype="multipart/form-data" action=new-2.php method=post>
        <input type="hidden" name="MAX_FILE_SIZE" value="<?php echo $max_file_size; ?>">
        <input type=hidden name=on_what_table value="<?php  echo $on_what_table ?>">
        <input type=hidden name=on_what_id value="<?php  echo $on_what_id ?>">
        <input type=hidden name=return_url value="<?php  echo $return_url ?>">
        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=2>File Information</td>
            </tr>
            <tr>
                <td class=widget_label_right>Attached&nbsp;To</td>
                <td class=widget_content_form_element><?php echo $attached_to_name; ?></td>
            </tr>
            <tr>
                <td class=widget_label_right>File&nbsp;Name</td>
                <td class=widget_content_form_element><input type=text size=40 name=file_pretty_name></td>
            </tr>
            <tr>
                <td class=widget_label_right_166px>Description</td>
                <td class=widget_content_form_element><textarea rows=10 cols=100 name=file_description></textarea></td>
            </tr>
            <tr>
                <td class=widget_label_right>Date</td>
                <td class=widget_content_form_element><input type=text name=file_entered_at value="<?php  echo $file_entered_at; ?>"> <a href="javascript:cal1.popup();"><img class=date_picker border=0 src="../img/cal.gif"></a></td>
            </tr>
            <tr>
                <td class=widget_label_right>Upload</td>
                <td class=widget_content_form_element><input type=file name=file1></td>
            </tr>
            <tr>
                <td class=widget_content_form_element colspan=2><input class=button type=submit value="Upload"></td>
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

<script language="JavaScript" type="text/javascript">

function initialize() {
    document.forms[0].file_pretty_name.focus();
}

initialize();

<!--

// create calendar object(s) just after form tag closed
// specify form element as the only parameter (document.forms['formname'].elements['inputname']);
// note: you can have as many calendar objects as you need for your application

    var cal1 = new calendar1(document.forms[0].elements['file_entered_at']);
    cal1.year_scroll = false;
    cal1.time_comp = false;

//-->
</script>

<?php

end_page();

/**
 * $Log: new.php,v $
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