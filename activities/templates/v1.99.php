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
$user_email_menu = get_user_email_menu($con, $email_to, true, 'email_to', false);

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
        WHERE company_id = $company_id ";
        if ($company_id == 1) { //limit the list if this is on the Unknown Company
            $sql .= "AND contact_id = $contact_id ";
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
    // changed to correctly show only related activities belonging to the same company
    if ($company_id)
     $ra_extra_where[] = "(a.on_what_table = '$on_what_table' AND a.on_what_id = $on_what_id AND a.company_id = $company_id AND a.activity_id <> $activity_id)";
    else
     $ra_extra_where[] = "(a.on_what_table = '$on_what_table' AND a.on_what_id = $on_what_id AND a.activity_id <> $activity_id)";
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

<?php if (get_user_preference($con, $user_id, "html_activity_notes") == 'y') { ?>
<script language="javascript" type="text/javascript" src="<?PHP echo $http_site_root;?>/include/tinymce/jscripts/tiny_mce/tiny_mce.js"></script>
<script language="javascript" type="text/javascript">
        tinyMCE.init({
                mode : "textareas",
                theme : "advanced",
                plugins : "table,emotions,iespell,insertdatetime,preview,print,paste,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras",
                theme_advanced_buttons1_add : "fontselect,fontsizeselect",
                theme_advanced_buttons2_add : "separator,insertdate,inserttime,preview,separator,forecolor,backcolor",
                theme_advanced_buttons3_add_before : "tablecontrols,separator",
                theme_advanced_buttons3_add : "iespell,print,fullscreen",
                theme_advanced_toolbar_location : "top",
                theme_advanced_toolbar_align : "left",
                plugin_insertdate_dateFormat : "%Y-%m-%d",
                plugin_insertdate_timeFormat : "%H:%M:%S",
                extended_valid_elements : "hr[class|width|size|noshade],font[face|size|color|style],span[class|align|style]",
                theme_advanced_resize_horizontal : false,
                theme_advanced_resizing : true,
                nonbreaking_force_tab : true,
                apply_source_formatting : true,
                fix_table_elements : true,
                convert_urls : true
        });
</script>
<?php } ?>

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
        <input type=hidden name=email_to value="<?php  echo $email_to; ?>">
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
                    <input class=button value="<?php echo _("Insert Log"); ?>" type=button onclick="var new_message = prompt('<?php echo addslashes(_("Enter Note")); ?>', ''); document.forms[0].opportunity_description.value =
                        logTime() + '<?php echo " " . _("By") . " " . $_SESSION['username']; ?>: ' + new_message + '\n' + document.forms[0].opportunity_description.value; document.forms[0].return_url.value = '<?php echo current_page() . '&fill_user'; ?>'; document.forms[0].submit();">
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
                 <td class=widget_content_form_element><?php  echo $user_email_menu; ?></td>
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
                <!-- db: begin  the following satement must to be written as single line to avoid bad display behaviour of the resolution description field content//-->
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
/**
 * $Log: v1.99.php,v $
 * Revision 1.1  2008/01/30 21:11:47  gpowers
 * - directory for storing activity templates
 *
 * Revision 1.143  2007/12/10 22:34:32  gpowers
 * - added system preference for html activity notes (uses tinymce)
 * - move $con->close(); to end of file.
 */
?>
