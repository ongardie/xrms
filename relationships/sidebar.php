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
 *
 * $Id: sidebar.php,v 1.26 2005/01/12 22:06:09 vanmer Exp $
 */

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

for($j = 0; $j <= $expand; $j++) {
    $made_rows = false;

    if($j == 1) {
        $relationships = array($_GET['expand'] => $_GET['id']);
    }

    foreach($relationships as $key => $value) {
        if(empty($value)) {
            unset($relationships[$key]);
        }
    }
    
    $sql = "SELECT COUNT(DISTINCT relationship_name) AS multiple, relationship_type_id, relationship_name, from_what_table, to_what_table
            FROM relationship_types
            WHERE (from_what_table IN ('" . implode("', '", array_keys($relationships)) . "')
                OR to_what_table IN ('" . implode("', '", array_keys($relationships)) . "'))
            AND relationship_status = 'a'
            GROUP BY relationship_type_id, relationship_name, from_what_table, to_what_table
            ORDER BY relationship_type_id";
    $rst = $con->execute($sql);
    if(!$rst) {
        db_error_handler($con, $sql);
    }
    elseif(!$rst->EOF) {
        while(!$rst->EOF) {
            $sql = "SELECT rt.relationship_type_id, rt.relationship_name, rt.from_what_table, rt.to_what_table, rt.from_what_text,
                           rt.to_what_text, rt.relationship_status, rt.pre_formatting, rt.post_formatting
                    FROM relationship_types rt
                    WHERE rt.from_what_table = '{$rst->fields['from_what_table']}'
                    AND rt.to_what_table = '{$rst->fields['to_what_table']}'
                    AND rt.relationship_status = 'a'";
            $rst2 = $con->execute($sql);
            if(!$rst2) {
                db_error_handler($con, $sql);
            }
            elseif(!$rst2->EOF) {
                $what['from']['table'] = $rst->fields['from_what_table'];
                $what['from']['singular'] = make_singular($rst->fields['from_what_table']);
                $what['to']['table'] = $rst->fields['to_what_table'];
                $what['to']['singular'] = make_singular($rst->fields['to_what_table']);
                
                $both = 0; //If it's a relationship from a table on itself, we need to check both directions
                if(in_array($what['from']['table'], array_keys($relationships)) && in_array($what['to']['table'], array_keys($relationships))) {
                    $working_direction = 'from';
                    $opposite_direction = 'to';
                    $both = 1;
                }
                elseif(in_array($what['from']['table'], array_keys($relationships))) {
                    $working_direction = 'from';
                    $opposite_direction = 'to';
                }
                else {
                    $working_direction = 'to';
                    $opposite_direction = 'from';
                }
                
                $relationship_type_ids = array();
                while(!$rst2->EOF) {
                    $relationship_type_ids[] = $rst2->fields['relationship_type_id'];
                    $rst2->movenext();
                }
                $rst2->close();
                
                for($i = 0; $i <= $both; $i++) {
                
                    $display_name = ucfirst($what[$working_direction]['table']);
                    $display_singular = ucfirst($what[$working_direction]['singular']);
                    $opposite_name = ucfirst($what[$opposite_direction]['table']);
                            
                    $name_to_get = $con->Concat("c." . implode(", ' ' , c.", table_name($what[$opposite_direction]['table'])));
        
                    $sql = "SELECT rt.relationship_type_id, rt.relationship_name, rt.from_what_table, rt.to_what_table, rt.from_what_text,
                                rt.to_what_text, rt.relationship_status, rt.pre_formatting, rt.post_formatting,
                                r.relationship_id, r.from_what_id, r.to_what_id, r.relationship_type_id, r.established_at, r.ended_on, r.relationship_status,
                                c." . $what[$opposite_direction]['singular'] . "_id, " . $name_to_get . " as name
                            FROM relationship_types as rt, relationships as r, " . $what[$opposite_direction]['table'] . " as c
                            WHERE {$working_direction}_what_id = {$relationships[$what[$working_direction]['table']]}
                            AND r.relationship_type_id in (" . implode(',', $relationship_type_ids) . ")
                            AND r.relationship_status='a'
                            AND r." . $opposite_direction . "_what_id=" . $what[$opposite_direction]['singular'] . "_id
                            AND r.relationship_type_id = rt.relationship_type_id
                            AND r.relationship_id NOT IN (" . implode(', ', $relationship_ids) . ")";

                    $rst2 = $con->execute($sql);
                    if(!$rst2) {
                        db_error_handler($con, $sql);
                    }
                    elseif(!$rst2->EOF) {
                        if(!$made_rows) {
                            if($j == 0) {
                                $relationship_title = 'Relationships';
                            }
                            else {
                                $name_to_get = $con->Concat("c." . implode(", ' ' , c.", table_name($what[$working_direction]['table'])));
                            
                                $sql = "SELECT $name_to_get AS name
                                        FROM {$what[$working_direction]['table']} AS c
                                        WHERE {$what[$working_direction]['singular']}_id = {$_GET['id']}";
                                $rst3 = $con->execute($sql);
                                if(!$rst3) {
                                    db_error_handler($con, $sql);
                                }
                                elseif(!$rst3->EOF) {
                                    $relationship_title = $rst3->fields['name'];
                                }
                                $relationship_link_rows .= '
                        <a name="associated" id="associated">';
                            }
                        
                            $made_rows = true;
                            $relationship_link_rows .= '
                        <div id="company_link_sidebar">
                        <table class=widget cellspacing=1 width="100%">
                            <tr>
                                <td colspan=2 class=widget_header colspan=4>' . _($relationship_title) . '</td>
                            </tr>
                            <!-- Content Start -->';
                        }
                    
/*
                        COMMENTED, SHOWS WRONG INFORMATION IF RELATIONSHIPS ALL HAVE SEPERATE NAMES                        
                        if($rst->fields['multiple'] == 1) {
                            $relationship_link_rows .= "
                        <tr>
                            <td class=widget_label colspan=2 align=center>" . _($rst->fields['relationship_name']) . "</td>
                        </tr>
                        <tr>";
                        }
*/                        
                        if($j == 0) {
                            $relationship_link_rows .= "
                            <td class=widget_label>" . _($opposite_name) . "</td>";
                            if(in_array($_GET['other'], $relationship_type_ids)) {
                                $relationship_link_rows .= "
                                <td align=right class=widget_label>" . _("Linked Relationships") . "</td>";
                            }
                            else {
                                $relationship_link_rows .= "
                                <td align=right class=widget_label><a href=\"$http_site_root" . current_page("other={$rst->fields['relationship_type_id']}") . "\">" . _("Linked Relationships") . "</a></td>";
                            }
                        }
                        else {
                            $relationship_link_rows .= "
                            <td class=widget_label colspan=2>" . _($opposite_name) . "</td>";
                        }
                        $relationship_link_rows .= "
                        </tr>";
                        
                        while(!$rst2->EOF) {
                            $current_id = $rst2->fields[$what[$opposite_direction]['singular'] . '_id'];
                            $current_ids[] = $current_id;
                            $relationship_ids[] = $rst2->fields['relationship_id'];
        
                            $agent_count = 0;
                            $address = '';
                            if($what[$opposite_direction]['table'] == "companies") {
                                $sql = "SELECT COUNT(contact_id) as agent_count
                                        FROM contacts
                                        WHERE company_id = $current_id
                                        GROUP BY company_id";
                                $rst3 = $con->execute($sql);
                                if(!$rst3) {
                                    db_error_handler($con, $sql);
                                }
                                elseif(!$rst3->EOF) {
                                    $agent_count = $rst3->fields['agent_count'];
                                    $rst3->close();
                                }
                                $address=get_formatted_address($con, false, $current_id, true);
                            }
        
                            $opportunity_id = '';
                            if(($what[$opposite_direction]['table'] == "companies") || ($what[$opposite_direction]['table'] == "contacts")) {
                                $sql = "SELECT opportunity_id
                                        FROM opportunities
                                        WHERE " . $what[$opposite_direction]['singular'] . "_id = " . $current_id;
                                $rst3 = $con->execute($sql);
                                if(!$rst3) {
                                    db_error_handler($con, $sql);
                                }
                                elseif(!$rst3->EOF) {
                                    $opportunity_id = $rst3->fields['opportunity_id'];
                                    $rst3->close();
                                }
                            }
        
                            $relationship_link_rows .= "
                        <tr><td class=widget_content colspan=2 align=left>
                            {$rst2->fields[$working_direction . '_what_text']}:<br>
                            {$rst2->fields['pre_formatting']}";
                            if($opportunity_id) {
                                $relationship_link_rows .= "*";
                            }
                            $relationship_link_rows .= "<a href='$http_site_root/{$what[$opposite_direction]['table']}/one.php?{$what[$opposite_direction]['singular']}_id="
                                . $current_id . "'>" 
                                . $rst2->fields['name']
                                . "</a> ";
                            if($agent_count) {
                                $relationship_link_rows .= " (" . $agent_count . ") ";
                            }
                            $relationship_link_rows .= $relationship_arr[$rst->fields['relationship_type_id']]['post_formatting'] . "\n"
                                . "                &bull;"
                                . " <a href='$http_site_root/relationships/edit.php?working_direction=$opposite_direction"
                                . "&on_what_table=" . $what[$opposite_direction]['table']
                                . "&relationship_id=" . $rst2->fields['relationship_id']
                                . "&return_url=" . urlencode(current_page())
                                . "'>" . _("Edit") . "</a>";
                        
                            if($address) {
                                $relationship_link_rows .= "<br>" . $address;
                            }
                            $relationship_link_rows .= "
                            </td>
                        </tr>\n";
    
                            if($j == 0 && in_array($_GET['other'], $relationship_type_ids)) {
                                $name_to_get = $con->Concat("c." . implode(", ' ' , c.", table_name($what[$working_direction]['table'])));
                    
                                // Create the shared association entries
                                $sql = "SELECT rt.*, r.*, " . $name_to_get . " as name, c." . $what[$working_direction]['singular'] . "_id
                                        FROM relationship_types as rt, relationships as r, " . $what[$working_direction]['table'] . " as c
                                        WHERE r.relationship_type_id in (" . implode(',', $relationship_type_ids) . ")
                                        AND r.{$opposite_direction}_what_id = {$current_id}
                                        AND r.{$working_direction}_what_id != {$relationships[$what[$working_direction]['table']]}
                                        AND r." . $working_direction . "_what_id=" . $what[$working_direction]['singular'] . "_id
                                        AND r.relationship_type_id = rt.relationship_type_id
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
                                        if($what[$working_direction]['table'] == "companies") {
                                            $sql = "SELECT COUNT(contact_id) as agent_count, company_id
                                                    FROM contacts
                                                    WHERE company_id = $current_id2
                                                    GROUP BY company_id";
                                            $rst4 = $con->execute($sql);
                                            if(!$rst4) {
                                                db_error_handler($con, $sql);
                                            }
                                            elseif(!$rst4->EOF) {
                                                $agent_count = $rst4->fields['agent_count'];
                                                $rst4->close();
                                            }
                                            $address = get_formatted_address($con, false, $current_id2, true);
                                        }
                    
                                        $opportunity_id = 0;
                                        if(($what[$working_direction]['table'] == "companies") || ($what[$working_direction]['table'] == "contacts")) {
                                            $sql = "SELECT opportunity_id
                                                    FROM opportunities
                                                    WHERE {$what[$working_direction]['singular']}_id = $current_id2
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
                                            . $rst3->fields[$opposite_direction . '_what_text'].":<br>"
                                            . $rst3->fields['pre_formatting'];
                                        if($opportunity_id) {
                                            $relationship_link_rows .= "*";
                                        }
                                        $relationship_link_rows .= "<a href='$http_site_root/{$what[$working_direction]['table']}/one.php?{$what[$working_direction]['singular']}_id="
                                            . $current_id2 . "'>" 
                                            . $rst3->fields['name'] 
                                            . "</a>";
                                        if($agent_count) {
                                            $relationship_link_rows .= " (" . $agent_count . ") ";
                                        }
                                        $relationship_link_rows .= $rst3->fields['post_formatting'] . "\n";
                                        
                                        $name_to_get = $con->Concat("c." . implode(", ' ' , c.", table_name($what[$opposite_direction]['table'])));
                            
                                        $sql = "SELECT rt.relationship_type_id, rt.relationship_name, rt.from_what_table, rt.to_what_table, rt.from_what_text,
                                                    rt.to_what_text, rt.relationship_status, rt.pre_formatting, rt.post_formatting,
                                                    r.relationship_id, r.from_what_id, r.to_what_id, r.relationship_type_id, r.established_at, r.ended_on, r.relationship_status,
                                                    c." . $what[$opposite_direction]['singular'] . "_id, " . $name_to_get . " as name
                                                FROM relationship_types as rt, relationships as r, " . $what[$opposite_direction]['table'] . " as c
                                                WHERE {$working_direction}_what_id = {$current_id2}
                                                AND {$opposite_direction}_what_id != {$current_id}
                                                AND r.relationship_type_id in (" . implode(',', $relationship_type_ids) . ")
                                                AND r.relationship_status='a'
                                                AND r." . $opposite_direction . "_what_id=" . $what[$opposite_direction]['singular'] . "_id
                                                AND r.relationship_type_id = rt.relationship_type_id
                                                GROUP BY c." . $what[$opposite_direction]['singular'] . "_id, " . $name_to_get
                                                . ", rt.relationship_type_id, rt.relationship_name, rt.from_what_table, rt.to_what_table, rt.from_what_text,
                                                   rt.to_what_text, rt.relationship_status, rt.pre_formatting, rt.post_formatting,
                                                   r.relationship_id, r.from_what_id, r.to_what_id, r.relationship_type_id, r.established_at, r.ended_on, r.relationship_status";
    
                                        $rst4 = $con->execute($sql);
                                        if(!$rst4) {
                                            db_error_handler($con, $sql);
                                        }
                                        elseif(!$rst4->EOF) {
                                            $relationship_link_rows .= " &bull;"
                                            . " <a href=\"$http_site_root" . current_page("expand={$what[$working_direction]['table']}&id=$current_id2", "associated") . "\">"
                                            . $rst4->rowcount() . " other";
                                            if($rst4->rowcount() > 1) {
                                                $relationship_link_rows .= "s";
                                            }
                                        }
                                        $relationship_link_rows .= "</a>";
                                        if($address) {
                                            $relationship_link_rows .= "<br>" . $address;
                                        }
                                        $relationship_link_rows .= "</td></tr>\n";
                                        $rst3->movenext();
                                    }
                                    $rst3->close();
                                }
                            }
                            $rst2->movenext();
                        }
                    }
                    if(($both || $i) && $working_direction == 'from') {
                        $working_direction = 'to';
                        $opposite_direction = 'from';
                    }
                    elseif($both || $i) {
                        $working_direction = 'from';
                        $opposite_direction = 'to';
                    }
                }
            }
            $rst->movenext();
        }
    }
}

$relationships = $ori_relationships;

//put in the new button
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
                <td class=widget_label colspan=2 align=center>" . _("Add Relationship") . "</td>";
}

$relationship_link_rows .= "
            <tr>
            <form action='" . $http_site_root . "/relationships/new-relationship.php' method='post'>
                <input type=hidden name=on_what_id>
                <input type=hidden name=return_url value=\"" . current_page() . "\">
                <td class=widget_content_form_element colspan=2>";

$relationship_link_rows.='<input type=hidden name="relationship_entities" value="'.urlencode(serialize($relationships)).'">';
$relationship_link_rows .= "
                    <input type=submit class=button value=\""._("New Relationship")."\">
                </td>
            </form>
            </tr><!-- Form End -->";

$relationship_link_rows .= "        <!-- Content End --></table>\n</div>";

/**
 * $Log: sidebar.php,v $
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
