<?php
/* $Id: xml.php,v 1.1 2007/12/10 18:23:20 gpowers Exp $
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

define('APP_ROOT', dirname(__FILE__));
define('IN_PHPSYSINFO', true);
error_reporting(E_ALL);

require_once(APP_ROOT . '/includes/common_functions.php');  // Set of common functions used through out the app

/**
 * Check for the SimpleXML fuction. We need this for almost everything.
 * Even our error class needs this to output the errors.
 * Because of that this check uses a custom error function that will
 * return a hard coded XML file (with headers).
 */
checkForSimpleXml();

require_once(APP_ROOT . '/includes/class.Error.inc.php');
$error = Error::singleton();

// Figure out which OS where running on, and detect support
if ( file_exists( APP_ROOT . '/includes/os/class.' . PHP_OS . '.inc.php' ) ) {  
  require_once( APP_ROOT . '/includes/os/class.' . PHP_OS . '.inc.php' );
} else {
  $error->addError('include(class.' . PHP_OS . '.php.inc)' , PHP_OS . ' is not currently supported' );
}
if (!extension_loaded('pcre')) {
  $error->addError('extension_loaded(pcre)', 'phpsysinfo requires the pcre module for php to work' );
} 

if (!file_exists(APP_ROOT . '/config.php')) {
  $error->addError('file_exists(config.php)', 'config.php does not exist in the phpsysinfo directory.' );
} else { 
  require_once(APP_ROOT . '/config.php');       // get the config file
}
if (sensorProgram !== false) {  
  $sensor_program = basename(sensorProgram);
  if(!file_exists( APP_ROOT . '/includes/mb/class.' . sensorProgram . '.inc.php' )) {
    define('PSI_MBINFO', false);
    $error->addError('include(class.' . htmlspecialchars(sensorProgram, ENT_QUOTES) . '.inc.php)', 'specified sensor program is not supported' );
  } else {
    require_once(APP_ROOT . '/includes/mb/class.' . sensorProgram . '.inc.php');
    define('PSI_MBINFO', true);
  }
} else {
  define('PSI_MBINFO', false);
}

if(hddTemp !== false) {
  if (hddTemp != "tcp" && hddTemp != "suid" ) {
    $error->addError('include(class.hddtemp.inc.php)', 'bad configuration in config.php for $hddtemp_avail' );
    define('PSI_HDDTEMP', false);
  } else {
    require_once(APP_ROOT . '/includes/mb/class.hddtemp.inc.php');
    define('PSI_HDDTEMP', true);
  }
} else {
  define('PSI_HDDTEMP', false);
}
if( $error->ErrorsExist() ) {
  header("Content-Type: text/xml\n\n");
  echo $error->ErrorsAsXML();
  exit;
}

// Create the XML file
require_once(APP_ROOT . '/includes/xml.class.php');

$xml = new xml();
$xml->buildXml();
$xml->printXml();

