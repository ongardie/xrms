<?php
/**
 * Test function for the xrms_acl permission calculations
 *
 * Copyright (c) 2004 Explorer Fund Advisors, LLC
 * All Rights Reserved.
 *
 * $Id: acl_results.php,v 1.15 2006/07/09 05:04:03 vanmer Exp $
 *
 * @author Aaron van Meerten
 */

require_once('../../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'classes/Pager/GUP_Pager.php');

$session_user_id = session_check('Admin');

require_once ($include_directory.'classes/acl/xrms_acl_config.php');


$con = get_acl_dbconnection();

$acl = get_acl_object($acl_options, $con);

// $con->debug=1;

// we need this for the companies foreign key lookup
$xcon = get_xrms_dbconnection();


getGlobalVar($aclAction,'aclAction');
getGlobalVar($aclTest,'aclTest');
getGlobalVar($aclUser,'aclUser');
getGlobalVar($object,'object');
getGlobalVar($on_what_id,'on_what_id');
getGlobalVar($Permission, 'Permission');
getGlobalVar($msg, 'msg');

$title= _("ACL Results");
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
        $sql = "Select " . $con->CONCAT('first_names',"' '",'last_name')." as Name, user_id  FROM users";
        $rst=$con->execute($sql);
        if (!$rst) db_error_handler($con, $sql);
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
       $form_id="ACLSelectForm";
echo<<<TILLEND
        <form method=POST action="acl_results.php" name="$form_id">
        <input type=hidden name=aclAction value="$aclAction">
        <input type=hidden name=aclUser value="$aclUser">
        <input type=hidden name=object value="$object">
        <input type=hidden name=aclTest value="$aclTest">
        <table class=widget>
            <tr><td class=widget_header>Object Selection</td></tr>
            <tr><td class=widget_content>Please select a controlled object to search for permissions on:<br>
TILLEND;
        
        display_object_list($acl, $object, false, true, $con, $form_id);
        echo "</td></tr>";
        echo "<tr><td class=widget_content><input class=button type=submit onclick=\"javascript:this.form.aclAction.value='$nextAction';\" value=\"Calculate Permissions\"></td></tr>";
        echo "</table></form>";
    break;
    case 'calculatePermission':
        echo "<table class=widget><tr><td class=widget_header>"._("ACL Results")."</td></tr>";
        echo "<tr><td class=widget_content>";
        if ($aclTest=='Permission') {
            $result=$acl->get_permissions_user($object, $on_what_id, $aclUser);
            if (!$result) { echo _("No permission allowed."); }
            else {
                echo "<pre>"; 
                foreach ($result as $perm) {
                    $permData=$acl->get_permission(false, $perm);
                    echo _("Permission Available:")." {$permData['Permission_name']}\n";
                }
                echo "</pre>";
            }
        }
        if ($aclTest=='List') {

            $result = $acl->get_restricted_object_list($object, $aclUser, $Permission);
            if (!$result) { echo "No objects available."; }
            else {
//            echo "<pre>Final Result:\n";print_r($result); echo "</pre>";
            if ($result['ALL']) $controlled_objects=true;
            else $controlled_objects=$result['controlled_objects'];
            
            //print_r($ret);
            $form_id="ACLResultsForm";
            echo '<form method="POST" name='.$form_id.'>';
echo <<<TILLEND
            <input type=hidden name=object value="$object">
            <input type=hidden name=aclUser value="$aclUser">
            <input type=hidden name=aclTest value="$aclTest">
            <input type=hidden name=aclAction value="$aclAction">
            <input type=hidden name=Permission value="$Permission">
TILLEND;
            display_object_list($acl, $object, $controlled_objects, false, $con, $form_id);
            }
        }

        echo "</form></td></tr></table>";
        break;
    default:
//echo<<<TILLEND;
echo '<table class=widget>
            <tr><td class=widget_header>'._("ACL Result Test").'</td></tr>
            <tr><td class=widget_content>'._("Please select an action:").'<p>
                <a href="acl_results.php?aclAction=showParamSelect&aclTest=Permission">'._("Check Permission on an object for a user").'</a><p>
                <a href="acl_results.php?aclAction=showParamSelect&aclTest=List">'._("Get list of objects for a user").'</a>
            </td></tr>
        </table>';
        break;
}
echo "</div></div>";
end_page();
 
function display_object_list($acl, $object, $ids=false, $extrafield=false, $con, $form_id) {
//            echo "BLAH"; exit;
            global $http_site_root;
            $objectData = $acl->get_controlled_object(false, $object);
            $on_what_field=$objectData['on_what_field'];
            $on_what_table=$objectData['on_what_table'];

            if ($ids OR $extrafield) {
                if ($ids AND is_array($ids)) $fieldRestriction[$on_what_field]=$ids;
            }
            $ret = $acl->get_controlled_object_data($object, false, $fieldRestriction, false, true, false, false, false, true);

            $sql=$ret['sql'];
	    $ocon=$ret['con'];

            if ($extrafield) {
                $radiofield=$ocon->CONCAT($con->qstr("<input type=radio name=on_what_id value=\""),$on_what_field,$con->qstr("\">")) ." as select_on_what_id";
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

	    $tablecolumns=$ocon->MetaColumns($on_what_table);
            $columns = array();
            if ($extrafield) $columns[]=array('name'=>'SELECT','index_sql'=>'select_on_what_id');
            foreach ($tablecolumns as $tkey=>$tfield_info) {
                $fieldname=$tfield_info->name;
                $columns[]=array('name'=>$fieldname, 'index_sql'=>$fieldname);
            }

        $pager = new GUP_Pager($ocon, $sql,false, 'Results', $form_id, 'ControlledObjectData', $columns);
        $pager->Render();
}
 /*
  * $Log: acl_results.php,v $
  * Revision 1.15  2006/07/09 05:04:03  vanmer
  * - patched ACL interface to check for admin access
  *
  * Revision 1.14  2006/01/24 21:57:44  vanmer
  * - changed to allow results page to use database connection specific to the controlled object in
  * question
  *
  * Revision 1.13  2006/01/02 22:27:11  vanmer
  * - removed force of css theme for ACL interface
  * - changed to use centralized dbconnection function
  *
  * Revision 1.12  2005/12/12 21:02:02  vanmer
  * - changed to use GUP pager to render results for ACL results tests
  *
  * Revision 1.11  2005/09/15 21:40:45  vanmer
  * - changed to use the correct dbconnection when querying with the ACL query tool
  *
  * Revision 1.10  2005/08/11 23:12:42  vanmer
  * - changed to user users table from ACL db connection
  *
  * Revision 1.9  2005/08/11 22:53:53  vanmer
  * - changed to use ACL dbconnection
  *
  * Revision 1.8  2005/06/24 22:00:47  vanmer
  * - fixed parse error introduced by translations
  * - fixed on_what_id field to be called select_on_what_id so that it does not collide with existing on_what_id fields
  *
  * Revision 1.7  2005/05/10 13:28:14  braverock
  * - localized strings patches provided by Alan Baghumian (alanbach)
  *
  * Revision 1.6  2005/04/15 07:01:25  vanmer
  * - added extra parameters to acl_results calls
  *
  * Revision 1.5  2005/04/07 17:46:49  vanmer
  * - changed ACL results to reflect new ALL method of returning ACL results
  *
  * Revision 1.4  2005/03/04 23:20:44  vanmer
  * - quoted to allow permission check to correctly create radio buttons
  *
  * Revision 1.3  2005/02/15 19:51:39  vanmer
  * - updated to reflect new fieldnames
  *
  * Revision 1.2  2005/02/14 20:42:24  vanmer
  * - added missing connection object to do concat with adodb in acl results
  *
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
