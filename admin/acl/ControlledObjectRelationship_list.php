<?php
/**
 * Manage list of ControlledObjectRelationships
 *
 * Copyright (c) 2004 Explorer Fund Advisors, LLC
 * All Rights Reserved.
 *
 * @todo
 * $Id: ControlledObjectRelationship_list.php,v 1.8 2006/07/13 00:47:20 vanmer Exp $
 */

require_once('../../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'classes/Pager/GUP_Pager.php');
require_once($include_directory . 'classes/Pager/Pager_Columns.php');

global $http_site_root;

$session_user_id = session_check('Admin');

require_once ($include_directory.'classes/acl/xrms_acl_config.php');

$con = get_acl_dbconnection();


$page_title = _("Manage Controlled Object Relationships");

$form_id="ControlledObjectRelationships";

$sql="SELECT " . $con->Concat($con->qstr("<input type=\"button\" class=\"button\" value=\"Edit\" onclick=\"javascript: location.href='one_ControlledObjectRelationship.php?form_action=edit&return_url=ControlledObjectRelationship_list.php&CORelationship_id="), 'CORelationship_id', $con->qstr("'\">")) . "AS LINK, 
CORelationship_id,
Child.ControlledObject_name as 'ChildObject', 
Parent.ControlledObject_name as 'ParentObject',
on_what_child_field, on_what_parent_field, cross_table, singular 
FROM ControlledObjectRelationship LEFT OUTER JOIN ControlledObject AS Parent ON Parent.ControlledObject_id=ControlledObjectRelationship.ParentControlledObject_id
JOIN ControlledObject AS Child ON Child.ControlledObject_id=ControlledObjectRelationship.ChildControlledObject_id";


$form_id="ControlledObjectsForm";

    $columns = array();
    $columns[] = array('name' => _("Edit"), 'index_sql' => 'LINK');
    $columns[] = array('name' => _("ID"), 'index_sql' => 'CORelationship_id');
    $columns[] = array('name' => _("Child Object"), 'index_sql' => 'ChildObject', 'group_calc'=>true);
    $columns[] = array('name' => _("Parent Object"), 'index_sql' => 'ParentObject', 'group_calc'=>true);
    $columns[] = array('name' => _("Child Field"), 'index_sql' => 'on_what_child_field');
    $columns[] = array('name' => _("Parent Field"), 'index_sql' => 'on_what_parent_field');
    $columns[] = array('name' => _("Cross Table"), 'index_sql' => 'cross_table');
    $columns[] = array('name' => _("Singular?"), 'index_sql' => 'singular');

    $default_columns=array('LINK','ChildObject', 'ParentObject');
    
    $pager_columns = new Pager_Columns('ControlledObjectRelationshipPager', $columns, $default_columns, $form_id);
    $pager_columns_button = $pager_columns->GetSelectableColumnsButton();
    $pager_columns_selects = $pager_columns->GetSelectableColumnsWidget();

    $columns = $pager_columns->GetUserColumns('default');
    $colspan = count($columns);

        $endrows =  "
            <tr>
                <td colspan=$colspan class=widget_content_form_element>
                    <input type=\"button\" class=\"button\" value=\"". _("Add New") . "\" onclick=\"javascript: location.href='one_ControlledObjectRelationship.php?form_action=new&return_url=ControlledObjectRelationship_list.php'\">
                    $pager_columns_button
                </td>
            </tr>";

   $pager = new GUP_Pager($con, $sql,false, _("Controlled Object Relationships"), $form_id, 'ControlledObjectRelationshipPager', $columns);

    $pager->AddEndRows($endrows);


start_page($page_title);
echo '<form method="POST" name="'.$form_id.'">';

echo "<div id='Main'>";
require_once('xrms_acl_nav.php');
echo '<div id=Content>';

echo $pager_columns_selects;
$pager->Render();

?>

</div></div></form>

<?php
end_page();

/**
 * $Log: ControlledObjectRelationship_list.php,v $
 * Revision 1.8  2006/07/13 00:47:20  vanmer
 * - changed all columns/pager combinations to reference the same pager name, to allow saved views to operate properly
 *
 * Revision 1.7  2006/07/09 05:04:03  vanmer
 * - patched ACL interface to check for admin access
 *
 * Revision 1.6  2005/12/12 21:00:56  vanmer
 * - changed to use GUP pager instead of adodb pager
 *
 * Revision 1.5  2005/08/11 22:52:15  vanmer
 * - changed to use ACL dbconnection
 *
 * Revision 1.4  2005/05/10 13:28:14  braverock
 * - localized strings patches provided by Alan Baghumian (alanbach)
 *
 * Revision 1.3  2005/02/15 19:43:14  vanmer
 * - altered to reflect new fieldnames
 *
 * Revision 1.2  2005/02/14 23:43:49  vanmer
 * -requoted strings in sql statement
 *
 * Revision 1.1  2005/01/13 17:16:13  vanmer
 * - Initial Commit for ACL Administration interface
 *
 * Revision 1.5  2004/12/27 23:48:50  ke
 * - adjusted to reflect new stylesheet
 *
 * Revision 1.4  2004/12/15 17:58:02  ke
 * - added controlled object relationship id to list
 *
 * Revision 1.3  2004/12/02 09:34:24  ke
 * - added navigation sidebar to all list pages
 *
 * Revision 1.2  2004/12/02 05:58:50  ke
 * - Added lookup for controlled object names in sql query
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
