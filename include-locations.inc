<?php
/**
 * This file is defines where the include directory for XRMS is, 
 * and sets the error reporting level.
 *
 * Ideally, your include directory will be outside of the web server document root.
 *
 * The include directory needs to have the trailing slash to the directory.
 *
 * If you are running the webserver in a chroot environment, the first 
 * slash in the include path will be relative to the chroot path.
 *
 * The error reporting level for XRMS is intentionally set to 
 * E_ALL and not E_NOTICE.  This should allow all real errors and warnings to 
 * show up without bothering us with notices about variables that are assumed to 
 * be inititalized to NULL values.
 * 
 * If you are debugging a really thorny problem, you may want to uncomment the second
 * error_reporting line and sort through the Notices.
 *
 * $Id: include-locations.inc,v 1.9 2004/03/24 18:17:27 maulani Exp $
 */

// include trailing slash in path
$include_directory = "/full/path/to/xrms/include/";

// Report all errors except E_NOTICE
// This is the default value set in php.ini
error_reporting(E_ALL ^ E_NOTICE);

//uncomment the next line to turn notices on
//error_reporting(E_ALL);


/**
 * $Log: include-locations.inc,v $
 * Revision 1.9  2004/03/24 18:17:27  maulani
 * - Restore from wayward commit
 *
 * Revision 1.8  2004/03/24 18:12:43  maulani
 * - add phpdoc
 *
 * Revision 1.7  2004/01/28 20:28:36  braverock
 * - set error_reporting level
 * - add phpdoc and instructions to docblock
 *
 */
?>