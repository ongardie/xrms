<?php
/**
 * Utility functions for manipulating users
 *
 * $Id: utils-users.php,v 1.1 2005/02/10 23:44:20 vanmer Exp $
 */

/**
 * function add_xrms_user
 *
 * adds a user to the xrms database, using passed in paramters
 *
 * @param adodbconnection $con
 * @param string $new_username with new username with which to create the user
 * @param string $password with cleartext password for user
 * @param integer $role_id with role for user (defaults to 1, User)
 * @param string $first_names with first names of user
 * @param string $last_name of user
 * @param string $email with email address of user
 * @param integer $gmt_offset with +/- offset from gmt
 * @param boolean $user_enabled indicating if user account should have login access (defaults to true)
 * @param integer $user_id optionally specifying the user_id for the new user
 * @return integer $user_id with user_id of new user
 */
function add_xrms_user($con, $new_username, $password, $role_id, $first_names, $last_name, $email, $gmt_offset, $user_enabled=true, $user_id=false) {
    
    $gmt_offset = ($gmt_offset < 0) || ($gmt_offset > 0) ? $gmt_offset : 0;
    $password = md5($password);
    $user_record_status = ($user_enabled) ? 'a' : 'd';    
    //save to database
    $rec = array();
    $rec['role_id'] = $role_id;
    $rec['last_name'] = $last_name;
    $rec['first_names'] = $first_names;
    $rec['username'] = $new_username;
    $rec['password'] = $password;
    $rec['email'] = $email;
    $rec['gmt_offset'] = $gmt_offset;
    $rec['user_record_status']=$user_record_status;
    $rec['language'] = 'english';
    if ($user_id) $rec['user_id']=$user_id;
    
    $tbl = 'users';
    $ins = $con->GetInsertSQL($tbl, $rec, get_magic_quotes_gpc());
    $rst = $con->execute($ins);
    
    if(!$rst) {
        db_error_handler($con, $ins);
    }
    $user_id=$con->Insert_ID();
    if (!$group) {
        $group="Users";
    }
    add_user_group(false, $group, $user_id, $role_id);
    return $user_id;
}

/**
 * function get_xrms_user
 *
 * function to get data about an xrms user
 * @param adodbconnection $con
 * @param string $username with username to search for
 * @param integer $user_id with user_id to search for
 * @return array of user data
 * 
*/ 
function get_xrms_user($con, $username=false, $user_id=false) {
    if (!$username AND !$user_id) return false;
    $sql = "SELECT * from users WHERE";
    if ($username) $sql.=" username=" . $con->qstr($username, get_magic_quotes_gpc());
    elseif ($user_id) $sql.=" user_id=$user_id";
    
    $rst = $con->execute($sql);
    if (!$rst) {
        db_error_handler($con, $sql);
        return false;
    }
    if (!$rst->EOF) {
        return $rst->fields;
    }
    return false;   
}

/**
 * $Log: utils-users.php,v $
 * Revision 1.1  2005/02/10 23:44:20  vanmer
 * -Initial revision of a set of functions for manipulating user data
 *
 *
*/

?>