<?php
/**
 * Browse Activity Types Sidebar
 * /activities/browse-sidebar.php
 *
 * Include this sidebar anywhere that you would like to show a list of activity types. 
 * It will allow a user to work on a single type of activity, clicking save and next to stay within that type.
 * Once the type has been fully traversed, it drops to the activity type of next lowest priority.
 * If there are no more activities left, it returns to this screen.
 *
 * @author Neil Roberts
 *
 * $Id: browse-sidebar.php,v 1.5 2004/07/16 04:53:51 introspectshun Exp $
 */

//add contact information block on sidebar
$browse_block = '<table class=widget cellspacing=1 width="100%">
    <tr>
        <td class=widget_header colspan=5>' . _("Browse") . '</td>
    </tr>
    <tr>
        <td class=widget_label>' . _("Activity Types") . '</td>
    </tr>';

$sql = "select activity_type_id, activity_type_display_html
        from activity_types
        order by sort_order asc";

$rst = $con->execute($sql);

if(!$rst) {
     db_error_handler($con, $sql);
}
elseif($rst->rowcount() > 0) {
    while(!$rst->EOF) {
        $browse_block .= "\n<tr><td class=widget_content>"
            . "<a href='browse-next.php?activity_type_id=" . $rst->fields['activity_type_id'] . "'>"
            . $rst->fields['activity_type_display_html'] . "</a></td></tr>";
        $rst->movenext();
    }
} else {
    $browse_block .= "<tr><td class=widget_content>"
        . _("No Activities Types")
        . "</td>\n\t</tr>";
}

$browse_block .= "\n</table>";
$rst->close();

/**
 * $Log: browse-sidebar.php,v $
 * Revision 1.5  2004/07/16 04:53:51  introspectshun
 * - Localized strings for i18n/translation support
 *
 * Revision 1.4  2004/07/02 17:57:57  neildogg
 * Now works for all activity types. Sort by reimplemented in SQL call.
 *
 * Revision 1.3  2004/07/02 14:56:52  maulani
 * - Repair HTML so page will validate
 *
 * Revision 1.2  2004/06/28 14:35:09  maulani
 * - Added dollar sign to sort-order variable
 * - Added phpdoc
 *
 */
?>
