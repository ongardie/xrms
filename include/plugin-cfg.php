<?php

if ( !defined('IN_XRMS') )
{
  die('Hacking attempt');
  exit;
}

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
 *    $plugins[2] = 'demo';
 *
 *
 * Normally, this file is generated by admin/plugin/plugin-admin.php
 * $Id: plugin-cfg.php,v 1.5 2005/11/15 12:08:04 braverock Exp $
 */
// Add list of enabled plugins here
$plugins = array();
?>