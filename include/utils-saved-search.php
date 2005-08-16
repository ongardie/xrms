<?php
/**
 * Common saved action functions file.
 *
 * $Id: utils-saved-search.php,v 1.2 2005/08/16 00:53:48 vanmer Exp $
 */

/**
 * 
 * This function adds a saved search item (or updates an existing item) based on parameters
 *
 * @param adodbconnection $con
 * @param string $saved_title with title of saved search (unique within on_what_table and user_id
 * @param integer $group_item used to group saved items together
 * @param string $on_what_table specifies what table this search is saved on
 * @param array $saved_data with data to save
 * @param string $saved_action is usually 'search', indicates what type of saved action this is
 * @param integer $user_id sets the user_id of the saved search, defaults to $session_user_id
 * @return integer $saved_id with id of saved search just created
 */ 
function add_saved_search_item($con, $saved_title, $group_item, $on_what_table, $saved_data, $saved_action='search',  $user_id=false) {
    global $session_user_id;
    if (!$user_id) $user_id=$session_user_id;
 
   $prev=get_saved_search_item($con, $on_what_table, $user_id, $saved_title);
    
    $rec = array();
    $rec['saved_title'] = $saved_title;
    $rec['group_item'] = round($group_item);
    $rec['on_what_table'] =$on_what_table;
    $rec['saved_action'] = $saved_action;
    $rec['user_id'] = $user_id;
    $rec['saved_data'] = addslashes(urlencode(serialize($saved_data)));
    
    if ($prev AND !$prev->EOF) {
        $upd = $con->GetUpdateSQL($prev, $rec, false, get_magic_quotes_gpc());
//        echo "UPDATING WITH $upd<p>";
        $con->execute($upd);
        $saved_id = $prev->fields['saved_id'];
    }
    else {
        $tbl = "saved_actions";
        $ins = $con->GetInsertSQL($tbl, $rec, get_magic_quotes_gpc());
        
        $con->execute($ins);
        $saved_id = $con->Insert_ID();
    }
    return $saved_id;
}

/**
 * 
 * This function adds a saved search item (or updates an existing item) based on parameters
 *
 * @param adodbconnection $con
 * @param string $on_what_table specifies what table to look for saved searches in
 * @param integer $user_id sets the user_id to find searches on
 * @param string $saved_title with title of saved search (unique within on_what_table and user_id)
 * @param integer $saved_id gets the saved_id under which the saved item is stored in the db
 * @param boolean $ignore_current ensures that the saved search titled 'Current' isn't visible
 * @param string $saved_action is usually 'search', indicates what type of saved action this is
 * @param boolean $as_menu restricts the fields returned to simply the the title and id, for use when displaying the saved searches as a menu
 * @return adodbrecordset $rst with results of search
 */ 
function get_saved_search_item($con,  $on_what_table, $user_id, $saved_title, $saved_id=false, $ignore_current=false, $saved_action=false, $as_menu=false) {
    if ($as_menu) { $fields="saved_title, saved_id"; } else $fields='*';
    $sql_saved = "SELECT $fields
            FROM saved_actions
            WHERE (user_id=$user_id
            OR group_item=1)
            AND on_what_table=" . $con->qstr($on_what_table) . "
            AND saved_status='a'";
    if ($saved_id) { $sql_saved .= " AND saved_id=$saved_id"; }
    if ($saved_title) { $sql_saved.=" AND saved_title=" .$con->qstr($saved_title); }
    if ($ignore_current) {
            $sql_saved.=" AND saved_title!='Current'";
    }
    if ($saved_action) {
            $sql_saved.=' AND saved_action=' .$con->qstr($saved_action);
    }

    $rst = $con->execute($sql_saved);
    if(!$rst) {
        db_error_handler($con, $sql_saved);
    }
    return $rst;
}

/**
  * This function marks a saved search as deleted
  *
  * @param adodbconnection $con
  * @param integer $saved_id with ID of saved search to delete
  * @param adodbrecordset $saved_rst with recordset of saved search to delete
  * @return boolean indicating success of update
**/
function delete_saved_search($con, $saved_id=false, $saved_rst=false) {
    if (!$saved_rst) { $saved_rst=get_saved_search_item($con, false, false, false, $saved_id); }
    if (!$saved_rst) return false;    
    if (check_user_role(false, $_SESSION['session_user_id'], 'Administrator') || $rst->fields['user_id'] == $session_user_id) {
            $rec = array();
            $rec['saved_status'] = 'd';

            $upd = $con->GetUpdateSQL($saved_rst, $rec, false, get_magic_quotes_gpc());
            $con->execute($upd);
           return true;
   } else return false;
}

/**
  * This function is used to load the initial _POST variable if a saved seach has been loaded
  *
  * @param adodbconnection $con
  * @param string $on_what_table indicating which table this search occurs on
  * @param integer $saved_id with database identifier of saved search to load variables for or delete
  * @param boolean $delete_saved deletes the saved search ireferenced in saved_id
  * @return null
**/
function load_saved_search_vars($con, $on_what_table, $saved_id, $delete_saved) {
    global $session_user_id;
    // check for saved search
    if($saved_id) {
        $rst=get_saved_search_item($con, $on_what_table, $session_user_id, false,  $saved_id);
        if($rst AND !$rst->EOF) {
            if($delete_saved) {
                delete_saved_search($con, $saved_id, $rst);
            }
            else {
                //load over POST
                $saved_data=unserialize(urldecode($rst->fields['saved_data']));
                if (!$saved_data) $saved_data=unserialize($rst->fields['saved_data']);
                $_POST = $saved_data;
                $day_diff = $_POST['day_diff'];
            }
        }
    }
}

/**
   * $Log: utils-saved-search.php,v $
   * Revision 1.2  2005/08/16 00:53:48  vanmer
   * - added urlencode to saved serialized data, to deal with \r\n vs \r issue with windows/unix on unserialize
   * - added urldecode to unserialize of saved search data (if that fails, try just unserializing to maintain backward
   * compatibility)
   *
   * Revision 1.1  2005/08/05 01:21:58  vanmer
   * - Initial revision of centralized functions for saved searches
   *
   *
**/
?>