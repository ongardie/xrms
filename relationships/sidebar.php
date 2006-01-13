<?php

if ( !defined('IN_XRMS') )
{
  die(_("Hacking attempt"));
  exit;
}

/**
 * Associated Relationships Sidebar
 *
 * Include this file anywhere you want to show other relationships
 *
 * @param string $relationships Associative array of table names and IDs
 *
 * @author Brad Marshall
 * @author Neil Roberts
 * @author Aaron van Meerten
 *
 * $Id: sidebar.php,v 1.39 2006/01/13 00:01:00 vanmer Exp $
 */

require_once($include_directory.'utils-relationships.php');

 //set these variables to control what information is output by the relationship sidebar

$show_other_relationships=false;
$show_address=false;
$show_agent_count=false;
$show_related_relationships=false;
$show_opportunity_indicator=false;
$show_contact_company=true;

if(empty($relationships)) {
    $relationships = array();
}
if(empty($ori_relationships)) {
    $ori_relationships = $relationships;
}
$expand = 0;
if(isset($_GET['expand'])) {
    $expand = 1;
}
$current_ids = array(-1);
$relationship_ids = array(-1);
$relationship_link_rows = '';

// $con->debug=1;


    //check relationships to ensure value before loading relationships
    foreach($relationships as $key => $value) {
        if(empty($value)) {
            unset($relationships[$key]);
        }
    }
    //loop through each type of relationship requested to display
    foreach ($relationships as $_on_what_table => $_on_what_id) {
        $relationship_title='';

        //get possible relationship types for this table
        $relationship_types=get_relationship_types($con, $_on_what_table);
        if (!$relationship_types) continue;
        if (!is_array($relationship_types)) continue;
        //get possible ids
        $relationship_type_ids=array_keys($relationship_types);
        //make string out of possible ids
        $relationship_type_ids_str=implode(",",$relationship_type_ids);

        //get relationships based on relationship types from above, plus id
        $relationship_data=get_relationships($con, $_on_what_table, $_on_what_id, $relationship_types);

        //write table and header for relationships on this entity
        $on_what_table_singular=ucfirst(make_singular($_on_what_table));
        $relationship_link_rows .= '
                        <table class=widget cellspacing=1 width="100%">
                            <tr>
                                <td class=widget_header colspan=4>' . _("Relationships for") .' '. _($on_what_table_singular) ."</td>
                            </tr>
                            <!-- Content Start -->";

        //loop on each relationship that this entity exists in
        //if we get no results, skip to the next type
        $contacts=array();
    if ($relationship_data) {
            foreach ($relationship_data as $relationship_id=>$relationship_details) {
                //retrieve needed relationship information from relationship details
                $relationship_type_data=$relationship_details['relationship_type_data'];
                $relationship_type_id=$relationship_details['relationship_type_id'];

                $working_direction=$relationship_details['working_direction'];
                $opposite_direction=$relationship_details['opposite_direction'];


                $display_name = ucfirst($relationship_type_data[$working_direction.'_what_table']);
                $display_singular = ucfirst($relationship_type_data[$working_direction.'_what_table_singular']);
                $opposite_name = ucfirst($relationship_type_data[$opposite_direction.'_what_table']);

                //check to see if the relationship name is the same as the last, if not, write a new row for the new relationship name
                if ($relationship_title!=$relationship_type_data['relationship_name']) {
                    $relationship_title=$relationship_type_data['relationship_name'];
                    $relationship_link_rows .= "<tr>
                    <td class=widget_label>" . _($relationship_title) . "</td>";

                    //if enabled, show link for linked relationships
                    if ($show_other_relationships) {
                        $relationship_link_rows .= "<td align=right class=widget_label><a href=\"$http_site_root" . current_page("other={$relationship_type_id}") . "\">" . _("Linked Relationships") . "</a></td>";
                    }

                    //end title row
                    $relationship_link_rows .= "
                </tr>";
                }
                    //grab current id, relationship details
                    $current_id = $relationship_details[$relationship_type_data[$opposite_direction.'_what_table_singular'] . '_id'];
                    if (($relationship_type_data[$working_direction.'_what_table']=='contacts') AND ($_on_what_id!=$relationship_details[$relationship_type_data[$working_direction.'_what_table_singular'] . '_id'])) {
                        if ($relationship_details[$relationship_type_data[$working_direction.'_what_table_singular'] . '_id'])
                            $contacts[]=$relationship_details[$relationship_type_data[$working_direction.'_what_table_singular'] . '_id'];
                    }
                    if (($relationship_type_data[$opposite_direction.'_what_table']=='contacts') AND ($_on_what_id!=$current_id)) {
                        $contacts[]=$current_id;
                    }
                    $current_ids[] = $current_id;
                    $relationship_ids[] = $relationship_details['relationship_id'];
                    $agent_count = 0;
                    $address = '';

                    if($relationship_type_data[$opposite_direction.'_what_table'] == "companies") {
                        if ($show_agent_count) {
                            $agent_count=get_agent_count($con, $current_id);
                        }
                        if ($show_address) {
                            $address=get_formatted_address($con, false, $current_id, true);
                        }
                    }
                    if ($show_opportunity_indicator) {
                        $opportunity_id = '';
                        if(($relationship_type_data[$opposite_direction.'_what_table'] == "companies") || ($relationship_type_data[$opposite_direction.'_what_table'] == "contacts")) {
                            $sql = "SELECT opportunity_id
                                    FROM opportunities
                                    WHERE " . $relationship_type_data[$opposite_direction.'_what_table_singular'] . "_id = " . $current_id;
                            $rst3 = $con->execute($sql);
                            if(!$rst3) {
                                db_error_handler($con, $sql);
                            }
                            elseif(!$rst3->EOF) {
                                $opportunity_id = $rst3->fields['opportunity_id'];
                                $rst3->close();
                            }
                        }
                    }

                    /* NEW ROW FOR RELATIONSHIP */
                    $relationship_link_rows .= "
                    <tr><td class=widget_content colspan=2 align=left>
                    {$relationship_type_data[$working_direction . '_what_text']}:<br>
                    {$relationship_type_data['pre_formatting']}";
                    if($opportunity_id) {
                        $relationship_link_rows .= "*";
                    }

                    //hack to set link to the right URL from centralized url creator
                    $href="$http_site_root".table_one_url($relationship_type_data[$opposite_direction.'_what_table'], $current_id);
                    $relationship_link_rows .= "<a href='$href'>"
                        . $relationship_details['name']
                        . "</a> ";
                    if($agent_count) {
                        $relationship_link_rows .= " (" . $agent_count . ") ";
                    }

                    $relationship_link_rows .= $relationship_type_data['post_formatting'] . "\n"
                        . "                &bull;"
                        . " <a href='$http_site_root/relationships/edit.php?working_direction=$opposite_direction"
                        . "&on_what_table=" . $relationship_type_data[$opposite_direction.'_what_table']
                        . "&relationship_id=" . $relationship_id
                        . "&return_url=" . urlencode(current_page())
                        . "'>" . _("Edit") . "</a>";

                    if ($show_contact_company AND ($relationship_type_data[$opposite_direction.'_what_table']=='contacts')) {
                        $contact_company_sql="SELECT company_name, contacts.company_id FROM contacts JOIN companies ON contacts.company_id=companies.company_id WHERE contact_id=$current_id";
                        $cc_rst=$con->execute($contact_company_sql);
                        if (!$cc_rst) db_error_handler($con, $contact_company_sql);
                        if (!$cc_rst->EOF) {
                            $rel_company_name=$cc_rst->fields['company_name'];
                            $contact_company_id=$cc_rst->fields['company_id'];
                            $href="$http_site_root".table_one_url('companies', $contact_company_id);
                            $relationship_link_rows.="<br>@ <a href='$href'>$rel_company_name</a>";
                        }
                    }

                    if($address) {
                        $relationship_link_rows .= "<br>" . $address;
                    }
                    $relationship_link_rows .= "
                    </td>
                    </tr>\n";
                /* END NEW ROW FOR RELATIONSHIP */

                    if ($show_other_relationships) {
                        if($j == 0 && in_array($_GET['other'], $relationship_type_ids)) {
                            $name_to_get = $con->Concat("c." . implode(", ' ' , c.", table_name($relationship_type_data[$working_direction.'_what_table'])));

                            // Create the shared association entries
                            $sql = "SELECT r.*, " . $name_to_get . " as name, c." . $relationship_type_data[$working_direction.'_what_table_singular'] . "_id
                                    FROM relationships as r, " . $relationship_type_data[$working_direction.'_what_table'] . " as c
                                    WHERE r.relationship_type_id IN ($relationship_type_ids_str)
                                    AND r.{$opposite_direction}_what_id = {$current_id}
                                    AND r.{$working_direction}_what_id != {$relationships[$relationship_type_data[$working_direction.'_what_table']]}
                                    AND r." . $working_direction . "_what_id=" . $relationship_type_data[$working_direction.'_what_table_singular'] . "_id
                                    AND r.relationship_status='a'";

                            $rst3 = $con->execute($sql);

                            if(!$rst3) {
                                db_error_handler($con, $sql);
                            }
                            elseif(!$rst3->EOF) {
                                while(!$rst3->EOF) {
                                    $current_id2 = $rst3->fields[$working_direction . '_what_id'];

                                    $agent_count = 0;
                                    $address = '';
                                    if($relationship_type_data[$working_direction.'_what_table']== "companies") {
                                        if ($show_agent_count) {
                                            $agent_count=get_agent_count($con, $current_id2);
                                        }
                                        if ($show_address) {
                                            $address = get_formatted_address($con, false, $current_id2, true);
                                        }
                                    }

                                    $opportunity_id = 0;
                                    if(($relationship_type_data[$working_direction.'_what_table']== "companies") || ($relationship_type_data[$working_direction.'_what_table'] == "contacts")) {
                                        $sql = "SELECT opportunity_id
                                                FROM opportunities
                                                WHERE {$relationship_type_data[$working_direction.'_what_table_singular']}_id = $current_id2
                                                AND opportunity_record_status = 'a'";
                                        $rst4 = $con->execute($sql);
                                        if(!$rst4) {
                                            db_error_handler($con, $sql);
                                        }
                                        elseif(!$rst4->EOF) {
                                            $opportunity_id = $rst4->fields['opportunity_id'];
                                            $rst4->close();
                                        }
                                    }

                                    $relationship_link_rows .= "<tr><td class=widget_content colspan=2 align=right>\n"
                                        . $relationship_types[$rst3->fields['relationship_type_id']][$opposite_direction . '_what_text'].":<br>"
                                        . $relationship_types[$rst3->fields['relationship_type_id']]['pre_formatting'];
                                    if($opportunity_id) {
                                        $relationship_link_rows .= "*";
                                    }
                                    $relationship_link_rows .= "<a href='$http_site_root/{$relationship_type_data[$working_direction.'_what_table']}/one.php?{$relationship_types[$rst3->fields['relationship_type_id']][$working_direction.'_what_table_singular']}_id="
                                        . $current_id2 . "'>"
                                        . $rst3->fields['name']
                                        . "</a>";
                                    if($agent_count) {
                                        $relationship_link_rows .= " (" . $agent_count . ") ";
                                    }
                                    $relationship_link_rows .= $relationship_type_data['post_formatting'] . "\n";

                                    $name_to_get = $con->Concat("c." . implode(", ' ' , c.", table_name($relationship_type_data[$opposite_direction.'_what_table'])));

                                    if ($show_related_relationships) {
                                        $sql = "SELECT rt.relationship_type_id, rt.relationship_name, rt.from_what_table, rt.to_what_table, rt.from_what_text,
                                                    rt.to_what_text, rt.relationship_status, rt.pre_formatting, rt.post_formatting,
                                                    r.relationship_id, r.from_what_id, r.to_what_id, r.relationship_type_id, r.established_at, r.ended_on, r.relationship_status,
                                                    c." . $relationship_type_data[$opposite_direction.'_what_table_singular'] . "_id, " . $name_to_get . " as name
                                                FROM relationship_types as rt, relationships as r, " . $relationship_type_data[$opposite_direction.'_what_table'] . " as c
                                                WHERE {$working_direction}_what_id = {$current_id2}
                                                AND {$opposite_direction}_what_id != {$current_id}
                                                AND r.relationship_type_id IN ($relationship_type_ids_str)
                                                AND r.relationship_status='a'
                                                AND r." . $opposite_direction . "_what_id=" .$relationship_type_data[$opposite_direction.'_what_table_singular'] . "_id
                                                AND r.relationship_type_id = rt.relationship_type_id
                                                GROUP BY c." . $relationship_type_data[$opposite_direction.'_what_table_singular'] . "_id, " . $name_to_get
                                                . ", rt.relationship_type_id, rt.relationship_name, rt.from_what_table, rt.to_what_table, rt.from_what_text,
                                                rt.to_what_text, rt.relationship_status, rt.pre_formatting, rt.post_formatting,
                                                r.relationship_id, r.from_what_id, r.to_what_id, r.relationship_type_id, r.established_at, r.ended_on, r.relationship_status";

                                        $rst4 = $con->execute($sql);
                                        if(!$rst4) {
                                            db_error_handler($con, $sql);
                                        }
                                        elseif(!$rst4->EOF) {
                                            $relationship_link_rows .= " &bull;"
                                            . " <a href=\"$http_site_root" . current_page("expand={$relationship_type_data[$working_direction.'_what_table']}&id=$current_id2", "associated") . "\">"
                                            . $rst4->rowcount() . " other";
                                            if($rst4->rowcount() > 1) {
                                                $relationship_link_rows .= "s";
                                            }
                                        }
                                        $relationship_link_rows .= "</a>";
                                    }
                                    if($address) {
                                        $relationship_link_rows .= "<br>" . $address;
                                    }
                                    $relationship_link_rows .= "</td></tr>\n";
                                    $rst3->movenext();
                                }
                                $rst3->close();
                            }
                        }
                    }
                }
        }
            if(empty($relationship_link_rows)) {
                $relationship_link_rows .= "
                    <div id='expanded_associated_by_sidebar'>
                    <table class=widget cellspacing=1 width=\"100%\">
                        <tr>
                            <td class=widget_header colspan=2>" . _("Add Relationship") . "</td>
                        </tr>";
            }
            else {
                $relationship_link_rows .= "
                        <tr>
                            <td class=widget_label colspan=2 align=center>" . _("Add Relationship") . "</td></tr>";
            }
        $relationship_link_rows .= "
                        <tr>
                        <form action='" . $http_site_root . "/relationships/new-relationship.php' method='post'>
                            <input type=hidden name=on_what_id>
                            <input type=hidden name=return_url value=\"" . current_page() . "\">
                            <td class=widget_content_form_element colspan=2>";
             $current_relationship=array($_on_what_table=>$_on_what_id);
            $relationship_link_rows.='<input type=hidden name="relationship_entities" value="'.urlencode(serialize($current_relationship)).'">';
            $relationship_link_rows .= "
                                <input type=submit class=button value=\""._("New Relationship")."\">
                            </td>
                        </form>
                        </tr><!-- Form End -->";
            $contacts=array_unique($contacts);
            if (count($contacts)>0) {
                $relationship_link_rows .= "<tr><td class=widget_label colspan=2 align=center>"._("Mail Merge")."</td></tr>";
                $contact_list=implode(",",$contacts);
                $relationship_link_rows .= "<tr><td class=widget_content_form_element colspan=2><input type=button class=button value=\""._("Mail Merge")."\" onclick=\"javascript:location.href='$http_site_root/email/email.php?scope=contact_list&contact_list=$contact_list'\"></td></tr>";
                $contacts=array();
            }
    }

            $relationship_link_rows .= "        <!-- Content End --></table>\n";

// $con->debug=0;

/**
 * $Log: sidebar.php,v $
 * Revision 1.39  2006/01/13 00:01:00  vanmer
 * - changed to use newly created include/utils-relationships.php instead of relationships/relationship_functions.php
 * - removed deprecated relationship_functions.php
 *
 * Revision 1.38  2005/08/19 19:26:22  braverock
 * - avoid overwriting $company_name
 *
 * Revision 1.37  2005/08/08 16:15:52  vanmer
 * - added ability to display company name for relationships on contacts
 * - added control of this display using variable defined at the top of the sidebar
 *
 * Revision 1.36  2005/07/11 13:52:35  braverock
 * - Localize table name (on what relationship)
 *
 * Revision 1.35  2005/07/08 01:29:43  vanmer
 * - centralized hack to allow link to company_division to link properly automagically into utils-database.php
 *
 * Revision 1.34  2005/07/07 18:53:05  vanmer
 * - added case to handle URLs for relationships involving company divisions
 *
 * Revision 1.33  2005/05/23 22:05:06  vanmer
 * - added check for contact relationships, add to contact list used for mail merge on relationships
 * - added mail merge button which calls email for a mail merge for all contacts in a relationship in the
 * sidebar
 *
 * Revision 1.32  2005/03/01 15:11:11  vanmer
 * - changed to show New Relationship button for each type of relationship on a page
 *
 * Revision 1.31  2005/02/24 18:36:49  vanmer
 * - added conditional to only show relationships if data is returned
 * - allow relationship table to display with only new relationship button if no relationships on entity already exist
 *
 * Revision 1.30  2005/02/11 21:17:02  vanmer
 * - fixed edit links to properly allow edit of relationship
 *
 * Revision 1.29  2005/02/11 20:25:45  vanmer
 * - added check to ensure that relationship types actually get returned before using them
 *
 * Revision 1.28  2005/02/10 13:13:28  braverock
 * - add debug commands (commented) so that we can continue to optimize this
 *
 * Revision 1.27  2005/02/10 02:34:30  vanmer
 * - altered execution of sidebar to use newly created relationship functions
 * - added optional parameters to show/hide certain relationship information, including address, agent count, linked relationships, and opportunities
 * - can be set in the sidebar.php to start, will migrate to system parameters eventually
 *
 * Revision 1.26  2005/01/12 22:06:09  vanmer
 * - changed Other links to read Linked Relationships, as Other Companies is misleading
 * - Commented out relationship name display, as it does not show the name for each relationship, and is misleading because of this
 *
 * Revision 1.25  2005/01/12 20:23:13  vanmer
 * - removed unneeded Group By out of relationship query
 * - removed unneeded NOT IN query, overly restrictive from deprecated method
 * - altered address lookup to use centralized function
 *
 * Revision 1.24  2005/01/12 02:08:01  introspectshun
 * - Extended GROUP BY clauses to include all fields for db compatibility
 * - Added tests for undefined indexes
 *
 * Revision 1.23  2005/01/11 20:51:23  neildogg
 * - Combined Relationships bars, fixed incorrect ID passed to create company address
 *
 * Revision 1.22  2005/01/11 17:19:00  neildogg
 * - Avoid duplicating relationships
 *
 * Revision 1.21  2005/01/10 23:02:33  neildogg
 * - Fixed error on empty values
 *
 * Revision 1.20  2005/01/10 22:15:43  neildogg
 * - Complete independance from relationship names granted
 *
 * Revision 1.19  2005/01/10 20:31:21  neildogg
 * - Relationships sidebar now takes array, requires no code change, supports any available tables, decreases space usage
 *
 * Revision 1.18  2004/12/30 20:12:55  vanmer
 * - refined search for relationships, no longer displays incorrect linkages
 * - displays all types of relationships for an entity
 * - beginning of a larger set of changes for display/processing of multiple relationship table sources on a single page
 *
 * Revision 1.17  2004/12/01 18:22:12  vanmer
 * - changed sidebar to not show relationships if there are no relationship types that include the page displayed
 * - changed to use Add Relationship button to add a new relationship
 *
 * Revision 1.16  2004/09/13 23:35:36  introspectshun
 * - Added CSS class to "Next" button
 *
 * Revision 1.15  2004/08/21 20:13:48  johnfawcett
 * - added gettext calls to new strings
 *
 * Revision 1.14  2004/08/05 15:12:31  neildogg
 * - Allows multiple relationships on one sidebar
 *
 * Revision 1.13  2004/07/27 20:56:42  neildogg
 * - Removed categories if no records found
 *
 * Revision 1.12  2004/07/25 22:47:31  johnfawcett
 * - updated gettext strings
 *
 * Revision 1.11  2004/07/18 18:10:22  braverock
 * - convert all strings for i18n/translation
 *   - applies i18n patch contributed by John Fawcett
 *
 * Revision 1.10  2004/07/15 20:55:51  neildogg
 * - Proper return URL
 *
 * Revision 1.9  2004/07/15 17:41:30  cpsource
 * - Fix undef for relationship_link_rows.
 *
 * Revision 1.8  2004/07/15 13:47:34  neildogg
 * - If using "both" user can choose either option from relationship type
 *
 * Revision 1.7  2004/07/14 22:15:42  neildogg
 * - Now uses $overall_id
 *  - Can use $working_direction = "both" for
 *  - same-table relationships
 *
 * Revision 1.6  2004/07/14 21:04:37  neildogg
 * - Fixed genericity bugs, allowed for multiple includes
 *
 * Revision 1.5  2004/07/14 19:38:00  neildogg
 * - Added address/agent count/is opportunity depending on table
 *  - Should give example for people that want to extend functionality
 *  - If another generic method could be used, let me know
 *
 * Revision 1.4  2004/07/14 14:49:28  cpsource
 * - All sidebar.php's now support IN_XRMS security feature.
 *
 * Revision 1.3  2004/07/14 14:08:53  neildogg
 * - Add new relationship now in /relationships directory
 *
 * Revision 1.2  2004/07/13 16:00:57  cpsource
 * - Get rid of some undefined variable usages.
 *
 * Revision 1.1  2004/07/09 15:33:42  neildogg
 * New, generic programs that utilize the new relationships table
 *
 * Revision 1.3  2004/07/07 20:55:39  neildogg
 * - Was missing a deleted item check
 *
 * Revision 1.2  2004/07/02 18:03:24  neildogg
 * Some light bug fixes and added relationship text around all entries.
 *
 * Revision 1.1  2004/07/01 19:48:10  braverock
 * - add new configurable relationships code
 *   - adapted from patches submitted by Neil Roberts
 *
 */
?>
