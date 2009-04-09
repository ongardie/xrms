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
GetGlobalVar($activity_id,'activity_id');
GetGlobalVar($on_what_entity,'on_what_entity');
getGlobalVar($on_what_table, 'on_what_table');
GetGlobalVar($on_what_id, 'on_what_id');
getGlobalVar($restrict_string, 'restrict_string');
getGlobalVar($restrict_string1, 'restrict_string1');
getGlobalVar($reconnect_action,'reconnect_action');


$con = get_xrms_dbconnection();
$con->debug=0;

if (!$on_what_entity) {
    $possible_types=array( 'companies' => _("Companies"),
                                            'opportunities' => _("Opportunities"),
                                            'contacts'=>_("Contacts"),
                                            'cases' => _("Cases"),
                                            'campaigns' => _("Campaigns")//,
                       //                     'company_division' => _("Divisions")
                                           );
        asort($possible_types);
        $on_what_table_select = create_select_from_array($possible_types, 'on_what_table', $on_what_table, '', true);
}

$search_button=_("Search");
$search_title=_("Search Entities");
$for_label=_("Search For");
$restrict_label=_("Containing");
$restrict_label1=_("Limit to Company");

                    if ($on_what_entity == 'company') {
                        $body_entity .= "Company";
                        $on_what_table = 'companies';
                        // $reconnect_action = 'showSelectEntity';
                    } elseif ($on_what_entity == 'contact') {
                        $body_entity .= "Contact";
                        $on_what_table = 'contacts';
                        // $reconnect_action = 'showSelectEntity';
                    } else {
                        $body_entity .= $on_what_table_select;
                    }

$body_content = "
        <form action=\"activity-reconnect.php\" name=searchNewEntity method=POST>
            <input type=hidden name=reconnect_action value=showSelectEntity>
            <input type=hidden name=activity_id value=\"$activity_id\">
            <input type=hidden name=on_what_entity value=\"$on_what_entity\">
            <input type=hidden name=on_what_table value=\"$on_what_table\">
            <input type=hidden name=on_what_id value=\"$on_what_id\">

            <table class=widget cellspacing=1>
                <tr>
                    <td class=widget_header colspan=2>
                        $search_title
                    </td>
                </tr>
                <tr>
                    <td class=widget_label>
                        $for_label
                    </td>
                    <td class=widget_content_form_element>         
                        $body_entity
                    </td>
                </tr>
                <tr>
                    <td class=widget_label>
                        $restrict_label
                    </td>
                    <td class=widget_content_form_element>
                        <input type=text name=restrict_string value=$restrict_string>
                    </td>
                </tr>
                <tr>
                    <td class=widget_label>
                        $restrict_label1
                    </td>
                    <td class=widget_content_form_element>
                        <input type=text name=restrict_string1 value=$restrict_string1>
                    </td>
                </tr>
                <tr>
                    <td class=widget_content_form_element colspan=2>
                        <input type=submit class=button name=btSearch value=\"$search_button\">
                    </td>
                </tr>
            </table>
</form>";

switch ($reconnect_action) {
    case 'showTypeSearch':
    default:
        $page_title="Re-Attach Activity";
    break;
    case 'showSelectEntity':
        require_once ($include_directory . 'classes/Pager/Pager_Columns.php');
        require_once ($include_directory . 'classes/Pager/GUP_Pager.php');
        if (!$on_what_table) {
            $msg=urlencode(_("Please select an entity type to search for"));
            Header("Location: activity-reconnect.php?activity_id=$activity_id&reconnect_action=showTypeSearch&msg=$msg&on_what_entity=$on_what_entity");
            exit();
        }
        $page_title=_("Select Entity");
        $entity_singular= make_singular($on_what_table);
        $on_what_field = $entity_singular.'_id';
        $on_what_name_field=$con->Concat(implode(", ' ' , ", table_name($on_what_table)));
        $sql = "SELECT *, $on_what_name_field as on_what_name FROM $on_what_table WHERE $on_what_name_field LIKE ". $con->qstr("%$restrict_string%");

        if (($restrict_string1)&&($on_what_table <> 'campaigns')) {
           $sql .= " AND company_id IN (SELECT company_id FROM companies WHERE company_name LIKE ". $con->qstr("%$restrict_string1%") . ")";
        }

        $columns=array();
        $columns[] = array('name' => _("Select"), 'index_sql' => 'on_what_id', 'sql_sort_column' => $on_what_field);
        $columns[] = array('name' => _("Name"), 'index_sql' => 'on_what_name');

        $default_columns=array('on_what_id',$on_what_field);

        $pager = new GUP_Pager($con, $sql, 'GetEntityPagerData', _("Reconnect To"). ' ' . ucfirst($entity_singular), 'EntityPagerForm', 'EntityPager', $columns, false, true);
    $endrows = "<tr><td class=widget_content_form_element colspan=10><input class=button type=button onclick=\"document.EntityPagerForm.reconnect_action.value='reconnectActivity'; document.EntityPagerForm.submit();\" value=\"" . _("Reconnect Activity") . "\"></td></tr>";

        $pager->AddEndRows($endrows);

        global $system_rows_per_page;
        $entity_pager = $pager->Render($system_rows_per_page);
        $body_content .=<<<TILLEND
        <p><form action="activity-reconnect.php" name=EntityPagerForm method=POST>
            <input type=hidden name=reconnect_action value="reconnectActivity">
            <input type=hidden name=on_what_table value="$on_what_table">
            <input type=hidden name=restrict_string value="$restrict_string">
            <input type=hidden name=restrict_string1 value="$restrict_string1">
            <input type=hidden name=activity_id value="$activity_id">
            <input type=hidden name=on_what_entity value="$on_what_entity">
            $entity_pager
        </form>
TILLEND;
    break;
    case 'reconnectActivity':
    $rec=array();
        if ($on_what_entity == 'company' ) {
            $rec['company_id']=$on_what_id;            
        } elseif ($on_what_entity == 'contact') {
            $rec['contact_id']=$on_what_id;
        } elseif ($on_what_table AND $on_what_id) {
            $rec['on_what_table']=$on_what_table;
            $rec['on_what_id']=$on_what_id;            
        } else {
            $msg=urlencode(_("Please select an entity"));
            $restrict_string=urlencode($restrict_string);
            $return_url="activity-reconnect.php?reconnect_action=showSelectEntity&restrict_string=$restrict_string&on_what_table=$on_what_table&activity_id=$activity_id&on_what_entity=$on_what_entity&msg=$msg";
            header("Location: $return_url");
            exit;
        }
            $ret=update_activity($con, $rec, $activity_id);
            if ($ret) {
                $msg=urlencode(_("Successfully re-attached activity"));
                $return_url="$http_site_root/activities/one.php?activity_id=$activity_id&msg=$msg";
            } else {
                $msg=urlencode(_("Failed to update activity"));
                $return_url="$http_site_root/activities/one.php?activity_id=$activity_id&msg=$msg";
            }
        header("Location: $return_url");
        exit;
    break;
}


start_page($page_title, true, $msg);
?>

<div id="Main">
    <div id="Sidebar">
        &nbsp;
    </div>
    <div id="Content">
        <?php echo $body_content; ?>
    </div>
</div>
<?php
end_page();

function GetEntityPagerData($row) {
    global $on_what_field;
    $row['on_what_id']="<input type=radio name=on_what_id value={$row[$on_what_field]}>";
//    print_r($row);
    return $row;
}

/**
 * $Log: activity-reconnect.php,v $
 * Revision 1.6  2009/04/09 06:09:13  gopherit
 * Missing <div id="Main"> closure on line 198
 *
 * Revision 1.5  2008/01/30 21:10:06  gpowers
 * - added functionality to transfer activities to other entities. worksforme
 *
 * Revision 1.4  2006/09/30 18:12:09  braverock
 * - patch 2006/07/31 from  dbaudone
 *  -- added code to display company name along with contact name
 *
 * Revision 1.3  2006/01/02 21:23:18  vanmer
 * - changed to use centralized database connection function
 *
 * Revision 1.2  2005/07/08 01:08:05  vanmer
 * - added contacts as possibly type to connect to
 * - sorting list alphabetically
 * - added translations for types
 *
 * Revision 1.1  2005/07/08 00:51:41  vanmer
 * -Initial revision of subapplication to reconnect activities to new entity
 **/
?>