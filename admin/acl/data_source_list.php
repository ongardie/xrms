<?php
/**
 * Manage list of data_sources
 *
 * Copyright (c) 2004 Explorer Fund Advisors, LLC
 * All Rights Reserved.
 *
 * @todo
 * $Id: data_source_list.php,v 1.7 2006/07/13 00:47:20 vanmer Exp $
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

$page_title = _("Manage Data Sources");

$form_id="DataSourcesForm";

$sql="SELECT " . $con->Concat($con->qstr("<input type=\"button\" class=\"button\" value=\"Edit\" onclick=\"javascript: location.href='one_data_source.php?form_action=edit&return_url=data_source_list.php&data_source_id="), 'data_source_id', $con->qstr("'\">")) . "AS LINK, data_source_id, data_source_name FROM data_source";


    $columns = array();
    $columns[] = array('name' => _("Edit"), 'index_sql' => 'LINK');
    $columns[] = array('name' => _("ID"), 'index_sql' => 'data_source_id');
    $columns[] = array('name' => _("Data Source Name"), 'index_sql' => 'data_source_name');

    $default_columns=array('LINK','data_source_name');
    
    $pager_columns = new Pager_Columns('DataSourcesPager', $columns, $default_columns, $form_id);
    $pager_columns_button = $pager_columns->GetSelectableColumnsButton();
    $pager_columns_selects = $pager_columns->GetSelectableColumnsWidget();

    $columns = $pager_columns->GetUserColumns('default');
    $colspan = count($columns);

        $endrows =  "
            <tr>
                <td colspan=$colspan class=widget_content_form_element>
                    <input type=\"button\" class=\"button\" value=\"". _("Add New") . "\" onclick=\"javascript: location.href='one_data_source.php?form_action=new&return_url=data_source_list.php'\">
                    $pager_columns_button
                </td>
            </tr>";

   $pager = new GUP_Pager($con, $sql,false, _("Data Sources"), $form_id, 'DataSourcesPager', $columns);

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
 * $Log: data_source_list.php,v $
 * Revision 1.7  2006/07/13 00:47:20  vanmer
 * - changed all columns/pager combinations to reference the same pager name, to allow saved views to operate properly
 *
 * Revision 1.6  2006/07/09 05:04:03  vanmer
 * - patched ACL interface to check for admin access
 *
 * Revision 1.5  2005/12/12 21:17:43  vanmer
 * - changed to use GUP pager instead of adodb pager
 *
 * Revision 1.4  2005/08/11 22:53:53  vanmer
 * - changed to use ACL dbconnection
 *
 * Revision 1.3  2005/05/10 13:28:14  braverock
 * - localized strings patches provided by Alan Baghumian (alanbach)
 *
 * Revision 1.2  2005/02/15 00:30:56  vanmer
 * - requoted strings for general use
 *
 * Revision 1.1  2005/01/13 17:16:15  vanmer
 * - Initial Commit for ACL Administration interface
 *
 * Revision 1.2  2004/12/27 23:48:50  ke
 * - adjusted to reflect new stylesheet
 *
 * Revision 1.1  2004/12/02 09:33:45  ke
 * - Initial revision of data source individual and list pages
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
