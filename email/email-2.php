<?php
/**
*
* Email 2.
*
* $Id: email-2.php,v 1.26 2007/06/13 17:08:19 niclowe Exp $
*/

require_once('include-locations-location.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'utils-files.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();
$msg = $_GET['msg'];

    //Turn $_POST array into variables
    extract($_POST);

if ( $_POST['act'] == 'add' )
{
    require_once $include_directory . 'classes/File/file_upload.php';

    // Create new Class
    $objUpFile = new file_upload( 'attach' );

    if ( $objUpFile->getErrorCode() )
    {
        echo 'Could not create Upload Object: ';
        echo $objUpFile->getErrorMsg();
        exit;
    }

    // Where do we want this file sent to
    $objUpFile->setDestDir ( $GLOBALS['file_storage_directory'] );

    $_SESSION['uploadPath'] = serialize($GLOBALS['file_storage_directory']);

    if ( $objUpFile->getErrorCode() )
    {
        echo 'Could not set Upload Directory: ';
        echo $objUpFile->getErrorMsg();
        exit;
    }

    // Now process uploaded file
    $objUpFile->processUpload();

    if ( $objUpFile->getErrorCode() )
    {
        echo 'Could not process upload file: ';
        echo $objUpFile->getErrorMsg();
        exit;
    }

    // We only need to create an array if one was passed
    if ( $_SESSION['attachment_list'] )
        $attach_list = $_SESSION['attachment_list'];

    // place new upload into the array
    $attach_list[$objUpFile->getFilename()] =  $objUpFile->getFilename();

    // Make names keys, which will remove dups
    $attach_list = array_flip(array_flip($attach_list));

    // now prep array for passing around
    $_SESSION['attachment_list'] = $attach_list;

}
else if ( $_POST['act'] == 'del' )
{
    // get attached files list to remove
    $attachedFile = $_POST['attachedFile'];

    // We only need to create an array if one was passed
    $attach_list = $_SESSION['attachment_list'];

    // remove files
    foreach ( $attachedFile as $_killFile )
    {
        unset ( $attach_list[$_killFile] );
    }

    // now prep array for passing around
    $_SESSION['attachment_list'] = $attach_list;
}
else
{
    $email_template_id = (strlen($_POST['email_template_id']) > 0) ? $_POST['email_template_id'] : $_GET['email_template_id'];

    $con = get_xrms_dbconnection();
    //$con->debug=true;
    $sql = "SELECT * FROM users WHERE user_id = '".$session_user_id."'";
    $rst = $con->execute($sql);
    $sender_name=$rst->fields['email'];

    $rec = array();
    $rec['last_hit'] = Time();

    $upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
    $con->execute($upd);

    $sql = "select * from email_templates where email_template_id = $email_template_id";

    $rst = $con->execute($sql);
    $email_template_title = $rst->fields['email_template_title'];
    $email_template_body = $rst->fields['email_template_body'];

    // Build data setup
    $files_data['on_what_table']    = 'email_templates';
    $files_data['on_what_id']       = $email_template_id;

    // Get pre-attached files for this template
    if ( $file_sidebar_rst = get_file_records( $con, $files_data ) )
    {
        while (!$file_sidebar_rst->EOF)
        {
            $attach_list[$file_sidebar_rst->fields['file_filesystem_name']] = $file_sidebar_rst->fields['file_name'];

            $file_sidebar_rst->movenext();
        }

    // now prep array for passing around
    $_SESSION['attachment_list'] = $attach_list;

    }
    $rst->close();
}

//add fields menu for custom emails
$con = get_xrms_dbconnection();
$i=0;
$sql="SHOW COLUMNS FROM contacts";
$rst_fields=$con->execute($sql);
$contacts_menu.="<select name=\"contacts_fields\">\n";
$arr=$rst_fields->GetRows();
/*
I had to do it the way below because this way doesnt work....
$contacts_menu.=$rst_fields->GetMenu("companies_fields");
*/
while ($i <$rst_fields->RecordCount()) {
		$contacts_menu.="<option value=".$arr[$i]["Field"].">".$arr[$i]["Field"]."</option>";
		$i++;
}
$contacts_menu.="</select>";
$i=0;

$sql="SHOW COLUMNS FROM companies";
$rst_fields=$con->execute($sql);
$companies_menu.="<select name=\"companies_fields\">\n";
$arr=$rst_fields->GetRows();
while ($i <$rst_fields->RecordCount()) {
		$companies_menu.="<option value=".$arr[$i]["Field"].">".$arr[$i]["Field"]."</option>";
		$i++;
}
$companies_menu.="</select>";
$i=0;

$sql="SHOW COLUMNS FROM addresses";
$rst_fields=$con->execute($sql);
$addresses_menu.="<select name=\"addresses_fields\">\n";
$arr=$rst_fields->GetRows();
while ($i <$rst_fields->RecordCount()) {
		$addresses_menu.="<option value=".$arr[$i]["Field"].">".$arr[$i]["Field"]."</option>";
		$i++;
}
$addresses_menu.="</select>";




function createFileList ()
{
    global $attach_list;

    // Build HTML code to display uploads
    $i = 0;
    $attach_file_list = '';
    foreach ( $attach_list as $_fileName => $_displayName )
    {
        $attach_file_list .= '<input type="checkbox"
                                     name="attachedFile[]"
                                     id="attachedFile_' . $i . '"
                                     value="' . $_fileName . '" /> ';
        $attach_file_list .= $_displayName . '<br />';
    }

    return $attach_file_list;
}


$page_title = _("Edit Message");
start_page($page_title, true, $msg);

?>

<script language="javascript">


function nextPage( $_where, $_what )
{
    if ( $_what == 'add' )
        document.mainForm.act.value = 'add';

    if ( $_what == 'del' )
        document.mainForm.act.value = 'del';

    document.mainForm.action = $_where;
    document.mainForm.submit();
    return false;
}


</script>
<script>
function insertAtCursor(myField, myValue) {
//IE support
if (document.selection) {
myField.focus();
sel = document.selection.createRange();
sel.text = myValue;
}
//MOZILLA/NETSCAPE support
else if (myField.selectionStart || myField.selectionStart == '0') {
var startPos = myField.selectionStart;
var endPos = myField.selectionEnd;
myField.value = myField.value.substring(0, startPos)
+ myValue
+ myField.value.substring(endPos, myField.value.length);
} else {
myField.value += myValue;
}
}
</script>
<script language="javascript" type="text/javascript" src="<?PHP echo $http_site_root;?>/include/tinymce/jscripts/tiny_mce/tiny_mce.js"></script>
<script language="javascript" type="text/javascript">
tinyMCE.init({
	mode : "textareas"
});
</script>

<div id="Main">
<div id="Content">

<form action="email-2.php"
      method="POST"
      enctype="multipart/form-data"
      name="mainForm"
      id="mainForm"
      onsubmit="javascript: return validate();" method="post">

    <table class="widget" cellspacing="1">
      <tr> 
        <td class="widget_header" colspan="2"> 
          <?php echo _("Edit Message"); ?>
          - 
          <?php echo $email_template_title ?>
        </td>
      </tr>
      <tr> 
        <td class="widget_label_right" width="1%" nowrap> 
          <?php echo _("From"); ?>
          : </td>
        <td class="widget_content_form_element"> 
          <input type="text"
                   name="sender_name"
                   id="sender_name"
                   size="50"
                   value="<?php echo $sender_name ?>" />
          <?php echo $required_indicator; ?>
        </td>
      </tr>
      <!--
<tr>
<td class=widget_label_right width="1%" nowrap><?php echo _("Reply to"); ?>:</td>
<td class=widget_content_form_element><input type=text name="sender_address" size=50 value="<?php echo $sender_name ?>"><?php echo $required_indicator; ?></td>
</tr>
<tr>
<td class=widget_label_right width="1%" nowrap><?php echo _("Bcc"); ?>:</td>
<td class=widget_content_form_element><input type=text name="bcc_address" size=50 value=""></td>
</tr>
-->
      <tr> 
        <td class="widget_label_right" width="1%" nowrap> 
          <?php echo _("Subject"); ?>
          : </td>
        <td class="widget_content_form_element"> 
          <input type=text
                   name="email_template_title"
                   id="email_template_title"
                   size="50"
                   value="<?php echo $email_template_title ?>" />
        </td>
      </tr>
      <tr> 
        <td class="widget_content_form_element"> 
          <table width="75%" border="1" cellpadding="2">
            <tr> 
              <td> 
                <?PHP echo _("Contact") . "<BR>" . $contacts_menu; ?>
              </td>
              <td><a href="javascript:void(0);" onClick="tinyMCE.execInstanceCommand('email_template_body','mceInsertContent',true,'{'+document.forms[0].contacts_fields.value+'}');"><?php echo _("Add");?></a></td>
            </tr>
            <tr> 
              <td> 
                <?PHP echo _("Company") . "<BR>" . $companies_menu; ?>
              </td>
              <td><a href="javascript:void(0);" onClick="tinyMCE.execInstanceCommand('email_template_body','mceInsertContent',true,'{'+document.forms[0].companies_fields.value+'}');"><?php echo _("Add");?></a> 
			  </td>
            </tr>
            <tr> 
              <td> 
                <?PHP echo _("Addresse") . "<BR>" . $addresses_menu; ?>
              </td>
              <td><a href="javascript:void(0);" onClick="tinyMCE.execInstanceCommand('email_template_body','mceInsertContent',true,'{'+document.forms[0].addresses_fields.value+'}');"><?php echo _("Add");?></a></td>
            </tr>
            <tr> 
              <td colspan="2"><?php echo _("Click 'Add' to add the custom field to your mail merge. You can also use these fields in the SUBJECT line too, just copy-&gt;paste them into it.");?><BR></td>
            </tr>
          </table>
          <br />
        </td>
        <td class="widget_content_form_element">
          <textarea class="monospace"
                      name="email_template_body"
                      id="email_template_body" rows="20"
                      cols="80"><?php echo $email_template_body ?></textarea>
        </td>
      </tr>
      <tr> 
        <td class="widget_label_right" width="1%" nowrap> 
          <?php echo _("Attachments"); ?>
          : </td>
        <td class="widget_content_form_element"> 
          <input type="file"
                   name="attach"
                   id="attach"
                   size="50" />
          &nbsp;&nbsp; 
          <input type="button"
                   class="button"
                   name="go"
                   id="go"
                   value="<?php echo _("Add"); ?>"
                   onclick="javascript: nextPage( 'email-2.php', 'add' );" />
        </td>
      </tr>
      <?php
if ( $attach_list )
{ ?>
      <tr> 
        <td class="widget_label_right" width="1%" nowrap> </td>
        <td class="widget_content_form_element"> 
          <?php
                echo createFileList();
            ?>
          <input type="button"
                   class="button"
                   name="go"
                   id="go"
                   value="<?php echo _("Delete Selected"); ?>"
                   onclick="javascript: nextPage( 'email-2.php', 'del' );" />
        </td>
      </tr>
      <?php } ?>
      <tr> 
        <td class="widget_content_form_element" colspan="2"> 
          <input type="hidden"
                   name="act"
                   id="act" />
          <input type="hidden"
                   name="email_template_id"
                   id="email_template_id"
                   value="<?php echo $email_template_id ?>" />
          <input type="button"
                   class="button"
                   value="<?php echo _("Continue"); ?>"
                   onclick="javascript: nextPage( 'email-3.php' );" />
          <input type="button"
                   class="button"
                   value="<?php echo _("Update Template"); ?>"
                   onclick="javascript: nextPage( 'update-template.php' );" />
          <input type="button"
                   class="button"
                   value="<?php echo _("Save as New Template"); ?>"
                   onclick="javascript: nextPage( 'save-as-new-template.php' );" />
        </td>
      </tr>
    </table>

</form>

</div>

<!-- right column //-->
<div id="Sidebar">

&nbsp;

</div>

</div>

<script language="javascript" type="text/javascript" >

function initialize() {
document.forms[0].sender_name.select();
// document.forms[0].company_name.focus();
}

function validate() {

var numberOfErrors = 0;
var msgToDisplay = '';

if (document.getElementById('sender_name').value == '') {
numberOfErrors ++;
msgToDisplay += '\n<?php echo addslashes(_("You must enter a name to let the recipient know who the email is from.")); ?>';
}

/*
if (document.forms[0].sender_address.value == '') {
numberOfErrors ++;
msgToDisplay += '\n<?php echo addslashes(_("You must enter an reply address so the recipient can reply to the message.")); ?>';
}
*/

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
* Revision 1.26  2007/06/13 17:08:19  niclowe
* Fixed [ 1648768 ] Bug in email-2.php
*
* Revision 1.25  2007/04/06 16:27:19  myelocyte
* - Enabled localization of two strings
* - Updated pot file to reflect this changes
* - Updated Spanish translation
* - Changed some Spanish strings
*
* Revision 1.24  2006/12/27 11:41:37  jnhayart
* Change Syntax for Insert correctly in IE
*
* Revision 1.23  2006/12/15 17:22:13  jnhayart
* change Javascript syntax for working with IE
*
* Revision 1.22  2006/12/11 17:34:36  jnhayart
* add Translation
* and force email in HTML (temporary) for test
*
* Revision 1.21  2006/12/10 15:28:39  jnhayart
* change somes code including
* put field insert in cursor place
*
* Revision 1.20  2006/12/05 11:29:29  jnhayart
* correct localisation for java string
*
* Revision 1.19  2006/11/29 20:03:46  niclowe
* added tinymce
*
* Revision 1.18  2006/10/26 08:57:56  niclowe
* -added custom field to mail merge
* -added error trapping for emails that fail silently (or appear to have worked)
* -added mail merge preview for custom emails
*
* Revision 1.17  2006/01/02 23:02:14  vanmer
* - changed to use centralized dbconnection function
*
* Revision 1.16  2005/10/03 10:28:27  braverock
* - remove legacy file_limit_sql
*
* Revision 1.15  2005/07/22 17:30:11  braverock
* - fix spelling of Attachment
*
* Revision 1.14  2005/07/08 02:15:27  jswalter
*  - added pre-defined FILES handling
*  - modified how attached files are passed
*  - removed debug code
* Bug 311
*
* Revision 1.13  2005/06/24 16:52:47  jswalter
*  - drastic modification to JS on how page is "submitted" (simplified)
*  - made HTML more XHTML comliant
*  - changed default ACTION to 'email-2.php' (itself)
*  - removed "extra" Forms for "Replace" and "new" templates
*  - created central JS to handle different Form processing
*  - added FILE object to Form
*  - added multi File Attachment "Add" capability
*  - added "Remove" File Attachment capability
* Bug 310
*
* Revision 1.12  2005/03/17 20:02:10  jswalter
*  - commented out:
*    * REPLY_TO
*    * BCC
*    * 'sender.address' JS test
*  - modified 'sender.name' JS test
*
* Revision 1.11  2005/01/25 03:55:25  braverock
* - remove errant short tags
*
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
