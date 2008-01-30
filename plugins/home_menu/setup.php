<?php

// copyright 2007 Glenn Powers <glenn@net127.com>

function xrms_plugin_init_home_menu() {
    global $xrms_plugin_hooks;
    $xrms_plugin_hooks['private_front_splash']['home_menu'] = 'new_act';
    $xrms_plugin_hooks['private_sidebar_top']['home_menu'] = 'home_menu';
}


function home_menu() {

    global $con, $session_user_id, $include_directory, $my_company_id;

$menu = "
        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=3>
                    " . _("Tools") . "</a>
                </td>
            </tr>           
            <tr>
                <td class=widget_content>
                    " . render_create_button(_("New Company"), 'button', "javascript: location.href='../companies/new.php';", false, false, 'cases') . "
                </td>
                <td class=widget_content>
                    " . render_create_button(_("New Campaign"), 'button', "javascript: location.href='../campaigns/new.php?company_id=$my_company_id';", false, false, 'cases') . "
                </td>
                <td class=widget_content>
                </td>
            </tr>
            <tr>
                <td class=widget_content>
                    " . render_create_button(_("New Contact"), 'button', "javascript: location.href='../contacts/new_contact_company_select.php';", false, false, 'cases') . "
                </td>

                <td class=widget_content>
                    " . render_create_button(_("New Opportunity"), 'button', "javascript: location.href='../opportunities/new.php?company_id=$my_company_id';", false, false, 'cases') . "
                </td>
                <td class=widget_content>
                " . render_create_button(_("New Case"), 'button', "javascript: location.href='../cases/new.php?company_id=$my_company_id';", false, false, 'cases') . "
                </td>
            </tr>

            <!-- tr>
                <td class=widget_content_large>
                    <a href=\"../reports/activitytimes.php?user_id=" . $session_user_id . "\">" . _("Timesheets") . "</a>
                </td> -->
            </tr>
       </table>
";

return $menu;

}

function new_act() {

    global $con, $session_user_id, $include_directory, $my_company_id, $user_contact_id;
    require_once($include_directory . '../activities/activities-widget.php');
    
// New Activities Widget
$company_id = $my_company_id;
$contact_id = $session_user_id;
$return_url = "/private/home.php";
// function GetNewActivityWidget($con, $session_user_id, $return_url, $on_what_table, $on_what_id, $company_id, $contact_id) {

$menu .= GetNewActivityWidget($con, $session_user_id, $return_url, '', '', 1, 1);

return $menu;

}

?>