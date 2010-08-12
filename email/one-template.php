<?php
/**
 *
 * Not sure what the value of this script is any longer.  Maybe it just needs to be removed altogether since
 * email-2.php provides all of this functionality, and better.
 *
 * $Id: one-template.php,v 1.6 2010/08/12 15:21:18 gopherit Exp $
 */

require_once('include-locations-location.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');
require_once($include_directory . 'ckeditor-loader.php');

$session_user_id = session_check();
$msg = $_GET['msg'];
$return_url = $_GET['return_url'];

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

$con->close();

$page_title = _("Edit Template");
start_page($page_title, true, $msg);

?>

<script language="javascript">

function updateTemplate() {
    document.forms[0].action = "update-template.php?return_url=<?php echo $return_url; ?>";
    document.forms[0].submit();
}

function saveAsNewTemplate() {
    document.forms[0].action = "save-as-new-template.php?return_url=<?php echo $return_url; ?>";
    document.forms[0].submit();
}

</script>


<div id="Main">

        <form action="email-2.php?return_url=<?php echo $return_url; ?>" enctype="text/html" onsubmit="javascript: return validate();" method="post">
            <?php if ($email_template_id) {
                echo '<input type="hidden" name="email_template_id" value="'. $email_template_id .'">';
            } ?>


    <table class="widget" cellspacing="1">
        <tr>
            <td class=widget_header colspan=2><?php echo _("Edit Template"); ?> - <?php  echo $email_template_title ?></td>
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
                   value="<?php echo _("Update Template"); ?>"
                   onclick="javascript: updateTemplate();" />
          <input type="button"
                   class="button"
                   value="<?php echo _("Save as New Template"); ?>"
                   onclick="javascript: saveAsNewTemplate();" />
          <input type="button"
                   class="button"
                   value="<?php echo _("Cancel"); ?>"
                   onclick="javascript: if (confirm('You are about to discard all of you changes.\n\nDo you want to proceed?\n'))
                            location.href='<?php echo $http_site_root. $return_url; ?>';" />
        </td>
      </tr>
    </table>

        </form>
    </div>

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
        msgToDisplay += '\n<?php echo addslashes(_("You must enter a name to let the recipient know who the email is from.")); ?>';
    }

    if (document.forms[0].email_reply_to.value == '') {
        numberOfErrors ++;
        msgToDisplay += '\n<?php echo addslashes(_("You must enter an reply address so the recipient can reply to the message.")); ?>';
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
 * Revision 1.6  2010/08/12 15:21:18  gopherit
 * Fixed Bug Artifact ID: 3043687.  Also, multiple improvements: added new sets of merge fields, thoroughly revised the mail_merge_functions and updated all email template editing scripts to reflect the new functionality.
 *
 * Revision 1.5  2010/07/20 17:41:50  gopherit
 * Cleaned up HTML and Javascript.
 *
 * Revision 1.4  2006/12/05 11:29:29  jnhayart
 * correct localisation for java string
 *
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