<?php
/**
 * utils-prefs.php - this file contains user/system preference functions for XRMS
 *
 * Functions in this file may be used throughout XRMS.
 * Almost all files in the system include this file.
 *
 * @author Aaron van Meerten
 *
 * $Id: utils-preferences.php,v 1.1 2005/01/24 18:26:50 vanmer Exp $
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
    if (!$user_id) return false;
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
    return true;
}

/**
 * Set default on a preference for user
 *
 * @param handle @$con handle to database connection
 * @param integer $user_id specifying which user's preference to load
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
 * Get default on a preference for user
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
 * Get default on a preference for user
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
        return delete_user_preference($con, $user_id, 'default',$type_name, false, $delete_from_db);
    } else return false;
}

/**
 * Get preference for user
 *
 * @param handle @$con handle to database connection
 * @param integer $user_id specifying which user's preference to load
 * @param string $preference_type specifying which preference type to set default for (name or number)
 * @param string $preference_name optionally specifying which of multiple entries to return
 * @param boolean $show_all optionally returning all available user preference of specified type
 * @return string specifying value of preference requested (or false for failure)
 */
function get_user_preference($con, $user_id, $preference_type, $preference_name=false, $show_all=false) {
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
    if ($ret)
        return $ret;
    else return false;
}

/**
 * Remove a preference setting for a user
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
    echo "<br>$sql<br>";
    $pref_rst=$con->execute($sql);
    if (!$pref_rst) { db_error_handler( $con, $sql); return false; }
    return true;
}
/**
 * Get data about a user preference type
 *
 * @param handle @$con handle to database connection
 * @param string $type_name optionally specifying the name of the preference type (required if not specifying type_id)
 * @param integer $type_id optionally specifying the database id of the preference type (required if not specifying type_name)
 * @return array describing user preference type
 */
function get_user_preference_type($con, $type_name=false, $type_id=false) {
    if (!$type_name AND !$type_id) return false;
    $where=array();
    $where[]="user_preference_type_status='a'";
    if ($type_name) $where[]="user_preference_name=". $con->qstr($type_name);
    if ($type_id) $where[]="user_preference_type_id=$type_id";
    $wherestr= implode(" AND ", $where);
    $sql = "SELECT * FROM user_preference_types WHERE $wherestr";
    $type_rst=$con->execute($sql);
    if (!$type_rst) { db_error_handler($con, $sql); return false; }
    if ($type_rst->numRows()>0)
        return $type_rst->fields;
    else return false;
}