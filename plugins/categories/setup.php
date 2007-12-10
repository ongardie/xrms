<?php

function xrms_plugin_init_categories() {
    global $xrms_plugin_hooks;
    $xrms_plugin_hooks['system_monitoring']['categories'] = 'categories';
}


function categories() {

    global $con, $session_user_id, $include_directory, $http_site_root;
    
    if (!$con) {
require_once($include_directory . 'vars.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();

$con = get_xrms_dbconnection();
//$con->debug = 1;
    
    }
    
    require_once($include_directory . 'adodb/tohtml.inc.php');
    $tableheader_attributes="";
    // $con->debug=1;
    
    $sql="SELECT 
    LEFT OUTER JOIN opportunities ON ( opportunities.opportunity_status_id
= opportunity_statuses.opportunity_status_id
AND opportunity_statuses.opportunity_type_id =1
AND opportunities.opportunity_record_status = 'a' )
LEFT JOIN cf_instances ON ( cf_instances.key_id = opportunities.contact_id )
LEFT JOIN cf_data ON ( cf_data.instance_id = cf_instances.instance_id
AND cf_data.field_id = '25' )
    		FROM categories";
        
    $sql = "SELECT category_display_html as Catagory,
    			count(entity_category_map.on_what_table) as Companies,
    			count(entity_category_map.on_what_table) as Contacts,
    			count(entity_category_map.on_what_table) as Campaigns,
    			count(entity_category_map.on_what_table) as Opportunities 			
			FROM categories
			LEFT JOIN category_category_scope_map ON (categories.category_id = category_category_scope_map.category_id)
			LEFT JOIN category_scopes ON (category_category_scope_map.category_scope_id = category_category_scope_map.category_scope_id)
			LEFT JOIN entity_category_map ON (categories.category_id = entity_category_map.category_id)
			WHERE category_record_status = 'a'
			GROUP BY Catagory
			";
   $rst = $con->execute($sql);
        $con->debug=0;
   // rs2html(&$rs,$ztabhtml=false,$zheaderarray=false,$htmlspecialchars=true,$echo = true)
   
$menu = "
        <table class=widget>
            <tr>
                <td class=widget_header>
                    " . _("Entities by Categories") . "</a>
                </td>
        	</tr>
        	<tr>
                <td class=widget_content>
				" . rs2html($rst, true, false, true, false) . "
                </td>
        	</tr>
        </table>
";

/*
            <tr>
                <td class=widget_content>
                            <form action=\"$http_site_root/admin/categories/some.php\" method=post>
                            <input class=button type=submit value=" . _("Manage Categories") . ">
                            </form>
                
                </td>
            </tr>
*/

echo $menu;
}
?>