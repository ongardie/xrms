<?php
/**
 * Utility functions for manipulating users
 *
 * @package XRMS_API
 *
 * $Id: utils-users.php,v 1.4 2005/12/02 01:47:47 vanmer Exp $
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
function add_xrms_user($con, $new_username, $password, $role_id, $first_names, $last_name, $email, $gmt_offset, $user_enabled=true, $user_id=false, &$error_msg) {
    if (!$new_username) { $error_msg=_("No username specified"); return false; }
    $current_user=get_xrms_user($con, $new_username);
    if ($current_user) { $error_msg=_("User") . ' ' . $new_username . ' ' . _("already exists in the system, please choose a different username"); return false; }
    if (!$password) { $error_msg=_("You must enter a password"); return false; }
    if (!$last_name) { $error_msg=_("You must enter a last name"); return false; }
    $gmt_offset = ($gmt_offset < 0) || ($gmt_offset > 0) ? $gmt_offset : 0;
    $password = md5($password);
    $user_record_status = ($user_enabled) ? 'a' : 'd';    
    //save to database
    $rec = array();
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
    if ($role_id AND $user_id) {
        add_user_group(false, $group, $user_id, $role_id, true);
    }
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
 * Revision 1.4  2005/12/02 01:47:47  vanmer
 * - added XRMS_API package tag
 *
 * Revision 1.3  2005/09/26 01:17:17  vanmer
 * - added more errors if user already exists, if password or last name are not specified
 *
 * Revision 1.2  2005/05/18 05:46:47  vanmer
 * - removed role id from user table on add_xrms_user
 * - made role an optional field, in case of adding a user with no role
 *
 * Revision 1.1  2005/02/10 23:44:20  vanmer
 * -Initial revision of a set of functions for manipulating user data
 *
 *
*/

?>