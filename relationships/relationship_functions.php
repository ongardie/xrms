<?php

if ( !defined('IN_XRMS') )
{
  die(_("Hacking attempt"));
  exit;
}

/**
 * Functions for managing relationships
 *
 * @author Aaron van Meerten
 *
 * $Id: relationship_functions.php,v 1.3 2005/06/07 20:58:55 vanmer Exp $
 */
 
/*****************************************************************************/
/**
 * function get_relationships
 *
 * This function takes a table and id, and returns an array of relationships which exist on this table/id
 *
 * @param adodbconnection $con
 * @param string $_on_what_table with table of entity to search relationships
 * @param integer $_on_what_id with id of entity
 * @param array optionally providing relationship types this table exists in
 * @param string $exclude_relationships with comma separated list of relationship_ids to exclude) {
 *
 * @return array keyed by relationship_id, including data about relationship, as well as relationship_type
 */
function get_relationships($con, $_on_what_table, $_on_what_id, $relationship_types=false, $exclude_relationships=false) {
    if (!$relationship_types) $relationship_types=get_relationship_types($con, $_on_what_table);
    if (!$relationship_types) return false;
    $relationships=array();
    
    foreach ($relationship_types as $relationship_type_id=>$relationship_type_data) {
        $both = 0; //If it's a relationship from a table on itself, we need to check both directions
        if($relationship_type_data['from_what_table']==$_on_what_table && $relationship_type_data['to_what_table']==$_on_what_table) {
            $working_direction = 'from';
            $opposite_direction = 'to';
            $both = 1;
        }
        elseif($relationship_type_data['from_what_table']==$_on_what_table) {
            $working_direction = 'from';
            $opposite_direction = 'to';
        }
        else {
            $working_direction = 'to';
            $opposite_direction = 'from';
        }
                                                    
        $name_to_get = $con->Concat("c." . implode(", ' ' , c.", table_name($relationship_type_data[$opposite_direction.'_what_table'])));
        $sql = "SELECT r.relationship_id, r.from_what_id, r.to_what_id, r.relationship_type_id, r.established_at, r.ended_on, r.relationship_status,
                    c." . $relationship_type_data[$opposite_direction.'_what_table_singular'] . "_id, " . $name_to_get . " as name, r.relationship_type_id 
                FROM relationships as r, " . $relationship_type_data[$opposite_direction.'_what_table'] . " as c
                WHERE 
                r.relationship_type_id = $relationship_type_id 
                AND r.relationship_status='a'";
        $sqlend =" AND (({$working_direction}_what_id = $_on_what_id AND r." . $opposite_direction . "_what_id=" . $relationship_type_data[$opposite_direction.'_what_table_singular'] . "_id";
                if ($both==1) {
					$sql = $sql . $sqlend . ")) UNION " . $sql .
                		"AND (({$opposite_direction}_what_id = $_on_what_id AND r." . $working_direction . "_what_id=" . $relationship_type_data[$working_direction.'_what_table_singular'] . "_id))";
                } else $sql.= $sqlend . "))";
                if ($exclude_relationships) {
                    $sql.=" AND r.relationship_id NOT IN ($exclude_relationships)";
                }
        //echo "<br>$sql<br>";
        $rst2 = $con->execute($sql);
        if(!$rst2) {
            db_error_handler($con, $sql);
        }
        elseif(!$rst2->EOF) {
            while (!$rst2->EOF) {
                $relationships[$rst2->fields['relationship_id']]=$rst2->fields;
                $relationships[$rst2->fields['relationship_id']]['relationship_type_data']=$relationship_type_data;
                if ($rst2->fields[$working_direction.'_what_id']==$_on_what_id) {
                    $relationships[$rst2->fields['relationship_id']]['working_direction']=$working_direction;
                    $relationships[$rst2->fields['relationship_id']]['opposite_direction']=$opposite_direction;
                } else {
                    $relationships[$rst2->fields['relationship_id']]['working_direction']=$opposite_direction;
                    $relationships[$rst2->fields['relationship_id']]['opposite_direction']=$working_direction;
                }                
                $rst2->movenext();
            }
        }
        if(($both || $i) && $working_direction == 'from') {
            $working_direction = 'to';
            $opposite_direction = 'from';
        }
        elseif($both || $i) {
            $working_direction = 'from';
            $opposite_direction = 'to';
        }
    }
    if (count($relationships)>0) {
        return $relationships;
    } else return false;
} 

/*****************************************************************************/
/**
 * function get_relationship_types
 *
 * This function takes a table and returns an array of possible relationships type which exist for this table
 *
 * @param adodbconnection $con
 * @param string $_on_what_table with table of entity to search relationships
 *
 * @return array keyed by relationship_type_id, including data about relationship_type
 */
function get_relationship_types($con, $_on_what_table) {

    $sql = "SELECT relationship_type_id, relationship_name, from_what_text, from_what_table, to_what_text, to_what_table, pre_formatting, post_formatting
            FROM relationship_types
            WHERE from_what_table = " . $con->qstr($_on_what_table, get_magic_quotes_gpc()) . "
                OR to_what_table = " . $con->qstr($_on_what_table,  get_magic_quotes_gpc()) . "
            AND relationship_status = 'a'
            ORDER BY relationship_name, from_what_table, to_what_table";
    $rst = $con->execute($sql);
    if(!$rst) {
        db_error_handler($con, $sql);
        return false;
    }
    elseif(!$rst->EOF) {
        $relationship_types=array();
        while(!$rst->EOF) {
            $relationship_types[$rst->fields['relationship_type_id']]=$rst->fields;
            
            //make singular names for from and to what table
            $relationship_types[$rst->fields['relationship_type_id']]['from_what_table_singular']=make_singular($relationship_types[$rst->fields['relationship_type_id']]['from_what_table']);
            $relationship_types[$rst->fields['relationship_type_id']]['to_what_table_singular']=make_singular($relationship_types[$rst->fields['relationship_type_id']]['to_what_table']);
            $rst->movenext();
        }
        return $relationship_types;
    }
    return false;
} 
 
/*****************************************************************************/
/**
 * function get_agent_count
 *
 * This function a company id, and returns the number of contacts at this company
 *
 * @param adodbconnection $con
 * @param integer $company_id with table of entity to search relationships
 *
 * @return integer with count of contacts, or false if query failed
 */
function get_agent_count($con, $company_id) {
    if (!$company_id) return false;
    $sql = "SELECT COUNT(contact_id) as agent_count
        FROM contacts
        WHERE company_id = $company_id
        GROUP BY company_id";
    $rst = $con->execute($sql);
    if(!$rst) {
        db_error_handler($con, $sql);
    }
    elseif(!$rst->EOF) {
        $agent_count = $rst->fields['agent_count'];
        $rst->close();
    }
    return $agent_count;
}
 /**
  * $Log: relationship_functions.php,v $
  * Revision 1.3  2005/06/07 20:58:55  vanmer
  * - patch to speed relationship checks when both sides are active provided by matthew berardi
  *
  * Revision 1.2  2005/02/10 04:10:05  vanmer
  * - modified to use a single query for each relationship type
  *
  * Revision 1.1  2005/02/10 02:31:13  vanmer
  * - Initial revision of a collection of functions for manipulating and returning relationships
  *
  *
**/
 ?>