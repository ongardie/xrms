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
 * @param string $relationship_name Name of the relationship as named in relationship_types
 * @param string $working_direction from or to or both: Starting point as shown in relationships table
 * @param string $overall_id Where from/to is the same as working direction
 *
 * @author Brad Marshall
 * @author Neil Roberts
 *
 * $Id: sidebar.php,v 1.18 2004/12/30 20:12:55 vanmer Exp $
 */

global $relationship_entries;
if ((!$relationship_entries) OR count($relationship_entries)==0) {
    $relationship_entries=array($on_what_table=>$overall_id);
}

$expand_id = isset($_GET['expand_id']) ? $_GET['expand_id'] : '';
$found = 0;
$orig_working_direction = $working_direction;

$sql = "SELECT from_what_table, to_what_table
        FROM relationship_types
        WHERE relationship_name='$relationship_name'";
$rst = $con->execute($sql);

$what_table['from']          = $rst->fields['from_what_table'];

$what_table_singular['from'] = make_singular($what_table['from']);
$what_table['to']            = $rst->fields['to_what_table'];
$what_table_singular['to']   = make_singular($what_table['to']);
//$on_what_table=$what_table['to'];

$rst->close();

/*
$loop = 0;
if($working_direction == "from") {
    $opposite_direction = "to";
}
elseif($working_direction == "to") {
    $opposite_direction = "from";
}
else {
    $working_direction = "from";
    $opposite_direction = "to";
    $loop = 1;
}
*/
global $relationship_link_rows;
if (!$directions) { $directions=array('to', 'from'); }
$display_name          = ucfirst($what_table[$working_direction]);
$display_name_singular = ucfirst($what_table_singular[$working_direction]);
$opposite_name         = ucfirst($what_table[$opposite_direction]);

foreach ($relationship_entries as $relationship_on_what_table=>$relationship_on_what_id) {
    foreach ($directions as $working_direction) {
        if($working_direction == "from") {
            $opposite_direction = "to";
        }
        elseif($working_direction == "to") {
            $opposite_direction = "from";
        }
        $sql = "SELECT * FROM relationship_types WHERE (({$working_direction}_what_table=".$con->qstr($on_what_table) . ") AND (relationship_status='a'))"; 
        $typerst=$con->execute($sql);
        if (!$typerst) { db_error_handler($con, $sql); }
        elseif ($typerst->numRows()>0) {
        
            if (!(isset($relationship_link_rows) && $relationship_link_rows)) {
                //build the table heading
                $relationship_link_rows = "        <div id='company_link_sidebar'>
                    <table class=widget cellspacing=1 width=\"100%\">
                        <tr>
                            <td colspan=2 class=widget_header colspan=4>"._("Relationships")."</td>
                        </tr>
                        <!-- Content Start -->";
            }
            else {
                list($relationship_link_rows,) = split('<!-- Content End -->', $relationship_link_rows, 2);
            }
    
            $relationship_type_ids = array();
            $relationship_arr=array();
            while(!$typerst->EOF) {
                $relationship_type_id = $typerst->fields['relationship_type_id'];
                $what_table['from']          = $typerst->fields['from_what_table'];
                
                $what_table_singular['from'] = make_singular($what_table['from']);
                $what_table['to']            = $typerst->fields['to_what_table'];
                $what_table_singular['to']   = make_singular($what_table['to']);
        
                $relationship_arr[$relationship_type_id]['pre_formatting'] = $typerst->fields['pre_formatting'];
                $relationship_arr[$relationship_type_id]['post_formatting'] = $typerst->fields['post_formatting'];
                $relationship_arr[$relationship_type_id]['from_what_text'] = $typerst->fields['from_what_text'];
                $relationship_arr[$relationship_type_id]['to_what_text'] = $typerst->fields['to_what_text'];
                $relationship_arr[$relationship_type_id]['what_table']=$what_table;
                $relationship_arr[$relationship_type_id]['what_table_singular']=$what_table_singular;
                $relationship_type_ids[] = $typerst->fields['relationship_type_id'];
                
                $typerst->movenext();
            }
            $typerst->close();
            foreach ($relationship_arr as $relationship_type_id=>$relationship_info) {
                //build the relationships sql query
                $name_to_get = $con->Concat("c." . implode(", ' ' , c.", table_name($relationship_info['what_table'][$opposite_direction])));
                $sql = "SELECT r.*, c." . $relationship_info['what_table_singular'][$opposite_direction] . "_id, " . $name_to_get . " as name
                        FROM relationships as r, " . $relationship_info['what_table'][$opposite_direction] . " as c
                        WHERE r." . $working_direction . "_what_id=" . $relationship_on_what_id . "
                        AND r.relationship_type_id = $relationship_type_id 
                        AND r.relationship_status='a'
                        AND r." . $opposite_direction . "_what_id=" . $relationship_info['what_table_singular'][$opposite_direction] . "_id
                        /* GROUP BY c." . $relationship_info['what_table_singular'][$opposite_direction] . "_id */";
                
                //uncomment the debug line to see what's going on with the query
                //$con->debug=1;
                $rst = $con->execute($sql);
                if(!$rst) {
                    db_error_handler($con, $sql);
                }
                elseif($rst->rowcount()) {
                    if($i == 0) {
                        $relationship_link_rows .= "<tr>
                            <td class=widget_label>" . _($opposite_name) . "</td>
                            <td align=right class=widget_label>" . _("Other ". $display_name) . "</td>
                        </tr>\n";
                    }
                    $found += $rst->rowcount();
                    while(!$rst->EOF) {
                        $current_id = $rst->fields[$relationship_info['what_table_singular'][$opposite_direction] . '_id'];
                        // $current_ids used later to make sure there aren't duplicate names
                        $current_ids[] = $current_id;
            
                        $agent_count = 0;
                        $address = "";
                        if($relationship_info['what_table'][$opposite_direction] == "companies") {
                            $sql = "SELECT count(contact_id) as agent_count
                                    FROM contacts
                                    WHERE company_id = " . $current_id . "
                                    GROUP BY company_id";
                            $extra_rst = $con->execute($sql);
                            $agent_count = $extra_rst->fields['agent_count'];
                            $extra_rst->close();
                            $sql = "SELECT line1, city, province
                                    FROM addresses
                                    WHERE company_id = " . $current_id;
                            $extra_rst = $con->execute($sql);
                            $address = explode(" ", $extra_rst->fields['line1']);
                            while(count($address) > 3) {
                                array_pop($address);
                            }
                            $address = implode(" ", $address) . ", " 
                                . $extra_rst->fields['city'] . ", " 
                                . $extra_rst->fields['province'];
                            $extra_rst->close();
                        }
                        $opportunity_id = 0;
                        if(($relationship_info['what_table'][$opposite_direction] == "companies") or ($relationship_info['what_table'][$opposite_direction] == "contacts")) {
                            $sql = "SELECT opportunity_id
                                    FROM opportunities
                                    WHERE " . $relationship_info['what_table_singular'][$opposite_direction] . "_id = " . $current_id;
                            $opportunity_rst = $con->execute($sql);
                            $opportunity_id = $opportunity_rst->fields['opportunity_id'];
                            $opportunity_rst->close();
                        }
                    
                        // Create the Initial entry
                        $relationship_link_rows .= "<tr><td class=widget_content colspan=2 align=left>\n"
                            . $relationship_arr[$rst->fields['relationship_type_id']][$working_direction . '_what_text'].":<br>"
                            . $relationship_arr[$rst->fields['relationship_type_id']]['pre_formatting'];
                        if($opportunity_id) {
                            $relationship_link_rows .= "*";
                        }
                        $relationship_link_rows .= "<a href='$http_site_root/{$relationship_info['what_table'][$opposite_direction]}/one.php?{$relationship_info['what_table_singular'][$opposite_direction]}_id="
                            . $current_id . "'>" 
                            . $rst->fields['name']
                            . "</a> ";
                        if($agent_count) {
                            $relationship_link_rows .= " (" . $agent_count . ") ";
                        }
                        $relationship_link_rows .= $relationship_arr[$rst->fields['relationship_type_id']]['post_formatting'] . "\n"
                            . " &bull;"
                            . " <a href='$http_site_root/relationships/edit.php?working_direction=$opposite_direction"
                            . "&on_what_table=" . $relationship_info['what_table'][$opposite_direction]
                            . "&relationship_id=" . $rst->fields['relationship_id']
                            . "&return_url=" . $relationship_info['what_table'][$working_direction]
                            . "/one.php?{$relationship_info['what_table_singular'][$working_direction]}_id=$overall_id"
                            . "'>"._("Edit")."</a>";
                        if($address) {
                            $relationship_link_rows .= "<br>" . $address;
                        }
                        $relationship_link_rows .= "</td></tr>\n";
            
                        $name_to_get = $con->Concat("c." . implode(", ' ' , c.", table_name($relationship_info['what_table'][$working_direction])));
                        // Create the shared association entries
                        $sql = "SELECT r.*, " . $name_to_get . " as name, c." . $relationship_info['what_table_singular'][$working_direction] . "_id
                                FROM relationships as r, " . $relationship_info['what_table'][$working_direction] . " as c
                                WHERE r." . $opposite_direction . "_what_id=" . $current_id . "
                                AND relationship_type_id in (" . implode(',', $relationship_type_ids) . ")
                                AND r." . $working_direction . "_what_id!=" . $overall_id . "
                                AND r." . $working_direction . "_what_id=c." . $relationship_info['what_table_singular'][$working_direction] . "_id
                                AND r.relationship_status='a'";
                        $rst2 = $con->execute($sql);
                        if(!$rst2) {    
                            db_error_handler($con, $sql);
                        } 
                        elseif($rst2->rowcount()) {
                            while(!$rst2->EOF) {
                                $current_id2 = $rst2->fields[$relationship_info['what_table_singular'][$working_direction] . '_id'];
                                // Find out how many relationships exist below this user
                                $sql = "SELECT " . $opposite_direction . "_what_id
                                    FROM relationships
                                    WHERE " . $working_direction . "_what_id=" . $rst2->fields[$relationship_info['what_table_singular'][$working_direction] . '_id'] . "
                                    AND " . $opposite_direction . "_what_id!=" . $current_id . "
                                    AND relationship_type_id IN (" . implode(',', $relationship_type_ids) . ")
                                    AND relationship_status='a'";
                                $rst3 = $con->execute($sql);
                                $count = $rst3->rowcount();
                                $rst3->close();
            
                                $agent_count = 0;
                                $address = "";
                                if($relationship_info['what_table'][$working_direction] == "companies") {
                                    $sql = "SELECT count(contact_id) as agent_count
                                        FROM contacts
                                        WHERE company_id = " . $current_id2 . "
                                        GROUP BY company_id";
                                    $extra_rst = $con->execute($sql);
                                    $agent_count = $extra_rst->fields['agent_count'];
                                    $extra_rst->close();
                                    $sql = "SELECT line1, city, province
                                            FROM addresses
                                            WHERE company_id = " . $current_id2;
                                    $extra_rst = $con->execute($sql);
                                    $address = explode(" ", $extra_rst->fields['line1']);
                                    while(count($address) > 3) {
                                        array_pop($address);
                                    }
                                    $address = implode(" ", $address) . ", " 
                                        . $extra_rst->fields['city'] . ", " 
                                        . $extra_rst->fields['province'];
                                    $extra_rst->close();
                                }
                                $opportunity_id = 0;
                                if(($relationship_info['what_table'][$working_direction] == "companies") or ($relationship_info['what_table'][$working_direction] == "contacts")) {
                                    $sql = "SELECT opportunity_id
                                            FROM opportunities
                                            WHERE " . $relationship_info['what_table_singular'][$working_direction] . "_id = " . $current_id2;
                                    $opportunity_rst = $con->execute($sql);
                                    $opportunity_id = $opportunity_rst->fields['opportunity_id'];
                                    $opportunity_rst->close();
                                }   
            
                                $relationship_link_rows .= "<tr><td class=widget_content colspan=2 align=right>\n"
                                    . $relationship_arr[$rst2->fields['relationship_type_id']][$opposite_direction . '_what_text'].":<br>"
                                    . $relationship_arr[$rst2->fields['relationship_type_id']]['pre_formatting'];
                                if($opportunity_id) {
                                    $relationship_link_rows .= "*";
                                }
                                $relationship_link_rows .= "<a href='$http_site_root/{$relationship_info['what_table'][$working_direction]}/one.php?{$relationship_info['what_table_singular'][$working_direction]}_id="
                                    . $current_id2 . "'>" 
                                    . $rst2->fields['name'] 
                                    . "</a>";
                                if($agent_count) {
                                    $relationship_link_rows .= " (" . $agent_count . ") ";
                                }
                                $relationship_link_rows .= $relationship_arr[$rst2->fields['relationship_type_id']]['post_formatting'] . "\n";
                                if($count > 0) {
                                    $relationship_link_rows .= " &bull;"
                                    . " <a href='one.php?{$relationship_info['what_table_singular'][$working_direction]}_id=$overall_id&expand_id=" . $rst2->fields[$relationship_info['what_table_singular'][$working_direction] . '_id'] . "#associated'>"
                                    . "$count other";
                                    if($count > 1) {
                                        $relationship_link_rows .= "s";
                                    }
                                }
                                $relationship_link_rows .= "</a>";
                                if($address) {
                                    $relationship_link_rows .= "<br>" . $address;
                                }
                                $relationship_link_rows .= "</td></tr>\n";
                                $rst2->movenext();
                            }
                            $rst2->close();
                        }
                        $rst->movenext();
                    }
                    $rst->close();
                }
            
            }
            // This is pretty ugly, meaninng that it's very pretty for how ugly it should be
            // Basically, it means that a sidebar can be included a whole heck
            // of a lot of times and it will still have only one bottom bar.
            // Since it makes $relationship_link_rows global, it can be included as a plugin.
            if(preg_match("|<!-- Form Start -->([^\e]*)<!-- Form End -->|", $relationship_link_rows, $matched)) {
                $relationship_link_rows = str_replace($matched[0], '', $relationship_link_rows);
                $old_rows = $matched[1];
                preg_match_all("|(<input type=hidden[^\e]*>)|U", $old_rows, $matched);
                $amount = count($matched[0]) / 4;
            }
            else {
                $old_rows = '';
            }
            if(!(isset($amount) and $amount)) {
                $amount = 1;
            }
            if(isset($old_rows) and $old_rows) {
                preg_match("|<!-- Start Inputs -->([^\e]*)<!-- End Inputs -->|", $old_rows, $matched);
                $old_inputs = $matched[1];
            }
            else {
                $old_inputs = '';
            }
            $new_rows = "<!-- Start Inputs -->                    " . $old_inputs . "
                                <input type=hidden name=relationship_name_" . $amount . " value='" . $relationship_name . "'>
                                <input type=hidden name=on_what_id_" . $amount . " value='$overall_id'>
                                <input type=hidden name=working_direction_" . $amount . " value='$orig_working_direction'>
                                <input type=hidden name=return_url_" . $amount . " value='/$what_table[$opposite_direction]/one.php?$what_table_singular[$opposite_direction]_id=$overall_id'><!-- End Inputs -->";
            if(preg_match("|<!-- Start Options -->([^\e]*)<!-- End Options -->|", $old_rows, $matched)) {
                $old_options = $matched[1];
            }
            else {
                $old_options = '';
            }
            $new_options = "<!-- Start Options -->                    " . $old_options . "
                                <option>" . _($what_table[$opposite_direction] . "/" . $what_table[$working_direction]) . "</option><!-- End Options -->";
            
            //put in the new button
            $table_singular=make_singular($on_what_table);
            $returnurl="&return_url=/" . $on_what_table
                            . "/one.php?$table_singular"."_id=$overall_id";
        }    
    }
        if (count($relationship_type_ids)>0) {                                       
            $relationship_link_rows .= "\n<tr><td class=widget_content colspan=2><input type=button class=button value=\""._("Add Relationship")."\" name=AddRelationship onclick=\"javascript:location.href='$http_site_root/relationships/new-relationship.php?on_what_table=$on_what_table&on_what_id=$overall_id"."$returnurl'\"></td></tr>\n";       
        }
    /*
    $relationship_link_rows .= "<!-- Form Start --></table>\n</div>
            <div id='expanded_associated_by_sidebar'>
            <table class=widget cellspacing=1 width=\"100%\">
                <tr>
                    <td class=widget_header><a name=associated></a>"._("Add Relationship")."</td>
                </tr>
                <tr>
                <form action='" . $http_site_root . "/relationships/new-relationship.php' method='post'>
                    <td class=widget_content_form_element colspan=2>
                        <input type=hidden name=relationship_name value=''>
                        <input type=hidden name=on_what_id value=''>
                        <input type=hidden name=working_direction value=''>
                        <input type=hidden name=return_url value=''>
                        " . $new_rows . "
                        <select onchange=\"if(this.selectedIndex > 0) { 
                                            this.form.relationship_name.value = eval('this.form.relationship_name_' + this.selectedIndex + '.value');
                                            this.form.on_what_id.value = eval('this.form.on_what_id_' + this.selectedIndex + '.value');
                                            this.form.working_direction.value = eval('this.form.working_direction_' + this.selectedIndex + '.value');
                                            this.form.return_url.value = eval('this.form.return_url_' + this.selectedIndex + '.value'); }\">
                        <option>"._("Choose a relationship")."</option>
                        " . $new_options . "
                        </select>
                        <input type=submit class=button value=\""._("Next")."\">
                    </td>
                </form>
                </tr><!-- Form End -->";
    */                    
    
    $relationship_link_rows .= "        <!-- Content End --></table>\n</div>";
    
    // If the table is intended to be expanded, show all companies associated relationships (if they don't exist)
    if($expand_id) {
        $name_to_get = $con->Concat("o." . implode(", ' ' , o.", table_name($what_table[$opposite_direction])));
        $name_to_get2 = $con->Concat("c." . implode(", ' ' , c.", table_name($what_table[$working_direction])));
        $sql = "SELECT r.relationship_type_id, o." . $what_table_singular[$opposite_direction] . "_id, 
                        " . $name_to_get . " as name,
                        " . $name_to_get2 . " as name2
                    FROM relationships as r, " . $what_table[$opposite_direction] . " as o, " . $what_table[$working_direction] . " as c
                    WHERE c." . $what_table_singular[$working_direction] . "_id='$expand_id' 
                    AND relationship_type_id in (".implode(',', $relationship_type_ids).")
                    AND o." . $what_table_singular[$opposite_direction] . "_id=r." . $opposite_direction . "_what_id
                    AND c." . $what_table_singular[$working_direction] . "_id=r." . $working_direction . "_what_id
                    AND r.relationship_status = 'a'";
        $rst = $con->execute($sql);
        $relationship_link_rows .= "<div id='expanded_associated_by_sidebar'>
            <table class=widget cellspacing=1 width=\"100%\">
                <tr>
                    <td class=widget_header><a name=associated></a>" . $rst->fields['name2'] . "</td>
                </tr>
                <tr>
                    <td class=widget_label>$opposite_name</td>
                </tr>\n";
    
        while(!$rst->EOF) {
            if(!in_array($rst->fields[$what_table_singular[$opposite_direction] . '_id'], $current_ids)) {
            
                $agent_count = 0;
                $address = "";
                if($what_table[$opposite_direction] == "companies") {
                    $sql = "SELECT count(contact_id) as agent_count
                        FROM contacts
                        WHERE company_id = " . $rst->fields['company_id'] . "
                        GROUP BY company_id";
                    $extra_rst = $con->execute($sql);
                    $agent_count = $extra_rst->fields['agent_count'];
                    $extra_rst->close();
                    $sql = "SELECT line1, city, province
                            FROM addresses
                            WHERE company_id = " . $rst->fields['company_id'];
                    $extra_rst = $con->execute($sql);
                    $address = explode(" ", $extra_rst->fields['line1']);
                    while(count($address) > 3) {
                        array_pop($address);
                    }
                    $address = implode(" ", $address) . ", " 
                        . $extra_rst->fields['city'] . ", " 
                        . $extra_rst->fields['province'];
                    $extra_rst->close();
                }
                $opportunity_id = 0;
                if(($what_table[$opposite_direction] == "companies") or ($what_table[$opposite_direction] == "contacts")) {
                    $sql = "SELECT opportunity_id
                            FROM opportunities
                            WHERE " . $what_table_singular[$opposite_direction] . "_id = " . $rst->fields[$what_table_singular[$opposite_direction] . '_id'];
                    $opportunity_rst = $con->execute($sql);
                    $opportunity_id = $opportunity_rst->fields['opportunity_id'];
                }
            
                $relationship_link_rows .= "<tr><td class=widget_content>"
                    . $relationship_arr[$rst->fields['relationship_type_id']][$working_direction . '_what_text'].":<br>"
                    . $relationship_arr[$rst->fields['relationship_type_id']]['pre_formatting'];
                if($opportunity_id) {
                    $relationship_link_rows .= "*";
                }
                $relationship_link_rows .= "<a href='$http_site_root/$what_table[$opposite_direction]/one.php?$what_table_singular[$opposite_direction]_id="
                    . $rst->fields[$what_table_singular[$opposite_direction] . '_id'] . "'>"
                    . $rst->fields['name'] . "</a>";
                if($agent_count) {
                    $relationship_link_rows .= " (" . $agent_count . ") ";
                }
                $relationship_link_rows .= $relationship_arr[$rst->fields['relationship_type_id']]['post_formatting'] . "\n";
                if($address) {
                    $relationship_link_rows .= "<br>" . $address;
                }
                $relationship_link_rows .= "</td></tr>\n";
            }
            $rst->movenext();
        }
        $relationship_link_rows .= '        </table>
            </div>';
    
    }
}
/**
 * $Log: sidebar.php,v $
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
