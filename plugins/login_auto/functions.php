<?php

   /*
    *  Login:Auto Plugin
    *  By Brendon Baumgartner <brendon@brendon.com>
    *  Orinally programmed for Squirrelmail By Jay Guerette 
    *  (c) 2005 (GNU GPL - see ../../COPYING)
    *
    *  If you need help with this, or see improvements that can be made, please
    *  email the XRMS Plugins mailing list or try contacting me at
    *  the address above. I definately welcome suggestions and comments.
    *  This plugin, is not directly supported by the developers.
    *
    *  View the INSTALL document for information on installing this.  Also view
    *  the README document and plugins/README.plugins for more information.
    *
    */


   // called in login.php
   // hook: login_cookie
   function login_auto_get_user_do() {

      //global $auto_user, $auto_pass, $auto_key, $base_uri, $user, $loginname;
      global $auto_user, $auto_pass, $auto_key, $user, $username, $http_site_root;
      global $target;

      include_once ('plugins/login_auto/config.php');
      
      $base_uri = get_base_uri($http_site_root);

      if (isset($_COOKIE['user'])) {
         $user = $_COOKIE['user'];
      }
      
      if (isset($_COOKIE['pass'])) {
         $pass = $_COOKIE['pass'];
      }


      if (!$auto_pass) setcookie('pass', '', time()-3600, $base_uri);
      if (!$auto_user) setcookie('user', '', time()-3600, $base_uri);
      if ($auto_user && (isset($user) && $user)) {
         //$loginname=MD5Decrypt(base64_decode($user),$auto_key);
         $username=MD5Decrypt(base64_decode($user),$auto_key);
         //compatibility_sqsession_register($loginname, 'loginname');
         //sqsession_register($username, 'username');
         
         if ($auto_pass && (isset($pass) && $pass)) {
            //echo "user cookie set to $user<br>username: $username<br>pass cookie set to: $pass";
            header("Location: login-2.php?username=$username&target=$target");
         }
      } 
   }


   // called in login.php
   function login_auto_set_login_do() {

      global $auto_pass, $auto_user, $user, $login_doc, $login_link;

      // note that we get $user as well as all config 
      // variables from the hook before this


      $cb_auto_user = (isset($_POST['cb_auto_user'])?$_POST['cb_auto_user']:'');
      $cb_auto_pass = (isset($_POST['cb_auto_pass'])?$_POST['cb_auto_pass']:'');

      echo "<center><table border=0>\n";

      if ($auto_pass) {
         echo "<tr><td valign=center>\n";
         echo "<input type=checkbox name=cb_auto_pass value=true>\n";
         echo "</td><td valign=center nowrap>\n";
         echo "<small>" . _("Remember my Name & Password") . "<small>\n";
         echo "</td></tr>\n";
      }
      else {
         if ($auto_user && !isset($user)) {
            echo "<tr><td valign=center>\n";
            echo "<input type=checkbox name=cb_auto_user value=true>\n";
            echo "</td><td valign=center nowrap>\n";
            echo "<small>" . _("Remember my Name") . "</small>\n";
            echo "</td></tr>\n";
         }
      }

      if (($auto_user || $auto_pass) && $login_doc!='') {
         if (!isset($login_link) || $login_link=='') 
            $login_link=_("What's this?");
         echo "<tr><td colspan='2' valign=center align=center><a href=$login_doc>$login_link</a></td></tr>\n";
      }

      echo "</table></center>\n";

   }


   // called in login-2.php
   // using login_before hook
   function login_auto_get_pass_do() {
      //global $auto_pass, $auto_key, $secretkey, $just_logged_in, 
             //$user, $pass, $login_username;
      global $auto_pass, $auto_key, $password, $just_logged_in, 
             $user, $pass, $username;

         include_once ('plugins/login_auto/config.php');

      if (isset($_COOKIE['user'])) {
         $user = $_COOKIE['user'];
      }

      if (isset($_COOKIE['pass'])) {
         $pass = $_COOKIE['pass'];
      }

//      if (isset($_GET['login_username'])) {
//         $login_username = $_GET['login_username'];
//      }

      if ($auto_pass && isset($pass) && isset($user)) {
         //$login_username = MD5Decrypt(base64_decode($user),$auto_key);
         $username = MD5Decrypt(base64_decode($user),$auto_key);
         //$secretkey = MD5Decrypt(base64_decode($pass),$auto_key);
         $password = MD5Decrypt(base64_decode($pass),$auto_key);
        //echo "username: $username<br>passw is $password";
         $just_logged_in=1;
      }

      //login_auto_clear_cookies();

   }


   // called in login-2.php
   function login_auto_set_cookies_do() {

      //global $auto_user, $auto_pass, $auto_key, $user, $login_username, 
      global $auto_user, $auto_pass, $auto_key, $user, $username, 
             $pass, $auto_expire_days, $auto_expire_hours, $auto_expire_minutes,  
             //$base_uri, $secretkey;
             $password, $http_site_root;
        
      $base_uri = get_base_uri($http_site_root);
        
      // note that we get $user and $pass as well as all config
      // variables from the hook before this

      $auto_expire = $auto_expire_days*86400 + $auto_expire_hours*3600 
                   + $auto_expire_minutes*60;

      $cb_auto_user = (isset($_POST['cb_auto_user'])?$_POST['cb_auto_user']:'');
      $cb_auto_pass = (isset($_POST['cb_auto_pass'])?$_POST['cb_auto_pass']:'');

      if ($auto_user && !$auto_pass && !isset($cb_auto_user)) return;

      if ((isset($cb_auto_user) && $cb_auto_user) || (isset($cb_auto_pass) && $cb_auto_pass) ||
         ($auto_user && ($user || $login_username)) || ($auto_pass && $pass)) {
         //setcookie('user', base64_encode(MD5Encrypt($login_username, $auto_key)), time()+$auto_expire, $base_uri);
         setcookie('user', base64_encode(MD5Encrypt($username, $auto_key)), time()+$auto_expire, $base_uri);
      }
      if ((isset($cb_auto_pass) && $cb_auto_pass) || ($auto_pass && $pass && $user)) {
         //setcookie('pass', base64_encode(MD5Encrypt($secretkey, $auto_key)), time()+$auto_expire, $base_uri);
         setcookie('pass', base64_encode(MD5Encrypt($password, $auto_key)), time()+$auto_expire, $base_uri);
      }

   }


   // called in signout.php
   function login_auto_clear_cookies_do() {

      global $http_site_root;

      $base_uri = get_base_uri($http_site_root);

      setcookie('user', '', time()-3600, $base_uri);
      setcookie('pass', '', time()-3600, $base_uri);

   }


   /*
    * The algorithm is a double XOR. MD5Encrypt XORs the plaintext with a random number.
    * The number is interleaved with the XOR output so it can retreived for decryption.
    * MD5Keycrypt XORs this string with your encryption key. Techincally, MD5 is not used
    * for the actual encryption, just to stengthen it.
    *
    */

   function MD5Keycrypt($txt,$key) 
   { 
    for ($i=0,$j=0,$val='',$key=md5($key),$keylen=strlen($key),$txtlen=strlen($txt);$i<$txtlen;$i++) {
        $val.=substr($txt,$i,1)^substr($key,($j==$keylen)?$j*=0:$j++,1);
    } 
    return $val; 
   } 

   function MD5Encrypt($txt,$key) 
   { 
    if (is_array($key)) {
        for ($i=0,$alen=sizeof($key);$i<$alen;$i++) {
            $txt=($i%2)?MD5Encrypt($txt,$key[$i]):MD5Keycrypt($txt,$key[$i]);
        }
        return $txt;
    }
    srand((double)microtime()*1000000); 
    $cryptkey=md5(rand(0,32000));
    for ($i=0,$j=0,$val='',$keylen=strlen($cryptkey),$txtlen=strlen($txt);$i<$txtlen;$i++) {
        $val.=substr($cryptkey,$j,1).(substr($txt,$i,1)^substr($cryptkey,($j==$keylen)?$j*=0:$j++,1));
    } 
    return MD5Keycrypt($val,$key); 
   } 

   function MD5Decrypt($txt,$key) 
   { 
    if (is_array($key)) {
        for ($i=sizeof($key)-1;$i>=0;$i--) {
            $txt=($i%2)?MD5Decrypt($txt,$key[$i]):MD5Keycrypt($txt,$key[$i]);
        }
        return $txt;
    }
    for ($i=0,$val='',$txt=MD5Keycrypt($txt,$key),$txtlen=strlen($txt);$i<$txtlen;$i++) {
        $val.=(substr($txt,$i+1,1)^substr($txt,$i++,1));
    } 
    return $val; 
   } 



   function compatibility_sqsession_register ($var, $name) {

       if (compatibility_check_sm_version(1, 2, 11))
       {  
          sqsession_register ($var, $name);
          return;
       }

       compatibility_sqsession_is_active();
       compatibility_sqsession_unregister($name);

       if ( !compatibility_check_php_version(4,1) ) {
           global $HTTP_SESSION_VARS;
           $HTTP_SESSION_VARS["$name"] = $var;
       }
       else {
          $_SESSION["$name"] = $var;
       }   
           session_register("$name");
   }


// example call to this:
//sqsession_register($username, 'username');
function sqsession_register ($var, $name) {

    sqsession_is_active();

        $_SESSION["$name"] = $var;
    session_register("$name");
}
function sqsession_is_active() {

    $sessid = session_id();
    if ( empty( $sessid ) ) {
        session_start();
    }
}

function get_base_uri($s) {
   $t = preg_replace('%^http://[a-zA-Z\.\-]+%i', '', $s);
   if (substr($t, -1, 1) != "/") {
      $t = $t . "/";
   }
   return $t;
}
?>
