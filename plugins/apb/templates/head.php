<?

//####################################################################
// Active PHP Bookmarks - lbstone.com/apb/
//
// Filename:    head.php
// Authors:     L. Brandon Stone (lbstone.com)
//
// 2003-03-11   Added security check. [LBS]
//
//####################################################################

//////////////////////////////////////////////////////////////////////
// Security check.
//////////////////////////////////////////////////////////////////////

if ($HTTP_COOKIE_VARS["APB_SETTINGS"]["template_path"] ||
    $HTTP_POST_VARS["APB_SETTINGS"]["template_path"] ||
    $HTTP_GET_VARS["APB_SETTINGS"]["template_path"])
{ exit(); }

//////////////////////////////////////////////////////////////////////
// Configuration.
//////////////////////////////////////////////////////////////////////

// This is where you can change the title of the program that shows up
// on every page.
$html_title = "";

//////////////////////////////////////////////////////////////////////
// There should be no need to alter anything below this point.  If
// you want to change the look and feel of APB, you should change the
// head_design.php file.
//////////////////////////////////////////////////////////////////////

// If you want to create your own design for APB, change the head_design.php file
include($APB_SETTINGS['template_path'] . "head_design.php");

if ($edit_mode) {  echo "<h2 class='warning'>Edit Mode</h2>"; }

?>
