<?php
/**
 *
 * /campaigns/lists/some.php - Manage campaign lists
 *
 * $Id: some.php,v 1.1 2011/01/14 15:51:28 gopherit Exp $
 */

// Where do we include from
require_once('../../include-locations.inc');

// Include necessary files
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

// Ensure we have a valid logged-in user
$session_user_id = session_check();

// Passed-in parameters
$campaign_id = (int)$_GET['campaign_id'];
$msg = $_GET['msg'];
$return_url = $_GET['return_url'];
if (!$return_url) $return_url = '/campaigns/some.php';

// Get the database connection
$con=get_xrms_dbconnection();
//$con->debug = 1;

// Get the campaign menu
$sql = "SELECT campaign_title, campaign_id
        FROM campaigns
        WHERE campaign_record_status = 'a'
        ORDER BY campaign_title";
$rst = $con->execute($sql);
if (!$rst)
    db_error_handler($con, $sql);
else
    $campaign_menu= $rst->getmenu2('campaign_id', $campaign_id, true, false, 1, "id=campaign_id onchange=javascript:restrictByType('campaign_id');");

// Retrieve the campaign lists
$sql = 'SELECT  cl.campaign_list_id,
                cl.user_id,
                cl.campaign_id,
                cl.campaign_list_title,
                cl.campaign_list_description,
                cl.list_created_on,
                cl.list_processing_started_on,
                c.campaign_title,
                cont.contact_id,
                cont.last_name,
                cont.first_names, '.
                $con->Concat($con->qstr('<a href="/contacts/one.php?contact_id='), 'cont.contact_id', $con->qstr('">'), 'cont.first_names', "' '", 'cont.last_name', $con->qstr('</a>')) . ' AS user_link
        FROM campaign_lists cl
        LEFT OUTER JOIN campaigns c ON cl.campaign_list_record_status = '. "'a'". '
            AND cl.campaign_id = c.campaign_id
        LEFT OUTER JOIN users u ON cl.user_id = u.user_id
        LEFT OUTER JOIN contacts cont ON u.user_contact_id = cont.contact_id
        WHERE cl.campaign_list_record_status = \'a\'';
if ($campaign_id)
    $sql .= " AND cl.campaign_id = $campaign_id";
$sql .= ' ORDER BY cl.list_created_on DESC;';

$rst = $con->execute($sql);

if ($rst) {
    $campaign_lists_rows = '';

    while (!$rst->EOF) {
        $campaign_lists_rows .= '<tr>
                                    <td class="widget_content">
                                        <a href="one.php?campaign_list_id='. $rst->fields['campaign_list_id'] .'&return_url='. $return_url .'">'. $rst->fields['campaign_list_title'] .'</a>
                                    </td>
                                    <td class="widget_content">
                                        '. $rst->fields['campaign_list_description'] .'
                                    </td>
                                    <td class="widget_content">';
        if ($rst->fields['campaign_id'])
            $campaign_lists_rows .= '<a href="../one.php?campaign_id='. $campaign_id .'&return_url='. $return_url .'">'. $rst->fields['campaign_title'] .'</a>';
        $campaign_lists_rows .= '   </td>
                                    <td class="widget_content">
                                        '. $rst->fields['user_link'] .'
                                    </td>
                                    <td class="widget_content">';
                                        $status_msgs = array();
                                        if ($rst->fields['list_processing_started_on'])
                                            $status_msgs[] = _('This campaign list has already been processed.');
                                        elseif ($rst->fields['list_processing_started_on'])
                                            $status_msgs[] = _('Processing of this campaign list has already been initiated.');
                                        if (!$rst->fields['campaign_id'])
                                            $status_msgs[] = '<span style="color: #FF6666;">'. _('List is not attached to a campaign.') .'</span>';
                                        if (!$rst->fields['user_id'])
                                            $status_msgs[] = '<span style="color: #FF6666;">'. _('List is not assigned to a user.') .'</span>';
                                        if ($rst->fields['user_id'] AND ($rst->fields['user_id'] != $session_user_id))
                                            $status_msgs[] = '<span style="color: #FF9933;">'. _('List assigned to another user.') .'</span>';
                                        foreach ($status_msgs as $status_msg) {
                                            $campaign_lists_rows .= $status_msg .'<br />';
                                        }

                                        if ($rst->fields['campaign_id'] AND $rst->fields['user_id'] AND !$rst->fields['list_processing_started_on'] AND ($rst->fields['user_id'] == $session_user_id))
                                                $campaign_lists_rows .= '<form method="post" action="launch-campaign-on-contact-list.php?campaign_list_id='. $rst->fields['campaign_list_id'] .'&return_url='. $return_url .'">
                                                                            <input type="submit" onclick="return (confirm(\''. _('You are about to launch the') .' '. $rst->fields['campaign_title']  .' '. _('campaign on every contact in the') .' '. $rst->fields['campaign_list_title']  .' '. _('list') .'.\\n\\n'. _('Do you wish to proceed') .'?\\n\\n\'));"
                                                                                                            "value="'. _('Launch Campaign on this List') .'" />
                                                                        </form>';

        $campaign_lists_rows .= '   </td>
                                    <td class="widget_content">
                                        <input type="button" onclick="javascript: if (confirm(\''. _('You are about to clone this list') .'.\\n\\n'. _('Do you wish to proceed') .'?\\n\\n\'))
                                                                            location.href=\'clone.php?campaign_list_id='. $rst->fields['campaign_list_id'] .'&return_url='. $return_url .'\'" value="'. _('Clone') .'">
                                        <input type="button" onclick="javascript: if (confirm(\''. _('You are about to delete this list') .'.\\n\\n'. _('Do you wish to proceed') .'?\\n\\n\'))
                                                                            location.href=\'delete.php?campaign_list_id='. $rst->fields['campaign_list_id'] .'&return_url='. $return_url .'\'" value="'. _('Delete') .'">
                                    </td>
                                </tr>';
        $rst->movenext();
    }
} else {
    db_error_handler ($con, $sql);
}

$page_title = _('Campaign Lists');
if ($campaign_id)
    $page_title .= ' '. _('for Campaign') .': '. $rst->fields['campaign_title'];

$con->close();

start_page($page_title, true, $msg);

?>

<script type="text/javascript" language="JavaScript">
<!--
    function restrictByType(selectName) {
        select=document.getElementById(selectName);
        destination = 'some.php';
        if (select.value) {
            destination = destination + '?' + selectName + '=' + select.value;
        }
        location.href = destination;
    }

    function phpCall(url) {
        location.href = url;
    }
//-->
</script>

<div id="Main">

    <table class="widget" cellspacing="1">
        <tr>
            <td class="widget_header" colspan="6"><?php echo _('Campaign Lists'); ?></td>
        </tr>

        <tr>
            <td class="widget_content" colspan="6">
                <?php echo _('Filter by Campaign') . $campaign_menu; ?>
            </td>
        </tr>

        <tr>
            <td class="widget_label"><?php echo _('Title'); ?></td>
            <td class="widget_label"><?php echo _('Description'); ?></td>
            <td class="widget_label"><?php echo _('Campaign'); ?></td>
            <td class="widget_label"><?php echo _('User'); ?></td>
            <td class="widget_label"><?php echo _('Ready Status'); ?></td>
            <td class="widget_label"><?php echo _('Options'); ?></td>
        </tr>

        <?php  echo $campaign_lists_rows; ?>

        <td class="widget_content_form_element" colspan="6">
            <input type="button" onclick="location.href='<?php echo $http_site_root.$return_url; ?>'" value="<?php echo _('Done'); ?>">
        </td>
    </table>
</div>

<?php
end_page();
 /**
  * $Log: some.php,v $
  * Revision 1.1  2011/01/14 15:51:28  gopherit
  * Implemented the Campaign Lists functionality to allow launching of campaign workflows on lists of contacts created with /contacts/some.php
  *
  *
  */
?>