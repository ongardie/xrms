<?php
/**
 * Details about one item
 *
 * $Id: one.php,v 1.10 2005/01/11 19:43:47 gpowers Exp $
 *
 */

//include required files
require_once('../../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

require_once('info.inc');

$session_user_id = session_check();

$msg = $_GET['msg'];

# Always retrieve, and pass on, server and company ID
$info_id = $_GET['info_id'];
$info_type_id = $_GET['info_type_id'];
$company_id = $_GET['company_id'];
global $http_site_root;

$return_url = urlencode("one.php?info_id=" . $info_id . "&info_type_id=" . $info_type_id . "&company_id=" . $company_id);

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
//$con->debug = 1;

$sql = "SELECT info_type_id FROM info_map WHERE info_id = $info_id";
$rst = $con->execute($sql);
if ($rst) {
    if (!$rst->EOF) {
        $info_type_id = $rst->fields['info_type_id'];
    }
}

$sql = "SELECT info_type_name FROM info_types WHERE info_type_id = $info_type_id";
$rst = $con->execute($sql);
if ($rst) {
    if (!$rst->EOF) {
        $info_type_name = $rst->fields['info_type_name'];
    }
}

# Get details of all defined elements
$sql = "SELECT info_element_definitions.* FROM info_element_definitions ";
$sql .= "WHERE info_element_definitions.element_enabled=1 ";
$sql .= "AND info_element_definitions.info_type_id=$info_type_id ";
$sql .= "ORDER BY element_column, element_order";
$all_elements = $con->execute($sql);
if (!$all_elements) {
  db_error_handler ($con, $sql);
  exit;
}

# Get details of all elements for this server
$sql2 = "SELECT value, element_id FROM info WHERE info_id='$info_id'";
$rst = $con->execute($sql2);
if (!$rst) {
  db_error_handler ($con, $sql);
  exit;
}

# Build an array of this server's elements indexed by element_id
$this_server = array();
if(!$rst->EOF) {
    while (!$rst->EOF) {
        $this_server[$rst->fields['element_id']] = $rst->fields['value'];
        $rst->movenext();
    }
}

# Build output, one array per column of display
# Step through each defined element and get the value
# for it for this server
$data = array();
while (!$all_elements->EOF) {
  $element_id = $all_elements->fields['element_id'];
  $column = $all_elements->fields['element_column'];

  # If this server doesn't have this element defined, use default value
  $value = (array_key_exists($element_id, $this_server)) ?
    $this_server[$element_id] : $all_elements->fields['element_default_value'];

  # Use words for checkbox status
  if ($all_elements->fields['element_type'] == "checkbox") {
    $print_value = (1 == $value) ? $checkbox_set : $checkbox_clear;
  }
  else {
    $print_value = $value;
  }
  $data[$column] .= "<tr>\n";
  $data[$column] .= "\t<td class=sublabel>".$all_elements->fields['element_label']."</td>\n";
  $data[$column] .= "\t<td class=clear>".$print_value."</td>\n";
  $data[$column] .= "</tr>\n";
  if ($all_elements->fields['element_label'] == 'Name') {
    $item_name = $print_value;
  }
  $all_elements->movenext();
}

# Calculate width of each column
$pcent = (count($data)>0) ? round(100/count($data)) : 100;
$column_width = $pcent."%";

# Retrieve the name of the company owning this server
$sql = "SELECT company_name FROM companies WHERE company_id=$company_id";
$company_info = $con->execute($sql);
if (!$company_info) {
  db_error_handler ($con, $sql);
  exit;
}
if ($company_info) {
    $company_name = $company_info->fields['company_name'];
}

# Most of remainder of page from companies/one.php
//
//  list of most recent activities
//

$sql_activities = "select activity_id,
activity_title,
scheduled_at,
on_what_table,
on_what_id,
a.entered_at,
activity_status,
at.activity_type_pretty_name,
cont.first_names as contact_first_names,
cont.last_name as contact_last_name,
u.username,
if(activity_status = 'o' and ends_at < now(), 1, 0) as is_overdue
from activity_types at, users u, activities a left join contacts cont on a.contact_id = cont.contact_id
where a.company_id = $company_id
and a.on_what_table = 'info'
and a.on_what_id = $info_id
and a.user_id = u.user_id
and a.activity_type_id = at.activity_type_id
and a.activity_record_status = 'a'
order by is_overdue desc, a.scheduled_at desc, a.entered_at desc";

$all_elements = $con->selectlimit($sql_activities, $display_how_many_activities_on_company_page);

if ($all_elements) {
    while (!$all_elements->EOF) {

        $open_p = $all_elements->fields['activity_status'];
        $scheduled_at = $all_elements->unixtimestamp($all_elements->fields['scheduled_at']);
        $is_overdue = $all_elements->fields['is_overdue'];
        $on_what_table = $all_elements->fields['on_what_table'];
        $on_what_id = $all_elements->fields['on_what_id'];

        if ($open_p == 'o') {
            if ($is_overdue) {
                $classname = 'overdue_activity';
            } else {
                $classname = 'open_activity';
            }
        } else {
            $classname = 'closed_activity';
        }

        if ($on_what_table == 'opportunities') {
            $attached_to_link = "<a href='$http_site_root/opportunities/one.php?opportunity_id=$on_what_id'>";
            $sql2 = "select opportunity_title as attached_to_name from opportunities where opportunity_id = $on_what_id";
        } elseif ($on_what_table == 'cases') {
            $attached_to_link = "<a href='$http_site_root/cases/one.php?case_id=$on_what_id'>";
            $sql2 = "select case_title as attached_to_name from cases where case_id = $on_what_id";
        } else {
            $attached_to_link = "N/A";
            $sql2 = "select * from companies where 1 = 2";
        }

        $rst = $con->execute($sql2);

        if ($all_elements) {
            $attached_to_name = $rst->fields['attached_to_name'];
            $attached_to_link .= $attached_to_name . "</a>";
            $rst->close();
        }

        $return_url = urlencode("one.php?info_id=$info_id&info_type_id=$info_type_id&company_id=$company_id");
        $activity_rows .= '<tr>';
        $activity_rows .= "<td class='$classname'><a href='$http_site_root/activities/one.php?company_id=$company_id&activity_id=" . $all_elements->fields['activity_id'] . "&return_url=$return_url'>" . $all_elements->fields['activity_title'] . '</a></td>';
        $activity_rows .= '<td class=' . $classname . '>' . $all_elements->fields['username'] . '</td>';
        $activity_rows .= '<td class=' . $classname . '>' . $all_elements->fields['activity_type_pretty_name'] . '</td>';
        $activity_rows .= '<td class=' . $classname . '>' . $all_elements->fields['contact_first_names'] . ' ' . $all_elements->fields['contact_last_name'] . '</td>';
        $activity_rows .= '<td class=' . $classname . ">$attached_to_link</td>";
        $activity_rows .= '<td class=' . $classname . '>' . $con->userdate($all_elements->fields['scheduled_at']) . '</td>';
        $activity_rows .= '</tr>';
        $all_elements->movenext();
    }
    $all_elements->close();
}

// associated with

$categories_sql = "select category_display_html
from categories c, category_scopes cs, category_category_scope_map ccsm, entity_category_map ecm
where ecm.on_what_table = 'companies'
and ecm.on_what_id = $company_id
and ecm.category_id = c.category_id
and cs.category_scope_id = ccsm.category_scope_id
and c.category_id = ccsm.category_id
and cs.on_what_table = 'companies'
and category_record_status = 'a'
order by category_display_html";

$all_elements = $con->execute($categories_sql);
$categories = array();

if ($all_elements) {
    while (!$all_elements->EOF) {
        array_push($categories, $all_elements->fields['category_display_html']);
        $all_elements->movenext();
    }
    $all_elements->close();
}

$categories = implode($categories, ", ");

/*********************************/
/*** Include the sidebar boxes ***/

//set up our substitution variables for use in the siddebars
$on_what_table = 'info';
$on_what_id = $company_id;

//include the Cases sidebar
$case_limit_sql = "and cases." . make_singular($on_what_table) . "_id = $on_what_id";
require_once("$xrms_file_root/cases/sidebar.php");

//include the files sidebar
require_once("$xrms_file_root/files/sidebar.php");

//include the notes sidebar
require_once("$xrms_file_root/notes/sidebar.php");

//call the sidebar hook
$sidebar_rows = do_hook_function('company_sidebar_bottom');

/** End of the sidebar includes **/
/*********************************/

$sql = "select concat(first_names, ' ', last_name) as contact_name, contact_id from contacts where company_id = $company_id and contact_record_status = 'a'";
$all_elements = $con->execute($sql);
if ($all_elements) {
    $contact_menu = $all_elements->getmenu2('contact_id', '', true);
    $all_elements->close();
}

$sql = "select username, user_id from users where user_record_status = 'a' order by username";
$all_elements = $con->execute($sql);
$user_menu = $all_elements->getmenu2('user_id', $session_user_id, false);
$all_elements->close();

$sql = "select activity_type_pretty_name, activity_type_id from activity_types where activity_type_record_status = 'a' order by activity_type_pretty_name";
$all_elements = $con->execute($sql);
$activity_type_menu = $all_elements->getmenu2('activity_type_id', '', false);
$all_elements->close();

$con->close();

if (strlen($activity_rows) == 0) {
    $activity_rows = "<tr><td class=widget_content colspan=7>No activities</td></tr>";
}

if (strlen($contact_rows) == 0) {
    $contact_rows = "<tr><td class=widget_content colspan=6>$strCompaniesOneNoContactsMessage</td></tr>";
}

if (strlen($former_name_rows) == 0) {
    $former_name_rows = "";
}

if (strlen($relationship_rows) == 0) {
    $relationship_rows = "";
}

if (strlen($categories) == 0) {
    $categories = $strCompaniesOneNoCategoriesMessage;
}

$page_title = $company_name . ": " . $info_type_name . ": " . $item_name;
start_page($page_title, true, $msg);


?>

<script language="JavaScript" type="text/javascript">
<!--
function markComplete() {
    document.forms[0].activity_status.value = "c";
    document.forms[0].submit();
}

function openNewsWindow() {
    window_url = "http://news.google.com/news?q=%22<?php  echo str_replace(' ', '+', $company_name); ?>%22";
    window_name = "News";
    window_attr = "";
    window.open(window_url, window_name, window_attr);
}

//-->
</script>

<div id="Main">
  <div id="Content">
    <table class=widget cellspacing=1>
      <tr>
        <td class=widget_header>
          <?php echo _("Details"); ?>
        </td>
      </tr>
      <tr>
        <td class=widget_content>
          <table border=0 cellpadding=0 cellspacing=0 width=100%>
            <tr>
              <?php foreach ($data as $column) { ?>
                <td width=<?php echo $column_width ?> class=clear align=left valign=top>
                  <table border=0 cellpadding=0 cellspacing=0 width=100%>
                    <?php echo $column; ?>
                  </table>
                </td>
              <?php } ?>
            </tr>
          </table>
          <p><?php  echo $profile; ?>
        </td>
      </tr>
      <tr>
        <td class=widget_content_form_element>
<?php $return_url = urlencode("one.php?info_id=" . $info_id . "&info_type_id=" . $info_type_id . "&company_id=" . $company_id);?>
          <input class=button type=button value="<?php echo _("Edit"); ?>"
            onclick="javascript: location.href='<?php echo "edit.php?info_id=$info_id&info_type_id=$info_type_id&company_id=$company_id&return_url=$return_url"; ?>';">
          <input class=button type=button value="<?php echo _("Delete"); ?>"
            onclick="javascript: location.href='<?php echo "delete-2.php?info_id=$info_id&info_type_id=$info_type_id&company_id=$company_id&return_url=$return_url"; ?>';">
          <input class=button type=button value="<?php echo _("New"); ?>"
            onclick="javascript: location.href='<?php echo "edit.php?info_id=0&info_type_id=$info_type_id&company_id=$company_id&return_url=$return_url"; ?>';">
          <input class=button type=button value="<?php echo _("Back"); ?>"
            onclick="javascript: location.href='<?php echo "../../companies/one.php?company_id=$company_id&division_id=$division_id"; ?>';">
        </td>
      </tr>
    </table>
        <?php jscalendar_includes(); ?>
        <!-- activities //-->
        <form action="<?php  echo $http_site_root; ?>/activities/new-2.php" method=post>
        <input type=hidden name=return_url value="<?php echo $return_url; ?>">
        <input type=hidden name=company_id value="<?php echo $company_id; ?>">
        <input type=hidden name=activity_status value="o">
        <input type=hidden name=on_what_table value="info">
        <input type=hidden name=on_what_id value="<?php echo $info_id; ?>">
        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=7><?php  echo _("Activities"); ?></td>
            </tr>
            <tr>
                <td class=widget_label><?php echo _("Title"); ?></td>
                <td class=widget_label><?php echo _("User"); ?></td>
                <td class=widget_label><?php echo _("Type"); ?></td>
                <td class=widget_label><?php echo _("Contact"); ?></td>
                <td class=widget_label><?php echo _("About"); ?></td>
                <td colspan=2 class=widget_label><?php echo _("Starts"); ?></td>
            </tr>
            <tr>
                <td class=widget_content_form_element><input type=text name=activity_title></td>
                <td class=widget_content_form_element><?php  echo $user_menu; ?></td>
                <td class=widget_content_form_element><?php  echo $activity_type_menu; ?></td>
                <td class=widget_content_form_element><?php  echo $contact_menu; ?></td>
                <td class=widget_content_form_element>&nbsp;</td>
                <td colspan=2 class=widget_content_form_element>
                    <input type=text ID="f_date_d" name=scheduled_at value="<?php  echo date('Y-m-d H:i:s'); ?>">
                    <img ID="f_trigger_d" style="CURSOR: hand" border=0 src="../img/cal.gif">
                    <input class=button type=submit value="Add">
                    <input class=button type=button onclick="javascript: markComplete();" value="Done">
                </td>
            </tr>
            <?php  echo $activity_rows; ?>
        </table>
        </form>
    </div>

        <!-- right column //-->
    <div id="Sidebar">

        <!-- categories //-->
        <div id='category_sidebar'>
        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header><?php  echo _("Categories"); ?></td>
            </tr>
            <tr>
                <td class=widget_content><?php  echo $categories; ?></td>
            </tr>
            <tr>
                <td class=widget_content_form_element><input type=button class=button onclick="javascript: location.href='categories.php?company_id=<?php  echo $company_id; ?>';" value="Manage"></td>
            </tr>
        </table>
        </div>

        <!-- cases //-->
        <?php echo $case_rows; ?>

        <!-- notes //-->
        <?php echo $note_rows; ?>

        <!-- files //-->
        <?php echo $file_rows; ?>

        <!-- sidebar plugins //-->
        <?php echo $sidebar_rows; ?>

    </div>

</div>

<script>
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

?>
