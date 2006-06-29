<?php
/**
 * Edit the details for a single Activity
 *
 * $Id: one.php,v 1.139 2006/06/29 15:53:46 braverock Exp $
 *
 * @todo Fix fields to use CSS instead of absolute positioning
 */

//include required files
require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-activities.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');
require_once($include_directory . 'confgoto.php');
require_once('../activities/activities-widget.php');

getGlobalVar($activity_id, 'activity_id');
getGlobalVar($msg, 'msg');
getGlobalVar($return_url, 'return_url');
getGlobalVar($print_view, 'print_view');

if (!$return_url) $return_url='/activities/some.php';



$on_what_id=$activity_id;
$session_user_id = session_check();

if(!isset($activity_id)){
    header("Location: " . $http_site_root . $return_url.'?msg='.urlencode(_("Error: No Activity ID Specified")));
};


$save_and_next = isset($_GET['save_and_next']) ? true : false;

$con = get_xrms_dbconnection();
//$con->debug = 1;

update_recent_items($con, $session_user_id, "activities", $activity_id);
update_daylight_savings($con);


$activity_rst = get_activity($con,$activity_id,$show_deleted=false, $return_rst=true);

if ($activity_rst) {
    //set recurrance id
    $recurrance_sql = "SELECT activity_recurrence_id FROM activities_recurrence where activity_id=$activity_id";
    $recurrence_rst=$con->execute($recurrance_sql);
    if (!$recurrence_rst) { db_error_handler($con, $recurrance_sql); }
    if ($recurrence_rst->fields['activity_recurrence_id']) {
        $activity_rst->fields['activity_recurrence_id'] = $recurrence_rst->fields['activity_recurrence_id'];
    } //end recurrance processing


    // Instantiating variables for each activity field, so that  fields
    // are accessible to plugin code without an extra read from database.
    foreach ($activity_rst->fields as $activity_field => $activity_field_value ) {
        $$activity_field = $activity_field_value;
    }

    $entered_at = date('Y-m-d H:i:s', strtotime($activity_rst->fields['entered_at']));
    $last_modified_at = date('Y-m-d H:i:s', strtotime($activity_rst->fields['last_modified_at']));
    $scheduled_at = date('Y-m-d H:i:s', strtotime($activity_rst->fields['scheduled_at']));
    $ends_at = date('Y-m-d H:i:s', strtotime($activity_rst->fields['ends_at']));
    $local_time = calculate_time_zone_time($con, $activity_rst->fields['daylight_savings_id'], $rst->fields['gmt_offset']);

    $activity_rst->close();
} else {
    // add to $msg
}

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
} // end time rationalization on uncompleted activities

if($on_what_table == 'opportunities') {
    $sql = "select o.probability
        from opportunities as o, activities as a
        where activity_id = $activity_id
        and a.on_what_id=o.opportunity_id";

    $rst = $con->execute($sql);

    if($rst) {
        $probability = array();
        $probability[$rst->fields['probability']] = ' selected';
    }
}

// since the activity can be attached to many things -- a company, contact, opportunity, or case -- we need to figure
// out a way to display the link... this is probably less than perfect, but it works ok
if ($on_what_table == 'opportunities') {
    $attached_to_link = "<a href='$http_site_root/opportunities/one.php?opportunity_id=$on_what_id'>";
    $sql = "select opportunity_title as attached_to_name from opportunities where opportunity_id = $on_what_id";
} elseif ($on_what_table == 'cases') {
    $attached_to_link = "<a href='$http_site_root/cases/one.php?case_id=$on_what_id'>";
    $sql = "select case_title as attached_to_name from cases where case_id = $on_what_id";
} elseif ($on_what_table) {
    $attached_to_link = "<a href='$http_site_root" . table_one_url($on_what_table, $on_what_id) . "'>";
    $singular=make_singular($on_what_table);
    $name_field=$con->Concat(implode(", ' ' , ", table_name($on_what_table)));
    $on_what_field=$singular.'_id';
    $sql = "select $name_field as attached_to_name from $on_what_table WHERE $on_what_field = $on_what_id";
} else {
    $attached_to_link = "N/A";
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
// end attached_to processing


$show_blank = (get_system_parameter($con, 'Allow Unassigned Activities') == "y" ? true : false);
$user_menu = get_user_menu($con, $user_id, $show_blank, 'user_id', false);

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

//get activity type menu
$sql = "SELECT activity_type_pretty_name, activity_type_id
        FROM activity_types
        WHERE activity_type_record_status = 'a'
        ORDER BY sort_order, activity_type_pretty_name";
$rst = $con->execute($sql);
if (!$rst) { db_error_handler($con, $sql); }
$activity_type_menu = $rst->getmenu2('activity_type_id', $activity_type_id, false);
$rst->close();

//get priority type menu
$sql = "SELECT case_priority_pretty_name,case_priority_id
        FROM case_priorities
        WHERE case_priority_record_status = 'a'
        ORDER BY case_priority_pretty_name";
$rst = $con->execute($sql);
if (!$rst) { db_error_handler($con, $sql); }
$activity_priority_menu = $rst->getmenu2('activity_priority_id', $activity_priority_id,true);
$rst->close();


//get activity type menu
$sql = "SELECT resolution_pretty_name, activity_resolution_type_id
        FROM activity_resolution_types
        WHERE resolution_type_record_status = 'a'
        ORDER BY sort_order, resolution_pretty_name";
$rst = $con->execute($sql);
if (!$rst) { db_error_handler($con, $sql); }
$activity_resolution_type_menu = $rst->getmenu2('activity_resolution_type_id', $activity_resolution_type_id, true, false, 0, 'id=activity_resolution_type_id');
$rst->close();


if ($company_id) {
    //get contact name menu
    $sql = "
    SELECT " . $con->Concat("first_names","' '","last_name") . " AS contact_name, contact_id
    FROM contacts
    WHERE company_id = $company_id
    AND contact_record_status = 'a'
    ORDER BY last_name, first_names
    ";
    $rst = $con->execute($sql);
    if ($rst) {
        $contact_menu = $rst->getmenu2('contact_id', $contact_id, true);
        $rst->close();
    } else {
        db_error_handler ($con, $sql);
    }

    //get activity location menu
    $location_menu=get_company_address_select($con, $company_id, $activity_address_id);
}

add_audit_item($con, $session_user_id, 'viewed', 'activities', $activity_id, 3);

/* add opportunities/case/campaign combo box */
//get singular form of table name (from on_what_table field)
$is_linked = true;
if ($on_what_table == "opportunities") {
    $table_name = "opportunity";
}
elseif ($on_what_table == "campaigns") {
    $table_name = "campaign";
}
elseif ($on_what_table == "cases") {
    $table_name = "case";
}
else {
    $table_name = "attached to";
    $is_linked = false;
}


//Check if activity is linked to something, then generate a SQL statement
$table_status_id = '';
if ($is_linked) {
    switch ($table_name) {
        case 'case':
            $type_field="{$table_name}_type_id";
            $type_field_limit=",{$on_what_table}.$type_field";
        break;
        case 'opportunity':
            $type_field="{$table_name}_type_id";
            $type_field_limit=",{$on_what_table}.$type_field";
        break;
        default:
            $type_field=false;
            $type_field_limit='';
       break;
    }
    $sql = "select ".$table_name."_id,
            ".$table_name."_statuses.".$table_name."_status_pretty_name,
            ".$on_what_table.".".$table_name."_id,
            ".$on_what_table.".".$table_name."_status_id,
            ".$table_name."_statuses.".$table_name."_status_id
            $type_field_limit
            from ".$table_name."_statuses, ".$on_what_table."
            where ".$on_what_table.".".$table_name."_id=$on_what_id
            and ".$on_what_table.".".$table_name."_status_id=".$table_name."_statuses.".$table_name."_status_id";
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
    } else db_error_handler($con, $sql);

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

$table_name=ucwords($table_name);

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
//call the activity_content_bottom hook
//sending null parameter, expecting return instead of change to passed in reference
$param=NULL;
$activity_content_top = do_hook_function('activity_content_top',$param);

$param=NULL;
$activity_content_bottom = do_hook_function('activity_content_bottom',$param);

//call activity inline hook, pass the activity_rst result set as parameter
$activity_inline_rows    = do_hook_function('activity_inline_edit',$activity_rst);

// related activities

$ra_extra_where = array();

if($on_what_table && $on_what_id) {
    //don't do anything if this activity is on companies or contacts
    if (($on_what_table != 'companies') && ($on_what_table != 'contacts')){
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




/*********************************/
/*** Include the sidebar boxes ***/

require_once('participant_sidebar.php');


$related_block='';
require_once('attachment_sidebar.php');


if ($contact_id) {
    // include the contact sidebar code
    require_once ('../contacts/sidebar.php');
} else {
  $contact_block = '';
}

if ($company_id) {
    // include the company sidebar code
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
$sidebar_plugin_rows = do_hook_function('activity_sidebar_bottom',$param);

/** End of the sidebar includes **/
/*********************************/

$con->close();

$page_title = _("Activity Details").': '.$activity_title;
start_page($page_title, true, $msg);

// load confGoTo.js
confGoTo_includes();

?>

<script language="JavaScript" type="text/javascript">

function changeAttachment(attachAction) {
   if (!attachAction) {
      document.forms[0].change_attachment.value='true';
      document.forms[0].submit();
   } else if (attachAction=='detach') {
      document.forms[0].change_attachment.value='detach';
      document.forms[0].submit();
   }
}

function logTime() {
    var date = new Date();
    var d = date.getDate();
    var day = (d < 10) ? '0' + d : d;
    var m = date.getMonth() + 1;
    var month = (m < 10) ? '0' + m : m;
    var yy = date.getYear();
    var year = (yy < 1000) ? yy + 1900 : yy;

    var h = date.getHours();
    var hour = (h < 10) ? '0' + h : h;
    var mm = date.getMinutes();
    var minute = (mm < 10) ? '0' + mm : mm;
    var s = date.getSeconds();
    var second = (s < 10) ? '0' + s : s;

    return year + '-' + month + '-' + day + ' ' + hour + ':' + minute + ':' + second;
}
</script>
<div id="Main">

    <div id="Content">


        <form action=edit-2.php method=post class="print" name=activity_data>
        <input type=hidden name=return_url value="<?php  echo $return_url; ?>">
        <input type=hidden name=current_activity_status value="<?php  echo $activity_status; ?>">
        <input type=hidden name=activity_status value="<?php  echo $activity_status; ?>">
        <input type=hidden name=activity_id value="<?php  echo $activity_id; ?>">
        <input type=hidden name=company_id value="<?php  echo $company_id; ?>">
        <input type=hidden name=on_what_table value="<?php  echo $on_what_table; ?>">
        <input type=hidden name=on_what_id value="<?php  echo $on_what_id; ?>">
        <input type=hidden name=table_name value="<?php echo $table_name ?>">
        <input type=hidden name=table_status_id value="<?php echo $table_status_id ?>">
        <input type=hidden name=old_status value="<?php echo $table_status_id ?>">
        <input type=hidden name=thread_id value="<?php  echo $thread_id; ?>">
        <input type=hidden name=followup_from_id value="<?php  echo $followup_from_id; ?>">

        <table class=widget cellspacing=1>
                <?php echo $activity_content_top; ?>
            <tr>
                <td class=widget_header colspan=2><?php echo _("About This Activity"); ?> <?php echo ($save_and_next) ? "(<input onclick=\"var input = prompt('Jump to', ''); if(input != null && input != '') document.location.href='browse-next.php?activity_id=" . $activity_id . "&pos=' + (input);\" type=button class=button value=" . $_SESSION['pos'] . ">/" . count($_SESSION['next_to_check']) . ")": "" ; ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Company"); ?></td>
                <td class=widget_content>
                    <?php echo '<a href="../companies/one.php?company_id='.$company_id.'">'.$company_name; ?></a>
                </td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Contact"); ?></td>
                <input type=hidden name=add_participant>
                <input type=hidden name=remove_participant>
                <input type=hidden name=mailmerge_participant>
                <td class=widget_content>
                    <?php
                        echo $contact_menu;
                    ?>
                </td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Attached To"); ?></td>
                <td class=widget_content>
                    <input type=hidden name=change_attachment>
                    <?php  echo $attached_to_link;
                        if ($table_name != "Attached To") {
                            echo " &nbsp; " . _("Status") . " &nbsp; ";
                            echo $table_menu;
                        }
                    ?><br>
                </td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Activity Type"); ?></td>
                <td class=widget_content_form_element><?php  echo $activity_type_menu; ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Activity Priority"); ?></td>
                <td class=widget_content_form_element><?php  echo $activity_priority_menu; ?></td>
            </tr>
           <?php
                if($on_what_table == 'opportunities') {
           ?>
           <tr>
                <td class=widget_label_right><?php echo _("Probability") . "&nbsp;" . _("(%)"); ?></td>
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
            <?php
                } //end if on_what_table=opportunities check
            ?>
            <tr>
                <td class=widget_label_right><?php echo _("Summary"); ?></td>
                <td class=widget_content_form_element>
                    <input type=text size=50 name=activity_title value="<?php  echo htmlspecialchars(trim($activity_title)); ?>">
                </td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Owner"); ?></td>
                <td class=widget_content_form_element><?php  echo $user_menu; ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Activity Notes"); ?></td>
                <td class=widget_content_form_element>
                <?php if($print_view) {
                        echo htmlspecialchars(nl2br(trim($activity_description)));
                        echo "<input type=hidden name=activity_description value=\"" . htmlspecialchars(nl2br(trim($activity_description))) . "\">\n";
                      } else { ?>
                        <textarea rows=10 cols=70 name=activity_description><?php  echo htmlspecialchars(trim($activity_description)); ?></textarea>
                <?php }  ?>
                </td>
            </tr>
            <?php
            if($on_what_table == 'opportunities') {
            ?>
            <tr>
                <td class=widget_label_right><?php echo _("Opportunity Notes"); ?></td>
                <td class=widget_content_form_element>
                    <textarea rows=10 cols=70 name=opportunity_description><?php  echo htmlspecialchars(trim($opportunity_description)); ?></textarea><br>
                    <input class=button value="<?php echo _("Insert Log"); ?>" type=button onclick="var new_message = prompt('Enter note', ''); document.forms[0].opportunity_description.value =
                        logTime() + ' by <?php echo $_SESSION['username']; ?>: ' + new_message + '\n' + document.forms[0].opportunity_description.value; document.forms[0].return_url.value = '<?php echo current_page() . '&fill_user'; ?>'; document.forms[0].submit();">
                    <?php do_hook('opportunity_notes_buttons'); ?>
                </td>
            </tr>
            <?php } if($location_menu) { ?>
            <tr>
                <td class=widget_label_right><?php echo _("Location"); ?></td>
                <td class=widget_content_form_element>
                    <?php
                        echo $location_menu;
                    ?>
                </td>
            </tr>
            <?php } if($local_time) { ?>
            <tr>
                <td class=widget_label_right><?php echo _("Local Time"); ?></td>
                <td class=widget_content_form_element>
                    <?php
                        //Remember to call update_daylight_savings($con);
                        echo gmdate('Y-m-d H:i:s', $local_time);
                    ?>
                </td>
            </tr>
            <?php } ?>
            <tr>
                <td class=widget_label_right><?php echo _("Entered By"); ?></td>
                <td class=widget_content_form_element><?php echo $entered_by_username.' '._("on").' '.$entered_at; ?></td>
            </tr>
            <?php if ($last_modified_by) { ?>
            <tr>
                <td class=widget_label_right><?php echo _("Last Modified By"); ?></td>
                <td class=widget_content_form_element><?php echo $last_modified_by_username.' '._("on").' '.$last_modified_at; ?></td>
            </tr>
           <?php } ?>
            <tr>
                <td class=widget_label_right><?php echo _("Scheduled Start"); ?></td>
                <td class=widget_content_form_element>
                    <?php jscalendar_includes(); ?>
                    <input type=text ID="f_date_c" name=scheduled_at value="<?php  echo $scheduled_at; ?>">
                    <img ID="f_trigger_c" style="CURSOR: hand" border=0 src="../img/cal.gif">
                </td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Scheduled End"); ?></td>
                <td class=widget_content_form_element>
                    <input type=text ID="f_date_d" name=ends_at value="<?php  echo $ends_at; ?>">
                    <img ID="f_trigger_d" style="CURSOR: hand" border=0 src="../img/cal.gif">
                </td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Email This To"); ?></td>
                <td class=widget_content_form_element><input type=text name=email_to></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Completed?"); ?></td>
                <td class=widget_content_form_element><input type=checkbox id=activity_completed name=activity_status value='on' <?php if ($activity_status == 'c') {print "checked";}; ?>>
                    <?php if ($completed_by) echo _("by").' '.$completed_by_username; if ($completed_at AND ($completed_at!='0000-00-00 00:00:00')) echo ' '._("on").' '. $completed_at; ?>
                </td>
            </tr>
            <tr id='resolution_type' >
                <td class=widget_label_right><?php echo _("Resolution Type"); ?></td>
                <td class=widget_content_form_element>
                    <?php echo $activity_resolution_type_menu; ?>
                </td>
            </tr>
            <tr id='resolution_reason' >
                <td class=widget_label_right><?php echo _("Resolution Description"); ?></td>
                <td class=widget_content_form_element>
                    <textarea rows=10 cols=70 id=resolution_description name=resolution_description><?php echo htmlspecialchars(trim($resolution_description)); ?></textarea>
                </td>
            </tr>
            <?php
                //add the inline data added by plugin(s)
                echo $activity_inline_rows;
            ?>
            <tr>
                <td class=widget_content_form_element colspan=2>
                    <?php

                        echo render_edit_button(_("Save Changes"),'submit',false,'save', false,'activities',$activity_id);

                        if($save_and_next) {
                            echo '<input class=button type=submit name="saveandnext" value="'._("Save and Next").'"';
                            $save_and_next="&save_and_next=true";
                        }

                        echo render_create_button(_("Schedule Followup"),'submit',false,'followup');

                        if($activity_recurrence_id) {
                            echo render_edit_button(_("Edit Recurrence"),'submit',false,'recurrence');
                        } else {
                            echo render_edit_button(_("Create Recurrence"),'submit',false,'recurrence');
                        }

                        if($print_view) {
                            echo render_edit_button(_("Edit View"),'submit','javascript:document.activity_data.return_url.value=\''."/activities/one.php?activity_id=$activity_id$save_and_next&return_url=".urlencode($return_url)."".'\'','edit_view', false,'activities',$activity_id);
                        } else {
                            echo render_edit_button(_("Print View"),'submit','javascript:document.activity_data.return_url.value=\''."/activities/one.php?activity_id=$activity_id$save_and_next&return_url=".urlencode($return_url)."".'\'','print_view', false,'activities',$activity_id);
                        }


                        echo render_delete_button(_("Delete"),'button',"javascript:location.href='delete.php?activity_id=$activity_id$save_and_next&return_url=".urlencode($return_url)."'", false, false, 'activities',$activity_id);
                    ?>

                </td>
            </tr>
           <?php  echo $history_text; ?>
        </table>


        </form>

        <form action=one.php name="OneActivityForm" method=post>
            <input type=hidden name="activity_id" value="<?php echo $activity_id; ?>">
            <input type=hidden name="return_url" value="<?php echo $return_url; ?>">
            <?php
                // output the selectable columns widget
                echo $pager_columns_selects;

                echo $related_activities_widget['content'];
                echo $related_activities_widget['sidebar'];
                echo $related_activities_widget['js'];
            ?>
        </form>

        <?php echo $activity_content_bottom; ?>
    </div>

    <!-- right column //-->
    <div id="Sidebar">
       <!-- sidebar top plugins //-->
        <?php echo $sidebar_top_plugin_rows; ?>
        <!-- participant list block //-->
        <?php echo $participant_block; ?>
        <!-- attachment list block //-->
        <?php echo $related_block; ?>
        <!-- company information block //-->
        <?php echo $company_block; ?>
        <!-- contact information block //-->
        <?php if ( $contact_block) echo $contact_block; ?>
        <!-- file block //-->
        <?php echo $file_rows; ?>
        <!-- sidebar plugins //-->
        <?php if ( isset($relationship_link_rows) && $relationship_link_rows ) echo $relationship_link_rows; ?>
        <?php if ( isset($sidebar_plugin_rows) && $sidebar_plugin_rows ) echo $sidebar_plugin_rows; ?>
    </div>

</div>

<script type="text/javascript">
    var old_description='';
    var old_type='';
    function HideResolutionFields() {
        old_type=document.getElementById('activity_resolution_type_id').value;
        old_description=document.getElementById('resolution_description').value;

        document.getElementById('resolution_type').style.display='none';
        document.getElementById('resolution_reason').style.display='none';
        document.getElementById('activity_resolution_type_id').value='';
        document.getElementById('resolution_description').value='';

        document.getElementById('activity_completed').onclick=ShowResolutionFields;
    }
    function ShowResolutionFields() {
        document.getElementById('resolution_type').style.display='';
        document.getElementById('resolution_reason').style.display='';
        document.getElementById('activity_resolution_type_id').value=old_type;
        document.getElementById('resolution_description').value=old_description;

        document.getElementById('activity_completed').onclick=HideResolutionFields;
    }

    if (!document.getElementById('activity_completed').checked) {
        HideResolutionFields();
    } else {
        document.getElementById('activity_completed').onclick=HideResolutionFields;
    }

    Calendar.setup({
        inputField     :    "f_date_c",      // id of the input field
        ifFormat       :    "%Y-%m-%d %H:%M:%S",       // format of the input field
        showsTime      :    true,            // will display a time selector
        button         :    "f_trigger_c",   // trigger for the calendar (button ID)
        singleClick    :    false,           // double-click mode
        step           :    1,                // show all years in drop-down boxes (instead of every other year as default)
        align          :    "Bl"           // alignment (defaults to "Bl")
    });
</script>
<script type="text/javascript">
    Calendar.setup({
        inputField     :    "f_date_d",      // id of the input field
        ifFormat       :    "%Y-%m-%d %H:%M:%S",       // format of the input field
        showsTime      :    true,            // will display a time selector
        button         :    "f_trigger_d",   // trigger for the calendar (button ID)
        singleClick    :    false,           // double-click mode
        step           :    1,                // show all years in drop-down boxes (instead of every other year as default)
        align          :    "Bl"           // alignment (defaults to "Bl")
    });
</script>


<?php

    end_page();

/**
 * $Log: one.php,v $
 * Revision 1.139  2006/06/29 15:53:46  braverock
 * - remove extra whitespace from resolution_description field.
 *   - patch by Frederik Jervfors
 *
 * Revision 1.138  2006/06/21 15:51:25  jswalter
 *  - LOCATION list does not default to address selected for a given activity. This was corrected.
 *
 * Revision 1.137  2006/05/06 09:32:18  vanmer
 * - added passthrough for old status seperately from status in dropdown
 *
 * Revision 1.136  2006/05/02 00:40:16  vanmer
 * - moved recurrence check back into activities/one.php
 *
 * Revision 1.135  2006/05/01 19:32:24  braverock
 * - use get_activity API call
 *
 * Revision 1.134  2006/04/28 16:31:38  braverock
 * - use get_activity API call
 *   - move created,modified,completed by processing into API
 *   - standardize how variables are assigned from the result set
 *   - limit processing in this file to UI-directed items
 *
 * Revision 1.133  2006/01/19 22:20:32  daturaarutad
 * add Print View button which displays textarea as a static element
 *
 * Revision 1.132  2006/01/19 16:22:11  braverock
 * - add class="print" to the main form to aid in printing support
 *
 *
 * Revision 1.131  2006/01/10 08:47:01  gpowers
 * - added activity_content_top plugin hook
 * - added activity_sidebar_top plugin hook
 * - added limiting of opp. statuses by opp. types
 *
 * Revision 1.130  2006/01/02 21:23:18  vanmer
 * - changed to use centralized database connection function
 *
 * Revision 1.129  2005/12/18 02:56:23  vanmer
 * - changed to use gmt_offset fieldname
 *
 * Revision 1.128  2005/11/04 16:26:50  braverock
 * - clear ends_at time if scheduled and end times are the same
 *   and activity uncompleted
 * - rationalizes time for activities like phone calls
 *
 * Revision 1.127  2005/10/08 21:07:52  vanmer
 * - added hidden variables to track actions for participants subsystem
 *
 * Revision 1.126  2005/09/29 14:49:10  vanmer
 * - added lookup of activity template id, to provide to attachment sidebar
 *
 * Revision 1.125  2005/09/25 04:12:23  vanmer
 * - added ability to detach an activity from an on_what_table/on_what_id relationship using Detach button
 * - added case to check for $on_what_id before attempting to query for activity attachmetn
 * - added error handling on sql errors when querying for a name of the activity's attached entity
 *
 * Revision 1.124  2005/09/23 20:55:40  daturaarutad
 * add space after thread_id clause in extra_where
 *
 * Revision 1.123  2005/09/21 20:08:34  vanmer
 * - added menu for location of activity
 * - removed address table from query on activity, unused and overrides activity address_id
 *
 * Revision 1.122  2005/09/08 21:30:43  vanmer
 * - changed to have edit button reference correctly the activity rather than whatever $on_what_table and $on_what_id
 * happen to be
 *
 * Revision 1.121  2005/08/11 02:36:52  vanmer
 * - Added sidebar to control activity association
 * - moved button from main form to sidebar
 *
 * Revision 1.120  2005/08/02 22:00:43  ycreddy
 * Added Last Modified By and Last Modified At fields to the details
 *
 * Revision 1.119  2005/07/31 17:41:32  braverock
 * - make changes to improve functioning even if register_globals is 'on'
 *
 * Revision 1.118  2005/07/15 22:52:53  vanmer
 * - changed join to reflect activities without a company
 * - changed User field to be listed as Owner instead, to reflect standard field labels
 *
 * Revision 1.117  2005/07/08 14:49:57  braverock
 * - fix to properly handle saving activities that are not part of workflow activity templates
 * - trim description fields
 *
 * Revision 1.116  2005/07/08 14:40:25  braverock
 * - set textarea for resolution to be the same size as the other textarea's on the page
 *
 * Revision 1.115  2005/07/08 01:30:09  vanmer
 * - changed Change button into Change Attachment button
 * - changed to redirect and save instead of going immediately to change the attachment
 *
 * Revision 1.114  2005/07/08 01:18:56  braverock
 * - localize button strings
 *
 * Revision 1.113  2005/07/08 01:07:24  vanmer
 * - changed to try to show attached entity if possible
 *
 * Revision 1.112  2005/07/08 00:53:25  vanmer
 * - added change button to reconnect activity to another entity
 *
 * Revision 1.111  2005/07/07 20:54:49  vanmer
 * - changed return_url path from activities into sidebars
 *
 * Revision 1.110  2005/07/07 03:38:46  daturaarutad
 * updated to use new activities-widget functions
 *
 * Revision 1.109  2005/06/30 17:32:27  vanmer
 * - added javascript and needed ID's to form elements to allow resolution fields to be hidden before activity is
 * completed
 *
 * Revision 1.108  2005/06/30 04:39:44  vanmer
 * - added UI for resolution description, resolution types and activity priority
 *
 * Revision 1.107  2005/06/27 16:31:46  braverock
 * - add Entered By into main screen after many requests
 * - fix localization of several strings
 *
 * Revision 1.106  2005/06/08 17:36:28  daturaarutad
 * updated rst->activity_rst to fix broken page
 *
 * Revision 1.105  2005/06/08 15:31:24  braverock
 * - add activity_inline_edit hook
 *
 * Revision 1.104  2005/06/03 22:55:45  braverock
 * - change the logic for recurring activities pager to exclude on_what_table of
 *   companies or contacts
 *
 * Revision 1.103  2005/06/03 20:58:22  daturaarutad
 * moved recurrence configuration widget to its own page
 *
 * Revision 1.102  2005/06/03 12:53:59  braverock
 * - remove 'Switch Opportunity' contact switching, as this is confusing to users
 * - take out nbsp; tags from inside strings that are better combined for i18n
 *
 * Revision 1.101  2005/05/25 21:35:53  braverock
 * - improve color CSS style rendering on related activities pager
 *
 * Revision 1.100  2005/05/25 15:10:52  braverock
 * - changed to urlencode the string for localized error msg
 *
 * Revision 1.99  2005/05/25 14:55:32  braverock
 * - add error message and return if no activity_id is passed in
 *
 * Revision 1.98  2005/05/25 05:37:58  vanmer
 * - added output to display completed_by and completed_at when an activity is completed, next to the checked
 * completed box.
 *
 * Revision 1.97  2005/05/25 05:35:51  daturaarutad
 * added the activity recurrence sidebar
 *
 * Revision 1.96  2005/05/19 20:29:41  daturaarutad
 * added support for followup activities
 *
 * Revision 1.95  2005/05/19 13:20:43  maulani
 * - Remove trailing whitespace
 *
 * Revision 1.94  2005/05/10 21:31:43  braverock
 * - modify so selectable columns widget is rendered inside the <html> and <form> tags
 *
 * Revision 1.93  2005/05/05 23:01:02  braverock
 * - changed labels on Summary, Scheduled Start, Scheduled End columns in pager
 *   for consistency in naming conventions
 *
 * Revision 1.92  2005/05/05 21:38:31  daturaarutad
 * added Related Activities Pager and changed $_GET usage to getGlobalVar
 *
 * Revision 1.91  2005/05/04 14:33:37  braverock
 * - removed obsolete widget_label_right_166px CSS style, replaces w/ widget_label_right
 *
 * Revision 1.90  2005/05/04 14:30:40  braverock
 * - fix CSS style for 'Activity Notes'
 *
 * Revision 1.89  2005/05/04 14:27:28  braverock
 * - change Activity 'Title' to 'Summary' for consistency
 *
 * Revision 1.88  2005/05/04 13:39:50  braverock
 * - change 'Start' to 'Scheduled Start' for consistenct of activity start time labels
 * - change 'End' to 'Scheduled End' for consistenct of activity end time labels
 *
 * Revision 1.87  2005/04/28 15:31:35  braverock
 * - applied patch for clearing case/opp/campaign id on editing of activities
 *   patch supplied by Miguel Gonçalves (mig77)
 *
 * Revision 1.86  2005/04/20 21:26:27  braverock
 * - change $on_what_table to 'activities' before calling file sidebar
 *
 * Revision 1.85  2005/04/18 23:34:13  maulani
 * - participant sidebar include was stomping on $return_url variable.  Changed
 *   variable name to resolve conflict in activities/one.php
 *
 * Revision 1.84  2005/04/18 16:32:39  vanmer
 * - changed default behavior when clicking the delete button: used to redirect to arbitrary return_url (by default same page)
 * - now redirects back to /activities/some.php
 *
 * Revision 1.83  2005/04/15 07:48:21  vanmer
 * - added sidebar for display of activity participants
 *
 * Revision 1.82  2005/04/07 17:45:43  vanmer
 * - added NULL parameter to do_hook_function, to fulfill new requirement of passing a second parameter to do_hook_function
 *
 * Revision 1.81  2005/03/22 22:12:43  gpowers
 * - added activity_content_bottom plugin hook
 *
 * Revision 1.80  2005/03/21 14:45:42  maulani
 * - Display optional id and other info about activity.  Option controlled
 *   with system parameter.  ID is useful for developers tracking bugs in
 *   production.
 *
 * Revision 1.79  2005/03/21 14:38:31  maulani
 * - Having unassigned activities is now an option that can be set in
 *   system parameters.  Installations that do not need activity pools
 *   can require activities to have an assigned user.
 *
 * Revision 1.78  2005/03/21 13:40:51  maulani
 * - Remove redundant code by centralizing common user menu call
 *
 * Revision 1.77  2005/02/10 21:16:43  maulani
 * - Add audit trail entries
 *
 * Revision 1.76  2005/01/22 15:07:14  braverock
 * - add sort order to activity_types menu
 *
 * Revision 1.75  2005/01/10 21:43:32  vanmer
 * - added types so that status dropdown can operate properly when activity is attached to a case with case types
 *
 * Revision 1.74  2005/01/10 20:47:47  neildogg
 * - Changed to support new relationship sidebar variable requirement
 *
 * Revision 1.73  2005/01/09 18:08:57  vanmer
 * - moved definition activity_id to above session_check (for ACL)
 * - added default return_url if none is defined
 * - changed to use render_button functions for buttons instead of direct HTML
 *
 * Revision 1.72  2004/12/31 13:46:51  braverock
 * - localize 'Entered by'
 * - sort contact list by last_name, first_names
 *
 * Revision 1.71  2004/12/30 00:13:16  maulani
 * - Display Entered by User data in addition to assigned user
 *
 * Revision 1.70  2004/12/27 15:57:21  braverock
 * - localize "Switch Opportunity"
 *
 * Revision 1.69  2004/12/20 21:50:51  neildogg
 * - Updated to reflect new parameter passing
 *
 * Revision 1.68  2004/12/20 13:50:39  neildogg
 * Added ability to select an empty user (allows an activity pool)
 *
 * Revision 1.67  2004/12/01 18:12:42  vanmer
 * - altered relationship setup section to reference relationships that relate to activities
 *
 * Revision 1.66  2004/10/31 14:14:30  braverock
 * - fixed bug that overwrote table_name, breaking link w/ opportunities/cases
 * - moved sidebar code lower in page, resolved issues with overwriting values
 * - adjusted width of textareas to solve CSS layout problem in IE
 *
 * Revision 1.65  2004/10/08 19:30:43  gpowers
 * - added file attachment sidebar to activities
 *
 * Revision 1.64  2004/09/13 21:59:03  introspectshun
 * - Changed order of tables in main query.
 *   - MSSQL chokes on the JOIN otherwise.
 *
 * Revision 1.63  2004/09/02 23:20:21  maulani
 * - Reduce textarea width to fit on 1024 wide screen
 *
 * Revision 1.62  2004/08/26 14:41:31  neildogg
 * - Display nothing if no daylight savings in address
 *
 * Revision 1.61  2004/08/25 14:34:53  neildogg
 * - Displays local time
 *  - Change position as you see fit
 *
 * Revision 1.60  2004/08/19 20:43:51  neildogg
 * - Added jump to position in save and next
 *
 * Revision 1.59  2004/08/04 18:18:11  neildogg
 * - If you're going to change one textarea
 *  - for goodness sake, change the one below it
 *
 * Revision 1.58  2004/08/04 15:58:05  maulani
 * - Narrow textarea so it will fit on 1024 x 768 screen
 * - todo to make relative positioning so adjusts for larger screens.
 *
 * Revision 1.57  2004/08/04 15:31:12  neildogg
 * - Added more plugin support
 *
 * Revision 1.56  2004/07/30 10:03:10  cpsource
 * - Remove undefines
 *     contact_block
 *     relationship_link_rows
 *
 * Revision 1.55  2004/07/30 09:45:24  cpsource
 * - Place confGoTo setup later in startup sequence.
 *
 * Revision 1.54  2004/07/29 09:35:47  cpsource
 * - Seperate .js and .php for confGoTo for PHP V4 problems.
 *
 * Revision 1.53  2004/07/28 20:55:32  neildogg
 * - Added parenthesis around save and next numbers
 *
 * Revision 1.52  2004/07/28 19:24:21  cpsource
 * - Move confGoTo sub-system out into a seperate file for
 *   a more structured, and general implementation.
 *
 * Revision 1.51  2004/07/27 19:50:41  neildogg
 * - Major changes to browse functionality
 *  - Removal of sidebar for "browse" button
 *  - Removal of activity_type browse
 *  - Aesthetic modifications
 *  - Date in some.php is now mySQL curdate()
 *
 * Revision 1.50  2004/07/26 12:10:26  cpsource
 * - Fix bug whereby javascript is used to confirm the
 *   delete of an activity.
 *
 * Revision 1.49  2004/07/25 20:06:57  johnfawcett
 * - standardized delete button
 *
 * Revision 1.48  2004/07/25 16:15:25  johnfawcett
 * - unified page title
 *
 * Revision 1.47  2004/07/25 12:27:42  braverock
 * - remove lang file require_once, as it is no longer used
 *
 * Revision 1.46  2004/07/22 14:06:00  neildogg
 * - Errant commit, rollback to 1.44
 *
 * Revision 1.45  2004/07/22 13:58:27  neildogg
 * - Limit group saved-search functionality to admin
 *
 * Revision 1.44  2004/07/21 13:00:54  neildogg
 * - Rolling back previous erroneous commit to reactivate sidebars
 *  - Sidebar variables are declared in the sidebar requires
 *
 * Revision 1.43  2004/07/21 11:48:47  cpsource
 * - Stub out unused right sidebar.
 *
 * Revision 1.42  2004/07/20 16:50:00  neildogg
 * - Have to remove the hidden opportunity_description AGAIN
 *
 * Revision 1.41  2004/07/20 11:25:26  braverock
 * - removed second jscalendar_includes call
 *   - it is unecessary, and causes a stack overflow on IE 6
 *   - applies fix for SF bug 976476 suggested by cdeneve
 *
 * Revision 1.40  2004/07/19 21:19:52  neildogg
 * - Allow contact to be shifted with opportunity as well as activity
 *
 * Revision 1.39  2004/07/16 04:53:51  introspectshun
 * - Localized strings for i18n/translation support
 *
 * Revision 1.38  2004/07/15 22:57:20  cpsource
 * - Post $opportunity_description
 *
 * Revision 1.37  2004/07/14 22:10:49  neildogg
 * - Now uses $overall_id
 *
 * Revision 1.36  2004/07/14 18:30:37  neildogg
 * - I don't have a calender1.js file, correct if wrong
 *  - For some reason someone added a second opportunity_description, removed
 *  - Proper form naming, since form name=form was removed
 *
 * Revision 1.35  2004/07/14 15:22:17  cpsource
 * - Fixed various undefines, including:
 *     $opportunity_description
 *     $followup
 *     $saveandnext
 *     $table_status_id
 *     $probability
 *
 * Revision 1.34  2004/07/11 15:12:40  braverock
 * - Change 'Description' to 'Activity Notes' for consistency
 *
 * Revision 1.33  2004/07/09 19:41:02  neildogg
 * - Now matches normal description textarea width\n- Break before Insert button
 *
 * Revision 1.32  2004/07/09 15:50:56  neildogg
 * - Uses the new, generic relationship sidebar
 *
 * Revision 1.31  2004/07/08 12:27:05  braverock
 * - clean up formatting of probability syntax for less parser switching and easier readability
 *
 * Revision 1.30  2004/07/08 02:22:11  gpowers
 * - changed description textarea width to 90 (was 100)
 *   - this screen will now fit on a 1024x768 Windows XP display
 *     with MSIE v6.0 (Maximized)
 *
 * Revision 1.29  2004/07/07 22:23:18  neildogg
 * - Fixed lack of <?php in probability printing\n- Added logging formatting in opportunity notes
 *
 * Revision 1.28  2004/07/07 18:06:18  neildogg
 * - Added sticky opportunity description
 *
 * Revision 1.27  2004/07/02 18:09:19  neildogg
 * - Added contact-company sidebar to activities page as per new support in companies/company-sidebar.php.
 *
 * Revision 1.26  2004/06/25 03:12:41  braverock
 * - add error handling for missing variables
 *
 * Revision 1.25  2004/06/24 19:58:47  braverock
 * - committing enhancements to Save&Next functionality
 *   - patches submitted by Neil Roberts
 *
 * Revision 1.24  2004/06/13 09:15:07  braverock
 * - add Save & Next functionality
 *   - code contributed by Neil Roberts
 *
 * Revision 1.23  2004/06/11 21:20:11  introspectshun
 * - Now use ADODB Concat and Date functions.
 *
 * Revision 1.22  2004/06/10 20:30:07  braverock
 * - added ability to edit probability on linked opportunity
 *   - code contributed by Neil Roberts
 *
 * Revision 1.21  2004/06/04 15:57:24  gpowers
 * Applied Patch [ 965012 ] Calendar replacement By: miguel GonÃ§ves - mig77
 * w/minor changes: changed includes to function, used complete php tags
 *
 * Revision 1.20  2004/06/03 17:29:04  gpowers
 * changed the order of the sidebars(contact,company) to match the order in
 * the form to the left (company,contact)
 *
 * Revision 1.19  2004/06/03 16:31:05  gpowers
 * my bad. they exist now.
 *
 * Revision 1.18  2004/06/03 16:29:58  gpowers
 * commented out the includes for the contact sidebar code and
 * the company sidebar code. these sidebars do not appear to exist.
 *
 * Revision 1.17  2004/06/03 16:11:00  braverock
 * - add functionality to support workflow and activity templates
 *   - functionality contributed by Brad Marshall
 *
 * Revision 1.16  2004/05/28 13:58:33  gpowers
 * removed "viewed" audit log entry. this is redundant, as this data is
 * already stored in httpd access logs.
 *
 * Revision 1.15  2004/05/27 20:36:12  gpowers
 * Added Support for Patch [ 951138 ] Export Activities vCALENDAR
 * Export one activity into the vCalendar format.
 *
 * Revision 1.14  2004/05/10 13:07:20  maulani
 * - Add level to audit trail
 * - Clean up audit trail text
 *
 * Revision 1.13  2004/05/07 16:15:48  braverock
 * - fixed multiple bugs with date-time formatting in activities
 * - correctly use dbtimestamp() date() and strtotime() fns
 * - add support for $default_followup_time config var
 *   - fixes SF bug  949779 reported by miguel Gonï¿½alves (mig77)
 *
 * Revision 1.12  2004/04/27 16:42:07  gpowers
 * - fixed usertimestamp
 *
 * Revision 1.11  2004/04/27 16:28:39  gpowers
 * - added support for activity times.
 *   NOTE: usertimestamp doesn't appear to work. I don't know why.
 *   (The unformatted time works fine with MySQL, but may not with other DBs.)
 * - added support for activity emails.
 *
 * Revision 1.10  2004/04/26 01:54:45  braverock
 * - add ability to schedule a followup activity based on the current activity
 *
 * Revision 1.9  2004/04/19 22:21:15  maulani
 * - Correct javascript syntax
 *
 * Revision 1.8  2004/04/17 16:02:40  maulani
 * - Add CSS2 positioning
 *
 * Revision 1.7  2004/04/16 22:21:19  maulani
 * - Add CSS2 positioning
 *
 * Revision 1.6  2004/03/22 22:44:29  braverock
 * - add htmlspecialchars around activity_title and activity_description
 *   - fixes SF bug 921295
 *
 * Revision 1.5  2004/03/15 14:51:28  braverock
 * - fix ends-at display bug
 * - make sure both scheduled_at and ends_at have legal values
 * - add phpdoc
 */
?>
