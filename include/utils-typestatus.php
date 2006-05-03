<?php
/**********************************************************************/
/**
  * Adds an entity type to the database or finds an existing type with an identical short_name
  *
  * @param adodbconnection $con with handle to the DB
  * @param string $entity_type with type of entity (currently case or opportunity)
  * @param string $entity_type_short_name 10 character unique identifier for the new type
  * @param string $entity_type_pretty_name multi-word name for the type
  * @param string $entity_type_pretty_plural plural pretty version of the type name
  * @param string $entity_type_display_html HTML-enabled version of the pretty name
  * @param boolean $magic_quotes indicating if incoming strings are magic_quote'd or not (for _POST/_GET strings the output of get_magic_quotes_gpc() should be passed here)
  *
  * @return integer $type_id with database identifier for the newly added (or identified existing type with the same short_name), or false for failure
  *
**/
    function add_entity_type($con, $entity_type, $entity_type_short_name, $entity_type_pretty_name=false, $entity_type_pretty_plural=false, $entity_type_display_html=false, $magic_quotes=false) {
        //we require con, entity_type, short name and pretty name
        if (!$con) return false;

        if (!$entity_type) return false;

        if (!$entity_type_short_name) return false;

        if (!$entity_type_pretty_name) return false;

        $table=$entity_type."_types";

        //find existing type with same short name, if it exists
        $ret=get_entity_type($con, $entity_type, false, $entity_type_short_name);
        if ($ret) {
            $entity_type_id=$ret["{$entity_type}_type_id"];
            //undelete if marked as logically deleted
            if ($ret["{$entity_type}_type_record_status"]=='d') {
                $upd="UPDATE $table SET {$entity_type}_type_record_status=".$con->qstr("a") . " WHERE {$entity_type}_type_id=$entity_type_id";
                $rst=$con->execute($upd);
                if (!$rst) { db_error_handler($con, $upd); return false; }
            }
            //return existing type
            return $entity_type_id;
        }

        $rec=array();

        $rec["{$entity_type}_type_short_name"]=$entity_type_short_name;
        $rec["{$entity_type}_type_pretty_name"]=$entity_type_pretty_name;

        //optionally add pretty plural and display HTML
        if ($entity_type_pretty_plural) {
            $rec["{$entity_type}_type_pretty_plural"]=$entity_type_pretty_plural;
        }
        if ($entity_type_display_html) {
            $rec["{$entity_type}_type_display_html"]=$entity_type_display_html;
        }

        $rec["{$entity_type}_type_record_status"]='a';

        //get insert sql statement
        $ins = $con->getInsertSQL($table, $rec, $magic_quotes);
        if (!$ins) return false;

        //execute
        $rst = $con->execute($ins);

        if (!$rst) { db_error_handler($con, $ins); return false; }

        $type_id=$con->Insert_ID();

        return $type_id;

    }
/**********************************************************************/
/**
  * Adds an entity status to the database or finds an existing status with an identical short_name and type_id
  *
  * @param adodbconnection $con with handle to the DB
  * @param string $entity_type with type of entity (currently case or opportunity)
  * @param integer $entity_type_id to associate the status with
  * @param string $entity_status_short_name 10 character unique identifier for the new status
  * @param string $entity_status_pretty_name multi-word name for the status
  * @param string $entity_status_pretty_plural plural pretty version of the status name
  * @param string $entity_status_display_html HTML-enabled version of the pretty name
  * @param string $entity_status_long_desc longer description of status to add
  * @param integer $sort_id with level of sort for the status
  * @param character $status_open_indicator with 'o' for open, 'r' for resolved and 'u' for unresolved (both r and u indicate a closed status)
  * @param boolean $magic_quotes indicating if incoming strings are magic_quote'd or not (for _POST/_GET strings the output of get_magic_quotes_gpc() should be passed here)
  *
  * @return integer $status_id with database identifier for the newly added (or identified existing status with the same short_name and type_id), or false for failure
  *
**/
    
    function add_entity_status($con, $entity_type, $entity_type_id, $entity_status_short_name, $entity_status_pretty_name=false, $entity_status_pretty_plural=false, $entity_status_display_html=false, $entity_status_long_desc=false, $sort_order=1, $status_open_indicator='o', $magic_quotes=false) {
        //we require con, entity_type, entity_type_id, short name and pretty name
        if (!$con) return false;

        if (!$entity_type) return false;

        if (!$entity_type_id) return false;

        if (!$entity_status_short_name) return false;

        if (!$entity_status_pretty_name) return false;

        $table=$entity_type."_statuses";

        //find existing status with same short name, if it exists
        $ret=get_entity_status($con, $entity_type, false, $entity_type_id, $entity_status_short_name);
        if ($ret) {
            $entity_status_id=$ret["{$entity_type}_status_id"];
            //undelete if marked as logically deleted
            if ($ret["{$entity_type}_status_record_status"]=='d') {
                $upd="UPDATE $table SET {$entity_type}_status_record_status=".$con->qstr("a") . " WHERE {$entity_type}_status_id=$entity_status_id";
                $rst=$con->execute($upd);
                if (!$rst) { db_error_handler($con, $upd); return false; }
            }
            //return existing status
            return $entity_status_id;
        }

        $rec=array();
        if (!$sort_order) $sort_order=1;
        if (!$status_open_indicator) $status_open_indicator='o';

        $rec['sort_order']=$sort_order;
        $rec['status_open_indicator']=$status_open_indicator;
        $rec["{$entity_type}_type_id"]=$entity_type_id;
        $rec["{$entity_type}_status_short_name"]=$entity_status_short_name;
        $rec["{$entity_type}_status_pretty_name"]=$entity_status_pretty_name;

        //optionally add pretty plural and display HTML
        if ($entity_status_pretty_plural) {
            $rec["{$entity_type}_status_pretty_plural"]=$entity_status_pretty_plural;
        }
        if ($entity_status_display_html) {
            $rec["{$entity_type}_status_display_html"]=$entity_status_display_html;
        }
        if ($entity_status_long_desc) {
            $rec["{$entity_type}_status_long_desc"]=$entity_status_long_desc;
        }
        $rec["{$entity_type}_status_record_status"]='a';

        //get insert sql statement
        $ins = $con->getInsertSQL($table, $rec, $magic_quotes);
        if (!$ins) return false;

        //execute
        $rst = $con->execute($ins);

        if (!$rst) { db_error_handler($con, $ins); return false; }

        $status_id=$con->Insert_ID();

        return $status_id;

    
    }

/**********************************************************************/
/**
  * Find an entity type in the database
  *
  * @param adodbconnection $con with handle to the DB
  * @param string $entity_type with type of entity (currently case or opportunity)
  * @param string $entity_type_short_name 10 character unique identifier for the type
  * @param string $entity_type_pretty_name multi-word name for the type
  * @param boolean $show_all indicating if records with a record_status other than 'a' should be shown (defaults to false, only show active records)
  *
  * @return array of associative arrays, each with the data for one entity_type, or false for failure/no results
  *
**/
    function find_entity_type($con, $entity_type, $entity_type_short_name=false, $entity_type_pretty_name=false, $show_all=false) {
        if (!$con) return false;
        if (!$entity_type) return false;
        if (!$entity_type_short_name AND !$entity_type_pretty_name) return false;

        $table=$entity_type."_types";
        $where=array();
        if ($entity_type_short_name) { $where[]="{$entity_type}_type_short_name LIKE ".$con->qstr($entity_type_short_name); }
        if ($entity_type_pretty_name) { $where[]="{$entity_type}_type_pretty_name LIKE ".$con->qstr($entity_type_pretty_name); }
        if (!$show_all) { $where[]= "{$entity_type}_type_record_status = " . $con->qstr('a'); }
        $wherestr=implode(" AND ", $where);
        if (!$wherestr) return false;

        $sql = "SELECT * FROM $table WHERE $wherestr";

        $rst=$con->execute($sql);
        if (!$rst) { db_error_handler($con, $sql); return false; }

        $ret=array();
        while (!$rst->EOF) {
            $ret[]=$rst->fields;
            $rst->movenext();
        }

        //if we have any records, return them, otherwise return false
        if (count($ret)>0) {
            return $ret;
        } else return false;

    }
    
/**********************************************************************/
/**
  * Find an entity status in the database
  *
  * @param adodbconnection $con with handle to the DB
  * @param string $entity_type with type of entity (currently case or opportunity)
  * @param integer $entity_type_id that status is associated with (required)
  * @param string $entity_status_short_name 10 character unique identifier for the status
  * @param string $entity_status_pretty_name multi-word name for the status
  * @param string $entity_status_long_desc piece of long description to search for (% is added to front and back, for SQL search)
  * @param integer $sort_order indicating order that status has
  * @param boolean $show_all indicating if records with a record_status other than 'a' should be shown (defaults to false, only show active records)
  *
  * @return array of associative arrays, each with the data for one entity_status, or false for failure/no results
  *
**/
    function find_entity_status($con, $entity_type, $entity_type_id, $entity_status_short_name=false, $entity_status_pretty_name=false, $entity_status_long_desc=false, $sort_order=false, $show_all=false) {
        if (!$con) return false;
        if (!$entity_type) return false;
        if (!$entity_type_id) return false;
        if (!$entity_status_short_name AND !$entity_status_pretty_name AND !$sort_order) return false;

        $table=$entity_type."_statuses";
        $where=array();
        $where[]="{$entity_type}_type_id = $entity_type_id";
        if ($entity_status_short_name) { $where[]="{$entity_type}_status_short_name LIKE ".$con->qstr($entity_status_short_name); }
        if ($entity_status_pretty_name) { $where[]="{$entity_type}_status_pretty_name LIKE ".$con->qstr($entity_status_pretty_name); }
        if ($entity_status_long_desc) { $where[]="{$entity_type}_status_long_desc LIKE %".$con->qstr($entity_status_long_desc)."%"; }
        if ($sort_order) $where[]="sort_order = $sort_order";
        if (!$show_all) { $where[]= "{$entity_type}_status_record_status = " . $con->qstr('a'); }
        $wherestr=implode(" AND ", $where);
        if (!$wherestr) return false;

        $sql = "SELECT * FROM $table WHERE $wherestr";

        $rst=$con->execute($sql);
        if (!$rst) { db_error_handler($con, $sql); return false; }

        $ret=array();
        while (!$rst->EOF) {
            $ret[]=$rst->fields;
            $rst->movenext();
        }

        //if we have any records, return them, otherwise return false
        if (count($ret)>0) {
            return $ret;
        } else return false;

    
    }
    
/**********************************************************************/
/**
  * Get an entity type from the database by ID or short name
  *
  * @param adodbconnection $con with handle to the DB
  * @param string $entity_type with type of entity (currently case or opportunity)
  * @param integer $entity_type_id DB identifier for the entity_type (required if short_name is not provided)
  * @param string $entity_type_short_name 10 character unique identifier for the type (required if ID is not provided)
  * @param boolean $return_rst indicating if return should be recordset or associative array
  *
  * @return array or recordset for one entity_type, or false for failure/no results
  *
**/
    function get_entity_type($con, $entity_type, $entity_type_id=false, $entity_type_short_name=false, $return_rst=false) {
        if (!$con) return false;
        if (!$entity_type) return false;
        if (!$entity_type_short_name AND !$entity_type_id) return false;

        $table=$entity_type."_types";
        $where=array();
        if ($entity_type_id) { $where[]="{$entity_type}_type_id = $entity_type_id"; }
        elseif ($entity_type_short_name) { $where[]="{$entity_type}_type_short_name LIKE ".$con->qstr($entity_type_short_name); }
        $wherestr=implode(" AND ", $where);
        if (!$wherestr) return false;

        $sql = "SELECT * FROM $table WHERE $wherestr";

        $rst=$con->execute($sql);
        if (!$rst) { db_error_handler($con, $sql); return false; }

        if (!$rst->EOF) {
            if (!$return_rst) {
                $ret=$rst->fields;
            } else $ret=$rst;
            return $ret;
        } else return false;
    }
    
/**********************************************************************/
/**
  * Get an entity status from the database by ID or short name
  *
  * @param adodbconnection $con with handle to the DB
  * @param string $entity_type with type of entity (currently case or opportunity)
  * @param integer $entity_status_id DB identifier for status (required if type_id/short_name are not provided)
  * @param integer $entity_type_id that status is associated with (required if status_id is not provided)
  * @param string $entity_status_short_name 10 character unique identifier for the status (required if status_id is not provided)
  * @param boolean $return_rst indicating if return should be recordset or associative array
  *
  * @return array or recordset for one entity_type, or false for failure/no results
  *
**/
    function get_entity_status($con, $entity_type, $entity_status_id=false, $entity_type_id=false, $entity_status_short_name=false, $return_rst=false) {
        if (!$con) return false;
        if (!$entity_type) return false;
        if (!$entity_status_id) { 
            //short_name AND type_id must be provided if status_id is not provided
            if (!$entity_status_short_name OR !$entity_type_id) return false;
        }

        $table=$entity_type."_statuses";
        $where=array();
        if ($entity_status_id) { $where[]="{$entity_type}_status_id = $entity_status_id"; }
        else { 
            $where[]="{$entity_type}_status_short_name LIKE ".$con->qstr($entity_status_short_name); 
            $where[]="{$entity_type}_type_id =$entity_type_id"; 
        }
        $wherestr=implode(" AND ", $where);
        if (!$wherestr) return false;

        $sql = "SELECT * FROM $table WHERE $wherestr";

        $rst=$con->execute($sql);
        if (!$rst) { db_error_handler($con, $sql); return false; }

        if (!$rst->EOF) {
            if (!$return_rst) {
                $ret=$rst->fields;
            } else $ret=$rst;
            return $ret;
        } else return false;
    }
/**********************************************************************/
/**
  * Updates an entity type in the database by ID and fields to update
  *
  * @param adodbconnection $con with handle to the DB
  * @param string $entity_type with type of entity (currently case or opportunity)
  * @param integer $entity_type_id DB identifier (required)
  * @param string $entity_type_short_name 10 character unique identifier for the status, to change the existing value if provided
  * @param string $entity_type_pretty_name multi-word name for the type, to change the existing value if provided
  * @param string $entity_type_pretty_plural plural pretty version of the type name, to change the existing value if provided
  * @param string $entity_type_display_html HTML-enabled version of the pretty name, to change the existing value if provided
  * @param boolean $magic_quotes indicating if incoming strings are magic_quote'd or not (for _POST/_GET strings the output of get_magic_quotes_gpc() should be passed here)
  *
  * @return entity_type_id of the updated type, or false if update failed
  *
**/
    function update_entity_type($con, $entity_type, $entity_type_id, $entity_type_short_name=false, $entity_type_pretty_name=false, $entity_type_pretty_plural=false, $entity_type_display_html=false, $magic_quotes=false) {
        if (!$con) return false;
        if (!$entity_type) return false;
        if (!$entity_type_id) return false;

        $type_rst=get_entity_type($con, $entity_type, $entity_type_id, false, true);
        if (!$type_rst) return false;

        $rec=array();
        
        //optionally add pretty plural and display HTML
        if ($entity_type_short_name) {
            $rec["{$entity_type}_type_short_name"]=$entity_type_short_name;
        }
        if ($entity_type_pretty_name) {
            $rec["{$entity_type}_type_pretty_name"]=$entity_type_pretty_name;
        }
        if ($entity_type_pretty_plural) {
            $rec["{$entity_type}_type_pretty_plural"]=$entity_type_pretty_plural;
        }
        if ($entity_type_display_html) {
            $rec["{$entity_type}_type_display_html"]=$entity_type_display_html;
        }

        $upd=$con->getUpdateSQL($type_rst, $rec, false, $magic_quotes);
        if ($upd) {
            $rst=$con->execute($upd);
            if (!$rst) { db_error_handler($con, $upd); return false; }
        }

        return $entity_type_id;

    }
/**********************************************************************/
/**
  * Updates an entity status in the database by ID and fields to update
  *
  * @param adodbconnection $con with handle to the DB
  * @param string $entity_type with type of entity (currently case or opportunity)
  * @param integer $entity_status_id DB identifier (required)
  * @param integer $entity_type_id that status is associated with, to change the existing value if provided
  * @param string $entity_status_short_name 10 character unique identifier for the status, to change the existing value if provided
  * @param string $entity_status_pretty_name multi-word name for the status, to change the existing value if provided
  * @param string $entity_status_pretty_plural plural pretty version of the status name, to change the existing value if provided
  * @param string $entity_status_display_html HTML-enabled version of the pretty name, to change the existing value if provided
  * @param string $entity_status_long_desc longer description of status, to change the existing value if provided
  * @param integer $sort_id with level of sort for the status, to change the existing value if provided
  * @param character $status_open_indicator with 'o' for open, 'r' for resolved and 'u' for unresolved (both r and u indicate a closed status)
  * @param boolean $magic_quotes indicating if incoming strings are magic_quote'd or not (for _POST/_GET strings the output of get_magic_quotes_gpc() should be passed here)
  *
  * @return entity_status_id of the updated status, or false if update failed
  *
**/
    function update_entity_status($con, $entity_type, $entity_status_id, $entity_type_id=false, $entity_status_short_name=false, $entity_status_pretty_name=false, $entity_status_pretty_plural=false, $entity_status_display_html=false, $entity_status_long_desc=false, $sort_order=1, $status_open_indicator='o', $magic_quotes=false) {
        if (!$con) return false;
        if (!$entity_type) return false;
        if (!$entity_status_id) return false;

        $status_rst=get_entity_status($con, $entity_type, $entity_status_id, false, false, true);
        if (!$status_rst) return false;

        $rec=array();
        
        //optionally add pretty plural and display HTML
        if ($entity_status_short_name) {
            $rec["{$entity_type}_status_short_name"]=$entity_status_short_name;
        }
        if ($entity_status_pretty_name) {
            $rec["{$entity_type}_status_pretty_name"]=$entity_status_pretty_name;
        }
        if ($entity_status_pretty_plural) {
            $rec["{$entity_type}_status_pretty_plural"]=$entity_status_pretty_plural;
        }
        if ($entity_status_display_html) {
            $rec["{$entity_type}_status_display_html"]=$entity_status_display_html;
        }
        if ($entity_status_long_desc) {
            $rec["{$entity_type}_status_long_desc"]=$entity_status_long_desc;
        }
        if ($sort_order) {
            $rec['sort_order']=$sort_order;
        }
        if ($status_open_indicator) {
            $rec['status_open_indicator']=$status_open_indicator;
        }
        if ($entity_type_id) {
            $rec["{$entity_type}_type_id"]=$entity_type_id;
        }

        $upd=$con->getUpdateSQL($status_rst, $rec, false, $magic_quotes);
        if ($upd) {
            $rst=$con->execute($upd);
            if (!$rst) { db_error_handler($con, $upd); return false; }
        }

        return $entity_status_id;

    }

/**********************************************************************/
/**
  * Deletes an entity type from the database by ID
  *
  * @param adodbconnection $con with handle to the DB
  * @param string $entity_type with type of entity (currently case or opportunity)
  * @param integer $entity_type_id DB identifier (required)
  * @param boolean $delete_from_database indicating if record should be deleted from database or just marked as deleted (logical delete by default)
  *
  * @return boolean indicating success of delete operation
  *
**/
    function delete_entity_type($con, $entity_type, $entity_type_id, $delete_from_database=false) {
        if (!$con) return false;
        if (!$entity_type) return false;
        if (!$entity_type_id) return false;

        $table=$entity_type."_types";

        if ($delete_from_database) {
            $sql = "DELETE FROM $table WHERE {$entity_type}_type_id=$entity_type_id";
        } else {
            $sql = "UPDATE $table SET {$entity_type}_type_record_status=".$con->qstr("d")." WHERE {$entity_type}_type_id=$entity_type_id";
        }

        $rst=$con->execute($sql);
        if (!$rst) { db_error_handler($con, $sql); return false; }

        return true;
    }

/**********************************************************************/
/**
  * Deletes an entity status from the database by ID
  *
  * @param adodbconnection $con with handle to the DB
  * @param string $entity_type with type of entity (currently case or opportunity)
  * @param integer $entity_status_id DB identifier (required)
  * @param boolean $delete_from_database indicating if record should be deleted from database or just marked as deleted (logical delete by default)
  *
  * @return boolean indicating success of delete operation
  *
**/
    function delete_entity_status($con, $entity_type, $entity_status_id, $delete_from_database=false) {
        if (!$con) return false;
        if (!$entity_type) return false;
        if (!$entity_status_id) return false;

        $table=$entity_type."_statuses";

        if ($delete_from_database) {
            $sql = "DELETE FROM $table WHERE {$entity_type}_status_id=$entity_status_id";
        } else {
            $sql = "UPDATE $table SET {$entity_type}_status_record_status=".$con->qstr("d")." WHERE {$entity_type}_status_id=$entity_status_id";
        }

        $rst=$con->execute($sql);
        if (!$rst) { db_error_handler($con, $sql); return false; }

        return true;
    }



/** PRIORITIES API ****/


/**********************************************************************/
/**
  * Adds an entity priority to the database or finds an existing priority with an identical short_name
  *
  * @param adodbconnection $con with handle to the DB
  * @param string $entity_type with priority of entity (currently case)
  * @param string $entity_priority_short_name 10 character unique identifier for the new priority
  * @param string $entity_priority_pretty_name multi-word name for the priority
  * @param string $entity_priority_pretty_plural plural pretty version of the priority name
  * @param string $entity_priority_display_html HTML-enabled version of the pretty name
  * @param integer $entity_priority_score_adjustment with priority score adjustment on entity
  * @param boolean $magic_quotes indicating if incoming strings are magic_quote'd or not (for _POST/_GET strings the output of get_magic_quotes_gpc() should be passed here)
  *
  * @return integer $priority_id with database identifier for the newly added (or identified existing priority with the same short_name), or false for failure
  *
**/
    function add_entity_priority($con, $entity_type='case', $entity_priority_short_name, $entity_priority_pretty_name=false, $entity_priority_pretty_plural=false, $entity_priority_display_html=false, $entity_priority_score_adjustment=false, $magic_quotes=false) {
        //we require con, entity_priority, short name and pretty name
        if (!$con) return false;

        if (!$entity_type) return false;

        if (!$entity_priority_short_name) return false;

        if (!$entity_priority_pretty_name) return false;

        $table=$entity_type."_priorities";

        //find existing priority with same short name, if it exists
        $ret=get_entity_priority($con, $entity_type, false, $entity_priority_short_name);
        if ($ret) {
            $entity_priority_id=$ret["{$entity_type}_priority_id"];
            //undelete if marked as logically deleted
            if ($ret["{$entity_type}_priority_record_status"]=='d') {
                $upd="UPDATE $table SET {$entity_type}_priority_record_status=".$con->qstr("a") . " WHERE {$entity_type}_priority_id=$entity_priority_id";
                $rst=$con->execute($upd);
                if (!$rst) { db_error_handler($con, $upd); return false; }
            }
            //return existing priority
            return $entity_priority_id;
        }

        $rec=array();

        $rec["{$entity_type}_priority_short_name"]=$entity_priority_short_name;
        $rec["{$entity_type}_priority_pretty_name"]=$entity_priority_pretty_name;

        //optionally add pretty plural and display HTML
        if ($entity_priority_pretty_plural) {
            $rec["{$entity_type}_priority_pretty_plural"]=$entity_priority_pretty_plural;
        }
        if ($entity_priority_display_html) {
            $rec["{$entity_type}_priority_display_html"]=$entity_priority_display_html;
        }
        if ($entity_priority_score_adjustment) {
            $rec["{$entity_type}_priority_score_adjustment"]=$entity_priority_score_adjustment;
        }

        $rec["{$entity_type}_priority_record_status"]='a';

        //get insert sql statement
        $ins = $con->getInsertSQL($table, $rec, $magic_quotes);
        if (!$ins) return false;

        //execute
        $rst = $con->execute($ins);

        if (!$rst) { db_error_handler($con, $ins); return false; }

        $priority_id=$con->Insert_ID();

        return $priority_id;

    }

/**********************************************************************/
/**
  * Find an entity priority in the database
  *
  * @param adodbconnection $con with handle to the DB
  * @param string $entity_type with priority of entity (currently case)
  * @param string $entity_priority_short_name 10 character unique identifier for the priority
  * @param string $entity_priority_pretty_name multi-word name for the priority
  * @param integer $entity_priority_score_adjustment with priority score adjustment on entity
  * @param boolean $show_all indicating if records with a record_status other than 'a' should be shown (defaults to false, only show active records)
  *
  * @return array of associative arrays, each with the data for one entity_priority, or false for failure/no results
  *
**/
    function find_entity_priority($con, $entity_type='case', $entity_priority_short_name=false, $entity_priority_pretty_name=false, $entity_priority_score_adjustment=false, $show_all=false) {
        if (!$con) return false;
        if (!$entity_type) return false;
        if (!$entity_priority_short_name AND !$entity_priority_pretty_name) return false;

        $table=$entity_type."_priorities";
        $where=array();
        if ($entity_priority_short_name) { $where[]="{$entity_type}_priority_short_name LIKE ".$con->qstr($entity_priority_short_name); }
        if ($entity_priority_pretty_name) { $where[]="{$entity_type}_priority_pretty_name LIKE ".$con->qstr($entity_priority_pretty_name); }
        if ($entity_priority_score_adjustment) { $where[]="{$entity_type}_priority_score_adjustment=$entity_priority_score_adjustment"; }
        if (!$show_all) { $where[]= "{$entity_type}_priority_record_status = " . $con->qstr('a'); }
        $wherestr=implode(" AND ", $where);
        if (!$wherestr) return false;

        $sql = "SELECT * FROM $table WHERE $wherestr";

        $rst=$con->execute($sql);
        if (!$rst) { db_error_handler($con, $sql); return false; }

        $ret=array();
        while (!$rst->EOF) {
            $ret[]=$rst->fields;
            $rst->movenext();
        }

        //if we have any records, return them, otherwise return false
        if (count($ret)>0) {
            return $ret;
        } else return false;

    }

/**********************************************************************/
/**
  * Get an entity priority from the database by ID or short name
  *
  * @param adodbconnection $con with handle to the DB
  * @param string $entity_type with priority of entity (currently case)
  * @param integer $entity_priority_id DB identifier for the entity_priority (required if short_name is not provided)
  * @param string $entity_priority_short_name 10 character unique identifier for the priority (required if ID is not provided)
  * @param boolean $return_rst indicating if return should be recordset or associative array
  *
  * @return array or recordset for one entity_priority, or false for failure/no results
  *
**/
    function get_entity_priority($con, $entity_type='case', $entity_priority_id=false, $entity_priority_short_name=false, $return_rst=false) {
        if (!$con) return false;
        if (!$entity_type) return false;
        if (!$entity_priority_short_name AND !$entity_priority_id) return false;

        $table=$entity_type."_priorities";
        $where=array();
        if ($entity_priority_id) { $where[]="{$entity_type}_priority_id = $entity_priority_id"; }
        elseif ($entity_priority_short_name) { $where[]="{$entity_type}_priority_short_name LIKE ".$con->qstr($entity_priority_short_name); }
        $wherestr=implode(" AND ", $where);
        if (!$wherestr) return false;

        $sql = "SELECT * FROM $table WHERE $wherestr";

        $rst=$con->execute($sql);
        if (!$rst) { db_error_handler($con, $sql); return false; }

        if (!$rst->EOF) {
            if (!$return_rst) {
                $ret=$rst->fields;
            } else $ret=$rst;
            return $ret;
        } else return false;
    }

/**********************************************************************/
/**
  * Updates an entity priority in the database by ID and fields to update
  *
  * @param adodbconnection $con with handle to the DB
  * @param string $entity_type with priority of entity (currently case)
  * @param integer $entity_priority_id DB identifier (required)
  * @param string $entity_priority_short_name 10 character unique identifier for the status, to change the existing value if provided
  * @param string $entity_priority_pretty_name multi-word name for the priority, to change the existing value if provided
  * @param string $entity_priority_pretty_plural plural pretty version of the priority name, to change the existing value if provided
  * @param string $entity_priority_display_html HTML-enabled version of the pretty name, to change the existing value if provided
  * @param integer $entity_priority_score_adjustment with priority score adjustment on entity
  * @param boolean $magic_quotes indicating if incoming strings are magic_quote'd or not (for _POST/_GET strings the output of get_magic_quotes_gpc() should be passed here)
  *
  * @return entity_priority_id of the updated priority, or false if update failed
  *
**/
    function update_entity_priority($con, $entity_type='case', $entity_priority_id, $entity_priority_short_name=false, $entity_priority_pretty_name=false, $entity_priority_pretty_plural=false, $entity_priority_display_html=false, $entity_priority_score_adjustment, $magic_quotes=false) {
        if (!$con) return false;
        if (!$entity_type) return false;
        if (!$entity_priority_id) return false;

        $priority_rst=get_entity_priority($con, $entity_type, $entity_priority_id, false, true);
        if (!$priority_rst) return false;

        $rec=array();
        
        //optionally add pretty plural and display HTML
        if ($entity_priority_short_name) {
            $rec["{$entity_type}_priority_short_name"]=$entity_priority_short_name;
        }
        if ($entity_priority_pretty_name) {
            $rec["{$entity_type}_priority_pretty_name"]=$entity_priority_pretty_name;
        }
        if ($entity_priority_pretty_plural) {
            $rec["{$entity_type}_priority_pretty_plural"]=$entity_priority_pretty_plural;
        }
        if ($entity_priority_display_html) {
            $rec["{$entity_type}_priority_display_html"]=$entity_priority_display_html;
        }
        if ($entity_priority_score_adjustment) {
            $rec["{$entity_type}_priority_score_adjustment"]=$entity_priority_score_adjustment;
        }

        $upd=$con->getUpdateSQL($priority_rst, $rec, false, $magic_quotes);
        if ($upd) {
            $rst=$con->execute($upd);
            if (!$rst) { db_error_handler($con, $upd); return false; }
        }

        return $entity_priority_id;

    }

/**********************************************************************/
/**
  * Deletes an entity priority from the database by ID
  *
  * @param adodbconnection $con with handle to the DB
  * @param string $entity_type with priority of entity (currently case)
  * @param integer $entity_priority_id DB identifier (required)
  * @param boolean $delete_from_database indicating if record should be deleted from database or just marked as deleted (logical delete by default)
  *
  * @return boolean indicating success of delete operation
  *
**/
    function delete_entity_priority($con, $entity_type='case', $entity_priority_id, $delete_from_database=false) {
        if (!$con) return false;
        if (!$entity_type) return false;
        if (!$entity_priority_id) return false;

        $table=$entity_type."_priorities";

        if ($delete_from_database) {
            $sql = "DELETE FROM $table WHERE {$entity_type}_priority_id=$entity_priority_id";
        } else {
            $sql = "UPDATE $table SET {$entity_type}_priority_record_status=".$con->qstr("d")." WHERE {$entity_type}_priority_id=$entity_priority_id";
        }

        $rst=$con->execute($sql);
        if (!$rst) { db_error_handler($con, $sql); return false; }

        return true;
    }



?>