<?php

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

$session_user_id = session_check();
$msg = $_GET['msg'];

$email_template_id = (strlen($_POST['email_template_id']) > 0) ? $_POST['email_template_id'] : $_GET['email_template_id'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
$con->execute("update users set last_hit = " . $con->dbtimestamp(mktime()) . " where user_id = $session_user_id");

$sql = "select * from email_templates where email_template_id = $email_template_id";

$rst = $con->execute($sql);
$email_template_title = $rst->fields['email_template_title'];
$email_template_body = $rst->fields['email_template_body'];
$rst->close();

$con->close();

$page_title = 'Edit Message';
start_page($page_title, true, $msg);

?>

<script language="javascript">

function updateTemplate() {
    document.forms[1].email_template_title.value = document.forms[0].email_template_title.value;
    document.forms[1].email_template_body.value = document.forms[0].email_template_body.value;
    document.forms[1].submit();
}

function saveAsNewTemplate() {
    document.forms[2].email_template_title.value = document.forms[0].email_template_title.value;
    document.forms[2].email_template_body.value = document.forms[0].email_template_body.value;
    document.forms[2].submit();
}

</script>

<div id="Main">
    <div id="Content">

        <form action=email-3.php method=post>
		<table class=widget cellspacing=1>
			<tr>
				<td class=widget_header colspan=2>Edit Message - <?php  echo $email_template_title ?></td>
			</tr>
			<tr>
                <td class=widget_label_right width="1%">Subject:</td>
				<td class=widget_content_form_element><input type=text name=email_template_title size=50 value="<?php  echo $email_template_title ?>"></td>
			</tr>
			<tr>
				<td class=widget_content_form_element colspan=2><textarea class=monospace rows=20 cols=80 name=email_template_body><?php  echo $email_template_body ?></textarea></td>
			</tr>
			<tr>
				<td class=widget_content_form_element colspan=2><input class=button type=submit value="Continue"> <input class=button onclick="javascript: updateTemplate();" type=button value="Update Template"> <input class=button type=button onclick="javascript: saveAsNewTemplate();" value="Save as New Template"></td>
			</tr>
		</table>
        </form>

    </div>

        <!-- right column //-->
    <div id="Sidebar">

        &nbsp;

    </div>

</div>

<form action=update-template.php method=post>
<input type=hidden name=email_template_id value="<?php  echo $email_template_id ?>">
<input type=hidden name=email_template_title>
<input type=hidden name=email_template_body>
</form>

<form action=save-as-new-template.php method=post>
<input type=hidden name=email_template_title>
<input type=hidden name=email_template_body>
</form>

<?php end_page(); ?>