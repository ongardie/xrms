<?php
/**
 * User Preferences Interface
 *
 * Administration screen for managing user preferences
 *
 *
 * $Id: user_prefs.php,v 1.4 2006/01/02 22:09:39 vanmer Exp $
 */

require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();

getGlobalVar($msg, 'msg');
getGlobalVar($preference_action,'preference_action');
if (!$preference_action) $preference_action='redirect';

$user_id=$session_user_id;

$page_title="User Preferences";

$con = get_xrms_dbconnection();
//get all user preference types
$types=get_user_preference_type($con, false, false, true);
if (!$types) { $msg="Failed to load an user preference types, no user preferences available"; $user_preferences_table='';}
else {
    switch ($preference_action) {
        case 'savePrefs':
            foreach ($types as $type_info) {
                if ($type_info['allow_user_edit_flag']==0) continue;
                if ($type_info['allow_multiple_flag']==1) {
                 //do a think of multiple options
                } else {
                    //handle single preferences
                    $user_preference_name= $type_info['user_preference_name'];
                    $user_preference_type_id= $type_info['user_preference_type_id'];
                    
                   getGlobalVar($preference_value, $user_preference_name);
                   if ($preference_value OR ($preference_value!=get_user_preference($con, $user_id, $user_preference_type_id))) {
//                        echo "set_user_preference($con,$user_id,  $user_preference_type_id, $preference_value);";
                      $ret=set_user_preference($con,$user_id,  $user_preference_type_id, $preference_value);
                      if (!$ret) $msg.=_("Failed to set preference value for preference") . ' ' .$user_preference_name;
                   }
                }
            }
            unset($_SESSION['XRMS_function_cache']['get_user_preference']);
            if (!$msg) $msg=_("Preferences successfully saved");
//            $msg=urlencode($msg);
        case 'redirect':
            Header("Location: self.php?msg=$msg");
        break;
        default:
        case 'displayPrefs':
            return false;
        break;
    }
}

start_page($page_title, true, $msg);
echo <<<TILLEND
<div id=Main>
    <div id=Content>
        <form action='user_prefs.php' method=POST>
        <table class=widget>
            <tr><td class=widget_header>User Preferences</td></tr>
        </table>
        $user_preferences_table
        </form>
    </div>
</div>
TILLEND;

?>