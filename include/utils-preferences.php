<?php
/**
 * utils-preferences.php - this file contains user/system preference functions for XRMS
 *
 * Functions in this file may be used throughout XRMS.
 * Almost all files in the system include this file.
 *
 * User Preferences are based on User Preference Types, which control the visibility and function of user preferences
 * Each user preference is associated with a type.
 *
 * Depending on the way the type is configured, the user preference can store more than one option for a user in a preference type, with a name.
 * This makes sense for certain configuration items which might be for instance different named views on a pager, or different saved searches for a particular
 * page.  Each preference for which multiple options exist also allow the setting of a default, which can be fetched
 * Other preferences might only allow a single value, such as the users theme preference or the number of columns to display by default on a search
 *
 * The preferences are also configured to provide system level defaults for user-specific preferences.  These preferences will be used only if there are no user
 * preferences set.
 *
 * @author Aaron van Meerten
 * @package XRMS_API
 *
 * $Id: utils-preferences.php,v 1.19 2006/07/25 19:44:44 vanmer Exp $
 */

if ( !defined('IN_XRMS') )
{
  die('Hacking attempt');
  exit;
}

/**
 * Set preference for user
 *
 * @param handle @$con handle to database connection
 * @param integer $user_id specifying which user's preference to load
 * @param string $preference_type specifying which preference type to load (name or number)
 * @param string $preference_value specifying value to set
 * @param string $preference_name optionally specifying which of multiple entries to retrieve
 * @param boolean $set_default optionally setting just set option to default for multiple entries
 * @return boolean indication success of setting user preference
 */
function set_user_preference(&$con, $user_id, $preference_type, $preference_value, $preference_name=false, $set_default=false) {
    if (!$user_id AND $user_id!==0) return false;
    if (!$preference_type) return false;
    if (!is_numeric($preference_type)) {
        $preference_type_data=get_user_preference_type($con, $preference_type);
        $preference_type=$preference_type_data['user_preference_type_id'];
        if (!$preference_type) return false;
    }

    $sql = "SELECT * FROM user_preferences WHERE user_preference_type_id=$preference_type AND user_id=$user_id";
    if ($preference_name) $sql.= " AND user_preference_name=".$con->qstr($preference_name);
    $pref_rst=$con->execute($sql);
//    echo $sql;
    if (!$pref_rst) {
        db_error_handler($con, $sql);
        return false;
    }
    $preference_record['user_preference_status']='a';
    $preference_record['user_preference_type_id']=$preference_type;
    $preference_record['user_preference_value']=$preference_value;
    $preference_record['user_preference_modified_on']=time();
    $preference_record['user_id']=$user_id;

    if ($preference_name)
        $preference_record['user_preference_name']=$preference_name;


    if ($pref_rst->numRows()==0) {
        $pref_table='user_preferences';
        $pref_sql = $con->GetInsertSQL($pref_table, $preference_record);
    } else {
        $pref_sql = $con->GetUpdateSQL($pref_rst, $preference_record);
    }
    if ($pref_sql) {
        $pref_result=$con->execute($pref_sql);
        if (!$pref_result) { db_error_handler($con, $pref_sql); return false; }
    } //else echo "NO SQL USED<br>";

    if ($preference_name AND $set_default) {
        set_default_user_preference($con, $user_id, $preference_type, $preference_name);
    }

    //clear the function cache for ALL value and the named value (or default)
    $params=array($user_id, $preference_type, false, $preference_name);
    function_cache_unset($func_name,$params,false);
    $params=array($user_id, $preference_type, true, false);
    function_cache_unset($func_name,$params,false);
    return true;
}

/**
 * Set default on a preference for user
 *
 * @param handle @$con handle to database connection
 * @param integer $user_id specifying which user's preference to set default on
 * @param string $preference_type specifying which preference type to set default for (name or number)
 * @param string $preference_name specifying which of multiple entries to set as default
 * @return boolean indication success of setting default
 */
function set_default_user_preference($con, $user_id, $preference_type, $preference_name) {
//    echo "IN set_default_user_preference<br>";
    if (is_numeric($preference_type)) {
        $preference_type_data=get_user_preference_type($con, false, $preference_type);
    } else {
        $preference_type_data=get_user_preference_type($con, $preference_type);
    }
    $type_name=$preference_type_data['user_preference_name'];
    if ($type_name) {
        return set_user_preference($con, $user_id, 'default', $preference_name, $type_name);
    } else return false;
}

/**
 * Get default on a preference for user, on a preference type
 *
 * @param handle @$con handle to database connection
 * @param integer $user_id specifying which user's preference to load
 * @param string $preference_type specifying which preference type to set default for (name or number)
 * @return string specifying name of default option for specified preference type
 */
function get_default_user_preference($con, $user_id, $preference_type) {
    if (is_numeric($preference_type)) {
        $preference_type_data=get_user_preference_type($con, false, $preference_type);
    } else {
        $preference_type_data=get_user_preference_type($con, $preference_type);
    }
    $type_name=$preference_type_data['user_preference_name'];
    if ($type_name) {
        return get_user_preference($con, $user_id, 'default',$type_name);
    } else return false;
}

/**
 * Remove default on a preference for user
 *
 * @param handle @$con handle to database connection
 * @param integer $user_id specifying which user's preference to load
 * @param string $preference_type specifying which preference type to set default for (name or number)
 * @return string specifying name of default option for specified preference type
 */
function delete_default_user_preference($con, $user_id, $preference_type, $delete_from_db=false) {
    if (is_numeric($preference_type)) {
        $preference_type_data=get_user_preference_type($con, false, $preference_type);
    } else {
        $preference_type_data=get_user_preference_type($con, $preference_type);
    }
    $type_name=$preference_type_data['user_preference_name'];
    if ($type_name) {
        //remove the setting of the default preference for the user for the user preference type's name
        return delete_user_preference($con, $user_id, 'default',$type_name, false, $delete_from_db);
    } else return false;
}

/**
 * Get preference for user, based on the preference type (to get the users default response) or by preference_name for a particular named preference
 * Can optionally show all preferences for a user, in an array
 *
 * @param handle @$con handle to database connection
 * @param integer $user_id specifying which user's preference to load
 * @param string $preference_type specifying which preference type to set default for (name or number)
 * @param string $preference_name optionally specifying which of multiple entries to return
 * @param boolean $show_all optionally returning all available user preferences of specified type
 * @param boolean $show_all optionally use function caching to improve performance
 * @return string specifying value of preference requested (or false for failure)
 */
function get_user_preference($con, $user_id, $preference_type, $preference_name=false, $show_all=false, $use_cache=true) {
    if (!$con) $con=get_xrms_dbconnection();
    if (!$user_id AND $user_id!==0) {
        return false;
    }

//    echo "IN get_user_preference<br>";
    if (is_numeric($preference_type)) {
//        echo "NUMERIC";
        $preference_type_data=get_user_preference_type($con, false, $preference_type);
    } else {
//        echo "STRING";
//        echo "get_user_preference_type($con, $preference_type);";
        $preference_type_data=get_user_preference_type($con, $preference_type);
    }
    $preference_type=$preference_type_data['user_preference_type_id'];
    $func_name='get_user_preference';
    $params=array($user_id,$preference_type, $show_all, $preference_name);
    if($use_cache) {
        if (function_cache_bool($func_name, $params)) {
            return function_cache_get($func_name, $params);
        }
    }
    if (!$preference_type) { return false; }
    if ($preference_type_data['allow_multiple_flag']==1) {
        $allow_multiple=true;
    } else $allow_multiple=false;

    if (!$show_all AND !$preference_name AND ($preference_type_data['allow_multiple_flag']==1)) {
        $preference_name = get_default_user_preference($con, $user_id, $preference_type);
    }

    $where=array();
    $where[]="user_preference_type_id=$preference_type";
    $where[]="user_preference_status='a'";

    //find preference for user, or system (user_id 0)
    $where[]="( user_id=$user_id OR user_id=0)";
    if ($preference_name) {
        $where[]="user_preference_name=".$con->qstr($preference_name);
    }
    $wherestr=implode(" AND ", $where);
    $sql = "SELECT * FROM user_preferences WHERE $wherestr ORDER BY user_id";
    //if showing all preferences, sort with user preferences second, to allow them to override system preferences
    if ($show_all AND $allow_multiple) $sql.=" ASC";
    //otherwise show user options first, to allow user preference to be used if available
    else $sql.=" DESC";

//    echo "<p><hr>$sql<p>\n";

    $pref_rst=$con->execute($sql);

    if (!$pref_rst) { db_error_handler( $con, $sql); return false; }

    if ($allow_multiple AND $show_all) {
        $ret=array();
        while (!$pref_rst->EOF) {
            if ($pref_rst->fields['user_preference_name']) {
                $ret[$pref_rst->fields['user_preference_name']]=$pref_rst->fields['user_preference_value'];
            } else { $ret[]=$pref_rst->fields['user_preference_value']; }
            $pref_rst->movenext();
        }
    } else {
        $ret=$pref_rst->fields['user_preference_value'];
    }
    if ($ret) {
        function_cache_set($func_name, $params, $ret, false);
        return $ret;
    }
    else return false;
}

/**
 * Remove a preference setting for a user.
 * Can remove a specific instance of a preference or all preferences of a type for a user, and can optionally delete from
 * the database instead of simply marking the record_status field with a 'd'
 *
 * @param handle @$con handle to database connection
 * @param integer $user_id specifying which user's preference to load
 * @param string $preference_type specifying which preference type to set default for (name or number)
 * @param string $preference_name optionally specifying which of multiple entries to return
 * @param boolean $delete_all optionally removing all available user preferences of specified type
 * @param boolean $delete_from_db optionally deleting record from database instead of simply setting status to deleted
 * @return string specifying value of preference requested (or false for failure)
 */
 function delete_user_preference($con, $user_id, $preference_type, $preference_name=false, $delete_all=false, $delete_from_db=false) {
//    echo "IN get_user_preference<br>";
    if (is_numeric($preference_type)) {
//        echo "NUMERIC";
        $preference_type_data=get_user_preference_type($con, false, $preference_type);
    } else {
//        echo "STRING";
//        echo "get_user_preference_type($con, $preference_type);";
        $preference_type_data=get_user_preference_type($con, $preference_type);
    }
    if (!$preference_type_data) return false;
    $preference_type=$preference_type_data['user_preference_type_id'];
    if ($preference_type_data['allow_multiple_flag']==1) {
        $allow_multiple=true;
    } else $allow_multiple=false;

    if (!$delete_all AND $preference_name AND $allow_multiple) {
        //make sure the deleted preference is not the default.  If it is, unset the default
        $default_preference_name = get_default_user_preference($con, $user_id, $preference_type);
        if ($default_preference_name==$preference_name) { delete_default_user_preference($con, $user_id, $preference_type, $delete_from_db); }
    }
    if (!$preference_name AND $allow_multiple AND !$delete_all) {
        return false;
    } else {
        if ($delete_all AND $allow_multiple) delete_default_user_preference($con, $user_id, $preference_type, $delete_from_db);
    }

    $where=array();
    $where[]="user_preference_type_id=$preference_type";

    //find preference for user, or system (user_id 0)
    $where[]="user_id=$user_id";
    if ($preference_name) {
        $where[]="user_preference_name=".$con->qstr($preference_name);
    }
    $wherestr=implode(" AND ", $where);
    if (!$delete_from_db) {
        $sql = "UPDATE user_preferences SET user_preference_status='d' WHERE $wherestr";
    } else {
        $sql = "DELETE FROM user_preferences WHERE $wherestr";
    }
//    echo "<br>$sql<br>";
    $pref_rst=$con->execute($sql);
    if (!$pref_rst) { db_error_handler( $con, $sql); return false; }
    return true;
}


/**
 * This function returns a string with the HTML to change/edit multiple user preference entries of a single type
 * @todo make this work right instead of using print_rs
 *
**/
function render_preference_form_multi_element($con, $user_id, $user_preference_type_id, $type_info=false) {
    if (!$user_preference_type_id) return false;
    if (!$type_info) {
        $type_info=get_user_preference_type($con, false, $user_preference_type_id);
    }
    $user_multi_options=get_user_preference($con, $user_id, $user_preference_type_id, false, true);
    if ($user_multi_options) {
        $ret="<pre>";
        foreach ($user_multi_options as $opt_key=>$preference) {
            $ret.=print_r($preference,true);
        }
        $ret.='</pre>';
    }
}

/**
 * This function returns a string with the HTML to change/edit a user preference entry
 *
 * @param adodbconnection $con
 * @param integer $user_preference_type_id with type of user preference to render form element
 * @param integer $element_value with existing user preference value, if one exists
 * @param array $type_info with data about user preference type (retrieved if not provided)
 * @param string $element_extra_attributes with extra parameters to add inside the input element tag
 * @return string with HTML for preference form element
 *
**/
function render_preference_form_element($con, $user_preference_type_id, $element_value=false, $type_info=false, $element_extra_attributes='') {
    if (!$user_preference_type_id AND !$type_info) return false;
    if (!$type_info) {
        $type_info=get_user_preference_type($con, false, $user_preference_type_id);
    } else { $user_preference_type_id=$type_info['user_preference_type_id']; }
    $element_type=$type_info['form_element_type'];
    $element_name=$type_info['user_preference_name'];
    $element_label=$type_info['user_preference_pretty_name'];
    $read_only=$type_info['read_only'];

    $show_blank_first=false;
    $element_length=false;
    $element_height=false;
    switch ($element_type) {
        case 'select':
            $show_blank_first=true;
        case 'radio':
            $possible_values=get_preference_possible_values($con, $user_preference_type_id, $type_info);
        break;
        default:
            $show_blank_first=false;
            $possible_values=false;
            if (!$element_type) $element_type='text';
        break;
    }
    $element=create_form_element($element_type, $element_name, $element_value, $element_extra_attributes, $element_length, $element_height, $possible_values, $show_blank_first, $read_only);

    return $element;

}

/**
 * This function returns an array of possible css themes with the name also as the array key
 * Used when rendering the CSS theme selector in the user preferences
 *
**/
function get_css_theme_possible_values() {
    $themes=get_css_themes();
    if ($themes) {
        $possible_values=array();
        foreach ($themes as $theme_name=>$theme_files) {
            $possible_values[$theme_name]=$theme_name;
        }
        return $possible_values;
    }
    return false;
}

/**
 * This function returns an array of possible languages with the language name as the value, and the language code as the key
 * Used when rendering the language selector in the user preference
 *
**/
function get_language_possible_values() {
    global $languages;
    global $xrms_file_root;
    $locale_root=$xrms_file_root.DIRECTORY_SEPARATOR.'locale';
    foreach ($languages as $lkey=>$ldetail) {
        if ($ldetail['NAME']) {
            if ($lkey=='en_US' OR is_dir($locale_root.DIRECTORY_SEPARATOR.$lkey)) {
                $possible_values[$lkey]=$ldetail['NAME'];
            }
        }
    }
    return $possible_values;
}

/**
 * This function returns an array of possible options for a user preference type, based on ID or info
 * Used when rendering the HTML table of user preferences
 * This is where custom user preferences output can be done (like languages and CSS)
 * Plugins can also add option values using a hook in this function
 *
 * @param adodbconnection $con with handle to the database
 * @param integer $user_preference_type_id with identifier for the user preference type
 * @param array $type_info with data about the user preference type (will be retrieved if not provided)
 * @return associative array with option value as the key and display name as the value
 *
**/
function get_preference_possible_values($con, $user_preference_type_id, $type_info=false) {
    if (!$user_preference_type_id) return false;

    if (!$type_info) {
        $type_info=get_user_preference_type($con, false, $user_preference_type_id);
    }
    $preference_name = $type_info['user_preference_name'];
    switch ($preference_name) {
        case 'user_language':
            return get_language_possible_values();
        break;
        case 'css_theme':
            return get_css_theme_possible_values();
        break;
        default:
            $options=get_preference_options($con, $user_preference_type_id, false, true);
        break;
    }
    $option_record['user_preference_name']=$preference_name;
    $option_record['user_preference_type_id']=$user_preference_type_id;
    $option_record['possible_values']=$options;
    do_hook_function('preference_possible_values',$option_record);
    if ($option_record['possible_values']!=$options) {
        $options=$option_record['possible_values'];
    }
    return $options;
}

/**
 * This function returns an array of user preference options
 *
 * @param adodbconnection $con with handle to the database
 * @param integer $user_preference_type_id to retrieve options for
 * @param boolean $show_all optionally specifying if deleted options should be shown (defaults to false, only show active options)
 * @param boolean $return_possible_values specifying if return is an array of option values, keyed by db id (default) or an array of display values, keyed by option value (true)
 * @return array with option data keyed by database identifier or option value, or false if failure occurs
**/
function get_preference_options($con, $user_preference_type_id, $show_all=false, $return_possible_values=false) {
    if (!$user_preference_type_id) return false;
    $sql = "SELECT * FROM user_preference_type_options WHERE user_preference_type_id=$user_preference_type_id";
    if (!$show_all) $sql.= " AND option_record_status='a'";
    $sql .=" ORDER BY sort_order";
    $rst = $con->execute($sql);
//    echo $sql;
    if (!$rst) { db_error_handler($con, $sql); return false; }
    else {
        if ($rst->EOF) return false;
        $options=array();
        while (!$rst->EOF) {

            $display=$rst->fields['option_display'];
            if (!$display) { $display=$rst->fields['option_value']; }

            if (!$return_possible_values) {
                $options[$rst->fields['up_option_id']]=$rst->fields['option_value'];
            } else {
                $options[$rst->fields['option_value']]=$display;
            }
            $rst->movenext();
        }
        if (count($options)>0) {
            return $options;
        }
    }
    return false;
}

/**
 * This function adds an option to the list of possible values that a user preference type can have
 *
 * @param adodbconnection $con with handle to the database
 * @param integer $user_preference_type_id with ID of the user preference type to add an option for
 * @param string $option_value with value to add as an option for user preference type
 * @param string $option_display with string to show as display name for the option value
 * @param integer $sort_order to determine what order option is shown in, defaults to 1, shown with first options
 * @return integer $key with database identifier of the newly added key, or existing id, or false if addition failed
 **/
 function add_preference_option($con, $user_preference_type_id, $option_value, $option_display=false, $sort_order=1) {
    if (!$user_preference_type_id OR !$option_value) return false;

    $options=get_preference_options($con, $user_preference_type_id, true);
    if ($options) {
        //option already exists, so return true
        $key=array_search($option_value, $options);
        if ($key) {
            $upd['option_record_status']='a';
            $rst=$con->execute("Select * from user_preference_type_options WHERE up_option_id=$key");
            $upd_sql=$con->getUpdateSQL($rst, $upd);
            if ($upd_sql) {
                $upd_rst=$con->execute($upd_sql);
                if (!$upd_rst) { db_error_handler($con, $sql); return false; }
            }
            return $key;
         }
    }

    //option doesn't exist, so add it
    if (!$option_display) $option_display=$option_value;
    $ins=array();
    $ins['sort_order']=$sort_order;
    $ins['user_preference_type_id']=$user_preference_type_id;
    $ins['option_value']=$option_value;
    $ins['option_display']=$option_display;
    $table="user_preference_type_options";
    $ins_sql = $con->getInsertSQL($table, $ins);
    if ($ins_sql) {
        $ins_rst=$con->execute($ins_sql);
        if (!$ins_rst) { db_error_handler($con, $ins_sql); return false; }
        else return $con->Insert_ID();
    }
    return false;
}

/**
 * This function deletes an entry as an option for a user preference type, based on value and preference type
**/
function delete_preference_option($con, $user_preference_type_id, $option_value, $delete_from_database=false) {
    $options=get_preference_options($con, $user_preference_type_id);
    $key = array_search($option_value, $options);
    if ($key!==false) {
        if ($delete_from_database) {
            $sql = "DELETE FROM user_preference_type_options";
        } else $sql = "UPDATE user_preference_type_options SET option_record_status='d'";
        $sql .= " WHERE up_option_id=$key";
        $rst=$con->execute($sql);
        if (!$rst) { db_error_handler($con, $sql); return false; }
        else return true;
    }
    return false;
}

/**
 * Get data about a user preference type
 *
 * @param handle @$con handle to database connection
 * @param string $type_name optionally specifying the name of the preference type (required if not specifying type_id)
 * @param integer $type_id optionally specifying the database id of the preference type (required if not specifying type_name)
 * @param boolean $return_all optionally specifying if all user preference types get returned
 * @return array describing user preference type
 */
function get_user_preference_type($con, $type_name=false, $type_id=false, $return_all=false) {
    if (!$type_name AND !$type_id AND !$return_all) return false;
    $func_name='get_user_preference_type';
    $params=array($type_name,$type_id,$return_all);
    if (!$return_all) {
        if (function_cache_bool($func_name, $params)) {
            return function_cache_get($func_name, $params);
        }
    }
    $where=array();
    $where[]="user_preference_type_status='a'";
    if ($type_name) $where[]="user_preference_name=". $con->qstr($type_name);
    if ($type_id) $where[]="user_preference_type_id=$type_id";
    $wherestr= implode(" AND ", $where);
    $sql = "SELECT * FROM user_preference_types WHERE $wherestr ORDER BY user_preference_pretty_name";
    $type_rst=$con->execute($sql);

    if ($return_all) $types=array();

    if (!$type_rst) { db_error_handler($con, $sql); return false; }
    if ($type_rst->numRows()>0) {
        if ($return_all) {
            while (!$type_rst->EOF) {
                $types[$type_rst->fields['user_preference_type_id']]=$type_rst->fields;
                $type_rst->movenext();
            }
            if (count($types)>0) {
                return $types;
            }
         } else {
            $preference_type_data=$type_rst->fields;
            function_cache_set($func_name, $params, $preference_type_data, false);
            return $preference_type_data;
        }
    }
    return false;
}

/**
 * Adds a user preference type to the database
 *
  * @param adodbconnection $con handle to the database
  * @param string $user_preference_name with unique name of preference
  * @param string $user_preference_pretty_name optionally providing display name for user preference (used for user edit)
  * @param string $user_preference_description optionally providing description for user preference (used for user edit)
  * @param boolean $allow_multiple optionally specifying if user option can have multiple entires (defaults to false, only allow single entry)
  * @param boolean $allow_user_edit optionally specifying if preference should be included in the UI for editing (defaults to false, do not show in user UI)
  * @param string $form_element_type optionally specifying what type of field should be displayed in the UI, defaults to text
  * @param boolean $read_only optionally specifying if preference should be included in the UI for as read-only (not editable) (defaults to false, allow edit in UI)
  * @return integer with database id of newly created preference type (or pre-existing id), or false for failure
**/
function add_user_preference_type($con,
                                                                    $user_preference_name,
                                                                    $user_preference_pretty_name=false,
                                                                    $user_preference_description=false,
                                                                    $allow_multiple=false,
                                                                    $allow_user_edit=false,
                                                                    $form_element_type=false,
                                                                    $read_only=false,
								    $skip_system_edit=false)
								    {
    if (!$user_preference_name) {
        //if no preference name is specified, fail
        return false;
    }
    //if user preference already exists, return it
    $pref_info = get_user_preference_type($con, $user_preference_name);
    if ($pref_info) return $pref_info['user_preference_type_id'];

    $preference_type=array();
    $preference_type['user_preference_name']=$user_preference_name;

    if ($user_preference_pretty_name)
        $preference_type['user_preference_pretty_name']=$user_preference_pretty_name;

    if ($user_preference_description)
        $preference_type['user_preference_description']=$user_preference_description;

    if ($allow_multiple)
        $preference_type['allow_multiple_flag']=1;

    if ($allow_user_edit)
        $preference_type['allow_user_edit_flag']=1;

    if ($read_only)
        $preference_type['read_only']=1;

    if ($skip_system_edit)
        $preference_type['skip_system_edit_flag']=1;

    if ($form_element_type)
        $preference_type['form_element_type']=$form_element_type;

    $table = "user_preference_types";
    $insert_sql = $con->getInsertSQL($table, $preference_type);

    if ($insert_sql) {
        $rst=$con->execute($insert_sql);
        if (!$rst) { db_error_handler($con, $insert_sql); return false; }
        else return $con->Insert_ID();
    } else return false;

}

/**
 *  Deletes a user preference type, optionally from the database
 *
 * @param adodbconnection $con handle to databsae
 * @param integer $user_preference_type_id with database identifier for user preference type
 * @param boolean $delete_from_database indicating if record should be deleted or just marked with status 'd' (defaults to false, status 'd')
 * @return boolean indicating success of delete operation
 */
function delete_user_preference_type($con, $user_preference_type_id, $delete_from_database=false) {
    if (!$user_preference_type_id) return false;
    if ($delete_from_database) {
        $sql = "DELETE FROM user_preference_types";
    } else {
        $sql = "UPDATE user_preference_type SET user_preference_type_status='d'";
    }

    $sql .= " WHERE user_preference_type_id=$user_preference_type_id";
    $rst=$con->execute($sql);
    if (!$rst) {
        db_error_handler($con, $sql); return false;
    } else return true;
}

/**
 * Get data about all user preference type
 *
 * @param handle @$con handle to database connection
 * @param boolean $show_only_active indicating if only active preference types should be listed (defaults to true)
 * @return array of arrays describing user preference types
 */
function list_user_preference_types($con, $show_only_active=true){

    $sql = "SELECT * FROM user_preference_types";
    if ($show_only_active) { $sql.=" WHERE user_preference_type_status='a'"; }

    $type_rst=$con->execute($sql);
    if (!$type_rst) { db_error_handler($con, $sql); return false; }
    if ($type_rst->numRows()>0) {
        $ret=array();
        while (!$type_rst->EOF) {
            $ret[]=$type_rst->fields;
            $type_rst->movenext();
        }
        return $ret;
    } else return false;
}

/**
 * This function is a wrapper for get_user_preferences_table forcing user_id to 0, to show system preferences
 * @param adodbconnection $con with handle to the database
 * @return system preferences table
**/
function get_admin_preferences_table($con) {
    return get_user_preferences_table($con, 0);
}

/**
 * This function is a wrapper for get_user_preferences forcing user_id to 0, to retrieve system preference
**/
function get_admin_preference($con, $preference_type, $preference_name=false, $show_all=false) {
    return get_user_preference($con, 0, $preference_type, $preference_name, $show_all);
}

/**
 * This function is a wrapper for set_user_preferences forcing user_id to 0, to set system preference
**/
function set_admin_preference($con, $preference_type, $preference_value, $preference_name=false, $set_default=false) {
    return set_user_preference($con, 0, $preference_type, $preference_value, $preference_name, $set_default);
}

/**
 * This function renders HTML with the preferences and options for a user, with their current preferences selected
 *
 * @param adodbconnection $con with handle to the database
 * @param integer $user_id with user for which to retrieve preferences (defaults to false, fetch from global session_user_id)
 * @return string with HTML table of user preferences and options
**/
function get_user_preferences_table($con, $user_id=false) {
    global $session_user_id;
    global $msg;
    if (!$user_id AND $user_id!==0) {
        $user_id=$session_user_id;
    }

    if ($user_id==0) {
        $admin=true;
    } else $admin=false;

    if ($admin) {
        $table_title=_("System Preferences");
    } else {
        $table_title=_("User Preferences");
   }
    //get all user preference types
    $types=get_user_preference_type($con, false, false, true);
    if (!$types) { $msg="Failed to load an user preference types, no user preferences available"; $user_preferences_table='';}
    else {
        if ($user_id!=$session_user_id) { $user_preferences_table="<input type=hidden name=edit_user_id value=$user_id>"; }
	else $user_preferences_table='';

        $user_preferences_table.="<table class=widget>";
        $user_preferences_table.="<tr><td colspan=2 class=widget_header>$table_title</td></tr>";
        foreach ($types as $type_info) {
            if ((!$type_info['allow_user_edit_flag']) AND (!$admin)) continue;
	    if ($type_info['skip_system_edit_flag']) continue;
            $user_preference_type_id=$type_info['user_preference_type_id'];
            if ($type_info['user_preference_description']) {
                $type_desc=_($type_info['user_preference_description']);
            } else $type_desc='';
            $type_pretty_name=_($type_info['user_preference_pretty_name']);
            if (!$type_pretty_name) $type_pretty_name=_($type_info['user_preference_name']);

            if ($type_info['allow_multiple_flag']==1) {
                //branch for showing multiple options, fetch all user set options
                $element_field=render_preference_form_multi_element($con, $user_id, $user_preference_type_id, $type_info);
            } else {
            //branch for showing single option
                $preference_value=get_user_preference($con, $user_id, $user_preference_type_id);
                $element_field=render_preference_form_element($con, $user_preference_type_id, $preference_value, $type_info);
            }

            //this is to avoid printing translation file header instead of type description!
            if($type_desc != NULL){
                    $user_preferences_table.="<tr><td class=widget_content_label><b>"._($type_pretty_name)."</b><br>"._($type_desc)."</td><td class=widget_content_form_element>$element_field</td></tr>";
                }else{
                    $user_preferences_table.="<tr><td class=widget_content_label><b>"._($type_pretty_name)."</b><br>$type_desc</td><td class=widget_content_form_element>$element_field</td></tr>";
                }
        }
        $user_preferences_table.="<tr><td colspan=2 class=widget_content_form_element><input type=hidden name=preference_action value=savePrefs><input class=button type=submit value=\""._("Save Preferences") . "\"></tr></td>";
        $user_preferences_table.="</table>";
    }
    return $user_preferences_table;
}

/**
 * This function is intended to be run on XRMS installs which still use the deprecated system parameters subsystem,.
 * This code upgrades all system parameters into system preferences, with their options and values intact
 *
 * @param adodbconnection $con with handle to the database to upgrade
**/
function upgrade_system_parameter_user_preferences($con) {
    $sql = "SELECT * FROM system_parameters";
    $rst=$con->execute($sql);
    $count=0;
    //no table available, or some other error occured, so return false
    if (!$rst) { return false; }
    while (!$rst->EOF) {
        $param = $rst->fields['param_id'];
        $type=get_user_preference_type($con, $param);
        if (!$type) {
            $count++;
            move_system_parameters($con, $rst->fields);
        }
        $rst->movenext();
    }
    return $count;
}

/**
 * This function is intended to be run on XRMS installs which still use the deprecated system parameters subsystem,.
 * This code upgrades one set of system parameters into system preferences, with its options and value intact
 *
 * @param adodbconnection $con with handle to the database to upgrade
 * @param array $fields with previous system parameter fields
**/
function move_system_parameters($con, $fields) {
    $param = $fields['param_id'];
    $description = $fields['description'];
    $value = get_system_parameter($con, $param);

    $sql = "SELECT * from system_parameters_options WHERE param_id=".$con->qstr($param, get_magic_quotes_gpc()) . " ORDER BY sort_order";
    $rst = $con->execute($sql);
    if (!$rst) { db_error_handler($con, $sql); return false; }
    //if options existed, use these options, otherwise just use text field
    if (!$rst->EOF) {
        $element_type='select';
    } else { $element_type='text'; }

    $type_id=add_user_preference_type($con, $param, $param, $description,  false, false,$element_type);
    set_admin_preference($con, $type_id, $value);

    while (!$rst->EOF) {
        $fields=$rst->fields;
        $sort_order=$fields['sort_order'];
        $option_value = (($fields['string_val']) ? $fields['string_val'] : (($fields['int_val']) ? $fields['int_val'] : (($fields['float_val']) ? $fields['float_val'] : (($fields['datetime']) ? $fields['datetime'] : false) ) ) );

        $option_display=false;
        switch ($option_value) {
            case 'y':
                $option_display='Yes';
            break;
            case 'n':
                $option_display='No';
            break;
        }

        add_preference_option($con, $type_id, $option_value, $option_display, $sort_order);
        $rst->movenext();
    }

}

/**
 * $Log: utils-preferences.php,v $
 * Revision 1.19  2006/07/25 19:44:44  vanmer
 * - ensure that user preferences table passed on user_id, to allow admin edit of alternate user info
 *
 * Revision 1.18  2006/07/07 20:03:44  vanmer
 * - altered params when caching user_preference_type
 *
 * Revision 1.17  2006/04/26 13:13:53  braverock
 * - order preference display by user_preference_pretty_name
 *
 * Revision 1.16  2006/04/05 00:51:42  vanmer
 * - changed to use preference name in cache settings
 * - use new function cache unset function to clear function cache when setting new preferences
 *
 * Revision 1.15  2006/03/16 23:39:50  vanmer
 * - added system edit flag to control display of system preferences
 *
 * Revision 1.14  2006/02/01 21:46:13  daturaarutad
 * add use_cache param to get_user_preference()
 *
 * Revision 1.13  2005/12/06 22:36:01  vanmer
 * - added check to ensure that system parameter table exists before attempting to upgrade it to system preferences
 *
 * Revision 1.12  2005/12/02 01:45:22  vanmer
 * - added more PHPDoc to preferences API
 * - added XRMS_API package tag
 *
 * Revision 1.11  2005/11/30 00:45:20  vanmer
 * - added read-only option for preferences
 *
 * Revision 1.10  2005/08/05 22:13:23  vanmer
 * - added check to ensure that db connection is avaible when loading preferences
 *
 * Revision 1.9  2005/07/08 20:30:29  vanmer
 * - changed to display option value if option display is not available
 *
 * Revision 1.8  2005/07/08 18:46:57  vanmer
 * - removed echo of preference name on admin preference retrieval
 * - added unneeded whitespace
 *
 * Revision 1.7  2005/07/06 17:10:45  vanmer
 * - added function to upgrade system parameters into system preferences
 *
 * Revision 1.6  2005/05/16 22:00:57  vanmer
 * - altered to cache user preference value only after preference type number is found
 * - changed set preference to recache newly set value
 *
 * Revision 1.5  2005/05/16 20:48:36  vanmer
 * - added session caching to user preference functions, both for user preference type and user preference
 * values
 *
 * Revision 1.4  2005/05/07 17:03:21  vanmer
 * - added check for user_id before executing get preference function
 *
 * Revision 1.3  2005/05/06 00:49:17  vanmer
 * - added needed expansion of user preferences to handle default options on a preference
 * - expanded preferences system
 *
**/
?>