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
 * be initalized to NULL values.
 *
 * If you are debugging a really thorny problem, you may want to uncomment the second
 * error_reporting line and sort through the Notices.
 *
 * $Id: include-locations.inc,v 1.20 2006/03/16 00:35:16 vanmer Exp $
 */

//
// A security feature that helps prevent malicious users
// from attempting to execute scripts out of order
//
if (!defined('IN_XRMS')) {
    define('IN_XRMS', true);
}

// This path should be the full filesystem path to the include directory for XRMS
// include trailing slash in path
$include_directory = "/full/path/to/xrms/include/";

// Report all errors except E_NOTICE
// This is the default value set in php.ini
error_reporting(E_ALL ^E_NOTICE);

//uncomment the next line to turn notices on
//error_reporting(E_ALL);

/**
 * $Log: include-locations.inc,v $
 * Revision 1.20  2006/03/16 00:35:16  vanmer
 * - added better notes and a more easily understood example for the include path
 *
 * Revision 1.19  2005/09/22 13:09:18  braverock
 * - remove trailing ?> from vars.php and include-locations.inc
 *   *trailing text on vars and include-locations file will now be parsed as php
 * - remove whitespace check from install, it causes too many problems
 *
 * Revision 1.18  2005/06/21 15:47:09  vanmer
 * - rolling back another errant commit
 *
 * Revision 1.16  2004/12/21 19:38:57  braverock
 * roll back errant commit
 *
 * Revision 1.15  2004/12/21 19:36:14  braverock
 * - improved display of screen table
 * - fixed code formatting
 *
 * Revision 1.14  2004/08/06 15:47:40  braverock
 * - roll back previous errant commit
 *
 * Revision 1.13  2004/08/06 14:47:08  braverock
 * - push in changes to turn on i18n gettext
 *
 * Revision 1.12  2004/07/19 21:05:01  maulani
 * - Add check to make sure that IN_XRMS is defined only once
 *
 * Revision 1.11  2004/07/14 11:47:06  cpsource
 * - Add security feature IN_XRMS
 *
 * Revision 1.10  2004/07/13 13:11:54  cpsource
 * - Add new-line to end-of-file so emacs emerges work
 *
 * Revision 1.9  2004/03/24 18:17:27  maulani
 * - Restore from wayward commit
 *
 * Revision 1.8  2004/03/24 18:12:43  maulani
 * - add phpdoc
 *
 * Revision 1.7  2004/01/28 20:28:36  braverock
 * - set error_reporting level
 * - add phpdoc and instructions to docblock
 */
//?>
