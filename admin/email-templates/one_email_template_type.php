<?php
/**
 * one_email-template.php - Display HTML form for a single email template
 *
 * Copyright (c) 2004-2006 XRMS Development Team
 *
 * @author Aaron van Meerten
 * $Id: one_email_template_type.php,v 1.4 2006/04/18 15:48:38 braverock Exp $
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

$page_title = _("Manage Email Template Types");


start_page($page_title);

//hacks to make created and modified on work properly
        if ($_GET['form_action']=='create') {
            $_GET['created_on']=time();
            $_GET['created_by']=$session_user_id;
            $_GET['modified_by']=$session_user_id;
            $_GET['modified_on']=time();
        }
        if ($_GET['form_action']=='update') {
            $_GET['modified_by']=$session_user_id;
            $_GET['modified_on']=time();
        }

  $model = new ADOdb_QuickForm_Model();
  $model->ReadSchemaFromDB($con, 'email_template_type');

	$model->SetDisplayNames(array('email_template_type_name' => _("Type Name")));

        $model->SetFieldType('modified_on', 'hidden');
        $model->SetFieldType('modified_by', 'hidden');
        $model->SetFieldType('created_by', 'hidden');
        $model->SetFieldType('created_on', 'hidden');

  $view = new ADOdb_QuickForm_View($con, _("Email Template"));
  $view->SetReturnButton(_("Return to List"), $return_url);

  $controller = new ADOdb_QuickForm_Controller(array(&$model), &$view);
  $form_html = $controller->ProcessAndRenderForm();


	$con->close();

?>


<div id="Main">
<div id='Sidebar'>
<?php include('email_template_nav.php'); ?>
</div>
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
 * $Log: one_email_template_type.php,v $
 * Revision 1.4  2006/04/18 15:48:38  braverock
 * - localize missed i18n strings
 * - fix indentation for better legibility
 *
 * Revision 1.3  2006/01/02 22:12:31  vanmer
 * - changed to use centralized dbconnection function
 *
 * Revision 1.2  2005/06/24 23:52:31  vanmer
 * - added sidebar wrapper
 *
 * Revision 1.1  2005/06/23 16:54:38  vanmer
 * - new interface for managing email templates and their types
 *
 *
 */
?>