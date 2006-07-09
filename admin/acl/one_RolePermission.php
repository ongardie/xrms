<?php
/**
 * one_RolePermission.php - Display HTML form for a single RolePermission.
 *
 * Copyright (c) 2004 Explorer Fund Advisors, LLC
 * All Rights Reserved.
 *
 * @author Aaron van Meerten
 * $Id: one_RolePermission.php,v 1.6 2006/07/09 05:04:03 vanmer Exp $
 */

require_once('../../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

$session_user_id = session_check('Admin');

require_once ($include_directory.'classes/acl/xrms_acl_config.php');


global $symbol_precendence;

	$con = get_acl_dbconnection();
	
	// $con->debug=1;
	
	// we need this for the companies foreign key lookup
	$xcon = get_xrms_dbconnection();


	getGlobalVar($return_url, 'return_url');


	$page_title = 'Manage Role Permissions';
        
	start_page($page_title);

  require_once($include_directory ."classes/QuickForm/ADOdb_QuickForm.php");

            $sql = "SELECT CORelationship_id as ID, Child.ControlledObject_name as ChildName, Parent.ControlledObject_name as ParentName
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
  $model->SetPrimaryKeyName('RolePermission_id');

  $model->SetDisplayNames(array('Inheritable_flag' => 'Inheritable Flag')); //, 'on_what_table' => 'Table', 'on_what_field' => 'Field', 'data_source_id' => 'Data Source'));

  $model->SetForeignKeyField('CORelationship_id', 'Controlled Object Relationship', null, null, null, null, $relationships);
  $model->SetForeignKeyField('Role_id', 'Role', 'Role', 'Role_id', 'Role_name');
  $model->SetForeignKeyField('Permission_id', 'Permission', 'Permission', 'Permission_id', 'Permission_name');
  
  $inherit_values[1]=_("Yes");
  $inherit_values[0]=_("No");
  $model->SetSelectField('Inheritable_flag', _("Inheritable Flag"), $inherit_values);
  
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
 * Revision 1.6  2006/07/09 05:04:03  vanmer
 * - patched ACL interface to check for admin access
 *
 * Revision 1.5  2006/01/02 22:27:11  vanmer
 * - removed force of css theme for ACL interface
 * - changed to use centralized dbconnection function
 *
 * Revision 1.4  2005/07/28 20:09:02  vanmer
 * - changed to use new acl dataconnection
 * - changed to use select box for inheritable flag
 *
 * Revision 1.3  2005/03/05 00:52:34  daturaarutad
 * manually setting primary keys until mssql driver supports metacolumns fully
 *
 * Revision 1.2  2005/02/15 19:51:58  vanmer
 * - updated to reflect new fieldnames
 *
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
