<?php
/**
 * Contact Information Sidebar
 *
 * Include this file anywhere you want to show a summary of the company information
 *
 * @author Neil Roberts
 *
 */

//add contact information block on sidebar
$browse_block = '<table class=widget cellspacing=1 width="100%">
    <tr>
        <td class=widget_header colspan=5>Browse</td>
    </tr>
    <tr>
        <td class=widget_label>Opportunity Activities</td>
    </tr>';

$sql = "select activity_type_id, activity_type_display_html
        from activity_types
        order by sort_order desc";

$rst = $con->execute($sql);

if(!$rst) {
     db_error_handler($con, $sql);
}
elseif($rst->rowcount() > 0) {
    while(!$rst->EOF) {
        $browse_block .= "\n<tr><td class=widget_content>"
            . "<a href='browse-next.php?current_on_what_table=opportunities&current_activity_type_id=" . $rst->fields['activity_type_id'] . "'>"
            . $rst->fields['activity_type_display_html'] . "</a></td></tr>";
        $rst->movenext();
    }
} else {
    $browse_block .= "<tr><td class=widget_content>"
        . "No Activities Types"
        . "</td>\n\t</tr>";
}

$browse_block .= '<tr>
        <td class=widget_label>Case Activities
    </tr>';

$sql = "select activity_type_id, activity_type_display_html
        from activity_types
        order by sort_order desc";

$rst = $con->execute($sql);

if(!$rst) {
     db_error_handler($con, $sql);
}
elseif($rst->rowcount() > 0) {
    while(!$rst->EOF) {
        $browse_block .= "\n<tr><td class=widget_content>"
            . "<a href='browse-next.php?current_on_what_table=cases&current_activity_type_id=" . $rst->fields['activity_type_id'] . "'>"
            . $rst->fields['activity_type_display_html'] . "</a></td></tr>";
        $rst->movenext();
    }
} else {
    $browse_block .= "<tr><td class=widget_content>"
        . "No Activities Types"
        . "</td>\n\t</tr>";
}

$browse_block .= "\n</table>";
$rst->close();

/**
 * $Log: *
 */
?>