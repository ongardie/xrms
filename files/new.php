<?php

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

$con->close();

$page_title = "Attach File";
start_page($page_title, true, $msg);

?>

<table border=0 cellpadding=0 cellspacing=0 width=100%>
	<tr>
		<td class=lcol width=55% valign=top>

		<form enctype="multipart/form-data" action=new-2.php method=post>
		<input type="hidden" name="MAX_FILE_SIZE" value="<?php echo $max_file_size; ?>">
		<input type=hidden name=on_what_table value="<?php  echo $on_what_table ?>">
		<input type=hidden name=on_what_id value="<?php  echo $on_what_id ?>">
		<input type=hidden name=return_url value="<?php  echo $return_url ?>">
		<table class=widget cellspacing=1 width=100%>
			<tr>
				<td class=widget_header colspan=2>File Information</td>
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

<script language=javascript>

function initialize() {
	document.forms[0].file_pretty_name.focus();
}

initialize();

</script>

<?php end_page(); ?>
