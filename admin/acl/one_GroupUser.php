<?php
/**
 * one_GroupUser.php - Display HTML form for a single GroupUser.
 *
 * Copyright (c) 2004 Explorer Fund Advisors, LLC
 * All Rights Reserved.
 *
 * @author Aaron van Meerten
 * $Id: one_GroupUser.php,v 1.2 2005/02/28 21:45:13 vanmer Exp $
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
//	$con->debug=1;
	
	// we need this for the companies foreign key lookup
	$xcon = &adonewconnection($xrms_db_dbtype);
	$xcon->nconnect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);


	getGlobalVar($return_url, 'return_url');
        getGlobalVar($msg, 'msg');
        getGlobalVar($form_action,'form_action');
        
        if ($form_action=='create' OR $form_action=='update') {
            getGlobalVar($Group_id, 'Group_id');
            getGlobalVar($user_id, 'user_id');
            getGlobalVar($Role_id,'Role_id');
            getGlobalVar($ChildGroup_id,'ChildGroup_id');
            
            if (($Group_id AND $user_id AND $Role_id AND ($ChildGroup_id!='NULL')) OR (!$Group_id AND !$user_id AND !$Role_id AND ($ChildGroup_id=='NULL'))) {
                $mymsg=_("Please select either a User/Group/Role combination or a Group/Child Group combination");
            }
            if ($user_id AND (!$Group_id OR !$Role_id)) {
                $mymsg ="Please select a Group/Role for this user";
            }
            if (($ChildGroup_id!='NULL') AND !$Group_id) {
                $mymsg="Please select a group for selected child group to exist in";
            }
            if ($ChildGroup_id AND $Group_id) {
                if (!check_acl_group_recursion($Group_id, $ChildGroup_id)) {
                    $mymsg="Group/Child Group combination fails recursion check.";
                }
            }
            if ($mymsg) {
                $msg=$mymsg;
                if (count($_POST)>0) {
                    $_POST['form_action']='new';
                } else { $_GET['form_action']='new';}
            }
        }
	$page_title = 'Manage Group Users';
        $css_theme='basic-left';
	start_page($page_title, true, $msg);

  require_once($include_directory ."classes/QuickForm/ADOdb_QuickForm.php");


  $model = new ADOdb_QuickForm_Model();
  $model->ReadSchemaFromDB($con, 'GroupUser');
  $model->SetDisplayNames(array('Group_name' => 'Group Name')); //, 'on_what_table' => 'Table', 'on_what_field' => 'Field', 'data_source_id' => 'Data Source'));
  $model->SetForeignKeyField('user_id', 'User', 'users', 'user_id', $xcon->CONCAT('first_names',"' '",'last_name'),$xcon,array('' => ' Select One'));
  $model->SetForeignKeyField('Group_id', 'Group', 'Groups', 'Group_id', 'Group_name', null, array('' => ' Select One'));
  $model->SetForeignKeyField('ChildGroup_id', 'Child Group', 'Groups', 'Group_id', 'Group_name', null, array('NULL' => ' Select One'));
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
