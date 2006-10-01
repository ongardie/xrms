<?php
/**
* bulk-activity-widget.php
*
*
* @author Daniele Baudone <d.baudone@virgilio.it>
*
* $Id: bulk-activities-widget.php,v 1.1 2006/10/01 00:15:06 braverock Exp $
*/



function GetBulkActivityWidget($con, $session_user_id, $return_url, $on_what_table, $on_what_id, $company_id, $contact_id, $scope='') {

global $http_site_root;

$form_name = 'NewActivity';

if(!$company_id) $company_id = 0;

// create menu of users
$user_menu = get_user_menu($con, $session_user_id);

$activity_type_menu=get_activity_type_menu($con);

$sql2 = "select campaign_title, campaign_id from campaigns, campaign_statuses
         where campaign_record_status = 'a' and
         campaign_statuses.campaign_status_id = campaigns.campaign_status_id and
         campaign_statuses.status_open_indicator = 'o'
         order by campaign_title";
$rst = $con->execute($sql2);
if ( $rst && !$rst->EOF ) {
   $campaign_id = $rst->fields['campaign_id'];
} else {
   $campaign_id = '';
}
$campaign_menu = $rst->getmenu('campaign_id', $campaign_id, true, false, 0, 'style="font-size: x-small; height: 20px; width: 140px"');
$rst->close();


$hidden = '';

if($on_what_table && $on_what_id) {
    $hidden .= "<input type=hidden name=on_what_table value=\"$on_what_table\">";
    $hidden .= "<input type=hidden name=on_what_id value=\"$on_what_id\">";
}

$hidden .= "<input type=hidden name=company_id value=\"$company_id\">";


$ret = "
<script language=\"JavaScript\" type=\"text/javascript\">
<!--
function markComplete() {
    document.$form_name.activity_status.value = \"c\";
    document.$form_name.submit();
}
//-->
</script>


        <!-- activities //-->
        <form name=\"$form_name\" action=\"$http_site_root/bulkactivity/bulkactivity.php\" method=post>
        <input type=hidden name=return_url value=\"$return_url\">
        <input type=hidden name=scope value=\"$scope\">

        $hidden
        <input type=hidden name=activity_status value=\"o\">
        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=5>". _("New Activity") . "</td>
            </tr>
            <tr>
                <td class=widget_label>" . _("Summary") . "</td>
                <td class=widget_label>" . _("User") . "</td>
                <td class=widget_label>" . _("Type") . "</td>
                <td class=widget_label>" . _("Campaign") . "</td>
                <td class=widget_label>" . _("Scheduled End") . "</td>
            </tr>
            <tr>
                <td class=widget_content_form_element><input type=text name=activity_title></td>
                <td class=widget_content_form_element>$user_menu</td>
                <td class=widget_content_form_element>$activity_type_menu</td>
                <td class=widget_content_form_element>$campaign_menu</td>
                <td class=widget_content_form_element>
                    <input type=text ID=\"f_date_new_activity\" name=ends_at size=12 value=\"" . date('Y-m-d') . "\">
                    <img ID=\"f_trigger_new_activity\" style=\"CURSOR: hand\" border=0 src=\"../img/cal.gif\">" .
                    render_create_button(_("Continue")) . "
                </td>

                </tr>

        </table>
        </form>
        <script language=\"JavaScript\" type=\"text/javascript\">
            Calendar.setup({
                    inputField     :    \"f_date_new_activity\",      // id of the input field
                    ifFormat       :    \"%Y-%m-%d\",       // format of the input field
                    showsTime      :    false,            // will display a time selector
                    button         :    \"f_trigger_new_activity\",   // trigger for the calendar (button ID)
                    singleClick    :    false,           // double-click mode
                    step           :    1,                // show all years in drop-down boxes (instead of every other year as default)
                    align          :    \"Bl\"           // alignment (defaults to \"Bl\")
            });
        </script>
";

return $ret;
}

 /**
  * $Log: bulk-activities-widget.php,v $
  * Revision 1.1  2006/10/01 00:15:06  braverock
  * - Initial Revision of Bulk Activity and Bulk Assignment contributed by Danielle Baudone
  *
  * Revision 1.0  2006/01/15 01:18:00  dbaudone
  */
?>