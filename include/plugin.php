<?php
/**
 * plugin.php
 *
 * Copyright (c) 1999-2004 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * This file provides the framework for a plugin architecture.
 *
 * Documentation on how to write plugins will follow.
 *
 * This file has been modified from the Squirrelmail plugin.php file
 * by Brian Peterson for use in XRMS
 *
 * $Id: plugin.php,v 1.11 2006/04/05 01:27:46 vanmer Exp $
 * @package xrms
 */

if ( !defined('IN_XRMS') )
{
  die('Hacking attempt');
  exit;
}

/** Everything needs vars.php */
require_once($include_directory .'vars.php');
require_once($include_directory .'plugin-cfg.php');

global $xrms_plugin_hooks;
$xrms_plugin_hooks = array();

/***********************************************************************/
/**
 * function use_plugin: This function adds a plugin.
 *
 * @param string $name Internal plugin name (ie. news, notifications)
 * @return void
 */
function use_plugin ($name) {

    global $xrms_file_root;

    // uncomment the following debug line to trace
    // echo "<br> Looking for: ".$xrms_file_root."/plugins/".$name."/setup.php \n";
    $setup_filename=$xrms_file_root . DIRECTORY_SEPARATOR . 'plugins'. DIRECTORY_SEPARATOR .$name. DIRECTORY_SEPARATOR .'setup.php';
    if (file_exists($setup_filename)) {
        include_once($setup_filename);
        $function = "xrms_plugin_init_$name";

        // uncomment the following debug line to trace
        // echo 'executing '. $function;

        if (function_exists($function)) {
            $function();
        }
    }
} //end use_plugin fn

/***********************************************************************/
/**
 * This function executes a hook.
 * @param string $name Name of hook to fire
 * @return mixed $data
 */
function do_hook ($name) {
    global $xrms_plugin_hooks;
    $data = func_get_args();
    $ret = '';

    if (isset($xrms_plugin_hooks[$name])
          && is_array($xrms_plugin_hooks[$name])) {
        foreach ($xrms_plugin_hooks[$name] as $function) {
            /* Add something to set correct gettext domain for plugin. */
            if (function_exists($function)) {
                $function($data);
            }
        }
    }

    /* Variable-length argument lists have a slight problem when */
    /* passing values by reference. Pity. This is a workaround.  */
    return $data;
}

/***********************************************************************/
/**
 * This function executes a hook and allows for parameters to be passed.
 *
 * @param string name the name of the hook
 * @param mixed param the parameters to pass to the hook function (by reference)
 * @return mixed the return value of the hook function
 */
function do_hook_function($name, &$parm) {
    global $xrms_plugin_hooks;
    $ret = '';

    if (isset($xrms_plugin_hooks[$name])
          && is_array($xrms_plugin_hooks[$name])) {
        foreach ($xrms_plugin_hooks[$name] as $function) {
            /* Add something to set correct gettext domain for plugin. */
            if (function_exists($function)) {
                $ret = $function($parm);
            }
        }
    }

    /* Variable-length argument lists have a slight problem when */
    /* passing values by reference. Pity. This is a workaround.  */
    return $ret;
}

/***********************************************************************/
/**
 * This function executes a hook, concatenating the results of each
 * plugin that has the hook defined.
 *
 * @param string name the name of the hook
 * @param mixed parm optional hook function parameters
 * @return string a concatenation of the results of each plugin function
 */
function concat_hook_function($name,$parm=NULL) {
    global $xrms_plugin_hooks;
    $ret = '';

    if (isset($xrms_plugin_hooks[$name])
          && is_array($xrms_plugin_hooks[$name])) {
        foreach ($xrms_plugin_hooks[$name] as $function) {
            /* Concatenate results from hook. */
            if (function_exists($function)) {
                $ret .= $function($parm);
            }
        }
    }

    /* Variable-length argument lists have a slight problem when */
    /* passing values by reference. Pity. This is a workaround.  */
    return $ret;
}

/***********************************************************************/
/**
 * This function is used for hooks which are to return true or
 * false. If $priority is > 0, any one or more trues will override
 * any falses. If $priority < 0, then one or more falses will
 * override any trues.
 * Priority 0 means majority rules.  Ties will be broken with $tie
 *
 * @param string name the hook name
 * @param mixed parm the parameters for the hook function
 * @param int priority
 * @param bool tie
 * @return bool the result of the function
 */
function boolean_hook_function($name,$parm=NULL,$priority=0,$tie=false) {
    global $xrms_plugin_hooks;
    $yea = 0;
    $nay = 0;
    $ret = $tie;

    if (isset($xrms_plugin_hooks[$name]) &&
        is_array($xrms_plugin_hooks[$name])) {

        /* Loop over the plugins that registered the hook */
        foreach ($xrms_plugin_hooks[$name] as $function) {
            if (function_exists($function)) {
                $ret = $function($parm);
                if ($ret) {
                    $yea++;
                } else {
                    $nay++;
                }
            }
        }

        /* Examine the aftermath and assign the return value appropriately */
        if (($priority > 0) && ($yea)) {
            $ret = true;
        } elseif (($priority < 0) && ($nay)) {
            $ret = false;
        } elseif ($yea > $nay) {
            $ret = true;
        } elseif ($nay > $yea) {
            $ret = false;
        } else {
            // There's a tie, no action needed.
        }
        return $ret;
    }
    // If the code gets here, there was a problem - no hooks, etc.
    return NULL;
}

/*************************************/
/*** MAIN PLUGIN LOADING CODE HERE ***/
/*************************************/
global $plugins;

/* On startup, register all plugins configured for use. */
if (isset($plugins) && is_array($plugins)) {
    foreach ($plugins as $name) {
        // uncomment the following debug line to trace
        //echo 'calling use_plugin '. $name. '<br>';

        // register the plugin hooks into the array
        use_plugin($name);
    }

    // uncomment the following debug line to trace
    //print_r ($xrms_plugin_hooks);
}


/*************************************/
/**
 * $Log: plugin.php,v $
 * Revision 1.11  2006/04/05 01:27:46  vanmer
 * - don't append function value, instead take function value directly
 *
 * Revision 1.10  2005/07/26 18:57:57  vanmer
 * - changed to define variable for setup.php path
 * - changed setup.php path to use DIRECTORY_SEPARATOR between directories and files
 *
 * Revision 1.9  2005/04/07 18:15:08  vanmer
 * - changed second parameter to do_hook_function to take reference
 *
 * Revision 1.8  2005/03/17 20:49:50  gpowers
 * - fixed bug: "only displays one plugin per hook"
 *
 * Revision 1.7  2005/01/09 00:25:38  vanmer
 * - altered do_hook_function to original behavior to allow variables to be passed by reference, and not be interpreted using eval()
 *
 * Revision 1.6  2004/08/12 13:54:31  braverock
 * - remove SoupNazi fn, as it isn't used in XRMS
 *
 * Revision 1.5  2004/07/14 11:50:50  cpsource
 * - Added security feature IN_XRMS
 *
 * Revision 1.4  2004/07/06 21:19:00  neildogg
 * - Allows for multi-parameter passing
 * - ie do_hook_function("plugin_name", $param, $param2, ...)
 * - instead of do_hook_function("plugin_name", $param)
 * - Transparent to existing methods
 *
 * Revision 1.3  2004/03/20 20:01:57  braverock
 *  - comment out one of the debug statements (sorry)
 *
 * Revision 1.2  2004/03/20 19:21:40  braverock
 *  - finalized main plugin loading code
 *
 * Revision 1.1  2004/03/02 14:05:17  braverock
 * Initial revision of Plugin Infrastructure code
 *
 */
?>
