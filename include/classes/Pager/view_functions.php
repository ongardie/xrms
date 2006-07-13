<?php
/**
 * Pager View API
 *
 * This file contains a set of utility functions for saving and retrieving views for a GUP_Pager and
 * Pager_Columns object.
 *
 * $Id: view_functions.php,v 1.2 2006/07/13 00:24:26 vanmer Exp $
 */

function initViews($con) {
//run to ensure that view data tables are available
    $table_name="pager_saved_view";

    $table_list = list_db_tables($con);

    if (array_search($table_name, $table_list)===false) {
    
        $table_fields=array();
        $table_fields[]=array('NAME'=>'pager_saved_view_id','TYPE'=>'I','SIZE'=>'','NOTNULL'=>'NOTNULL','KEY'=>'KEY','AUTOINCREMENT'=>'AUTOINCREMENT');
        $table_fields[]=array('NAME'=>'user_id', 'TYPE'=>'I','SIZE'=>'','NOTNULL'=>'NOTNULL','DEFAULT'=>0);
        $table_fields[]=array('NAME'=>'pager_name','TYPE'=>'C','SIZE'=>255);
        $table_fields[]=array('NAME'=>'view_data','TYPE'=>'X');
        $table_opts=false;
        create_table($con, $table_name, $table_fields, $table_opts, $upgrade_msgs);
    }

}

function readViews($con, $pager_name, $user_id=false, $return_rs=false) {
    if (!$pager_name) return false;
    $sql = "SELECT * FROM pager_saved_view WHERE pager_name=".$con->qstr($pager_name) ." AND user_id=$user_id";

    $rs = $con->execute($sql);
    if (!$rs) { db_error_handler($con, $sql); }
    else {
        if (!$rs->EOF) {
            if ($return_rs) return $rs;
            $view_data=unserialize($rs->fields['view_data']);
            return $view_data;
        }
    }
    return false;
}

function writeViews($con, $pager_name, $user_id, $views) {
    if (!$pager_name OR !$views) return false;
    $view_table="pager_saved_view";
    $view_data=array();
    $view_data['view_data']=serialize($views);
    $view_data['user_id']=$user_id;
    $view_data['pager_name']=$pager_name;
    $last_view=readViews($con, $pager_name, $user_id, true);
    if (!$last_view) {
        $view_sql = $con->GetInsertSQL($view_table, $view_data);
    } else {
        $view_sql = $con->GetUpdateSQL($last_view, $view_data);
    }
    if ($view_sql) {
        $view_result=$con->execute($view_sql);
        if (!$view_result) { db_error_handler($con, $view_sql); return false; }
        return true;
    }

    return false;

}

function checkViewAdmin($con, $user_id) {
    return check_user_role(false, $user_id, 'Administrator');
}



/**
 * $Log: view_functions.php,v $
 * Revision 1.2  2006/07/13 00:24:26  vanmer
 * - added user role check instead of always allowing admin access
 *
 * Revision 1.1  2006/07/13 00:12:13  vanmer
 * - Initial revision of a set of functions to save and retrieve views for pagers, by user
 *
 *
**/
?>