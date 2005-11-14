<?php

/** 
* Config file for ADOdb_QuickForm classes
* 
* This file defines two locations: $file_path_to_fckeditor and $web_path_to_fckeditor 
*
*/


// These are defined in vars.php
global $http_site_root;

// Defined in XRMS
global $fckeditor_location;
global $file_path_to_fckeditor;

global $file_path_to_fckeditor;
$file_path_to_fckeditor = $fckeditor_location;

// This is the web site path
global $web_path_to_fckeditor;
$web_path_to_fckeditor = $http_site_root . '/include/fckeditor/';

global $fckeditor_height;
$fckeditor_height = '400';





?>