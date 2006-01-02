<?php
/**
 * Manage list of GroupGroups
 *
 * Copyright (c) 2004 Explorer Fund Advisors, LLC
 * All Rights Reserved.
 *
 * @todo
 * $Id: email_template_list.php,v 1.4 2006/01/02 22:12:31 vanmer Exp $
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

$con = get_xrms_dbconnection();

$page_title = _("Manage Email Templates");

$form_name = 'EmailTemplates';

$sql="SELECT " . 
$con->Concat($con->qstr("<input type=\"button\" class=\"button\" value=\""._("Edit")."\" onclick=\"javascript: location.href='one_email_template.php?form_action=edit&return_url=email_template_list.php&email_template_id="), 'email_template_id', $con->qstr("'\">")) . "AS LINK, email_templates.email_template_title as 'Title', email_template_type.email_template_type_name as Type FROM email_templates LEFT OUTER JOIN email_template_type on email_templates.email_template_type_id=email_template_type.email_template_type_id WHERE email_template_record_status='a'";

    $columns = array();
    $columns[] = array('name' => 'Edit', 'index_sql' => 'LINK');
    $columns[] = array('name' => 'Title', 'index_sql' => 'Title');
    $columns[] = array('name' => 'Type', 'index_sql' => 'Type','group_calc'=>true);


    $default_columns=array('LINK','Title', 'Type');
    
    $pager_columns = new Pager_Columns('EmailTemplate', $columns, $default_columns, $form_name);
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

   $pager = new GUP_Pager($con, $sql,false, _("Email Templates"), $form_name, 'EmailTemplate', $columns);

    $pager->AddEndRows($endrows);



start_page($page_title);
?>



<form method="POST" name="<?php echo $form_name; ?>">
<?php

echo "<div id='Main'>";
echo "<div id='Sidebar'>";
require_once('email_template_nav.php');
echo "</div>";
echo '<div id=Content>';
echo $pager_columns_selects;
$pager->Render();

?>
<input type="button" class="button" value="<?php echo _("Add New"); ?>" onclick="javascript: location.href='one_email_template.php?form_action=new&return_url=email_template_list.php'">
</div></div></form>

<?php
end_page();

/**
 * $Log: email_template_list.php,v $
 * Revision 1.4  2006/01/02 22:12:31  vanmer
 * - changed to use centralized dbconnection function
 *
 * Revision 1.3  2005/07/05 05:28:18  alanbach
 * fa_IR translation update + some gettext corrections.
 *
 * Revision 1.2  2005/06/24 22:32:03  vanmer
 * - added div to define sidebar
 *
 * Revision 1.1  2005/06/23 16:54:38  vanmer
 * - new interface for managing email templates and their types
 *
 *
**/
?>
