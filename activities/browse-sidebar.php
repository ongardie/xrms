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
 * $Id: browse-sidebar.php,v 1.6 2004/07/21 22:21:41 neildogg Exp $
 */

//add contact information block on sidebar
$browse_block = '<form method=post action=browse-next.php>
<table class=widget cellspacing=1 width="100%">
    <tr>
        <td class=widget_header colspan=5>' . _("Browse") . '</td>
    </tr>
    <tr>
        <td class=widget_label>' . _("Activity Types") . '</td>
    </tr>
    <tr>
        <td class=widget_content>';

$sql = "SELECT activity_type_pretty_name, activity_type_id
        FROM activity_types
        WHERE activity_type_record_status='a'
        ORDER BY sort_order asc";

$rst = $con->execute($sql);

if(!$rst) {
     db_error_handler($con, $sql);
}
elseif($rst->rowcount() > 0) {
    $browse_block .= $rst->getmenu2('activity_type_id', 0, false) . " <input type=submit value=Browse>";
} else {
    $browse_block .= _("No Activities Types");
}

$browse_block .= '</td>
    </tr>
</table>
</form>';
$rst->close();

$browse_block .= '<form method=post action=browse-next.php>
<table class=widget cellspacing=1 width="100%">
    <tr>
        <td class=widget_header colspan=5>' . _("Saved Search Browse") . '</td>
    </tr>
    <tr>
        <td class=widget_label>' . _("Search Name") . '</td>
    </tr>
    <tr>
        <td class=widget_content>';
    
//get saved searches
$sql_saved = "SELECT saved_title, saved_id
        FROM saved_actions
        WHERE (user_id=$session_user_id
        OR group_item=1)
        AND on_what_table='activities'
        AND saved_action='search'
        AND saved_status='a'";
$rst = $con->execute($sql_saved);
if($rst->rowcount()) {
    $browse_block .= $rst->getmenu2('saved_id', 0, false) . " <input type=submit value=Browse>";
}
else {
    $browse_block .= _("No Saved Searches");
}

$browse_block .= '</td>
    </tr>
</table>
</form>';


/**
 * $Log: browse-sidebar.php,v $
 * Revision 1.6  2004/07/21 22:21:41  neildogg
 * - Rearranged sidebar
 *  - Now can browse saved searches
 *
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
