<?php
/*
 * setup.php
 *
 * @example Create a function called
 *      xrms_plugin_init_pluginname
 *      where pluginname is the name of your pluign directory
 *      inside this function, you will register all the hooks
 *      that you wish your plugin to be called by
 *
 * You should also put the called functions in your setup,php file
 * Please take care to keep this file as small as possible, as it
 * is included on every page load.  Place your actualy functionality
 * in another file.  It will improve the performance of the entire
 * system.
 *
 * $Id $
 */



// Note:  functionality should be moved out of this file and into another that does the actual work!
// reason is that this file gets loaded often!


function xrms_plugin_init_owl() {
    global $xrms_plugin_hooks;
    $xrms_plugin_hooks['file_add_file']['owl'] = 'fn_add_file';
    $xrms_plugin_hooks['file_update_file']['owl'] = 'fn_upd_file';
    $xrms_plugin_hooks['file_delete_file']['owl'] = 'fn_delete_file';
    $xrms_plugin_hooks['file_get_file_info']['owl'] = 'fn_get_file_info';
    $xrms_plugin_hooks['file_get_one_file_html']['owl'] = 'fn_get_one_file_html';
    $xrms_plugin_hooks['file_add_folder']['owl'] = 'fn_add_folder';
    $xrms_plugin_hooks['file_delete_folder']['owl'] = 'fn_delete_folder';
    $xrms_plugin_hooks['file_download_file']['owl'] = 'fn_download_file';
    $xrms_plugin_hooks['file_get_xrms_file_id']['owl'] = 'fn_get_xrms_file_id';
    $xrms_plugin_hooks['file_browse_files']['owl'] = 'fn_browse_files';
    $xrms_plugin_hooks['file_search_files']['owl'] = 'fn_search_files';
    $xrms_plugin_hooks['file_search_files_callback']['owl'] = 'fn_search_files_callback';
    $xrms_plugin_hooks['file_get_search_fields_html']['owl'] = 'fn_get_search_fields_html';
    $xrms_plugin_hooks['file_get_search_fields_sql']['owl'] = 'fn_get_search_fields_sql';
	$xrms_plugin_hooks['xrms_update']['owl'] = 'fn_owl_xrms_update';
}



/**
*
*  These functions are hooks, but not part of the external OWL API
*
*/

/**
* Add the extra owl search fields to the search bar in xrms?
*/
function fn_get_search_fields_html() {
	require_once('owl_plugin.php');
	return op_get_search_fields_html();
}


/**
* Add OWL related SQL fields to the query (used by files/some.php)
*/
function fn_get_search_fields_sql() {
	require_once('owl_plugin.php');
	return op_get_search_fields_sql();
}



function fn_get_xrms_file_id(&$params) { 
	require_once('owl_plugin.php');
	return op_get_xrms_file_id($params);
}


/**
* This function is called by XRMS whenever the admin updates the databases
*/
function fn_owl_xrms_update($con) {
	require_once('owl_plugin.php');
	return op_owl_xrms_update($con);
}


/**
* Get Extra HTML rows for display in files/one.php
*/
function fn_get_one_file_html(&$params) {
	require_once('owl_plugin.php');
	return op_get_one_file_html($params);
}


/**
* Actual OWL_API functions are below.  See owl_plugin.php for documentation.
*/


function fn_add_file(&$params) {
	require_once('owl_plugin.php');
	return op_add_file($params);
}

function fn_upd_file(&$params) {
	require_once('owl_plugin.php');
	return op_upd_file($params);
}

function fn_delete_file(&$params) {
	require_once('owl_plugin.php');
	return op_delete_file($params);
}

function fn_add_folder(&$params) {
	require_once('owl_plugin.php');
	return op_add_folder($params);
}

function fn_delete_folder(&$params) {
	require_once('owl_plugin.php');
	return op_delete_folder($params);
}

function fn_download_file(&$params) { 
	require_once('owl_plugin.php');
	return op_download_file($params);
}

function fn_browse_files(&$params) {
	require_once('owl_plugin.php');
	return op_browse_files($params);
}

function fn_search_files_callback(&$params) {
	require_once('owl_plugin.php');
	return op_search_files_callback($params);
}

function fn_search_files(&$params) {
	require_once('owl_plugin.php');
	return op_search_files($params);
}

function fn_get_file_info(&$params) {
	require_once('owl_plugin.php');
	return op_get_file_info($params);
}



/*
// You can use this for adding new API fns
function fn_template() {
	global $owl_location;
	require_once($owl_location . 'OWL_API.php');

	if(is_array($params) && count($params) == 1) {
		$folder_info 		= $params[0];
	} else {
		echo "error in param count";
		return null;
	}
	$params =  OWL_Function($folder_info);
}
*/


/**
 * $Log: setup.php,v $
 * Revision 1.7  2005/12/19 23:20:24  daturaarutad
 * remove OWL menu
 *
 * Revision 1.6  2005/12/09 19:23:23  daturaarutad
 * add delete hook
 *
 * Revision 1.5  2005/11/09 22:31:00  daturaarutad
 * updated API to use named keys
 *
 * Revision 1.4  2005/10/24 21:53:29  daturaarutad
 * add hook to add external_id to XRMS files table
 *
 * Revision 1.3  2005/09/23 20:35:17  daturaarutad
 * add new hooks and fix old ones
 *
 * Revision 1.2  2005/04/28 18:49:07  daturaarutad
 * small tweak for search files
 *
 * Revision 1.1  2005/04/28 15:47:10  daturaarutad
 * new files
 *
 */
?>
