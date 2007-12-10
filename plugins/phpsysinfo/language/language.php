<?php
/* $Id: language.php,v 1.1 2007/12/10 18:23:17 gpowers Exp $
 * 
 * Copyright (c) 2006, 2007 by phpSysInfo
 * http://phpsysinfo.sourceforge.net/
 * 
 * This program is free software; you can redistribute it
 * and/or modify it under the terms of the
 * GNU General Public License version 2 (GPLv2)
 * as published by the Free Software Foundation.
 * See COPYING for details.
 *
 */

// Set the correct content-type header.
header("Content-Type: text/xml\n\n");


// Determine application root, and include the config file if it exists.
define('APP_ROOT', realpath(dirname(__FILE__).'/../'));
if(file_exists(APP_ROOT . '/config.php')) {
  include(APP_ROOT . '/config.php');
}
// Set the default language if the language isn't properly configured
if(!defined('lang')) {
  define('lang', 'en');
}
if(isset($_GET['lang'])) {
  if(file_exists(APP_ROOT . '/language/' . $_GET['lang'] . '.xml')) {    
    $lang = $_GET['lang'];
  } else {
    $lang = lang;
  }
} else {
  $lang = lang;
}

if(file_exists(APP_ROOT . '/language/' . $lang . '.xml')) {  
  echo file_get_contents(APP_ROOT . '/language/' . $lang . '.xml');
} else {  
  echo file_get_contents(APP_ROOT . '/language/en.xml');
}