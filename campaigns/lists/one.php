<?php
/**
 *
 * /campaigns/lists/one.php - Used to create and edit campaign lists
 *
 * $Id: one.php,v 1.1 2011/01/14 15:51:28 gopherit Exp $
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
$campaign_list_id = (int)$_GET['campaign_list_id'];
$return_url = $_GET['return_url'];
if ($campaign_list_id)
    // If we have a campaign_list_id, we came from /campaigns/lists/some.php
    $go_back_to_url = "$http_site_root/campaigns/lists/some.php?return_url=$return_url";
else
    // otherwise we came from somewhere else so let's go back there
    $go_back_to_url = $http_site_root . $return_url;
$msg = $_GET['msg'];

// Get the database connection
$con=get_xrms_dbconnection();
//$con->debug = 1;


////////// Figure out what we need to do //////////

// If all we have is an SQL, this is the first pass in creating a new campaign list
if ( (isset($_SESSION['search_sql'])) AND (!$campaign_list_id) AND (!$_POST['array_of_contacts']) ) {
    // Query for the array of contacts
    $array_of_contacts = array();
    $sql = $_SESSION['search_sql'];
    $rst = $con->execute($sql);
    if ($rst) {
        while (!$rst->EOF) {
            $array_of_contacts[] = $rst->fields['contact_id'];
            $rst->movenext();
        }
    } else {
        db_error_handler ($con, $sql);
    }
    if (count($array_of_contacts) < 1)
        $array_of_contacts = FALSE;


// If we have a campaign_list_id and no POST-ed data, this is the first pass in
// editing an existing campaign list
} elseif ( ($campaign_list_id) AND (!$_POST['array_of_contacts']) ) {
    // Retrieve an existing array of contacts
    $array_of_contacts = array();
    $sql = "SELECT user_id,
                   campaign_id,
                   campaign_list_title,
                   campaign_list_description,
                   target_contact_ids,
                   list_processing_started_on AS read_only
            FROM campaign_lists
            WHERE campaign_list_id = $campaign_list_id
                AND campaign_list_record_status = 'a'
            LIMIT 1";
    $rst = $con->execute($sql);
    if ($rst) {
        if (!$rst->EOF)
            extract($rst->fields);
            $array_of_contacts = explode(",", $target_contact_ids);
    } else {
        db_error_handler ($con, $sql);
    }
    
    if ( (is_array($array_of_contacts)) AND (count($array_of_contacts) < 1) )
        $array_of_contacts = FALSE;

// Otherwise, this is the second pass in either creating or editing
// a campaign list
} else {
    ///// Validate POST-ed data /////

    // Validate the user_id or default to the logged-in user
    $user_id = (int)$_POST['user_id'];

    // Sanitize the campaign_id
    $campaign_id = (int)$_POST['campaign_id'];

    // Validate the campaign_list_title
    $campaign_list_title = ($_POST['campaign_list_title']) ? $_POST['campaign_list_title'] : _('Default Campaign List Title');

    // Validate the array of contacts
    if (is_array($_POST['array_of_contacts']) AND (count($_POST['array_of_contacts']) > 0)) {
        $array_of_contacts = $_POST['array_of_contacts'];
    } else {
        $array_of_contacts = FALSE;
        $msg = _('No Array of Contacts');
    }

    // Make sure the user has selected at least one contact
    if (is_array($_POST['selected_contacts']) AND (count($_POST['selected_contacts']) > 0)) {
        $selected_contacts = $_POST['selected_contacts'];
    } else {
        $selected_contacts = FALSE;
        $msg = _('No Contacts Selected');

    }

}

// If we have a sufficiently complete data set, we can store the list in the database
if ($campaign_list_title AND $selected_contacts) {

    $rec = array();
    $rec['user_id']                     = $user_id;
    $rec['campaign_id']                 = $campaign_id;
    $rec['campaign_list_title']         = $campaign_list_title;
    $rec['campaign_list_description']   = $_POST['campaign_list_description'];
    sort($selected_contacts);
    $rec['target_contact_ids']          = implode(",", $selected_contacts);

    // If we have a campaign_list_id, we are updating an existing list
    if ($campaign_list_id) {

        $rst = $con->AutoExecute('campaign_lists', $rec, 'UPDATE', "campaign_list_id=$campaign_list_id", FALSE, get_magic_quotes_gpc());

    // Without a campaign_list_id, we are creating a new list
    } else {

        // Add the extra fields we need
        $rec['list_created_on']             = time();
        $rec['list_created_by']             = $session_user_id;
        $rec['campaign_list_record_status'] = 'a';

        $rst = $con->AutoExecute('campaign_lists', $rec, 'INSERT', FALSE, FALSE, get_magic_quotes_gpc());

    }

    if (!$rst)
        db_error_handler ($con, $sql);

    header("Location: $go_back_to_url");

// If we are missing data, present the user with the UI
} else {

    // If processing of this list has commenced, let the user know they will
    // not be allowed to edit it.
    if ($read_only) {
        $msg = _('This campaign list cannot be modified.') .'  '. _('Processing of this campaign list has already been initiated.');
        $disabled = ' disabled="disabled"';
        $readonly = ' readonly="readonly"';
    } else {
        $disabled = '';
    }

    // Get the User Menu; for a new list, default to the logged-in user.
    if ($read_only) {
        $sql = 'SELECT '. $con->Concat('cont.last_name', "', '", 'cont.first_names') .' AS name,
                        cont.contact_id,
                        cont.last_name,
                        cont.first_names,
                        u.user_id,
                        u.user_contact_id
                FROM contacts cont, users u
                WHERE cont.contact_id = u.user_contact_id
                AND u.user_id = '. $user_id;
        $rst = $con->execute($sql);
        $user_menu = $rst->GetMenu('user_id', $rst->fields['name'], FALSE, FALSE, 0, $disabled);
        $rst->close();
    } else {
        $user_id = ( (isset($_SESSION['search_sql'])) AND (!$campaign_list_id) AND (!$_POST['array_of_contacts']) ) ? $session_user_id : $user_id;
        $user_menu = get_user_menu($con, $user_id, TRUE, 'user_id', FALSE);
    }
    
    // Get the Campaign Menu
    $sql = "SELECT campaign_title, campaign_id
            FROM campaigns, campaign_statuses
            WHERE campaign_record_status = 'a'
                AND campaign_statuses.campaign_status_id = campaigns.campaign_status_id
                AND campaign_statuses.status_open_indicator = 'o'
            ORDER BY campaign_title";
    $rst = $con->execute($sql);
    $campaign_menu = $rst->GetMenu2('campaign_id', $campaign_id, TRUE, FALSE, 0, $disabled);
    $rst->close();

    if (is_array($array_of_contacts)) {

        $contact_ids_csv = implode(",",$array_of_contacts);
        // Build the contact search results table row set
        $sql = "SELECT ". $con->Concat($con->qstr('<a href="../../contacts/one.php?contact_id='), "cont.contact_id", $con->qstr('">'), "cont.last_name", "', '", "cont.first_names", $con->qstr('</a>')) ." AS contact_link, ".
                    $con->Concat('cont.last_name', "', '", 'cont.first_names') .' AS name, '.
                    $con->Concat($con->qstr('<a id="'), "c.company_name", $con->qstr('" href="../../companies/one.php?company_id='), "c.company_id", $con->qstr('">'), "c.company_name", $con->qstr('</a>')) ." AS company,
                    cont.contact_id,
                    cont.last_name,
                    cont.first_names,
                    c.company_name,
                    company_code,
                    title,
                    description,
                    u.username,
                    cont.email,
                    cont.address_id,
                    cont.work_phone,
                    cont.cell_phone
                FROM contacts cont, companies c, users u
                WHERE cont.company_id = c.company_id
                    AND cont.user_id = u.user_id
                    AND contact_record_status = 'a'
                    AND cont.contact_id IN ($contact_ids_csv)
                ORDER BY name ASC";
        $rst = $con->execute($sql);

        if ($rst) {
            $contact_rows = '';
            while (!$rst->EOF) {
                $contact_rows .=   '<tr>
                                        <input type="hidden" name="array_of_contacts[]" value="'. $rst->fields['contact_id'] .'">
                                        <td class="widget_content_form_element">
                                            <input type="checkbox" name="selected_contacts[]" value="'. $rst->fields['contact_id'] .'" checked="checked"'. $disabled .'>
                                        </td>
                                        <td class="widget_content">'. $rst->fields['contact_link'] .'</td>
                                        <td class="widget_content">'. $rst->fields['company'] .'</td>
                                        <td class="widget_content">'. $rst->fields['title'] .'</td>
                                        <td class="widget_content">'. $rst->fields['work_phone'] .'</td>
                                        <td class="widget_content">'. $rst->fields['cell_phone'] .'</td>
                                        <td class="widget_content">'. $rst->fields['email'] .'</td>';
                $contact_rows .= "</tr>\n";
                $rst->movenext();
            }

        $rst->close();
        }
    } else {
        $contact_rows = '<tr><td class="widget_content" colspan="7">'. _("No array of contacts!") .'</td></tr>';
    }

    $con->close();

    if ($campaign_list_id)
        $page_title = _("Edit Campaign List") .': '. $campaign_list_title;
    else
        $page_title = _("Create New Campaign List");


    start_page($page_title, true, $msg);

    ?>

    <div id="Main">
        <?php if (!$read_only) { ?>
            <form action="one.php<?php
                // Preserve the passed-in parameters
                if ($campaign_list_id OR $return_url) {
                    echo '?';
                    if ($campaign_list_id) {
                        echo "campaign_list_id=$campaign_list_id";
                        if ($return_url)
                            echo '&';
                    }
                    if ($return_url)
                        echo "return_url=$return_url";
                }
            ?>" method="post">
            <input type="hidden" name="user_id" value="<?php  echo $user_id; ?>">
        <?php } ?>
        <table class="widget" cellspacing="1">
            <tr>
                <td class=widget_label_right><?php echo _("Campaign List Title"); ?></td>
                <td class=widget_content_form_element>
                    <input type="text" name="campaign_list_title" size="30" value="<?php echo $campaign_list_title; ?>"<?php echo $readonly; ?>>
                </td>

                <td class=widget_label_right><?php echo _("Campaign"); ?></td>
                <td class=widget_content_form_element>
                    <?php echo $campaign_menu; ?>&nbsp;&nbsp;
                    <a href="../some.php" target="_blank"><?php echo _('View Campaigns')?></a>
                </td>
                
                <td class=widget_label_right><?php echo _("Owner"); ?></td>
                <td class=widget_content_form_element><?php echo $user_menu; ?></td>
            </tr>

            <tr>
                <td class=widget_label_right><?php echo _("Campaign List Description"); ?></td>
                <td class=widget_content_form_element colspan="5">
                    <textarea name="campaign_list_description" cols="80" rows="4" style="width: 98%;"<?php echo $readonly; ?>><?php echo $campaign_list_description; ?></textarea>
                </td>
            </tr>
        </table>
        
        <table class="widget" cellspacing="1">
            <tr>
                <td class=widget_header colspan=7><?php echo _("Confirm Selection"); ?></td>
            </tr>
            <tr>
                <td class="widget_label"><?php echo _("Select"); ?></td>
                <td class="widget_label"><?php echo _("Contact"); ?></td>
                <td class="widget_label"><?php echo _("Company"); ?></td>
                <td class="widget_label"><?php echo _("Title"); ?></td>
                <td class="widget_label"><?php echo _("Phone"); ?></td>
                <td class="widget_label"><?php echo _("Cell Phone"); ?></td>
                <td class="widget_label"><?php echo _("Email"); ?></td>
            </tr>
            <?php  echo $contact_rows ?>
            <tr>
                <td class="widget_content_form_element" colspan="7">
                    <input type="submit" class="button" value="<?php echo _("Continue"); ?>"<?php echo $disabled; ?>>
                    <input class="button" type="button" value="<?php echo _('Cancel'); ?>" onclick="location.href='<?php echo $go_back_to_url; ?>'">
                </td>
            </tr>
        </table>
        <?php if (!$read_only) { ?></form><?php } ?>
    </div>

    <?php
    unset($_SESSION['search_sql']);
    end_page();
}
 /**
  * $Log: one.php,v $
  * Revision 1.1  2011/01/14 15:51:28  gopherit
  * Implemented the Campaign Lists functionality to allow launching of campaign workflows on lists of contacts created with /contacts/some.php
  *
  * 
  */
?>