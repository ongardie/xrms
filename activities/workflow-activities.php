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


//the beginning of the insert statement to add values
$sql_insert = "insert into activities
    (activity_type_id,
    activity_description,
    ends_at,
    user_id,
    company_id,
    contact_id,
    on_what_table,
    on_what_id,
    on_what_status,
    activity_title,
    entered_at,
    entered_by,
    scheduled_at,
    activity_status,
    activity_record_status) values ";

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

    //generate SQL insert statement
    $sql_insert .= "($activity_type_id,
        '',
        '$ends_at 23:59:59',
        '$user_id',
        '$company_id',
        '$contact_id',
        '$on_what_table',
        '$on_what_id',
        '$on_what_id_template',
        '$activity_title',
        " . $con->DBTimeStamp(date ('Y-m-d H:i:s')) . ",
        $user_id,
        " . $con->DBTimeStamp(date ('Y-m-d H:i:s')) . ",
        'o',
        'a')";

    $rst->movenext();
    }
    $rst->close();
}

$con->execute($sql_insert);

?>
