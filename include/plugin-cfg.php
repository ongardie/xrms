<?php
/*** Plugins ***/
/**
 * To install plugins, just add elements to this array that have
 * the plugin directory name relative to the /plugins/ directory.
 * For instance, for the 'clock' plugin, you'd put a line like
 * the following.
 *
 * @example
 *    $plugins[0] = 'clock';
 *    $plugins[1] = 'inventory';
 *    $plugins[2] = 'inventory';
 *
 * $Id: plugin-cfg.php,v 1.1 2004/03/20 20:07:57 braverock Exp $
 */
// Add list of enabled plugins here
$plugins = array();

$plugins[0] = 'demo';

// uncomment the following debug line to trace
// print_r ($plugins);

?>