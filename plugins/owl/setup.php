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

global $owl_location;
$owl_location = '/www/owl/intranet/';


function xrms_plugin_init_owl() {
    global $xrms_plugin_hooks;
    $xrms_plugin_hooks['menuline']['owl'] = 'owl_menu';
    $xrms_plugin_hooks['file_add_file']['owl'] = 'fn_add_file';
    $xrms_plugin_hooks['file_add_folder']['owl'] = 'fn_add_folder';
    $xrms_plugin_hooks['file_get_file_info']['owl'] = 'fn_get_file_info';
    $xrms_plugin_hooks['file_browse_files']['owl'] = 'fn_browse_files';
    $xrms_plugin_hooks['file_search_files']['owl'] = 'fn_search_files';
    $xrms_plugin_hooks['file_get_search_fields_html']['owl'] = 'fn_get_search_fields_html';
}


function owl_menu() {

    global $http_site_root;

    //Add Demo link to upper menu
    echo "&nbsp;<a href='$http_site_root/plugins/owl/owl_main.php'>Owl</a>&nbsp;&bull;\n";
}


function fn_add_file(&$params) {
	global $owl_location;
	require_once($owl_location . 'OWL_API.php');


	if(is_array($params) && count($params) == 2) {
		$file_field_name = $params[0];
		$file_info 		= $params[1];

		if($file_info['on_what_table'] && $file_info['on_what_id']) {
			$file_info['parent_id']	= $_SESSION[$file_info['on_what_table']][$file_info['on_what_id']]['owl_parent_id'];
		} else {
			$file_info['parent_id'] = $_SESSION['owl_parent_id'];
		}


	} else {
		echo "error in param count";
		return null;
	}
	$params =  OWL_Add_File($file_field_name, $file_info);
}

function fn_add_folder(&$params) {
	global $owl_location;
	require_once($owl_location . 'OWL_API.php');

	if(is_array($params) && count($params) == 1) {
		$folder_info 		= $params[0];

		if($folder_info['on_what_table'] && $folder_info['on_what_id']) {
			$folder_info['parent_id']	= $_SESSION[$folder_info['on_what_table']][$folder_info['on_what_id']]['owl_parent_id'];
		} else {
			$folder_info['parent_id'] = $_SESSION['owl_parent_id'];
		}
	} else {
		echo "error in param count";
		return null;
	}
	$params =  OWL_Add_Folder($folder_info);
}
function fn_get_file_info(&$params) { 
	global $owl_location; 
	require_once($owl_location . 'OWL_API.php'); 
	$params =  OWL_Get_File_Info($params);
}

function fn_browse_files(&$params) {
	global $owl_location;
	require_once($owl_location . 'OWL_API.php');

	if(is_array($params) && count($params) == 1) {
		$rst = $params[0];

		$file_data = $rst->GetArray();

		$owl_parent_id  = $_GET['owl_parent_id'];

		if(!$owl_parent_id) $owl_parent_id = 0;

		$owl_data =  OWL_Browse_Files($owl_parent_id, $file_data);

		global $http_site_root;
		global $on_what_table;
		global $on_what_id;


		// manipulate the $data so that buttons get pushed into a single row, etc.
		// $data['owl_actions'] = $data['button1'] . $data['button2'] . etc;

		if($owl_parent_id) {
			$row = array();
			$row['name'] = '.. (folder)';
			$row['id'] = 0;
			$row['is_folder'] = true;
			$owl_data[] = $row;

		}

		foreach($owl_data as $k => $owl_row) {

			if($owl_row['is_folder']) {
				
				$owl_data[$k]['file_size'] = '';
				// at some point this will be a link to current page + owl_parent_id=$folder_id
				//$vars = array('owl_parent_id' => $owl_data[$k]['id']);
				$vars = array('owl_parent_id' => 23);
				$folder_link = $http_site_root . current_page('owl_parent_id=' . $owl_data[$k]['id']);
				if('?' != substr($folder_link, -1)) $folder_link .= '?';


				$owl_data[$k]['file_pretty_name'] = "<a href='$folder_link&owl_parent_id={$owl_data[$k]['id']}'>{$owl_data[$k]['name']}</a>";

			} else {

				$owl_data[$k]['file_size'] = pretty_filesize($owl_row['file_size']);
				$owl_data[$k]['file_pretty_name'] = "<a href='$http_site_root/files/one.php?file_id={$owl_data[$k]['file_id']}&return_url=". current_page() ."'>" . $owl_data[$k]['file_pretty_name'] . '</a>';
			}
		}


		require_once('../include-locations.inc');
		global $include_directory;
		require_once($include_directory . 'vars.php');
		require_once($include_directory . 'classes/Pager/GUP_Pager.php');


        $columns=array();
        $columns[] = array('name' => 'Name', 'index_calc' => 'file_pretty_name');
        $columns[] = array('name' => 'Size', 'index_calc' => 'file_size');
        $columns[] = array('name' => 'Owner', 'index_calc' => 'username');
        $columns[] = array('name' => 'Date', 'index_calc' => 'entered_at');

        $caption= _('Files');

        $pager = new GUP_Pager($con, null, $owl_data, $caption, $form_id, 'Files_Sidebar', $columns, false, true);

        $colspan = count($columns);

        $new_file_button=render_create_button(_('Add File'), 'submit');
        $new_folder_button=render_create_button(_('Add Folder'), 'submit');

		if($on_what_table) {
			$return_url = "/$on_what_table/one.php?" . make_singular($on_what_table) . "_id=".$on_what_id;
		} else {
			$return_url = current_page();
		}

		// not sure about this, but let's store the owl_parent_id in the session for add new file/folder
		if($owl_parent_id) {
			if($on_what_table && $on_what_id) {
        		$_SESSION[$on_what_table][$on_what_id]['owl_parent_id'] =  $owl_parent_id;
			} else {
        		$_SESSION['owl_parent_id'] =  $owl_parent_id;
			}
		} else {
	
      		unset($_SESSION[$on_what_table][$on_what_id]['owl_parent_id']);
       		unset($_SESSION['owl_parent_id']);
		}

        $endrows =  "
                <tr>
                <form action='".$http_site_root."/files/new.php' method='post'>
                    <td class=widget_content_form_element>
                            <input type=hidden name=on_what_table value='$on_what_table'>
                            <input type=hidden name=on_what_id value='$on_what_id'>
                            <input type=hidden name=return_url value='$return_url'>
                            $new_file_button
                    </td>
                </form>
                <form action='".$http_site_root."/plugins/owl/new_folder.php' method='post'>
                    <td class=widget_content_form_element>
                            <input type=hidden name=on_what_table value='$on_what_table'>
                            <input type=hidden name=on_what_id value='$on_what_id'>
                            <input type=hidden name=return_url value='$return_url'>
                            $new_folder_button
                    </td>
                </form>
				</tr> ";


        $pager->AddEndRows($endrows);

        $params['file_rows'] = $pager->Render();

	} else {
		echo "error in param count";
	}
}


// Search

// how do we add the extra owl search fields to the search bar in xrms?
// You can use this for adding new API fns
function fn_get_search_fields_html() {

return "
<tr>
	<td colspan=4 class=widget_label>" . _("Search Contents of Files") . "
	</td>
</tr>

<tr>
	<td colspan=4 class=widget>
		<input type=text name=owl_search_string size=50>
	</td>
</tr>
";

}


/**
* 	You'll be needing a files array that ya got from the XRMS search that's already ACL filtered.
*	You also might need to run an OWL search that filters on that result set.
*
*/
function fn_search_files(&$params) {

	getGlobalVars($owl_search_string, 'owl_search_string');

	global $owl_location;
	require_once($owl_location . 'OWL_API.php');

	if(is_array($params) && count($params) == 1) {

        $file_data = $params[0];

	} else {
		echo "error in param count";
		return null;
	}
	$params =  OWL_Search_Files($owl_search_string, $file_data);
}

// You can use this for adding new API fns
function fn_template() {
/*
	global $owl_location;
	require_once($owl_location . 'OWL_API.php');

	if(is_array($params) && count($params) == 1) {
		$folder_info 		= $params[0];
	} else {
		echo "error in param count";
		return null;
	}
	$params =  OWL_Function($folder_info);
	*/
}


/**
 * $Log: setup.php,v $
 * Revision 1.1  2005/04/28 15:47:10  daturaarutad
 * new files
 *
 */
?>
