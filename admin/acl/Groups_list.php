<?php
/**
 * Manage list of Groupss
 *
 * Copyright (c) 2004 Explorer Fund Advisors, LLC
 * All Rights Reserved.
 *
 * @todo
 * $Id: Groups_list.php,v 1.6 2006/07/09 05:04:03 vanmer Exp $
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

$page_title = _("Manage Groups");

$sql="SELECT " . $con->Concat($con->qstr("<input type=\"button\" class=\"button\" value=\"Edit\" onclick=\"javascript: location.href='one_Groups.php?form_action=edit&return_url=Groups_list.php&Group_id="), 'Group_id', $con->qstr("'\">")) . "AS LINK, Groups.* FROM Groups";
$form_id='GroupPagerForm';
start_page($page_title);
?>
<form method="POST" name="<?php echo $form_id; ?>">
<?php

echo "<div id='Main'>";
require_once('xrms_acl_nav.php');
echo '<div id=Content>';

$columns = array();
$columns[] = array('name' => _("Action"), 'index_sql' => 'LINK', 'sql_sort_column' => 'Groups.Group_name', 'type' => 'url');
$columns[] = array('name' => _("Name"), 'index_sql' => 'Group_name');
$columns[] = array('name' => _("ID"), 'index_sql' => 'Group_id');

$default_columns = array('LINK','Group_name');

$pager_columns = new Pager_Columns('GroupsPager', $columns, $default_columns, $form_id);
$pager_columns_button = $pager_columns->GetSelectableColumnsButton();
$pager_columns_selects = $pager_columns->GetSelectableColumnsWidget();

$columns = $pager_columns->GetUserColumns('default');
$colspan=count($columns);

echo $pager_columns_selects;

$pager = new GUP_Pager($con, $sql, null,_("Groups"), $form_id, 'GroupsPager', $columns, false);

$endrows="<tr><td colspan=$colspan>$pager_columns_button<input type=\"button\" class=\"button\" value=\""._("Add New"). "\" onclick=\"javascript: location.href='one_Groups.php?form_action=new&return_url=Groups_list.php'\"></tr></td>";
$pager->AddEndRows($endrows);

$pager->Render();


?>
</div></div></form>

<?php
end_page();

/**
 * $Log: Groups_list.php,v $
 * Revision 1.6  2006/07/09 05:04:03  vanmer
 * - patched ACL interface to check for admin access
 *
 * Revision 1.5  2005/07/28 18:47:13  vanmer
 * - changed to use acl dbconnection code from wrapper
 * - changed to use GUP_Pager instead of adodb pager
 *
 * Revision 1.4  2005/05/10 13:28:14  braverock
 * - localized strings patches provided by Alan Baghumian (alanbach)
 *
 * Revision 1.3  2005/02/14 23:34:06  vanmer
 * - removed single quote slash
 *
 * Revision 1.2  2005/02/14 23:31:22  vanmer
 * - altered to use qstr for concat
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
 * Revision 1.1  2004/12/02 04:20:43  ke
 * - Initial revision of group list manager
 *
 *
 */
?>
