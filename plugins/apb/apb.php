<?

// include the common files
require_once('../../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');

$session_user_id = session_check();
$user_id = $session_user_id;

//####################################################################
// Active PHP Bookmarks - lbstone.com/apb/
//
// Filename:    apb.php
// Authors:     L. Brandon Stone (lbstone.com)
//              Nathanial P. Hendler (retards.org)
//
// 2003-03-11   Added security check. [LBS]
// 2002-02-07   Rearranged the order of things, added some
//              additional comments. [LBS]
// 2001-09-04   Starting on version 1.0 [NPH] [LBS]
//
//####################################################################

//////////////////////////////////////////////////////////////////////
// Security check.
//////////////////////////////////////////////////////////////////////

//if ($HTTP_COOKIE_VARS["DOCUMENT_ROOT"] ||
//    $HTTP_POST_VARS["DOCUMENT_ROOT"] ||
//    $HTTP_GET_VARS["DOCUMENT_ROOT"])
//{ exit(); }

//////////////////////////////////////////////////////////////////////
// Database configuration.
//////////////////////////////////////////////////////////////////////

// Change these vars so that you can connect to your database.
global $xrms_db_dbtype;
global $xrms_db_server;
global $xrms_db_username;
global $xrms_db_password;
global $xrms_db_dbname;

$APB_SETTINGS['apb_host']     = $xrms_db_server;
$APB_SETTINGS['apb_database'] = $xrms_db_dbname;
$APB_SETTINGS['apb_username'] = $xrms_db_username;
$APB_SETTINGS['apb_password'] = $xrms_db_password;

// If you would like to set your database variables from an external
// file, link to that from here.
//include("/var/www/apb_db_config.php");

//////////////////////////////////////////////////////////////////////
// Paths and URLs.
//////////////////////////////////////////////////////////////////////

// Change the apb_dir_name, if you want the program to run somewhere other
// than the "bookmarks", directory.  By default this value is 'bookmarks/'.
// If you want to run APB from 'http://www.yoursite.com/bm/', just change
// this value to 'bm/'.
$APB_SETTINGS['apb_dir_name'] = 'xrms/plugins/apb/';
$DOCUMENT_ROOT = '/var/www/html';

// There is usually no reason to change these.
global $http_site_root;
//$APB_SETTINGS['apb_url']   = $http_site_root . $APB_SETTINGS['apb_dir_name'];
$APB_SETTINGS['apb_url']   = $http_site_root . "/plugins/apb/";
$APB_SETTINGS['home_url']  = $APB_SETTINGS['apb_url'];
$APB_SETTINGS['apb_path']  = $DOCUMENT_ROOT . '/' . $APB_SETTINGS['apb_dir_name'];
$APB_SETTINGS['log_path']  = $APB_SETTINGS['apb_path'] . 'apb.log';
$APB_SETTINGS['view_group_path'] = $APB_SETTINGS['apb_url'] . "view_group.php";
$APB_SETTINGS['daily_browsing_public'] = 0;

//////////////////////////////////////////////////////////////////////
// Global settings.
//////////////////////////////////////////////////////////////////////

// Change these at your own risk.  (These will be documented after
// they've been fully tested.)
$APB_SETTINGS['template']  = 'default';
$APB_SETTINGS['limit']     = 5;
$APB_SETTINGS['debug']     = 0;

//////////////////////////////////////////////////////////////////////
// Load the program libraries.
//////////////////////////////////////////////////////////////////////

include_once($APB_SETTINGS['apb_path']."apb_common.php");

?>
