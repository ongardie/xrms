<?php
/**
 * install/extract-util.php - This page should be used by developers and administrators to extract an xml schema
 *
 * $Id: extract-xml.php,v 1.4 2006/01/02 23:23:09 vanmer Exp $
 */

if (!defined('IN_XRMS')) {
    define('IN_XRMS', true);
}


// include the installation utility routines
require_once('install-utils.inc');
require_once('database.php');
require_once('data.php');

// where do we include from
require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');


// more tests on the vars.php variables should exist here
// optimally, all variables would be tested.


// get required common files
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb/adodb-xmlschema.inc.php' );

// can we make a database connection?
$connectiontest = get_xrms_dbconnection();
if (!$connectiontest) {
    // Oops!  We do not have a valid database connection
    // Now instruct the user in how to fix this problem
    $problem = 'We cannot connect to the database.  Check the database ';
    $problem .= 'parameters in include/vars.php to make sure they are correct.';
    $problem .= 'Also make sure the database is running and can accept a connection ';
    $problem .= 'from this server. <BR><BR>';
    $problem .= 'Then run this installation again.<BR><BR>';

    install_fatal_error($problem);
} else $con=$connectiontest;


$role = $_SESSION['role_short_name'];

if ( $msg ) {
  $msg = "?msg=$msg";
}

// if this is a mailto link, try to open the user's default mail application
//if (!check_user_role(false, $_SESSION['session_user_id'], 'Administrator')) {
//    $msg = _("You do not have permission to access that page.");

//    header("Location: " . $http_site_root . "/private/home.php" . $msg);
//}

$schema = new adoSchema( $con );

$schemastring= $schema->ExtractSchema(true);

//send download headers, don't force pop-up download dialog on browser
//SendDownloadHeaders('text','xml', 'xrms-schema.xml', true, strlen($schemastring));
$out_path=$tmp_upload_directory."xrms-schema.xml";
$fpath=fopen($out_path, 'w');
$ret=fwrite($fpath, $schemastring, strlen($schemastring));
fclose($fpath);
echo "Placed Schema in $out_path";

/**
 *$Log: extract-xml.php,v $
 *Revision 1.4  2006/01/02 23:23:09  vanmer
 *- changed to use centralized dbconnection function
 *
 *Revision 1.3  2005/12/06 22:32:14  vanmer
 *- changed to save xml schema file in the upload directory
 *
 *Revision 1.2  2005/11/30 00:40:09  vanmer
 *- changed to extract XML to a file in the temporary directory instead of downloading to the
 *client
 *
 *Revision 1.1  2005/10/03 21:19:58  vanmer
 *- Adding application to use XML schema for install of XRMS tables
 *- Commit includes extract file to get current database into XML form
 *- includes apply script to install schema
 *- includes a standard clean install .xml file, for use in a new install
 *
 */
?>