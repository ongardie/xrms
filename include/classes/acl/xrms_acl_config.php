<?php
/**
 * config file for ACL
 *
 * Design copyright 2004 Explorer Fund Advisors
 * All Rights Reserved
 *
 * $Id: xrms_acl_config.php,v 1.9 2006/04/05 01:29:42 vanmer Exp $
 */

/**
 * Database Connection info for ACL system
 *
 * You need to create a database, add a user, and grant permissions.
 * @example:  from a mysql prompt
 * create database acl;
 * grant all privileges on xrms_acl.* to acl@localhost identified by 'yourpasswordhere';
 * source xrms_acl.sql;
 *
 * After you've created the database and database user,
 * you can start setting up ACL privileges
 */
 require_once ($include_directory.'vars.php');
 require_once ($include_directory.'plugin.php');

 $xrms_acl_db_dbtype   = $xrms_db_dbtype;
 $xrms_acl_db_server   = $xrms_db_server;
 $xrms_acl_db_username = $xrms_db_username;
 $xrms_acl_db_password = $xrms_db_password;
 $xrms_acl_db_dbname   = $xrms_db_dbname;


  $xrms_acl_test_db_dbname   = 'xrms_acl_test';


 $options['default']['db_dbtype'] = $xrms_acl_db_dbtype;
 $options['default']['db_server'] = $xrms_acl_db_server;
 $options['default']['db_username'] = $xrms_acl_db_username;
 $options['default']['db_password'] = $xrms_acl_db_password;
 $options['default']['db_dbname'] = $xrms_acl_db_dbname;


 $plugin_options=do_hook_function('xrms_acl_database_access',$options);
 
 $options['ACL_Test']=$options['default'];
 $options['ACL_Test']['db_dbname'] = $xrms_acl_test_db_dbname;

 if (isset($xrms_db_dbname)) {
     $options['XRMS'] = $options['default'];
     $options['XRMS']['db_dbname'] = $xrms_db_dbname;
 }

function xrms_acl_auth_callback(&$authInfo, $data_source_name) {
    global $options;
    if ($data_source_name=='default' OR $data_source_name=='XRMS') {
        $mycon=get_xrms_dbconnection();
        if ($mycon AND (strpos(strtolower(get_class($mycon)),'adodb')==0)) {
            return $mycon;
        } else {
            $authInfo=$options;
        }
    } else $authInfo=$options;
    
    //add key to pluginInfo so plugins know they should try to make a connection or provide auth info for this data_source_name
    $pluginInfo[$data_source_name]=array();
    
    //try to get back a return (adodbconnection) or pluginInfo populated with $pluginInfo[$data_source_name]  
    $option_value=do_hook_function('xrms_acl_auth', $pluginInfo);
    if ($option_value AND (substr(get_class($option_value),0,5)=='adodb')) {
        return $option_value;
    } else {
        if (count($pluginInfo[$data_source_name])>0) {
	   if ($pluginInfo[$data_source_name]['dbconnection'] AND (substr(strtolower(get_class($pluginInfo[$data_source_name]['dbconnection'])),0,5)=='adodb')) {
	       return $pluginInfo[$data_source_name]['dbconnection'];
	   }
           $authInfo[$data_source_name]=$pluginInfo[$data_source_name];
        }
        return false;
    }
}
 
/**
 * $Log: xrms_acl_config.php,v $
 * Revision 1.9  2006/04/05 01:29:42  vanmer
 * - ensure class is adodb connection before using as connection object
 *
 * Revision 1.8  2005/11/17 23:54:44  daturaarutad
 * add strtolower to get_class calls on $con for php5
 *
 * Revision 1.7  2005/08/11 22:27:45  vanmer
 * - added code to allow an application to return dbconnection in the pluginInfo array
 *
 * Revision 1.6  2005/08/10 22:56:00  vanmer
 * - added case for plugin data source to pass back authInfo to ACL
 *
 * Revision 1.5  2005/07/22 23:14:43  vanmer
 * - added callback function to provide the ACL with authentication information
 *
 * Revision 1.4  2005/04/07 18:14:30  vanmer
 * - changed second parameter to do_hook_function to pass variable instead of passing reference (reference is now in function definition)
 *
 * Revision 1.3  2005/01/25 06:25:25  vanmer
 * - changed to use pointer to array to allow plugins to modify directly
 *
 * Revision 1.2  2005/01/25 06:07:16  vanmer
 * - removed extraneous ACL configuration
 * - added hook for plugins to define ACL authentication information
 *
 * Revision 1.1  2005/01/13 17:07:17  vanmer
 * - Initial Revision of the ACL Install, Wrapper, Class and configuration files
 *
 * Revision 1.6  2004/12/27 23:52:39  ke
 * - force stylesheet to be left, to allow admin interface to be left-rendered
 *
 * Revision 1.5  2004/12/15 18:27:35  ke
 * - added isset checks before defining data sources for external data
 *
 * Revision 1.4  2004/12/15 17:59:36  ke
 * -added new database definitions for other data sources
 *
 * Revision 1.3  2004/12/02 07:13:28  ke
 * - updated to have generic username/password at first
 *
 * Revision 1.2  2004/12/02 04:00:27  ke
 * -Fixed variable naming scheme
 *
 * Revision 1.1  2004/12/02 03:58:50  ke
 * -Initial Revision of the xrms acl database access configuration file
 *
 *
 */
?>
