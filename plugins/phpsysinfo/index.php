<?php
/* $Id: index.php,v 1.1 2007/12/10 18:23:19 gpowers Exp $
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
 
if (PHP_VERSION < 5.2) {
  die("PHP 5.2 or greater is required!!!");
} 
require_once('./includes/class.Error.inc.php');
require_once('./includes/common_functions.php');
$error = Error::singleton();

if (!file_exists('./config.php')) {
  $error->addError('file_exists(config.php)', 'config.php does not exist in the phpsysinfo directory.' );
} else { 
  require_once('./config.php'); // get the config file
}
if( $error->ErrorsExist() ) {  
  echo $error->ErrorsAsHTML();
  exit;
}

$template = template;

if(isset($_GET['template']) && $_GET['template'] != "") {
  if(file_exists('templates/'.$_GET['template'].'.css')) {
    $template = $_GET['template'].'.css';
  } 
}

if(!file_exists('templates/'.$template)) {  
  $template = 'phpsysinfo.css';
}

//TODO: Get the default language first.
//TODO: Create a language picker. Preferrably using GET.

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<META http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link rel="stylesheet" href="templates/<?php echo $template;?>" type="text/css">
<script type="text/JavaScript" src="jquery.js"></script>
<script type="text/JavaScript" src="jquery.tablesorter.pack.js"></script>
<script type="text/JavaScript" src="phpsysinfo.js"></script>

<title>Loading... please wait!</title>
</head>
<body>

<div id="loader">
  <h1>Loading... please wait!</h1>
</div>

<div id="errors" style="display: none;">
   <h1>Huston, we got a problem.</h1>
   <form id="select"><p></p></form>
   <div id="errorlist">
   <h2>Oh, I'm sorry. Something seems to be wrong. </h2>
   
   </div>
</div>

<div id="container" style="display: none;">  
  <h1 id="title"></h1>
  <form name="theme" id="select" action="index.php" method="GET">
  <span lang='044'>T</span>
  <select name="template" onChange="document.theme.submit();">
  <?php

  $dirlist = gdc('./templates/');
    
  foreach($dirlist as $file) {  
    $tpl_ext = substr($file,strlen($file)-4);
    $tpl_name = substr($file,0,strlen($file)-4);
  
    if($tpl_ext == ".css") {
      if($tpl_name.".css" == $template) {
        echo '<option name="' . $tpl_name . '" selected="selected">' . $tpl_name . '</option>';
      } else {
        echo '<option name="' . $tpl_name . '">' . $tpl_name . '</option>';
      }
    }
  }
  
  ?>
  </select>
  </form>

  <div id="vitals">
    <h2 lang="002">System vitals</h2>
    <table class="stripeMe" id="vitalsTable" cellspacing="0"></table>
  </div>
   
  <div id="hardware">
    <h2 lang='010'>Hardware Information</h2>
    <table class="stripeMe" id="cpuTable" cellspacing="0"></table>
    <h3 style="cursor: pointer" id="sPci"><img src="gfx/bullet_toggle_plus.png" /> <span lang='017'>PCI devices</span></h3>
    <h3 style="cursor: pointer; display: none;" id="hPci"><img src="gfx/bullet_toggle_minus.png" /> <span lang='017'>PCI devices</span></h3>
    <table id="pciTable" cellspacing="0" style="display: none;"></table>  
    <h3 class="odd" style="cursor: pointer" id="sIde"><img src="gfx/bullet_toggle_plus.png"> <span lang='018'>IDE devices</span></h3>
    <h3 class="odd" style="cursor: pointer; display: none;" id="hIde"><img src="gfx/bullet_toggle_minus.png"> <span lang='018'>IDE devices</span></h3>
    <table class="odd" id="ideTable" cellspacing="0" style="display: none;"></table>  
    <h3 style="cursor: pointer" id="sScsi"><img src="gfx/bullet_toggle_plus.png"> <span lang='019'>SCSI devices</span></h3>
    <h3 style="cursor: pointer; display: none;" id="hScsi"><img src="gfx/bullet_toggle_minus.png"> <span lang='019'>SCSI devices</span></h3>
    <table id="scsiTable" cellspacing="0" style="display: none;"></table>  
    <h3 class="odd"style="cursor: pointer" id="sUsb"><img src="gfx/bullet_toggle_plus.png"> <span lang='020'>USB devices</span></h3>
    <h3 class="odd"style="cursor: pointer; display: none;" id="hUsb"><img src="gfx/bullet_toggle_minus.png"> <span lang='020'>USB devices</span></h3>
    <table class="odd" id="usbTable" cellspacing="0" style="display: none;"></table>   
  </div>

   <div id="memory">
    <h2 lang='027'>Memory Usage</h2>
    <table class="stripeMe" id="memoryTable" cellspacing="0"></table>
  </div>
  
  <div id="filesystem">
    <h2 lang='030'>Mounted Filesystems</h2>
    <table class="stripeMe" id="filesystemTable" cellspacing="0"></table>
  </div> 
  
  <div id="network">
    <h2 lang='021'>Network Usage</h2>
    <table class="stripeMe" id="networkTable" cellspacing="0"></table>
  </div>
  
  <div id="voltage" style="display: none;">
    <h2 lang='052'>Voltage</h2>
    <table class="stripeMe" id="voltageTable" cellspacing="0">
    <tr><th lang='059'>Label</th><th lang='052'>Voltage</th><th lang='055'>Min</th><th lang='056'>Max</th></tr>
    </table>    
  </div>
  
  <div id="temp" style="display: none;">
    <h2 lang='051'>Temperature</h2>
    <table class="stripeMe" id="tempTable" cellspacing="0">
    <tr><th lang='059'>Label</th><th lang='054'>Value</th><th lang='058'>Limit</th></tr>
    </table>    
  </div> 


  <div id="footer">
    <span lang='047'>Generated by</span> <a href="http://phpsysinfo.sourceforge.net/">phpSysInfo - <span id="version"></span></a>
  </div>

</div>



</body>
</html>
