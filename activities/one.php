<?php
/**
 * Edit the details for a single Activity
 *
 * $Id: one.php,v 1.54 2004/07/29 09:35:47 cpsource Exp $
 */

//include required files
require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');
require_once($include_directory . 'confgoto.php');

$session_user_id = session_check();

$msg         = isset($_GET['msg']) ? $_GET['msg'] : '';
$activity_id = isset($_GET['activity_id']) ? $_GET['activity_id'] : '';
$return_url  = isset($_GET['return_url']) ? $_GET['return_url'] : '';
$save_and_next = isset($_GET['save_and_next']) ? true : false;

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
//$con->debug = 1;

update_recent_items($con, $session_user_id, "activities", $activity_id);

$sql = "select a.*, c.company_id, c.company_name, cont.first_names, cont.last_name
from companies c, activities a left join contacts cont on a.contact_id = cont.contact_id
where a.company_id = c.company_id
and activity_id = $activity_id
and activity_record_status='a'";

$rst = $con->execute($sql);

if ($rst) {
    $activity_type_id = $rst->fields['activity_type_id'];
    $current_activity_type_id = $rst->fields['activity_type_id'];
    $activity_title = $rst->fields['activity_title'];
    $activity_description = $rst->fields['activity_description'];
    $user_id = $rst->fields['user_id'];
    $company_id = $rst->fields['company_id'];
    $company_name = $rst->fields['company_name'];
    $contact_id = $rst->fields['contact_id'];
    $on_what_table = $rst->fields['on_what_table'];
    $current_on_what_table = $rst->fields['on_what_table'];
    $on_what_id = $rst->fields['on_what_id'];
    $scheduled_at = date('Y-m-d H:i:s', strtotime($rst->fields['scheduled_at']));
    $ends_at = date('Y-m-d H:i:s', strtotime($rst->fields['ends_at']));
    $activity_status = $rst->fields['activity_status'];
    $rst->close();
} else {
    db_error_handler($con, $sql);
}

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
} else {
    $attached_to_link = "N/A";
    $sql = "select * from companies where 1 = 2";
}

$rst = $con->execute($sql);

if ($rst) {
    $attached_to_name = $rst->fields['attached_to_name'];
    $attached_to_link .= $attached_to_name . "</a>";
    $rst->close();
}

//get user menu
$sql = "select username, user_id from users where user_record_status = 'a'";
$rst = $con->execute($sql);
$user_menu = $rst->getmenu2('user_id', $user_id, false);
$rst->close();

//get activity type menu
$sql = "select activity_type_pretty_name, activity_type_id from activity_types where activity_type_record_status = 'a' order by activity_type_pretty_name";
$rst = $con->execute($sql);
$activity_type_menu = $rst->getmenu2('activity_type_id', $activity_type_id, false);
$rst->close();

if ($company_id) {
    //get contact name menu
    $sql = "
    SELECT " . $con->Concat("first_names","' '","last_name") . " AS contact_name, contact_id
    FROM contacts
    WHERE company_id = $company_id
    AND contact_record_status = 'a'
    ";
    $rst = $con->execute($sql);
    if ($rst) {
        $contact_menu = $rst->getmenu2('contact_id', $contact_id, true);
        $rst->close();
    } else {
        db_error_handler ($con, $sql);
    }
}


// add_audit_item($con, $session_user_id, 'viewed', 'activities', $activity_id, 3);

if ($contact_id) {
    // include the contact sidebar code
    require_once ('../contacts/sidebar.php');
}

if ($company_id) {
    // include the company sidebar code
    require_once ('../companies/sidebar.php');
}

//include the contacts-companies sidebar
if ($contact_id) {
    $relationship_name = "company link";
    $working_direction = "from";
    $overall_id = $contact_id;
    require("../relationships/sidebar.php");
}

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
    $sql = "select ".$table_name."_id,
            ".$table_name."_statuses.".$table_name."_status_pretty_name,
            ".$on_what_table.".".$table_name."_id,
            ".$on_what_table.".".$table_name."_status_id,
            ".$table_name."_statuses.".$table_name."_status_id
            from ".$table_name."_statuses, ".$on_what_table."
            where ".$on_what_table.".".$table_name."_id=$on_what_id
            and ".$on_what_table.".".$table_name."_status_id=".$table_name."_statuses.".$table_name."_status_id";

    $rst = $con->execute($sql);
    //If not empty, get pretty name and id
    if ($rst) {
        $table_status = $rst->fields[$table_name.'_status_pretty_name'];
        $table_status_id = $rst->fields[$table_name.'_status_id'];
        $rst->close();
    }

    //generate SQL for status combo box
    $sql = "select ".$table_name."_status_pretty_name,
            ".$table_name."_status_id
            from ".$table_name."_statuses
            where ".$table_name."_status_record_status='a'
            order by sort_order";
    $rst = $con->execute($sql);

    //create combo box using ADODB getmenu2 function
    if ($rst) {
        $table_menu = $rst->getmenu2('table_status_id', $table_status_id, false);
        $rst->close();
    }

}

// add_audit_item($con, $session_user_id, 'viewed', $table_name, $table_status_id, 3);

$table_name=ucwords($table_name);

$opportunity_description = '';

if($on_what_table == 'opportunities') {
    $sql = "select opportunity_description from opportunities where opportunity_id='$on_what_id'";
    $rst = $con->execute($sql);
    $opportunity_description = $rst->fields['opportunity_description'];
    $rst->close();
}

$con->close();

// load confGoTo.js
confGoTo_includes();

$page_title = _("Activity Details").': '.$activity_title;
start_page($page_title, true, $msg);

?>

<script language="JavaScript" type="text/javascript">

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


        <form action=edit-2.php method=post>
        <input type=hidden name=return_url value="<?php  echo $return_url; ?>">
        <input type=hidden name=current_activity_status value="<?php  echo $activity_status; ?>">
        <input type=hidden name=activity_status value="<?php  echo $activity_status; ?>">
        <input type=hidden name=activity_id value="<?php  echo $activity_id; ?>">
        <input type=hidden name=company_id value="<?php  echo $company_id; ?>">
        <input type=hidden name=on_what_table value="<?php  echo $on_what_table; ?>">
        <input type=hidden name=on_what_id value="<?php  echo $on_what_id; ?>">
        <input type=hidden name=table_name value="<?php echo $table_name ?>">
        <input type=hidden name=table_status_id value="<?php echo $table_status_id ?>">

        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=2><?php echo _("About This Activity"); ?> <?php echo ($save_and_next) ? "(" . $_SESSION['pos'] . "/" . count($_SESSION['next_to_check']) . ")": "" ; ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Company"); ?></td>
                <td class=widget_content>
                    <?php echo '<a href="../companies/one.php?company_id='.$company_id.'">'.$company_name; ?></a>
                </td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Contact"); ?></td>
                <td class=widget_content><?php echo $contact_menu; if($on_what_table == "opportunities") { ?>
                    &nbsp; Switch Opportunity <input type=checkbox name=switch_opportunity value=on>
                    <?php } ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Attached") . "&nbsp;" . _("To"); ?></td>
                <td class=widget_content>
                    <?php  echo $attached_to_link;
                        if ($table_name != "Attached To") {
                            echo " &nbsp; " . _("Status") . " &nbsp; ";
                            echo $table_menu;
                        }
                    ?>
                </td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Activity") . "&nbsp;" . _("Type"); ?></td>
                <td class=widget_content_form_element><?php  echo $activity_type_menu; ?></td>
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
                <td class=widget_label_right><?php echo _("Title"); ?></td>
                <td class=widget_content_form_element>
                    <input type=text size=50 name=activity_title value="<?php  echo htmlspecialchars($activity_title); ?>">
                </td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("User"); ?></td>
                <td class=widget_content_form_element><?php  echo $user_menu; ?></td>
            </tr>
            <tr>
                <td class=widget_label_right_166px><?php echo _("Activity Notes"); ?></td>
                <td class=widget_content_form_element><textarea rows=10 cols=90 name=activity_description><?php  echo htmlspecialchars($activity_description); ?></textarea></td>
            </tr>
            <?php
            if($on_what_table == 'opportunities') {
            ?>
            <tr>
                <td class=widget_label_right_166px><?php echo _("Opportunity Notes"); ?></td>
                <td class=widget_content_form_element>
                    <textarea rows=10 cols=90 name=opportunity_description><?php  echo htmlspecialchars($opportunity_description); ?></textarea><br>
                    <input class=button value="<?php echo _("Insert Log"); ?>" type=button onclick="document.forms[0].opportunity_description.value =
                        logTime() + ' by <?php echo $_SESSION['username']; ?>: \n' + document.forms[0].opportunity_description.value">
                </td>
            </tr>
            <?php } ?>
            <tr>
                <td class=widget_label_right><?php echo _("Starts"); ?></td>
                <td class=widget_content_form_element>
                    <?php jscalendar_includes(); ?>
                    <input type=text ID="f_date_c" name=scheduled_at value="<?php  echo $scheduled_at; ?>">
                    <img ID="f_trigger_c" style="CURSOR: hand" border=0 src="../img/cal.gif">
                </td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Ends"); ?></td>
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
                <td class=widget_content_form_element><input type=checkbox name=activity_status value='on' <?php if ($activity_status == 'c') {print "checked";}; ?>></td>
            </tr>
            <tr>
                <td class=widget_content_form_element colspan=2>
                    <input class=button type=submit name="save" value="<?php echo _("Save Changes"); ?>">
                    <?php if($save_and_next) { ?>
                    <input class=button type=submit name="saveandnext" value="<?php echo _("Save and Next"); ?>">
                    <?php } ?>
                    <input class=button type=submit name="followup" value="<?php echo _("Schedule Followup"); ?>">

<?php
		confGoTo ( _('Delete Activity?'),
			   _('Delete'),
			   'delete.php?activity_id='.$activity_id.'&return_url='.urlencode($return_url));
?>
                </td>
            </tr>
        </table>
        </form>

    </div>

    <!-- right column //-->
    <div id="Sidebar">
        <!-- company information block //-->
        <?php echo $company_block; ?>
        <!-- contact information block //-->
        <?php echo $contact_block; ?>
        <!-- sidebar plugins //-->
        <?php echo $relationship_link_rows; ?>
    </div>

</div>

<script type="text/javascript">
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
 * Applied Patch [ 965012 ] Calendar replacement By: miguel Gonçves - mig77
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
 *   - fixes SF bug  949779 reported by miguel Gon�alves (mig77)
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
 *
 */
?>
