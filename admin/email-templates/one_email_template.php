<?php
/**
 * one_email-template.php - Display HTML form for a single email template
 *
 * Copyright (c) 2004 Explorer Fund Advisors, LLC
 * All Rights Reserved.
 *
 *
 * @author Aaron van Meerten
 * $Id: one_email_template.php,v 1.1 2005/06/23 16:54:38 vanmer Exp $
 */

require_once('../../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once $include_directory."classes/QuickForm/ADOdb_QuickForm.php";

$session_user_id = session_check();


		// we need this for the companies foreign key lookup
	  $con = &adonewconnection($xrms_db_dbtype);
	  $con->nconnect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);


getGlobalVar($return_url, 'return_url');

$page_title = 'Manage Email Template';


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
  $view->SetReturnButton('Return to List', $return_url);

  $controller = new ADOdb_QuickForm_Controller(array(&$model), &$view);
  $form_html = $controller->ProcessAndRenderForm();


	$con->close();

?>


<div id="Main">
<?php include('email_template_nav.php'); ?>
<div id="Content">
<table border=0 cellpadding=0 cellspacing=0 width=100%>
    <tr>
        <td class=lcol width=30% valign=top>
					<?php echo $form_html ?>
        </td>
    </tr>
</table>
</div>

<?php


  end_page();

/**
 * $Log: one_email_template.php,v $
 * Revision 1.1  2005/06/23 16:54:38  vanmer
 * - new interface for managing email templates and their types
 *
 *
 */
?>