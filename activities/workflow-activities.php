<?php

/*
 * Author: Brad Marshall
 * Description: generates activities that are linked to the status
 *   when the status is changed.
 * Date: 2004/05/24
 *
 */



$sql = "select * from activity_templates
    where on_what_table='$on_what_table_template'
    and on_what_id=$on_what_id_template
    and activity_template_record_status='a'
    order by sort_order";

$rst = $con->execute($sql);

//generates insert statement to add activities to the current list
$cnt = 0;
if ($rst) {
    while (!$rst->EOF) {

        //puts a comma before the set of values (skips the first set)
        if ($cnt == 0) {
            $cnt++;
        } else {
            $sql_insert .= ", ";
        }
    
        //get the field values from the next record in the query
        $activity_type_id = $rst->fields['activity_type_id'];
        $activity_title = $rst->fields['activity_title'];
        $activity_description = $rst->fields['activity_description'];
        $duration = $rst->fields['duration'];
    
    
        //calculate ends_at, based on duration and current date
        if ( is_numeric("$duration") ) {
            $duration = $duration.' days';
        }
    
        $ends_at = date('Y-m-d',strtotime($duration));
        
        $sql2 = "SELECT * FROM activities WHERE 1 = 2"; //select empty record as placeholder
        $rst2 = $con->execute($sql2);

        $rec = array();
        $rec['activity_type_id'] = $activity_type_id;
        $rec['activity_description'] = '';
        $rec['ends_at'] = $con->DBTimeStamp(date('Y-m-d 23:59:59', strtotime($ends_at)));
        $rec['user_id'] = $user_id;
        $rec['company_id'] = $company_id;
        $rec['contact_id'] = $contact_id;
        $rec['on_what_table'] = $on_what_table;
        $rec['on_what_id'] = $on_what_id;
        $rec['on_what_status'] = $on_what_id_template;
        $rec['activity_title'] = $activity_title;
        $rec['entered_at'] = $con->DBTimeStamp(date('Y-m-d H:i:s'));
        $rec['entered_by'] = $user_id;
        $rec['scheduled_at'] = $con->DBTimeStamp(date('Y-m-d H:i:s'));
        $rec['activity_status'] = 'o';
        $rec['activity_record_status'] = 'a';
        
        $ins = $con->GetInsertSQL($rst2, $rec, get_magic_quotes_gpc());
        $con->execute($ins);
        
        $rst->movenext();
    }
    $rst->close();
}

?>
