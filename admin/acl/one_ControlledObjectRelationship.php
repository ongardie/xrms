<?php
/**
 * one_ControlledObjectRelationship.php - Display HTML form for a single ControlledObjectRelationship.
 *
 * Copyright (c) 2004 Explorer Fund Advisors, LLC
 * All Rights Reserved.
 *
 * @author Justin Cooper
 * $Id: one_ControlledObjectRelationship.php,v 1.1 2005/01/13 17:16:15 vanmer Exp $
 */

require_once('../../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

$session_user_id = session_check();

require_once ($include_directory.'classes/acl/xrms_acl_config.php');


global $symbol_precendence;

	$con = &adonewconnection($xrms_acl_db_dbtype);
	$con->connect($xrms_acl_db_server, $xrms_acl_db_username, $xrms_acl_db_password, $xrms_acl_db_dbname);
	// $con->debug=1;
	
	// we need this for the companies foreign key lookup
	$xcon = &adonewconnection($xrms_db_dbtype);
	$xcon->nconnect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);


	getGlobalVar($return_url, 'return_url');


	$page_title = 'Manage Controlled Objects';

        $css_theme='basic-left';
	start_page($page_title);

  require_once($include_directory ."classes/QuickForm/ADOdb_QuickForm.php");


  $model = new ADOdb_QuickForm_Model();
  $model->ReadSchemaFromDB($con, 'ControlledObjectRelationship');
  $model->SetDisplayNames(array('ControlledObjectRelationship_name' => 'Controlled Object Name', 'on_what_table' => 'Table', 'on_what_field' => 'Field', 'data_source_id' => 'Data Source'));
  $model->SetForeignKeyField('ChildControlledObject_id', 'Child Controlled Object', 'ControlledObject', 'ControlledObject_id', 'ControlledObject_name');
  $model->SetForeignKeyField('ParentControlledObject_id', 'Parent Controlled Object', 'ControlledObject', 'ControlledObject_id', 'ControlledObject_name', null, array('NULL' => ' Select One'));

  $view = new ADOdb_QuickForm_View($con, 'Controlled Objects');
  $view->SetReturnButton('Return to List', $return_url);

  $controller = new ADOdb_QuickForm_Controller(array(&$model), &$view);
  $form_html = $controller->ProcessAndRenderForm();

	$con->close();

?>


<div id="Main">
<?php require_once('xrms_acl_nav.php'); ?>
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
 * $Log: one_ControlledObjectRelationship.php,v $
 * Revision 1.1  2005/01/13 17:16:15  vanmer
 * - Initial Commit for ACL Administration interface
 *
 * Revision 1.5  2005/01/03 18:31:50  ke
 * - allow null for default option for the parent in a controlled object relationship
 *
 * Revision 1.4  2004/12/27 23:48:50  ke
 * - adjusted to reflect new stylesheet
 *
 * Revision 1.3  2004/12/02 20:53:49  ke
 * - altered to allow null entry for parent relationship
 *
 * Revision 1.2  2004/12/02 09:34:45  ke
 * - added navigation sidebar to all one_ pages
 *
 * Revision 1.1  2004/12/02 04:19:58  justin
 * initial version
 *
 * Revision 1.1  2004/12/02 04:12:03  justin
 * initial version
 *
 *
 */
?>
