<?php
/**
 * one_RolePermission.php - Display HTML form for a single RolePermission.
 *
 * Copyright (c) 2004 Explorer Fund Advisors, LLC
 * All Rights Reserved.
 *
 * @author Aaron van Meerten
 * $Id: one_RolePermission.php,v 1.1 2005/01/13 17:16:16 vanmer Exp $
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


	$page_title = 'Manage Role Permissions';
        
        $css_theme='basic-left';
	start_page($page_title);

  require_once($include_directory ."classes/QuickForm/ADOdb_QuickForm.php");

            $sql = "SELECT ControlledObjectRelationship_id as ID, Child.ControlledObject_name as ChildName, Parent.ControlledObject_name as ParentName
            FROM ControlledObjectRelationship LEFT OUTER JOIN ControlledObject AS Parent ON
            Parent.ControlledObject_id=ControlledObjectRelationship.ParentControlledObject_id
            JOIN ControlledObject AS Child ON Child.ControlledObject_id=ControlledObjectRelationship.ChildControlledObject_id";
            $rst=$con->execute($sql);
            
            db_error_handler($con, $sql);
            while (!$rst->EOF) {
                $relationships[$rst->fields['ID']]=$rst->fields['ChildName']. " -> " . $rst->fields['ParentName'];
                $rst->movenext();
            }
             
  $model = new ADOdb_QuickForm_Model();
  $model->ReadSchemaFromDB($con, 'RolePermission');
  $model->SetDisplayNames(array('Group_name' => 'Group Name')); //, 'on_what_table' => 'Table', 'on_what_field' => 'Field', 'data_source_id' => 'Data Source'));

  $model->SetForeignKeyField('ControlledObjectRelationship_id', 'Controlled Object Relationship', null, null, null, null, $relationships);
  $model->SetForeignKeyField('Role_id', 'Role', 'Role', 'Role_id', 'Role_name');
  $model->SetForeignKeyField('Permission_id', 'Permission', 'Permission', 'Permission_id', 'Permission_name');
  
  $view = new ADOdb_QuickForm_View($con, 'Group User');
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
 * $Log: one_RolePermission.php,v $
 * Revision 1.1  2005/01/13 17:16:16  vanmer
 * - Initial Commit for ACL Administration interface
 *
 * Revision 1.6  2004/12/27 23:48:50  ke
 * - adjusted to reflect new stylesheet
 *
 * Revision 1.5  2004/12/03 20:28:45  ke
 * - updated title to reflect page contents
 *
 * Revision 1.4  2004/12/02 20:54:47  ke
 * - altered to properly display controlled object relationship list
 *
 * Revision 1.3  2004/12/02 20:28:37  justin
 * Changed order of additional fields array in SetForeignKeyField
 *
 * Revision 1.2  2004/12/02 09:34:45  ke
 * - added navigation sidebar to all one_ pages
 *
 * Revision 1.1  2004/12/02 05:15:17  ke
 * - Initial revision of role permission list and individual page
 *
 * Revision 1.1  2004/12/02 04:39:10  ke
 * - Initial revision of group user list and individual page
 *
 *
 *
 */
?>
