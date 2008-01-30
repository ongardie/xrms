<?php

// set thread_id to activity_id if it's not set already.
if(!$thread_id) {
    $thread_id = $activity_id;
}

//check for uncompleted activity with equal start and end times
if (!strlen($completed_at)) {
    //check if ends_at is in the past
    if (strtotime($ends_at)<time()) {
        //check if start and end time are equal
        if ($ends_at=$scheduled_at) {
           //clear $ends_at
           $ends_at='';
           // hopefully the user will pick an ends_at time in the UI
           // otherwise, activities/edit-2.php will set the ends_at to the current time
        }
    }
}

//mark selected probability
if($on_what_table == 'opportunities') {
    $sql = "select o.probability
        from opportunities as o, activities as a
        where activity_id = '" . $activity_id . "'
        and a.on_what_id=o.opportunity_id";

    $rst = $con->execute($sql);

    if($rst) {
        $probability = array();
        $probability[$rst->fields['probability']] = ' selected';
    }
}

//display technical details
$show_blank = (get_system_parameter($con, 'Allow Unassigned Activities') == "y" ? true : false);
$activity_id_text = _("Activity ID:") . ' ' . $activity_id;

if (get_system_parameter($con, 'Display Item Technical Details') == 'y') {
    $history_text = '<tr> <td class=widget_content colspan=2>';

    //display user info for who entered the activity
    $history_text .= _("ID") . ' ' . $activity_id . ' ' .
                     _("entered by") . ' ' . $entered_by_username . ' ' .
                     _("at") . ' ' . $entered_at . '. ';

    $history_text .= _("Last modified by") . ' ' . $last_modified_username . ' ' .
                         _("at") . ' ' . $last_modified_at . '.';
    $history_text .= '</td> </tr>';
} else {
    $history_text = '';
}

//get user menus
$user_menu = get_user_menu($con, $user_id, $show_blank, 'user_id', false);
$user_email_menu = get_user_email_menu($con, $email_to, true, 'email_to', false);

//get activity type menu
$sql = "SELECT activity_type_pretty_name, activity_type_id
        FROM activity_types
        WHERE activity_type_record_status = 'a'
        ORDER BY sort_order, activity_type_pretty_name";
$rst = $con->execute($sql);
if (!$rst) db_error_handler($con, $sql);
$activity_type_menu = $rst->getmenu2('activity_type_id', $activity_type_id, false);
$rst->close();

//get priority type menu
$sql = "SELECT case_priority_pretty_name,case_priority_id
        FROM case_priorities
        WHERE case_priority_record_status = 'a'
        ORDER BY case_priority_pretty_name";
$rst = $con->execute($sql);
if (!$rst) db_error_handler($con, $sql);
$activity_priority_menu = $rst->getmenu2('activity_priority_id', $activity_priority_id,true);
$rst->close();

//get activity type menu
$sql = "SELECT resolution_pretty_name, activity_resolution_type_id
        FROM activity_resolution_types
        WHERE resolution_type_record_status = 'a'
        ORDER BY sort_order, resolution_pretty_name";
$rst = $con->execute($sql);
if (!$rst) db_error_handler($con, $sql);
$activity_resolution_type_menu = $rst->getmenu2('activity_resolution_type_id', $activity_resolution_type_id, true, false, 0, 'id=activity_resolution_type_id');
$rst->close();

//get contact name menu
if ($company_id) {
    $sql = "SELECT " . $con->Concat("first_names","' '","last_name") . " AS contact_name, contact_id
            FROM contacts
            WHERE company_id = '" . $company_id . "'";
        if ($company_id == 1) { //limit the list if this is on the Unknown Company
            $sql .= "AND contact_id = '" . $contact_id . "'";
        }
        $sql .= "AND contact_record_status = 'a'
        ORDER BY last_name, first_names";
    $rst = $con->execute($sql);
    if ($rst) {
        $contact_menu = $rst->getmenu2('contact_id', $contact_id, true);
        $rst->close();
    } else {
        db_error_handler ($con, $sql);
    }
}

//get activity location menu
$location_menu=get_company_address_select($con, $company_id, $activity_address_id);

//add audit item
add_audit_item($con, $session_user_id, 'viewed', 'activities', $activity_id, 3);

//atteched_to processing
if ($activity_on_what_table == 'opportunities') {
    $attached_to_link = "<a href='$http_site_root" . table_one_url($activity_on_what_table, $activity_on_what_id) . "'>";
    $sql = "select opportunity_title as attached_to_name from opportunities where opportunity_id = $activity_on_what_id";
} elseif ($activity_on_what_table == 'cases') {
    $attached_to_link = "<a href='$http_site_root" . table_one_url($activity_on_what_table, $activity_on_what_id) . "'>";
    $sql = "select case_title as attached_to_name from cases where case_id = $activity_on_what_id";
} elseif ($activity_on_what_table == 'companies') {
    $attached_to_link = "<a href='$http_site_root" . table_one_url($activity_on_what_table, $activity_on_what_id) . "'>";
    $sql = "select company_name as attached_to_name from companies WHERE company_id = '" . $activity_on_what_id . "'";
} elseif ($activity_on_what_table == 'contacts') {
    $attached_to_link = "<a href='$http_site_root" . table_one_url($activity_on_what_table, $activity_on_what_id) . "'>";
    $sql = "select CONCAT(first_names, ' ', last_name) as attached_to_name from contacts WHERE contact_id = '" . $activity_on_what_id . "'";
} elseif ($activity_on_what_table == 'campaigns') {
    $attached_to_link = "<a href='$http_site_root" . table_one_url($activity_on_what_table, $activity_on_what_id) . "'>";
    $sql = "select campaign_title as attached_to_name from campaigns WHERE campaign_id = '" . $activity_on_what_id . "'";
} elseif ($activity_on_what_table == "nono") {
    $attached_to_link = "<a href='$http_site_root" . table_one_url($activity_on_what_table, $activity_on_what_id) . "'>";
    $singular=make_singular($on_what_table);
    $name_field=$con->Concat(implode(", ' ' , ", table_name($on_what_table)));
    $on_what_field=$singular.'_id';
    $sql = "select $name_field as attached_to_name from $activity_on_what_table WHERE $on_what_field = '" . $activity_on_what_id . "'";
} else {
    $attached_to_link = _("None");
    $sql = "select * from companies where 1 = 2";
}
$rst = $con->execute($sql);

if ($rst) {
    $attached_to_name = $rst->fields['attached_to_name'];
    $attached_to_link .= $attached_to_name . "</a>";
    $rst->close();
} else {
    db_error_handler($con,$sql);
}

//add opportunities/case/campaign combo box
$is_linked = true;
$table_name = make_singular($activity_on_what_table);
if (!$table_name) $is_linked = false;

//Check if activity is linked to something, then generate a SQL statement
$table_status_id = '';
if ($is_linked) {
    switch ($table_name) {
        case 'case':
            $type_field="{$table_name}_type_id";
            $type_field_limit=",{$activity_on_what_table}.$type_field";
        break;
        case 'opportunity':
            $type_field="{$table_name}_type_id";
            $type_field_limit=",{$activity_on_what_table}.$type_field";
        break;
        default:
            $type_field=false;
            $type_field_limit='';
       break;
    }
    $sql = "select ".$table_name."_id,
            ".$table_name."_statuses.".$table_name."_status_pretty_name,
            ".$activity_on_what_table.".".$table_name."_id,
            ".$activity_on_what_table.".".$table_name."_status_id,
            ".$table_name."_statuses.".$table_name."_status_id
            $type_field_limit
            from ".$table_name."_statuses, ".$activity_on_what_table."
            where ".$activity_on_what_table.".".$table_name."_id=$activity_on_what_id
            and ".$activity_on_what_table.".".$table_name."_status_id=".$table_name."_statuses.".$table_name."_status_id";
    $rst = $con->execute($sql);

    //If not empty, get pretty name and id
    if ($rst) {
        $table_status = $rst->fields[$table_name.'_status_pretty_name'];
        $table_status_id = $rst->fields[$table_name.'_status_id'];
        if (!empty($type_field)) {
            //if we have a type, use it to limit the statuses
            $type_limit=" AND $type_field=" . $rst->fields[$type_field];
        }
        $rst->close();
    }

    //generate SQL for status combo box
    $sql = "select ".$table_name."_status_pretty_name,
            ".$table_name."_status_id
            from ".$table_name."_statuses
            where ".$table_name."_status_record_status='a'
            $type_limit
            order by sort_order";
    $rst = $con->execute($sql);

    //create combo box using ADODB getmenu2 function
    if ($rst) {
        $table_menu = $rst->getmenu2('table_status_id', $table_status_id, false);
        $rst->close();
    }
}

$transfer_link = 'javascript:document.activity_data.return_url.value=\'' . $http_site_root . "/activities/activity-reconnect.php?activity_id="  . $activity_id . "&return_url=".urlencode($http_site_root . "/activities/one.php?activity_id=" . $activity_id)."".'\'';

$regarding_sidebar = "               
        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=2>
                    " . _("Regarding") . "
                </td>
            </tr>
            <tr>
                <td class=widget_label_right>
                    " . ucwords($table_name) . "
                </td>
                <td class=widget_content>
                    " . $attached_to_link . "</a>
                </td>
            </tr>
            <tr>
                <td class=widget_label_right>
                    " . ucwords($table_name) . " " . _("Status") . "
                </td>
                <td class=widget_content>
                    <input type=hidden name=change_attachment>
                    " . $table_status . "
                </td>
            </tr>
            <!--
            <tr>
                <td class=widget_label_right>
                    " . _("Notes") . "
                </td>
                <td class=widget_content>
                    <input type=textarea name=ENTITY_profile rows=5 cols=20>
                    " . $notes . "
                </td>
            </tr>
            -->
            <tr>
                <td class=widget_content colspan=2>
                    <form action=\"" . $http_site_root . "/activities/activity-reconnect.php\" method=\"get\">
                    <input type=hidden name=activity_id value=\"" . $activity_id . "\">
                    " . render_edit_button(_("Transfer Activity"),'submit', $transfer_link,'edit_view', false,'activities',$activity_id) . "
                    </form>
                </td>
            </tr>      
        </table>
";


$tools_sidebar = '
            <form action="#" method="post" class="print" name="activity_data">

            <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=2>
                    <?php  echo _("Activity Tools"); ?>
                </td>
            </tr> ' ;

if (0) {
    $tools_sidebar .= '
            <tr>
                <td class=widget_label_right>
                    <?php echo _("Email This To"); ?>
                </td>
                <td class=widget_content_form_element>
                    ' . $user_email_menu
                      . render_edit_button(_("Save and Send"),'submit',false,'save', false,'activities',$activity_id) . '
                </td>
            </tr>
            <tr>
            <td class=widget_header ></td>
            <td class=widget_content_right>
            ';
}

if($save_and_next) {
    $tools_sidebar .= '<input class=button type=submit name="saveandnext" value="'._("Save and Next").'"';
    $save_and_next="&save_and_next=true";
}

$tools_sidebar .= render_create_button(_("Schedule Followup"),'submit',false,'followup');

if($activity_recurrence_id) {
    $tools_sidebar .= render_edit_button(_("Edit Recurrence"),'submit',false,'recurrence');
} else {
    $tools_sidebar .= render_edit_button(_("Create Recurrence"),'submit',false,'recurrence');
}

$tools_sidebar .= render_delete_button(_("Delete"),'button',"javascript:location.href='delete.php?activity_id=$activity_id$save_and_next&return_url=".urlencode($return_url)."'", false, false, 'activities',$activity_id);

$tools_sidebar .= '   
            </td>
            </tr>
            
    </table>
    </form>
    ';


//something with opporunities
$opportunity_description = '';

if($on_what_table == 'opportunities') {
    $sql = "select opportunity_description from opportunities where opportunity_id='$on_what_id'";
    $rst = $con->execute($sql);
    $opportunity_description = $rst->fields['opportunity_description'];
    $rst->close();
}

// make sure $activity_content_bottom is defined
if ( !isset($activity_content_bottom) ) {
  $activity_content_bottom = '';
}


$activity_details_sidebar = '
            <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=2>
                    ' . _("Activity Details") . '
                </td>
            </tr>
            <tr>
                <td class=widget_label_right>
                    ' . _("Activity Type") . '
                </td>
                <td class=widget_content_form_element>
                    ' .$activity_type_menu . '
                </td>
            </tr>
            <tr>
                <td class=widget_label_right>
                    ' . _("Activity Priority") . '
                </td>
                <td class=widget_content_form_element>
                    ' . $activity_priority_menu . '
                </td>
            </tr>';
            
if($on_what_table == 'opportunities') {
    $activity_details_sidebar .= '
           <tr>
                <td class=widget_label_right>
                    <?php echo _("Probability") . "&nbsp;" . _("(%)"); ?>
                </td>
                <td class=widget_content_form_element>
                    <select name=probability>
                        <?php
                        for($i = 0; $i <= 100; $i += 10) {
                            echo "\n\t\t<option value=\"$i\" ".$probability[$i]."> $i %";
                        };
                        ?>
                </select>
                </td>
            </tr>
';
}

if($on_what_table == 'opportunities') {
    $activity_details_sidebar .= "
            <tr>
                <td class=widget_label_right>
                    " . _("Opportunity Notes") . "
                </td>
                <td class=widget_content_form_element>
                    <textarea rows=10 cols=70 name=opportunity_description>
                    " . htmlspecialchars(trim($opportunity_description)) . "
                    </textarea><br>
                    <input class=button value=\"" . _("Insert Log") . "\" type=button
                        onclick=\"var new_message = prompt('" . addslashes(_("Enter Note")) . "', '');
                            document.forms[0].opportunity_description.value = logTime() + ' " . _("By") . " " . $_SESSION['username'] . ": ' + new_message + '\n' + document.forms[0].opportunity_description.value;
                            document.forms[0].return_url.value = '" . current_page() . "&fill_user';
                            document.forms[0].submit();\">
                        " . do_hook('opportunity_notes_buttons') . "
                </td>
            </tr>
            ";
}

if ($location_menu) {
    $activity_details_sidebar .= '
            <tr>
                <td class=widget_label_right>
                    ' . _("Location") . '
                </td>
                <td class=widget_content_form_element>
                    ' . $location_menu . '
                </td>
            </tr>
';
}

 $activity_details_sidebar .= '
            <tr>
                <td class=widget_label_right>
                    ' .  _("Scheduled Start") . '
                </td>
                <td class=widget_content_form_element>
                    ' .  jscalendar_includes() . '
                    <input type=text ID="f_date_c" name=scheduled_at value="' . $scheduled_at . '">
                    <img ID="f_trigger_c" style="CURSOR: hand" border=0 src="../img/cal.gif">
                </td>
            </tr>
            <tr>
                <td class=widget_label_right>
                    ' . _("Scheduled End") . '
                </td>
                <td class=widget_content_form_element>
                    <input type=text ID="f_date_d" name=ends_at value="' . $ends_at . '">
                    <img ID="f_trigger_d" style="CURSOR: hand" border=0 src="../img/cal.gif">
                </td>
            </tr>
            <tr>
                <td class=widget_label_right>
                    ' . _("Owner") . '
                </td>
                <td class=widget_content_form_element>
                    ' . $user_menu . '
                </td>
            </tr>
            <tr>
                <td class=widget_label_right>
                    ' . _("Entered By") . '
                </td>
                <td class=widget_content_form_element>
                    ' . $entered_by_username.' '._("on").' '.$entered_at . '
                </td>
            </tr>';
            
if ($last_modified_by) {
    $activity_details_sidebar .= '
            <tr>
                <td class=widget_label_right>
                    <?php echo _("Last Modified By"); ?>
                </td>
                <td class=widget_content_form_element>
                    ' . $last_modified_by_username.' '._("on").' '.$last_modified_at . '
                </td>
            </tr>
            ';
}
           
$activity_details_sidebar .= "
            <tr>
                <td class=widget_label_right>
                    " . _("Completed?") . "
                </td>
                <td class=widget_content_form_element>
                    <input type=checkbox id=activity_completed name=activity_status value='on'";

if ($activity_status == 'c') $activity_details_sidebar .= ' "checked"';
$activity_details_sidebar .= ">";

if ($completed_by) $activity_details_sidebar .=  _("by").' '.$completed_by_username;
if ($completed_at AND ($completed_at!='0000-00-00 00:00:00')) $activity_details_sidebar .= ' '._("on").' '. $completed_at;

$activity_details_sidebar .= "

                </td>
            </tr>
            <tr>
                <td class=widget_content_form_element colspan=2>
                " . render_edit_button(_("Save Changes"),'submit',false,'save', false,'activities',$activity_id) . "
                </td>
            </tr>
        </table>
    </form>
";


$activity_resolution = "
            <tr id='resolution_type' >
                <td class=widget_label_right>
                    " . _("Resolution Type") . "
                </td>
                <td class=widget_content_form_element>
                    " . $activity_resolution_type_menu . "
                </td>
            </tr>
            <!--  <?php /* ?><tr id='resolution_reason' >
                <td class=widget_label_right>
                    " . _("Resolution Description") . "
                </td>
                <td class=widget_content_form_element>
";

if (get_user_preference($con, $user_id, "html_activity_notes") == 'a') {
$activity_resolution .= '
                          <input type="hidden" id="FCKeditor2" name="FCKeditor2" value="<?php  echo htmlspecialchars(trim($resolution_description)); ?>"
                                style="display:none" />
                               <input type="hidden" id="FCKeditor2___Config" value="" style="display:none" />
                               <iframe id="FCKeditor2___Frame" src="/xrms/include/fckeditor/editor/fckeditor.html?InstanceName=FCKeditor2&amp;Toolbar=Default"
                              width="100%" height="200" frameborder="1" scrolling="no"></iframe>
                        <?php }  else {?>
                    <textarea rows=10 cols=70 id=resolution_description name=resolution_description><?php echo htmlspecialchars(trim($resolution_description)); ?></textarea>
                    <?php } ?> 
                </td>
            </tr>
';
}               
                


// related activities
$ra_extra_where = array();

if ($on_what_table && $on_what_id) {
    // changed to correctly show only related activities belonging to the same company
    if ($company_id) {
        $ra_extra_where[] = "(a.on_what_table = '$on_what_table' AND a.on_what_id = $on_what_id AND a.company_id = $company_id AND a.activity_id <> $activity_id)";
    } else {
        $ra_extra_where[] = "(a.on_what_table = '$on_what_table' AND a.on_what_id = $on_what_id AND a.activity_id <> $activity_id)";
    }
    //don't do anything if this activity is on companies or contacts
    if (($on_what_table != 'companies') && ($on_what_table != 'contacts')) {
        $ra_extra_where[] = "(a.on_what_table = '$on_what_table' AND a.on_what_id = $on_what_id)";
    }
}

if($thread_id) {
    $ra_extra_where[] = "a.thread_id = $thread_id ";
}

if(count($ra_extra_where)) {
    $extra_where = "AND " . join(" OR ", $ra_extra_where);
} else {
    $extra_where = "AND 1 = 2";
}


// Activities Widget
$default_columns = array('title', 'owner', 'type', 'contact', 'activity_about', 'scheduled', 'due');
$related_activities_widget = GetActivitiesWidget($con, $search_terms, 'OneActivityForm', _('Related Activities'), $session_user_id, $return_url, $extra_where, null, $default_columns);


//call the activity_content_top hook
$param=NULL;
$activity_content_top = do_hook_function('activity_content_top',$param);

//call the activity_content_bottom hook
$param=NULL;
$activity_content_bottom = do_hook_function('activity_content_bottom',$param);

//call activity inline hook
$activity_inline_rows    = do_hook_function('activity_inline_edit',$activity_rst);


/*********************************/
/*** Include the sidebar boxes ***/

require_once('participant_sidebar.php');

$related_block='';
require_once('attachment_sidebar.php');

// contact sidebar box
if ($contact_id) {
    require_once ('../contacts/sidebar.php');
} else {
  $contact_block = '';
}

 if (!$contact_block) {
$contact_block = "
        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header>
                    " . _("Contact Information") . "
                </td>
            </tr>
            <tr>
                <td class=widget_label_left>
                    " . _("None") . "
                </td>
            </tr>

            <tr>
                <td class=widget_label_left>
                    <form action=\"" . $http_site_root . "/activities/activity-reconnect.php\" method=\"POST\">
                    <input type=hidden name=activity_id value=\"" . $activity_id . "\">
                    <input type=hidden name=on_what_entity value=\"contact\">
                    " . render_edit_button(_("Add"),'submit', $transfer_link,'edit_view', false,'activities',$activity_id) . "
                    </form>
                </td>
            </tr>   
        </table>
        ";
        }

//company sidebar box
if ($company_id) {
    require_once ('../companies/sidebar.php');
}

//include the contacts-companies sidebar
$relationships = array('contacts' => $contact_id, 'companies' => $company_id, 'activities' => $activity_id);
if(!empty($on_what_table)) {
    $relationships[$on_what_table] = $on_what_id;
}
require("../relationships/sidebar.php");

//include the files sidebar
$ori_on_what_id=$on_what_id;
$ori_on_what_table=$on_what_table;
$ori_return_url=$return_url;
$on_what_table='activities';
$on_what_id=$activity_id;
$return_url=current_page();
require_once( '../files/sidebar.php');
$return_url=$ori_return_url;
$on_what_table=$ori_on_what_table;
$on_what_id=$ori_on_what_id;

//Add optional tables
//sending null parameter, expecting return instead of change to passed in reference
$param=NULL;
$sidebar_top_plugin_rows = do_hook_function('activity_sidebar_top',$param);
$param=NULL;
$sidebar_bottom_plugin_rows = do_hook_function('activity_sidebar_bottom',$param);

/** End of the sidebar includes **/
/*********************************/

if ($activity_type_id == 4) {
    include("templates/cft-email.php");
} else {
    include("templates/cft-default.php");
}
?>