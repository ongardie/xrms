<?php
/**
 * Associated Companies Sidebar
 *
 * Include this file anywhere you want to show other relationships
 *
 * @param string $relationship_name Name of the relationship as named in relationship_types
 * @param string $working_direction From or To: Starting point as shown in relationships table
 * @param string $from_what_id/$to_what_id Where from/to is the same as working direction
 *
 * @author Brad Marshall
 * @author Neil Roberts
 *
 * $Id: sidebar.php,v 1.3 2004/07/14 14:08:53 neildogg Exp $
 */

$expand_id = isset($_GET['expand_id']) ? $_GET['expand_id'] : '';

$sql = "SELECT from_what_table, to_what_table
        FROM relationship_types
        WHERE relationship_name='$relationship_name'";
$rst = $con->execute($sql);

$what_table['from']          = $rst->fields['from_what_table'];
$what_table_singular['from'] = make_singular($what_table['from']);
$what_table['to']            = $rst->fields['to_what_table'];
$what_table_singular['to']   = make_singular($what_table['to']);

$rst->close();

// TBD - BUG - $to_what_id and/or $from_what_id NEED to be defined here

if($working_direction == "from") {
    $opposite_direction = "to";
    $overall_id = $from_what_id;
}
else {
    $opposite_direction = "from";
    $overall_id = $to_what_id;
}
$display_name          = ucfirst($what_table[$working_direction]);
$display_name_singular = ucfirst($what_table_singular[$working_direction]);
$opposite_name         = ucfirst($what_table[$opposite_direction]);

$relationship_type_ids = array();

$sql = "SELECT * 
        FROM relationship_types 
        WHERE relationship_name = '$relationship_name'";
$rst = $con->execute($sql);
if (!$rst) {
    db_error_handler($con, $sql);
}
elseif($rst->rowcount()) {
    while(!$rst->EOF) {
        $relationship_type_id = $rst->fields['relationship_type_id'];

        $relationship_arr[$relationship_type_id]['pre_formatting'] = $rst->fields['pre_formatting'];
        $relationship_arr[$relationship_type_id]['post_formatting'] = $rst->fields['post_formatting'];
        $relationship_arr[$relationship_type_id]['from_what_text'] = $rst->fields['from_what_text'];
        $relationship_arr[$relationship_type_id]['to_what_text'] = $rst->fields['to_what_text'];
        $relationship_type_ids[] = $rst->fields['relationship_type_id'];
        $rst->movenext();
    }
}
$rst->close();

//build the table heading
$relationship_link_rows = "<div id='company_link_sidebar'>
        <table class=widget cellspacing=1 width=\"100%\">
            <tr>
                <td colspan=2 class=widget_header colspan=4>Associated $opposite_name</td>
            </tr>
            <tr>
                <td class=widget_label>$opposite_name</td><td align=right class=widget_label>Other $display_name</td>
            </tr>\n";
//build the companies sql query

$name_to_get = $con->Concat("c." . implode(", ' ' , c.", table_name($what_table[$opposite_direction])));

$sql = "SELECT r.*, c." . $what_table_singular[$opposite_direction] . "_id, " . $name_to_get . " as name
        FROM relationships as r, " . $what_table[$opposite_direction] . " as c
        WHERE r." . $working_direction . "_what_id=" . $overall_id . "
        AND r.relationship_type_id in (" . implode(',', $relationship_type_ids) . ")
        AND r.relationship_status='a'
        AND r." . $opposite_direction . "_what_id=" . $what_table_singular[$opposite_direction] . "_id
        GROUP BY c." . $what_table_singular[$opposite_direction] . "_id";

//uncomment the debug line to see what's going on with the query
//$con->debug=1;
$rst = $con->execute($sql);
if(!$rst) {
    db_error_handler($con, $sql);
}
elseif($rst->rowcount()) {
    while(!$rst->EOF) {
        $current_id = $rst->fields[$what_table_singular[$opposite_direction] . '_id'];
        // $current_ids used later to make sure there aren't duplicate names
        $current_ids[] = $current_id;

        // Create the Initial entry
        $relationship_link_rows .= "<tr><td class=widget_content colspan=2 align=left>\n"
            . $relationship_arr[$rst->fields['relationship_type_id']][$working_direction . '_what_text'].":<br>"
            . $relationship_arr[$rst->fields['relationship_type_id']]['pre_formatting']
            . "<a href='$http_site_root/$what_table[$opposite_direction]/one.php?$what_table_singular[$opposite_direction]_id="
            . $current_id . "'>" . $rst->fields['name'] . "</a> "
            . $relationship_arr[$rst->fields['relationship_type_id']]['post_formatting'] . "\n"
            . " &bull;"
            . " <a href='$http_site_root/relationships/edit.php?working_direction=$opposite_direction"
            . "&on_what_table=" . $what_table[$opposite_direction]
            . "&relationship_id=" . $rst->fields['relationship_id']
            . "&return_url=" . $what_table[$working_direction]
            . "/one.php?$what_table_singular[$working_direction]_id=$overall_id"
            . "'>Edit</a>"
            . "</td></tr>\n";

        $name_to_get = $con->Concat("c." . implode(", ' ' , c.", table_name($what_table[$working_direction])));
        // Create the shared association entries
        $sql = "SELECT r.*, " . $name_to_get . " as name, c." . $what_table_singular[$working_direction] . "_id
                FROM relationships as r, " . $what_table[$working_direction] . " as c
                WHERE r." . $opposite_direction . "_what_id=" . $current_id . "
                AND relationship_type_id in (" . implode(',', $relationship_type_ids) . ")
                AND r." . $working_direction . "_what_id!=" . $overall_id . "
                AND r." . $working_direction . "_what_id=c." . $what_table_singular[$working_direction] . "_id
                AND r.relationship_status='a'";
        $rst2 = $con->execute($sql);
        if(!$rst2) {    
            db_error_handler($con, $sql);
        } 
        elseif($rst2->rowcount()) {
            while(!$rst2->EOF) {
                $current_id2 = $rst2->fields[$what_table_singular[$working_direction] . '_id'];
                // Find out how many relationships exist below this user
                $sql = "SELECT " . $opposite_direction . "_what_id
                    FROM relationships
                    WHERE " . $working_direction . "_what_id=" . $rst2->fields[$what_table_singular[$working_direction] . '_id'] . "
                    AND " . $opposite_direction . "_what_id!=" . $current_id . "
                    AND relationship_type_id IN (" . implode(',', $relationship_type_ids) . ")
                    AND relationship_status='a'";
                $rst3 = $con->execute($sql);
                $count = $rst3->rowcount();
                $rst3->close();

                $relationship_link_rows .= "<tr><td class=widget_content colspan=2 align=right>\n"
                    . $relationship_arr[$rst2->fields['relationship_type_id']][$opposite_direction . '_what_text'].":<br>"
                    . $relationship_arr[$rst2->fields['relationship_type_id']]['pre_formatting']
                    . "<a href='$http_site_root/$what_table[$working_direction]/one.php?$what_table_singular[$working_direction]_id="
                    . $current_id2 . "'>" . $rst2->fields['name'] . "</a>"
                    . $relationship_arr[$rst2->fields['relationship_type_id']]['post_formatting'] . "\n";
                if($count > 0) {
                    $relationship_link_rows .= " &bull;"
                        . " <a href='one.php?$what_table_singular[$working_direction]_id=$overall_id&expand_id=" . $rst2->fields[$what_table_singular[$working_direction] . '_id'] . "#associated'>"
                        . "$count other";
                    if($count > 1) {
                        $relationship_link_rows .= "s";
                    }
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
else {
    $relationship_link_rows .= "            <tr> <td class=widget_content colspan=4> No attached companies </td> </tr>\n";
}

//put in the new button
$relationship_link_rows .= "
            <tr>
            <form action='" . $http_site_root . "/relationships/new-relationship.php' method='post'>
                <td class=widget_content_form_element colspan=2>
                    <input type=hidden name=relationship_name value='company link'>
                    <input type=hidden name=on_what_id value='$overall_id'>
                    <input type=hidden name=working_direction value='$working_direction'>
                    <input type=hidden name=return_url value='/$what_table[$working_direction]/one.php?$what_table_singular[$working_direction]_id=$overall_id'>
                    <input type=submit class=button value='New'>
                </td>
            </form>
            </tr>";

//now close the table, we're done
$relationship_link_rows .= "        </table>\n</div>";

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
             $relationship_link_rows .= "<tr><td class=widget_content>"
                 . $relationship_arr[$rst->fields['relationship_type_id']][$working_direction . '_what_text'].":<br>"
                 . $relationship_arr[$rst->fields['relationship_type_id']]['pre_formatting']
                 . "<a href='$http_site_root/$what_table[$opposite_direction]/one.php?$what_table_singular[$opposite_direction]_id="
                 . $rst->fields[$what_table_singular[$opposite_direction] . '_id'] . "'>"
                 . $rst->fields['name'] . "</a>"
                 . $relationship_arr[$rst->fields['relationship_type_id']]['post_formatting'] . "<br>\n"
                 . "</td></tr>\n";
        }
        $rst->movenext();
    }

    //now close the table, we're done
    $relationship_link_rows .= "        </table>\n</div>";
}

/**
 * $Log: sidebar.php,v $
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
