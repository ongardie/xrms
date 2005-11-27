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

   function xrms_plugin_init_login_auto() {

      global $xrms_plugin_hooks;
      
      $xrms_plugin_hooks['login_cookie']['login_auto'] = 'login_auto_get_user';
      $xrms_plugin_hooks['login_form']['login_auto'] = 'login_auto_set_login';
      $xrms_plugin_hooks['login_before']['login_auto'] = 'login_auto_get_pass';
      $xrms_plugin_hooks['login_verified']['login_auto'] = 'login_auto_set_cookies';
      $xrms_plugin_hooks['logout']['login_auto'] = 'login_auto_clear_cookies';
   }

   
   function login_auto_get_user() {
      include_once('plugins/login_auto/functions.php');
      login_auto_get_user_do();
   }

   function login_auto_set_login() {
      include_once('plugins/login_auto/functions.php');
      login_auto_set_login_do();
   }


   function login_auto_get_pass() {
      include_once('plugins/login_auto/functions.php');
      login_auto_get_pass_do();
   }

   function login_auto_set_cookies() {
      include_once('plugins/login_auto/functions.php');
      login_auto_set_cookies_do();
   }

   function login_auto_clear_cookies() {
      include_once('plugins/login_auto/functions.php');
      login_auto_clear_cookies_do();
   }

   function login_auto_version() {
      return '0.1';
   }


?>
