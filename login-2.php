<?php
/**
 * Check if login is valid
 *
 * $Id: login-2.php,v 1.20 2005/01/11 17:08:38 maulani Exp $
 */
require_once('include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$username = $_POST['username'];
$password = $_POST['password'];
$target   = $_POST['target'];
    if ($target== '') {
        $target=$http_site_root.'/private/home.php';
    }



$con = &adonewconnection($xrms_db_dbtype);
$connectiontest = $con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
if (!$connectiontest) {
    // Oops!  We do not have a valid database connection
    // Now instruct the user in how to fix this problem
    $problem = _("The system cannot connect to the database.")
             . '&nbsp;'
             . _("Have the administrator check the database parameters in vars.php to make sure they are correct.")
             . _("Also make sure the database is running and can accept a connection from this server.")
             . '<BR><BR>';
    $title = _("Test Results");

        echo <<<EOQ
        <!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01 Transitional//EN'>
        <html>
        <head>
        <title>$title</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        </head>
        <body>
        $problem<BR>
        </body>
        </html>
EOQ;
    exit;
}
// $con->debug = 1;

$ldapok = true;
if ($xrms_use_ldap) {
     //if we use ldap, we check the password there first, and we do not in check it in db
     $ldapok = false;
     ini_set ('error_reporting', 0);
     $ds=ldap_connect($xrms_ldap["server"]);  // connect to the LDAP server!
     if ($ds) {
          $ldap_version = get_system_parameter($con, "LDAP Version");
	      ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, $ldap_version);

          $r=ldap_bind($ds, $xrms_ldap["search_user"], $xrms_ldap["search_pw"]);  //authenticate as the search user specified
          //specify which attributes to fetch from ldap
          $justthese = array ("dn","uid","givenName","sn","mail");
        // Search for the user name
        $sr=ldap_search($ds,$xrms_ldap["search_context"], $xrms_ldap["search_attribute"]."=".$username, $justthese);
          $info = ldap_get_entries($ds, $sr);

          if ($info[0]["dn"] != "") {  //If we found a user (we assume that usernames are unique)
               $r=ldap_bind($ds, $info[0]["dn"], $password);
               //Try to authenticate using the password provided
               if ($r) {
                  $ldapok = true;  //Password check was successfull
               }
          }
          ldap_close($ds);
     }
     ini_restore ('error_reporting');

     if ($ldapok) {
          // we have been able to authenticate against ldap,
          // now lets retreive the user info from the database
          $sql = "select * from users where username = " . $con->qstr($username, get_magic_quotes_gpc());
          $rst = $con->execute($sql);

          //we check the user_record_status separately in order to allow ldap provisioning which
          //should not occur if the user exists but is not marked active...
          if ($rst && !$rst->EOF) {
               if ($rst->fields['user_record_status'] != "a") {
                    //the user is present but is not marked active, authentication failed
                    $ldapok = false;
               }
          } else {
               // if the user does not exist in the database but we were able to authenticate him, we create it automatically in the database
               $rec = array();
               $rec['role_id'] = $xrms_ldap['default_role_id'];
               $rec['last_name'] = $info[0]['sn'][0];
               $rec['first_names'] = $info[0]['givenName'][0];
               $rec['username'] = $info[0]['uid'][0];
               $rec['password'] = 'NOPASSWORD';
               $rec['email'] = $info[0]['mail'][0];
               $rec['gmt_offset'] = $xrms_ldap['default_gmt_offset'];
               $rec['language'] = 'english';

               $tbl = 'users';
               $ins = $con->GetInsertSQL($tbl, $rec, get_magic_quotes_gpc());
               $rst = $con->execute($ins);
               if (!$rst) {
                  db_error_handler ($con,$sql);
               }
               // and now pull the data we just inserted
               $sql = "select * from users where username = " . $con->qstr($username, get_magic_quotes_gpc());
               $rst = $con->execute($sql);

               if (!$rst) {
                  db_error_handler ($con,$sql);
               }
          }
     }

} else {
     //We are using db to check the password
     $password = md5($password);
     $sql = "select * from users where username = " . $con->qstr($username, get_magic_quotes_gpc()) . " AND password = " . $con->qstr($password, get_magic_quotes_gpc()) . " AND user_record_status = 'a'";
     $rst = $con->execute($sql);
     if (!$rst) {
         db_error_handler ($con,$sql);
     }
}


if ($rst && !$rst->EOF && $ldapok) {

    // get variables
    $session_user_id = $rst->fields['user_id'];
    $role_id         = $rst->fields['role_id'];
    $username        = $rst->fields['username'];
    $language        = $rst->fields['language'];
    $gmt_offset      = $rst->fields['gmt_offset'];

    // close result set
    $rst->close();

    // get role_short_name from table 'roles'
    $sql = "select r.role_short_name as role
        from roles r, users u
        where u.role_id=r.role_id
        and u.user_id = $session_user_id";
    $role_short_name = '';
    $rst = $con->execute($sql);
    if ($rst) {
      while (!$rst->EOF) {
         $role_short_name = $rst->fields['role'];
         break;
      }
      $rst->close();
    } else {
      db_error_handler($con,$sql);
    }

    // make sure we have a session, and place variables in it
    session_startup();
    $_SESSION['session_user_id'] = $session_user_id;
    $_SESSION['xrms_system_id']  = $xrms_system_id;
    $_SESSION['role_id']         = $role_id;
    $_SESSION['username']        = $username;
    $_SESSION['language']        = $language;
    $_SESSION['gmt_offset']      = $gmt_offset;
    $_SESSION['role_short_name'] = $role_short_name;

    // audit
    add_audit_item($con, $session_user_id, 'login', '', '', 2);

    // redirect
    header("Location: $target");
} else {
    header("Location: $http_site_root/login.php?msg=noauth");
}

/**
 * $Log: login-2.php,v $
 * Revision 1.20  2005/01/11 17:08:38  maulani
 * - Added parameter for LDAP Version.  Some LDAP Version 3 installations
 *   require this option to be set.  Initial parameter setting is version 2
 *   since most current installations probably use v2.
 *
 * Revision 1.19  2005/01/09 15:50:29  braverock
 * - add db_error_handler on all queries
 *
 * Revision 1.18  2004/07/22 17:22:04  introspectshun
 * - Made connectiontest doc title a GetText string
 * - Corrected LDAP authentication to use $xrms_ldap['search_pw'] instead of
 *   $xrms_ldap['search_password']
 *
 * Revision 1.17  2004/07/22 11:12:55  maulani
 * - Change default characterset to UTF-8
 *
 * Revision 1.16  2004/07/20 10:43:16  cpsource
 * - Moved SESSION['role'] to SESSION['role_short_name']
 *   role is now set in login-2.php instead of admin/routing.php
 *   utils-misc.php updated to check session with role_short_name
 *
 * Revision 1.15  2004/07/19 22:26:59  braverock
 * - localize $problem message for i18n
 *
 * Revision 1.14  2004/07/19 21:59:42  maulani
 * - Remove errant cut and paste code and assumption of vars.php location
 *
 * Revision 1.13  2004/07/19 21:55:53  maulani
 * - Add test that we are able to connect to the database.  Fail with
 *   an appropriate error message if the database is not available
 *
 * Revision 1.12  2004/07/14 15:00:10  braverock
 * - changed session_start to new session_startup fn
 *   - without this change, new login was impossible
 *
 * Revision 1.11  2004/07/14 12:14:43  cpsource
 * - Remove extraneous session_start()
 *
 * Revision 1.10  2004/07/13 14:51:07  braverock
 * - change user_type_id to role_id
 *
 * Revision 1.9  2004/07/07 22:51:03  introspectshun
 * - Now passes a table name instead of a recordset into GetInsertSQL
 *
 * Revision 1.8  2004/06/11 20:46:04  introspectshun
 * - Now use ADODB GetInsertSQL function.
 *
 * Revision 1.7  2004/05/07 21:30:39  maulani
 * - Add audit-level to allow different levels of audit-logging
 *
 * Revision 1.6  2004/05/07 17:22:56  maulani
 * - Correct login audit entry so it leaves id blank instead of entering 0
 *
 * Revision 1.5  2004/04/21 14:02:02  braverock
 * - apply LDAP change password patches submitted by cduffy
 *
 * Revision 1.4  2004/03/26 21:44:08  maulani
 * - Add login to audit trail
 * - Add phpdoc
 */
?>
