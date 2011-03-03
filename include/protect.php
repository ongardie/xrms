<?php

if ( !defined('IN_XRMS') )
{
  die('Hacking attempt');
  exit;
}

/**
 * protect.php - Protect against some common security flaws
 *
 * Ported from the MODx CMS distributed under the GNU General Public License
 * © 2005-2009 the MODx CMS project http://modxcms.com/
 *
 * $Id: protect.php,v 1.4 2011/03/03 17:46:41 gopherit Exp $
 */

// Null is evil
if (isset($_SERVER['QUERY_STRING']) && strpos(urldecode($_SERVER['QUERY_STRING']), chr(0)) !== false)
    die();

// Unregister globals
if (@ ini_get('register_globals')) {
    foreach ($_REQUEST as $key => $value) {
        $$key = null; // This is NOT paranoid because
        unset ($$key); // unset may not work.
    }
}

$sanitizetags = array (
    '@<script[^>]*?>.*?</script>@si',
    '@%3Cscript[^>]*?%3E.*?%3C/script%3E@si',
    '@&#(\d+);@e',
    '@\[\[(.*?)\]\]@si',
    '@\[!(.*?)!\]@si',
    '@\[\~(.*?)\~\]@si',
    '@\[\((.*?)\)\]@si',
    '@{{(.*?)}}@si',
    '@\[\+(.*?)\+\]@si',
    '@\[\*(.*?)\*\]@si'
);
if (!function_exists('sanitize_gpc')) {
    function sanitize_gpc(&$target, $sanitizetags, $limit= 3) {
        foreach ($target as $key => $value) {
            if (is_array($value) && $limit > 0) {
                sanitize_gpc($value, $sanitizetags, $limit - 1);
            } else {
                $target[$key] = preg_replace($sanitizetags, "", $value);
            }
        }
        return $target;
    }
}

sanitize_gpc($_GET, $sanitizetags);
sanitize_gpc($_POST, $sanitizetags);
sanitize_gpc($_COOKIE, $sanitizetags);
sanitize_gpc($_REQUEST, $sanitizetags);
sanitize_gpc($_SERVER, $sanitizetags);

foreach (array ('PHP_SELF', 'HTTP_USER_AGENT', 'HTTP_REFERER', 'QUERY_STRING') as $key) {
    $_SERVER[$key] = isset ($_SERVER[$key]) ? htmlspecialchars($_SERVER[$key], ENT_QUOTES) : null;
}

// Unset vars
unset ($sanitizetags, $key, $value);

/**
 * $Log: protect.php,v $
 * Revision 1.4  2011/03/03 17:46:41  gopherit
 * Egh!  CVS Keyword Syntax!
 *
 */
?>
