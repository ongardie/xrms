<?php
/**
 * workflow-activities.php -  generates activities that are linked to
 *                            the workflow status when the status is changed.
 *
 * @author Brad Marshall
 * @author Brian Peterson
 *
 * $Id: utils-workflow.php,v 1.3 2006/05/06 09:30:30 vanmer Exp $
 *
 * @todo To extend and internationalize activity template substitution,
 *       we would need to add a table to the database that would hold
 *       the substitution string and the sql to execute to return
 *       a single field to substitute.
 *       Then, this page would retrieve the result set for string/sql pairs, and
 *       run through the result set and do a test/select/substitute for each member
 *       the substitution result set.
 */

 require_once($include_directory.'utils-activities.php');
 require_once($include_directory.'utils-misc.php');
 require_once($include_directory.'utils-contacts.php');
 require_once($include_directory.'utils-companies.php');

function get_activity_template($con, $activity_template_id) {
    if (!$con OR !$activity_template_id) return false;
    $sql = "select * from activity_templates
        where activity_template_id=$activity_template_id";

    $rst = $con->execute($sql);
    if (!$rst) { db_error_handler($con, $sql); return false; }
    if (!$rst->EOF) return $rst->fields;
    else return false;

}

function find_activity_template($con, $on_what_table_template, $on_what_id_template, $template_sort_order=false, $show_all=false) {
    $sql = "select * from activity_templates
        where on_what_table='$on_what_table_template'
        and on_what_id=$on_what_id_template";
    
    if ($template_sort_order) $sql.=" and sort_order=$template_sort_order";
    
    if (!$show_all) $sql .=" and activity_template_record_status='a'";
    $sql .= " order by sort_order";
    
    $rst = $con->execute($sql);
    if (!$rst) { db_error_handler($con, $sql); return false; }
    $ret=array();
    while (!$rst->EOF) {
        $template_info=$rst->fields;
        $ret[]=$template_info;
        $rst->movenext();
    }
    if (count($ret)>0) {
        return $ret;
    } else return false;
}

function add_workflow_activities($con, $on_what_table_template, $on_what_id_template, $on_what_table, $on_what_id, $company_id, $contact_id, $template_sort_order=1) {
    if (!$template_sort_order) { $template_sort_order=1; }
    
    $activity_templates=find_activity_template($con, $on_what_table_template, $on_what_id_template, $template_sort_order);
    
    //generates insert statement to add activities to the current list
    $cnt = 0;
    if(empty($activity_record_status)) {
        $activity_record_status = 'a';
    }
    if ($activity_templates) {
        foreach ($activity_templates AS $template_info) {
        
            //get the field values from the next record in the query
            $activity_template_id =$template_info['activity_template_id'];
            $activity_type_id = $template_info['activity_type_id'];
            $activity_title = $template_info['activity_title'];
            $default_text = $template_info['default_text'];
            $activity_description = $template_info['activity_description'];
            $duration = $template_info['duration'];
            $activity_template_role_id = $template_info['role_id'];
            
            
            //calculate ends_at, based on duration and current date
            if ( is_numeric("$duration") ) {
                $duration = $duration.' days';
            }
            $ends_at = date('Y-m-d',strtotime($duration));
    
            /**
            * Do variable substitution on the Activity Title in an Activity Template
            *
            * @todo Move variable substitutions for actvity templates into a user-definable table.
            */
            if (strpos($activity_title, 'company_name')) {
                $company_data=get_company($con, $company_id);
                $company_name=$company_data['company_name'];
                if ($company_name) {
                    $activity_title = str_replace('company_name',$company_name,$activity_title);
                }
            }
            if (strpos($activity_title, 'contact_name')) {
                // get the contact name for variable substitution
                $contact_data=get_contact($con, $contact_id);
                if ($contact_data) {
                    $contact_name=$contact_data['first_names'] . " " . $contact_data['last_name'];
                    if ($contact_name) {
                        $activity_title = str_replace('contact_name',$contact_name,$activity_title);
                    }
                }
            }
            
            $activity_type_data=get_activity_type($con, false, false, $activity_type_id);
            if ($activity_type_data) {
                $activity_type_name=$activity_type_data['activity_type_short_name'];
                switch ($activity_type_name) {
                    //handle internal activity type
                    case 'INT':
                    break;
                    
                    //handle process activity type (instantiate new entity)
                    case 'PRO':
                        $entity=$template_info['workflow_entity'];
                        $entity_type=$template_info['workflow_entity_type'];
                        $ret=add_process_entity($con, $entity, $entity_type, $activity_title, $activity_description, $company_id, $contact_id, $on_what_table, $on_what_id);
                    break;
                    
                    //process system activities here
                    case 'SYS':
                        $ret=do_hook_function('workflow_system', $template_info);
                    break;
                    
                    default:
                    break;
                }
            }
    
            $user_id=get_least_busy_user_in_role($con, $activity_template_role_id, strtotime($ends_at));
            if (!$user_id) $user_id=$session_user_id;
            //save to database
            $rec = array();
            $rec['activity_type_id'] = $activity_type_id;
            $rec['activity_description'] = addslashes($default_text);
            $rec['ends_at'] = $ends_at;
            $rec['user_id'] = $user_id;
            $rec['activity_template_id']=$activity_template_id;
            $rec['company_id'] = $company_id;
            $rec['contact_id'] = $contact_id;
            $rec['on_what_table'] = $on_what_table;
            $rec['on_what_id'] = $on_what_id;
            $rec['on_what_status'] = $on_what_id_template;
            $rec['activity_title'] = addslashes($activity_title);
            $rec['entered_at'] = time();
            $rec['entered_by'] = $user_id;
            $rec['last_modified_at'] = time();
            $rec['last_modified_by'] = $user_id;
            //$rec['scheduled_at'] = time();
            $rec['activity_status'] = 'o';
            $rec['activity_record_status'] = $activity_record_status;
//            print_r($rec);
    //    $con->debug=true;        
            add_activity($con, $rec);
    /*
            $tbl = 'activities';
            $ins = $con->GetInsertSQL($tbl, $rec, get_magic_quotes_gpc());
            $ins_rst=$con->execute($ins);
            if (!$ins_rst) { db_error_handler($con, $sql); }
    //        echo "INSERTED ". $con->Insert_ID();
    */        
            do_hook_function('workflow_addition', $activity_template_id);
    
        }
    }
}


//function to load old status, compare to new status and return any activities which match the old status, and are linked to this particular entity
function get_open_workflow_activities_on_status_change($con, $on_what_table, $on_what_id, $new_status_id, $company_id, $contact_id, $old_status_id=false) {
    if (!$con OR !$on_what_table OR !$on_what_id OR !$new_status_id) return false;

    $table=$on_what_table;
    $entity=make_singular($on_what_table);
    
    if (!$old_status_id) {
        switch ($table) {
            case 'cases':
                $data=get_case($con, $on_what_id);
            break;
            case 'opportunities':
                $data=get_opportunity($con, $on_what_id);
            break;
        }
        if ($data) {
            $old_status=$data["{$entity}_status_id"];
        } else return false;
    } else { $old_status=$old_status_id; }
    
    if ($old_status != $new_status_id) {
    
        /* ADD CHECK TO SEE IF THERE ARE STILL OPEN ACTIVITIES FROM
            THE PREVIOUS STATUS, THEN GIVE THEM OPTIONS  */
        $activity_data=array();
        $activity_data['on_what_status']=$old_status;
        $activity_data['on_what_table'] = $on_what_table;
        $activity_data['on_what_id']=$on_what_id;
        $activity_data['contact_id']= $contact_id;
        $activity_data['company_id']=$company_id;
        $activity_data['activity_status']='o';
    
        $open_activities=get_activity($con, $activity_data);
        return $open_activities;
    } else return false;
}



/**
 * Function to add to workflow history, used to track status changes in entities that have workflow
 *
 * @param adodbconnection $con handle to the database
 * @param string $on_what_table with table of entity for which status changed
 * @param integer $on_what_id with db identifier for entity in table
 * @param integer $old_status with number of status entity has now
 * @param integer $new_status with number of status entity will have after change
 * @param integer $user_id optionally providing a user_id who made the change, otherwise $_SESSION['session_user_id'] is used
 * @return integer with database identifier of history entry, or false if failed
 *
**/
function add_workflow_history($con, $on_what_table, $on_what_id, $old_status, $new_status, $user_id=false) {
    if (!$on_what_table OR !$on_what_id OR !$old_status OR !$new_status) return false;
    $ins['on_what_table']=$on_what_table;
    $ins['on_what_id']=$on_what_id;
    $ins['old_status']=$old_status;
    $ins['new_status']=$new_status;
    if (!$user_id) $user_id=$_SESSION['session_user_id'];
    $ins['status_change_by']=$user_id;
    $ins['status_change_timestamp']=time();

    $table='workflow_history';

    $sql=$con->GetInsertSQL($table, $ins);
    if ($sql) {
        $rst=$con->execute($sql);
        if ($rst) return $con->Insert_ID();
	else db_error_handler($con, $sql);
    }
    return false;
}

/**
 * Function to add to workflow entity to the system.  This function creates the new case/opportunity in the workflow, based on data from an activity_template of the 'process' type
 * @todo This should be hacked eventually to use the add_ API for the entities being added, but for now this uses a GetInsertSQL statement
 *
**/
function add_process_entity($con, $entity, $entity_type, $title, $description, $company_id, $contact_id, $on_what_table, $on_what_id, $user_id=false) {

    if (!$entity OR !$entity_type) return false;
    global $session_user_id;
    global $include_directory;

    $singular_entity=make_singular($entity);

    $sql = "SELECT {$singular_entity}_status_id FROM {$singular_entity}_statuses WHERE {$singular_entity}_type_id=$entity_type";
    $rst=$con->SelectLimit($sql, 1);
    if (!$rst) { db_error_handler($con, $sql); return false; }
    if (!$rst->EOF) { $status=$rst->fields[$singular_entity.'_status_id']; }
    if (!$status) $status=1;

    $entity_data=array();

    if (!$user_id) $user_id=$session_user_id;

    $entity_data[$singular_entity.'_type_id']=$entity_type;
    $entity_data[$singular_entity.'_title']=$title;
    $entity_data[$singular_entity.'_description']=$description;
    if ($status) $entity_data[$singular_entity.'_status_id']=$status;
    $entity_data['company_id']=$company_id;
    $entity_data['contact_id']=$contact_id;
    $entity_data['division_id']=$division_id;
    $entity_data['last_modified_at'] = time();
    $entity_data['last_modified_by'] = $user_id;
    $entity_data['entered_at'] = time();
    $entity_data['entered_by'] = $user_id;
    $entity_data['user_id']=$user_id;

    switch ($entity) {
        case 'cases':
            $entity_data['case_priority_id']=1;
        break;
    }

//    $type_info="SELECT * FROM {$singular_entity}_types WHERE {$singular_entity}_type_id=$entity_type";
    $ins = $con->getInsertSQL($entity, $entity_data);
    if ($ins) {
        $rst=$con->execute($ins);
        if (!$rst) { db_error_handler($con, $ins); return false; }
        $entity_id=$con->Insert_ID();
    }

    //look up INTERNAL activity type
    $sql = "SELECT activity_type_id FROM activity_types WHERE activity_type_short_name=" . $con->qstr('INT', get_magic_quotes_gpc());
    $rst = $con->execute($sql);
    if (!$rst) { db_error_handler($con, $sql); return false; }
    else { $internal_type=$rst->fields['activity_type_id']; }

    //no activity type to link to, so fail
    if (!$internal_type) return false;

    //create shared details of activity
    $activity_detail=array();
    $user_id=$session_user_id;
    $activity_detail['company_id']=$company_id;
    $activity_detail['contact_id']=$contact_id;
    $activity_detail['activity_status'] = 'c';
    $activity_detail['activity_record_status'] = 'a';
    $activity_detail['activity_type_id'] = $internal_type;
    $activity_detail['entered_at'] = time();
    $activity_detail['entered_by'] = $user_id;
    $activity_detail['last_modified_at'] = time();
    $activity_detail['last_modified_by'] = $user_id;


    //create activity on old entity linking to newly created entity
    $entity_url = "<a href=\"$http_site_root" . table_one_url($entity, $entity_id) . "\">"._("New workflow process") ."</a>";

    $last_entity_activity=$activity_detail;
    $last_entity_activity['on_what_table']=$on_what_table;
    $last_entity_activity['on_what_id']=$on_what_id;
    $last_entity_activity['activity_title']=_("A new workflow has been started");
    $last_entity_activity['activity_description']=_("A new workflow of type") . " $entity_singular " . _(" has been created.  To access it, you can use the link here:") ." $entity_url";
    add_activity($con, $last_entity_activity);

    //create activity on new entity linking to old entity
    $entity_url = "<a href=\"$http_site_root" . table_one_url($on_what_table, $on_what_id) . "\">"._("Forked workflow process") . "</a>";
    $on_what_singular=make_singular($on_what_table);

    $new_entity_activity=$activity_detail;
    $new_entity_activity['on_what_table']=$entity;
    $new_entity_activity['on_what_id']=$entity_id;
    $new_entity_activity['activity_title']=_("Forked from old workflow");
    $new_entity_activity['activity_description']=_("This process was forked from a previous workflow of type:") . " $on_what_singular.  " . _("To access it, you can use the link here:") ." $entity_url";
    add_activity($con, $new_entity_activity);

    //generate activities for the new entity
    $on_what_table = $entity;
    $on_what_id = $entity_id;
    $on_what_table_template = "{$singular_entity}_statuses";
    $on_what_id_template = $status;

    add_workflow_activities($con, $on_what_table_template, $on_what_id_template, $on_what_table,$on_what_id, $company_id, $contact_id);

    return $entity_id;
}

function workflow_activity_completed($con, $on_what_table, $on_what_id, $activity_template_id, $company_id, $contact_id, $return_url) {
    $ret['allow_status_change']=true;
    $on_what_singular=make_singular($on_what_table);
    //get the entity status

    //this should eventually use a generic get_entity style API to retrieve this data instead of a generic on_what_singular call
    $table_status_sql = "SELECT {$on_what_singular}_status_id FROM {$on_what_table} WHERE {$on_what_singular}_id=$on_what_id";
    $table_status_rst=$con->execute($table_status_sql);
    $old_status=$table_status_rst->fields["{$on_what_singular}_status_id"];
    if (!$table_status_id) {
        $table_status_id=$old_status;
    }

    $status_data=get_entity_status($con, strtolower($on_what_singular), $table_status_id);
    if ($status_data) {
        $sort_order=$status_data['sort_order'];
        $type_id=$status_data[strtolower($on_what_singular).'_type_id'];
    } else { $type_id=false; }

        //by default allow the status change code to be run
        $allow_change_status=true;
        $ret['allow_status_change']=true;
            //get activity template data for current activity
            $sql="SELECT * FROM activity_templates WHERE activity_template_id=$activity_template_id";
            $template_rst= $con->execute($sql);
            if (!$template_rst) { db_error_handler($con, $sql); }
            $template_sort_order=$template_rst->fields['sort_order'];
            if ($template_sort_order!==false) {
                //find activities that come from the same template status/sort order
                $sql = "SELECT * from activities JOIN activity_templates ON activities.activity_template_id = activity_templates.activity_template_id WHERE activity_templates.on_what_table=" . $con->qstr($on_what_singular.'_statuses') . " AND activity_templates.on_what_id=$table_status_id AND  activity_templates.sort_order = $template_sort_order AND activities.activity_status='o' AND activities.activity_record_status='a'
                and activities.on_what_status='$old_status'
                and activities.on_what_table='$on_what_table'
                and activities.on_what_id=$on_what_id";
                $activity_sort_open=$con->execute($sql);
                if (!$activity_sort_open) { db_error_handler($con, $sql); }
                else {
                    //if we find none, then we can go to the next activities sort order
                    if ($activity_sort_open->EOF) $next_activities_sort=true;
                }
            }
    
            //no activities with the current sort order, so look for the next highest sort order
            if ($next_activities_sort) {
                //increment sort order to next highest
                if ($template_sort_order>0) { $template_sort_order+=1; }
    
                //now we look for the highest sort order for templates with the same status
                $sql = "SELECT MAX(sort_order) as max_sort_order FROM activity_templates WHERE activity_templates.on_what_table=" . $con->qstr($on_what_singular.'_statuses') . " AND activity_templates.on_what_id=$table_status_id";
                $max_sort=$con->execute($sql);
    //            echo $sql;
                if (!$max_sort) { db_error_handler($con, $sql); }
                //pull max sort order
                $max_sort_id=$max_sort->fields['max_sort_order'];
    
                //if we have a lower sort order than the maximum, then simply spawn workflow activities for our new sort order
                if ($template_sort_order <= $max_sort_id) {
                    $on_what_table_template = $on_what_singular .  "_statuses";
                    $on_what_id_template = $table_status_id;
                    //run the add_workflow_activities  to actually make the update
                    add_workflow_activities($con, $on_what_table_template, $on_what_id_template, $on_what_table, $on_what_id, $company_id, $contact_id, $template_sort_order);
                    //don't allow the status to be changed, since we just added workflow activities
                    $allow_change_status=false;
                }
            }
    
        if ($allow_change_status) {
            // null out old_status
            $old_status = '';
            
            /* this saves case/opportunity status changes to the database when they are changed in one.php */
            //check if there are open activities from this status
            if ($on_what_singular !== "attached to") {
                $open_activities=get_open_workflow_activities_on_status_change($con, $on_what_table, $on_what_id, $table_status_id, $company_id, $contact_id);
                if ($open_activities) {
                    $first_activity=current($open_activities);
                    $activity_return_id=$first_activity['activity_id'];
                    $no_update = true;
                } else {
                        //increment status sort order, find next status
                        $sort_order++;
                        $status_data=find_entity_status($con, $on_what_singular, $type_id, false, false, false, $sort_order);
                        if ($status_data) {
                            $status_data=current($status_data);
                            $table_status_id = $status_data[$on_what_singular . '_status_id'];
            
                            //look for activity_templates defined for the next status in the workflow
                            $on_what_table_template=$on_what_singular.'_statuses';
                            $on_what_id_template=$table_status_id;
                            $templates=find_activity_template($con, $on_what_table_template, $on_what_id_template);
                            //if there are templates defined for the next status, find it
                            if ($templates) {
                                $no_update = false;
                            } else { 
                                //next status has no templates defined for it, so do not update status automatically.  Set return to allow update
                                $no_update=true;
                                $ret['allow_status_change']=true;
                            }
                      }
                }
            
                    //if there is only one field, the result set is empty (no old activities)
                    //  otherwise prompt the user
                    if ($no_update) {
                        if ($activity_return_id) {
                            $return_url = "/activities/one.php?msg=no_change&activity_id=$activity_return_id";
                            $ret['allow_status_change']=false;
                        } elseif ($return_url) {
                            //no particular activity is still open, so we must have hit a status that has no template, send no_change message 
                            //@TODO add message to indicate no automatic status change to statuses with no templates associated with them
                            if (strpos($return_url,'?')!==false) { $sep='&'; }
                            else { $sep='?'; }
                            $return_url.=$sep.'msg=no_auto_change';
                        }
                        else{
                            $return_url="/private/home.php?msg=no_auto_change";
                        }

                        //add return url to return variable
                        $ret['return_url']=$return_url;

                    }
            
                    //update if there are no open activities
                    if (!$no_update) {
                        $rec = array();
                        $rec["{$on_what_singular}_id"]=$on_what_id;
                        $rec[$on_what_singular . "_status_id"] = $table_status_id;
            //            print_r($rec);
                        switch ($on_what_singular) {
                            case 'case':
                                update_case($con, $rec, $on_what_id, false, $magicq);
                            break;
                            case 'opportunity':
                                update_opportunity($con, $rec, $on_what_id, false, $magicq);
                            break;
                        }
                        $ret['allow_status_change']=false;
                    }
            }
        } else {
            $ret['allow_status_change']=false;
        }
    return $ret;
} //END WORKFLOW activity completed

/**
 *
 * $Log: utils-workflow.php,v $
 * Revision 1.3  2006/05/06 09:30:30  vanmer
 * - moved workflow functionality on a completed template activity into seperate function, from activities/edit-2.php
 *
 * Revision 1.2  2006/05/03 20:37:28  vanmer
 * - moved function to search for open workflow activities into utils-workflow functions
 * - changed cases and opportunities edit pages to use centralized open_workflow_activities function
 *
 * Revision 1.1  2006/04/29 01:44:02  vanmer
 * - added new file for workflow related functions (utils-workflow.php)
 * - moved workflow related functions out of utils-misc into utils-workflow.php
 *
 *
**/
?>