<?php
/**
 * one_email-template.php - Display HTML form for a single email template
 *
 * Copyright (c) 2004-2006 XRMS Development Team
 *
 * @author Aaron van Meerten
 * $Id: one_email_template.php,v 1.6 2006/04/18 15:45:32 braverock Exp $
 */

require_once('../../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once $include_directory."classes/QuickForm/ADOdb_QuickForm.php";

$session_user_id = session_check();

// we need this for the companies foreign key lookup
$con = get_xrms_dbconnection();


getGlobalVar($return_url, 'return_url');
getGlobalVar($email_template_id, 'email_template_id');

$page_title = _("Manage Email Template");

start_page($page_title);


  $model = new ADOdb_QuickForm_Model();
  $model->ReadSchemaFromDB($con, 'email_templates');

        $model->SetDisplayNames(array('email_template_type_id' => _("Email Template Type"),
                                      'email_template_title' => _("Title"),
                                      'email_template_body' => _("Body")));

        $model->SetForeignKeyField('email_template_type_id', _("Email Template Type"), 'email_template_type', 'email_template_type_id', 'email_template_type_name');
        $model->SetFieldType('email_template_record_status', 'db_only');
        $model->SetFieldType('email_template_body', 'textarea','cols=50 rows=10');

  $view = new ADOdb_QuickForm_View($con, _("Email Template"));
  $view->SetReturnButton(_("Return to List"), $return_url);

  $controller = new ADOdb_QuickForm_Controller(array(&$model), &$view);
  $template_form_html = $controller->ProcessAndRenderForm();



if ($_GET['form_action']=='edit') {
    $on_what_table='email_templates';
    $on_what_id=$email_template_id;
    $template_return_url=$return_url;
    $return_url=current_page();
    $file_sidebar_label=_("Attached Files");
    require_once($xrms_file_root.'/files/sidebar.php');
    $return_url=$template_return_url;
} else {
    $file_rows='';
}
?>

<div id="Main">
<div id="Sidebar">
<?php include('email_template_nav.php'); ?>
<?php echo $file_rows; ?>
</div>
<div id="Content">
<table border=0 cellpadding=0 cellspacing=0 width=100%>
    <tr>
        <td class=lcol width=30% valign=top>
            <?php echo $template_form_html; ?>
        </td>
    </tr>
</table>
</div>
<?php

  $con->close();

  end_page();

/**
 * $Log: one_email_template.php,v $
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