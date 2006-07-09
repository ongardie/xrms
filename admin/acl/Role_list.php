<?php
/**
 * Manage list of Roles
 *
 * Copyright (c) 2004 Explorer Fund Advisors, LLC
 * All Rights Reserved.
 *
 * @todo
 * $Id: Role_list.php,v 1.8 2006/07/09 05:04:03 vanmer Exp $
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

$page_title = _("Manage Roles");
$form_id="RolesForm";

$sql="SELECT " . $con->Concat($con->qstr("<input type=\"button\" class=\"button\" value=\"Edit\" onclick=\"javascript: location.href='one_Role.php?form_action=edit&return_url=Role_list.php&Role_id="), 'Role_id', $con->qstr("'\">")) . "AS LINK, Role.* FROM Role";

$columns = array();
$columns[] = array('name' => _("Edit"), 'index_sql' => 'LINK', 'sql_sort_column' => 'Role_id', 'type' => 'url');
$columns[] = array('name' => _("Name"), 'index_sql' => 'Role_name');
$columns[] = array('name' => _("ID"), 'index_sql' => 'Role_id');

$default_columns = array('LINK','Role_name');

$pager_columns = new Pager_Columns('RolesPager', $columns, $default_columns, $form_id);
$pager_columns_button = $pager_columns->GetSelectableColumnsButton();
$pager_columns_selects = $pager_columns->GetSelectableColumnsWidget();

$columns = $pager_columns->GetUserColumns('default');
$colspan=count($columns);


$pager = new GUP_Pager($con, $sql, null,_("Roles"), $form_id, 'RolesPager', $columns, false);

$endrows="<tr><td colspan=$colspan>$pager_columns_button
<input type=\"button\" class=\"button\" value=\"". _("Add New") ."\" onclick=\"javascript: location.href='one_Role.php?form_action=new&return_url=Role_list.php'\"></tr></td>";
$pager->AddEndRows($endrows);


start_page($page_title, true, $msg);
echo "<form method=\"POST\" name=\"$form_id\">";

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
 * $Log: Role_list.php,v $
 * Revision 1.8  2006/07/09 05:04:03  vanmer
 * - patched ACL interface to check for admin access
 *
 * Revision 1.7  2005/12/12 21:31:28  vanmer
 * - changed to use GUP_Pager instead of adodb pager
 *
 * Revision 1.6  2005/08/11 22:10:38  vanmer
 * - changed to use acl dbconnection
 *
 * Revision 1.5  2005/05/10 13:28:14  braverock
 * - localized strings patches provided by Alan Baghumian (alanbach)
 *
 * Revision 1.4  2005/02/14 23:24:52  vanmer
 * - altered to use adodb qstr for created input button
 *
 * Revision 1.3  2005/02/14 23:10:19  vanmer
 * - added missing single quote
 *
 * Revision 1.2  2005/02/14 23:04:54  vanmer
 * altered quote order to work on SQL server
 *
 * Revision 1.1  2005/01/13 17:16:15  vanmer
 * - Initial Commit for ACL Administration interface
 *
 * Revision 1.3  2004/12/27 23:48:50  ke
 * - adjusted to reflect new stylesheet
 *
 * Revision 1.2  2004/12/02 09:34:24  ke
 * - added navigation sidebar to all list pages
 *
 * Revision 1.1  2004/12/02 04:25:02  justin
 * initial version
 *
 * Revision 1.1  2004/12/02 04:12:03  justin
 * initial version
 *
 *
 */
?>
