<?php
/**
 * XRMS system configuration file
 *
 * You will need to review the variables in this file and
 * make changes as necessary for your environment.
 *
 * $Id: vars.php,v 1.13 2004/03/22 22:10:23 braverock Exp $
 */

/**
 *  Database Connection info for XRMS system
 *
 *  Beth Macknik (maulani) has created a nice, pretty web-based way
 *  to install XRMS.
 *
 *  You need to create a database, add a user, and grant permissions.
 *  @example:  from a mysql prompt
 *  create database xrms;
 *  grant all privileges on xrms.* to xrms@localhost identified by 'yourpasswordhere';
 *
 *  After you've created the database and database user,
 *  follow the instructions in the install/INSTALL file
 *  and on the screen in the install scripts.
 */
$xrms_db_dbtype = 'mysql';
$xrms_db_server = 'localhost';
$xrms_db_username = 'your_mysql_username';
$xrms_db_password = 'your_mysql_password';
$xrms_db_dbname = 'your_mysql_database';

// where is this application, web-wise? (no trailing slash)
$http_site_root = "http://www.yoursitename.com/xrms";

//where is the appliation in the filesystem (no trailing slash)
$xrms_file_root = "/full/path/to/xrms";

// directory where uploaded files should go
// make sure these directories are writable by the apache user
$max_file_size = 200000;
$tmp_upload_directory = "/full/path/to/xrms/tmp/";
$file_storage_directory = "/full/path/to/xrms/files/storage/";

//uncomment this if you are having trouble with file uploads
//ini_set ('upload_tmp_dir', $tmp_upload_directory);

// directory for exports
// directory must be writable by apache,
// and should not be world readable (0700)
// this needs to be relative to the xrms web root
// (browser needs to be able to see it)
// (no trailing slash)
$tmp_export_directory = "/full/path/to/xrms/export/";

// accounting software integration is in the works, but for now
$accounting_system = ''; // no integration

// if you have more than one XRMS installation,
// these need to be unique so that users logged in to one
// application can't just start using the other one.
// This variable sets "scope" to the user's login.
$xrms_system_id = "XRMS";

// what should this application be called?
$app_title = 'XRMS';

// set the default country
// 218 is usally the United States, for example
$default_country_id = 218;

// replace this with your organization's name
$system_company_name = 'XRMS';

// so that order numbers can be continuous with whatever you're using now
$order_number_seed = 1000;

// a few user-definable settings (there should be lots more)

$system_rows_per_page = 15;
$recent_items_limit = 15;
$display_how_many_activities_on_company_page = 20;
$display_how_many_activities_on_contact_page = 30;
$display_how_many_activities_on_home_page = 30;
$display_how_many_audit_items_on_dashboard = 20;
$how_many_rows_to_import_per_page = 10;

/* STYLE OPTIONS */

// replace this with HTML or another image
$required_indicator = "<img height=12 width=12 alt=required src=$http_site_root/img/required.gif>";

// if vertical space is tight, shrink this down and change the font size in the stylesheet
// these shouldn't even be here, though... all style info should be set in the stylesheet
$page_title_height = 70;
$report_graph_height = 400;
$report_graph_width = 600;

// doesn't quite work yet, but should by version 0.9
$after_adding_new_companies_from_your_web_site_redirect_to_this_page = "http://www.yoursite.com/thank-you.html";

// label up to four custom fields for contact information

$contact_custom1_label = "(Custom 1)";
$contact_custom2_label = "(Custom 2)";
$contact_custom3_label = "(Custom 3)";
$contact_custom4_label = "(Custom 4)";

// label up to four custom fields for company information

$company_custom1_label = "(Custom 1)";
$company_custom2_label = "(Custom 2)";
$company_custom3_label = "(Custom 3)";
$company_custom4_label = "(Custom 4)";

// Activities default creation behavior.  Change to long if your users always need
//  to enter more detail for activities
//  Options are "Fast" or "Long"

$activities_default_behavior = "Fast";

/** Optional LDAP configuration parameters **/
/**
 * Set $xrms_use_ldap to truw to tur on LDAP
 * username and password lookup
 *
 * LDAP code contributed by
 * nick <at> barcet <dot> com
 */
$xrms_use_ldap = false; //set to true if you want ldap authenthication
    //all other ldap params are useless if $xrms_use_ldap is false
    $xrms_ldap["server"] = "localhost";                             //ldap server address
    $xrms_ldap["search_user"] = "cn=search,ou=services,o=barcet";   //user name to do the search as (leave blank for anonymous login)
    $xrms_ldap["search_pw"] = "search";                             //password for the user to do the search as (leave blank for anonymous login)
    $xrms_ldap["search_context"] = "o=novell";                      //context where to start the search in the tree
    $xrms_ldap["search_attribute"] = "cn";                          //usually, search is done on cn, uid or mail

?>