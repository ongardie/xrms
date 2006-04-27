<?php
    function add_entity_type($con, $entity_type, $entity_type_short_name, $entity_type_pretty_name, $entity_type_pretty_plural=false, $entity_type_display_html=false, $magic_quotes=false) {
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
    
    function add_entity_status($con, $entity_type, $entity_type_id, $entity_status_short_name, $entity_status_pretty_name, $entity_status_pretty_plural, $entity_status_display_html, $entity_status_long_desc, $sort_order=1, $status_open_indicator='o') {
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
    
    function find_entity_type($con, $entity_type, $entity_type_short_name, $entity_type_pretty_name, $show_all=false) {
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
    
    function find_entity_status($con, $entity_type, $entity_type_id, $entity_status_short_name, $entity_status_pretty_name, $entity_status_long_desc, $sort_order, $show_all=false) {
        if (!$con) return false;
        if (!$entity_type) return false;
        if (!$entity_type_id) return false;
        if (!$entity_status_short_name AND !$entity_status_pretty_name) return false;

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

    function update_entity_status($con, $entity_type, $entity_status_id, $entity_type_id, $entity_status_short_name, $entity_status_pretty_name, $entity_status_pretty_plural, $entity_status_display_html, $entity_status_long_desc, $sort_order=1, $status_open_indicator='o', $magic_quotes=false) {
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
?>