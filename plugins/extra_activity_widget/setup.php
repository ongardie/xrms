<?php
/**
 * Example plugin to demonstrate adding extra activities widgets to pages
 *
 * This example adds an activities widget to the companies/one.php page, directly after the existing activities widget (and in fact using the same form)
 * 
 * $Id: setup.php,v 1.3 2006/07/19 01:36:53 vanmer Exp $
**/

/**
  * Plugin Initialization function.  Intended to register all needed hooks into XRMS
  *
**/
function xrms_plugin_init_extra_activity_widget() {
    global $xrms_plugin_hooks;
    $xrms_plugin_hooks['company_content_bottom']['extra_activity_widget']='extra_activity_widget_company';
}


/**
 * Plugin hook function to display activities widget
**/
function extra_activity_widget_company(&$string) {
    global $include_directory;
    global $session_user_id;
    global $return_url;
    global $company_id;
    global $division_id;
    global $con;
    require_once($include_directory.'../activities/activities-widget.php');

    //set up which columns to display as system default
    $default_columns = array('title', 'owner', 'type', 'contact', 'activity_about', 'scheduled', 'due');

    //set search terms.  This example shows all closed activities for a company and division
    //minisearch terms are added to this to create the criteria in the SQL
    $search_terms = array( 'company_id'            => $company_id,
                       'division_id'           => $division_id,
                       'activity_status' => "'c'"

                        );
    //enable the mini search filter for activities
    $show_mini_search=true;

    //no extra where clauses for the SQL statement
    $extra_where='';

    //no end rows to add to the widget
    $end_rows='';

    //don't set default sort
    $default_sort=null;
    
    //this must be unique to allow proper saved search and form operations
    $instance='Plugin';

    $caption=_("Closed Activities");
    $form_name="company_one_extra_activity";
    //retrieve activities widget
    $activities_widget =  GetActivitiesWidget($con, $search_terms, $form_name, $caption, $session_user_id, $return_url, $extra_where, $end_rows, $default_columns, $show_mini_search, $default_sort, $instance);

    //assign output to string
    $string.="<form name=$form_name action=\"one.php\"><input type=hidden name=company_id value=$company_id><input type=hidden name=division_id value=$division_id>";
    $string.= $activities_widget['content'];
    $string.= $activities_widget['sidebar'];
    $string.= $activities_widget['js'];
    $string.="</form>";

    return $string;
}

/**
 * $Log: setup.php,v $
 * Revision 1.3  2006/07/19 01:36:53  vanmer
 * - only require activities widget when running plugin
 *
 * Revision 1.2  2006/07/14 04:12:41  vanmer
 * - altered extra plugin to use _bottom hook (and define own form)
 *
 * Revision 1.1  2006/07/14 03:52:28  vanmer
 * - Initial revision of the example plugin for an activities widget on the companies/one.php page
 *
**/

?>