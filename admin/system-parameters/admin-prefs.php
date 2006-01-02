<?php
/**
 * Administrator Preferences Interface
 *
 * Administration screen for managing default system preferences
 *
 *
 * $Id: admin-prefs.php,v 1.5 2006/01/02 22:07:25 vanmer Exp $
 */

require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check('Admin');

$con = get_xrms_dbconnection();

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
                $read_only = $type_info['read_only'];
                $fetch_name=str_replace(' ','_',$user_preference_name);
               getGlobalVar($preference_value, $fetch_name);
                if (!$read_only AND ($preference_value OR ($preference_value!=get_admin_preference($con, $user_preference_type_id)))) {
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
  * Revision 1.5  2006/01/02 22:07:25  vanmer
  * - changed to use centralized dbconnection function
  *
  * Revision 1.4  2005/11/30 00:46:49  vanmer
  * - added check for read-only options, do not attempt to save any option that is read-only
  *
  * Revision 1.3  2005/07/08 18:49:39  vanmer
  * - ensure proper unsetting of parameters after save of preferences occurs
  *
  * Revision 1.2  2005/07/06 17:21:47  vanmer
  * - added check to allow preference to be reset to nothing
  * - added log at end of file
  *
**/
?>