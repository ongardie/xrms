<?php
/**
 * Associated Companies Sidebar
 *
 * Include this file anywhere you want to show other companies/customers tied to this contact
 *
 * @param integer $contact_id/$company_id The contact_id or company_id should be set before including this file
 *
 * @author Brad Marshall
 * @author Neil Roberts
 *
 * $Id: company-sidebar.php,v 1.3 2004/07/07 20:55:39 neildogg Exp $
 */

$what_table['from'] = "contacts";
$what_table_singular['from'] = "contact";
$what_table['to'] = "companies";
$what_table_singular['to'] = "company";
$relationship_name = "company link";
if($_GET['contact_id']) {
    $working_direction = "from";
    $opposite_direction = "to";
    $display_name = "Contacts";
    $display_name_singular = "Contact";
    $opposite_name = "Companies";
    $overall_id = $contact_id;
}
else {
    $working_direction = "to";
    $opposite_direction = "from";
    $display_name = "Companies";
    $display_name_singular = "Company";
    $opposite_name = "Contacts";
    $overall_id = $company_id;
}
$from_what_id = $contact_id;
$to_what_id = $company_id;
$relationship_type_ids = array();

$rel_sql = "select * from relationship_types where relationship_name = '$relationship_name'";
$relationship_type_query= $con->execute($rel_sql);
if ($relationship_type_query) {
    while(!$relationship_type_query->EOF) {
        $relationship_type_id = $relationship_type_query->fields['relationship_type_id'];

        $relationship_arr[$relationship_type_id]['pre_formatting'] = $relationship_type_query->fields['pre_formatting'];
        $relationship_arr[$relationship_type_id]['post_formatting'] = $relationship_type_query->fields['post_formatting'];
        $relationship_arr[$relationship_type_id]['from_what_text'] = $relationship_type_query->fields['from_what_text'];
        $relationship_arr[$relationship_type_id]['to_what_text'] = $relationship_type_query->fields['to_what_text'];
        $relationship_type_ids[] = $relationship_type_query->fields['relationship_type_id'];
        $relationship_type_query->movenext();
    }
} else {
    db_error_handler ($con, $rel_sql);
}

if ($relationship_type_ids) {
    // This merely lets us know which relationships need not be repeated
    //Upgrade this with more current mySQL calls
    $sql = "select $opposite_direction" . "_what_id
        from relationships
        where $working_direction" . "_what_id='" . $overall_id . "'";
        $sql .= " and relationship_type_id in (".implode(',', $relationship_type_ids).")";
        $sql .= " and relationship_status='a'";
    $rst3 = $con->execute($sql);
    $overall = array();
    if ($rst3) {
        while(!$rst3->EOF) {
            $overall[] = $rst3->fields[$opposite_direction . '_what_id'];
            $rst3->movenext();
        }
        $rst3->close();
    }
}
//build the table heading
$company_link_rows = "<div id='company_link_sidebar'>
        <table class=widget cellspacing=1 width=\"100%\">
            <tr>
                <td colspan=2 class=widget_header colspan=4>Associated $opposite_name</td>
            </tr>
            <tr>
                <td class=widget_label>$opposite_name</td><td align=right class=widget_label>Other $display_name</td>
            </tr>\n";
//build the companies sql query

if($working_direction == "from") {
    $sql = "select r.*, c.company_id, c.company_name, a.city, a.province
        from relationships as r, companies as c, addresses as a
        where r.from_what_id=$from_what_id ";
        $sql .= " and relationship_type_id in (".implode(',', $relationship_type_ids).")";
        $sql .= " and r.to_what_id=c.company_id
        and c.default_primary_address = a.address_id
        and relationship_status='a'
        order by c.company_name";
}
else {
   $sql = "select r.*, c.company_id, c.contact_id, c.first_names, c.last_name, a.city, a.province
       from relationships as r, contacts as c, addresses as a
       where r.to_what_id = $to_what_id ";
        $sql .= " and relationship_type_id in (".implode(',', $relationship_type_ids).")";
        $sql .= " and r.from_what_id=c.contact_id
       and c.address_id = a.address_id
       and relationship_status = 'a'
       order by c.last_name";
}

//uncomment the debug line to see what's going on with the query
//$con->debug=1;
$rst = $con->execute($sql);
if ($rst) {
if($rst->rowcount()) {
    while(!$rst->EOF) {
        $current_id = $rst->fields[$what_table_singular[$opposite_direction] . '_id'];
        // $company_ids used later to make sure there aren't duplicate company names
        $current_ids[] = $current_id;

//$con->debug  = 1;
       // Grab the name and ID of all the contacts that are associated with this company
        if($working_direction == "from") {
            $sql = "select r.*, c.first_names, c.last_name, c.contact_id
                from relationships as r, contacts as c
                where r.to_what_id=$current_id";
                $sql .= " and relationship_type_id in (".implode(',', $relationship_type_ids).")";
                $sql .= "and r.from_what_id!=$from_what_id
                and r.from_what_id=c.contact_id
                and r.relationship_status='a'";
        }
        else {
            // Grab the name and ID of all the companies associated with this user
            $sql = "select r.*, c.company_id, c.company_name
                from relationships as r, companies as c
                where r.from_what_id = $current_id";
                $sql .= " and relationship_type_id in (".implode(',', $relationship_type_ids).")";
                $sql .= " and r.to_what_id != $to_what_id
                and r.to_what_id = c.company_id";
        }
        // Grey out the background of the company set to the user default.
        if($rst->fields[company_id] == $to_what_id) {
            $class = "closed_activity";
        }
        else {
            $class = "widget_content";
        }

        // Create the Initial entry
        if($working_direction == "to") {
            //$from_what_id = $rst->fields['contact_id'];
            $name = $rst->fields['first_names'] . " " . $rst->fields['last_name'];
        }
        else {
            $name = $rst->fields['company_name'];
        }
        $company_link_rows .= "<tr><td class=$class colspan=2 align=left>\n"
                . $relationship_arr[$rst->fields['relationship_type_id']][$working_direction . '_what_text'].":<br>"
                . $relationship_arr[$rst->fields['relationship_type_id']]['pre_formatting']
                . "<a href='$http_site_root/$what_table[$opposite_direction]/one.php?$what_table_singular[$opposite_direction]_id="
                . $current_id . "'>" . $name . "</a>" . $relationship_arr[$rst->fields['relationship_type_id']]['post_formatting'] . "\n"
                . " &bull;"
                . " <a href='$http_site_root/companies/company-edit.php?working_direction=$working_direction"
                . "&relationship_id=" . $rst->fields['relationship_id']
                . "&return_url=" . $what_table[$working_direction]
                . "/one.php?$what_table_singular[$working_direction]_id=$overall_id"
                . "'>Edit</a>";
                if($working_direction == "from") {
                    $company_link_rows .= "<br>" . $rst->fields['city'] . ", " . $rst->fields['province'];
                }
        $company_link_rows .= "</td></tr>\n";

        // Create the shared association entries
        $rst2 = $con->execute($sql);
        if($rst2->rowcount()) {

            while(!$rst2->EOF) {
                $current_contact_id = $rst2->fields[$what_table_singular[$working_direction] . '_id'];
                //Upgrade this when the new version of mySQL comes out
                $sql = "select $opposite_direction" . "_what_id
                    from relationships
                    where (relationship_type_id=";
                    $sql .= join(" or relationship_type_id=", $relationship_type_ids);
                    $sql .= ") and $working_direction" . "_what_id='" . $current_contact_id . "'
                    and relationship_status='a'";
                $rst3 = $con->execute($sql);
                $count = 0;
                while(!$rst3->EOF) {
                    if(!in_array($rst3->fields[$opposite_direction . '_what_id'], $overall)) {
                         $count++;
                    }
                    $rst3->movenext();
                }
                $rst3->close();
                if($working_direction == "from") {
                    $name = $rst2->fields['first_names'] . ' ' . $rst2->fields['last_name'];
                }
                else {
                    $name = $rst2->fields['company_name'];
                }
                $company_link_rows .= "<tr><td class=widget_content colspan=2 align=right>\n"
                    . $relationship_arr[$rst2->fields['relationship_type_id']][$opposite_direction . '_what_text'].":<br>"
                    . $relationship_arr[$rst2->fields['relationship_type_id']]['pre_formatting']
                    . "<a href='$http_site_root/$what_table[$working_direction]/one.php?$what_table_singular[$working_direction]_id="
                    . $current_contact_id . "'>" . $name . '</a>'
                    . $relationship_arr[$rst2->fields['relationship_type_id']]['post_formatting'] . "\n";
                if($count == 1) {
                    $company_link_rows .= " &bull;"
                        . " <a href='one.php?$what_table_singular[$working_direction]_id=$overall_id&expand_id=" . $rst2->fields[$what_table_singular[$working_direction] . '_id'] . ">#associated'>"
                        . "$count other";
                }
                elseif($count > 1) {
                    $company_link_rows .= " &bull;"
                        . " <a href='one.php?$what_table_singular[$working_direction]_id=$overall_id&expand_id=" . $rst2->fields[$what_table_singular[$working_direction] . '_id'] . "#associated'>"
                        . "$count others";
                }
                $company_link_rows .= "</td></tr>\n";
                $rst2->movenext();
            }
            $rst2->close();
        }
        $rst->movenext();
    }
    $rst->close();
}
}
else {
    $company_link_rows .= "            <tr> <td class=widget_content colspan=4> No attached companies </td> </tr>\n";
}

//put in the new button
if (strlen($on_what_table)>0){
    $company_link_rows .= "
            <tr>
            <form action='".$http_site_root."/companies/new-company.php' method='post'>
                <td class=widget_content_form_element colspan=2>
                    <input type=hidden name=from_what_id value='$from_what_id'>
                    <input type=hidden name=relationship_name value='company link'>
                    <input type=hidden name=to_what_id value='$to_what_id'>
                    <input type=hidden name=working_direction value='$working_direction'>
                    <input type=hidden name=return_url value='/$what_table[$working_direction]/one.php?$what_table_singular[$working_direction]_id=$overall_id'>
                    <input type=submit class=button value='New'>
                </td>
            </form>
            </tr>";
}

//now close the table, we're done
$company_link_rows .= "        </table>\n</div>";

// If the table is intended to be expanded, show all companies associated with this customer (except for the shared companies)
$expand_id = $_GET['expand_id'];
if($expand_id) {
    if($working_direction == "from") {
      $expand_sql = "select r.relationship_type_id, c.contact_id, o.company_name, o.company_id, c.first_names, c.last_name, a.city, a.province
        from relationships as r, companies as o, contacts as c, addresses as a
        where c.contact_id='$expand_id' ";
      $expand_sql .= " and relationship_type_id in (".implode(',', $relationship_type_ids).")";
      $expand_sql .=" and c.contact_id=r.from_what_id
        and r.to_what_id=o.company_id
        and o.default_primary_address = a.address_id
        and r.relationship_status = 'a'";
    }
    else {
      $expand_sql = "select r.relationship_type_id, c.contact_id, o.company_name, o.company_id, c.first_names, c.last_name
        from relationships as r, companies as o, contacts as c
        where o.company_id='$expand_id' and ";
      $expand_sql .= " and relationship_type_id in (".implode(',', $relationship_type_ids).")";
      $expand_sql .= " and o.company_id=r.to_what_id
        and r.from_what_id=c.contact_id
        and r.relationship_status = 'a'";
    }
    $rst = $con->execute($expand_sql);
    $name = array();
    $name['from'] = $rst->fields['first_names'] . " " . $rst->fields['last_name'];
    $name['to'] = $rst->fields['company_name'];
    $company_link_rows .= "<div id='companies_associated_by_sidebar'>
        <table class=widget cellspacing=1 width=\"100%\">
            <tr>
                <td class=widget_header><a name=associated></a>$name[$working_direction]</td>
            </tr>
            <tr>
                <td class=widget_label>$opposite_name</td>
            </tr>\n";

    while(!$rst->EOF) {
        $name = array();
        $name['from'] = $rst->fields['first_names'] . " " . $rst->fields['last_name'];
        $name['to'] = $rst->fields['company_name'];
        if(!in_array($rst->fields[$what_table_singular[$opposite_direction] . '_id'], $current_ids)) {
             $company_link_rows .= "<tr><td class=widget_content>"
                 . $relationship_arr[$rst->fields['relationship_type_id']][$working_direction . '_what_text'].":<br>"
                 . $relationship_arr[$rst->fields['relationship_type_id']]['pre_formatting']
                 . "<a href='$http_site_root/$what_table[$opposite_direction]/one.php?$what_table_singular[$opposite_direction]_id="
                 . $rst->fields[$what_table_singular[$opposite_direction] . '_id'] . "'>"
                 . $name[$opposite_direction] . "</a>"
                 . $relationship_arr[$rst->fields['relationship_type_id']]['post_formatting'] . "<br>\n";
             if($working_direction == "from") {
                 $company_link_rows .= $rst->fields['city'] . ", " . $rst->fields['province'] . "</td></tr>\n";
             }
        }
        $rst->movenext();
    }

    //now close the table, we're done
    $company_link_rows .= "        </table>\n</div>";
}

/**
 * $Log: company-sidebar.php,v $
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
