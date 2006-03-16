<?php
/**
 * Application for adding and deleting contacts from positions in activities
 *
 * @author Aaron van Meerten
 */


require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'utils-activities.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();

GetGlobalVar($return_url,'return_url');
GetGlobalVar($msg, 'msg');
GetGlobalVar($activity_id, 'activity_id');
GetGlobalVar($activity_type_id,'activity_type_id');
getGlobalVar($participant_position_id, 'participant_position_id');
getGlobalVar($contact_id,'contact_id');
GetGlobalVar($activity_participant_action,'activity_participant_action');
getGlobalVar($btCancel, 'btCancel');

if ($btCancel==_("Cancel")) {
    //Cancelling, go back to return url immediately
    Header("Location: {$http_site_root}{$return_url}");
    exit;
}
if (!$activity_participant_action) $activity_participant_action='newParticipant';

$con = get_xrms_dbconnection();

if ($activity_id) {
    $activity_info=get_activity($con, array('activity_id'=>$activity_id));
    if($activity_info){
        $activity=current($activity_info);
        $activity_type_id=$activity['activity_type_id'];
        $activity_name=$activity['activity_title'];
    }
} else if ($activity_participant_action!='deleteActivityParticipant') {
    $msg=urlencode(_("Failed to find activity"));
    Header("Location:some.php?msg=$msg");
    exit;
}
switch($activity_participant_action) {
    /* Default case, first one run, beginning of the process.  Get search term and select position */
    default:
    case 'newParticipant':
        //get all positions available for this activity type
        $positions=get_activity_participant_positions($con, false, $activity_type_id);
        if (!$positions) $position_array[1]=_("Participant");
        else {
            foreach ($positions as $position_id=>$position_data) {
                $position_array[$position_id]=_($position_data['participant_position_name']);
            }
         }
        $position_menu=create_select_from_array($position_array, 'participant_position_id', $participant_position_id); //, '', $show_blank_first=true) 
        $header_text=_("Activity Participant");
        $body_content=<<<TILLEND
        <form method=POST action=new_activity_participant.php>
        <input type=hidden name=activity_participant_action value=selectContactParticipant>
        <input type=hidden name=activity_id value=$activity_id>
        <input type=hidden name=return_url value="$return_url">
        <table class=widget><tr><td class=widget_label>
TILLEND;
        $body_content.= "\n"._("Contact Search")."</td><td class=widget_label>"._("Position")."</td><td class=widget_label>"._("Activity")."</td></tr>";
        $body_content .= "<tr><td class=widget_content_form_element><input type=text name=search_text></td>";
        $body_content .= "<td class=widget_content_form_element>$position_menu</td>";
        $body_content .= "<td class=widget_content>$activity_name</td></tr>";
        $body_content .= "<tr><td colspan=3 class=widget_content_form_element><input type=submit name=btAddContact value=\""._("Search for contact") . "\" class=button><input type=submit name=btCancel value=\""._("Cancel") . "\" class=button></td></tr></table>";
    break;
    
    /* This case gets the list of contacts matching the results searched for on the previous, and displays the selected participant position */    
    case 'selectContactParticipant':
        getGlobalVar($search_text, 'search_text');
        $positions=get_activity_participant_positions($con, false, false, $participant_position_id);
        $position=current($positions);
        $position_name=_($position['participant_position_name']);
        if ($search_text) { $search_text=$con->qstr("%$search_text%",get_magic_quotes_gpc()); }
        $name_concat = $con->Concat(implode(", ' ', ", table_name('contacts'))) . ' as name';
        $search_name = 'last_name';
        $sql = "select $name_concat, contact_id FROM contacts WHERE contact_record_status=".$con->qstr('a',get_magic_quotes_gpc());
        if ($search_text) {
            $sql.= " AND ((first_names LIKE $search_text) OR ( last_name LIKE $search_text)) ";
        }
        $sql.= " ORDER BY $search_name";
        
        //search for contacts based on first or last name containing the search string
        $contact_rst=$con->execute($sql);
        if (!$contact_rst) { db_error_handler($con, $sql); return false; }
        
        //search returned no results, pass back to last page with msg recommend less letters
        if ($contact_rst->EOF) {
            $msg=urlencode(_("Failed to find any search results, perhaps try again with a less restrictive search."));
            Header("Location: new_activity_participant.php?msg=$msg&activity_id=$activity_id&participant_position_id=$participant_position_id&activity_participant_action=newParticipant&return_url=".urlencode($return_url));
            exit;
        }
        
        //get select list of contacts
        $contact_list=$contact_rst->getmenu2('contact_id','',false);
        
        $header_text=_("Activity Participant Contact");
        $body_content=<<<TILLEND
        <form method=POST action=new_activity_participant.php>
        <input type=hidden name=activity_participant_action value=addActivityParticipant>
        <input type=hidden name=activity_id value=$activity_id>
        <input type=hidden name=return_url value="$return_url">
        <input type=hidden name=participant_position_id value=$participant_position_id>
        <table class=widget><tr><td class=widget_label>
TILLEND;
        $body_content.= "\n"._("Contact Search")."</td><td class=widget_label>"._("Position")."</td><td class=widget_label>"._("Activity")."</td></tr>";
        
        
        $body_content .= "<tr><td class=widget_content_form_element>$contact_list</td>";
        $body_content .= "<td class=widget_content>$position_name</td>";
        $body_content .= "<td class=widget_content>$activity_name</td></tr>";
        $body_content .= "<tr><td colspan=3 class=widget_content_form_element><input type=submit name=btAddContact value=\""._("Add contact to activity") . "\" class=button><input type=submit name=btCancel value=\""._("Cancel") . "\" class=button></td></tr></table>";
                
    break;
    
    /* This case is the function call and return for adding an activity participant.  This could be called directly with a return_url set for automated participant addition from anywhere.
     @todo string return_url of msg string before returning with an error instead of automatically returning to activities/one.php
     */
    case 'addActivityParticipant':
        $ret=add_activity_participant($con, $activity_id, $contact_id, $participant_position_id);
        if (!$ret) {
            $msg=urlencode(_("Failed to add contact to activity."));
        } else {
            $msg=urlencode(_("Added contact to activity."));
        }
        if (strpos($return_url,"?")===false) $return_url.="?";
        else $return_url.="&";
        $return_url.="msg=$msg";
        Header("Location: {$http_site_root}{$return_url}");
        exit;
    break;
    
    /* This case handles the automatic marking of an activity participant as deleted. */
    case 'deleteActivityParticipant':
        getGlobalVar($activity_participant_id, 'activity_participant_id');
        $ret=delete_activity_participant($con, $activity_participant_id);
        if (!$ret) {
            $msg=urlencode(_("Failed to remove contact from activity."));
        } else {
            $msg=urlencode(_("Removed contact from activity."));
        }
        if (strpos($return_url,"?")===false) $return_url.="?";
        else $return_url.="&";
        $return_url.="msg=$msg";
        Header("Location: {$http_site_root}{$return_url}");
        exit;
    break;
}

/* This is the main output of these pages.  This could eventually be made into a template which is included */
start_page($header_text, true, $msg);
        echo '<div id="Main">';
        echo <<<TILLEND
	   <div id="Content">
            <table class=widget cellspacing=1>
                <tr>
                    <td class=widget_header>
                        $header_text
                    </td>
                </tr>
                <tr><td class=widget_content>
                    $body_content
                 </td></tr>                 
TILLEND;
end_page();

/*
 * $Log: new_activity_participant.php,v $
 * Revision 1.5  2006/03/16 06:31:35  ongardie
 * - Avoids a Warning when get_activity() returns false.
 *
 * Revision 1.4  2006/01/02 21:23:18  vanmer
 * - changed to use centralized database connection function
 *
 * Revision 1.3  2005/10/08 21:06:59  vanmer
 * - altered to use return_url for return even if failure occurs
 * - added msg when successfully adding a contact as a participant to an activity
 *
 * Revision 1.2  2005/06/21 15:29:09  vanmer
 * - caused strings that needed translation to be translated, including participant positions
 *
 * Revision 1.1  2005/04/15 07:35:14  vanmer
 * -Initial Revision of an application for adding and removing contacts from activities
 *
*/
?>
