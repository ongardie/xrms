<?php
/**
 * one_ControlledObject.php - Display HTML form for a single ControlledObject.
 *
 * Copyright (c) 2004 Explorer Fund Advisors, LLC
 * All Rights Reserved.
 *
 * @author Justin Cooper
 * $Id: one_ControlledObject.php,v 1.1 2005/01/13 17:16:15 vanmer Exp $
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
  $model->ReadSchemaFromDB($con, 'ControlledObject');
  $model->SetDisplayNames(array('ControlledObject_name' => 'Object Name', 'on_what_table' => 'Source Table', 'on_what_field' => 'Identifying Field', 'data_source_id' => 'Data Source'));
  $model->SetForeignKeyField('data_source_id', 'Data Source', 'data_source', 'data_source_id', 'data_source_name', null, array(null=> 'Select One'));

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
 * $Log: one_ControlledObject.php,v $
 * Revision 1.1  2005/01/13 17:16:15  vanmer
 * - Initial Commit for ACL Administration interface
 *
 * Revision 1.5  2004/12/27 23:48:50  ke
 * - adjusted to reflect new stylesheet
 *
 * Revision 1.4  2004/12/02 20:40:05  justin
 * added 'Select One' to datasource_id field
 *
 * Revision 1.3  2004/12/02 09:34:45  ke
 * - added navigation sidebar to all one_ pages
 *
 * Revision 1.2  2004/12/02 06:17:04  ke
 * - updated display fieldnames to reflect list fieldnames
 *
 * Revision 1.1  2004/12/02 04:12:03  justin
 * initial version
 *
 *
 */
?>
