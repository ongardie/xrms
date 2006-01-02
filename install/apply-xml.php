<?php
/**
 * install/extract-util.php - This page should be used by developers and administrators to extract an xml schema
 *
 * $Id: apply-xml.php,v 1.2 2006/01/02 23:23:09 vanmer Exp $
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

//Uncomment this if you want to see what's going on
//$con->debug=true;

/* Use the database connection to create a new adoSchema object.
 */
$schema = new adoSchema($con);

/* Call ParseSchema() to build SQL from the XML schema file.
 * Then call ExecuteSchema() to apply the resulting SQL to
 * the database.
 */
$schemaFile='xrms-schema.xml';

//execute while parsing schema, might work better than execute afterwards
//$schema->ExecuteInline( TRUE );
$sql = $schema->ParseSchema( $schemaFile );
$result = $schema->ExecuteSchema();

//<?php

//end_page();

/**
 *$Log: apply-xml.php,v $
 *Revision 1.2  2006/01/02 23:23:09  vanmer
 *- changed to use centralized dbconnection function
 *
 *Revision 1.1  2005/10/03 21:19:58  vanmer
 *- Adding application to use XML schema for install of XRMS tables
 *- Commit includes extract file to get current database into XML form
 *- includes apply script to install schema
 *- includes a standard clean install .xml file, for use in a new install
 *
 */
?>