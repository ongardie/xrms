<?php
/**
 * Administrator Preferences Interface
 *
 * Administration screen for managing default system preferences
 *
 *
 * $Id: admin-prefs.php,v 1.3 2005/07/08 18:49:39 vanmer Exp $
 */

require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check('Admin');

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

getGlobalVar($prefs_action, 'prefs_action');
getGlobalVar($msg, 'msg');
switch ($prefs_action) {
    case 'displayPrefs':
    default: 
        $admin_prefs_table=get_admin_preferences_table($con);
        $body_content="<form action=\"admin-prefs.php\" method=POST><input type=hidden name=prefs_action value=savePrefs>";
        $body_content.=$admin_prefs_table;
        $body_content.="</form>";
        $page_title=_("System Preferences");
    break;   
    case 'savePrefs':
//                    print_r($_POST);

        //get all user preference types
        $types=get_user_preference_type($con, false, false, true);
        foreach ($types as $type_info) {
           if ($type_info['allow_multiple_flag']==1) {
                //do a think of multiple options
            } else {
                //handle single preferences
                $user_preference_name= $type_info['user_preference_name'];
                $user_preference_type_id= $type_info['user_preference_type_id'];
                $fetch_name=str_replace(' ','_',$user_preference_name);
               getGlobalVar($preference_value, $fetch_name);
                if ($preference_value OR ($preference_value!=get_admin_preference($con, $user_preference_type_id))) {
                    $ret=set_admin_preference($con, $user_preference_type_id, $preference_value);
                    if (!$ret) $msg.=_("Failed to set preference value for preference") . ' ' .$user_preference_name;
                }
            }
        }
        unset($_SESSION['XRMS_function_cache']['get_user_preference']);
        if (!$msg) $msg=_("Preferences successfully saved");
        Header("Location: admin-prefs.php?msg=$msg");
    break;
    

}

start_page($page_title, true, $msg);
$content =<<<TILLEND
<div id=Main>
    <div id=Sidebar>
        &nbsp;
    </div>
    <div id=Content>
        $body_content
    </div>
</div>
TILLEND;
echo $content;
end_page();

/**
  * $Log: admin-prefs.php,v $
  * Revision 1.3  2005/07/08 18:49:39  vanmer
  * - ensure proper unsetting of parameters after save of preferences occurs
  *
  * Revision 1.2  2005/07/06 17:21:47  vanmer
  * - added check to allow preference to be reset to nothing
  * - added log at end of file
  *
**/
?>