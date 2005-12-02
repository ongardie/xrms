<?php
// $Id: main.php,v 1.2 2005/12/02 19:40:00 daturaarutad Exp $


global $include_directory;
require_once('../../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');
require_once($include_directory . 'confgoto.php');




// Harvest script parameters and other variables.  We do this even if
// register_globals=on; this way, we force the variables to be defined.
// (Which is better form in case the admin has warnings cranked all the
// way up, like using the next line... )
//   error_reporting(E_ALL);

$HTTP_REFERER = isset($HTTP_SERVER_VARS['HTTP_REFERER'])
                ? $HTTP_SERVER_VARS['HTTP_REFERER'] : '';
$QUERY_STRING = isset($HTTP_SERVER_VARS['QUERY_STRING'])
                ? $HTTP_SERVER_VARS['QUERY_STRING'] : '';
$REMOTE_ADDR  = isset($HTTP_SERVER_VARS['REMOTE_ADDR'])
                ? $HTTP_SERVER_VARS['REMOTE_ADDR'] : '';

$action       = isset($HTTP_GET_VARS['action'])
                ? $HTTP_GET_VARS['action'] : '';
$page         = isset($HTTP_GET_VARS['page'])
                ? $HTTP_GET_VARS['page'] : '';
$ver1         = isset($HTTP_GET_VARS['ver1'])
                ? $HTTP_GET_VARS['ver1'] : '';
$ver2         = isset($HTTP_GET_VARS['ver2'])
                ? $HTTP_GET_VARS['ver2'] : '';
$find         = isset($HTTP_GET_VARS['find'])
                ? $HTTP_GET_VARS['find'] : '';
$style         = isset($HTTP_GET_VARS['style'])
                ? $HTTP_GET_VARS['style'] : '';
$version      = isset($HTTP_GET_VARS['version'])
                ? $HTTP_GET_VARS['version'] : '';
$full         = isset($HTTP_GET_VARS['full'])
                ? $HTTP_GET_VARS['full'] : '';
$min          = isset($HTTP_GET_VARS['min'])
                ? $HTTP_GET_VARS['min'] : '';
$days         = isset($HTTP_GET_VARS['days'])
                ? $HTTP_GET_VARS['days'] : '';

$Preview      = isset($HTTP_POST_VARS['Preview'])
                ? $HTTP_POST_VARS['Preview'] : '';
$Save         = isset($HTTP_POST_VARS['Save'])
                ? $HTTP_POST_VARS['Save'] : '';
$archive      = isset($HTTP_POST_VARS['archive'])
                ? $HTTP_POST_VARS['archive'] : '';
$auth         = isset($HTTP_POST_VARS['auth'])
                ? $HTTP_POST_VARS['auth'] : '';
$categories   = isset($HTTP_POST_VARS['categories'])
                ? $HTTP_POST_VARS['categories'] : '';
$cols         = isset($HTTP_POST_VARS['cols'])
                ? $HTTP_POST_VARS['cols'] : '';
$comment      = isset($HTTP_POST_VARS['comment'])
                ? $HTTP_POST_VARS['comment'] : '';
$posted_code  = isset($HTTP_POST_VARS['posted_code'])
                ? $HTTP_POST_VARS['posted_code'] : '';
$days         = isset($HTTP_POST_VARS['days'])
                ? $HTTP_POST_VARS['days'] : $days;
$discard      = isset($HTTP_POST_VARS['discard'])
                ? $HTTP_POST_VARS['discard'] : '';
$document     = isset($HTTP_POST_VARS['document'])
                ? $HTTP_POST_VARS['document'] : '';
$hist         = isset($HTTP_POST_VARS['hist'])
                ? $HTTP_POST_VARS['hist'] : '';
$min          = isset($HTTP_POST_VARS['min'])
                ? $HTTP_POST_VARS['min'] : $min;
$nextver      = isset($HTTP_POST_VARS['nextver'])
                ? $HTTP_POST_VARS['nextver'] : '';
$rows         = isset($HTTP_POST_VARS['rows'])
                ? $HTTP_POST_VARS['rows'] : '';
$tzoff        = isset($HTTP_POST_VARS['tzoff'])
                ? $HTTP_POST_VARS['tzoff'] : '';
$user         = isset($HTTP_POST_VARS['user'])
                ? $HTTP_POST_VARS['user'] : '';
$referrer     = isset($HTTP_POST_VARS['referrer'])
                ? $HTTP_POST_VARS['referrer'] : '';

require('lib/init.php');
require('parse/transforms.php');

// To add an action=x behavior, add an entry to this array.  First column
//   is the file to load, second is the function to call, and third is how
//   to treat it for rate-checking purposes ('view', 'edit', or 'search').
$ActionList = array(
                'view' => array('action/view.php', 'action_view', 'view'),
                'edit' => array('action/edit.php', 'action_edit', 'view'),
                'save' => array('action/save.php', 'action_save', 'edit'),
                'diff' => array('action/diff.php', 'action_diff', 'search'),
                'find' => array('action/find.php', 'action_find', 'search'),
                'latex'   => array('action/latex.php', 'action_latex', 'view'),
                'history' => array('action/history.php', 'action_history',
                                   'search'),
                'prefs'   => array('action/prefs.php', 'action_prefs', 'view'),
                'macro'   => array('action/macro.php', 'action_macro', 'search'),
                'rss'     => array('action/rss.php', 'action_rss', 'view'),
                'style'   => array('action/style.php', 'action_style', ''),
                'delete'  => array('action/delete.php', 'action_delete', '')
              );

// Default action and page names.
if(!isset($page) && !isset($action))
  { $page = $QUERY_STRING; }
if(empty($action))
  { $action = 'view'; }
if(!isset($page) or $page=="")
  { $page = $HomePage; }

// Confirm we have a valid page name.
if(!validate_page($page))
  { die(LIB_ErrorInvalidPage); }

// Don't let people do too many things too quickly.
if($ActionList[$action][2] != '')
  { rateCheck($pagestore->dbh, $ActionList[$action][2]); }

// Dispatch the appropriate action.
if(!empty($ActionList[$action]))
{    
  include($ActionList[$action][0]); 
  $ActionList[$action][1]();
}

// Expire old versions, etc.
$pagestore->maintain();
?>
