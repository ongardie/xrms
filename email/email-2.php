<?php 
/** 
* 
* Email 2. 
* 
* $Id: email-2.php,v 1.10 2004/12/02 18:21:37 niclowe Exp $ 
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
 
$con = &adonewconnection($xrms_db_dbtype); 
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname); 
//$con->debug=true; 
$sql = "SELECT * FROM users WHERE user_id = '".$session_user_id."'"; 
$rst = $con->execute($sql); 
$default_user=$rst->fields['email'];

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
<td class=widget_header colspan=2><?php echo _("Edit Message"); ?> - <?php echo $email_template_title ?></td> 
</tr> 
<tr> 
<td class=widget_label_right width="1%" nowrap><?php echo _("From"); ?>:</td> 
<td class=widget_content_form_element><input type=text name="sender_name" size=50 value="<? echo $default_user ?>"><?php echo $required_indicator; ?></td> 
</tr> 
<tr> 
<td class=widget_label_right width="1%" nowrap><?php echo _("Reply to"); ?>:</td> 
<td class=widget_content_form_element><input type=text name="sender_address" size=50 value="<? echo $default_user ?>"><?php echo $required_indicator; ?></td> 
</tr> 
<tr> 
<td class=widget_label_right width="1%" nowrap><?php echo _("Bcc"); ?>:</td> 
<td class=widget_content_form_element><input type=text name="bcc_address" size=50 value=""></td> 
</tr> 
<tr> 
 
<tr> 
<td class=widget_label_right width="1%" nowrap><?php echo _("Subject"); ?>:</td> 
<td class=widget_content_form_element><input type=text name=email_template_title size=50 value="<?php echo $email_template_title ?>"></td> 
</tr> 
<tr> 
<td class=widget_content_form_element colspan=2><textarea class=monospace rows=20 cols=80 name=email_template_body><?php echo $email_template_body ?></textarea></td> 
</tr> 
<tr> 
<td class=widget_content_form_element colspan=2><input class=button type=submit value="<?php echo _("Continue"); ?>"> <input class=button onclick="javascript: updateTemplate();" type=button value="<?php echo _("Update Template"); ?>"> <input class=button type=button onclick="javascript: saveAsNewTemplate();" value="<?php echo _("Save as New Template"); ?>"></td> 
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
<input type=hidden name=email_template_id value="<?php echo $email_template_id ?>"> 
<input type=hidden name=email_template_title> 
<input type=hidden name=email_template_body> 
</form> 
 
<form action=save-as-new-template.php method=post> 
<input type=hidden name=email_template_title> 
<input type=hidden name=email_template_body> 
</form> 
 
<script language=javascript type="text/javascript" > 
 
function initialize() { 
document.forms[0].sender_name.select(); 
// document.forms[0].company_name.focus(); 
} 
 
function validate() { 
 
var numberOfErrors = 0; 
var msgToDisplay = ''; 
 
if (document.forms[0].sender_name.value == '') { 
numberOfErrors ++; 
msgToDisplay += '\n<?php echo _("You must enter a name to let the recipient know who the email is from."); ?>'; 
} 
 
if (document.forms[0].sender_address.value == '') { 
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
* $Log: email-2.php,v $
* Revision 1.10  2004/12/02 18:21:37  niclowe
* added default email origination from user table, added completed activity when a bulk email is sent
*
* Revision 1.9  2004/10/19 17:52:07  niclowe
* fixed script error contributed by konig here
* http://sourceforge.net/forum/forum.php?thread_id=1140799&forum_id=305409
* 
* Revision 1.8 2004/08/18 00:06:16 niclowe 
* Fixed bug 941839 - Mail Merge not working 
* 
* Revision 1.7 2004/08/04 21:46:42 introspectshun 
* - Localized strings for i18n/l10n support 
* - All paths now relative to include-locations-location.inc 
* 
*/ 
?>