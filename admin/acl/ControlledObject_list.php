<?php
/**
 * Manage list of ControlledObjects
 *
 * Copyright (c) 2004 Explorer Fund Advisors, LLC
 * All Rights Reserved.
 *
 * @todo
 * $Id: ControlledObject_list.php,v 1.9 2006/07/13 00:47:20 vanmer Exp $
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

getGlobalVar($msg, 'msg');

$page_title = _("Manage Controlled Objects");

$sql="SELECT " . $con->Concat($con->qstr("<input type=\"button\" class=\"button\" value=\"Edit\" onclick=\"javascript: location.href='one_ControlledObject.php?form_action=edit&return_url=ControlledObject_list.php&ControlledObject_id="), 'ControlledObject_id', $con->qstr("'\">")) . "AS LINK, ControlledObject_id, ControlledObject_name, on_what_table, on_what_field, data_source.data_source_name FROM ControlledObject  JOIN data_source ON data_source.data_source_id=ControlledObject.data_source_id";

$form_id="ControlledObjectsForm";

    $columns = array();
    $columns[] = array('name' => _("Edit"), 'index_sql' => 'LINK');
    $columns[] = array('name' => _("ID"), 'index_sql' => 'ControlledObject_id');
    $columns[] = array('name' => _("Object Name"), 'index_sql' => 'ControlledObject_name');
    $columns[] = array('name' => _("Source Table"), 'index_sql' => 'on_what_table');
    $columns[] = array('name' => _("Identifying Field"), 'index_sql' => 'on_what_field');
    $columns[] = array('name' => _("Data Source"), 'index_sql' => 'data_source_name');

    $default_columns=array('LINK','ControlledObject_name', 'on_what_table', 'data_source_name');
    
    $pager_columns = new Pager_Columns('ControlledObjectsPager', $columns, $default_columns, $form_id);
    $pager_columns_button = $pager_columns->GetSelectableColumnsButton();
    $pager_columns_selects = $pager_columns->GetSelectableColumnsWidget();

    $columns = $pager_columns->GetUserColumns('default');
    $colspan = count($columns);

        $endrows =  "
            <tr>
                <td colspan=$colspan class=widget_content_form_element>
                    <input type=\"button\" class=\"button\" value=\"". _("Add New") . "\" onclick=\"javascript: location.href='one_ControlledObject.php?form_action=new&return_url=ControlledObject_list.php'\">
                    $pager_columns_button
                </td>
            </tr>";

   $pager = new GUP_Pager($con, $sql,false, _("Controlled Objects"), $form_id, 'ControlledObjectsPager', $columns);

    $pager->AddEndRows($endrows);



start_page($page_title, true, $msg);
echo '<form method="POST" name="'.$form_id . '">';
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
 * $Log: ControlledObject_list.php,v $
 * Revision 1.9  2006/07/13 00:47:20  vanmer
 * - changed all columns/pager combinations to reference the same pager name, to allow saved views to operate properly
 *
 * Revision 1.8  2006/07/09 05:04:03  vanmer
 * - patched ACL interface to check for admin access
 *
 * Revision 1.7  2005/12/12 21:17:20  vanmer
 * - added internationalization calls to strings which were only english
 *
 * Revision 1.6  2005/12/12 21:01:42  vanmer
 * - added controlled object ID to pager list
 * - removed forced style
 *
 * Revision 1.5  2005/12/12 20:00:40  vanmer
 * - changed to use GUP_Pager instead of deprecated adodb pager
 *
 * Revision 1.4  2005/08/11 22:52:15  vanmer
 * - changed to use ACL dbconnection
 *
 * Revision 1.3  2005/05/10 13:28:14  braverock
 * - localized strings patches provided by Alan Baghumian (alanbach)
 *
 * Revision 1.2  2005/02/14 23:42:17  vanmer
 * -requoted strings
 *
 * Revision 1.1  2005/01/13 17:16:14  vanmer
 * - Initial Commit for ACL Administration interface
 *
 * Revision 1.4  2004/12/27 23:48:50  ke
 * - adjusted to reflect new stylesheet
 *
 * Revision 1.3  2004/12/02 09:34:24  ke
 * - added navigation sidebar to all list pages
 *
 * Revision 1.2  2004/12/02 06:16:27  ke
 * - Added lookups in foreign key tables to allow lists to display useful information
 *
 * Revision 1.1  2004/12/02 04:12:03  justin
 * initial version
 *
 *
 */
?>
