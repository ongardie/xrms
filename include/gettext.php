<?php
/**
 * XRMS internal gettext functions
 *
 * Copyright (c) 1999-2004 The Squirrelmail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 * Ported for use in XRMS by Brian Peterson
 *
 * Alternate to the system's built-in gettext.
 * relies on .po files (can't read .mo easily).
 * Uses the session for caching (speed increase)
 * Possible use in other PHP scripts?
 *
 * @link http://www.php.net/gettext Original php gettext manual
 * @version $Id: gettext.php,v 1.5 2011/02/18 19:45:33 gopherit Exp $
 * @package xrms
 * @subpackage i18n
 */


if ( !defined('IN_XRMS') )
{
  die('Hacking attempt');
  exit;
}


global $gettext_php_domain, $gettext_php_dir, $gettext_php_loaded,
 $gettext_php_translateStrings, $gettext_php_loaded_language,
 $gettext_php_short_circuit;

if (! isset($gettext_php_loaded)) {
    $gettext_php_loaded = false;
    xrms_session_register($gettext_php_loaded, 'gettext_php_loaded');
}
if (! isset($gettext_php_domain)) {
    $gettext_php_domain = '';
    xrms_session_register($gettext_php_domain, 'gettext_php_domain');
}
if (! isset($gettext_php_dir)) {
    $gettext_php_dir = '';
    xrms_session_register($gettext_php_dir, 'gettext_php_dir');
}
if (! isset($gettext_php_translateStrings)) {
    $gettext_php_translateStrings = array();
    xrms_session_register($gettext_php_translateStrings, 'gettext_php_translateStrings');
}
if (! isset($gettext_php_loaded_language)) {
    $gettext_php_loaded_language = '';
    xrms_session_register($gettext_php_loaded_language, 'gettext_php_loaded_language');
}
if (! isset($gettext_php_short_circuit)) {
    $gettext_php_short_circuit = false;
    xrms_session_register($gettext_php_short_circuit, 'gettext_php_short_circuit');
}

/**
 * Converts .po file into array and stores it in session.
 *
 * Used internally by _($str) function
 *
 * @internal function is used internally by functions/gettext.php code
 */
function gettext_php_load_strings() {
    global $xrms_language, $gettext_php_translateStrings,
        $gettext_php_domain, $gettext_php_dir, $gettext_php_loaded,
        $gettext_php_loaded_language, $gettext_php_short_circuit;

    /*
     * $xrms_language gives 'en' for English, 'de' for German,
     * etc.  I didn't wanna use getenv or similar, but you easily could
     * change my code to do that.
     */

    $gettext_php_translateStrings = array();

    $gettext_php_short_circuit = false;  /* initialization */

    $filename = $gettext_php_dir;
    if (substr($filename, -1) != '/')
        $filename .= '/';
    $filename .= $xrms_language . '/LC_MESSAGES/' .
        $gettext_php_domain . '.po';

    $file = @fopen($filename, 'r');
    if ($file == false) {
        /* Uh-ho -- we can't load the file.  Just fake it.  :-)
           This is also for English, which doesn't use translations */
        $gettext_php_loaded = true;
        $gettext_php_loaded_language = $xrms_language;
        /* Avoid fuzzy matching when we didn't load strings */
        $gettext_php_short_circuit = true;
        return;
    }

    $key = '';
    $SkipRead = false;
    while (! feof($file)) {
        if (! $SkipRead) {
            $line = trim(fgets($file, 4096));
        } else {
            $SkipRead = false;
        }

        if (preg_match('/^msgid "(.*)"$/', $line, $match)) {
            if ($match[1] == '') {
                /*
                 * Potential multi-line
                 * msgid ""
                 * "string string "
                 * "string string"
                 */
                $key = '';
                $line = trim(fgets($file, 4096));
                while (preg_match('/^[ ]*"(.*)"[ ]*$/', $line, $match)) {
                    $key .= $match[1];
                    $line = trim(fgets($file, 4096));
                }
                $SkipRead = true;
            } else {
                /* msgid "string string" */
                $key = $match[1];
            }
        } elseif (preg_match('/^msgstr "(.*)"$/', $line, $match)) {
            if ($match[1] == '') {
                /*
                 * Potential multi-line
                 * msgstr ""
                 * "string string "
                 * "string string"
                 */
                $gettext_php_translateStrings[$key] = '';
                $line = trim(fgets($file, 4096));
                while (preg_match('/^[ ]*"(.*)"[ ]*$/', $line, $match)) {
                    $gettext_php_translateStrings[$key] .= $match[1];
                    $line = trim(fgets($file, 4096));
                }
                $SkipRead = true;
            } else {
                /* msgstr "string string" */
                $gettext_php_translateStrings[$key] = $match[1];
            }
            $gettext_php_translateStrings[$key] =
                stripslashes($gettext_php_translateStrings[$key]);
            /* If there is no translation, just use the untranslated string */
            if ($gettext_php_translateStrings[$key] == '') {
                $gettext_php_translateStrings[$key] = $key;
            }
            $key = '';
        }
    }
    fclose($file);

    $gettext_php_loaded = true;
    $gettext_php_loaded_language = $xrms_language;
}

/**
 * Alternative php gettext function (short form)
 *
 * @link http://www.php.net/function.gettext
 *
 * @param string $str English string
 * @return string translated string
 */
function _($str) {
    global $gettext_php_loaded, $gettext_php_translateStrings,
        $xrms_language, $gettext_php_loaded_language,
        $gettext_php_short_circuit;

    if (! $gettext_php_loaded ||
        $gettext_php_loaded_language != $xrms_language) {
        gettext_php_load_strings();
    }

    /* Try finding the exact string */
    if (isset($gettext_php_translateStrings[$str])) {
        return $gettext_php_translateStrings[$str];
    }

    /* See if we should short-circuit */
    if ($gettext_php_short_circuit) {
        $gettext_php_translateStrings[$str] = $str;
        return $str;
    }

    /* Look for a string that is very close to the one we want
       Very computationally expensive */
    $oldPercent = 0;
    $oldStr = '';
    $newPercent = 0;
    foreach ($gettext_php_translateStrings as $k => $v) {
        similar_text($str, $k, $newPercent);
        if ($newPercent > $oldPercent) {
            $oldStr = $v;
            $oldPercent = $newPercent;
        }
    }
    /* Require 80% match or better
       Adjust to suit your needs */
    if ($oldPercent > 80) {
        /* Remember this so we don't need to search again */
        $gettext_php_translateStrings[$str] = $oldStr;
        return $oldStr;
    }

    /* Remember this so we don't need to search again */
    $gettext_php_translateStrings[$str] = $str;
    return $str;
}

/**
 * Alternative php bindtextdomain function
 *
 * Sets path to directory containing domain translations
 *
 * @link http://www.php.net/function.bindtextdomain
 * @param string $name gettext domain name
 * @param string $dir directory that contains all translations
 * @return string path to translation directory
 */
function bindtextdomain($name, $dir) {
    global $gettext_php_domain, $gettext_php_dir, $gettext_php_loaded;

    if ($gettext_php_domain != $name) {
        $gettext_php_domain = $name;
        $gettext_php_loaded = false;
    }
    if ($gettext_php_dir != $dir) {
        $gettext_php_dir = $dir;
        $gettext_php_loaded = false;
    }

    return $dir;
}

/**
 * Alternative php textdomain function
 *
 * Sets default domain name
 *
 * @link http://www.php.net/function.textdomain
 * @param string $name gettext domain name
 * @return string gettext domain name
 */
function textdomain($name = false) {
    global $gettext_php_domain, $gettext_php_loaded;

    if ($name != false && $gettext_php_domain != $name) {
        $gettext_php_domain = $name;
        $gettext_php_loaded = false;
    }

    return $gettext_php_domain;
}

/**
 * $Log: gettext.php,v $
 * Revision 1.5  2011/02/18 19:45:33  gopherit
 * Replaced functions ereg(), eregi(), ereg_replace() and eregi_replace() which have been deprecated as of PHP 5.3
 *
 * Revision 1.4  2004/08/06 14:47:07  braverock
 * - push in changes to turn on i18n gettext
 *
 * Revision 1.3  2004/07/14 11:50:50  cpsource
 * - Added security feature IN_XRMS
 *
 * Revision 1.2  2004/06/21 15:40:31  braverock
 * - modified i18n files to better integrate with XRMS
 *
 * Revision 1.1  2004/05/14 11:07:30  braverock
 * - initial checking of i18n files -- not yet working, doesn't break anything
 *
 */
?>