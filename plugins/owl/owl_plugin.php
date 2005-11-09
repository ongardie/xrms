<?php
/*
 * owl_plugins.php
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
$owl_location = '/home/www/owl/intranet';


/**
*
*  These functions are hooks, but not part of the external OWL API
*
*/

/**
* Add the extra owl search fields to the search bar in XRMS
*
* @return string extra HTML to add to the files/some.php search form
*/
function op_get_search_fields_html() {

	getGlobalVar($owl_search_string, 'owl_search_string');

return "
<tr>
	<td colspan=4 class=widget_label>" . _("Search Contents of Files") . "
	</td>
</tr>

<tr>
	<td colspan=4 class=widget>
		<input type=text name=owl_search_string value=\"$owl_search_string\" size=50>
	</td>
</tr>
";

}


/**
* Add OWL related SQL fields to the query (used by files/some.php)
*
* @return string extra fields to add to files/some.php search query (external_id)
*/
function op_get_search_fields_sql() {
	return "external_id, ";
}




/**
* Get the XRMS file_id of a file given its OWL file_id
*
* @param array ('external_id' => 'the OWL file id')
*
* @return array ('file_id' => 'the XRMS file id');
*/
function op_get_xrms_file_id(&$params) { 
	global $owl_location; 

    require_once('../include-locations.inc');
    global $include_directory;
    require_once($include_directory . 'vars.php');
    require_once($include_directory . 'utils-database.php');
    require_once($include_directory . 'utils-files.php');


    $xcon = get_xrms_dbconnection();

    $rst = get_file_records($xcon, array('external_id' => $params['external_id']));

    if($rst) {
        $row = $rst->GetRows();
        $params['file_id'] = $row[0]['file_id'];
    }
}


/** 
* Get Extra HTML rows for display in files/one.php
*   
* @param array ('pager' => $pager);
*   
* @return array ('pager' => 'pager object with modify callback set');
*/
function op_search_files_callback(&$params) { 
	global $owl_location; 

    require_once('../include-locations.inc');
    global $include_directory;
    require_once($include_directory . 'vars.php');
    require_once($include_directory . 'utils-database.php');
    require_once($include_directory . 'utils-files.php');

	if(is_array($params) && count($params) == 1) {
		$pager 		= $params['pager'];
	} else {
		echo "error in param count";
		return null;
	}

	// only set the callback (and therefore use the OWL search) when searching within files.
	getGlobalVar($owl_search_string, 'owl_search_string');
	if($owl_search_string) {
		$pager->AddModifyDataCallback('OWLFileDataCallback');
	}
	$params['pager'] = $pager;

}
/** 
* Get Extra HTML rows for display in files/one.php
*   
* @param array all db rows
*   
* @return array all db rows filtered by OWL search
*/
function OWLFileDataCallback($rows) {
    global $msg;
    $file_plugin_params = array('rows' => $rows);

    do_hook_function('file_search_files', $file_plugin_params);
    if($file_plugin_params['error_status']) {
        $msg = $file_plugin_params['error_text'];
    }

    return $file_plugin_params['rows'];
}





/** 
* This function is called by XRMS whenever the admin updates the databases
*   
* @param object XRMS connection object
*   
* @return none
*/
function op_owl_xrms_update($con) {

    global $http_site_root;

    $columns=$con->MetaColumns('files', true);

    if(!array_key_exists('EXTERNAL_ID',$columns)) {

        $update_sql = "ALTER TABLE files ADD external_id INT(11) DEFAULT 0";

        $rst=$con->execute($update_sql);

        if (!$rst) db_error_handler($con, $update_sql);
    }

	$tables=$con->MetaTables('TABLES');

	// owl_folders table
	if(!array_key_exists('folders',$tables)) {

        $create_sql = "
						CREATE TABLE folders (
  							id int(4) NOT NULL auto_increment,
  							name varchar(255) NOT NULL default '',
  							parent_id int(4) NOT NULL default '0',
  							description text NOT NULL,
  							security varchar(5) NOT NULL default '',
  							groupid int(4) NOT NULL default '0',
  							creatorid int(4) NOT NULL default '0',
  							`password` varchar(50) NOT NULL default '',
  							smodified datetime default NULL,
  							on_what_table varchar(100) NOT NULL default '',
  							on_what_id int(11) NOT NULL default '0',
  							external_id int(11) NOT NULL default '0',
  							PRIMARY KEY  (id),
  							UNIQUE KEY folderid_index (id)
						) ENGINE=MyISAM DEFAULT CHARSET=latin1;";


        $rst=$con->execute($create_sql);

        if (!$rst) db_error_handler($con, $create_sql);
    }

}


/**
* Get Extra HTML rows for display in files/one.php
*
* @param array ('file_info' => $file_info)
*
* @return array ('file_one_html' => '<html>', 
*				 'file_one_html_post' => 'html after the one.php form', 
* 				 'file_one_extra_download_args' => '&selected_version=N')
*/
function op_get_one_file_html(&$params) {
	global $http_site_root;
	global $owl_location;
	require_once($owl_location . '/lib/OWL_API.php');

	if(is_array($params) && count($params) == 1) {
		$file_info 		= $params['file_info'];
	} else {
		echo "error in param count";
		return null;
	}

	getGlobalVar($return_url, 'return_url');
	getGlobalVar($selected_version, 'selected_version');


	$params['file_one_html'] = "
            	<tr>
                	<td class=widget_label_right>" . _("Version") . "</td>
                	<td class=widget_content_form_element>{$file_info['selected_version']}</td>
					<input type=hidden name=\"selected_version\" value=\"{$file_info['selected_version']}\">
            	</tr>
            	<tr>
                	<td class=widget_label_right>" . _("Last Modified") . "</td>
                	<td class=widget_content_form_element>{$file_info['last_modified_on']}</td>
					<input type=hidden name=\"selected_version\" value=\"{$file_info['last_modified_on']}\">
            	</tr>

	";

	// Owl only returns versions if no version is passed, so re-get that record
  	$all_versions =  OWL_Get_File_Info($file_info);

	$file_versions = array();

	if(is_array($all_versions['versions'])) {
		foreach($all_versions['versions'] as $version) {
		   	$file_version =  OWL_Get_File_Info($file_info, $version);
			$file_version['link'] = "<a href=\"$http_site_root/files/one.php?file_id={$file_version['file_id']}&selected_version=$version&return_url=$return_url\">{$file_version['file_pretty_name']}</a>";
			$file_version['file_size'] = pretty_filesize($file_version['file_size']);

		   	$file_versions[] =  $file_version;
		}
	} 



	// Pager for other versions
	global $include_directory;
	
	require_once($include_directory . 'vars.php');
	require_once($include_directory . 'adodb/adodb.inc.php');
	
	
	require_once($include_directory . 'adodb-params.php');
	require_once($include_directory . 'classes/Pager/GUP_Pager.php');
	require_once($include_directory . 'classes/Pager/Pager_Columns.php');
	
   	
	$con = get_xrms_dbconnection();
	
	$columns = array();
	$columns[] = array('name' => _("Name"), 'index_data' => 'link');
	$columns[] = array('name' => _("Filename"), 'index_data' => 'file_name');
	$columns[] = array('name' => _("Description"), 'index_data' => 'file_description');
	$columns[] = array('name' => _("Created"), 'index_data' => 'entered_at');
	$columns[] = array('name' => _("Modified"), 'index_data' => 'last_modified_on');
	$columns[] = array('name' => _("Size"), 'index_data' => 'file_size');
	$columns[] = array('name' => _("Version"), 'index_data' => 'selected_version', 'default_sort' => 'asc'); 
	
	$pager = new GUP_Pager($con, null, $file_versions, _('All File Versions'), 'FileForm', 'FilePager', $columns, false, true);
	
	$pager->AddEndRows($endrows);                                                                                         
	$pager_html = $pager->Render($system_rows_per_page);
	$params['file_one_html_post'] = <<<END
	<form action="one.php" name="FileForm">
		<input type="hidden" name="file_id" value="{$file_info['file_id']}">
		<input type="hidden" name="return_url" value="$return_url">
		<input type="hidden" name="selected_version" value="$selected_version">
		$pager_html
	</form>
END;
   	$con->close();
	
	$params['file_one_extra_download_args'] = "selected_version={$file_info['selected_version']}";
}



/**
* OWL_API functions below
*/



/** 
* Add a file to OWL
*   
* @param array ('file_field_name' => $file_field_name, 'file_info' => $file_info);
*   
* @return array ('external_id' => 'The OWL file ID', 
*                'other owl_fields' => 'other OWL file fields', 
*                'error_status' => 'boolean indicating error status',
*				 'error_text' => 'text of the error message')
*/
function op_add_file(&$params) {
	global $owl_location;
	require_once($owl_location . '/lib/OWL_API.php');

	if(is_array($params) && count($params) == 2) {
		$file_field_name = $params['file_field_name'];
		$file_info 		= $params['file_info'];

		// Simply read the parent_id from the session (It is only ever set in the Browse function)
		if($file_info['on_what_table'] && $file_info['on_what_id']) {
			$file_info['parent_id']	= $_SESSION[$file_info['on_what_table']][$file_info['on_what_id']]['owl_parent_id'];
		} else {
			$file_info['parent_id'] = $_SESSION['owl_parent_id'];
		}
	} else {
		echo "error in param count";
		return null;
	}

	$file_info =  OWL_Add_File($file_field_name, $file_info);

	$params['file_info'] = $file_info;
	$params['file_stored'] = $file_info['file_stored'];

	global $owl_error;
	$params = array_merge($params, $owl_error);

}


/** 
* Update a file's information or file in OWL
*   
* @param array ('file_field_name' => $file_field_name, 'file_info' => $file_info);
*   
* @return array ('external_id' => 'The OWL file ID', 
*                'other owl_fields' => 'other OWL file fields', 
*                'error_status' => 'boolean indicating error status',
*                'error_text' => 'text of the error message')
*/
function op_upd_file(&$params) {
	global $owl_location;
	require_once($owl_location . '/lib/OWL_API.php');

	if(is_array($params) && count($params) == 2) {
		$file_field_name = $params['file_field_name'];
		$file_info 		= $params['file_info'];

		// Simply read the parent_id from the session (It is only ever set in the Browse function)
		if($file_info['on_what_table'] && $file_info['on_what_id']) {
			$file_info['parent_id']	= $_SESSION[$file_info['on_what_table']][$file_info['on_what_id']]['owl_parent_id'];
		} else {
			$file_info['parent_id'] = $_SESSION['owl_parent_id'];
		}


	} else {
		echo "error in param count";
		return null;
	}

	$file_info =  OWL_Upd_File($file_field_name, $file_info);
	$params['file_info'] = $file_info;
	$params['file_stored'] = $file_info['file_stored'];

	global $owl_error;
	$params = array_merge($params, $owl_error);
}



/** 
* Delete a file in OWL
*   
* @param array (0 => $file_field_name, 1 => $file_info);
*   
* @return array ('error_status' => 'boolean indicating error status',
*                'error_text' => 'text of the error message')
*/
function op_delete_file(&$params) {
	global $owl_location;
	require_once($owl_location . '/lib/OWL_API.php');

	if(is_array($params) && count($params) > 1) {
		$file_info 		= $params['file_info'];

	} else {
		echo "error in param count";
		echo "<pre>";
		print("COUNT: " . count($params) . "\n");
		print_r($file_info);
		echo "</pre>";
		return null;
	}
	global $owl_error;
	$params =  OWL_Delete_File($file_info);
	$params = array_merge($params, $owl_error);
}


/** 
* Add a Folder in OWL
*   
* @param array ('folder_info' => $folder_info);
*   
* @return array ('error_status' => 'boolean indicating error status',
*                'error_text' => 'text of the error message')
*/
function op_add_folder(&$params) {
	global $owl_location;
	require_once($owl_location . '/lib/OWL_API.php');

	if(is_array($params) && count($params) == 1) {
		$folder_info 		= $params['folder_info'];

		// Simply read the parent_id from the session (It is only ever set in the Browse function)
		if($folder_info['on_what_table'] && $folder_info['on_what_id']) {
			$folder_info['parent_id']	= $_SESSION[$folder_info['on_what_table']][$folder_info['on_what_id']]['owl_parent_id'];
		} else {
			$folder_info['parent_id'] = $_SESSION['owl_parent_id'];
		}
	} else {
		echo "error in param count";
		return null;
	}
	global $owl_error;
	$folder_info =  OWL_Add_Folder($folder_info);
	$params['folder_info'] = $folder_info;
	$params = array_merge($params, $owl_error);
}


/** 
* Download a file in OWL (send to the browser)
*   
* @param array ('file_info' => $file_info);
*   
* @return array ('error_status' => 'boolean indicating error status',
*                'error_text' => 'text of the error message')
*/
function op_download_file(&$params) { 
	global $owl_location; 
	require_once($owl_location . '/lib/OWL_API.php'); 

	getGlobalVar($selected_version, 'selected_version');


	if(is_array($params) && count($params) == 1) {
		$file_info 		= $params['file_info'];
	} else {
		echo "error in param count";
		return null;
	}

	global $owl_error;
	$params =  OWL_Download_File($file_info['external_id'], $selected_version);
	$params = array_merge($params, $owl_error);
}

/** 
* Browse files (pager) 
*   
* @param array ('rst' => $rst, 'on_what_table' => $on_what_table, 'on_what_id' => $on_what_id);
*   
* @return array ('file_rows' => 'pager content',
*				 'error_status' => 'boolean indicating error status',
*                'error_text' => 'text of the error message')
*/
function op_browse_files(&$params) {
	global $http_site_root;
	global $owl_location;
	require_once($owl_location . '/lib/OWL_API.php');
	require_once('folders_lib.php');

	if(is_array($params) && count($params) == 3) {

		$rst = $params['rst'];
		$on_what_table = $params['on_what_table'];
		$on_what_id = $params['on_what_id'];

		if($on_what_table) {
			$return_url = "/$on_what_table/one.php?" . make_singular($on_what_table) . "_id=".$on_what_id."&owl_parent_id=$owl_parent_id";
		} else {
			$return_url = current_page();
		}


		$file_data = $rst->GetArray();

		$folder_data = GetFolders($on_what_table, $on_what_id);

		
		/* 	The lowdown on owl_parent_id:
			This variable tracks where in the hierarchy a user is.
			A seperate copy is kept in the user's session for 'regular use', which I suppose is their 'home directory'
			This is the only place it's set by a form var, otherwise it's read from session
		*/

		$owl_parent_id  = $_GET['owl_parent_id'];

		if(!$owl_parent_id) {
			// Simply read the parent_id from the session (It is only ever set in the Browse function)
			if($on_what_table && $on_what_id) {
				$owl_parent_id	= $_SESSION[$on_what_table][$on_what_id]['owl_parent_id'];
			} else {
				$owl_parent_id = $_SESSION['owl_parent_id'];
			}
		}

		if(!$owl_parent_id) $owl_parent_id = 0;

		$owl_data = OWL_Browse_Files($owl_parent_id, $file_data, $folder_data);
		//print("<pre>");
		//print_r($file_data);
		//print("</pre>");

		// ADDED By BOZZ
		global $default;


		// Set up the pager rows with folder and file information
		foreach($owl_data as $k => $owl_row) {

			if($owl_row['is_folder']) {
				
				$owl_data[$k]['file_size'] = '';
				// at some point this will be a link to current page + owl_parent_id=$folder_id
				$folder_link = $http_site_root . current_page('owl_parent_id=' . $owl_data[$k]['id']);
				//if('?' != substr($folder_link, -1)) $folder_link .= '?';

				$owl_data[$k]['file_pretty_name'] = "<img src=\"$default->owl_graphics_url/$default->system_ButtonStyle/icon_filetype/folder_closed.gif\"></img>&nbsp;<a href='$folder_link'>{$owl_data[$k]['name']}</a>";

			} else {

				$owl_data[$k]['file_size'] = pretty_filesize($owl_row['file_size']);
				// Set up the icon
				$choped = split("\.", $owl_data[$k]['file_name']);
				$pos = count($choped);
				if ( $pos > 1 )
				{
					$ext = strtolower($choped[$pos-1]);
					$sDispIcon = $ext . ".gif";
				}
				else
				{
					$sDispIcon = "NoExtension";
				}
								
				if (($ext == "gz") && ($pos > 2))
				{
					$exttar = strtolower($choped[$pos-2]);
					if (strtolower($choped[$pos-2]) == "tar")
						$ext = "tar.gz";
				}
				
				//if ($sql->f("url") == "1")
				//{
				//print("<img src=\"$default->owl_graphics_url/$default->sButtonStyle/icon_filetype/url.gif\" border=\"0\" alt=\"\"></img>");
				//}
				//else
				//{
				
				if (!file_exists("$default->owl_fs_root/graphics/$default->system_ButtonStyle/icon_filetype/$sDispIcon"))
				{
					$sDispIcon = "file.gif";
				}
				//$owl_data[$k]['file_pretty_name'] = "<img src=\"$default->owl_graphics_url/$default->sButtonStyle/icon_filetype/$sDispIcon\" border=\"0\" alt=\"\"></img>&nbsp;";
				//}
				
				$owl_data[$k]['file_pretty_name'] = "<img src=\"$default->owl_graphics_url/$default->system_ButtonStyle/icon_filetype/$sDispIcon\" border=\"0\" alt=\"\"></img>&nbsp;<a href='$http_site_root/files/one.php?file_id={$owl_data[$k]['file_id']}&return_url=". current_page() . "' alt=\"File Name: " .$owl_data[$k]['file_name'] . "\" title=\"File Name: " .$owl_data[$k]['file_name'] . "\">" . $owl_data[$k]['file_pretty_name'] . '</a>';
				//print("<pre>");
				//print_r($owl_data);
				//print_r($default);
				//print("</pre>");
			}
		}

		// Set up the pager to display the current dir's data
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

        $pager = new GUP_Pager($con, null, $owl_data, $caption, 'Files_Sidebar_Form', 'Files_Sidebar', $columns, false, true);

        $colspan = count($columns);



        $new_file_button=render_create_button(_('Add File'), 'button', "javascript:location.href='$http_site_root/files/new.php?on_what_table=$on_what_table&on_what_id=$on_what_id&return_url=$return_url'");
        $new_folder_button=render_create_button(_('Add Folder'), 'button', "javascript:location.href='$http_site_root/plugins/owl/new_folder.php?on_what_table=$on_what_table&on_what_id=$on_what_id&return_url=$return_url'");


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
                    <td colspan=4 class=widget_content_form_element>
                            $new_file_button
                            $new_folder_button
                    </td>
				</tr> ";


        $pager->AddEndRows($endrows);

        $params['file_rows'] = "<form name=Files_Sidebar_Form method=POST>
			<input type=hidden name=contact_id value=$contact_id>
			<input type=hidden name=company_id value=$company_id>
			<input type=hidden name=division_id value=$division_id>
		" . $pager->Render() . "
		</form>
		";

	} else {
		echo "error in param count";
	}

	global $owl_error;
	$params = array_merge($params, $owl_error);
}


/** 
* Search for files in OWL
*   
* @param array (0 => $file_data);
*   
* @return array (0	=> 'array of matching files',
*				'error_status' => 'boolean indicating error status',
*               'error_text' => 'text of the error message')
*/
function op_search_files(&$params) {

	getGlobalVar($owl_search_string, 'owl_search_string');

	global $owl_location;
	require_once($owl_location . '/lib/OWL_API.php');

	if(is_array($params) && count($params) == 1) {

        $rows = $params['rows'];

	} else {
		echo "error in param count";
		return null;
	}
	$params['rows'] =  OWL_Search_Files($owl_search_string, $rows);
	global $owl_error;
	$params = array_merge($params, $owl_error);
}


/** 
* Get a file's info from OWL
*   
* @param array ('file_info' => $file_info);
*   
* @return array ('file_info' => 'file info containing OWLs data',
*				 'error_status' => 'boolean indicating error status',
*                'error_text' => 'text of the error message')
*/
function op_get_file_info(&$params) {
	global $owl_location;
	require_once($owl_location . '/lib/OWL_API.php');

	if(is_array($params) && count($params) == 1) {
		$file_info 		= $params['file_info'];
	} else {
		echo "error in param count";
		return null;
	}

	getGlobalVar($selected_version, 'selected_version');

	$params['file_info'] =  OWL_Get_File_Info($file_info, $selected_version);

	$params['file_info']['file_size'] = pretty_filesize($params['file_info']['file_size']);
	global $owl_error;
	$params = array_merge($params, $owl_error);
}


/*
// You can use this for adding new API fns
function op_template(&$params) {
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
 * $Log: owl_plugin.php,v $
 * Revision 1.2  2005/11/09 22:31:00  daturaarutad
 * updated API to use named keys
 *
 * Revision 1.1  2005/11/09 19:23:51  daturaarutad
 * Main interface to OWL_API.php
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
