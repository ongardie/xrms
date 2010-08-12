<?php
/**
 * one_email-template.php - Display HTML form for a single email template
 *
 * Copyright (c) 2004-2008 XRMS Development Team
 *
 * @author Randy Martinsen
 *
 * $Id: one_email_template.php,v 1.13 2010/08/12 15:21:18 gopherit Exp $
 */

require_once('../../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'utils-files.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');
require_once($include_directory . 'ckeditor-loader.php');

$session_user_id = session_check();
$msg = $_GET['msg'];
$form_action = $_GET['form_action'];

$save_button = _("Save");

    //Turn $_POST array into variables
    extract($_POST);

if ( $_POST['act'] == 'add' ) {

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

} else if ( $_POST['act'] == 'del' ) {

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
    $email_template_title = stripslashes($rst->fields['email_template_title']);
    $email_template_body = $rst->fields['email_template_body'];

    // Build data setup
    $files_data['on_what_table']    = 'email_templates';
    $files_data['on_what_id']       = $email_template_id;

    // Get pre-attached files for this template
    $file_sidebar_rst = get_file_records( $con, $files_data );
    if ( $file_sidebar_rst )
    {
        while (!$file_sidebar_rst->EOF)
        {
            $attach_list[$file_sidebar_rst->fields['file_filesystem_name']] = $file_sidebar_rst->fields['file_name'];

            $file_sidebar_rst->movenext();
        }

    // now prep array for passing around
    $_SESSION['attachment_list'] = $attach_list;

    }

	//add fields menu for custom emails
	$i=0;
	$sql="SHOW COLUMNS FROM contacts";
	$rst_fields=$con->execute($sql);
        $contact_menu.="<select name=\"contact_fields\">\n";
        $user_menu.="<select name=\"user_fields\">\n";
	$arr=$rst_fields->GetRows();
	/*
	I had to do it the way below because this way doesnt work....
	$contacts_menu.=$rst_fields->GetMenu("companies_fields");
	*/
	while ($i <$rst_fields->RecordCount()) {
                            $contact_menu.='<option value="contact_'.$arr[$i]["Field"].'">'.$arr[$i]["Field"]."</option>\n";
                            $user_menu.='<option value="user_'.$arr[$i]["Field"].'">'.$arr[$i]["Field"]."</option>\n";
				$i++;
	}
        $contact_menu.="</select>";
        $user_menu.="</select>";
	$i=0;

	$sql="SHOW COLUMNS FROM companies";
	$rst_fields=$con->execute($sql);
        $contact_company_menu.="<select name=\"contact_company_fields\">\n";
        $user_company_menu.="<select name=\"user_company_fields\">\n";
	$arr=$rst_fields->GetRows();
	while ($i <$rst_fields->RecordCount()) {
                            $contact_company_menu.='<option value="contact_company_'.$arr[$i]["Field"].'">'.$arr[$i]["Field"]."</option>\n";
                            $user_company_menu.='<option value="user_company_'.$arr[$i]["Field"].'">'.$arr[$i]["Field"]."</option>\n";
				$i++;
	}
        $contact_company_menu.="</select>";
        $user_company_menu.="</select>";
	$i=0;

	$sql="SHOW COLUMNS FROM addresses";
	$rst_fields=$con->execute($sql);
        $contact_address_menu.="<select name=\"contact_address_fields\">\n";
        $user_address_menu.="<select name=\"user_address_fields\">\n";
	$arr=$rst_fields->GetRows();
	while ($i <$rst_fields->RecordCount()) {
                            $contact_address_menu.='<option value="contact_address_'.$arr[$i]["Field"].'">'.$arr[$i]["Field"]."</option>\n";
                            $user_address_menu.='<option value="user_address_'.$arr[$i]["Field"].'">'.$arr[$i]["Field"]."</option>\n";
				$i++;
	}
        $contact_address_menu.="</select>";
        $user_address_menu.="</select>";

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

<script type="text/javascript"  language="javascript">


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

function InsertHTML($_text)
{
	// Get the editor instance that we want to interact with.
	var oEditor = FCKeditorAPI.GetInstance('email_template_body') ;

	// Check the active editing mode.
	if ( oEditor.EditMode == FCK_EDITMODE_WYSIWYG )
	{
		// Insert the desired HTML.
		oEditor.InsertHtml( $_text ) ;
	}
	else
		alert( 'You must be on WYSIWYG mode!' ) ;
}

</script>

<div id="Main">
<div id="Sidebar">
<?php include('email_template_nav.php'); ?>
<?php echo $file_rows; ?>
</div>
<div id="Content">

<form action="update-template.php"
      method="POST"
      enctype="multipart/form-data"
      name="mainForm"
      id="mainForm"
      onsubmit="javascript: return validate();" method="post">
     <input type="hidden" name="contact_id" value="<?php echo _($contact_id); ?>">


    <table class="widget" cellspacing="1">
      <tr>
        <td class="widget_header" colspan="2">
          <?php echo _("Edit Message"); ?>
          -
          <?php echo htmlspecialchars($email_template_title) ?>
        </td>
      </tr>
      <tr>
        <td class="widget_label_right" width="1%" nowrap>
          <?php echo _("Subject"); ?>
          : </td>
        <td class="widget_content_form_element">
          <input type=text
                   name="email_template_title"
                   id="email_template_title"
                   size="50"
                   value="<?php echo htmlspecialchars($email_template_title) ?>" />
        </td>
      </tr>
      <tr>
        <td class="widget_content_form_element">
          <table width="75%" border="1" cellpadding="2">

            <tr>
              <td><?PHP echo _("Contact") . "<BR>" . $contact_menu; ?></td>
              <td><a href="javascript:void(0);" onClick="CKEDITOR.instances.email_template_body.insertHtml('{'+document.forms[0].contact_fields.value+'}');"><?php echo _("Add");?></a></td>
            </tr>

            <tr>
              <td><?PHP echo _("Contact Company") . "<BR>" . $contact_company_menu; ?></td>
              <td><a href="javascript:void(0);" onClick="CKEDITOR.instances.email_template_body.insertHtml('{'+document.forms[0].contact_company_fields.value+'}');"><?php echo _("Add");?></a></td>
            </tr>

            <tr>
              <td><?PHP echo _("Contact Address") . "<BR>" . $contact_address_menu; ?></td>
              <td><a href="javascript:void(0);" onClick="CKEDITOR.instances.email_template_body.insertHtml('{'+document.forms[0].contact_address_fields.value+'}');"><?php echo _("Add");?></a></td>
            </tr>

            <?php if ($my_company_id > 0) { //only show if company ID is set in /include/vars.php?>
                <tr>
                  <td><?PHP echo _("User") . "<BR>" . $user_menu; ?></td>
                  <td><a href="javascript:void(0);" onClick="CKEDITOR.instances.email_template_body.insertHtml('{'+document.forms[0].user_fields.value+'}');"><?php echo _("Add");?></a></td>
                </tr>

                <tr>
                  <td><?PHP echo _("User Company") . "<BR>" . $user_company_menu; ?></td>
                  <td><a href="javascript:void(0);" onClick="CKEDITOR.instances.email_template_body.insertHtml('{'+document.forms[0].user_company_fields.value+'}');"><?php echo _("Add");?></a></td>
                </tr>

                <tr>
                  <td><?PHP echo _("User Address") . "<BR>" . $user_address_menu; ?></td>
                  <td><a href="javascript:void(0);" onClick="CKEDITOR.instances.email_template_body.insertHtml('{'+document.forms[0].user_address_fields.value+'}');"><?php echo _("Add");?></a></td>
                </tr>

            <?php } ?>

            <tr>
              <td colspan="2">Click 'Add' to add the custom field to your mail
                merge. You can also use these fields in the SUBJECT line too -
                just copy-&gt;paste them into it.<BR></td>
            </tr>
          </table>
        </td>
        <td class="widget_content_form_element">
            <?php
                $oCKeditor = new CKeditor() ;
                $oCKeditor->basePath = $ckeditor_location_url;
                // Override default CKEditor height
                $ckeditor_config['height']  = '300';
                // Insert the Font, Image and CreateDiv buttons in the CKEdtior Toolbar
                $ckeditor_config['toolbar'][0] = array_merge(array_slice($ckeditor_config['toolbar'][0], 0 , 3), array('Font'), array_slice($ckeditor_config['toolbar'][0],  3));
                $ckeditor_config['toolbar'][1] = array_merge(array_slice($ckeditor_config['toolbar'][1], 0 , 10), array('Image'), array_slice($ckeditor_config['toolbar'][1],  10));
                $ckeditor_config['toolbar'][1] = array_merge(array_slice($ckeditor_config['toolbar'][1], 0 , 6), array('CreateDiv'), array_slice($ckeditor_config['toolbar'][1], 6));

                $oCKeditor->editor('email_template_body', $email_template_body, $ckeditor_config) ;
            ?>
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
	<?php if ($form_action == "edit") {
		$save_button = _("Save as New Template"); ?>
          <input type="button"
                   class="button"
                   value="<?php echo _("Update Template"); ?>"
                   onclick="javascript: nextPage( 'update-template.php?type=update' );" />
		<?php } ?>
          <input type="button"
                   class="button"
                   value="<?php echo $save_button; ?>"
                   onclick="javascript: nextPage( 'update-template.php?type=new' );" />
	<?php if ($form_action == "edit") { ?>
          <input type="button"
                   class="button"
                   value="<?php echo _("Delete"); ?>"
                   onclick="javascript: nextPage( 'update-template.php?type=delete' );" />
		<?php } ?>
          <input type="button"
                   class="button"
                   value="<?php echo _("Cancel"); ?>"
                   onclick="javascript: nextPage( 'email_template_list.php' );" />

        </td>
      </tr>
    </table>

</form>


<!-- right column //-->

</div></div>

<?php
}
end_page();

/**
 * $Log: one_email_template.php,v $
 * Revision 1.13  2010/08/12 15:21:18  gopherit
 * Fixed Bug Artifact ID: 3043687.  Also, multiple improvements: added new sets of merge fields, thoroughly revised the mail_merge_functions and updated all email template editing scripts to reflect the new functionality.
 *
 * Revision 1.12  2010/08/06 22:12:57  gopherit
 * Updated the Administrative email template editing functionality to mirror the eMailMerge editing scripts: added user fields, additional CKEditor buttons and fixed a quote escaping bug.
 *
 * Revision 1.11  2010/03/30 21:38:43  gopherit
 * - Upgraded the WYSIWYG editor to the latest stable version (3.2) of CKEditor (formerly FCKEditor).
 *
 * Revision 1.10  2008/09/17 12:30:18  randym56
 * - Replaced TinyMCE with FCKEditor for GUI interface.
 * - Relocated FCKEditor in XRMS core from include folder to js folder
 *
 * Revision 1.9  2006/12/10 17:39:46  jnhayart
 * add Html editor
 *
 * Revision 1.8  2006/10/26 22:08:32  niclowe
 * added record status for template deletion
 *
 * Revision 1.7  2006/10/17 21:53:05  braverock
 * - fix mail_template_title (patch from dbaudone)
 *
 * Revision 1.6  2006/04/18 15:45:32  braverock
 * - localize missed i18n strings
 * - fix indentation for better legibility
 *
 * Revision 1.5  2006/01/02 22:12:31  vanmer
 * - changed to use centralized dbconnection function
 *
 * Revision 1.4  2005/11/15 12:38:31  braverock
 * - move include of files sidebar to $xrms_file_root from $include_directory
 *
 * Revision 1.3  2005/07/01 16:15:08  vanmer
 * - explicitly set file sidebar title
 *
 * Revision 1.2  2005/06/24 22:37:45  vanmer
 * - added files sidebar when editing an email template
 *
 * Revision 1.1  2005/06/23 16:54:38  vanmer
 * - new interface for managing email templates and their types
 */
?>