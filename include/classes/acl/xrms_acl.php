<?php
/**
 * ACL system for XRMS
 *
 * Copyright (c) 2004 Explorer Fund Advisors, LLC
 * All Rights Reserved.
 *
 * @todo
 * @package ACL
 * $Id: xrms_acl.php,v 1.35 2006/07/09 05:40:34 vanmer Exp $
 */

/*****************************************************************************/
/** Class xrms_acl
   *
   * Main API for accessing and editing ACLs for XRMS
   *
   **/
   
class xrms_acl {
    //Connection object for use throughout the entire ACL object
    /* @var $DBConnection contains the database handle to the database containing the main ACL tables */
    var $DBConnection=false;
    //Options for connection, used to instantiate the DB connection
    /* @var $DBOptions associative array keyed by datasource name, of database connection information.  Main datasource should be key 'default', with database connection information.  Required keys for each datasource are 'db_dbtype', 'db_server', 'db_username', 'db_password', 'db_dbname' */
    var $DBOptions=array();
    
    /* @var $authCallBacks array containing strings with callback functions which will return database handles or DB auth information */
    var $authCallbacks=array();

    /* @var $context_data_source_name is normally 'default', used to determine which datasource to use when no datasource is provided for a controlled object */
    var $context_data_source_name='default';

    /*****************************************************************************/
    /** function xrms_acl
     *
     * Constructor for the xrms_acl
     *
     * @param array with options for DB connection
    * @param adodbconnection $con with database connection with primary ACL tables
    * @param array $authCallbacks with array of function names to use for database connection or auth information for external datasources
    * @param string $context_data_source_name with name of datasource to use as the default datasource when no datasource is provided for an object
     * @return Object of type xrms_acl
     *
     **/
    function xrms_acl($DBOptionSet=false, $con=false, $authCallbacks=false, $context_data_source_name='default') {
        if ($DBOptionSet) {
            $this->DBOptions=$DBOptionSet;
        }
        
        if ($context_data_source_name) {
            $this->context_data_source_name=$context_data_source_name;
        }
        
        if ($authCallbacks) {
            if (!is_array($authCallbacks)) { $authCallbacks=array($authCallbacks); }
            $this->authCallbacks=$authCallbacks;
        }
        
        if (!$con) {
            $this->DBConnection = $this->get_object_adodbconnection();
        } else $this->DBConnection=$con;        
    }
    
    
    /*****************************************************************************/
    /** function get_context_data_source_name
     *
     * Returns the string used to identify the context within which objects without a data source will be referenced
     *
     * @return string with data_source_name
     *
     **/
    function get_context_data_source_name() {
        return $this->context_data_source_name;    
    }

    /*****************************************************************************/
    /** function set_context_data_source_name
     *
     * Sets the string used to identify the context within which objects without a data source will be referenced
     *
     * @param string $data_source_name with name of data source to use as context
     * @return string with data_source_name
     *
     **/
    function set_context_data_source_name($data_source_name=false) {
        if ($data_source_name) {
            $this->context_data_source_name=$data_source_name; 
            return true;
        }
        else return false;
    }
    
    /*****************************************************************************/
    /** function get_context_data_source_name
     *
     * Returns the string used to identify the data source for a controlled object and id
     * Currently simply returns the context from which the ACL was set
     *
     * @return string with data_source_name
     *
     **/
    function get_data_source_from_context($ControlledObject_id=false, $on_what_id=false) {
        return $this->get_context_data_source_name();
    }
    
    
        
    /*****************************************************************************/
    /** function get_authCallbacks()
     *
     * Returns the list of authCallbacks provided by parent application for access to databases
     *
     * @return Array of callback function names
     *
     **/
    function get_authCallbacks() {
        return $this->authCallbacks;
    }

    /*****************************************************************************/
    /** function add_authCallbacks()
     *
     * Adds an authCallback function by which a parent application can provide access to databases
     *
     * @param string $callback with name of callback function to add
     * @return boolean reflecting success of adding new callback, false means the callback already existed in the list
     *
     **/
    function add_authCallback($callback) {
        if (array_search($callback, $this->authCallbacks)===false) {
            $this->authCallbacks[]=$callback;
            return true;
        }
        return false;
    }
    
    /*****************************************************************************/
    /** function remove_authCallbacks()
     *
     * Removes a callback from the authCallback list
     *
     * @param string $callback with name of callback function to remove
     * @return boolean reflecting success of removing callback, false means the callback did not exist in the list
     *
     **/
    function remove_authCallback($callback) {
        $ckey=array_search($callback, $this->authCallbacks);
        if ($ckey===false) {
            return false;
        } else {
            unset($this->authCallbacks[$ckey]);
            return true;
        }
    }
    
    /*****************************************************************************/
    /** function get_group_objects
     *
     * Returns a list of specific Controlled Objects and values that are contained in a group
     *
     * @param integer Group_id identifying group for which to retrieve a list of Controlled Objects
     * @param integer ControlledObject_id optionally identifying which controlled object type to restrict with
     * @param integer on_what_id optionally identifying which controlled object id to restrict with
     * @param integer GroupMember_id optionally identifying which record to use
     * @return Array containing a collection of Controlled Objects and their possible values
     *
     **/
    function get_group_objects($Group_id, $ControlledObject_id=false, $on_what_id=false, $GroupMember_id=false) {
        $con = $this->DBConnection;
        $table = "GroupMember";
        $objectList=array();
        $where=array();
        if ($Group_id) {
            $where[]="Group_id=$Group_id";
        }
        if ($ControlledObject_id) { 
            $where[] = "ControlledObject_id=$ControlledObject_id";
        }
        if ($on_what_id) {
            $where[] ="on_what_id=$on_what_id";
        }
        $wherestr=implode(" AND ", $where);
        $sql = "SELECT * FROM $table";
        if ($wherestr) {
            $sql .= " WHERE $wherestr";
        }
        //get list of group objects by controll object, Group_id or individual object ID
        $rs = $con->execute($sql);
        
        if (!$rs) { db_error_handler($con, $sql); return false; }
        if ($rs->numRows()>0) {
            while (!$rs->EOF) {
                //if there is an explicit ID set, add this entry to the list
                if ($rs->fields['on_what_id']) {
                    $objectList[$rs->fields['GroupMember_id']]=$rs->fields;
                } else {
                    //otherwisie check if there is criteria available for this GroupMember entry
                    $criteria_objects=$this->get_group_members_by_criteria($rs->fields['GroupMember_id'],$rs->fields);
                    if ($criteria_objects) {
                        //use objects provided by criteria for group member
                        $objectList=array_merge($objectList, $criteria_objects);
                    }
                }
                $rs->movenext();
            }
        }
        //now search in child groups
        if ($Group_id) {
            $groupList = $this->get_group_user($Group_id);
            if ($groupList and is_array($groupList)) {
                foreach ($groupList as $groupInfo) {
                    if ($groupInfo['ChildGroup_id']) {
                        //get the list of objects within each child group
                        $result = $this->get_group_objects($groupInfo['ChildGroup_id'], $ControlledObject_id);
                        //add provided list to group member entries
                        if ($result) { $objectList = array_merge($objectList, $result); }
                    } //end ChildGroup check
                } //end foreach on grouplist
            } // end grouplist check
        } //end group check

	//DISABLED GROUP OBJECT INHERITANCE THROUGH OBJECT RELATIONSHIPS
	//TO ALLOW CHILD OBJECTS TO INHERIT PARENT GROUP, SET inherit_group_objects TO TRUE
	$inherit_group_objects=false;
        if ($ControlledObject_id AND $inherit_group_objects) {
            //now search in parent objects
            $Relationships = $this->get_controlled_object_relationship(false, $ControlledObject_id, false, true);
            if ($Relationships) {
                if (!is_array(current($Relationships))) $Relationships=array($Relationships);
                foreach ($Relationships as $Relationship) {
                    $parent = $Relationship['ParentControlledObject_id'];
                    if ($parent AND ($parent>0)) {
                        $gresult = $this->get_group_objects($Group_id, $parent);
                        if ($gresult) {
                            foreach ($gresult as $result) {
                                $parentIDs[] = $result['on_what_id'];
                            }
                            $parentIDs=array_unique($parentIDs);
                            //echo "<pre>\n";print_r($parentIDs); echo "\n</pre>";
                            if ($Relationship['singular']==1) {
                                    $on_what_child_field='on_what_id';
                                    $fieldRestriction['on_what_table']=$con->qstr($Relationship['parent_table']);
                            } else {
                                if (!trim($Relationship['on_what_child_field'])) {
                                    $on_what_child_field=$Relationship['parent_field'];
                                } else {
                                    $on_what_child_field=$Relationship['on_what_child_field'];
                                }
                                if (trim($Relationship['cross_table'])) {
                                    $on_what_table=$Relationship['cross_table'];
                                    $find_field=$on_what_child_field;
                                    $on_what_child_field=$Relationship['on_what_parent_field'];
                                } else {
                                    $on_what_table=false;
                                    $find_field=false;
                                }
                            }
                            $fieldRestriction[$on_what_child_field]=$parentIDs;
                            $ParentList=$this->get_field_list($ControlledObject_id, $fieldRestriction, $find_field, $on_what_table);

                            if ($ParentList) {
                                foreach ($ParentList as $result) {
                                    $ret = array(array("ControlledObject_id"=>$ControlledObject_id, "on_what_id"=>$result));
                                    $objectList = array_merge($objectList, $ret);
                                } //end parent list foreach
                            } //end check for parent list
    
                        } //end check for group result
                    } //end parent check
                } //end relationship foreach
            } //end relationship check
        } //end controlled object check

        //if we found any objects at all, return them
        if (count($objectList)>0) {
            return $objectList;
        }
        return false;
    }

    /*****************************************************************************/
    /**
     *
     * Returns a list of controlled objects and IDs that match the criteria given by a particular GroupMember_id
     *
     * @param integer GroupMember_id identifying group member entry to find criteria for
     * @param array GroupMember_id optionally providing data on the group member
     * @return Array containing a collection of Controlled Objects and their possible values
     *
     **/
    function get_group_members_by_criteria($GroupMember_id=false, $GroupMember_data=false) {
        $con = $this->DBConnection;
        //no parameters, can't do anything
        if (!$GroupMember_id AND !$GroupMember_data) return false;
        //no data retrieved yet, retrieve data on group member
        if (!$GroupMember_data) {
            $GroupMember_data=$this->get_group_member(false, false, false, false, false, $GroupMember_id);
            if ($GroupMember_data) { $GroupMember_data=current($GroupMember_data); }
            //no group member, fail
            else return false;
        }
        //get GroupMember_id, if not set along with data
        if (!$GroupMember_id) { $GroupMember_id=$GroupMember_data['GroupMember_id']; }        
        
        $ControlledObject_id=$GroupMember_data['ControlledObject_id'];
        
        //retrieve criteria, if any
        $criteria=$this->get_group_member_criteria($GroupMember_id);
        
        //no criteria, so fail
        if (!$criteria) { return false; }
        
        $table=$GroupMember_data['criteria_table'];
        $result_field=$GroupMember_data['criteria_resultfield'];
        if (!$table OR !$result_field) {
            //if table and result field are not set, user controlled object identifier fieldname
            $co=$this->get_controlled_object(false, $ControlledObject_id);
            if (!$table) { $table=$co['on_what_table']; }
            if (!$result_field) { $result_field=$co['on_what_field']; }
        }
        
        $where=array();
        foreach ($criteria as $ckey=>$cdata) {
            //only set criteria as where expression if both value and fieldname are provided
            if ($cdata['criteria_fieldname'] AND $cdata['criteria_value']) {
                $operator=( $cdata['criteria_operator'] ? $cdata['criteria_operator'] : '=');
                $where[]="{$cdata['criteria_fieldname']} $operator {$cdata['criteria_value']}";
            }
            if (count($where)>0) {
                $wherestr=implode(" AND ", $where);
            } else return false; //no useable criteria, so fail
        }
        
        $sql = "SELECT $result_field FROM $table WHERE $wherestr";
        $rst = $con->execute($sql);
        if (!$rst) { db_error_handler($con, $sql); return false; }

        if ($rst->EOF) return false;
        
        $ret=array();
        while (!$rst->EOF) {
            //look for fieldname, retrieve it as on_what_id value
            if (array_key_exists($result_field, $rst->fields)) {
                $ret[]=array('ControlledObject_id'=>$ControlledObject_id, 'on_what_id'=>$rst->fields[$result_field]);
            }
            $rst->movenext();
        }
        //return list of controlled object and ids found in table
        return $ret;
    }
        
    /*****************************************************************************/
    /** function get_object_relationship_parent
     *
     * Returns array of ControlledObject_id and on_what_id for each parent controlled object, based on a ControlledObject or ControlledObjectRelationship
     *
     * @param integer ControlledObject_id for which to find parents
     * @param integer on_what_id to specify which particular ControlledObject to check
     * @param integer CORelationship_id optional parameter to specify in which ControlledObjectRelationship the referenced ControlledObject is the child
     * @return Array containing an array indexed by ControlledObjectRelation_id, specifying the parent ControlledObject_id and on_what_id
     *
     **/
    function get_object_relationship_parent($ControlledObject_id, $on_what_id, $CORelationship_id=false, $objectData=false) {
        $con = $this->DBConnection;
        $table = "ControlledObjectRelationship";
        $objectList=array();
        $sql = "SELECT CORelationship_id, ParentControlledObject_id, on_what_field, on_what_table, on_what_child_field, on_what_parent_field, cross_table, singular FROM $table INNER JOIN ControlledObject ON ControlledObject_id=ParentControlledObject_id WHERE ChildControlledObject_id=$ControlledObject_id";
        if ($CORelationship_id) { $sql .=" AND CORelationship_id=$CORelationship_id"; }
        $rs = $con->execute($sql);
        if (!$rs) { db_error_handler($con, $sql); return false; }
        if ($rs->numRows()>0) {
            while (!$rs->EOF) {
                if (!trim($rs->fields['on_what_child_field'])) {
                    $on_what_child_field=$rs->fields['on_what_field'];
                } else { $on_what_child_field = $rs->fields['on_what_child_field']; }
                if (trim($rs->fields['cross_table'])) {
                    $on_what_parent_field=$rs->fields['on_what_parent_field'];
                    $objectData=$this->get_controlled_object_data($ControlledObject_id, $on_what_id, false, false, false, false, $rs->fields['cross_table'] );                    
                    if (!$objectData) { $rs->movenext(); continue; }
                    if (!is_array(current($objectData))) $objectData=array($objectData);
                    $ret=array();
                    foreach ($objectData as $object) {
                        if (trim($object[$on_what_parent_field])) {
                            $ret[]=array('ControlledObject_id'=>$rs->fields['ParentControlledObject_id'],'on_what_id'=>$object[$on_what_parent_field]);
                        }
                    }
                    if (count($ret)>0) {
                        $objectList[$rs->fields['CORelationship_id']] = $ret;
                    }                    
                } else {
                    if (!$objectData) { $objectData = $this->get_controlled_object_data($ControlledObject_id, $on_what_id); }
                    if ($objectData) {
                        if ($rs->fields['singular']==1) {
                            $on_what_table=$objectData['on_what_table'];
                            if ($rs->fields['on_what_table']==$objectData['on_what_table']) {
                                $objectList[$rs->fields['CORelationship_id']] = array('ControlledObject_id'=>$rs->fields['ParentControlledObject_id'],'on_what_id'=>$objectData['on_what_id']);
                            }
                        }
                        else {
                            $objectList[$rs->fields['CORelationship_id']] = array('ControlledObject_id'=>$rs->fields['ParentControlledObject_id'],'on_what_id'=>$objectData[$on_what_child_field]);
                        }
                    }
                }
                $rs->movenext();
            }
        }
        if (count($objectList)>0) { return $objectList; }
        return false;
    }
    
    /*****************************************************************************/
    /** function get_object_adodbconnection
     *
     * Returns array of fields contained within a particular controlled object
     *
     * @param string $data_source_name specifying which data source to look for data in
     * @return adodbconnection to correct data source to access object data
     *
     **/
    function get_object_adodbconnection($data_source_name='default') {
        $authInfo=array();
        foreach ($this->authCallbacks as $ac) {
            if (function_exists($ac)) {
//                echo "EXECUTING $ac($authInfo, $data_source_name)";
                $ret=$ac($authInfo, $data_source_name);
                if ($ret) return $ret;
            }
        }
        if (array_key_exists($data_source_name, $authInfo)) {
            $this->DBOptions[$data_source_name]=$authInfo[$data_source_name];
        }
       $xcon = &adonewconnection($this->DBOptions[$data_source_name]['db_dbtype']);
       $xcon->nconnect($this->DBOptions[$data_source_name]['db_server'], $this->DBOptions[$data_source_name]['db_username'], $this->DBOptions[$data_source_name]['db_password'], $this->DBOptions[$data_source_name]['db_dbname']);
       //$xcon->debug=1;
       return $xcon;
    }
    
    /*****************************************************************************/
    /** function get_controlled_object_data
     *
     * Returns array of fields contained within a particular controlled object.  
     * Builds a query based on table for the controlled object, as well a SQL restriction clause based on restrictionFields passed in.
     * Using parameters, this function can be coaxed into returning the database handle and recordset object instead of just an array of fields
     *
     * @param integer ControlledObject_id for which to find parents
     * @param integer on_what_id to specify which particular ControlledObject to check
     * @param array restrictionFields can be used to restrict the controlled object data returned
     * @param string operator optionally overriding logical operator AND separating clauses in restrictionFields
     * @param bool returncomponents optionally return array with key 'con' with connection and 'sql' with sql statement
     * @param string $_on_what_table optionally overrides the table for the controlled object
     * @return Array containing fields specific to the controlled object
     *
     **/        
    function get_controlled_object_data($ControlledObject_id, $on_what_id=false, $restrictionFields=false, $operator="AND", $returncomponents=false, $limit=false, $_on_what_table=false) {
        $con = $this->DBConnection;
        $sql = "SELECT *, data_source.* FROM ControlledObject LEFT OUTER JOIN data_source on ControlledObject.data_source_id=data_source.data_source_id WHERE ControlledObject_id=$ControlledObject_id";
        
        $rs = $con->execute($sql);
        if (!$rs) { db_error_handler($con, $sql); return false; }
        if ($rs->numRows()>0) {
            if (!trim($_on_what_table)) {
                $on_what_table = $rs->fields['on_what_table'];
            } else { $on_what_table=$_on_what_table; }
            $on_what_field = $rs->fields['on_what_field'];
            $data_source_name=$rs->fields['data_source_name'];
            if (!$data_source_name) {
                //retrieve data source name from the context that we're currently in, for this controlled object and on_what_id
                $data_source_name=$this->get_data_source_from_context($ControlledObject_id, $on_what_id); 
            }
            
            $xcon=$this->get_object_adodbconnection($data_source_name);
            
            $ret=array();
            $ret['acl_on_what_field']=$on_what_field;
            if ((trim($on_what_id)!==false) AND (trim($on_what_id)!=='')) {
                $restrictionFields[$on_what_field]=$on_what_id;
            }
            
	    $where=array();
            if ($restrictionFields AND count($restrictionFields)>0) {
                foreach ($restrictionFields as $key=>$value) {
                    if (is_array($value) AND (count($value)>0)) {
                        //array of values, seach for all of them
                        $where[]="($key IN (" . implode(",",$value)."))";
                    } else {
                        if ($value!==false) {
                            $where[]="($key=$value)";
                        }
                    }                       
                }
            }
            
            $wherestr = implode(" $operator ", $where);
            $sql = "SELECT * FROM $on_what_table";
            if ($wherestr) {
                $sql .= " WHERE $wherestr";
            }
            if ($returncomponents) {
                $ret['sql']=$sql;
                $ret['con']=$xcon;
                return $ret;
            }
            if ($limit) { $nrs=$xcon->SelectLimit($sql, $limit); }
            else {
                $nrs = $xcon->execute($sql);
            }
            if (!$nrs) { db_error_handler($xcon, $sql); return false; }
            if ($nrs->numRows()>0) {
                if ($nrs->numRows()==1) {
                    return array_merge($ret, $nrs->fields);
                }
                while (!$nrs->EOF) {
                    $ret[$nrs->fields[$on_what_field]]=$nrs->fields;
                    $nrs->movenext();
                }
                return $ret;
            }
        }
        
        return false;
    }
    /*****************************************************************************/
    /** function get_object_groups
     *
     * Returns a list of groups that contain a specific Controlled Object and value
     *
     * @param integer ControlledOjbect_id identifying which object to retrieve a list of groups
     * @param integer on_what_id identifying which particular object ID to search for
     * @return Array containing a collection of groups
     *
     **/
    function get_object_groups($ControlledObject_id,$on_what_id) {
        $con = $this->DBConnection;
        $table = "GroupMember";
        $groupList=array();
        $sql = "SELECT * FROM $table WHERE ControlledObject_id=$ControlledObject_id AND on_what_id=$on_what_id";
        $rs = $con->execute($sql);
        if (!$rs) { db_error_handler($con, $sql); return false; }
        if ($rs->numRows()>0) {
            while (!$rs->EOF) {
                $groupList[$rs->fields['GroupMember_id']]=$rs->fields['Group_id'];
                $rs->movenext();
            }
        }
        
        //find groups which match the criteria in the GroupMember criteria table
        $criteriagroupList=$this->get_object_groups_by_criteria($ControlledObject_id, $on_what_id);
        if ($criteriagroupList) {
            $groupList=array_merge($groupList, $criteriagroupList);
        }
        
        //find groups which have this group as the child
        foreach ($groupList as $Group_id) {
            $parentList = $this->get_group_parents($Group_id);
            if ($parentList) { $groupList = array_merge($groupList, $parentList); }
        }
        
        if (count($groupList)>0) {
            return $groupList;
        }
        return false;
    }
    
    /*****************************************************************************/
    /** function get_group_parents
     *
     * Returns a list of parents that contain a group
     *
     * @param integer Group_id identifying group for which to retrieve a list of parents
     * @return Array containing a list of groups to which the referenced group belongs
     *
     **/

    function get_group_parents($Group_id) {
        if (!$Group_id) { return false; }
        $groupList = array();
        $parentList = $this->get_group_user(false, false, false, $Group_id);
        if ($parentList) {
            foreach ($parentList as $GroupUser) {
                $groupList = array_merge($groupList, array($GroupUser['Group_id']));
                $ret = $this->get_group_parents($GroupUser['Group_id']);
                if ($ret) {
                    $groupList = array_merge($groupList, $ret);
                }
            }
        }
        if (count($groupList)>0) {
            return $groupList;
        } else {
            return false;
        }    
    }
    
    /*****************************************************************************/
    /**
     *
     * Returns a list of groups that contain a specific Controlled Object, through the criteria on the groups
     *
     * @param integer ControlledObject_id identifying which object to retrieve a list of groups
     * @param integer on_what_id identifying which particular object ID to search for
     * @return Array containing a collection of groups
     *
     **/
    function get_object_groups_by_criteria($ControlledObject_id, $on_what_id) {
        $con = $this->DBConnection;
        $groupList=array();
        if (!$ControlledObject_id OR ($on_what_id===false)) { return false; }
        $sql = "SELECT * FROM GroupMember WHERE ControlledObject_id=$ControlledObject_id AND on_what_id<=0";
        $rst = $con->execute($sql);
        if (!$rst) { db_error_handler($con, $sql); return false; }
        while (!$rst->EOF) {
            $gkey=array_search($rst->fields['Group_id'], $groupList);
            //make sure this group isn't already found in the list
            if ($gkey===false) {
                $objects=$this->get_group_members_by_criteria($rst->fields['GroupMember_id'],$rst->fields);
                if ($objects) {
                    foreach ($objects as $data) {
                        if ($data['on_what_id']==$on_what_id) {
                            $groupList[]=$rst->fields['Group_id'];
                            break;
                        }
                    }
                }
            }
            $rst->movenext();
        }
        if (count($groupList)>0) {
            return $groupList;
        } else return false;
    }
    /*****************************************************************************/
    /** function get_object_groups_recursive
     *
     * Returns a list of groups that contain a specific Controlled Object and value, recursively up the object tree
     *
     * @param integer ControlledObject_id identifying which object to retrieve a list of groups
     * @param integer on_what_id identifying which particular object ID to search for
     * @param integer $_CORelationship_id optionally specify which branch of the tree to search up
     * @return Array containing a collection of groups
     *
     **/
    function get_object_groups_recursive($ControlledObject_id,$on_what_id,$_CORelationship_id=false) {
//    	echo "$ControlledObject_id,$on_what_id,$_CORelationship_id=false";
    	if (!trim($on_what_id)) return false;
        //get the group list for this level
        $groupList = $this->get_object_groups($ControlledObject_id, $on_what_id);
        if (!$groupList) { $groupList = array(); }
        
        //get the list of parent objects
        $objectParents = $this->get_object_relationship_parent($ControlledObject_id, $on_what_id, $_CORelationship_id);
        if ($objectParents AND is_array($objectParents)) {
            foreach ($objectParents as $CORelationship_id => $aparent) {
                //get the group list of all parent objects
                if (!is_array(current($aparent))) $aparent=array($aparent);
                foreach ($aparent as $parent) {
                    $ret = $this->get_object_groups_recursive($parent['ControlledObject_id'],$parent['on_what_id']);
                    if ($ret) {
                        //if found, add them to the list of groups for this particular controlled object
                        $groupList = array_merge($groupList,$ret);
                    }
                }
            }
        }
        
        if (count($groupList)>0) {
            return $groupList;
        }
        return false;
    }

    
    /*****************************************************************************/
    /** function get_group
      *
      * Returns the id for the group based on the searched-for string
      *
      * @param string Group_name with text to search for within the names of groups
      * @return integer Group_id identifying the group in the database or false if not found
      * 
      */
    function get_group ($Group_name=false, $Group_id=false, $fuzzy=true) {     
        $tblName = "Groups";
        $con = $this->DBConnection;
        if (!$Group_name AND !$Group_id) { echo "Cannot locate group: not enough information"; return false; }
        //Search for group name exactly
        if ($Group_name) { $where="Group_name=" . $con->qstr($Group_name, get_magic_quotes_gpc()); }
        elseif ($Group_id) { $where="Group_id=$Group_id"; }
        $sql = "SELECT * from $tblName WHERE $where";
        $rs = $con->execute($sql);
        if (!$rs) { db_error_handler($con, $sql); return false; }
        if ($rs->numRows()>=1) {
            return $rs->fields;
        } elseif ($Group_name and $fuzzy) {
            $sql = "SELECT * from $tblName WHERE Group_name LIKE " . $con->qstr("%$Group_name%",get_magic_quotes_gpc());
            $rs = $con->execute($sql);
            if ($rs->numRows()>=1) {
                if (!$rs) { db_error_handler($con, $sql); return false; }
                return $rs->fields;
            }
        }
        return false;
     }
         
     /*****************************************************************************/
     /** function add_group
       *
       * Adds a group to the group list
       *
       * @param string Group_name with text identifying new group to add
       * @param integer parent with ID of parent group of this group (optional, adds group to parent group)
       * @return integer Group_id with new identifier for group in the database or false if duplicate/failed
       *
       */
     function add_group ($Group_name, $parent=false) {
        $tblName="Groups";
        $con = $this->DBConnection;
        
        //Find group, if already defined
        if ($this->get_group($Group_name, false,false)!== false) {
            return false;
        } else {
            //Create array to insert
            $GroupRow['Group_name']=$Group_name;
            
            //Create insert statement
            $sql = $con->getInsertSQL($tblName, $GroupRow, false);
            //Execute insert
            $rs=$con->execute($sql);
            if (!$rs) { db_error_handler($con, $sql); return false; }
            //Find and return newly added ID
            $groupID = $con->Insert_ID();
            
            if ($parent) {
                $ret=$this->add_group_group($parent, $groupID);
                if (!$ret) { return false; }
            }
            return $groupID;
        }
    }

     /*****************************************************************************/
     /** function delete_group
       *
       * Removes a group from the group list
       *
       * @param integer Group_id identifying which group to remove
       * @return bool indicating success (true) or failure (false) of delete
       *
       */
     function delete_group ($Group_id) {
        if (!$Group_id) { return false; }
        $tblName="Groups";
        $con = $this->DBConnection;
        
        $sql = "Delete from $tblName WHERE Group_id=$Group_id";
        $rs = $con->execute($sql);
        if (!$rs) { db_error_handler($con, $sql); return false; }
        else return true;
     }     

    /*****************************************************************************/
    /** function get_group_user
      *
      * Returns information for the user/group entry based on the searched-for parameters (either ChildGroup or User and Role)
      *
      * @param integer Group_id for group to search within 
      * @param integer User_id optional identifying which user to look up
      * @param integer User_id optional identifying which role this user has
      * @param integer ChildGroup_id optional identifying which child group to search for
      * @return array GroupUser containing Group_id, user_id, ChildGroup_id and Role_id
      * 
      */
    function get_group_user ($Group_id=false,  $User_id=false, $Role_id=false, $ChildGroup_id=false, $GroupUser_id=false, $include_group_name=false) {     
        if (!$Group_id AND (!$ChildGroup_id AND (!$User_id AND !$Role_id)) AND !$GroupUser_id) { 
            echo "Cannot get group user: bad input parameters<br>Group $Group_id Child $ChildGroup_id User $User_id Role $Role_id"; return false; }
        if (!$include_group_name) {
            $tblName = "GroupUser";
        } else {
            $tblName="GroupUser INNER JOIN Groups ON GroupUser.Group_id=Groups.Group_id ";
        }
        $con = $this->DBConnection;
        
        $where=array();
        
        if ($GroupUser_id) { $where[]="GroupUser_id=$GroupUser_id"; }
        if ($Group_id) { $where[]= "Group_id=$Group_id"; }
        if ($ChildGroup_id) {
             //search by child group
             $where[] = "ChildGroup_id=$ChildGroup_id";
         } elseif ($ChildGroup_id===NULL) {
            $where[] = "ChildGroup_id IS NULL";
        }
        if ($User_id) {
            $where[] = "user_id=$User_id";
        }
        if ($Role_id) {
            $where[] = "Role_id=$Role_id";
        }
        $wherestr = implode(" and ", $where);        
        //Search within group specified
        if ($include_group_name) {
            $sql = "SELECT GroupUser.*, Groups.Group_name FROM $tblName WHERE $wherestr";
        } else {
            $sql = "SELECT * FROM $tblName WHERE $wherestr";
        }
        $rs = $con->execute($sql);
        if (!$rs) { db_error_handler($con, $sql); return false; }
        if ($rs->numRows()>=1) {
            $ret=array();
            while (!$rs->EOF) {
                $ret[]= $rs->fields;
                $rs->movenext();
            }
            return $ret;
        } else {
            return false;
        }
     }
     
               
     /*****************************************************************************/
     /** function add_group_group
       *
       * Adds a group as a member of another group
       *
       * @param integer ParentGroup_id to specify which group to add into
       * @param integer Group_id of group to add to ParentGroup
       * @return integer GroupUser_id with new identifier for new Grouping in the database or false if duplicate/failed
       *
       */
    function add_group_group($ParentGroup_id, $Group_id) {
        $con = $this->DBConnection;
        $tblName = "GroupUser";
        //return false if parent or child not specified
        if (!$ParentGroup_id) { return false; }
        if (!$Group_id) { return false; }
        
        //look for existing relationship of this nature, return false if there it already exists
        if ($this->get_group_user($ParentGroup_id, false, false, $Group_id)!==false ) { echo "Group $Group_id already exists within parent group $ParentGroup_id"; return false; }
                
        //set of array for insert
        $GroupRow['Group_id']=$ParentGroup_id;
        $GroupRow['ChildGroup_id']=$Group_id;
        
        //create and execute insert SQL
        
        $sql = $con->getInsertSQL($tblName, $GroupRow,false);
        $rs = $con->execute($sql);
        
        //handle error if it happens
        if (!$rs) { db_error_handler($con,$sql); return false; }
        //get last insert ID, to return
        $groupuserID = $con->Insert_ID();
        return $groupuserID;

    }
    
     /*****************************************************************************/
     /** function add_group_user
       *
       * Adds a user to a role in a group
       *
       * @param integer Group_id to specify which group to add into
       * @param integer User_id of User to add to Group
       * @param integer Role_id of Role that user will have in the Group
       * @return integer GroupUser_id with new identifier for new entry in the group or false if duplicate/failed
       *
       */
    function add_group_user($Group_id, $User_id, $Role_id) {
        $con = $this->DBConnection;
        $tblName = "GroupUser";
        
        //return false if group, user or role not specified
        if (!$Group_id) { return false; }
        if (!$User_id) { return false; }
        if (!$Role_id) { return false; }
        
        //look for existing relationship of this nature, return false if there it already exists
        if ($this->get_group_user($Group_id, $User_id, $Role_id,false)!==false ) { "Cannot add user $User_id to group $Group_id with role $Role_id: already exists in group";return false; }
        
        //set of array for insert
        $GroupRow['Group_id']=$Group_id;
        $GroupRow['user_id']=$User_id;
        $GroupRow['Role_id']=$Role_id;
        
        //create and execute insert SQL
        
        $sql = $con->getInsertSQL($tblName, $GroupRow,false);
        $rs = $con->execute($sql);
        
        //handle error if it happens
        if (!$rs) { db_error_handler($con,$sql); return false; }
        //get last insert ID, to return
        $groupuserID = $con->Insert_ID();
        return $groupuserID;

    }
    
     /*****************************************************************************/
     /** function delete_group_user
       *
       * Deletes a user or group out of a group, by specific GroupUser id
       *
       * @param integer GroupUser_id to specify which group to add into
       * @return bool indicating success (true) or failure (false) for the delete
       *
       */
    function delete_group_user($GroupUser_id) {
        if (!$GroupUser_id) { return false; }
        $tblName="GroupUser";
        $con = $this->DBConnection;
        
        $sql = "Delete from $tblName WHERE GroupUser_id=$GroupUser_id";
        $rs = $con->execute($sql);
        if (!$rs) { db_error_handler($con, $sql); return false; }
        else return true;
        
    }
    /*****************************************************************************/
    /** function get_role_users
      *
      * Returns all users that exist in a particular role, optionally limited by Groups
      *
      *
      * @param integer Role_id identifying which role to find users for
      * @param integer Group_id optionally specify group to limit search with
      * @return array of arrays containing User_id and $Group_id for which this user has this role
      * 
      */
    function get_role_users($Role_id, $Groups=false) {
        if (!$Role_id) { return false; }
        $tblName = "GroupUser";
        $con = $this->DBConnection;
        if ($Groups) {
            $GroupList=implode(",", $Groups);
            $GroupWhere ="AND Group_id IN ($GroupList)";
        } else $GroupWhere ='';
        
        $RoleWhere="Role_id=$Role_id";
        $sql = "SELECT * FROM $tblName WHERE $RoleWhere $GroupWhere";
        $rst = $con->execute($sql);
        if (!$rst) { db_error_handler($con, $sql); return false; }
        if (!$rst->EOF) {
            $ret=array();
            while (!$rst->EOF) {
                $ret[]=$rst->fields;
                $rst->movenext();
            }
        }       
        return $ret;
    }
    /*****************************************************************************/
    /** function get_user_roles
      *
      * Returns roles for a particular user in a particular group, based on the searched-for parameters (user_id and Group_id)
      *
      * @param integer Group_id for group to search within 
      * @param integer User_id identifying which user to look up
      * @return array Roles containing Role_id and Role_name of the roles for a user within the group
      * 
      */
    function get_user_roles ($Group_id,  $User_id) {
        if (!$User_id) { return false; }
        $tblName = "GroupUser";
        $con = $this->DBConnection;
        $roleList=array();
        if ($Group_id) { $GroupWhere="Group_id=$Group_id AND "; } else { $GroupWhere=''; }
        $sql = "SELECT * FROM $tblName WHERE $GroupWhere user_id=$User_id";
        $rs = $con->execute($sql);
        if (!$rs) { db_error_handler($con, $sql); return false; }
        if ($rs->numRows()>0) {
            while (!$rs->EOF) {
                $roleList[$rs->fields['GroupUser_id']] = $rs->fields['Role_id'];
                $rs->movenext();
            }
        }
        //now search in parent groups (if group is specified)
        if ($Group_id) {
            $groupList = $this->get_group_user(false, false, false, $Group_id);
            if ($groupList and is_array($groupList)) {
                foreach ($groupList as $groupInfo) {
                    $result = $this->get_user_roles($groupInfo['Group_id'], $User_id);
                    if ($result) { $roleList = array_merge($roleList, $result); }
                }
            }
        }
        if (count($roleList)>0) {
            return $roleList;
        }      
        return false;
    }

    /*****************************************************************************/
    /** function get_user_roles_by_array
      *
      * Returns roles for a particular user in a particular group, based on the searched-for parameters (user_id and array of Groups)
      *
      * @param array Groups for groups to search within 
      * @param integer User_id identifying which user to look up
      * @return array with key Roles containing Role_id and Role_name of the roles for a user within the group, and array key GroupRoles by group, with roles in each group
      * 
      */
    function get_user_roles_by_array ($Groups,  $User_id) {
        if (!$User_id) { return false; }
        $retRoles=array();
        foreach ($Groups as $Group_id) {
            $roles = $this->get_user_roles($Group_id, $User_id);
            if ($roles and is_array($roles)) { $retRoles = array_unique(array_merge($retRoles, $roles)); $groupRoles[$Group_id]=$roles; }
        }
        $ret['Roles']=$retRoles;
        $ret['GroupRoles']=$groupRoles;
        if (count($retRoles)>0) { return $ret; }
        return false;
    }
    
     /*****************************************************************************/
     /**
       *
       * Searches for a particular group_member_id or a set of group_member_id's based on parameters
       *
       * @param integer Group_id to specify which group to search within
       * @param integer ControlledObject_id identifying what class of ControlledObject is being searched for
       * @param integer on_what_id identifying what value identifies the unique ControlledObject (if left false returns array of member_ids
       * @param string criteria_table identifying what table to look into for criteria
       * @param string critiera_resultfield identifying what field will contain the IDs of the controlled object to include in the group
       * @return integer GroupMember_id with new identifier for new entry in the group or false if duplicate/failed (or array if no on_what_id is specified)
       *
       */
    function get_group_member($Group_id=false, $ControlledObject_id=false, $on_what_id=false, $criteria_table=false, $criteria_resultfield=false, $GroupMember_id=false) {
        if (!$Group_id or !$ControlledObject_id) { return false; }
        $tblName = "GroupMember";
        $con = $this->DBConnection;
        
        if ($GroupMember_id) { $where = " GroupMember_id=$GroupMember_id"; }
        else { 
            $where = " Group_id=$Group_id and ControlledObject_id=$ControlledObject_id"; 
            if ($on_what_id) { $where .= " AND on_what_id=$on_what_id"; }
            if ($criteria_table) { $where.=" AND criteria_table=" . $con->qstr($criteria_table); }
            if ($criteria_resultfield) { $where.=" AND criteria_resultfield=" . $con->qstr($criteria_resultfield); }
        }
        
        //Search within group specified
        $sql = "SELECT * FROM $tblName WHERE $where";
        
        $rs = $con->execute($sql);
        if (!$rs) { db_error_handler($con, $sql); return false; }
        if (($rs->numRows()>1) and ($on_what_id===false)) {
            while (!$rs->EOF) {
                $ret[$rs->fields['GroupMember_id']] = $rs->fields;
                $rs->movenext();
            }
            return $ret;
        } elseif ($rs->numRows()==1) {
            return array($rs->fields['GroupMember_id'] => $rs->fields);
        } else {
            return false;
        }        
    }

    
     /*****************************************************************************/
     /**
       *
       * Wrapper to add_group_member for adding a particular ControlledObject to a group, based on the ControlledObject type and id
       *
       * @param integer Group_id to specify which group to add into
       * @param integer ControlledObject_id identifying what class of ControlledObject is being added
       * @param integer on_what_id identifying what value identifies the unique ControlledObject
       * @return integer GroupMember_id with new identifier for new entry in the group or false if duplicate/failed
       *
       */
    function add_group_object($Group_id, $ControlledObject_id, $on_what_id) {
        return $this->add_group_member($Group_id, $ControlledObject_id, $on_what_id);
    }
    
     /*****************************************************************************/
     /**
       *
       * Adds a particular ControlledObject to a group, based on the ControlledObject type and the value or criteria used to identify the specify ControlledObjects
       *
       * @param integer Group_id to specify which group to add into
       * @param integer ControlledObject_id identifying what class of ControlledObject is being added
       * @param integer on_what_id identifying what value identifies the unique ControlledObject
       * @param string criteria_table identifying what table to look into for criteria
       * @param string critiera_resultfield identifying what field will contain the IDs of the controlled object to include in the group
       * @return integer GroupMember_id with new identifier for new entry in the group or false if duplicate/failed
       *
       */
    function add_group_member($Group_id, $ControlledObject_id, $on_what_id, $criteria_table=false, $criteria_resultfield=false) {
        if (!$Group_id OR !$ControlledObject_id or (!$on_what_id AND !$criteria_table AND !$criteria_resultfield)) { return false; }

        $con = $this->DBConnection;
        $tblName = "GroupMember";
 
        //look for existing relationship of this nature, return false if there it already exists
        if ($this->get_group_member($Group_id, $ControlledObject_id, $on_what_id, $criteria_table, $criteria_resultfield)!== false ) {
            return false;
        }
                
        //set of array for insert
        $GroupRow['Group_id']=$Group_id;
        $GroupRow['ControlledObject_id']=$ControlledObject_id;
        if ($on_what_id)
            $GroupRow['on_what_id']=$on_what_id;
        if ($criteria_table)
            $GroupRow['criteria_table']=$criteria_table;
        if ($criteria_resultfield)
            $GroupRow['criteria_resultfield']=$criteria_resultfield;
        
        //create and execute insert SQL
        
        $sql = $con->getInsertSQL($tblName, $GroupRow,false);
        $rs = $con->execute($sql);
        
        //handle error if it happens
        if (!$rs) { db_error_handler($con,$sql); return false; }
        //get last insert ID, to return
        $groupmemberID = $con->Insert_ID();
        return $groupmemberID;
        
        
    }
    
     /*****************************************************************************/
     /** function delete_group_object
       *
       * Deletes an instance of a controlled object from a group,, by specific GroupMember id
       *
       * @param integer GroupMember_id to specify which specific group member to delete
       * @return bool indicating success (true) or failure (false) for the delete
       *
       */
    function delete_group_object($GroupMember_id) {
        if (!$GroupMember_id) { return false; }
        $tblName="GroupMember";
        $con = $this->DBConnection;
        
        $this->delete_group_member_criteria($GroupMember_id);
        $sql = "Delete from $tblName WHERE GroupMember_id=$GroupMember_id";
        $rs = $con->execute($sql);
        if (!$rs) { db_error_handler($con, $sql); return false; }
        else return true;
        
    }
    
     /*****************************************************************************/
     /**
        *
       * Searches for criteria on a group member, based on GroupMember_ld and fieldname/value/operator, or on the GroupMemberCriteria_id
       *
       * @param integer GroupMember_id to specify which group to search within
       * @param integer criteria_fieldname identifying what field in the table should be examined
       * @param string criteria_value identifying what value to compare fieldname against.  Must include quotes if fieldname is a string field
       * @param string criteria_operator to be used in the comparison, defaults to '=', should be 'LIKE' for case insensitive string compare, IS for null/not null, or >,<,>=,<=
       * @return array GroupMemberCriteria with criteria items matching passed in parameters
       *
       */
    function get_group_member_criteria($GroupMember_id=false, $criteria_fieldname=false, $criteria_value=false,$criteria_operator=false, $GroupMemberCriteria_id=false) {
        if (!$GroupMember_id AND !$GroupMemberCriteria_id) { return false; }
        $tblName = "GroupMemberCriteria";
        $con = $this->DBConnection;
        
        if ($GroupMemberCriteria_id) { $where = " GroupMemberCriteria_id=$GroupMemberCriteria_id"; }
        else {
            $where = " GroupMember_id=$GroupMember_id"; 
            if ($criteria_fieldname) { $where .= " AND criteria_fieldname=" . $con->qstr($criteria_fieldname); }
            if ($criteria_value) { $where .= " AND criteria_value=" . $con->qstr($criteria_value); }
            if ($criteria_operator) { $where .= " AND criteria_operator=" . $con->qstr($criteria_operator); }
        }
        
        //Search within group specified
        $sql = "SELECT * FROM $tblName WHERE $where";
        $rs = $con->execute($sql);
        if (!$rs) { db_error_handler($con, $sql); return false; }
        
        if (!$rs->EOF) {
            while (!$rs->EOF) {
                $ret[] = $rs->fields;
                $rs->movenext();
            }
            return $ret;
        } else {
            return false;
        }        
    }
     
     /*****************************************************************************/
     /** 
       *
       * Adds criteria on a group object entry, to give group ownership based on values in a table
       *
       * @param integer GroupMember_id to specify which Group Member entry to add criteria to
       * @param string criteria_fieldname identifying what field in the table should be examined
       * @param string criteria_value identifying what value to compare fieldname against.  Must include quotes if fieldname is a string field
       * @param string criteria_operator to be used in the comparison, defaults to '=', should be 'LIKE' for case insensitive string compare, IS for null/not null, or >,<,>=,<=
       * @return integer GroupMemberCriteria_id with new identifier for new entry in database or false if duplicate/failed
       *
       */
    function add_group_member_criteria($GroupMember_id, $criteria_fieldname, $criteria_value, $criteria_operator='=') {
        if (!$GroupMember_id OR !$criteria_fieldname or !$criteria_value OR !$criteria_operator) { return false; }

        $con = $this->DBConnection;
        $tblName = "GroupMemberCriteria";
 
        //look for existing relationship of this nature, return false if there it already exists
        if ($this->get_group_member_criteria($GroupMember_id, $criteria_fieldname, $criteria_value, $criteria_operator)!== false ) {
            return false;
        }
                
        //set of array for insert
        $CriteriaRow=array();
        $CriteriaRow['GroupMember_id']=$GroupMember_id;
        $CriteriaRow['criteria_fieldname']=$criteria_fieldname;
        $CriteriaRow['criteria_value']=$criteria_value;
        $CriteriaRow['criteria_operator']=$criteria_operator;
        
        //create and execute insert SQL        
        $sql = $con->getInsertSQL($tblName, $CriteriaRow, get_magic_quotes_gpc());
        if ($sql) {
            $rs = $con->execute($sql);
            
            //handle error if it happens
            if (!$rs) { db_error_handler($con,$sql); return false; }
            //get last insert ID, to return
            $ID = $con->Insert_ID();
            return $ID;
        } else return false;                
    }
     
     /*****************************************************************************/
     /**
       *
       * Deletes criteria from a group member, by GroupMember_id, and optional criteria
       *
       * @param integer GroupMember_id to specify which specific group member criteria to delete
       * @param array optionally providing an associative array specifying the parameters to match for deletion
       * @param integer GroupMemberCriteria_id to specify a particular GroupMemberCriteria item by ID
       * @return bool indicating success (true) or failure (false) for the delete
       *
       */
    function delete_group_member_criteria($GroupMember_id=false, $criteriaArray=false, $GroupMemberCriteria_id=false) {
        if (!$GroupMember_id AND !$GroupMemberCriteria_id) { return false; }
        $tblName="GroupMemberCriteria";
        $con = $this->DBConnection;
        $where=array();
        if ($GroupMember_id) {
            $where[]="GroupMember_id=$GroupMember_id";
        }
        if ($GroupMemberCriteria_id) {
            $where[]="GroupMemberCriteria_id=$GroupMemberCriteria_id";
        }
        
        if ($criteriaArray AND is_array($criteriaArray)) {
            if ($criteriaArray['criteria_fieldname']) {
                $where[]="criteria_fieldname=".$con->qstr($criteriaArray['criteria_fieldname']);
            }
            if ($criteriaArray['criteria_value']) {
                $where[]="criteria_value=".$con->qstr($criteriaArray['criteria_value']);
            }
            if ($criteriaArray['criteria_operator']) {
                $where[]="criteria_operator=".$con->qstr($criteriaArray['criteria_operator']);
            }
        }
        
        $wherestr = implode(" AND ", $where);
        $sql = "Delete from $tblName WHERE $wherestr";
        $rs = $con->execute($sql);
        if (!$rs) { db_error_handler($con, $sql); return false; }
        else return true;
        
    }

    /*****************************************************************************/
    /** function get_role
      *
      * Returns information for the role based on the searched-for string or id
      *
      * @param string Role_name with text to search for within the names of roles
      * @param integer Role_name with text to search for within the names of roles
      * @return array Role_id keyed by Role_id, identifying the role in the database or false if not found
      * 
      */
    function get_role ($Role_name=false,$Role_id=false, $fuzzy=true) {     
        $tblName = "Role";
        $con = $this->DBConnection;
        
        //Search for role exactly
            if ($Role_name) { $where = "Role_name=" . $con->qstr($Role_name, get_magic_quotes_gpc()); }
            elseif ($Role_id) { $where = "Role_id=$Role_id"; }
            
        $sql = "SELECT * from $tblName WHERE $where"; //Role_name=" . $con->qstr($Role_name, get_magic_quotes_gpc());
        $rs = $con->execute($sql);
        if (!$rs) { db_error_handler($con, $sql); return false; }
        if ($rs->numRows()>=1) {
            return $rs->fields;
        } elseif ($Role_name and $fuzzy){
            $sql = "SELECT * from $tblName WHERE Role_name LIKE " . $con->qstr("%$Role_name%",get_magic_quotes_gpc());
            $rs = $con->execute($sql);
            if ($rs->numRows()>=1) {
                if (!$rs) { db_error_handler($con, $sql); return false; }
                return $rs->fields;
            }
        }
        return false;
     }
     
     /*****************************************************************************/
     /** function add_role
       *
       * Adds a role to the list of available roles
       *
       * @param string Role_name with text identifying new role type to add
       * @return integer Role_id with new identifier for role in the database or false if duplicate/failed
       *
       */
     function add_role ($Role_name) {
        $tblName="Role";
        $con = $this->DBConnection;
        
        //Find role, if already defined
        if ($this->get_role($Role_name)!==false) {
            //If role already exists, do not add, return false instead
            return false;
        } else {
            //Create array to insert
            $RoleRow['Role_name']=$Role_name;
            
            //Create insert statement
            $sql = $con->getInsertSQL($tblName, $RoleRow, false);
            //Execute insert
            $rs=$con->execute($sql);
            if (!$rs) { db_error_handler($con, $sql); return false; }
            //Find and return newly added ID
            $roleID = $con->Insert_ID();
            
            return $roleID;
        }
     
     }
     
     /*****************************************************************************/
     /** function delete_role
       *
       * Deletes a role from the role list
       *
       * @param integer Role_id identifying role to delete
       * @return bool indicating success (true) or failure (false) for the delete
       *
       */
     function delete_role ($Role_id) {
        if (!$Role_id) { return false; }
        $tblName="Role";
        $con = $this->DBConnection;
        
        $sql = "Delete from $tblName WHERE Role_id=$Role_id";
        $rs = $con->execute($sql);
        if (!$rs) { db_error_handler($con, $sql); return false; }
        else return true;     
     }

     
     /*****************************************************************************/
     /** function get_role_permission
       *
       * Searches for a particular controlled object relationship or a set of group_member_id's based on parameters
       *
       * @param integer ParentControlledObject_id to specify which object is the parent in the relationship
       * @param integer ChildControlledObject_id to specify which object is the child in the relationship
       * @return integer RolePermission_id with new identifier for new entry for the relationship or false if duplicate/failed (or array if no on_what_id is specified)
       *
       */
    function get_role_permission($Roles=false, $CORelationships=false, $Scopes=false, $Permission_id=false, $RolePermission_id=false, $inheritable=true) {
        if (!$Roles and !$CORelationships and !$RolePermission_id) { return false; }
        $tblName = "RolePermission";
        
        $con = $this->DBConnection;
        $where=array();
        
        if ($RolePermission_id) { $where[]="RolePermission_id=$RolePermission_id"; }
        else {
            if ($Roles) { 
                if (is_array($Roles)) { $where[]="Role_id IN (".implode(",",$Roles) .")"; }
                else{ $where[]="Role_id=$Roles"; }
            }
            if ($CORelationships) {
                if (is_array($CORelationships)) { $where[]="CORelationship_id IN (".implode(",",$CORelationships) .")"; }
                else{ $where[]="CORelationship_id=$CORelationships"; }
            }
            if ($Permission_id) { $where[]="Permission_id=$Permission_id"; }
            
            if ($inheritable) { $where[]="Inheritable_flag=1"; }  else if ($inheritable===NULL) { $where[]='Inheritable_flag=0'; }
            
            if ($Scopes) { 
                if (!is_array($Scopes)) $Scopes=array($Scopes);
                $scopewhere=array();
                $scopewhere_str=false;
                foreach ($Scopes as $Scope) {
                    $scopewhere[]="(Scope=" . $con->qstr($Scope,get_magic_quotes_gpc()) . ")"; 
                }
                if (count($scopewhere)>0) {
                    $scopewhere_str=implode(" OR ",$scopewhere);
                 }
                if ($scopewhere_str) $where[]="($scopewhere_str)";
            }                
        }
        $whereclause = implode (" and ", $where);
        //Search within group specified
        $sql = "SELECT * FROM $tblName WHERE $whereclause ORDER BY Permission_id";
         
        $rs = $con->execute($sql);
        if (!$rs) { db_error_handler($con, $sql); return false; }
        if (!$rs->EOF) {
            $ret=array();
            while (!$rs->EOF) {
                $ret[$rs->fields['RolePermission_id']] = $rs->fields;
                $rs->movenext();
            }
            return $ret;
        } else {
            return false;
        }        
    }

     
     
     /*****************************************************************************/
     /** function add_role_permission
       *
       * Adds a permission on a type of controlled object to a role
       *
       * @param integer Role_id specifying which role to add this permission to
       * @param integer CORelationship_id specifying which controlled object the permission applies to
       * @param string Scope determining what scope the permission should apply to (currently World, Group and User)
       * @param integer Permission_id specifying which type of permission to add on this controlled object for this role
       * @param boolean Inheritable specifying whether permission can be inherited by children (defaults to true, children inherit permissions of the parent)
       * @return integer RolePermission_id with new identifier for role permission in the database or false if duplicate/failed
       *
       */
     function add_role_permission ($Role_id, $CORelationship_id, $Scope, $Permission_id, $inheritable=true) {

	global $xrms_db_dbtype;
        if (!$Role_id or !$CORelationship_id or !$Scope or !$Permission_id) { return false; }
        
        $tblName="RolePermission";
        $con = $this->DBConnection;
        
        //no duplicates
        if ($this->get_role_permission($Role_id, $CORelationship_id, $Scope, $Permission_id, false, $inheritable)!==false) { return false; }
        
        $RolePermissionRow['Role_id']=$Role_id;
        $RolePermissionRow['CORelationship_id']=$CORelationship_id;
        $RolePermissionRow['Scope']=$Scope;
        
        $RolePermissionRow['Permission_id']=$Permission_id;
        if ($inheritable) {
            $RolePermissionRow['Inheritable_flag']=1;
        } else {
            $RolePermissionRow['Inheritable_flag']=0;
        }
        
        //Create insert statement
        $sql = $con->getInsertSQL($tblName, $RolePermissionRow, false);
        //Execute insert
        $rs=$con->execute($sql);
        
        if (!$rs) { db_error_handler($con, $sql); return false; }
        //Find and return newly added ID
        $controlled_objectID = $con->Insert_ID();
        
        return $controlled_objectID;
        
        return false;
     }
     
     /*****************************************************************************/
     /** function delete_role_permission
       *
       * Adds a permission on a type of controlled object to a role
       *
       * @param integer RolePermission_id specifying which role permission to delete
       * @return bool indicating success (true) or failure (false) of delete
       *
       */
    function delete_role_permission($RolePermission_id) {
        if (!$RolePermission_id) { return false; }
        $tblName="RolePermission";
        $con = $this->DBConnection;
        
        $sql = "Delete from $tblName WHERE RolePermission_id=$RolePermission_id";
        $rs = $con->execute($sql);
        if (!$rs) { db_error_handler($con, $sql); return false; }
        else return true;     
    
    }    

    /*****************************************************************************/
    /** function get_controlled_object
      *
      * Returns data for the controlled object based on the searched-for string or id
      *
      * @param string ControlledObject_name with text to search for within the names of controlled objects
      * @return array with data about a ControlledObject or false if not found
      * 
      */
    function get_controlled_object($ControlledObject_name=false, $ControlledObject_id=false, $fuzzy=true, $on_what_table=false) {
        $tblName = "ControlledObject";
        $con = $this->DBConnection;
        if ($ControlledObject_name) { $where="ControlledObject_name=" . $con->qstr($ControlledObject_name, get_magic_quotes_gpc()); }
        elseif ($ControlledObject_id) { $where="ControlledObject_id=$ControlledObject_id"; }
        elseif($on_what_table) { $where="on_what_table=".$con->qstr($on_what_table, get_magic_quotes_gpc()); }
        
        if (!$where) { return false; } //echo "No Controlled object selected"; return false; }        
        //Search for controlled_object name exactly
        $sql = "SELECT * from $tblName WHERE $where"; 
        $rs = $con->execute($sql);
        if (!$rs) { db_error_handler($con, $sql); return false; }
        if ($rs->numRows()==1) {
            return $rs->fields;
        } else {
            if ($ControlledObject_name and $fuzzy) {
            $sql = "SELECT ControlledObject_id from $tblName WHERE ControlledObject_name LIKE " . $con->qstr("%$ControlledObject_name%",get_magic_quotes_gpc());
            $rs = $con->execute($sql);
            if ($rs->numRows()>=1) {
                if (!$rs) { db_error_handler($con, $sql); return false; }
                return $rs->fields;
            }
            }
        }
        return false;
     }

    /*****************************************************************************/
    /** function get_data_source
      *
      * Returns data about the data source
      *
      * @param string data_source_name with name to search for
      * @param integer data_source_id identifying the data source in the database or false if not found
      * @return array containing data source information
      * 
      */
    function get_data_source($data_source_name=false, $data_source_id=false) {
        $tblName="data_source";
        $con = $this->DBConnection;
        if ($data_source_name) { $where="data_source_name=" . $con->qstr($data_source_name, get_magic_quotes_gpc()); }
        elseif ($data_source_id) { $where="data_source_id=$data_source_id"; }
        if (!$where) { return false; } //echo "No Controlled object selected"; return false; }        
        //Search for exact record
        $sql = "SELECT * from $tblName WHERE $where"; 
        $rs = $con->execute($sql);
        if (!$rs) { db_error_handler($con, $sql); return false; }
        if ($rs->numRows()==1) {
            return $rs->fields;
        } else {
            return false;
        }    
    }
                 
     /*****************************************************************************/
     /** function add_data_source
       *
       * Adds a controlled object to the list of controlled objects
       *
       * @param string name of new data source
       * @return integer data_source_id with identifier for newly added data_source, or false if duplicate/failed
       *
       */
    function add_data_source($data_source_name) {
        $tblName="data_source";
        $con = $this->DBConnection;
        
        //Find data source, if already defined
        if ($this->get_data_source($data_source_name)!==false) { return false; }
        else {
            //Create array to insert
            $data_sourceRow['data_source_name']=$data_source_name;
            $data_sourceRow['on_what_table']=$on_what_table;
            $data_sourceRow['on_what_field']=$on_what_field;          
            
            //Create insert statement
            $sql = $con->getInsertSQL($tblName, $data_sourceRow, false);
            //Execute insert
            $rs=$con->execute($sql);
            if (!$rs) { db_error_handler($con, $sql); return false; }
            //Find and return newly added ID
            $data_sourceID = $con->Insert_ID();
            
            return $data_sourceID;
        }
    }
     
     /*****************************************************************************/
     /** function delete_data_source
       *
       * Deletes a data source from the system
       *
       * @param integer data_source_id specifying which role permission to delete
       * @return bool indicating success (true) or failure (false) of delete
       *
       */
    function delete_data_source($data_source_id) {
        if (!$data_source_id) { return false; }
        $tblName="data_source";
        $con = $this->DBConnection;
        
        $sql = "Delete from $tblName WHERE data_source_id=$data_source_id";
        $rs = $con->execute($sql);
        if (!$rs) { db_error_handler($con, $sql); return false; }
        else return true;     
    
    }    
    
     /*****************************************************************************/
     /** function add_controlled_object
       *
       * Adds a controlled object to the list of controlled objects
       *
       * @param string name of new controlled object
       * @param string on_what_table identifying in what table the ControlledObject data is stored
       * @param string on_what_field identifying what fieldname identifies the unique row in the table describing the ControlledObject
       * @return integer ControlledObject_id with identifier for newly added ControlledObject, or false if duplicate/failed
       *
       */
    function add_controlled_object($ControlledObject_name, $on_what_table, $on_what_field, $user_field=false, $data_source_id=false, $display_errors=true) {
        $tblName="ControlledObject";
        $con = $this->DBConnection;
        
        //Find controlled object, if already defined
        $existing=$this->get_controlled_object($ControlledObject_name,false,false);
        if ($existing!==false) { return $existing['ControlledObject_id']; }
        else {
            //Create array to insert
            $ControlledObjectRow['ControlledObject_name']=$ControlledObject_name;
            $ControlledObjectRow['on_what_table']=$on_what_table;
            $ControlledObjectRow['on_what_field']=$on_what_field;          
            $ControlledObjectRow['data_source_id']=$data_source_id;
            if ($user_field) $ControlledObjectRows['user_field']=$user_field;
            
            //Create insert statement
            $sql = $con->getInsertSQL($tblName, $ControlledObjectRow, false);
            //Execute insert
            $rs=$con->execute($sql);
            if (!$rs) { db_error_handler($con, $sql); return false; }
            //Find and return newly added ID
            $controlled_objectID = $con->Insert_ID();
            
            return $controlled_objectID;
        }
    }

     /*****************************************************************************/
     /** function delete_controlled_object
       *
       * Deletes a controlled object from the list of controlled objects
       *
       * @param integer ControlledObject_id specifying which controlled object to delete
       * @return bool indicating success (true) or failure (false) of delete
       *
       */
    function delete_controlled_object($ControlledObject_id) {
        if (!$ControlledObject_id) { return false; }
        $tblName="ControlledObject";
        $con = $this->DBConnection;
        
        $sql = "Delete from $tblName WHERE ControlledObject_id=$ControlledObject_id";
        $rs = $con->execute($sql);
        if (!$rs) { db_error_handler($con, $sql); return false; }
        else return true;     
    
    }

    
     /*****************************************************************************/
     /** function get_controlled_object_relationship
       *
       * Searches for a particular controlled object relationship based on parameters
       *
       * @param integer ParentControlledObject_id to specify which object is the parent in the relationship
       * @param integer ChildControlledObject_id to specify which object is the child in the relationship
       * @param integer CORelationship_id with particular database identifier for the desired controlled object relationship
       * @param boolean $IncludeControlledObject indicating whether the Controlled Object tables and fields for child and parent should be included in the data returned.  Extra fields returned are parent_table, parent_field, and all the controlled object table fields for the child in the relationship.  Defaults to false, only return controlled object relationship fields
       * @return array with data about controlled object relationship or false if relationship is not found
       *
       */
    function get_controlled_object_relationship($ParentControlledObject_id=false, 
                                                $ChildControlledObject_id=false,
                                                $CORelationship_id=false,
                                                $IncludeControlledObject=false) 
        {
//        if (!$ParentControlledObject_id and !$ChildControlledObject_id and !$CORelationship_id) { return false; }
        $tblName = "ControlledObjectRelationship";
        
        $con = $this->DBConnection;
        $where=array();
        
        if ($CORelationship_id) { $where[]="CORelationship_id=$CORelationship_id"; }
        else {
            if ($ParentControlledObject_id) { $where[]="ParentControlledObject_id=$ParentControlledObject_id"; }
            if ($ChildControlledObject_id) { $where[]="ChildControlledObject_id=$ChildControlledObject_id"; }
        }
        $whereclause = implode (" and ", $where);
        //Search within group specified
        if ($IncludeControlledObject) {
            $sql = "SELECT $tblName.*, Parent.on_what_table as parent_table, Parent.on_what_field as parent_field, Child.* FROM $tblName
             LEFT OUTER JOIN ControlledObject As Parent ON Parent.ControlledObject_id=$tblName.ParentControlledObject_id
             INNER JOIN ControlledObject As Child ON Child.ControlledObject_id=$tblName.ChildControlledObject_id ";
        } else {
            $sql = "SELECT * FROM $tblName ";
        }
        if ($whereclause) $sql.= " WHERE $whereclause";
        
        $rs = $con->execute($sql);
        
        if (!$rs) { db_error_handler($con, $sql); return false; }
        if ($rs->numRows()>1) {
            while (!$rs->EOF) {
                $ret[$rs->fields['CORelationship_id']] = $rs->fields;
                $rs->movenext();
            }
            return $ret;
        } elseif ($rs->numRows()==1) {
            return $rs->fields;
        } else {
            return false;
        }        
    }

            
     /*****************************************************************************/
     /** function add_controlled_object_relationship
       *
       * Adds a new parent-child relationship between ControlledObjects
       *
       * @param integer parentControlledObject_id identifier for parent controlled object
       * @param integer childControlledObject_id identifier for parent controlled object
       * @return integer CORelationship_id with identifier for newly added ControlledObjectRelationship, or false if duplicate/failed
       *
       */
    function add_controlled_object_relationship($parentControlledObject_id, $childControlledObject_id, $on_what_child_field=false, $on_what_parent_field=false, $cross_table=false, $singular=false) {
    
        if (!$parentControlledObject_id AND !$childControlledObject_id) { return false; }
        
        $con=$this->DBConnection;
        $tblName = "ControlledObjectRelationship";
        
            //ensure no duplicates
            $existing=$this->get_controlled_object_relationship($parentControlledObject_id, $childControlledObject_id);
            if ( $existing!== false) { 
                if (is_array(current($existing))) { 
                    $existing=current($existing); 
                } 
                return $existing['CORelationship_id'];
            }
            //Create array to insert
            $ControlledObjectRow['ParentControlledObject_id']=$parentControlledObject_id;
            $ControlledObjectRow['ChildControlledObject_id']=$childControlledObject_id;
            if ($on_what_child_field) { $ControlledObjectRow['on_what_child_field']=$on_what_child_field; }
            if ($on_what_parent_field) { $ControlledObjectRow['on_what_parent_field']=$on_what_parent_field; }
            if ($cross_table) { $ControlledObjectRow['cross_table']=$cross_table; }
            if ($singular) { $ControlledObjectRow['singular']=$singular; }
            
            //Create insert statement
            $sql = $con->getInsertSQL($tblName, $ControlledObjectRow, false);
            //Execute insert
            $rs=$con->execute($sql);
            if (!$rs) { db_error_handler($con, $sql); return false; }
            //Find and return newly added ID
            $controlled_object_relationshipID = $con->Insert_ID();
            
            return $controlled_object_relationshipID;                    
    }

     /*****************************************************************************/
     /** function delete_controlled_object_relationship
       *
       * Deletes a parent-child relationship between controlled objects
       *
       * @param integer CORelationship_id specifying which controlled object relationship to delete
       * @return bool indicating success (true) or failure (false) of delete
       *
       */
    function delete_controlled_object_relationship($CORelationship_id) {
        if (!$CORelationship_id) { return false; }
        $tblName="ControlledObjectRelationship";
        $con = $this->DBConnection;
        
        $sql = "Delete from $tblName WHERE CORelationship_id=$CORelationship_id";
        $rs = $con->execute($sql);
        if (!$rs) { db_error_handler($con, $sql); return false; }
        else return true;         
    }    

    /*****************************************************************************/
    /** function get_field_list
     *
     * Returns an array of values from a field, based on restricting parameters
     *
     * Permission defaults to Read
     *
     * @param integer ControlledObject_id identifying the type ControlledObject for which to retrieve the a list of ids
     * @param integer User_id specifying which user to search permissions on
     * @param integer Permission_id identifying the permission to search on (defaults to Read)
     * @return array $restricted_list with key 'controlled_objects' array of ID's of the ControlledObject ID for which user has searched for permission, key 'groups' array of GroupIDs to which the user has access, key 'roles' used to find list
     *
     **/
     function get_field_list($ControlledObject_id, $fieldRestriction, $_on_what_field=false, $_on_what_table=false) {
        $list = $this->get_controlled_object_data($ControlledObject_id, false, $fieldRestriction, 'AND', false, false, $_on_what_table);
        if (!$list) { return false; }
//        echo "<pre>"; print_r($list); echo "</pre>";
        $on_what_field = $list['acl_on_what_field'];
        next($list);
        if (trim($_on_what_field)) $on_what_field=$_on_what_field;
        
        if (!is_array(current($list))) { $list = array( $list); }
        $ret=array();
        foreach ($list as $key=>$value) {
            if ($key!=='acl_on_what_field') {
                $ret[]=$value[$on_what_field];
            }
        }
        if (count($ret)>0) {
            return $ret;
        } else return false;
     }
     
    /*****************************************************************************/
    /** function check_field_exists
     *
     * Checks if a field for a controlled object exists, and returns false if not
     *
     * @param integer ControlledObject_id identifying the type ControlledObject for which to retrieve the a list of ids
     * @param string $field_name identifying which field to look for
     * @return boolean indicating if field exists in controlled object table
     *
     **/
    function check_field_exists($ControlledObject_id, $field_name) {
        $ret = $this->get_controlled_object_data($ControlledObject_id, false, array(), false, false, 1);
        if (!$ret) return false;
        next($ret);
        if (is_array(current($ret))) { $ret = current($ret); }
        return array_key_exists($field_name, $ret);
    }
     
    /*****************************************************************************/
    /** function get_restricted_object_list
     *
     * Returns a restricted list of objects of a certain type, by user and optional permissions
     * Permission defaults to Read
     *
     * @param integer ControlledObject_id identifying the type ControlledObject for which to retrieve the a list of ids
     * @param integer User_id specifying which user to search permissions on
     * @param integer Permission_id identifying the permission to search on (defaults to Read)
     * @param integer level of permission search (used to keep track of recursive calls to function)
     * @return array $restricted_list with key 'controlled_objects' array of ID's of the ControlledObject ID for which user has searched for permission, key 'groups' array of GroupIDs to which the user has access, key 'roles' used to find list
     *
     **/
    function get_restricted_object_list($ControlledObject_id, $User_id, $Permission_id=false, $level=0) {
        $level++;
        if ($level==1) {
            //if this is the first call to this function, allow non-inheritable permissions to apply
            $inheritable=false;
        //otherwise only allow inheritable permissions
        } else $inheritable=true;
        
//        echo "SEARCHING FOR OBJECTS OF TYPE $ControlledObject_id for $User_id to $Permission_id";
        $con=$this->DBConnection;
        //default to search for read permission
        if (!$Permission_id) { $Permission_id=1; }
        if (!is_numeric($Permission_id)) { 
            $Permission=$this->get_permission($Permission_id);
            $Permission_id=$Permission['Permission_id'];
        }
        //Get list of objects with this controlledobject_id
        //COMMENTED BECAUSE THIS IS TOO EXPENSIVE
//        $ControlledObjectCompleteList = $this->get_field_list($ControlledObject_id, array());
        
        //still have entire list to restrict
//        $objectsLeft = $ControlledObjectCompleteList;
        $objectsFound=array();
                
        //by default, search for user permissions
        $searchUser=true;        
        
        //Get list of roles and groups for the user        
        $UserGroups = $this->get_group_user(false, $User_id);
        if (!$UserGroups) { return false;}
        foreach ($UserGroups as $UserGroup) { $GroupList[] = $UserGroup['Group_id']; }
        $RoleList = $this->get_user_roles_by_array($GroupList, $User_id);
        $UserRoleList=$RoleList['Roles'];
        $GroupRoleList=$RoleList['GroupRoles'];
//        echo "<pre>"; print_r($RoleList); echo "</pre>";
        //Get list of controlled object relationships for which this is the child
        $ControlledObjectRelationships = $this->get_controlled_object_relationship(false, $ControlledObject_id, false, true);
        if (!$ControlledObjectRelationships) { echo "No controlled object relationships found, failing."; return false; }     
        if (!is_array(current($ControlledObjectRelationships))) {
            $ControlledObjectRelationships=array($ControlledObjectRelationships['CORelationship_id']=>$ControlledObjectRelationships); 
        }
        $ControlledObjectRelationship_ids=array_keys($ControlledObjectRelationships);

//        $ScopeList=$this->get_scope_list();
            
            //LOOP ON ALL ROLES HELD BY USER IN ALL GROUPS
//            foreach ($RoleList as $urKey=>$Role) {
//                foreach ($ControlledObjectRelationships as $ControlledObjectRelationship) {
//                     $CORelationship_id=$ControlledObjectRelationship["CORelationship_id"];
                    //Search for permission for all roles on controlled object relationships
                    $Scopes=array('World','User');
                    $RolePermissions = $this->get_role_permission($UserRoleList, $ControlledObjectRelationship_ids, $Scopes, $Permission_id, false, $inheritable);
//                    echo "$RolePermission = $this->get_role_permission($Role, $CORelationship_id, $Scope, $Permission_id)";
                    
                    if ($RolePermissions) {
                        //if (!is_array(current($RolePermissions))) { $RolePermissions=array($RolePermissions); }
                        foreach ($RolePermissions as $RolePermission) {
                            switch ($RolePermission['Scope']) {
                                //WORLD SCOPE
                                case 'World':
                                    //If found, return whole list, and groups/roles
                                    $ret=array();
    //                                $ret['controlled_objects']=$ControlledObjectCompleteList;
                                    $ret['ALL']=true;
                                    $ret['groups']=$GroupList;
                                    $ret['roles']=$RoleList;
                                    return $ret;
                                    break;
                                case 'User':
                                    //USER SCOPE
                                    //check to ensure that the user field exists in for the controlled object before applying user permissions
                                    if ($searchUser) {
                                        reset($ControlledObjectRelationships);
                                        $FirstRelationship=current($ControlledObjectRelationships);
                                        $user_field=$FirstRelationship['user_field'];
                                        if (!$user_field) $user_field='user_id';
                                        //if there is no user info on this level, simply do not search for it
                                        if (!$this->check_field_exists($ControlledObject_id, $user_field)) { $searchUser=false;  continue;}
                                                                                
                                        //Search for user permissions on this object type
                                        //If found, query all objects with this user_id, add to list
                                        $fieldRestrict=array();
                                        $fieldRestrict[$user_field]=$User_id;
                                        $UserList=$this->get_field_list($ControlledObject_id, $fieldRestrict);
                                        if ($UserList) {
                                            //Restrict master list of objects, if empty, return the whole thing
                                            //If not, next scope
                                            $objectsFound=array_merge($objectsFound, $UserList);
                                        }
                                    }
                                break;
                            }
                        }
                    }// end if role permission if
//                }// end role list foreach
 //           } // end COR foreach
//        }
        $Scope='Group';
        foreach ($GroupRoleList as $Group_id=>$RoleList) {
            foreach($RoleList as $Role) {
//                foreach ($ControlledObjectRelationships as $ControlledObjectRelationship) {
//                    $CORelationship_id=$ControlledObjectRelationship["CORelationship_id"];
                    //Search for permission for all roles on controlled object relationships
                    $RolePermission = $this->get_role_permission($Role, $ControlledObjectRelationship_ids, $Scope, $Permission_id, false, $inheritable);
//                    echo "<pre>                    $RolePermission = $this->get_role_permission($Role, $CORelationship_id, $Scope, $Permission_id)";
                    if ($RolePermission) {
//                        print_r($RolePermission);
                        //GROUP SCOPE
                        //Search for permissions for roles in groups
                        //If found, get objects for group in question
//                        echo "Found a permission!<br>";   
                        $SearchGroupList=$this->get_group_objects($Group_id, $ControlledObject_id);
//                        echo "<pre>"; print_r($SearchGroupList); echo "</pre>";
                        if ($SearchGroupList) {
                            $FoundGroupList=array();
                            foreach ($SearchGroupList as $GroupMember) {
                                $FoundGroupList[]=$GroupMember['on_what_id'];
                            }
                        } else { $FoundGroupList=false; }
                        //Restrict master list of objects, if empty, return the whole thing
                        //If not, next scope
                        if ($FoundGroupList) {
                            $objectsFound=array_merge($objectsFound, $FoundGroupList);
                        } 
                    }
//                } // end CORelationship loop
            }
        }
//        echo "AFTER USER AND WORLD AND GROUP, WE HAVE<pre>"; print_r($objectsFound); echo "\nok!</pre>";
//        echo "LOOPING PARENTS<br>";
        //Loop on relationships, find parent objects   
        foreach ($ControlledObjectRelationships as $Relationship) {
//            $fieldRestrict=array();
            $ParentObject = $Relationship['ParentControlledObject_id'];
            //check to see that we have a parent object
            if ($ParentObject) {
//                echo "<br>Recursing to parent $ParentObject<br>";
                //This recursively calls same function
                $recurse = $this->get_restricted_object_list($ParentObject, $User_id, $Permission_id, $level);
                
                if ($recurse) {
//                    echo "Results: <pre>"; print_r($recurse); echo "</pre>";
                    //if we found any objects, get IDs
                    if (array_key_exists('controlled_objects',$recurse)) $ParentIDs = $recurse['controlled_objects'];
		    else $ParentIDs=false;

                    //check if relationship is singular (is only a child on one of its possible relationships)
                    if ($Relationship['singular']==1) {
                        $on_what_child_field='on_what_id';
                        $on_what_table=false;
                        $find_field=false;
                        //set on_what_table in child table to value of parent
                        $fieldRestrict['on_what_table']=$con->qstr($Relationship['parent_table']);
                        if ($recurse['ALL']) {
                            //if we found all parent objects of this type, clear parent object restrict (allow all of them)
                            $ParentIDs=false;
                        }
                    } else {
                        //relationship is not singular, check if we found all
                        if ($recurse['ALL']) {
                            //if all parents are found, and relationship isn't singular, then return all children
                            $ret['ALL']=true;
                            $ret['groups']=$recurse['groups'];
                            $ret['roles']=$recurse['roles'];
                            return $ret;
                        }
                        //not all parents found, so look for children of these parents
                        if ($ParentObject AND ($ParentObject>0)) {
                            //get child field name from relationship, to use in restricting child table
                            if (!trim($Relationship['on_what_child_field'])) {
                                $on_what_child_field=$Relationship['parent_field'];
                            } else {
                                $on_what_child_field=$Relationship['on_what_child_field'];
                            }
                            if (trim($Relationship['cross_table'])) {
                                //use cross table to find relationships between parent and child
                                $on_what_table=$Relationship['cross_table'];
                                $find_field=$on_what_child_field;
                                $on_what_child_field=$Relationship['on_what_parent_field'];
                            } else {
                                $on_what_table=false;
                                $find_field=false;
                            }
                        }
                    }                    
                    //Use parent restricted list to find all controlled object IDs to which this object is a child
                    if ($ParentIDs) {
                        $fieldRestrict[$on_what_child_field]=$ParentIDs;
                    }
                    $ParentList=$this->get_field_list($ControlledObject_id, $fieldRestrict, $find_field, $on_what_table);
  //                  echo "<p>$ParentList=\$this->get_field_list($ControlledObject_id, $fieldRestrict, $find_field, $on_what_table)";
                    if ($ParentList) {
                        $objectsFound=array_merge($objectsFound, $ParentList);
                    } //end check of parent list
                } //end check for return from recursive call
            } //end check for parent object 
        } //end loop on parent controlled objects
//        print_r($objectsFound);
        if (count($objectsFound)>0) {
            $ret=array();
            $ret['controlled_objects']=$objectsFound;
            $ret['groups']=$GroupList;
            $ret['roles']=$UserRoleList;
            $ret['ALL']=false;
            return $ret;
        } else {
            return false;
        }    
    }
    
    /*****************************************************************************/
    /** function get_permission_user_object
     *
     * Returns the permissions on a class of controlled object for a user
     *
     * @param integer ControlledObject_id identifying which class of ControlledObject to check permissions on
     * @param integer User_id identifying which group to check permissions on
     * @param integer _CORelationship_id optional, identifying in what relationship of the controlled object to check permissions on
     * @param array PermissionList of Permission_id's to search for (or integer to only search for one)
     * @param integer level keeps track of recursive calls to function, defaults to 0
     * @return array of Permission_ids on a class of ControlledObject for a user
    */    
    function get_permission_user_object(
                                                    $ControlledObject_id, 
                                                    $User_id, 
                                                    $_CORelationship_id=false,
                                                    $PermissionList=false,
                                                    $level=0
                                                  )                                                  
    {
        $level++;
        if ($level==1) {
            //if this is the first level, allow non-inheritable permissions to be found
            $inheritable=false;
        //otherwise only allow inheritable permissions to befound
        } else $inheritable=true;
        
        if (!$User_id OR !$ControlledObject_id) return false;
        if (!$PermissionList) {
            $PermissionList=$this->get_permissions_list();
        }
//        if (!is_array($PermissionList)) { $PermissionList = array( $PermissionList ); }
        foreach($PermissionList as $pkey=>$Perm) {
            if (!is_numeric($Perm)) {
                $nPerm=$this->get_permission($Perm);
                if ($nPerm) {
                    $PermissionList[$pkey]=$nPerm['Permission_id'];
                }
            }
        }
        
        $UserRoleList = $this->get_user_roles_by_array(array(false), $User_id);
        if (!$UserRoleList) { return false; }
        $UserRoleList=$UserRoleList['Roles'];
        if (count($UserRoleList)==0) return false;
        
        if (!$_ScopeList) {
            $ScopeList = $this->get_scope_list();
        }
        //if (!is_array($ScopeList)) { $ScopeList=array($ScopeList); }
        $FoundPermissionList=array();    
        $ControlledObjectRelationships = $this->get_controlled_object_relationship(false, $ControlledObject_id, $_CORelationship_id, true);

        if (!$ControlledObjectRelationships) { $ControlledObjectRelationships=array(); }
        if (!is_array(current($ControlledObjectRelationships)) AND (count($ControlledObjectRelationships)>0)) {
            //if only one is found and it is not an array, put it in an array
            $ControlledObjectRelationships=array($ControlledObjectRelationships['CORelationship_id']=>$ControlledObjectRelationships);
        }
        $ControlledObjectRelationship_ids=array_keys($ControlledObjectRelationships);
//        echo "SEARCHING CONTROLLED OBJECTS: <pre>"; print_r($ControlledObjectRelationships);
            //foreach ($UserRoleList as $Role) {
                $RolePermission = $this->get_role_permission($UserRoleList, $ControlledObjectRelationship_ids, false,false,false,$inheritable);
                if ($RolePermission) {
                    //if (!is_array(current($RolePermission))) $RolePermission=array($RolePermission);
                    foreach ($RolePermission as $IndividualRolePerm) {
                            //merge permissions with each individual permission
                        $FoundPermissionList = array_unique(array_merge($FoundPermissionList, array($IndividualRolePerm['Permission_id'])));
                        //find what permissions we have left to search for
                        $leftPermissions = array_diff($PermissionList,$FoundPermissionList);
                        if (count($leftPermissions)==0) {
                            //if we found all the permissions we are searching for, return
                            return $FoundPermissionList;
                        }
                    }
                }
         //} // end role foreach
        foreach ($ControlledObjectRelationships as $CORelationship_id=>$ControlledObjectRelationship_data) {
            if ($ControlledObjectRelationship_data['ParentControlledObject_id'] AND $ControlledObjectRelationship_data['ParentControlledObject_id']>0) {
//                echo "\nLOOPING TO PARENT\n";
                $parentPermissionList=$this->get_permission_user_object($ControlledObjectRelationship_data['ParentControlledObject_id'], $User_id, false, $leftPermissions, $level);
                if ($parentPermissionList) {
                    $FoundPermissionList = array_unique(array_merge($FoundPermissionList, $parentPermissionList));
                    $leftPermissions = array_diff($PermissionList,$FoundPermissionList);
                    if (count($leftPermissions)==0) {
                        //if we found all the permissions we are searching for, return
                        return $FoundPermissionList;
                    }            
                }
            }
        }
        if (count($FoundPermissionList)>0)
            return $FoundPermissionList;
       else return false;
    }
    
    /*****************************************************************************/
    /** function get_permissions_user
     *
     *  Core permission search function.  
     *  Searches across all intersections to add up available permissions
     *
     *  Returns the permissions on a controlled object for a particular user
     *  optionally restricting what relatiionship the controlled object is in, 
     *  and permissions to search for
     * 
     *
     * @param integer ControlledObject_id identifying which ControlledObject to check permissions on
     * @param integer on_what_id with the identifier for the controlled object in the table
     * @param integer User_id identifying which group to check permissions on
     * @param integer CORelationship_id optional, identifying in what relationship of the controlled object to check permissions on
     * @param array PermissionList of Permission_id's to search for (or integer to only search for one)
     * @param array _ControlledObjectRelationships providing list of already found controlled object relationships to search for permissions on
     * @param array _ScopeList providing a scope list to search for instead of using all scopes
     * @param integer level keeping track of the recursive calls to function (defaults to 0)
     * @return array of Permission_ids on a particular ControlledObject for a user
     *
     **/
    function get_permissions_user(
                                                    $ControlledObject_id, 
                                                    $on_what_id, 
                                                    $User_id, 
                                                    $_CORelationship_id=false,
                                                    $PermissionList=false,
                                                    $_ControlledObjectRelationships=false,
                                                    $_ScopeList=false,
                                                    $level=0                                                    
                                                  )
    {        
        $level++;
        if ($level==1) {
            //if this is the first level, allow non-inheritable permissions to be found
            $inheritable=false;
        //otherwise only allow inheritable permissions to befound
        } else $inheritable=true;
        
        if (!$PermissionList) { $PermissionList = $this->get_permissions_list(); }
        if (!is_array($PermissionList)) { $PermissionList = array( $PermissionList ); }
        foreach($PermissionList as $pkey=>$Perm) {
            if (!is_numeric($Perm)) {
                $nPerm=$this->get_permission($Perm);
                if ($nPerm) {
                    $PermissionList[$pkey]=$nPerm['Permission_id'];
                }
            }
        }
        //Get the possible scopes for permission
        if (!$_ScopeList) {
            $ScopeList = $this->get_scope_list();
        } else { $ScopeList=$_ScopeList; }
        if (!is_array($ScopeList)) { $ScopeList=array($ScopeList); }

                
        //start with no permissions
        $FoundPermissionList = array();
        //by default, search this level of controlled object relationships
        $SearchLevel=true;
        //by default, search for group permissions
        $SearchGroups=true;
        //by default, attempt to search for user permissions
        $SearchUser=true;
                      
        //Get all roles for user
        $UserRoleList = $this->get_user_roles_by_array(array(false), $User_id);
        if (!$UserRoleList) { $SearchLevel=false; }
        $UserRoleList=$UserRoleList['Roles'];
            
        //get the list of possible parents to this controlled object
        $ControlledObjectRelationships = $this->get_controlled_object_relationship(false, $ControlledObject_id, $_CORelationship_id, true);
        
        //if none are found, make a blank array
        if (!$ControlledObjectRelationships) { $ControlledObjectRelationships=array(); }
        if (!is_array(current($ControlledObjectRelationships)) AND (count($ControlledObjectRelationships)>0)) {
            //if only one is found and it is not an array, put it in an array
            $ControlledObjectRelationships=array($ControlledObjectRelationships['CORelationship_id']=>$ControlledObjectRelationships);
        }
        if ($_ControlledObjectRelationships) {
            //if any relationships are passed in, use them or add them in to the list to search
            if (count($ControlledObjectRelationships)>0) {
                foreach ($_ControlledObjectRelationships as $key => $value) {
                    //place in array while preserving integer key (array_merge will rekey starting from 0)
                    $ControlledObjectRelationships[$key]=$value;
                }
            } else { 
                //No controlled object relationships were found in search, so search only passed in list
                $ControlledObjectRelationships=$_ControlledObjectRelationships; 
            }
        }
        //If no controlled object relationhips were found at all, do not search this level, move to parents
        if (!$ControlledObjectRelationships OR !is_array($ControlledObjectRelationships)) { $SearchLevel=false; }
        
        
        
        //echo "Searching controlled object relationships:<pre>\n"; print_r($ControlledObjectRelationships); echo "</pre>";        
        //If no information was found for this level, move on
        if ($SearchLevel) {
            $ControlledObjectRelationship_ids=array_keys($ControlledObjectRelationships);
            $Scopes=array('World');
            if ($SearchUser) $Scopes[]='User';
            //$ApplyUserPerms=$SearchUser;
            $RolePermission = $this->get_role_permission($UserRoleList, $ControlledObjectRelationship_ids, $Scopes, false, false, $inheritable);
            if ($RolePermission) {
                //if (!is_array(current($RolePermission))) { $RolePermission = array ( $RolePermission); }
                //add up all permissions found
                /* @todo Add checks to see if permissions propagate up/down */
                foreach ($RolePermission as $IndividualRolePerm) {
                    //merge permissions with each individual permission
                    $newPerm_id=$IndividualRolePerm['Permission_id'];
                    switch ($IndividualRolePerm['Scope']) {
                        case 'User':
                            //we found user permissions, so check if we have user permissions enabled for this object
                            if (!$SearchUser) { $newPerm_id=false; }                           
                            else { 
                                //check to see if we have already checked the object for user searchability
                                //if (!$ApplyUserPerms) {
                                    // if no particular object is given, cannot search user record
                                    if (!$on_what_id) { $SearchUser=false; $newPerm_id=false;}//echo "No id provided, cannot check permissions"; return false; }
                                    else {
                                        reset($ControlledObjectRelationships);
                                        $FirstRelationship=current($ControlledObjectRelationships);

                                        $user_field=$FirstRelationship['user_field'];
                                        if (!trim($user_field)) $user_field='user_id';
                                        $objectData=$this->get_controlled_object_data($ControlledObject_id, $on_what_id);
                                        if ($objectData[$user_field]!=$User_id) {
                                            $SearchUser=false;
                                            $newPerm_id=false;                               
                                        } else { $ApplyUserPerms=true; }
                                    }
                                //}
                            }
                        break;
                    }
                    if ($newPerm_id) {
                        $FoundPermissionList[]=$newPerm_id;
                        $leftPermissions = array_diff($PermissionList,$FoundPermissionList);
                        if (count($leftPermissions)==0) {
                            //if we found all the permissions we are searching for, return
                            return $FoundPermissionList;
                        }                     
                    }
                }
            } //end if role permission 

            if (!$on_what_id) { $SearchGroups=false; }//echo "No id provided, cannot check permissions"; return false; }
            if ($SearchGroups) {
                $GroupList = $this->get_object_groups($ControlledObject_id,$on_what_id, $_CORelationship_id);
                if (!$GroupList) { $SearchGroups=false; }
                else {
                    //Get the roles this user has within the groups found
                    $GroupRoleList = $this->get_user_roles_by_array ($GroupList,  $User_id);
                    if (!$GroupRoleList) { $SearchGroups=false; }
                    else {
                        $GroupRoleList=$GroupRoleList['Roles'];
                    }
                }
                if ($SearchGroups) {
                    $Scopes=array('Group');
                    $RolePermission = $this->get_role_permission($GroupRoleList, $ControlledObjectRelationship_ids, $Scopes, false, false, $inheritable);
                    if ($RolePermission) {
                        //if (!is_array(current($RolePermission))) { $RolePermission = array ( $RolePermission); }
                        //add up all permissions found
                        /* @todo Add checks to see if permissions propagate up/down */
                        foreach ($RolePermission as $IndividualRolePerm) {
                            //merge permissions with each individual permission
                            $FoundPermissionList = array_unique(array_merge($FoundPermissionList, array($IndividualRolePerm['Permission_id'])));
                            //find what permissions we have left to search for
                            $leftPermissions = array_diff($PermissionList,$FoundPermissionList);
                            if (count($leftPermissions)==0) {
                                //if we found all the permissions we are searching for, return
                                return $FoundPermissionList;
                            }
                        }
                    } //end if role permission
                }                         
            } //end search groups check
        } // end search level check
        
        //Get parent objects for this particular controlled object
        $objectParents = $this->get_object_relationship_parent($ControlledObject_id, $on_what_id, $_CORelationship_id, $objectData);
        //If there are any parents, loops through them and get permissions on them
        if ($objectParents AND is_array($objectParents)) {
            foreach ($objectParents as $CORelationship_id=>$aparent) {
                //Recurse into parent objects to get permissions, only searching for permissions we haven't found yet
                if (!is_array(current($aparent))) $aparent=array($aparent);
                foreach ($aparent as $parent) {
                    $ret = $this->get_permissions_user($parent['ControlledObject_id'],$parent['on_what_id'],$User_id, false, $leftPermissions, $ControlledObjectRelationships, false, $level);
                    if ($ret) {
                        //if any permissions are found, add them to the found list, and reset the permissions left
                        $FoundPermissionList = array_unique(array_merge($FoundPermissionList,$ret));
                        $leftPermissions = array_diff($PermissionList,$FoundPermissionList);
                        if (count($leftPermissions)==0) {
                            //if we have found all the permissions we are searching for, return
                            return $FoundPermissionList;
                        }
                    }
                }
            }
        }
        //Didn't find all the permissions we are looking for, return all permissions found, if any
        if (count($FoundPermissionList)>0) {
            return $FoundPermissionList;
        } else {
            //no permissions found, return false
            return false;
        }
    }
    
    /*****************************************************************************/
    /**
     *
     * Returns the possible permissions
     *
     * @return array of possible permissions
     *
     **/
    function get_permissions_list() {
        if ($this->PermissionList) return $this->PermissionList;

        $sql = "SELECT Permission_id FROM Permission";
        $rst = $this->DBConnection->execute($sql);
        if (!$rst) { db_error_handler($this->DBConnection, $sql); return false; }
        $perms=array();
        while (!$rst->EOF) {
            $perms[]=$rst->fields['Permission_id'];
            $rst->movenext();
        }
        $this->PermissionList=$perms;
        return $perms;

//        return array (1,2,3,4,5);
    }
    
    /*****************************************************************************/
    /** function get_group
      *
      * Returns the id for the group based on the searched-for string
      *
      * @param string Group_name with text to search for within the names of groups
      * @return integer Group_id identifying the group in the database or false if not found
      * 
      */
    function get_permission ($Permission_name=false, $Permission_id=false, $fuzzy=true) {     
        $tblName = "Permission";
        $con = $this->DBConnection;
        
        //Search for group name exactly
        if ($Permission_name) { $where="Permission_name=" . $con->qstr($Permission_name, get_magic_quotes_gpc()); }
        elseif ($Permission_id) { $where="Permission_id=$Permission_id"; }
        $sql = "SELECT * from $tblName WHERE $where";
        $rs = $con->execute($sql);
        if (!$rs) { db_error_handler($con, $sql); return false; }
        if ($rs->numRows()>=1) {
            return $rs->fields;
        } elseif ($Permission_name and $fuzzy) {
            $sql = "SELECT * from $tblName WHERE Permission_name LIKE " . $con->qstr("%$Permission_name%",get_magic_quotes_gpc());
            $rs = $con->execute($sql);
            if ($rs->numRows()>=1) {
                if (!$rs) { db_error_handler($con, $sql); return false; }
                return $rs->fields;
            }
        }
        return false;
     }

    /*****************************************************************************/
    /** function get_role_list
     *
     * Returns the possible roles
     *
     * @param bool $return_array specifying to return results as an array (otherwise returns recordset)
     * @return array of roles/recordset of roles
     *
     **/
    function get_role_list($return_array=true) {
        $con = $this->DBConnection;
        $sql = "SELECT Role_name, Role_id FROM Role";
        $rst = $con->execute($sql);
        if (!$return_array) return $rst;
        else {
            $roleList=array();
            while (!$rst->EOF) {
                $roleList[$rst->fields['Role_id']]=$rst->fields;
                $rst->movenext();
            }
        }
        if (count($roleList)>0)
            return $roleList;
        else return false;
    }
    
    /*****************************************************************************/
    /** function get_scope_list
     *
     * Returns the possible scopes
     *
     * @param void
     * @return array of scopes
     *
     **/
    function get_scope_list() {
        return array ( 'World', 'Group', 'User');
    }
}  

/*
 * $Log: xrms_acl.php,v $
 * Revision 1.35  2006/07/09 05:40:34  vanmer
 * - fixed mixed caps on Inheritable_flag=0 clause
 * - added clause to set inheritable flag to 0 when not set to true on a RolePermission
 *
 * Revision 1.34  2006/06/07 22:05:05  vanmer
 * - disabled path for child objects in an object relationship do not inherit group of parent object
 *
 * Revision 1.33  2006/01/27 13:37:05  vanmer
 * - changed ACL to require array input for actions (permissions)
 * - removed check for array of permissions, now is always an array
 * - changed test and wrapper to properly pass permissions as an array to ACL object
 *
 * Revision 1.32  2005/12/19 22:19:55  vanmer
 * - removed check for applyUserPerms, unneeded and allowed more permissions than expected
 *
 * Revision 1.31  2005/12/02 00:22:58  vanmer
 * - better PHPDoc for ACL
 *
 * Revision 1.30  2005/09/21 21:11:41  vanmer
 * - removed blank stub functions (unneeded)
 * - ensured that all functions have php docblocks, and they are rational
 * - changed permission list to actually return correct permission ids
 *
 * Revision 1.29  2005/08/20 00:45:54  vanmer
 * - changed to call internal function, instead of incorrectly calling global function
 *
 * Revision 1.28  2005/08/19 03:18:03  vanmer
 * - changed to not assume array key exists for parent IDs
 *
 * Revision 1.27  2005/08/12 01:18:05  vanmer
 * - added case to ensure that id is not overwritten if not set
 *
 * Revision 1.26  2005/08/02 00:43:57  vanmer
 * - changed group member criteria retrieval to use simpler logic for creating array
 * - changed to also check get_magic_quotes_gpc to allow quoted values to be inserted properly
 *
 * Revision 1.25  2005/07/30 01:30:35  vanmer
 * - added function to get list of groups from GroupMemberCriteria data
 * - added group list addition to retrieval of groups list used in permissions
 *
 * Revision 1.24  2005/07/30 00:52:38  vanmer
 * - part of initial implementation of ACL's Group Member by Criteria functionality
 * - added API to add/get/delete group member criteria
 * - added table and result field to Group Member API
 * - added addition of objects found through criteria search to get_group_objects call
 *
 * Revision 1.23  2005/07/22 23:35:33  vanmer
 * - added functionality to use callback functions provided by parent application to access database connections
 * - added functionality to add/remove callbacks
 * - added functionality to set the context that the ACL is being called in, for controlled objects without a
 * datasource specified.
 *
 * Revision 1.22  2005/07/07 19:38:16  vanmer
 * - added method for querying for all users with a specified role
 *
 * Revision 1.21  2005/06/24 23:49:38  vanmer
 * - changed add_controlled_object and relationships to return existing ID if found, instead of returning false
 * - added export permission support
 *
 * Revision 1.20  2005/06/15 16:57:16  vanmer
 * - updated JOIN to use INNER JOIN to be more compatible with older mysql
 *
 * Revision 1.19  2005/06/13 22:10:24  vanmer
 * - removed unneeded is_array check for get_roles function calls, now assumes array is passed in
 * - added explict array for callers to the get_roles_by_array
 *
 * Revision 1.18  2005/06/07 21:33:12  vanmer
 * - added parameter to include Group name when returning GroupUser record
 *
 * Revision 1.17  2005/05/13 21:21:12  vanmer
 * - altered to make use of Inheritable flag to mark permissions assigned as inheritable
 *
 * Revision 1.16  2005/05/02 15:59:28  vanmer
 * - added explicit INNER to JOIN statements, to support older mysql installs
 *
 * Revision 1.15  2005/04/07 18:13:15  vanmer
 * - allow ACL to be called with initial DB connection already instantiated
 *
 * Revision 1.14  2005/03/24 20:27:06  ycreddy
 * Assignment of enum type made conditional to xrms_db_dbtype
 *
 * Revision 1.13  2005/03/21 20:46:11  vanmer
 * - added checks to ensure that a zero value will still create select criteria
 *
 * Revision 1.12  2005/03/15 23:13:49  vanmer
 * - changed logic of restricted object search to better reflect new walk of parent object tree
 * - removed no longer needed codeblocks
 *
 * Revision 1.11  2005/03/14 23:06:51  vanmer
 * - re-added qstr that was removed and broke the insert statement on mysql
 *
 * Revision 1.10  2005/03/04 23:39:47  vanmer
 * - added trim to checks on data coming from the database, to deal with MSSQL returning a space instead of NULL for empty
 * fields
 *
 * Revision 1.9  2005/03/02 20:38:39  vanmer
 * - fixed singular relationship checking to reflect new restrict object list
 *
 * Revision 1.8  2005/03/01 21:50:23  ycreddy
 * Removing the extra qstr call when setting the Scope in add_role_permission
 *
 * Revision 1.7  2005/02/23 20:54:19  vanmer
 * - altered logic of permission checks to use SQL to loop instead of looping and then using SQL
 * - extended get_role_permission to allow arrays to be passed instead of single parameters
 *
 * Revision 1.6  2005/02/16 17:55:51  vanmer
 * - removed non-standard LIMIT statement, switched to use adodb SelectLimit statement
 *
 * Revision 1.5  2005/02/15 19:35:51  vanmer
 * - changed to reflect shorter fieldnames for controlled object relationships
 * - removed change made to get_controlled_object_data which broke the ACL
 *
 * Revision 1.4  2005/02/14 21:28:36  vanmer
 * - adjusted to no longer select all entities when checking permissions
 *
 * Revision 1.3  2005/01/27 00:12:53  neildogg
 * - Restricted query to applicable field
 *
 * Revision 1.2  2005/01/25 05:26:00  vanmer
 * - added functions for manipulating data sources in the ACL
 * - added parameters for newly added fields in ACL
 *
 * Revision 1.1  2005/01/13 17:07:16  vanmer
 * - Initial Revision of the ACL Install, Wrapper, Class and configuration files
 *
 * Revision 1.32  2005/01/10 15:59:42  ke
 * - completed stub for function get_role_list, listing roles available to a user
 *
 * Revision 1.31  2005/01/04 17:24:15  ke
 * - fixed singular relationship field restrictions
 * - added check for parent before recursing
 * - added AND seperator between where clauses for field restrictions
 *
 * Revision 1.30  2005/01/03 18:31:08  ke
 * - New function for checking permission on an object class instead of a particular object
 *
 * Revision 1.29  2004/12/30 22:35:33  ke
 * - removed the neccessity to always have an ID to search for in order to find permissions on an object
 *
 * Revision 1.28  2004/12/27 23:42:21  ke
 * - allow get_controlled_object_relationship to be called with no parameters in order to show all controlled object relationships
 * - fixed comment
 *
 * Revision 1.27  2004/12/20 17:03:38  ke
 * - added ability to call child group with NULL, to find only user records
 *
 * Revision 1.26  2004/12/16 02:17:28  ke
 * - added checks on parameters for group objects list function
 *
 * Revision 1.25  2004/12/15 18:30:18  ke
 * Removed debug output
 *
 * Revision 1.24  2004/12/14 22:51:48  ke
 * - added handling for arbitrary database defined field for user control of an object
 *
 * Revision 1.23  2004/12/14 18:08:11  ke
 * - added handling for parent objects when finding group objects
 * - added proper handling of permissions for cross tables
 *
 * Revision 1.22  2004/12/13 16:34:05  ke
 * - added handling for cross tables for object relationships
 * - added handling for singular relationships (on_what_table,on_what_id inside fields)
 * - added returns when all items are found
 *
 * Revision 1.21  2004/12/03 23:54:53  ke
 * - fixed user field restriction code, check if user field exists before querying
 *
 * Revision 1.20  2004/12/03 23:00:04  ke
 * - fixes recursive parent ownership problems
 *
 * Revision 1.19  2004/12/03 21:32:04  ke
 * - added error check for no id passed to permission check
 *
 * Revision 1.18  2004/12/03 21:05:53  ke
 * - fixed problem with permission set at top of restricted object search
 * - fixed breaking loops on group/user skip
 *
 * Revision 1.17  2004/12/03 20:24:07  ke
 * - added function to instantiate an adodb connection for a particular data source
 * - added option to return sql and connection for an object instead of data for get_controlled_object_data
 *
 * Revision 1.16  2004/12/02 08:19:51  ke
 * - added proper handling of group to restricted object list search
 * - added proper handing of recursion to higher levels in restricted object list search
 * - now correctly finds objects with User, Group and World scopes
 * Bug 64
 *
 * Revision 1.15  2004/12/02 07:01:13  ke
 * - changed search of external data to not always restrict (can have select * from table)
 * - changed to fully support simple restricted list
 *
 * Revision 1.14  2004/12/02 00:39:14  ke
 * - added ability to query parent and child controlled object data when returning a controlled object relationship
 * - First revision of restricted object list with new logic committed (untested, committing to save)
 *
 * Revision 1.13  2004/11/25 00:28:53  ke
 * - added code to find a list of object_id's based on search criteria
 *
 * Revision 1.12  2004/11/24 23:26:06  ke
 * - added notes on code needed to run restricted object lists
 * - added flexibility in underlying functions to allow search for roles in all groups
 * - added scope handling to permission check function
 *
 * Revision 1.11  2004/11/15 18:46:31  ke
 * - committing un-complete restricted object list
 *
 * Revision 1.10  2004/11/10 09:09:40  ke
 * - added needed group and role list as parameters to restricted object list function
 * - added comments laying out basic operation of restricted object list function
 * Bug 64
 *
 * Revision 1.9  2004/11/10 06:28:52  ke
 * - added stub for controlled object list function
 *
 * Revision 1.8  2004/11/09 04:13:18  ke
 * - Added recursive parent group function
 * - Use parent group function to find parent groups for objects
 * Bug 64
 *
 * Revision 1.7  2004/11/09 01:54:22  ke
 * - updated permissions calculation to successfully walk a tree for permissions
 *
 * Revision 1.6  2004/11/08 21:46:24  ke
 * - First revision of a recursive get permissions search
 * - Changed object group list to not be recursive
 * - Added recursive function to get groups by walking up the tree
 * Bug 64
 *
 * Revision 1.5  2004/11/05 01:14:00  ke
 * - added functions to walk the ControlledObject relationship tree and find group ownership on parent objects
 * - added ability to set different data sources based on data_source table, data_source name
 * Bug 64
 *
 *
 */
?>
