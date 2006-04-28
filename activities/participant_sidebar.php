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
 * $Id: participant_sidebar.php,v 1.12 2006/04/28 16:48:51 braverock Exp $
 */
require_once($include_directory.'utils-activities.php');


// add participant information block on sidebar
if (!$activity_id) { $participant_block=''; return false; }
$participant_return_url="/activities/one.php?activity_id=$activity_id";


$participant_block='
<script language="JavaScript" type="text/javascript">

function addParticipant() {
      document.forms[0].add_participant.value=true;
      document.forms[0].submit();
}

function removeParticipant(part_id) {
      document.forms[0].remove_participant.value=part_id;
      document.forms[0].submit();
}

function mailmergeParticipants(contacts) {
      document.forms[0].mailmerge_participant.value=contacts;
      document.forms[0].submit();
}
</script>';


$participant_block .= '<table class=widget cellspacing=1 width="100%">
    <tr>
        <td class=widget_header colspan=5>'._("Activity Participants").'</td>
    </tr>'."\n";

$participants=get_activity_participants($con, $activity_id);

if (!$participants) {
    $colspan=1;
    $participant_block.="\n".'<tr><td class=wiget_content>'._("No Participants") . '</td></tr>';
} else {
    $colspan=3;
    $participant_block.="\n".'<tr><td class=widget_label>'._("Name").'</td><td class=widget_label>'._("Position").'</td><td class=widget_label>'._("Action").'</td></tr>';
    $contact_ids=array();
    foreach ($participants as $participant_info) {
        $contact_ids[]=$participant_info['contact_id'];
        $remove_link="javascript: removeParticipant({$participant_info['activity_participant_id']});";
        $participant_block.="\n<tr><td class=widget_content><a href=\"$http_site_root/contacts/one.php?contact_id={$participant_info['contact_id']}\">{$participant_info['contact_name']}</a>
        </td>
        <td>"._($participant_info['participant_position_name'])."</td>
        <td><a href=\"$remove_link\">"._("Remove").'</a></td>
        </tr>';
    }
}
if (count($contact_ids)>0) {
    $contacts=implode(",",$contact_ids);
} else {
    $contacts=false;
}

$participant_block.="\n".'<tr>
    <td colspan='.$colspan.' class=widget_content_form_element>
    <input type=button onclick="addParticipant()" value="'
    ._("Add New Participant")
    .'" class=button name=btAddParticipant>';

if ($contacts) { $participant_block .= "<input type=button class=button onclick=\"mailmergeParticipants('$contacts');\" value=\""._("Mail Merge") ."\">"; }
$participant_block.= "\n\t</td>\n</tr>";
$participant_block .= "\n</table></form>";

/**
 * $Log: participant_sidebar.php,v $
 * Revision 1.12  2006/04/28 16:48:51  braverock
 * - fix colspan
 *
 * Revision 1.11  2006/04/14 14:22:22  braverock
 * - unquote javascript value attribute
 *
 * Revision 1.10  2006/04/13 21:18:59  braverock
 * - fix unlocalized string
 *
 * Revision 1.9  2006/04/13 21:10:23  braverock
 * - fix unlocalized string
 *
 * Revision 1.8  2005/10/08 21:09:51  vanmer
 * - changed participant sidebar to use javascript instead of directly changing the location in the browser
 * - sets form variables on activities/one.php and submits, so changes can be saved
 *
 * Revision 1.7  2005/07/15 19:02:37  vanmer
 * - added link to each contact on participant name in participant sidebar
 *
 * Revision 1.6  2005/06/21 15:31:55  vanmer
 * - added needed translations for displaying participants on activities
 *
 * Revision 1.5  2005/05/25 05:49:24  vanmer
 * - added mail merge button to email all contacts on an activity
 *
 * Revision 1.4  2005/05/19 13:20:43  maulani
 * - Remove trailing whitespace
 *
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