<?php
/**
 * ACL system for XRMS
 *
 * Copyright (c) 2004 Explorer Fund Advisors, LLC
 * All Rights Reserved.
 *
 * @todo
 * @package ACL
 * $Id: xrms_acl.php,v 1.2 2005/01/25 05:26:00 vanmer Exp $
 */

/*****************************************************************************/
/** Class xrms_acl
   *
   * Main API for accessing and editing ACLs for XRMS
   *
   **/
   
class xrms_acl {
    //Connection object for use throughout the entire ACL object
    var $DBConnection=false;
    //Options for connection, used to instantiate the DB connection
    var $DBOptions=array();

    /*****************************************************************************/
    /** function xrms_acl
     *
     * Constructor for the xrms_acl
     *
     * @param array with options for DB connection
     * @return Object of type xrms_acl
     *
     **/
    function xrms_acl($DBOptionSet=false) {
        if ($DBOptionSet) {
            $this->DBOptions=$DBOptionSet;
        }
        $this->DBConnection = $this->get_object_adodbconnection();
        
    }
    
    /*****************************************************************************/
    /** function get_group_data
     *
     * Returns information about a group, including possible children, parents and accessible objects
     *
     * @param integer Group_id identifying group for which to retrieve data
     * @return Array containing elements describing details of a group
     *
     **/
    function get_group_data($Group_id) {
    
    }

    /*****************************************************************************/
    /** function get_group_children
     *
     * Returns a list of children that are contained in a group
     *
     * @param integer Group_id identifying group for which to retrieve a list of children
     * @return Array containing a list of groups which are the children to the referenced group
     *
     **/
    function get_group_children($Group_id) {
        
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
        $rs = $con->execute($sql);
        
        if (!$rs) { db_error_handler($con, $sql); return false; }
        if ($rs->numRows()>0) {
            while (!$rs->EOF) {
                $objectList[$rs->fields['GroupMember_id']]=$rs->fields;
                $rs->movenext();
            }
        }
        //now search in parent groups
        if ($Group_id) {
            $groupList = $this->get_group_user($Group_id);
            if ($groupList and is_array($groupList)) {
                foreach ($groupList as $groupInfo) {
                    if ($groupInfo['ChildGroup_id']) {
                        $result = $this->get_group_objects($groupInfo['ChildGroup_id'], $ControlledObject_id);
                        if ($result) { $objectList = array_merge($objectList, $result); }
                    }
                }
            }
        }
        if ($ControlledObject_id) {
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
                                if (!$Relationship['on_what_child_field']) {
                                    $on_what_child_field=$Relationship['parent_field'];
                                } else {
                                    $on_what_child_field=$Relationship['on_what_child_field'];
                                }
                                if ($Relationship['cross_table']) {
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
    //                        echo "$ParentList=get_field_list($ControlledObject_id, $fieldRestriction, $find_field, $on_what_table)";
    //                        echo "<pre>"; print_r($ParentList);
                            if ($ParentList) {
                                foreach ($ParentList as $result) {
                                    $ret = array(array("ControlledObject_id"=>$ControlledObject_id, "on_what_id"=>$result));
    //                                print_r($ret); print_r($objectList);
                                    $objectList = array_merge($objectList, $ret);
    //                                print_r($objectList);
                                }
                            }
    //                        echo "</pre>";
    
                        }
                    }
                }
            }
        }
        if (count($objectList)>0) {
            return $objectList;
        }
        return false;
    }
    
    /*****************************************************************************/
    /** function get_object_relationship_parent
     *
     * Returns array of ControlledObject_id and on_what_id for each parent controlled object, based on a ControlledObject or ControlledObjectRelationship
     *
     * @param integer ControlledObject_id for which to find parents
     * @param integer on_what_id to specify which particular ControlledObject to check
     * @param integer ControlledObjectRelationship_id optional parameter to specify in which ControlledObjectRelationship the referenced ControlledObject is the child
     * @return Array containing an array indexed by ControlledObjectRelation_id, specifying the parent ControlledObject_id and on_what_id
     *
     **/
    function get_object_relationship_parent($ControlledObject_id, $on_what_id, $ControlledObjectRelationship_id=false, $objectData=false) {
        $con = $this->DBConnection;
        $table = "ControlledObjectRelationship";
        $objectList=array();
        
        $sql = "SELECT ControlledObjectRelationship_id, ParentControlledObject_id, on_what_field, on_what_table, on_what_child_field, on_what_parent_field, cross_table, singular FROM $table JOIN ControlledObject ON ControlledObject_id=ParentControlledObject_id WHERE ChildControlledObject_id=$ControlledObject_id";
        if ($ControlledObjectRelationship_id) { $sql .=" AND ControlledObjectRelationship_id=$ControlledObjectRelationship_id"; }
        $rs = $con->execute($sql);
        if (!$rs) { db_error_handler($con, $sql); return false; }
        if ($rs->numRows()>0) {
            while (!$rs->EOF) {
                if (!$rs->fields['on_what_child_field']) {
                    $on_what_child_field=$rs->fields['on_what_field'];
                } else { $on_what_child_field = $rs->fields['on_what_child_field']; }
                if ($rs->fields['cross_table']) {
                    $on_what_parent_field=$rs->fields['on_what_parent_field'];
                    $objectData=$this->get_controlled_object_data($ControlledObject_id, $on_what_id, false, false, false, false, $rs->fields['cross_table'] );                    
                    if (!$objectData) { $rs->movenext(); continue; }
                    if (!is_array(current($objectData))) $objectData=array($objectData);
                    $ret=array();
                    foreach ($objectData as $object) {
                        if ($object[$on_what_parent_field]) {
                            $ret[]=array('ControlledObject_id'=>$rs->fields['ParentControlledObject_id'],'on_what_id'=>$object[$on_what_parent_field]);
                        }
                    }
                    if (count($ret)>0) {
                        $objectList[$rs->fields['ControlledObjectRelationship_id']] = $ret;
                    }                    
                } else {
                    if (!$objectData) { $objectData = $this->get_controlled_object_data($ControlledObject_id, $on_what_id); }
                    if ($objectData) {
                        if ($rs->fields['singular']==1) {
                            $on_what_table=$objectData['on_what_table'];
                            if ($rs->fields['on_what_table']==$objectData['on_what_table']) {
                                $objectList[$rs->fields['ControlledObjectRelationship_id']] = array('ControlledObject_id'=>$rs->fields['ParentControlledObject_id'],'on_what_id'=>$objectData['on_what_id']);
                            }
                        }
                        else {
                            $objectList[$rs->fields['ControlledObjectRelationship_id']] = array('ControlledObject_id'=>$rs->fields['ParentControlledObject_id'],'on_what_id'=>$objectData[$on_what_child_field]);
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
       $xcon = &adonewconnection($this->DBOptions[$data_source_name]['db_dbtype']);
       $xcon->nconnect($this->DBOptions[$data_source_name]['db_server'], $this->DBOptions[$data_source_name]['db_username'], $this->DBOptions[$data_source_name]['db_password'], $this->DBOptions[$data_source_name]['db_dbname']);
       return $xcon;
    }
    /*****************************************************************************/
    /** function get_controlled_object_data
     *
     * Returns array of fields contained within a particular controlled object
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
            if (!$_on_what_table) {
                $on_what_table = $rs->fields['on_what_table'];
            } else { $on_what_table=$_on_what_table; }
            $on_what_field = $rs->fields['on_what_field'];
            $data_source_name=$rs->fields['data_source_name'];
            
            $xcon=$this->get_object_adodbconnection($data_source_name);
            
            $ret=array();
            $ret['acl_on_what_field']=$on_what_field;
            if ($on_what_id) {
                $restrictionFields[$on_what_field]=$on_what_id;
            }

            $where=array();
            if ($restrictionFields AND count($restrictionFields)>0) {
                foreach ($restrictionFields as $key=>$value) {
                    if (is_array($value)) {
                        //array of values, seach for all of them
                        $where[]="($key IN (" . implode(",",$value)."))";
                    } else {
                        $where[]="($key=$value)";
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
            if ($limit) { $sql .=" LIMIT $limit"; }
//            echo "<p>$sql<p>";
                                    
            $nrs = $xcon->execute($sql);
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
    /** function get_object_groups_recursive
     *
     * Returns a list of groups that contain a specific Controlled Object and value, recursively up the object tree
     *
     * @param integer ControlledObject_id identifying which object to retrieve a list of groups
     * @param integer on_what_id identifying which particular object ID to search for
     * @param integer $_ControlledObjectRelationship_id optionally specify which branch of the tree to search up
     * @return Array containing a collection of groups
     *
     **/
    function get_object_groups_recursive($ControlledObject_id,$on_what_id,$_ControlledObjectRelationship_id=false) {
        //get the group list for this level
        $groupList = $this->get_object_groups($ControlledObject_id, $on_what_id);
        if (!$groupList) { $groupList = array(); }
        
        //get the list of parent objects
        $objectParents = $this->get_object_relationship_parent($ControlledObject_id, $on_what_id, $_ControlledObjectRelationship_id);        
        if ($objectParents AND is_array($objectParents)) {
            foreach ($objectParents as $ControlledObjectRelationship_id => $aparent) {
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
    function get_group_user ($Group_id=false,  $User_id=false, $Role_id=false, $ChildGroup_id=false, $GroupUser_id=false) {     
        if (!$Group_id AND (!$ChildGroup_id AND (!$User_id AND !$Role_id)) AND !$GroupUser_id) { 
            echo "Cannot get group user: bad input parameters<br>Group $Group_id Child $ChildGroup_id User $User_id Role $Role_id"; return false; }
        $tblName = "GroupUser";
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
        $sql = "SELECT * FROM $tblName WHERE $wherestr";
        
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
        if (!is_array($Groups)) {
            $Groups = array( $Groups );
        }
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
     /** function get_group_member_id
       *
       * Searches for a particular group_member_id or a set of group_member_id's based on parameters
       *
       * @param integer Group_id to specify which group to search within
       * @param integer ControlledObject_id identifying what class of ControlledObject is being searched for
       * @param integer on_what_id identifying what value identifies the unique ControlledObject (if left false returns array of member_ids
       * @return integer GroupMember_id with new identifier for new entry in the group or false if duplicate/failed (or array if no on_what_id is specified)
       *
       */
    function get_group_member($Group_id=false, $ControlledObject_id=false, $on_what_id=false,$GroupMember_id=false) {
        if (!$Group_id or !$ControlledObject_id) { return false; }
        $tblName = "GroupMember";
        $con = $this->DBConnection;
        
        if ($GroupMember_id) { $where = " GroupMember_id=$GroupMember_id"; }
        else { 
            $where = " Group_id=$Group_id and ControlledObject_id=$ControlledObject_id"; 
            if ($on_what_id) { $where .= " AND on_what_id=$on_what_id"; }
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
     /** function add_group_object
       *
       * Adds a particular ControlledObject to a group, based on the ControlledObject type and the value used to identify the specify ControlledObject
       *
       * @param integer Group_id to specify which group to add into
       * @param integer ControlledObject_id identifying what class of ControlledObject is being added
       * @param integer on_what_id identifying what value identifies the unique ControlledObject
       * @return integer GroupMember_id with new identifier for new entry in the group or false if duplicate/failed
       *
       */
    function add_group_object($Group_id, $ControlledObject_id, $on_what_id) {
        if (!$Group_id OR !$ControlledObject_id or !$on_what_id) { return false; }

        $con = $this->DBConnection;
        $tblName = "GroupMember";
 
        //look for existing relationship of this nature, return false if there it already exists
        if ($this->get_group_member($Group_id, $ControlledObject_id, $on_what_id)!== false ) {
            return false;
        }
                
        //set of array for insert
        $GroupRow['Group_id']=$Group_id;
        $GroupRow['ControlledObject_id']=$ControlledObject_id;
        $GroupRow['on_what_id']=$on_what_id;
        
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
        
        $sql = "Delete from $tblName WHERE GroupMember_id=$GroupMember_id";
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
    function get_role_permission($Role_id=false, $ControlledObjectRelationship_id=false, $Scope=false, $Permission_id=false, $RolePermission_id=false) {
        if (!$Role_id and !$ControlledObjectRelationship_id and !$RolePermission_id) { return false; }
        $tblName = "RolePermission";
        
        $con = $this->DBConnection;
        $where=array();
        
        if ($RolePermission_id) { $where[]="RolePermission_id=$RolePermission_id"; }
        else {
            if ($Role_id) { $where[]="Role_id=$Role_id"; }
            if ($ControlledObjectRelationship_id) { $where[]="ControlledObjectRelationship_id=$ControlledObjectRelationship_id"; }
            if ($Permission_id) { $where[]="Permission_id=$Permission_id"; }
            if ($Scope) { $where[]="Scope=" . $con->qstr($Scope,get_magic_quotes_gpc()); }
            
        }
        $whereclause = implode (" and ", $where);
        //Search within group specified
        $sql = "SELECT * FROM $tblName WHERE $whereclause ORDER BY Permission_id";
         
        $rs = $con->execute($sql);
        if (!$rs) { db_error_handler($con, $sql); return false; }
        if ($rs->numRows()>1) {
            while (!$rs->EOF) {
                $ret[$rs->fields['RolePermission_id']] = $rs->fields;
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
     /** function add_role_permission
       *
       * Adds a permission on a type of controlled object to a role
       *
       * @param integer Role_id specifying which role to add this permission to
       * @param integer ControlledObjectRelationship_id specifying which controlled object the permission applies to
       * @param string Scope determining what scope the permission should apply to (currently World, Group and User)
       * @param integer Permission_id specifying which type of permission to add on this controlled object for this role
       * @return integer RolePermission_id with new identifier for role permission in the database or false if duplicate/failed
       *
       */
     function add_role_permission ($Role_id, $ControlledObjectRelationship_id, $Scope, $Permission_id) {
        if (!$Role_id or !$ControlledObjectRelationship_id or !$Scope or !$Permission_id) { return false; }
        
        $tblName="RolePermission";
        $con = $this->DBConnection;
        
        //no duplicates
        if ($this->get_role_permission($Role_id, $ControlledObjectRelationship_id, $Scope, $Permission_id)!==false) { return false; }
        
        $RolePermissionRow['Role_id']=$Role_id;
        $RolePermissionRow['ControlledObjectRelationship_id']=$ControlledObjectRelationship_id;
        $RolePermissionRow['Scope']=$con->qstr($Scope);    
        $RolePermissionRow['Permission_id']=$Permission_id;        
        
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
      * @return integer ControlledObject_id identifying the controlled object in the database or false if not found
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
    function add_controlled_object($ControlledObject_name, $on_what_table, $on_what_field, $user_field=false, $data_source_id=false) {
        $tblName="ControlledObject";
        $con = $this->DBConnection;
        
        //Find role, if already defined
        if ($this->get_controlled_object($ControlledObject_name,false,false)!==false) { return false; }
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
       * Searches for a particular controlled object relationship or a set of group_member_id's based on parameters
       *
       * @param integer ParentControlledObject_id to specify which object is the parent in the relationship
       * @param integer ChildControlledObject_id to specify which object is the child in the relationship
       * @return integer ControlledObjectRelationship_id with new identifier for new entry for the relationship or false if duplicate/failed (or array if no on_what_id is specified)
       *
       */
    function get_controlled_object_relationship($ParentControlledObject_id=false, 
                                                $ChildControlledObject_id=false,
                                                $ControlledObjectRelationship_id=false,
                                                $IncludeControlledObject=false) 
        {
//        if (!$ParentControlledObject_id and !$ChildControlledObject_id and !$ControlledObjectRelationship_id) { return false; }
        $tblName = "ControlledObjectRelationship";
        
        $con = $this->DBConnection;
        $where=array();
        
        if ($ControlledObjectRelationship_id) { $where[]="ControlledObjectRelationship_id=$ControlledObjectRelationship_id"; }
        else {
            if ($ParentControlledObject_id) { $where[]="ParentControlledObject_id=$ParentControlledObject_id"; }
            if ($ChildControlledObject_id) { $where[]="ChildControlledObject_id=$ChildControlledObject_id"; }
        }
        $whereclause = implode (" and ", $where);
        //Search within group specified
        if ($IncludeControlledObject) {
            $sql = "SELECT $tblName.*, Parent.on_what_table as parent_table, Parent.on_what_field as parent_field, Child.* FROM $tblName
             LEFT OUTER JOIN ControlledObject As Parent ON Parent.ControlledObject_id=$tblName.ParentControlledObject_id
             JOIN ControlledObject As Child ON Child.ControlledObject_id=$tblName.ChildControlledObject_id ";
        } else {
            $sql = "SELECT * FROM $tblName ";
        }
        if ($whereclause) $sql.= " WHERE $whereclause";
        
        $rs = $con->execute($sql);
        
        if (!$rs) { db_error_handler($con, $sql); return false; }
        if ($rs->numRows()>1) {
            while (!$rs->EOF) {
                $ret[$rs->fields['ControlledObjectRelationship_id']] = $rs->fields;
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
       * @return integer ControlledObjectRelationship_id with identifier for newly added ControlledObjectRelationship, or false if duplicate/failed
       *
       */
    function add_controlled_object_relationship($parentControlledObject_id, $childControlledObject_id, $on_what_child_field=false, $on_what_parent_field=false, $cross_table=false, $singular=false) {
    
        if (!$parentControlledObject_id AND !$childControlledObject_id) { return false; }
        
        $con=$this->DBConnection;
        $tblName = "ControlledObjectRelationship";
        
        //ensure no duplicates
        if ($this->get_controlled_object_relationship($parentControlledObject_id, $childControlledObject_id) !== false) { return false; }
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
       * @param integer ControlledObjectRelationship_id specifying which controlled object relationship to delete
       * @return bool indicating success (true) or failure (false) of delete
       *
       */
    function delete_controlled_object_relationship($ControlledObjectRelationship_id) {
        if (!$ControlledObjectRelationship_id) { return false; }
        $tblName="ControlledObjectRelationship";
        $con = $this->DBConnection;
        
        $sql = "Delete from $tblName WHERE ControlledObjectRelationship_id=$ControlledObjectRelationship_id";
        $rs = $con->execute($sql);
        if (!$rs) { db_error_handler($con, $sql); return false; }
        else return true;         
    }    
        
    /*****************************************************************************/
    /** function get_controlled_object_children
     *
     * Returns a list of children that are contained in a controlled object
     *
     * @param integer ControlledObject_id identifying group for which to retrieve a list of children
     * @return Array containing a list of ControlledObjects which are the children to the referenced object
     *
     **/
    function get_controlled_object_children($ControlledObject_id) {    
    
    }

    /*****************************************************************************/
    /** function get_controlled_object_parents
     *
     * Returns a list of parents that contain a ControlledObject
     *
     * @param integer ControlledObject_id identifying group for which to retrieve a list of parents
     * @return Array containing a list of ControlledObjects to which the referenced object belongs
     *
     **/
    function get_controlled_object_parents($ControlledObject_id) {
    
    }

    /*****************************************************************************/
    /** function get_controlled_object_relationship_parent
     *
     * Returns the parent in a ControlledObjectRelationship
     *
     * @param integer ControlledObjectRelationship_id identifying group for which to retrieve the parent
     * @return integer ParentControlledObject_id which is the parent in the specified relationship
     *
     **/
    function get_controlled_object_relationship_parent($ControlledObjectRelationship_id) {
    
    }

    /*****************************************************************************/
    /** function get_controlled_object_relationship_child
     *
     * Returns the child in a ControlledObjectRelationship
     *
     * @param integer ControlledObjectRelationship_id identifying the ControlledObject for which to retrieve the child
     * @return integer ChildControlledObject_id which is the child in the specified relationship
     *
     **/
    function get_controlled_object_relationship_child($ControlledObjectRelationship_id) {
    
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
        if ($_on_what_field) $on_what_field=$_on_what_field;
        
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
     * @return array $restricted_list with key 'controlled_objects' array of ID's of the ControlledObject ID for which user has searched for permission, key 'groups' array of GroupIDs to which the user has access, key 'roles' used to find list
     *
     **/
    function get_restricted_object_list($ControlledObject_id, $User_id, $Permission_id=false) {
        $con=$this->DBConnection;
        //default to search for read permission
        if (!$Permission_id) { $Permission_id=1; }
        if (!is_numeric($Permission_id)) { 
            $Permission=$this->get_permission($Permission_id);
            $Permission_id=$Permission['Permission_id'];
        }
        //Get list of objects with this controlledobject_id
        $ControlledObjectCompleteList = $this->get_field_list($ControlledObject_id, array());
        
        //still have entire list to restrict
        $objectsLeft = $ControlledObjectCompleteList;
        $objectsFound=array();
                
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
        if (!is_array(current($ControlledObjectRelationships))) { $ControlledObjectRelationships=array($ControlledObjectRelationships); }
       
        $ScopeList=$this->get_scope_list();
        foreach ($ScopeList as $Scope) {
//            echo "Processing Scope $Scope at Level $ControlledObject_id<br>";
             switch ($Scope) {
                case 'World':
                     //WORLD SCOPE, use all roles
                    $RoleList=$UserRoleList;
                    break;
                case 'User':
                    reset($ControlledObjectRelationships);
                    $FirstRelationship=current($ControlledObjectRelationships);
                    $user_field=$FirstRelationship['user_field'];
                    if (!$user_field) $user_field='user_id';
                    //if there is no user info on this level, simply do not search for it
                    if (!$this->check_field_exists($ControlledObject_id, $user_field)) continue 2;
                    $RoleList=$UserRoleList;
                    break;
                case 'Group':
                    //Leave group handling seperately
                    continue 2;
                    break;
            }
            //LOOP ON ALL ROLES HELD BY USER IN ALL GROUPS
            foreach ($RoleList as $urKey=>$Role) {
                foreach ($ControlledObjectRelationships as $ControlledObjectRelationship) {
                     $ControlledObjectRelationship_id=$ControlledObjectRelationship["ControlledObjectRelationship_id"];
                    //Search for permission for all roles on controlled object relationships
                    $RolePermission = $this->get_role_permission($Role, $ControlledObjectRelationship_id, $Scope, $Permission_id);
//                    echo "$RolePermission = $this->get_role_permission($Role, $ControlledObjectRelationship_id, $Scope, $Permission_id)";
                    
                    if ($RolePermission) {
                        switch ($Scope) {
                            //WORLD SCOPE
                            case 'World':
                                //If found, return whole list, and groups/roles
                                $ret=array();
                                $ret['controlled_objects']=$ControlledObjectCompleteList;
                                $ret['ALL']=true;
                                $ret['groups']=$GroupList;
                                $ret['roles']=$RoleList;
                                return $ret;
                                break;
                            case 'User':
                                 //USER SCOPE
                                 //Search for user permissions on this object type
                                //If found, query all objects with this user_id, add to list
                                $fieldRestrict=array();
                                $fieldRestrict[$user_field]=$User_id;
                                $UserList=$this->get_field_list($ControlledObject_id, $fieldRestrict);
                                if ($UserList) {
                                    //Restrict master list of objects, if empty, return the whole thing
                                    //If not, next scope
                                    $objectsFound=array_merge($objectsFound, $UserList);
                                    $objectsLeft = array_diff($ControlledObjectCompleteList, $objectsFound);
                                    if (count($objectsLeft)==0) {
                                        $ret=array();
                                        $ret['controlled_objects']=$ControlledObjectCompleteList;
                                        $ret['ALL']=true;
                                        $ret['groups']=$GroupList;
                                        $ret['roles']=$RoleList;
                                        return $ret;
                                    }
                                }
                                break;
                        }
                    }
                }
            }
        }
        $Scope='Group';
        foreach ($GroupRoleList as $Group_id=>$RoleList) {
            foreach($RoleList as $Role) {
                foreach ($ControlledObjectRelationships as $ControlledObjectRelationship) {
                    $ControlledObjectRelationship_id=$ControlledObjectRelationship["ControlledObjectRelationship_id"];
                    //Search for permission for all roles on controlled object relationships
                    $RolePermission = $this->get_role_permission($Role, $ControlledObjectRelationship_id, $Scope, $Permission_id);
//                    echo "<pre>                    $RolePermission = $this->get_role_permission($Role, $ControlledObjectRelationship_id, $Scope, $Permission_id)";
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
                            $objectsLeft = array_diff($ControlledObjectCompleteList, $objectsFound);
                            if (count($objectsLeft)==0) {
                                $ret=array();
                                $ret['controlled_objects']=$ControlledObjectCompleteList;
                                $ret['ALL']=true;
                                $ret['groups']=$GroupList;
                                $ret['roles']=$RoleList;
                                return $ret;
                            }
                        }
                    }
                }
            }
        }
//        echo "AFTER USER AND WORLD AND GROUP, WE HAVE<pre>"; print_r($objectsFound); echo "\nok!</pre>";
//        echo "LOOPING PARENTS<br>";
        //Loop on relationships, find parent objects   
        foreach ($ControlledObjectRelationships as $Relationship) {
            $ParentObject = $Relationship['ParentControlledObject_id'];
                //This recursively calls same function
            if ($ParentObject) {
//                echo "<br>Recursing to parent $ParentObject<br>";
                $recurse = $this->get_restricted_object_list($ParentObject, $User_id, $Permission_id);
                if ($recurse) {
//                    echo "Results: <pre>"; print_r($recurse); echo "</pre>";
                    //if we found all parent objects, return all children
                    if ($recurse['ALL']) {
 //                       echo "FOUND ALL PARENTS in $ParentObject<pre>"; print_r($Relationship); echo "</pre>";
                        if ($Relationship['singular']==1) {
                            $fieldRestrict['on_what_table']=$con->qstr($Relationship['parent_table']);
                            //get the list of objects who are owned by this parent table
                            $ParentList=$this->get_field_list($ControlledObject_id, $fieldRestrict);
//                            echo "ALREADY FOUND: <pre>"; print_r($objectsFound); echo "</pre>";
//                            print_r($ParentList);
                            if ($ParentList) {
                                $objectsFound=array_unique(array_merge($objectsFound, $ParentList));
                                $objectsLeft = array_diff($ControlledObjectCompleteList, $objectsFound);
                                if (count($objectsLeft)==0) {
                                    $ret=array();
                                    $ret['controlled_objects']=$ControlledObjectCompleteList;
                                    $ret['ALL']=true;
                                    $ret['groups']=$recurse['groups'];
                                    $ret['roles']=$recurse['roles'];
                                    return $ret;
                                }
                            }
                        } else {
                            $ret['controlled_objects']=$ControlledObjectCompleteList;
                            $ret['ALL']=true;
                            $ret['groups']=$recurse['groups'];
                            $ret['roles']=$recurse['roles'];
                            return $ret;
                        }
                    }
                    $ParentIDs = $recurse['controlled_objects'];
                    //walk to the top level, stop when we have no parent
                    $fieldRestrict=array();
                    if ($Relationship['singular']==1) {
                        $on_what_child_field='on_what_id';
                        $fieldRestrict['on_what_table']=$con->qstr($Relationship['parent_table']);
                    } else {
                        if ($ParentObject AND ($ParentObject>0)) {
                            if (!$Relationship['on_what_child_field']) {
                                $on_what_child_field=$Relationship['parent_field'];
                            } else {
                                $on_what_child_field=$Relationship['on_what_child_field'];
                            }
                            if ($Relationship['cross_table']) {
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
                    $fieldRestrict[$on_what_child_field]=$ParentIDs;
//                    print_r($fieldRestrict);
                    $ParentList=$this->get_field_list($ControlledObject_id, $fieldRestrict, $find_field, $on_what_table);
//                    echo "<p>$ParentList=\$this->get_field_list($ControlledObject_id, $fieldRestrict, $find_field, $on_what_table)";
                    if ($ParentList) {
                        $objectsFound=array_merge($objectsFound, $ParentList);
                        $objectsLeft = array_diff($ControlledObjectCompleteList, $objectsFound);
                        if (count($objectsLeft)==0) {
                            $ret=array();
                            $ret['controlled_objects']=$ControlledObjectCompleteList;
                            $ret['ALL']=true;
                            $ret['groups']=$recurse['groups'];
                            $ret['roles']=$recurse['roles'];
                            return $ret;
                        }
                    }
                }
            }
        }

        if (count($objectsFound)>0) {        
            $ret=array();
            $ret['controlled_objects']=$objectsFound;
            $ret['groups']=$GroupList;
            $ret['roles']=$UserRoleList;
                
            if (count($objectsLeft)==0) {
                $ret['ALL']=true;
            }
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
     * @param integer _ControlledObjectRelationship_id optional, identifying in what relationship of the controlled object to check permissions on
     * @param array PermissionList of Permission_id's to search for (or integer to only search for one)
     * @return array of Permission_ids on a class of ControlledObject for a user
    */    
    function get_permission_user_object(
                                                    $ControlledObject_id, 
                                                    $User_id, 
                                                    $_ControlledObjectRelationship_id=false,
                                                    $PermissionList=false
                                                  )                                                  
    {
    
        if (!$User_id OR !$ControlledObject_id) return false;
        if (!$PermissionList) {
            $PermissionList=$this->get_permissions_list();
        }
        if (!is_array($PermissionList)) { $PermissionList = array( $PermissionList ); }
        foreach($PermissionList as $pkey=>$Perm) {
            if (!is_numeric($Perm)) {
                $nPerm=$this->get_permission($Perm);
                if ($nPerm) {
                    $PermissionList[$pkey]=$nPerm['Permission_id'];
                }
            }
        }
        
        $UserRoleList = $this->get_user_roles_by_array(false, $User_id);
        if (!$UserRoleList) { return false; }
        $UserRoleList=$UserRoleList['Roles'];
        if (count($UserRoleList)==0) return false;
        
        if (!$_ScopeList) {
            $ScopeList = $this->get_scope_list();
        }
        if (!is_array($ScopeList)) { $ScopeList=array($ScopeList); }
        $FoundPermissionList=array();    
        $ControlledObjectRelationships = $this->get_controlled_object_relationship(false, $ControlledObject_id, $_ControlledObjectRelationship_id, true);

        if (!$ControlledObjectRelationships) { $ControlledObjectRelationships=array(); }
        if (!is_array(current($ControlledObjectRelationships)) AND (count($ControlledObjectRelationships)>0)) {
            //if only one is found and it is not an array, put it in an array
            $ControlledObjectRelationships=array($ControlledObjectRelationships['ControlledObjectRelationship_id']=>$ControlledObjectRelationships);
        }
//        echo "SEARCHING CONTROLLED OBJECTS: <pre>"; print_r($ControlledObjectRelationships);
        foreach ($ControlledObjectRelationships as $ControlledObjectRelationship_id=>$ControlledObjectRelationship_data) {
            foreach ($UserRoleList as $Role) {
                $RolePermission = $this->get_role_permission($Role, $ControlledObjectRelationship_id);
                if ($RolePermission) {
                    if (!is_array(current($RolePermission))) $RolePermission=array($RolePermission);
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
            }
            if ($ControlledObjectRelationship_data['ParentControlledObject_id'] AND $ControlledObjectRelationship_data['ParentControlledObject_id']>0) {
//                echo "\nLOOPING TO PARENT\n";
                $parentPermissionList=$this->get_permission_user_object($ControlledObjectRelationship_data['ParentControlledObject_id'], $User_id, false, $leftPermissions);
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
     * @param integer ControlledObjectRelationship_id optional, identifying in what relationship of the controlled object to check permissions on
     * @param array PermissionList of Permission_id's to search for (or integer to only search for one)
     * @return array of Permission_ids on a particular ControlledObject for a user
     *
     **/
    function get_permissions_user(
                                                    $ControlledObject_id, 
                                                    $on_what_id, 
                                                    $User_id, 
                                                    $_ControlledObjectRelationship_id=false,
                                                    $PermissionList=false,
                                                    $_ControlledObjectRelationships=false,
                                                    $_ScopeList=false
                                                  )
    {        
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
        //by default, do not search for user permissions
        $SearchUser=false;
                      
        //Get all roles for user
        $UserRoleList = $this->get_user_roles_by_array(false, $User_id);
        if (!$UserRoleList) { $SearchLevel=false; }
        $UserRoleList=$UserRoleList['Roles'];
        
/*        //Get the groups which this particular controlled object exist in
        $GroupList = $this->get_object_groups($ControlledObject_id,$on_what_id, $_ControlledObjectRelationship_id);
        if (!$GroupList) { $SearchGroups=false; }
        else {
            //Get the roles this user has within the groups found
            $GroupRoleList = $this->get_user_roles_by_array ($GroupList,  $User_id);
            if (!$GroupRoleList) { $SearchGroups=false; }
            else {
                $GroupRoleList=$GroupRoleList['Roles'];
            }
        }
        */
        //get the list of possible parents to this controlled object
        $ControlledObjectRelationships = $this->get_controlled_object_relationship(false, $ControlledObject_id, $_ControlledObjectRelationship_id, true);
        
        //if none are found, make a blank array
        if (!$ControlledObjectRelationships) { $ControlledObjectRelationships=array(); }
        if (!is_array(current($ControlledObjectRelationships)) AND (count($ControlledObjectRelationships)>0)) {
            //if only one is found and it is not an array, put it in an array
            $ControlledObjectRelationships=array($ControlledObjectRelationships['ControlledObjectRelationship_id']=>$ControlledObjectRelationships);
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
//            echo "SEARCHING<br>";
            //Loop through the scope list, starting from World -> Group -> User
            foreach ($ScopeList as $Scope) {
                switch ($Scope) {
                    case 'World':
//                        echo "SEARCHING WORLD PERMISSIONS<br>";
                        //search all roles for world permissions
                        $RoleList = $UserRoleList;
                    break;
                    case 'Group':
                        if (!$on_what_id) { $SearchGroups=false; }//echo "No id provided, cannot check permissions"; return false; }
                        if ($SearchGroups) {
                            $GroupList = $this->get_object_groups($ControlledObject_id,$on_what_id, $_ControlledObjectRelationship_id);
                            if (!$GroupList) { $SearchGroups=false; }
                            else {
                                //Get the roles this user has within the groups found
                                $GroupRoleList = $this->get_user_roles_by_array ($GroupList,  $User_id);
                                if (!$GroupRoleList) { $SearchGroups=false; }
                                else {
                                    $GroupRoleList=$GroupRoleList['Roles'];
                                }
                            }
                        }

                        if (!$SearchGroups) {
                            continue 2;
                        }
//                        echo "SEARCHING GROUP PERMISSIONS<br>";
                        //search only roles in groups that this object is part of (and parent groups)
                        $RoleList = $GroupRoleList;
                    break;
                    case 'User':
                        if (!$on_what_id) { $SearchUser=false; }//echo "No id provided, cannot check permissions"; return false; }
                        
                        reset($ControlledObjectRelationships);
                        $FirstRelationship=current($ControlledObjectRelationships);
                        
                        $user_field=$FirstRelationship['user_field'];
                        if (!$user_field) $user_field='user_id';
                        //Check to see if user owns object before applying user permissions
                        if ($on_what_id) {
                            $objectData=$this->get_controlled_object_data($ControlledObject_id, $on_what_id);
                            if ($objectData[$user_field]==$User_id) {
                                $SearchUser=true;
                            }
                        }
                    
                        //if we don't control this object, do not use user permissions
                        if (!$SearchUser) {
                            continue 2;
                        } else {
//                            echo "SEARCHING USER PERMISSIONS<br>";
                            //otherwise search in all roles for the user
                           $RoleList = $UserRoleList;
                        }
                    break;
                }
//                echo "SEARCHING FOR ROLES:<pre>"; print_r($RoleList); echo "</pre>";
                //Loop through the relationships in which this controlled object exists
                foreach ($ControlledObjectRelationships as $ControlledObjectRelationship_id => $ControlledObjectRelationship) {
                    if ($ControlledObjectRelationship_id AND $ControlledObjectRelationship_id>0) {
                        //Loop through the Roles a user has for this controlled object
                        foreach ($RoleList as $Role) {
//                            echo "SEARCHING ROLE $Role<br>";
                            //get permissions for this role on this ControlledObjectRelationship with the current scope
                            $RolePermission = $this->get_role_permission($Role, $ControlledObjectRelationship_id, $Scope);
                            if ($RolePermission) {
                                if (!is_array(current($RolePermission))) { $RolePermission = array ( $RolePermission); }
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
                            } 
                        }
                    }
                }
            }
        }
        //Get parent objects for this particular controlled object
        $objectParents = $this->get_object_relationship_parent($ControlledObject_id, $on_what_id, $_ControlledObjectRelationship_id, $objectData);

        //If there are any parents, loops through them and get permissions on them
        if ($objectParents AND is_array($objectParents)) {
            foreach ($objectParents as $ControlledObjectRelationship_id=>$aparent) {
                //Recurse into parent objects to get permissions, only searching for permissions we haven't found yet
                if (!is_array(current($aparent))) $aparent=array($aparent);
                foreach ($aparent as $parent) {
                    $ret = $this->get_permissions_user($parent['ControlledObject_id'],$parent['on_what_id'],$User_id, false, $leftPermissions, $ControlledObjectRelationships);
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
    /** function get_permissions_list
     *
     * Returns the possible permissions
     *
     * @param void
     * @return array of possible permissions
     *
     **/
    function get_permissions_list() {
        return array (1,2,3,4);
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
    /** function get_group_list
     *
     * Returns the current list of group
     *
     * @param void
     * @return array of groups
     *
     **/
    function get_groups_list() {
        
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

    /*****************************************************************************/
    /** function get_controlled_object_list
     *
     * Returns the possible controlled objects
     *
     * @param void
     * @return array of controlled objects
     *
     **/
    function get_controlled_object_list() {
        
    }
    
    
}  

/*
 * $Log: xrms_acl.php,v $
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