<?php
/**
 * Pager View API
 *
 * This file contains a set of utility functions for saving and retrieving views for a GUP_Pager and
 * Pager_Columns object.
 *
 * $Id: view_functions.php,v 1.3 2006/07/14 02:09:21 vanmer Exp $
 */


/**
 * function to check for the view table, and install it if it does not exist
 *
 * @param adodbconnection $con with connection to database where table should exist
 *
**/
function initViews($con) {
//run to ensure that view data tables are available
    $table_name="pager_saved_view";

    $table_list = list_db_tables($con);

    //check for table in list of existing tables
    if (array_search($table_name, $table_list)===false) {

        /** DEFINE VIEWS TABLE **/
        /** 
            pager_saved_view TABLE STRUCTURE:

            pager_saved_view_id INTEGER PRIMARY KEY
            user_id INTEGER
            pager_name VARCHAR(255)
            view_data TEXT

        **/
        $table_fields=array();
        $table_fields[]=array('NAME'=>'pager_saved_view_id','TYPE'=>'I','SIZE'=>'','NOTNULL'=>'NOTNULL','KEY'=>'KEY','AUTOINCREMENT'=>'AUTOINCREMENT');
        $table_fields[]=array('NAME'=>'user_id', 'TYPE'=>'I','SIZE'=>'','NOTNULL'=>'NOTNULL','DEFAULT'=>0);
        $table_fields[]=array('NAME'=>'pager_name','TYPE'=>'C','SIZE'=>255);
        $table_fields[]=array('NAME'=>'view_data','TYPE'=>'X');
        $table_opts=false;
        return create_table($con, $table_name, $table_fields, $table_opts, $upgrade_msgs);
    }

}

/**
 * function to retrieve views for a pager for a particular user
 *
 * @param adodbconnection $con with connection to database with saved views
 * @param string $pager_name with unique pager name to write
 * @param integer $user_id user identifier
 * @param boolean $return_r indicating if results should be returned as a recordset object (true) or as a view array (default)
 * 
 * @return views as passed in to writeViews (or recordset object for view row, depending on return_rs parameter) or false if no views were found/error
 *
**/
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

/**
 * function to retrieve views for a pager for a particular user
 *
 * @param adodbconnection $con with connection to database with saved views
 * @param string $pager_name with unique pager name to write
 * @param integer $user_id user identifier
 * @param array $views with view data to save 
 *
 * @return boolean indicating success of write of views to database
**/
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

/**
 * function to retrieve views for a pager for a particular user
 *
 * @param adodbconnection $con with connection to database with saved views
 * @param integer $user_id user identifier
 * 
 * @return boolean indicating whether user should be granted administrator access for views
 *
**/
function checkViewAdmin($con, $user_id) {
    return check_user_role(false, $user_id, 'Administrator');
}



/**
 * $Log: view_functions.php,v $
 * Revision 1.3  2006/07/14 02:09:21  vanmer
 * - added phpdoc comments for all view functions
 * - changed init function to return results of create table, if run
 *
 * Revision 1.2  2006/07/13 00:24:26  vanmer
 * - added user role check instead of always allowing admin access
 *
 * Revision 1.1  2006/07/13 00:12:13  vanmer
 * - Initial revision of a set of functions to save and retrieve views for pagers, by user
 *
 *
**/
?>