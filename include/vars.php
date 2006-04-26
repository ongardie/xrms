<?php

if ( !defined('IN_XRMS') )
{
  die('Hacking attempt');
  exit;
}

/**
 * XRMS system configuration file
 *
 * You will need to review the variables in this file and
 * make changes as necessary for your environment.
 *
 * $Id: vars.php,v 1.43 2006/04/26 02:18:12 vanmer Exp $
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

// where is this application, web-wise?
// this can either be a path on the server (/xrms) or a full path including hostname (http://www.exampe.com/xrms)
// in either case, no trailing slash should be included
$http_site_root = "/xrms";

//where is the application in the filesystem (no trailing slash)
$xrms_file_root = "/full/path/to/xrms";


/***  File Upload controls ***/
/**
 * These settings for XRMS are complimentary to settings in your php.ini
 * file.  Your PHP .ini file needs the following three variables
 * to appropriate values:
 *
 * ;Whether to allow HTTP file uploads.
 * file_uploads = On
 *
 * ;Temporary directory for HTTP uploaded files
 * ;(will use system default if not specified).
 * upload_tmp_dir = /tmp/apache
 *
 * ;Maximum allowed size for uploaded files.
 * upload_max_filesize = 1M
 * ;set the upload_max_filesize to a number larger than the $max_file_size below
 */
// directory where uploaded files should go
// make sure these directories are writable by the apache user
$max_file_size = 200000;
$tmp_upload_directory = $xrms_file_root."/tmp/";
$file_storage_directory = $xrms_file_root."/storage/";

//uncomment this if you are having trouble with file uploads
//ini_set ('upload_tmp_dir', $tmp_upload_directory);

// directory for exports
// directory must be writable by apache,
// this needs to be relative to the xrms web/file root
// (browser needs to be able to see the directory [+x bit])
// (with trailing slash)
$tmp_export_directory = $xrms_file_root."/export/";
// if you change the export directory,
// you will need to use a <Location> tag in your web server config

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
// 218 is usually the United States, for example
$default_country_id = 218;

// replace this with your organization's name
$system_company_name = 'XRMS';

// replace this with the xrms company id
$my_company_id = 0;

// a few user-definable settings (there should be lots more)

$system_rows_per_page = 15;
$recent_items_limit = 15;
$display_how_many_activities_on_company_page = 20;
$display_how_many_activities_on_contact_page = 30;
$display_how_many_activities_on_home_page = 30;
$display_how_many_audit_items_on_dashboard = 20;

/* STYLE OPTIONS */

// replace this with HTML or another image
$required_indicator = "<img height=12 width=12 alt=required src=$http_site_root/img/required.gif>";

// if vertical space is tight, shrink this down and change the font size in the stylesheet
// these shouldn't even be here, though... all style info should be set in the stylesheet
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

// Activities Association Behavior
// Often, staff are sloppy about associateting Activities with 
// Opportunities or Cases.  If this option is set to true,
// XRMS will automatically associate the activity if there is only
// *one* open Opportunity or Case
$associate_activities = true;

// Time Zone Behavior
// If this is set to 'n' then it will make a best guess
// at the time zone offset and daylight savings format
// for a given address. As more accurate data is put
// into the database, it will give a better result.
// If it's set to 'y' then only time zones that are
// confirmed will be used in calculations
$only_confirmed_time_zones = 'y';

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

    //ldap server address
    $xrms_ldap["server"] = "ldap";

    //user name to do the search as (leave blank for anonymous login)
    $xrms_ldap["search_user"] = "";

    //password for the user to do the search as (leave blank for anonymous login)
    $xrms_ldap["search_pw"] = "";

    //context where to start the search in the tree
    $xrms_ldap["search_context"] = "dc=People,dc=mycompany,dc=com";
    //usually, search is done on cn, uid or mail
    $xrms_ldap["search_attribute"] = "uid";

    // default values for users added from LDAP
    //role ID for new users added via LDAP
    $xrms_ldap["default_role_id"] = "1";
    //time zone for new users added via LDAP
    $xrms_ldap["default_gmt_offset"] = "-6";

    //reference context used by export code when creating export hierarchy
    //export will look like ou=[company name],[value supplied below]
    $xrms_ldap["reference_context"] = "dc=Contacts,dc=mycompany,dc=com";

/*** Language settings ***/
/**
 * Multiple language/i18n/localization/translation support
 * gratefully ported from Squirrelmail.
 *
 * With 4 million+ users worldwide, we'll assume that the i18n
 * team there knows what they are doing.
 *
 * Ported by Brian Peterson (braverock)
 */

/**
 * Default language
 *
 *   This is the default language. It is used as a last resort
 *   if XRMS can't figure out which language to display.
 *   Language names usually consist of language code, underscore
 *   symbol and country code
 * @global string $xrms_default_language
 */
$xrms_default_language = 'en_US';

/**
 * Default Charset
 *
 * This option controls what character set is used when sending mail
 * and when sending HTML to the browser. Do not set this to US-ASCII,
 * use ISO-8859-1 instead.
 *
 * You can set this option, only if $xrms_default_language setting
 * contains 'en_US' string. In any other case system does not allow
 * making mistakes with incorrect language and charset combinations.
 * @global string $default_charset
 */
$default_charset = 'iso-8859-1';

/**
 * Available Languages
 *
 * This option controls number of languages available to end user in
 * language selection preferences. You can use space separated list
 * of translations installed in locale/ directory or special keys
 * 'all' (all languages are available) and 'none' (language selection
 * is disabled, interface is set to $xrms_default_language
 * @global string $available_languages
 */
$available_languages   = 'all';

/**
 * Alternative Language Names Control
 *
 * This options allows displaying native language names in language
 * selection box.
 * @global bool $show_alternative_names
 */
$show_alternative_names   = false;

/**
 * Aggresive Decoding Control
 *
 * This option enables reading of Eastern multibyte encodings.
 * Functions that provide this support are very cpu and memory intensive.
 * Don't enable this option unless you really need it.
 * @global bool $aggresive_decoding
 */
$aggresive_decoding = false;

/**
 * PHP recode functions control
 *
 * Use experimental code with php recode functions when reading messages with
 * different encoding. This code is faster than interpreted PHP functions,
 * but it require php with recode support.
 *
 * Don't enable this option if you are not sure about availability of
 * recode support.
 * @global bool $use_php_recode
 */
$use_php_recode = false;
/**
 * PHP iconv functions control
 *
 * Use experimental code with php iconv functions when reading messages with
 * different encoding. This code is faster than interpreted PHP functions,
 * but it require php with iconv support and works only with some translations.
 *
 * Don't enable this option if you are not sure about availability of
 * iconv support.
 * @global bool $use_php_iconv
 */
$use_php_iconv = false;

/*** End Language settings ***/

/**
 * The use_self option is deprecated, and will not affect the behavior 
 * of the system anymore. Please use the system preference 
 * labeled Undefined Company Method to control the behavior of contacts without companies
 *
 */
$use_self_contacts = false;

/**
 * fckeditor - Defines the location of the FCKEditor code
 */
global $include_directory;
$fckeditor_location = $include_directory . '/fckeditor/';
$fckeditor_location_url =  $http_site_root . '/include/fckeditor/';


//?>
