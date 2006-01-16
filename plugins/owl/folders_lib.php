<?php
/**
 * Form for creating a new folder
 *
 * $Id: folders_lib.php,v 1.3 2006/01/16 22:59:16 daturaarutad Exp $
 */


// fix the 1=1 bit to use $where + join()
function GetFolders($on_what_table, $on_what_id, $extra_where = '') {

	$con = get_xrms_dbconnection();

    $where = array();
	if($on_what_table && $on_what_id) {
        $where[] = "on_what_table = '$on_what_table'";
        $where[] = "on_what_id = '$on_what_id'";
	}

    if($extra_where) {
        $where[] = $extra_where;
    }
    if($where) {
        $where_sql = 'WHERE ' . join(" AND ", $where);
    }

	$sql = "SELECT * FROM folders $where_sql";

    //echo $sql;

    $rst = $con->execute($sql);

	$return = array();

    if(!$rst) {
        db_error_handler($con, $sql);
    } else {
        while(!$rst->EOF) {
            $return[] = $rst->fields;
			$rst->MoveNext();
		}
	}

	return $return;
}


/** 
* Build a string path given a folder id (requires a walk up the folder tree)
*
* @param array list of folder records
* @param integer owl parent id
* @param string return_url minus &owl_parent_id=N
*
* @return string full path
*/
function BuildFolderPath($folder_data, $owl_parent_id, $return_url_base) {

    $path = array();
    $current_folder = '';

    BuildFolderPathRecurse($folder_data, $owl_parent_id, $path);

    // Build a path for "Files in" ... 
	$full_path = '/';
    if($path) {
		$path = array_reverse($path);

		array_shift($path);
		array_shift($path);
		array_shift($path);

		foreach($path as $folder) {
			$url = $return_url_base . get_url_seperator($return_url_base) . "owl_parent_id={$folder['external_id']}";

			$full_path .= "<a href=\"$url\">{$folder['name']}</a>/";
        	$return['current_folder'] = $folder['name'];
		}
    }
    $return['path'] = $full_path;

    return $return;
}

/** Recursive function that walks up the folder tree
*
* @param array list of folder records
* @param integer owl parent id
* @param string reference to path string
*/
function BuildFolderPathRecurse($folder_data, $owl_parent_id, &$path) {
    $folders = GetFolders(null, null, "external_id = $owl_parent_id");
    if($folders) {
        $folder = $folders[0];

		$path[] = $folder;

        if($folder['parent_id']) {
            BuildFolderPathRecurse($folder_data, $folder['parent_id'], $path);
        }
    }
}


/**
* Returns the ID for the top level folder for a company/activity/etc or a user (if no on_what_table/on_what_id provided)
*
* in OWL we have things like: /xrms/companies/23, /xrms/activities/7, /xrms/users/username1
*
*/
function GetEntityFolderID($on_what_table, $on_what_id) {

    global $owl_error;

    $con = get_xrms_dbconnection();

    if(!$on_what_id) $on_what_id = 0;


    // Look for subfolders and create them if they don't exist 
    // (/xrms/companies/23, /xrms/activities/7, /xrms/users/username1, etc.)
    if(!$parent_id) {

        // this function takes care of creation of /xrms/.
        $root_folder_id = GetRootFolderOWLID();

        if(!$root_folder_id) {
            $folder_info = array();
            $folder_info['parent_id'] = 1;  // 1 is owl root.
            $folder_info['name'] = 'xrms';

            $folder_info =  OWL_Add_Folder($folder_info);

            if($owl_error['error_status']) {
                //$msg = $folder_info['error_text'];
                return false;
            } 


            $folder_info['on_what_table'] =  'xrms';

            $table = 'folders'; 
            $sql = $con->GetInsertSQL($table, $folder_info, get_magic_quotes_gpc());

            $rst = $con->execute($sql);
            if(!$rst) {
                db_error_handler($con, $sql);
            } else {
                $root_folder_id = GetRootFolderOWLID();
	        }
        } else {
            //echo "/xrms/ folder ID: $root_folder_id<br>";
        }

        // check for /xrms/users or /xrms/contacts/ 
        if($on_what_table) {
            $entity = $on_what_table;
        } else {
            $entity = 'users';
        }
        //echo "look for /xrms/$entity<br>";
        $entity_folder = GetFolders(null, null, "name = '$entity' AND parent_id = $root_folder_id");

        if(!$entity_folder) {
            //echo "/xrms/$entity not there, creating it under $root_folder_id<br>";


            // create /xrms/companies if doesn't exist
            $folder_info = array();
            $folder_info['parent_id'] = $root_folder_id;
            $folder_info['name'] = $entity;

            $folder_info =  OWL_Add_Folder($folder_info);

            if($owl_error['error_status']) {
                //$msg = $folder_info['error_text'];
                return false;
            } 


            $table = 'folders'; 
            $sql = $con->GetInsertSQL($table, $folder_info, get_magic_quotes_gpc());

            //echo $sql;


            $rst = $con->execute($sql);
            if(!$rst) {
                db_error_handler($con, $sql);
            } else {
                $entity_folder = GetFolders(null, null, "name = '$entity' AND parent_id = $root_folder_id");
	        }


        }
        if('users' == $entity) {
            $sub_entity_name = $_SESSION['username'];
            $extra_where = "name = '$sub_entity_name' AND parent_id = {$entity_folder[0]['external_id']}";
        } else {
            $sub_entity_name = $on_what_id;
            $extra_where = "name = '$sub_entity_name' AND parent_id = {$entity_folder[0]['external_id']}";
        }

        //echo "Sub entity check: $extra_where<br>";

        $sub_entity_folder = GetFolders(null, null, $extra_where);


        if(!$sub_entity_folder) {
            //echo "/xrms/$entity/$sub_entity_name not there, creating it<br>";

             // create /xrms/companies if doesn't exist
            $folder_info = array();
            $folder_info['parent_id'] = $entity_folder[0]['external_id'];
            $folder_info['name'] = $sub_entity_name;

            $folder_info['on_what_table'] = $on_what_table;
            $folder_info['on_what_id'] = $on_what_id;

            $folder_info = OWL_Add_Folder($folder_info);

            if($owl_error['error_status']) {
                //$msg = $folder_info['error_text'];
                return false;
            } 


            $table = 'folders'; 
            $sql = $con->GetInsertSQL($table, $folder_info, get_magic_quotes_gpc());

            //echo $sql.'<br>';

            $rst = $con->execute($sql);
            if(!$rst) {
                db_error_handler($con, $sql);
            } else {
                $sub_entity_folder = GetFolders(null, null, $extra_where);
	        }
        }

        $parent_id = $sub_entity_folder[0]['external_id'];
    } else {
        //echo "returning session parent_id $parent_id<br>";
    }
    return $parent_id;
}


/**
* We use a special convention of setting on_what_table='xrms' to use as a placeholder for the root folder.
*/
function GetRootFolderOWLID() {

    $con = get_xrms_dbconnection();

	$sql = "SELECT external_id FROM folders where on_what_table = 'xrms'";

    $rst = $con->execute($sql);

    if(!$rst) {
        db_error_handler($con, $sql);
    } else {
        if(!$rst->EOF) {
            $folder_id = $rst->fields['external_id'];
		}
	}
    return $folder_id;
}



/**
 * $Log: folders_lib.php,v $
 * Revision 1.3  2006/01/16 22:59:16  daturaarutad
 * change way path is built to add links to Files in: in browser
 *
 * Revision 1.2  2005/12/09 19:24:06  daturaarutad
 * add functions GetRootFolderOWLID() and GetEntityFolderID()
 *
 * Revision 1.1  2005/11/09 19:24:15  daturaarutad
 * folder functions
 *
 */
?>
