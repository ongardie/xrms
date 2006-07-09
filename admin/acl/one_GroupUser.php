<?php
/**
 * one_GroupUser.php - Display HTML form for a single GroupUser.
 *
 * Copyright (c) 2004 Explorer Fund Advisors, LLC
 * All Rights Reserved.
 *
 * @author Aaron van Meerten
 * $Id: one_GroupUser.php,v 1.9 2006/07/09 05:04:03 vanmer Exp $
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
	
//	$con->debug=1;
	
	// we need this for the companies foreign key lookup


	getGlobalVar($return_url, 'return_url');
        getGlobalVar($msg, 'msg');
        getGlobalVar($form_action,'form_action');
        getGlobalVar($GroupUser_id, 'GroupUser_id');

        if ($form_action=='create' OR $form_action=='update') {
            getGlobalVar($Group_id, 'Group_id');
            getGlobalVar($user_id, 'user_id');
            getGlobalVar($Role_id,'Role_id');
            getGlobalVar($ChildGroup_id,'ChildGroup_id');
            
            if (!$Group_id OR !$user_id OR !$Role_id OR ($Group_id=='NULL') OR ($user_id=='NULL') OR ($Role_id=='NULL')) {
                $mymsg=_("Please select a User/Group/Role combination");
            }
            if ($user_id AND (!$Group_id OR !$Role_id)) {
                $mymsg ="Please select a Group/Role for this user";
            }
            if ($mymsg) {
                $msg=$mymsg;
                if ($form_action=='create') { $newaction='new'; }
                if ($form_action=='update') { $newaction='edit'; }
                if (count($_POST)>0) {
                    $_POST['form_action']=$newaction;
                } else { $_GET['form_action']=$newaction;}
            }
        }
	$page_title = 'Manage Group Users';
	start_page($page_title, true, $msg);

  require_once($include_directory ."classes/QuickForm/ADOdb_QuickForm.php");


  $model = new ADOdb_QuickForm_Model();
  $model->ReadSchemaFromDB($con, 'GroupUser');
  $model->SetPrimaryKeyName('GroupUser_id');
  $model->removeField('ChildGroup_id');
  $model->SetDisplayNames(array('Group_name' => 'Group Name')); //, 'on_what_table' => 'Table', 'on_what_field' => 'Field', 'data_source_id' => 'Data Source'));
  $model->SetForeignKeyField('user_id', 'User', 'users', 'user_id', $con->CONCAT('last_name',"', '",'first_names'),$con,array('' => ' Select One'),'last_name, first_names');
  $model->SetForeignKeyField('Group_id', 'Group', 'Groups', 'Group_id', 'Group_name', null, array('' => ' Select One'));
  $model->SetForeignKeyField('Role_id', 'Role', 'Role', 'Role_id', 'Role_name', null, array('' => ' Select One'));

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
 * $Log: one_GroupUser.php,v $
 * Revision 1.9  2006/07/09 05:04:03  vanmer
 * - patched ACL interface to check for admin access
 *
 * Revision 1.8  2006/04/05 01:10:45  vanmer
 * - added global var retrieval for key value
 *
 * Revision 1.7  2006/01/02 22:27:11  vanmer
 * - removed force of css theme for ACL interface
 * - changed to use centralized dbconnection function
 *
 * Revision 1.6  2005/08/11 22:53:53  vanmer
 * - changed to use ACL dbconnection
 *
 * Revision 1.5  2005/06/07 20:20:25  vanmer
 * - added new interface to GroupUsers, splitting out child groups
 * - added new interface for adding child groups/managing them
 * - added handler for deleting users from roles in groups
 * - added link to new group management pages
 *
 * Revision 1.4  2005/03/09 17:25:03  vanmer
 * - changed user list output to show last name first, then first names
 * - changed sort order of user list to sort on last name, first names
 *
 * Revision 1.3  2005/03/05 00:52:34  daturaarutad
 * manually setting primary keys until mssql driver supports metacolumns fully
 *
 * Revision 1.2  2005/02/28 21:45:13  vanmer
 * - added error checking to not allow broken records to be added to the group user table
 *
 * Revision 1.1  2005/01/13 17:16:15  vanmer
 * - Initial Commit for ACL Administration interface
 *
 * Revision 1.6  2005/01/03 18:32:07  ke
 * - allow null for default option for the child group in the groupUser view
 *
 * Revision 1.5  2004/12/27 23:48:50  ke
 * - adjusted to reflect new stylesheet
 *
 * Revision 1.4  2004/12/02 20:54:33  ke
 * - altered to allow nulls for fields
 *
 * Revision 1.3  2004/12/02 20:27:43  justin
 * Added 'Select One' to Child Group select
 *
 * Revision 1.2  2004/12/02 09:34:45  ke
 * - added navigation sidebar to all one_ pages
 *
 * Revision 1.1  2004/12/02 04:39:10  ke
 * - Initial revision of group user list and individual page
 *
 *
 *
 */
?>
