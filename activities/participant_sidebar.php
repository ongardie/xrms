<?php

if ( !defined('IN_XRMS') )
{
  die('Hacking attempt');
  exit;
}

/**
 * Activity Participant Information Sidebar
 *
 * Include this file anywhere you want to show a summary of the activity participants
 *
 * @param integer $activity_id The activity_id should be set before including this file
 *
 * @author Aaron van Meerten
 *
 * $Id: participant_sidebar.php,v 1.3 2005/04/22 22:05:53 ycreddy Exp $
 */
require_once($include_directory.'utils-activities.php');
// add participant information block on sidebar
if (!$activity_id) { $participant_block=''; return false; }
$participant_return_url="/activities/one.php?activity_id=$activity_id";
$participant_block = "<form action=new_activity_participant.php method=POST><input type=hidden name=activity_id value=$activity_id><input type=hidden name=return_url value=\"$participant_return_url\">";
$participant_block .= '<table class=widget cellspacing=1 width="100%">
    <tr>
        <td class=widget_header colspan=5>Activity Participants</td>
    </tr>'."\n";

$participants=get_activity_participants($con, $activity_id);

if (!$participants) {
    $colspan=1;
    $participant_block.='<tr><td class=wiget_content>'._("No Participants") . '</td></tr>';
} else {
    $colspan=3;
    $participant_block.='<tr><td class=widget_label>'._("Name").'</td><td class=widget_label>'._("Position").'</td><td class=widget_label>'._("Action").'</td></tr>';
    foreach ($participants as $participant_info) {
        $remove_link="new_activity_participant.php?activity_participant_action=deleteActivityParticipant&activity_participant_id={$participant_info['activity_participant_id']}&return_url=".urlencode($participant_return_url);
        $participant_block.="<tr><td class=widget_content>{$participant_info['contact_name']}</td><td>{$participant_info['participant_position_name']}</td><td><a href=\"$remove_link\">Remove</a></td></tr>";    
    }
}
$participant_block.="<tr><td colspan=$colspan class=widget_content_form_element><input type=submit value=\""._("Add New Participant")."\" class=button name=btAddParticipant></td></tr>";
$participant_block .= "\n</table></form>";

/**
 * $Log: participant_sidebar.php,v $
 * Revision 1.3  2005/04/22 22:05:53  ycreddy
 * Added the missing .php extension for the Remove Participant Link
 *
 * Revision 1.2  2005/04/18 23:34:12  maulani
 * - participant sidebar include was stomping on $return_url variable.  Changed
 *   variable name to resolve conflict in activities/one.php
 *
 * Revision 1.1  2005/04/15 16:55:07  vanmer
 * -Initial revision of the sidebar for participant lists on an activity
 *
**/
?>
