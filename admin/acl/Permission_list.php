<?php
/**
 * Manage list of Permission
 *
 * Copyright (c) 2004 Explorer Fund Advisors, LLC
 * All Rights Reserved.
 *
 * @todo
 * $Id: Permission_list.php,v 1.6 2006/07/09 05:04:03 vanmer Exp $
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


$page_title = _("Manage Permission");

$sql="SELECT " . $con->Concat($con->qstr("<input type=\"button\" class=\"button\" value=\"Edit\" onclick=\"javascript: location.href='one_Permission.php?form_action=edit&return_url=Permission_list.php&Permission_id="), 'Permission_id', $con->qstr("'\">")) . "AS LINK, Permission.* FROM Permission";

start_page($page_title);

echo "<div id='Main'>";
require_once('xrms_acl_nav.php');
echo '<div id=Content>';

$form_id='GroupPagerForm';
echo "<form method=\"POST\" name=\"$form_id\">";

$columns = array();
$columns[] = array('name' => _("Action"), 'index_sql' => 'LINK', 'sql_sort_column' => 'Permission_id', 'type' => 'url');
$columns[] = array('name' => _("Name"), 'index_sql' => 'Permission_name');
$columns[] = array('name' => _("Abbreviation"), 'index_sql' => 'Permission_abbr');
$columns[] = array('name' => _("ID"), 'index_sql' => 'Permission_id');

$default_columns = array('LINK','Permission_name', 'Permission_abbr');

$pager_columns = new Pager_Columns('PermissionsPager', $columns, $default_columns, $form_id);
$pager_columns_button = $pager_columns->GetSelectableColumnsButton();
$pager_columns_selects = $pager_columns->GetSelectableColumnsWidget();

$columns = $pager_columns->GetUserColumns('default');
$colspan=count($columns);

echo $pager_columns_selects;

$pager = new GUP_Pager($con, $sql, null,_("Permissions"), $form_id, 'PermissionsPager', $columns, false);

$endrows="<tr><td colspan=$colspan>$pager_columns_button
<input type=\"button\" class=\"button\" value=\"". _("Add New") ."\" onclick=\"javascript: location.href='one_Permission.php?form_action=new&return_url=Permission_list.php'\"></tr></td>";
$pager->AddEndRows($endrows);

$pager->Render();

?>

</div></div></form>

<?php
end_page();

/**
 * $Log: Permission_list.php,v $
 * Revision 1.6  2006/07/09 05:04:03  vanmer
 * - patched ACL interface to check for admin access
 *
 * Revision 1.5  2006/01/02 22:27:11  vanmer
 * - removed force of css theme for ACL interface
 * - changed to use centralized dbconnection function
 *
 * Revision 1.4  2005/07/28 18:56:44  vanmer
 * - changed to use GUP_Pager instead of older adodb pager
 * - changed to use acl dbconnection code
 *
 * Revision 1.3  2005/05/10 13:28:14  braverock
 * - localized strings patches provided by Alan Baghumian (alanbach)
 *
 * Revision 1.2  2005/02/15 19:45:55  vanmer
 * - added qstr quoting to button concat
 *
 * Revision 1.1  2005/01/13 17:16:14  vanmer
 * - Initial Commit for ACL Administration interface
 *
 * Revision 1.3  2004/12/27 23:48:50  ke
 * - adjusted to reflect new stylesheet
 *
 * Revision 1.2  2004/12/02 09:34:24  ke
 * - added navigation sidebar to all list pages
 *
 * Revision 1.1  2004/12/02 04:37:56  justin
 * initial version
 *
 */
?>
