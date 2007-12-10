<?php

// copyright 2007 Glenn Powers <glenn@net127.com>

function xrms_plugin_init_home_menu() {
    global $xrms_plugin_hooks;
    $xrms_plugin_hooks['private_front_splash']['home_menu'] = 'home_menu';
}


function home_menu() {

    global $con, $session_user_id, $include_directory, $my_company_id;
    require_once($include_directory . '../activities/activities-widget.php');

$menu = "
        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=2>
                    " . _("Main Menu") . "</a>
                </td>
            </tr>
            <tr>
                <td class=widget_content_large>
                    <a href=\"../companies/new.php\">" . _("New Company") . "</a>
		        </td>
                <td class=widget_content_large>
                    <a href=\"../companies/some.php?clear=1\">" . _("List Companies") . "</a>
		        </td>
            </tr>

            <tr>
                <td class=widget_content_large>
                    <a href=\"../contacts/new_contact_company_select.php\">" . _("New Contact") . "</a>
                </td>
                <td class=widget_content_large>
                    <a href=\"../contacts/some.php?clear=1\">" . _("List Contacts") . "</a>
                </td>
            </tr>
            <tr>
                <td class=widget_content_large>
                    <a href=\"../cases/new.php?company_id=" . $my_company_id . "\">" . _("New Case") . "</a>
                </td>
                <td class=widget_content_large>
                    <a href=\"../cases/some.php\">" . _("List Cases") . "</a>
                </td>
            </tr>
       </table>
";

// New Activities Widget
$company_id = 1;
$contact_id = 1;
$return_url = "/contacts/one.php?contact_id=$contact_id";

$menu .= GetNewActivityWidget($con, $session_user_id, $return_url, null, null, $company_id, $contact_id);

return $menu;

}

?>