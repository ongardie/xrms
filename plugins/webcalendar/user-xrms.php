<?php

// This file contains all the functions for getting information
// about users.  So, if you want to use an authentication scheme
// other than the webcal_user table, you can just create a new
// version of each function found below.
//
// Note: this application assumes that usernames (logins) are unique.
//
// Note #2: If you are using HTTP-based authentication, then you still
// need these functions and you will still need to add users to
// webcal_user.


// Set some global config variables about your system.
$user_can_update_password = true;
$admin_can_add_user = true;
$admin_can_delete_user = true;


// Check to see if a given login/password is valid.  If invalid,
// the error message will be placed in $login_error.
// params:
//   $login - user login
//   $password - user password
// returns: true or false
function user_valid_login ( $login, $password ) {
  global $error;
  $ret = false;

  $login_error = "";

  $sql = "SELECT username FROM users WHERE " .
    "username = '" . $login . "' AND password = '" . md5($password) . "'";
  $res = dbi_query ( $sql );
  if ( $res ) {
    $row = dbi_fetch_row ( $res );
    if ( $row && $row[0] != "" ) {
      // MySQL seems to do case insensitive matching, so double-check
      // the login.
      if ( $row[0] == $login )
        $ret = true; // found login/password
      else
        $error = translate ("Invalid login");
    } else {
      $error = translate ("Invalid login");
    }
    dbi_free_result ( $res );
  } else {
    $error = translate("Database error") . ": " . dbi_error();
  }

  return $ret;
}


// Check to see if a given login/crypted password is valid.  If invalid,
// the error message will be placed in $login_error.
// params:
//   $login - user login
//   $crypt_password - crypted user password
// returns: true or false
function user_valid_crypt ( $login, $crypt_password ) {
  global $error;
  $ret = false;

  $login_error = "";
  $salt = substr($crypt_password, 0, 2);

  $sql = "SELECT username, password FROM users WHERE " .
    "username = '" . $login . "'";
  $res = dbi_query ( $sql );
  if ( $res ) {
    $row = dbi_fetch_row ( $res );
    if ( $row && $row[0] != "" ) {
      // MySQL seems to do case insensitive matching, so double-check
      // the login.
      // also check if password matches
      if ( ($row[0] == $login) && (crypt($row[1], $salt) == $crypt_password) )
        $ret = true; // found login/password
      else
        //$error = translate ("Invalid login");
        $error = "Invalid login";
    } else {
      //$error = translate ("Invalid login");
      $error = "Invalid login";
    }
    dbi_free_result ( $res );
  } else {
    //$error = translate("Database error") . ": " . dbi_error();
    $error = "Database error : " . dbi_error();
  }

  return $ret;
}


// Load info about a user (first name, last name, admin) and set
// globally.
// params:
//   $user - user login
//   $prefix - variable prefix to use
function user_load_variables ( $login, $prefix ) {
  global $PUBLIC_ACCESS_FULLNAME, $NONUSER_PREFIX;

  if ($NONUSER_PREFIX && substr($login, 0, strlen($NONUSER_PREFIX) ) == $NONUSER_PREFIX) {
    nonuser_load_variables ( $login, $prefix );
    return true;
  }
  
  if ( $login == "__public__" ) {
    $GLOBALS[$prefix . "login"] = $login;
    $GLOBALS[$prefix . "firstname"] = "";
    $GLOBALS[$prefix . "lastname"] = "";
    $GLOBALS[$prefix . "is_admin"] = "N";
    $GLOBALS[$prefix . "email"] = "";
    $GLOBALS[$prefix . "fullname"] = $PUBLIC_ACCESS_FULLNAME;
    $GLOBALS[$prefix . "password"] = "";
    return true;
  }
  $sql =
    "SELECT first_names, last_name, role_id, email, password " .
    "FROM users WHERE username = '" . $login . "'";
  $res = dbi_query ( $sql );
  if ( $res ) {
    if ( $row = dbi_fetch_row ( $res ) ) {
      $GLOBALS[$prefix . "login"] = $login;
      $GLOBALS[$prefix . "firstname"] = $row[0];
      $GLOBALS[$prefix . "lastname"] = $row[1];
      if ($row[2] == '2') {
         $GLOBALS[$prefix . "is_admin"] = "Y";
      }
      else {
         $GLOBALS[$prefix . "is_admin"] = "N";
      }
      $GLOBALS[$prefix . "email"] = empty ( $row[3] ) ? "" : $row[3];
      if ( strlen ( $row[0] ) && strlen ( $row[1] ) )
        $GLOBALS[$prefix . "fullname"] = "$row[0] $row[1]";
      else
        $GLOBALS[$prefix . "fullname"] = $login;
      $GLOBALS[$prefix . "password"] = $row[4];
    }
    dbi_free_result ( $res );
  } else {
    $error = translate ("Database error") . ": " . dbi_error ();
    return false;
  }
  return true;
}



// Add a new user.
// params:
//   $user - user login
//   $password - user password
//   $firstname - first name
//   $lastname - last name
//   $email - email address
//   $admin - is admin? ("Y" or "N")
function user_add_user ( $user, $password, $firstname, $lastname, $email,
  $admin ) {
  global $error;

    $error = translate ("Database error");
    return false;
}


// Update a user
// params:
//   $user - user login
//   $firstname - first name
//   $lastname - last name
//   $email - email address
//   $admin - is admin?
function user_update_user ( $user, $firstname, $lastname, $email, $admin ) {
  global $error;

    $error = translate ("Database error");
    return false;
}


// Update user password
// params:
//   $user - user login
//   $password - last name
function user_update_user_password ( $user, $password ) {
  global $error;

    $error = translate ("Database error");
    return false;
}



// Delete a user from the system.
// We assume that we've already checked to make sure this user doesn't
// have events still in the database.
// params:
//   $user - user to delete
function user_delete_user ( $user ) {

    $error = translate ("Database error");
    return false;
}


// Get a list of users and return info in an array.
function user_get_users () {
  global $public_access, $PUBLIC_ACCESS_FULLNAME;

  $count = 0;
  $ret = array ();
  if ( $public_access == "Y" )
    $ret[$count++] = array (
       "cal_login" => "__public__",
       "cal_lastname" => "",
       "cal_firstname" => "",
       "cal_is_admin" => "N",
       "cal_email" => "",
       "cal_password" => "",
       "cal_fullname" => $PUBLIC_ACCESS_FULLNAME );
  $res = dbi_query ( "SELECT username, last_name, first_names, " .
    "role_id, email, password FROM users " .
    "ORDER BY last_name, first_names, username" );
  if ( $res ) {
    while ( $row = dbi_fetch_row ( $res ) ) {
      if ( strlen ( $row[1] ) && strlen ( $row[2] ) )
        $fullname = "$row[2] $row[1]";
      else
        $fullname = $row[0];
      $ret[$count++] = array (
        "cal_login" => $row[0],
        "cal_lastname" => $row[1],
        "cal_firstname" => $row[2],
        "cal_is_admin" => $row[3],
        "cal_email" => empty ( $row[4] ) ? "" : $row[4],
        "cal_password" => $row[5],
        "cal_fullname" => $fullname
      );
    }
    dbi_free_result ( $res );
  }
  return $ret;
}



?>
