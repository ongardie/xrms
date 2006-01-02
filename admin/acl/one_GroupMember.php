<?php
/**
 * one_GroupMember.php - Display HTML form for a single GroupMember.
 *
 * Copyright (c) 2004 Explorer Fund Advisors, LLC
 * All Rights Reserved.
 *
 * @author Aaron van Meerten
 * $Id: one_GroupMember.php,v 1.5 2006/01/02 22:27:11 vanmer Exp $
 */

require_once('../../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

$session_user_id = session_check('','Admin');

require_once ($include_directory.'classes/acl/xrms_acl_config.php');


global $symbol_precendence;

	$con = get_acl_dbconnection();
	
	// $con->debug=1;
	
	// we need this for the companies foreign key lookup
	$xcon = get_xrms_dbconnection();

    getGlobalVar($return_url, 'return_url');
    getGlobalVar($msg, 'msg');
    getGlobalVar($form_action, 'form_action');
    getGlobalVar($GroupMember_id, 'GroupMember_id');
    if (!$return_url) { $return_url='GroupMember_list.php'; }

	$page_title = 'Manage Group Member';
        
if ($form_action=='delete') {
    $ret=delete_group_member($con, $GroupMember_id);
    if ($ret) { $msg=_("Delete Successful"); }
    Header("Location: GroupMember_list.php?msg=$msg");
    exit();
} else {        
    require_once($include_directory ."classes/QuickForm/ADOdb_QuickForm.php");
    
    $model = new ADOdb_QuickForm_Model();
    $model->ReadSchemaFromDB($con, 'GroupMember');
    $model->SetPrimaryKeyName('GroupMember_id');
    $model->SetDisplayNames(array('Group_name' => _("Group Name"), 'on_what_id'=>_("Object ID"), 'criteria_table'=>_("Table"), 'criteria_resultfield'=>_("Result Field")));
    $model->SetForeignKeyField('ControlledObject_id', 'Controlled Object', 'ControlledObject', 'ControlledObject_id', 'ControlledObject_name');
    $model->SetForeignKeyField('Group_id', 'Group', 'Groups', 'Group_id', 'Group_name');
    
    $view = new ADOdb_QuickForm_View($con, 'Group Member');
    $view->SetReturnButton('Return to List', $return_url);
    $view->EnableDeleteButton();
    
    $controller = new ADOdb_QuickForm_Controller(array(&$model), &$view);
    $form_html = $controller->ProcessAndRenderForm();
    $render_msg=$controller->GetStatusMessage();
    if ($render_msg) { $msg.=$render_msg; }
    
    if ($form_action=='create' OR $form_action=='view' OR $form_action=='update') {
        if (!$GroupMember_id) {
            $values=$model->GetValues();
            $GroupMember_id=$values['GroupMember_id'];
        }
        if ($GroupMember_id) {
            $edit_button="<input type=button value="._("Edit") ." class=button onclick=javascript:location.href='one_GroupMember.php?form_action=edit&GroupMember_id=$GroupMember_id'>";
        } else $edit_button='';
    } else {$edit_button=''; }
    
    $form_html.=$edit_button;
        
    if (($form_action=='edit') OR ($form_action=='view') OR ($form_action=='create') OR ($form_action=='update')) {
        $criteria=get_acl_group_member_criteria($con, $GroupMember_id);
        $colspan=4;    
        $xrms_acl_nav_extra="<form method=POST action='edit_GroupMemberCriteria.php'><input type=hidden name=criteria_action value='addCriteria'><input type=hidden name=GroupMember_id value=$GroupMember_id>";
        $xrms_acl_nav_extra .="<table class=widget><tr><td class=widget_header colspan=$colspan>". _("Group Member Criteria") .'</td></tr>';
        $xrms_acl_nav_extra.='<tr><td class=widget_label>'._("Field") . '</td><td class=widget_label>' . _("Operator"). '</td><td class=widget_label>' ._("Value") .'</td><td class=widget_label>' ._("Action") .'</td></tr>';
        if ($criteria) {
            foreach ($criteria as $crit) {
                $xrms_acl_nav_extra.='<tr>';
                $xrms_acl_nav_extra.="<td class=widet_content_form_element>{$crit['criteria_fieldname']}</td>";
                $xrms_acl_nav_extra.="<td class=widet_content_form_element>{$crit['criteria_operator']}</td>";
                $xrms_acl_nav_extra.="<td class=widet_content_form_element>{$crit['criteria_value']}</td>";
                if ($form_action=='edit') {
                    $xrms_acl_nav_extra.="<td class=widet_content_form_element><input type=button value=". _("Delete") ." class=button onclick=\"javascript: location.href='edit_GroupMemberCriteria.php?criteria_action=deleteCriteria&GroupMember_id=$GroupMember_id&GroupMemberCriteria_id={$crit['GroupMemberCriteria_id']}'\"></td>";
                }
                $xrms_acl_nav_extra.="</tr>\n";
            }
        } else {
            $xrms_acl_nav_extra.="<tr><td class=widget_content_form_element colspan=$colspan>"._("No Criteria Defined") ."</td></tr>\n";
        }
        if ($form_action=='edit') {
            $operators=array('='=>'=','IS'=>'IS','LIKE'=>'LIKE','>'=>'>','>='=>'>=','<'=> '<','<=' =>'<=');
            $operator_list=create_select_from_array($operators, 'criteria_operator',$criteria_operator, false, false);        
            $xrms_acl_nav_extra.='<tr>';
            $xrms_acl_nav_extra.="<td class=widet_content_form_element><input size=5 type=text value=\"$criteria_fieldname\" name=criteria_fieldname></td>";
            $xrms_acl_nav_extra.="<td class=widet_content_form_element>$operator_list</td>";
            $xrms_acl_nav_extra.="<td class=widet_content_form_element><input size=5 type=text value=\"$criteria_value\" name=criteria_value></td>";
            $xrms_acl_nav_extra.="<td class=widet_content_form_element><input type=submit class=button value=\""._("Add") ."\"></td>";
            $xrms_acl_nav_extra.="</tr>\n";
        }
    //    $xrms_acl_nav_extra.="<tr><td class=widget_content_form_element colspan=$colspan><input type=submit class=button value=\""._("Add") ."\"></td></tr>\n";
        $xrms_acl_nav_extra.="</table></form>";
    }
}
$con->close();

start_page($page_title, true, $msg);
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
 * $Log: one_GroupMember.php,v $
 * Revision 1.5  2006/01/02 22:27:11  vanmer
 * - removed force of css theme for ACL interface
 * - changed to use centralized dbconnection function
 *
 * Revision 1.4  2005/08/11 22:53:53  vanmer
 * - changed to use ACL dbconnection
 *
 * Revision 1.3  2005/08/02 00:47:58  vanmer
 * - added sidebar for managing criteria on a group member, when editing
 * - added translated fieldnames for new table and result fieldname fields
 *
 * Revision 1.2  2005/03/05 00:52:34  daturaarutad
 * manually setting primary keys until mssql driver supports metacolumns fully
 *
 * Revision 1.1  2005/01/13 17:16:15  vanmer
 * - Initial Commit for ACL Administration interface
 *
 * Revision 1.4  2004/12/27 23:48:50  ke
 * - adjusted to reflect new stylesheet
 *
 * Revision 1.3  2004/12/02 09:34:45  ke
 * - added navigation sidebar to all one_ pages
 *
 * Revision 1.2  2004/12/02 06:17:24  ke
 * - updated display fieldnames to reflect list fieldnames
 *
 * Revision 1.1  2004/12/02 04:27:33  ke
 * - Initial revision of group member list and individual page
 *
 *
 *
 */
?>
