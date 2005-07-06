<?php
/**
 * User Preferences Interface
 *
 * Administration screen for managing user preferences
 *
 *
 * $Id: user_prefs.php,v 1.2 2005/07/06 17:23:01 vanmer Exp $
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
if (!$preference_action) $preference_action='displayPrefs';

$user_id=$session_user_id;

$page_title="User Preferences";

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

//get all user preference types
$types=get_user_preference_type($con, false, false, true);
if (!$types) { $msg="Failed to load an user preference types, no user preferences available"; $user_preferences_table='';}
else {
    switch ($preference_action) {
        case 'savePrefs':
            foreach ($types as $type_info) {
                if ($type_info['allow_user_edit_flag']==0) next;
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
            if (!$msg) $msg=_("Preferences successfully saved");
//            $msg=urlencode($msg);
            Header("Location: self.php?msg=$msg");
        break;
        default:
        case 'displayPrefs':
            $user_preferences_table="<table class=widget>";
            foreach ($types as $type_info) {
                if ($type_info['allow_user_edit_flag']==0) next;
                $user_preference_type_id=$type_info['user_preference_type_id'];
                $type_desc=$type_info['user_preference_description'];
                $type_pretty_name=$type_info['user_preference_pretty_name'];
                if (!$type_pretty_name) $type_pretty_name=$type_info['user_preference_name'];
                
                if ($type_info['allow_multiple_flag']==1) {
                    //branch for showing multiple options, fetch all user set options   
                    $element_field=render_preference_form_multi_element($con, $user_id, $user_preference_type_id, $type_info);
                } else {
                //branch for showing single option
                    $preference_value=get_user_preference($con, $user_id, $user_preference_type_id);
                    $element_field=render_preference_form_element($con, $user_preference_type_id, $preference_value, $type_info);
                }
                $user_preferences_table.="<tr><td class=widget_content_label><b>$type_pretty_name</b><br>$type_desc</td><td class=widget_content_form_element>$element_field</td></tr>";
            }
            $user_preferences_table.="<tr><td class=widget_content_form_element><input type=hidden name=preference_action value=savePrefs><input class=button type=submit value=\""._("Save Preferences") . "\"></tr></td>";
            $user_preferences_table.="</table>";
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