<?php
/**
 *
 * Email 2.
 *
 * $Id: one-template.php,v 1.3 2006/01/02 23:02:14 vanmer Exp $
 */

require_once('include-locations-location.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();
$msg = $_GET['msg'];

$email_template_id = (strlen($_POST['email_template_id']) > 0) ? $_POST['email_template_id'] : $_GET['email_template_id'];

$con = get_xrms_dbconnection();
//$con->debug=true;
$sql = "SELECT * FROM users WHERE user_id = '".$session_user_id."'";
$rst = $con->execute($sql);

$rec = array();
$rec['last_hit'] = Time();


$upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
$con->execute($upd);

$sql = "select * from email_templates where email_template_id = $email_template_id";

$rst = $con->execute($sql);
$email_template_title = $rst->fields['email_template_title'];
$email_template_body = $rst->fields['email_template_body'];
$rst->close();

$con->close();

$page_title = _("Edit Message");
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

        <form action=email-3.php onsubmit="javascript: return validate();" method=post>
		<table class=widget cellspacing=1>
			<tr>
				<td class=widget_header colspan=2><?php echo _("Edit Message"); ?> - <?php  echo $email_template_title ?></td>
			</tr>
			<tr>
                		<td class=widget_label_right width="1%" nowrap><?php echo _("From"); ?>:</td>
				<td class=widget_content_form_element><input type=text name="sender_name" size=50 value=""><?php echo $required_indicator; ?></td>
			</tr>
			<tr>
                		<td class=widget_label_right width="1%" nowrap><?php echo _("Reply to"); ?>:</td>
				<td class=widget_content_form_element><input type=text name="sender_address" size=50 value=""><?php echo $required_indicator; ?></td>
			</tr>
			<tr>
                		<td class=widget_label_right width="1%" nowrap><?php echo _("Bcc"); ?>:</td>
				<td class=widget_content_form_element><input type=text name="bcc_address" size=50 value=""></td>
			</tr>
			<tr>
                		
			<tr>
                <td class=widget_label_right width="1%" nowrap><?php echo _("Subject"); ?>:</td>
				<td class=widget_content_form_element><input type=text name=email_template_title size=50 value="<?php  echo $email_template_title ?>"></td>
			</tr>
			<tr>
				<td class=widget_content_form_element colspan=2><textarea class=monospace rows=20 cols=80 name=email_template_body><?php  echo $email_template_body ?></textarea></td>
			</tr>
			<tr>
				<td class=widget_content_form_element colspan=2><input class=button onclick="javascript: updateTemplate();" type=button value="<?php echo _("Update Template"); ?>"> <input class=button type=button onclick="javascript: saveAsNewTemplate();" value="<?php echo _("Save as New Template"); ?>"></td>
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

<script language=javascript type="text/javascript" >

function initialize() {
    document.forms[0].email_from.select();
    // document.forms[0].company_name.focus();
}

function validate() {

    var numberOfErrors = 0;
    var msgToDisplay = '';

    if (document.forms[0].email_from.value == '') {
        numberOfErrors ++;
        msgToDisplay += '\n<?php echo _("You must enter a name to let the recipient know who the email is from."); ?>';
    }

    if (document.forms[0].email_reply_to.value == '') {
        numberOfErrors ++;
        msgToDisplay += '\n<?php echo _("You must enter an reply address so the recipient can reply to the message."); ?>';
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
 * $Log: one-template.php,v $
 * Revision 1.3  2006/01/02 23:02:14  vanmer
 * - changed to use centralized dbconnection function
 *
 * Revision 1.2  2004/08/12 09:09:50  niclowe
 * fixed bug 998663 -  no email template appear when you click on URL
 *
 * Revision 1.7  2004/08/04 21:46:42  introspectshun
 * - Localized strings for i18n/l10n support
 * - All paths now relative to include-locations-location.inc
 *
 */
?>