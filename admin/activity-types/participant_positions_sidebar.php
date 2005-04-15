<?php

//if (!$activity_type_id) return false;
require_once($include_directory."utils-activities.php");
$activity_participant_positions=get_activity_participant_positions($con, false, $activity_type_id, false, false);
$return_url="/admin/activity-types/one.php?activity_type_id=$activity_type_id";
if ($activity_participant_positions) {
foreach ($activity_participant_positions as $position_data) {
    $activity_position_sidebar.="<tr><td class=widget_content>{$position_data['participant_position_name']}</td><td class=widget_form_element><a href='one_activity_participant_position.php?position_action=edit&activity_participant_position_id={$position_data['activity_participant_position_id']}'>Edit</a></tr>";
}
}

echo <<<TILLEND
<div id="Sidebar">
    <form action='one_activity_participant_position.php' method='POST'>
<input type=hidden name=activity_type_id value=$activity_type_id>
<input type=hidden name=position_action value='new'>
<input type=hidden name=return_url value="$return_url">
    
<table class=widget>
    <tr><td colspan=2 class=widget_header>Participant Positions</td></tr>
    $activity_position_sidebar
    <tr><td class=widget_label_right>Position Name</td><td class=widget_content_form_element><input type=text name='participant_position_name'></td></tr>
    <tr><td colspan=2 class=widget_content><input type=submit class=button value='Add New Participant Position'></td></tr>
    </table>
    </form>
</div>
TILLEND;
?>