<?php
/*
 * setup.php
 *
 * @author Francis Crossen, sponsored by Concept Information Consulting, Dublin, Ireland (http://www.conceptinfo.ie)
 *
 * @copyright Copyright &copy; 2007 The XRMS Development Team
 *
 * @license This plugin is distributed under the same license as XRMS. See http://www.xrms.org
 *
 * This plugin is sponsored by Concept Information Consulting, Dublin, Ireland (www.conceptinfo.ie)
 *
 * $Id: setup.php,v 1.1 2007/02/20 14:17:52 fcrossen Exp $
 */

/*
 * Plugin setup function
 *
 * Code should not be added here. Add/Edit code in case_hours or get_nulls_and_hours instead
 *
*/
function xrms_plugin_init_case_hours() {
    global $xrms_plugin_hooks;
    $xrms_plugin_hooks['case_one']['case_hours'] = 'case_hours_calc';
}

/*
 * This function contains the main plugin code. It generates the HTML for display of hours.
 *
 * @param   array   $params   passed by the hook call. We are only interested in $params[1]
 *                            which contains the case_id
 * @return  string            complete HTML for display
 *
*/
function case_hours_calc($params) {
    global $http_site_root;
    $con = get_xrms_dbconnection();
    // uncomment to debug sql calls
    //$con->debug = 1;
    //
    // get the case id...
    $case_id = $params[1];
    //
    // check that the activity types are set up...
    //
    if (!$hours_contract_rec = get_activity_type($con, 'H-C', 'Hours - Contract')) {
      $hours_contract_id = add_activity_type($con,'H-C','Hours - Contract','Hours - Contract','Hours - Contract',0,1,false);
    }
    if (!$hours_billable_rec = get_activity_type($con, 'H-B', 'Hours - Billable')) {
      $hours_billable_id = add_activity_type($con,'H-B','Hours - Billable','Hours - Billable','Hours - Billable',0,1,false);
    }
    if (!$hours_internal_rec = get_activity_type($con, 'H-I', 'Hours - Internal')) {
      $hours_internal_id = add_activity_type($con,'H-I','Hours - Internal','Hours - Internal','Hours - Internal',0,1,false);
    }
    //
    // warning text for NULLS. This is appended to eg: "2 -Hours Contract-" . $null_warning
    //
    $null_warning = " activities marked complete with no resolution type. This must be fixed to allow totals.";
    //
    // this is the base sql for totalling hours
    //
    $sql_base = "SELECT SEC_TO_TIME(SUM(TIME_TO_SEC(TIMEDIFF(ends_at,scheduled_at)))) AS hours FROM activities
            LEFT JOIN activity_resolution_types ON activities.activity_resolution_type_id = activity_resolution_types.activity_resolution_type_id
            WHERE activity_status='c' AND on_what_table='cases' AND on_what_id=$case_id
            AND activity_resolution_types.resolution_short_name <> 'Cancel'
            AND activity_resolution_types.resolution_short_name <> 'Duplicate'
            AND activity_resolution_types.resolution_short_name <> 'Obsolete' ";
    //
    // this is the base sql for checking for NULL resolution types...
    //
    $sql_base_check_nulls = "SELECT COUNT(activity_id) AS count_nulls FROM activities
                             WHERE activity_status='c' AND on_what_table='cases' AND on_what_id=$case_id AND
                             (activity_resolution_type_id IS NULL OR activity_resolution_type_id=0) ";

    //
    // get the billable hours
    //
    $extra_sql = "AND activities.activity_type_id=".$hours_billable_rec['activity_type_id'];
    $billable_hours = get_nulls_and_hours($sql_base, $sql_base_check_nulls, $extra_sql, $null_warning, '-Billable Hours-', $con);
    //
    // get the contract hours
    //
    $extra_sql = "AND activities.activity_type_id=".$hours_contract_rec['activity_type_id'];
    $contract_hours = get_nulls_and_hours($sql_base, $sql_base_check_nulls, $extra_sql, $null_warning, '-Contract Hours-', $con);
    //
    // get the internal hours
    //
    $extra_sql = "AND activities.activity_type_id=".$hours_internal_rec['activity_type_id'];
    $internal_hours = get_nulls_and_hours($sql_base, $sql_base_check_nulls, $extra_sql, $null_warning, '-Internal Hours-', $con);

    // open up a row...
    $sb_str = '<tr><td colspan="2">';
    // now for our table...
    $sb_str .= '<div class="hours_sidebar"><table>';
		$sb_str .= '<tr><th colspan="2" class=widget_header align="left">'. _("Completed Case Hours ") . '</th></tr>';
		$sb_str .= '<tr><td colspan="2" class="widget_content"><i>Open, Closed/Duplicate, Obsolete/Out of Date and Cancelled activities are excluded from totals.</i></td></tr>';
    $sb_str .= '<tr><td class="widget_content">Hours - Billable</td><td class="widget_content" align="right">'.$billable_hours.'</td></tr>';
    $sb_str .= '<tr><td class="widget_content">Hours - Contract</td><td class="widget_content" align="right">'.$contract_hours.'</td></tr>';
    $sb_str .= '<tr><td class="widget_content">Hours - Internal</td><td class="widget_content" align="right">'.$internal_hours.'</td></tr>';
    $sb_str .= '</table></div>';
    // close everything off
    $sb_str .= '</td></tr>';
    echo $sb_str;
}

/*
 * Function used by case_hours to
 * 1) check for NULLs in activities.activity_resolution_type_id. The NULL value prevents us from
 *    SELECTing the records we want to total.
 * 2) SELECT the matching activities and total the time spent on each activity
 * The function will return a string:
 * 1) If there are NULL values, a warning message
 * 2) else the total tuime for that activity type
 *
 * @param   string    $sql_base     SELECT SQL to total the hours spent on an activity type
 * @param   string    $sql_nulls    SELECT SQL to total the number of activities of a particular type containing
 *                                  NULLs for activity_resolution_type_id
 * @param   string    $null_warning string containing HTML for display instead of the number of hours
 *                                  when NULLs are found for activity_resolution_type_id. This is pre-pended by the
 *                                  number of NULLs, the type of hour being totaled and  wrapped by a A tag
 * @param   string    $hour_type    The type of hour being totaled
 * @param   AdodbConnection $con    An ADODB connection
 * @return  string    complete HTML for display
 *
*/
function get_nulls_and_hours($sql_base, $sql_nulls, $sql_extra_where, $null_warning, $hour_type, & $con) {
    // check for billable hours activities with NULL resolution types (we cannot total NULLS)
    $sql = $sql_nulls . $sql_extra_where;
    $rst = $con->execute($sql);
    if ($rst) {
        $nulls = $rst->fields['count_nulls'];
        $rst->close();
    } else {
       db_error_handler ($con,$sql);
    }
    if ($nulls) {
      $nulls = "<a href=\"#\" onmouseover=\"return escape('$nulls $hour_type $null_warning')\">$nulls NULLS</a>";
    }
    else {
      $sql = $sql_base . $sql_extra_where;
      $rst = $con->execute($sql);
      if ($rst) {
          $hours = $rst->fields['hours'];
          $rst->close();
      } else {
         db_error_handler ($con,$sql);
      }
    }
    $hours = ($nulls) ? $nulls : $hours;
    return $hours;
}

/**
 * $Log: setup.php,v $
 * Revision 1.1  2007/02/20 14:17:52  fcrossen
 * - initial revision
 *
 *
 */
?>
