<?php
/**
 * Manage list of GroupGroups
 *
 * Copyright (c) 2004 Explorer Fund Advisors, LLC
 * All Rights Reserved.
 *
 * @todo
 * $Id: teamnotice_list.php,v 1.2 2006/01/30 17:56:13 niclowe Exp $
 */

require_once('../../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

require_once($include_directory . 'classes/Pager/GUP_Pager.php');
require_once($include_directory . 'classes/Pager/Pager_Columns.php');

global $http_site_root;

$session_user_id = session_check();

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

$page_title = _("Team Notice List");

$form_name = 'TeamnoticeList';
//$button1=$con->Concat($con->qstr("<input type=\"button\" class=\"button\" value=\""._("Delete")."\" onclick=\"javascript: location.href='del.php?teamnotice_id="), 'teamnotice_id', $con->qstr("'\">")) . ";
$sql="SELECT " . 
$con->Concat($con->qstr("<input type=\"button\" class=\"button\" value=\""._("Delete")."\" onclick=\"javascript: location.href='teamnotice_list.php?form_action=del&teamnotice_id="), 'teamnotice_id', $con->qstr("'\">")) . " AS LINK, teamnotices.notice_heading as 'Heading' FROM teamnotices where status='a'";

    $columns = array();
    $columns[] = array('name' => 'Delete', 'index_sql' => 'LINK');
    $columns[] = array('name' => 'Heading', 'index_sql' => 'Heading');


    $default_columns=array('LINK','Heading');
    
    $pager_columns = new Pager_Columns('TeamnoticeList', $columns, $default_columns, $form_name);
    $pager_columns_button = $pager_columns->GetSelectableColumnsButton();
    $pager_columns_selects = $pager_columns->GetSelectableColumnsWidget();

    $columns = $pager_columns->GetUserColumns('default');
    $colspan = count($columns);

        $endrows =  "
            <tr>
                <td colspan=$colspan class=widget_content_form_element>
                    $pager_columns_button
                </td>
            </tr>";

   $pager = new GUP_Pager($con, $sql,false, _("Team Notice List"), $form_name, 'TeamnoticeList', $columns);

    //$pager->AddEndRows($endrows);

//add capacity to del records
if($form_action=="del") include_once "del.php";

start_page($page_title);
?>



<form method="POST" name="<?php echo $form_name; ?>">
<?php
echo "<div id='Main'>";
echo "<div id='Sidebar'>";
//require_once('email_template_nav.php');
echo "</div>";
echo '<div id=Content>';
echo $pager_columns_selects;
$pager->Render();

?>
<input type="button" class="button" value="<?php echo _("Add New"); ?>" onclick="javascript: location.href='new.php'">
</div></div></form>
<br>
For more information on the Team Notice plug in, please see the <a href="README.txt">README.txt</a> file in the teamnotice directory.
<?php
end_page();

/**
 * $Log: teamnotice_list.php,v $
 * Revision 1.2  2006/01/30 17:56:13  niclowe
 * fixed delete bug
 * fixed deprecated call by function bug
 *
 * Revision 1.1  2005/09/29 19:35:27  niclowe
 * first draft of team notices
 *
 * Revision 1.3  2005/07/05 05:28:18  alanbach
 * fa_IR translation update + some gettext corrections.
 *
 * Revision 1.2  2005/06/24 23:52:18  vanmer
 * - added sidebar wrapper
 *
 * Revision 1.1  2005/06/23 16:54:38  vanmer
 * - new interface for managing email templates and their types
 *
 *
**/
?>
