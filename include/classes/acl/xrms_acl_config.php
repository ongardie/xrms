<?php
/**
 * config file for ACL
 *
 * Design copyright 2004 Explorer Fund Advisors
 * All Rights Reserved
 *
 * $Id: xrms_acl_config.php,v 1.1 2005/01/13 17:07:17 vanmer Exp $
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

 $options['ACL_Test']=$options['default'];
 $options['ACL_Test']['db_dbname'] = $xrms_acl_test_db_dbname;

 if (isset($xrms_db_dbname)) {
     $options['XRMS'] = $options['default'];
     $options['XRMS']['db_dbname'] = $xrms_db_dbname;
 }
 if (isset($portfolio_db_server)) {
     $options['portfolio'] = $options['default'];
     $options['portfolio']['db_server'] = $portfolio_db_server;
     $options['portfolio']['db_username'] = $portfolio_db_username;
     $options['portfolio']['db_password'] = $portfolio_db_password;
     $options['portfolio']['db_dbname'] = $portfolio_db_dbname;
 }

 if (isset($tridenfs_site_db_server)) {
     $options['tridentfs_site'] = $options['default'];
     $options['tridentfs_site']['db_server'] = $tridentfs_site_db_server;
     $options['tridentfs_site']['db_username'] = $tridentfs_site_db_username;
     $options['tridentfs_site']['db_password'] = $tridentfs_site_db_password;
     $options['tridentfs_site']['db_dbname'] = $tridentfs_site_db_dbname;
 }

 if (isset($trident_db_server)) {
     $options['trident'] = $options['default'];
     $options['trident']['db_server'] = $trident_db_server;
     $options['trident']['db_username'] = $trident_db_username;
     $options['trident']['db_password'] = $trident_db_password;
     $options['trident']['db_dbname'] = $trident_db_dbname;
 }

/**
 * $Log: xrms_acl_config.php,v $
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
