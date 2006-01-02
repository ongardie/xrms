<?php
/**
 * Call Detail Report
 *
 * Search for and View a list of calls
 *
 * $Id: cdr.php,v 1.2 2006/01/02 23:52:14 vanmer Exp $
 */

require_once('../../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');
require_once($include_directory . 'classes/Pager/GUP_Pager.php');
require_once($include_directory . 'classes/Pager/Pager_Columns.php');

// create session
$on_what_table='cdr';
$session_user_id = session_check();

// Start connection
$con = get_xrms_dbconnection();
// $con->debug = 1;

$page_title = _("Call Detail Report");
start_page($page_title, true, $msg);

if (check_object_permission_bool($_SESSION['session_user_id'], 'cti_cdr', 'Read')) {

    $arr_vars = array(
        'uniqueid'  => array('uniqueid',arr_vars_SESSION),
        'userfield'  => array('userfield',arr_vars_SESSION),
        'accountcode'  => array('accountcode',arr_vars_SESSION),
        'src'  => array('src',arr_vars_SESSION),
        'dst'  => array('dst',arr_vars_SESSION),
        'dcontext'  => array('dcontext',arr_vars_SESSION),
        'clid'  => array('clid',arr_vars_SESSION),
        'channel'  => array('channel',arr_vars_SESSION),
        'dstchannel'  => array('dstchannel',arr_vars_SESSION),
        'lastapp'  => array('lastapp',arr_vars_SESSION),
        'lastdata'  => array('lastdata',arr_vars_SESSION),
        'calldate'  => array('calldate',arr_vars_SESSION),
        'duration'  => array('duration',arr_vars_SESSION),
        'billsec'  => array('billsec',arr_vars_SESSION),
        'disposition'  => array('disposition',arr_vars_SESSION),
        'amaflags'  => array('amaflags',arr_vars_SESSION),
        'date_start'  => array('date_start',arr_vars_SESSION),
        'end_start'  => array('end_start',arr_vars_SESSION),
    );

// get all passed in variables
arr_vars_get_all ( $arr_vars );

// set all session variables
arr_vars_session_set ( $arr_vars );

    //Set up date start and end times
    if (!empty($date_start)) {
        preg_match('/[^0-9]/', $date_start,$ret);
        $ds = explode($ret[0],$date_start);
        if (strlen($ds[0]) == 4) {
            $date_start = strtotime("{$ds[1]}/{$ds[2]}/{$ds[0]} 00:00:00");
        } else {
            $date_start = strtotime("{$ds[0]}/{$ds[1]}/{$ds[2]} 00:00:00");
        }
    }

    if (!empty($date_end)) {
        preg_match('/[^0-9]/', $date_end,$ret);
        $de = explode($ret[0],$date_end);
        if (strlen($ds[0]) == 4) {
            $date_end = strtotime("{$de[1]}/{$de[2]}/{$de[0]} 11:59:59");
        } else {
            $date_end = strtotime("{$de[0]}/{$de[1]}/{$de[2]} 11:59:59");
        }
    }

    if ($date_start > $date_end) {
        $date_end = $date_start + 86400;
    }
    ?>

<?php jscalendar_includes(); ?>
<div id="Main">
    <div id=ContentFullScreen>
        <form action="cdr.php">
            <table class="widget" cellspacing="1" width=\"100%\">
                <tr>
                    <td class="widget_header" colspan="6">
                        <?php echo _("Search Criteria"); ?>
                    </td>
                </tr>
                <tr>
                    <td class="widget_label">
                        <?php echo _("Call ID"); ?>
                    </td>
                    <td class=widget_content>
                        <input type="text" name="uniqueid" value="<?php echo($uniqid); ?>">
                    </td>
                    <td class="widget_label">
                        <?php echo _("Source"); ?>
                    </td>
                    <td class=widget_content>
                        <input type="text" name="src" value="<?php echo($src); ?>">
                    </td>
                    <td class="widget_label">
                        <?php echo _("Destination"); ?>
                    </td>
                    <td class=widget_content>
                        <input type="text" name="dst" value="<?php echo($dst); ?>">
                    </td>
                </tr>
                <tr>
                    <td class="widget_label">
                        <?php echo _("Caller ID"); ?>                       
                    </td>
                    <td class="widget_content">
                        <input type="text" name="clid" value="<?php echo $clid; ?>">
                    </td>
                    <td class="widget_label">
                        <?php echo(_("Src Channel")) ?>
                    </td>
                    <td class=widget_content>
                        <input type="text" name="clid" value="<?php echo $channel; ?>">
                        <?php echo($channel); ?>
                    </td>
                    <td class="widget_label">
                        <?php echo(_("Dest Channel")); ?>
                    </td>
                    <td class=widget_content>
                        <input type="text" name="clid" value="<?php echo $dchannel; ?>">
                    </td>
                </tr>
                <tr>
                    <td class="widget_label">
                        <?php echo(_("Duration")); ?>                       
                    </td>
                    <td class="widget_content">
                        <input type="text" name="clid" value="<?php echo $duration; ?>">
                    </td>
                    <td class="widget_label">
                        <?php echo _("Start Date"); ?>
                    </td>
                    <td class=widget_content>
                        <input type=text ID="f_date_a" name="date_start" size=12 value="<?php echo $date_start; ?>">
                        <img ID="f_trigger_a" style="CURSOR: hand" border=0 src="../../img/cal.gif" alt="">
                    </td>
                    <td class="widget_label">
                        <?php echo _("End Date"); ?>
                    </td>
                    <td class=widget_content>
                        <input type=text ID="f_date_b" name="date_end" size=12 value="<?php echo $date_end; ?>">
                        <img ID="f_trigger_b" style="CURSOR: hand" border=0 src="../../img/cal.gif" alt="">
                    </td>
                </tr>
                <tr>
                    <td class="widget_content" colspan="6">
                        <input type="submit" class="button" name="sub" value="<?php echo(_("Search")); ?>">
                    </td>
                </tr>
            </table>
        </form>
<?php

add_audit_item($con, $session_user_id, 'viewed', 'cti_cdr', $account_id, 4);

$sql = "SELECT * FROM cdr";

$where = array();

if ($uniqueid) {
    $criteria_count++;
    $where .= "uniqueid = '" . $uniqueid . "' ";
}
if ($date_start) {
    $criteria_count++;
    $where .= "UNIX_TIMESTAMP(calldate) >= $date_start";
}
if ($date_end) {
    $criteria_count++;
    $where .= "UNIX_TIMESTAMP(calldate) <= $date_end";
}

if ($userfield) {
    $criteria_count++;
    $where .= "userfield = '$userfield'";
}

if ($accountcode) {
    $criteria_count++;
    $where .= "accountcode ='$accountcode'";
}

if ($src) {
    $criteria_count++;
    $where .= "src LIKE '%" . $src . "%'";
}

if ($dst) {
    $criteria_count++;
    $where .= "dst LIKE '%" . $dst . "%'";
}

if ($dcontext) {
    $criteria_count++;
    $where .= "dcontext = '$dcontext'";
}

if ($clid) {
    $criteria_count++;
    $where .= "clid LIKE '%" . $clid . "%'";
}

if ($channel) {
    $criteria_count++;
    $where .= "channel = '$channel'";
}

if ($dstchannel) {
    $criteria_count++;
    $where .= "dstchannel = '$dstchannel'";
}

if ($lastapp) {
    $criteria_count++;
    $where .= "lastapp = '$lastapp'";
}

if ($lastdata) {
    $criteria_count++;
    $where .= "lastdata = '$lastdata'";
}

if ($duration) {
    $criteria_count++;
    $where .= "duration = '$duration'";
}

if ($billsec) {
    $criteria_count++;
    $where .= "billsec = '$billsec'";
}

if ($disposition) {
    $criteria_count++;
    $where .= "disposition = '$disposition'";
}

if ($amaflags) {
    $criteria_count++;
    $where .= "amaflags = '$amaflags'";
}

if ($criteria_count) {
    $sql .= "WHERE " . $where;
}


$columns = array();
$columns[] = array('name' => _('Record #'), 'index_sql' => 'uniqueid');
$columns[] = array('name' => _('User Field'), 'index_sql' => 'userfield');
$columns[] = array('name' => _('Account Code'), 'index_sql' => 'accountcode');
$columns[] = array('name' => _('Src'), 'index_sql' => 'src');
$columns[] = array('name' => _('Dest'), 'index_sql' => 'dst');
$columns[] = array('name' => _('Dest Context'), 'index_sql' => 'dcontext');
$columns[] = array('name' => _('Caller ID'), 'index_sql' => 'clid');
$columns[] = array('name' => _('Src Channel'), 'index_sql' => 'channel');
$columns[] = array('name' => _('Dest Channel'), 'index_sql' => 'dstchannel');
$columns[] = array('name' => _('Last App'), 'index_sql' => 'lastapp');
$columns[] = array('name' => _('Last Data'), 'index_sql' => 'lastdata');
$columns[] = array('name' => _('Call Date'), 'index_sql' => 'calldate');
$columns[] = array('name' => _('Duration'), 'index_sql' => 'duration');
$columns[] = array('name' => _('Seconds Billed'), 'index_sql' => 'billsec');
$columns[] = array('name' => _('Duration'), 'index_sql' => 'duration');
$columns[] = array('name' => _('AMA Flags'), 'index_sql' => 'amaflags');


// selects the columns this user is interested in
$default_columns =  array();
$default_columns[] = 'calldate';
$default_columns[] = 'clid';
$default_columns[] = 'src';
$default_columns[] = 'dst';
$default_columns[] = 'duration';

$pager_columns = new Pager_Columns('CDRPager', $columns, $default_columns, 'CDRPager');
$pager_columns_button = $pager_columns->GetSelectableColumnsButton();
$pager_columns_selects = $pager_columns->GetSelectableColumnsWidget();

$columns = $pager_columns->GetUserColumns('default');

$disp_lbl_export = _('Export');

// caching is disabled for this pager (since it's all sql)
$pager = new GUP_Pager($con, $sql, null, _('Call Detail Records'), 'CDRPager', 'CDRPager', $columns, false);

//Attempting to start moving towards having all the html at the bottom
    ?>

        <form action=cdr.php class=print method=post name="CDRPager">
	        <input type=hidden name=use_post_vars value=1>
    <?php  echo $pager_columns_selects; ?>
    <?php
    $pager->AddEndRows("
        <tr>
            <td class=widget_content_form_element colspan=10>
                $pager_columns_button
                <input type=button class=button onclick=\"javascript: exportIt();\" value=\"$disp_lbl_export\">
            </td>
        </tr>");
    $pager->Render($system_rows_per_page);
    echo "  </form>";

?>
    
    </div>

<script language="JavaScript" type="text/javascript">

function bulkEmail() {
    document.forms[0].action = "../email/email.php";
    document.forms[0].submit();
}

function exportIt() {
    document.forms[0].action = "export.php";
    document.forms[0].submit();
    // reset the form so that post-export searches work
    document.forms[0].action = "some.php";
}

function clearSearchCriteria() {
    location.href = "cdr.php?clear=1";
}

Calendar.setup({
    inputField     :    "f_date_a",      // id of the input field
    ifFormat       :    "%Y-%m-%d",       // format of the input field
    showsTime      :    false,            // will display a time selector
    button         :    "f_trigger_a",   // trigger for the calendar (button ID)
    singleClick    :    false,           // double-click mode
    step           :    1,                // show all years in drop-down boxes (instead of every other year as default)
    align          :    "Bl"           // alignment (defaults to "Bl")
});

Calendar.setup({
    inputField     :    "f_date_b",      // id of the input field
    ifFormat       :    "%Y-%m-%d",       // format of the input field
    showsTime      :    false,            // will display a time selector
    button         :    "f_trigger_b",   // trigger for the calendar (button ID)
    singleClick    :    false,           // double-click mode
    step           :    1,                // show all years in drop-down boxes (instead of every other year as default)
    align          :    "Bl"           // alignment (defaults to "Bl")
});

</script>

<?php

} else {
    echo _("You do not have permission to access this report.");
}

$con->close();

end_page();

/**
 * $Log: cdr.php,v $
 * Revision 1.2  2006/01/02 23:52:14  vanmer
 * - changed to use centralized dbconnection function
 *
 * Revision 1.1  2005/05/11 18:07:00  gpowers
 *
 * Call Detail Report
 *
 */
?>
