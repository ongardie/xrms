<?php

// config.php
//
// Certain other settings may be configured; look in lib/defaults.php
// to see them.  Rather than changing them in lib/defaults.php, you
// should copy them from there to here.  The settings here will safely
// over-ride those in lib/defaults.php.

// include the XRMS config file
require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');

// Disable Captcha Spam Blocking.
$UseCaptcha=0;

// $Admin specifies the administrator e-mail address used in error messages.
$Admin = '';

// If $DBPersist is not 0, persistent database connections will be used.
// Note that this is not supported by all hosting providers.
$DBPersist = 0;

// $xrms_db_dbtype;

$DBServer = $xrms_db_server;
$DBName = $xrms_db_dbname;
$DBUser = $xrms_db_username;
$DBPasswd = $xrms_db_password;

// $DBTablePrefix is used to start table names for the wiki's tables.  If your
// hosting provider only allows you one database, you can set up multiple
// wikis in the same database by creating tables that have different prefixes.
$DBTablePrefix = 'tavi_';

// $WikiName determines the name of your wiki.  This name is used in the
// browser title bar.  Often, it will be the same as $HomePage.
$WikiName = $app_title;

// $HomePage determines the "main" page of your wiki.  If browsers do not ask
// to see a specific page they will be shown the home page.  This should be
// a wiki page name, like 'AcmeProjectWiki'.
$HomePage = 'WikiHome';

// $InterWikiPrefix determines what interwiki prefix you recommend other
// wikis use to link to your wiki. Usually it is similar to your WikiName.
$InterWikiPrefix = '';

// If $EnableFreeLinks is set to 1, links of the form "((page name))" will be
// turned on for this wiki.  If it is set to 0, they will be disallowed.
$EnableFreeLinks = 1;

// If $EnableWikiLinks is set to 1, normal WikiNames will be treated as links
// in this wiki.  If it is set to 0, they will not be treated as links
// (in which case you should be careful to enable free links!).
$EnableWikiLinks = 1;

// When $EnableTextEnhance is set to 1, the extended operators allowing you
// to do both bold, italic, super-/subscript, ins/del are available. When
// set to 0, only the traditional ''italic'' and '''bold'' are available.
$EnableTextEnhance = 1;

// $ScriptBase determines the location of your wiki script.  It should indicate
// the full URL of the main index.php script itself.
$ScriptBase = $http_site_root . '/plugins/tavi/index.php';

// $AdminScript indicates the location of your admin wiki script.  It should
// indicate the full URL of the admin/index.php script itself.
$AdminScript = $http_site_root . '/plugins/tavi/admin/index.php';

// $WikiLogo determines the location of your wiki logo.
$WikiLogo = 'http://tavi.sourceforge.net/tavi.png';

// $MetaKeywords indicates what keywords to report on the meta-keywords tag.
// This is useful to aid search engines in indexing your wiki.
$MetaKeywords = '';

// $MetaDescription should be a sentence or two describing your wiki.  This
// is useful to aid search engines in indexing your wiki.
$MetaDescription = '';

// TemplateDir indicates what directory your wiki templates are located in.
// You may use this to install other templates than the default template.
define('TemplateDir', 'template');

// !!!WARNING!!!
// If $AdminEnabled is set to 1, the script admin/index.php will be accessible.
//   This allows administrators to lock pages and block IP addresses.  If you
//   want to use this feature, YOU SHOULD FIRST BLOCK ACCESS TO THE admin/
//   DIRECTORY BY OTHER MEANS, such as Apache's authorization directives.
//   If you do not do so, any visitor to your wiki will be able to lock pages
//   and block others from accessing the wiki.
// If $AdminEnabled is set to 0, administrator control will be disallowed.
$AdminEnabled = 0;

?>
