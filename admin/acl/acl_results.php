<?php
/**
 * Test function for the xrms_acl permission calculations
 *
 * Copyright (c) 2004 Explorer Fund Advisors, LLC
 * All Rights Reserved.
 *
 * $Id: acl_results.php,v 1.1 2005/01/13 17:16:15 vanmer Exp $
 *
 * @author Aaron van Meerten
 */

require_once('../../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb/adodb-pager.inc.php');

$session_user_id = session_check();

require_once ($include_directory.'classes/acl/xrms_acl_config.php');

$acl = new xrms_acl ($options );


$con = &adonewconnection($xrms_acl_db_dbtype);
$con->connect($xrms_acl_db_server, $xrms_acl_db_username, $xrms_acl_db_password, $xrms_acl_db_dbname);
// $con->debug=1;

// we need this for the companies foreign key lookup
$xcon = &adonewconnection($xrms_db_dbtype);
$xcon->nconnect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);



getGlobalVar($aclAction,'aclAction');
getGlobalVar($aclTest,'aclTest');
getGlobalVar($aclUser,'aclUser');
getGlobalVar($object,'object');
getGlobalVar($on_what_id,'on_what_id');
getGlobalVar($Permission, 'Permission');
getGlobalVar($msg, 'msg');

$title="ACL Results";
$css_theme='basic-left';
start_page($title, true, $msg);
echo '<div id="Main">';
require_once('xrms_acl_nav.php');
echo '<div id="Content">';
switch ($aclAction) {
    case 'showParamSelect':
        if ($aclTest=='List') {
            $nextAction="calculatePermission";
        } else { $nextAction="selectObject"; }
        $sql = "Select ControlledObject_name,ControlledObject_id FROM ControlledObject";
        $rst=$con->execute($sql);
        if (!$rst) db_error_handler($con, $sql);
        $objectMenu=$rst->getmenu2('object',$object,false);
        $sql = "Select Permission_name,Permission_id FROM Permission";
        $rst=$con->execute($sql);
        if (!$rst) db_error_handler($con, $sql);
        $permissionMenu=$rst->getmenu2('Permission',$Permission,false);
        $sql = "Select CONCAT(first_names,' ',last_name) as Name, user_id  FROM users";
        $rst=$xcon->execute($sql);
        if (!$rst) db_error_handler($xcon, $sql);
        $userMenu=$rst->getmenu2('aclUser',$object,false);
echo<<<TILLEND
        <form method=POST action="acl_results.php">
        <input type=hidden name=aclAction value="$nextAction">
        <input type=hidden name=aclTest value="$aclTest">
        <table class=widget>
            <tr><td class=widget_header colspan=2>ACL Parameters</td></tr>
            <tr><td class=widget_label_right>User</td><td class=widget_content_form_element>$userMenu</td></tr>
            <tr><td class=widget_label_right>Object</td><td class=widget_content_form_element>$objectMenu</td></tr>
TILLEND;
        if ($aclTest=='List') {
            echo "<tr><td class=widget_label_right>Permission</td><td class=widget_content_form_element>$permissionMenu</td></tr>";
        }
        echo "<tr><td class=widget_form_element><input type=submit class=button value='Get Results'></td></tr></table></form>\n";
            
    break;
    case 'selectObject':
        $nextAction="calculatePermission";
echo<<<TILLEND
        <form method=POST action="acl_results.php">
        <input type=hidden name=aclAction value="$aclAction">
        <input type=hidden name=aclUser value="$aclUser">
        <input type=hidden name=object value="$object">
        <input type=hidden name=aclTest value="$aclTest">
        <table class=widget>
            <tr><td class=widget_header>Object Selection</td></tr>
            <tr><td class=widget_content>Please select a controlled object to search for permissions on:<br>
TILLEND;
        
        display_object_list($acl, $object, false, true);
        echo "</td></tr>";
        echo "<tr><td class=widget_content><input class=button type=submit onclick=\"javascript:this.form.aclAction.value='$nextAction';\" value=\"Calculate Permissions\"></td></tr>";
        echo "</table></form>";
    break;
    case 'calculatePermission':
        echo "<table class=widget><tr><td class=widget_header>ACL Results</td></tr>";
        echo "<tr><td class=widget_content>";
        if ($aclTest=='Permission') {
            $result=$acl->get_permissions_user($object, $on_what_id, $aclUser);
            if (!$result) { echo "No permission allowed."; }
            else {
                echo "<pre>"; 
                foreach ($result as $perm) {
                    $permData=$acl->get_permission(false, $perm);
                    echo "Permission Available: {$permData['Permission_name']}\n";
                }
                echo "</pre>";
            }
        }
        if ($aclTest=='List') {

            $result = $acl->get_restricted_object_list($object, $aclUser, $Permission);
            if (!$result) { echo "No objects available."; }
            else {
//            echo "<pre>Final Result:\n";print_r($result); echo "</pre>";
            $controlled_objects=$result['controlled_objects'];
            //print_r($ret);
echo <<<TILLEND
            <form method="POST">
            <input type=hidden name=object value="$object">
            <input type=hidden name=aclUser value="$aclUser">
            <input type=hidden name=aclTest value="$aclTest">
            <input type=hidden name=aclAction value="$aclAction">
            <input type=hidden name=Permission value="$Permission">
TILLEND;
            display_object_list($acl, $object, $controlled_objects);
            }
        }

        echo "</form></td></tr></table>";
        break;
    default:
echo<<<TILLEND
        <table class=widget>
            <tr><td class=widget_header>ACL Result Test</td></tr>
            <tr><td class=widget_content>Please select an action:<p>
                <a href="acl_results.php?aclAction=showParamSelect&aclTest=Permission">Check Permission on an object for a user</a><p>
                <a href="acl_results.php?aclAction=showParamSelect&aclTest=List">Get list of objects for a user</a>
            </td></tr>
        </table>
TILLEND;
        break;
}
echo "</div></div>";
end_page();
 
function display_object_list($acl, $object, $ids=false, $extrafield=false) {
        global $http_site_root;
            if ($ids OR $extrafield) {
                $objectData = $acl->get_controlled_object(false, $object);
                $on_what_field=$objectData['on_what_field'];
                $on_what_table=$objectData['on_what_table'];
                if ($ids) $fieldRestriction[$on_what_field]=$ids;
            }

            $ret = $acl->get_controlled_object_data($object, false, $fieldRestriction, false, true);

           // begin sorted columns stuff
            getGlobalVar($sort_column, 'sort_column'); 
            getGlobalVar($current_sort_column, 'current_sort_column'); 
            getGlobalVar($sort_order, 'sort_order'); 
            getGlobalVar($current_sort_order, 'current_sort_order'); 
            getGlobalVar($Results_next_page, 'Results_next_page'); 
            getGlobalVar($resort, 'resort'); 
            
            if (!strlen($sort_column) > 0) {
                $sort_column = 1;
                            $current_sort_column = $sort_column;
                $sort_order = "asc";
            }
                
            if (!($sort_column == $current_sort_column)) {
                $sort_order = "asc";
            }
            
            
            $opposite_sort_order = ($sort_order == "asc") ? "desc" : "asc";
            $sort_order = (($resort) && ($current_sort_column == $sort_column)) ? $opposite_sort_order : $sort_order;
            
            $ascending_order_image = ' <img border=0 height=10 width=10 src="' . $http_site_root . '/img/asc.gif" alt="">';
            $descending_order_image = ' <img border=0 height=10 width=10 src="' . $http_site_root . '/img/desc.gif" alt="">';
            $pretty_sort_order = ($sort_order == "asc") ? $ascending_order_image : $descending_order_image;
            
            $order_by = $sort_column;
            
            
            
            $order_by .= " $sort_order";
            // end sorted columns stuff
            $sql=$ret['sql'];
            if (strlen(trim($order_by))>0) {
                $sql .= " order by $order_by";
            }
            if ($extrafield) {
                $radiofield="CONCAT('<input type=radio name=on_what_id value=',$on_what_field,'>') as on_what_id";
                if(preg_match("|SELECT([^\e]*)FROM([^\e]*)|", $sql, $matched)) {
                    $fields = trim($matched[1]);
                    if ($fields=='*') {
                        $fields="$on_what_table.*";
                    }
                    $rest = $matched[2];
                    $sql = "SELECT {$radiofield}, {$fields} FROM{$rest}";
                }
                echo "</pre>";
            }
echo <<<TILLEND
<script language="JavaScript" type="text/javascript">
<!--

function submitForm(nextPage) {
    document.forms[0].Results_next_page.value = nextPage;
    document.forms[0].submit();
}

function resort(sortColumn) {
    document.forms[0].sort_column.value = sortColumn + 1;
    document.forms[0].Results_next_page.value = '';
    document.forms[0].resort.value = 1;
    document.forms[0].submit();
}

//-->
</script>
    
            <input type=hidden name=use_post_vars value=1>
            <input type=hidden name=Results_next_page value="$Results_next_page">
            <input type=hidden name=resort value="0">
            <input type=hidden name=current_sort_column value="$sort_column">
            <input type=hidden name=sort_column value="$sort_column">
            <input type=hidden name=current_sort_order value="$sort_order">
            <input type=hidden name=sort_order value="$sort_order">
TILLEND;
        $pager = new ADODB_Pager($ret['con'], $sql, 'Results', false, $sort_column-1, $pretty_sort_order);
        $pager->Render();
}
 /*
  * $Log: acl_results.php,v $
  * Revision 1.1  2005/01/13 17:16:15  vanmer
  * - Initial Commit for ACL Administration interface
  *
  * Revision 1.4  2004/12/27 23:48:50  ke
  * - adjusted to reflect new stylesheet
  *
  * Revision 1.3  2004/12/04 05:46:51  ke
  * - added proper handling of no objects
  *
  * Revision 1.2  2004/12/03 21:04:47  ke
  * - added ability to select which permission to search on in object list
  *
  * Revision 1.1  2004/12/03 20:28:12  ke
  * - Initial revision of a page to display ACL search results
  *
  *
  */
?>