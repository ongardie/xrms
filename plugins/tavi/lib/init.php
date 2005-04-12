<?php
// $Id: init.php,v 1.1 2005/04/12 20:45:12 gpowers Exp $

// General initialization code.

require('lib/defaults.php');
require('config.php');
require('lib/url.php');
require('lib/pagestore.php');
require('lib/rate.php');

$PgTbl = $DBTablePrefix . 'pages';
$IwTbl = $DBTablePrefix . 'interwiki';
$SwTbl = $DBTablePrefix . 'sisterwiki';
$LkTbl = $DBTablePrefix . 'links';
$RtTbl = $DBTablePrefix . 'rate';
$RemTbl = $DBTablePrefix . 'remote_pages';

$FlgChr = chr(255);                     // Flag character for parse engine.

$pagestore = new PageStore();
$db = $pagestore->dbh;

// $HeadingOffset determines which offset all headings begin with.
// This is used when transcluding files combined with changing heading levels
$HeadingOffset = 0;

$Entity = array();                      // Global parser entity list.

$RefList = array(); // Array of referenced links, see view_macro_reflist
// Strip slashes from incoming variables.

if(get_magic_quotes_gpc())
{
  $document = stripslashes($document);
  $categories = stripslashes($categories);
  $comment = stripslashes($comment);
  $page = stripslashes($page);
}

// Read user preferences from cookie.

$prefstr = isset($HTTP_COOKIE_VARS[$CookieName])
           ? $HTTP_COOKIE_VARS[$CookieName] : '';

           // Define setConst to define language constants
function setConst($name, $value) {
  if (!defined($name)) {
    define($name, $value);
  }
}

// Choose a textual language for this wiki
if (defined('LANGUAGE_CODE')) {
  require('lang/lang_'. LANGUAGE_CODE . '.php');
}
// Due to definition of setConst, this will add those not defined yet
require('lang/default.php');

if(!empty($prefstr))
{
  if(ereg("rows=([[:digit:]]+)", $prefstr, $result))
    { $EditRows = $result[1]; }
  if(ereg("cols=([[:digit:]]+)", $prefstr, $result))
    { $EditCols = $result[1]; }
  if(ereg("user=([^&]*)", $prefstr, $result))
    { $UserName = urldecode($result[1]); }
  if(ereg("days=([[:digit:]]+)", $prefstr, $result))
    { $DayLimit = $result[1]; }
  if(ereg("min=([[:digit:]]+)", $prefstr, $result))
    { $MinEntries = $result[1]; }
  if(ereg("hist=([[:digit:]]+)", $prefstr, $result))
    { $HistMax = $result[1]; }
  if(ereg("tzoff=(-?[[:digit:]]+)", $prefstr, $result))
    { $TimeZoneOff = $result[1]; }
}

if($Charset != '')
  { header("Content-Type: text/html; charset=$Charset"); }

?>
