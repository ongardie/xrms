<?php
/**
 * XRMS system configuration file
 *
 * You will need to review the variables in this file and
 * make changes as necessary for your environment.
 *
 * $Id: vars.php,v 1.18 2004/04/29 20:19:13 braverock Exp $
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
 * Agresive Decoding Control
 *
 * This option enables reading of Eastern multibyte encodings.
 * Functions that provide this support are very cpu and memory intensive.
 * Don't enable this option unless you really need it.
 * @global bool $agresive_decoding
 */
$agresive_decoding = false;

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

?>