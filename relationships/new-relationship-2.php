<?php
/**
 * Associated Relationships
 *
 * Submit from new-relationships to return search.
 *
 * @author Neil Roberts
 */


require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();

$possible_id = $_POST['possible_id'];
$on_what_id = $_POST['on_what_id'];
$return_url = $_POST['return_url'];
$search_on = ($_POST['possible_id']) ? $_POST['possible_id'] : $_POST['search_on'];
$relationship_entity = $_POST['relationship_entity'];
if ($relationship_entity) {    
    $relationship_entity_array=explode(",",$relationship_entity);
    $on_what_table=array_shift($relationship_entity_array);
    $on_what_id = array_shift($relationship_entity_array);
}
getGlobalVar($relationship_entities,'relationship_entities');

getGlobalVar($relationship_type_direction, 'relationship_type_direction');
$typedirection = explode(",", $relationship_type_direction);
$working_direction = $typedirection[1];
$relationship_type_id = $typedirection[0];

$con = get_xrms_dbconnection();

if($working_direction == "from") {
    $opposite_direction = "to";
}
else {
    $opposite_direction = "from";
}

$sql = "SELECT *
        FROM relationship_types
        WHERE relationship_type_id = $relationship_type_id";

$rst = $con->execute($sql);
if(!$rst) {
    db_error_handler($con, $sql);
}
elseif(!$rst->EOF) {
    $what_table = $rst->fields[$opposite_direction . '_what_table'];
    $opposite_table = $rst->fields[$working_direction . '_what_table'];
    $what_table_singular = make_singular($what_table);
    $opposite_table_singular = make_singular($opposite_table);
    $text = strtolower($rst->fields[$working_direction . "_what_text"]);

    $rst->close();
}

$display_name = ucfirst($what_table);
$display_name_singular = ucfirst($what_table_singular);

$name_to_get = $con->Concat(implode(", ' ' , ", table_name($opposite_table)));
$sql = "SELECT $name_to_get AS name
        FROM $opposite_table
        WHERE $opposite_table_singular" . "_id = $on_what_id";

$rst2 = $con->execute($sql);
if (!$rst2) {
    db_error_handler($con, $sql);
}
elseif(!$rst2->EOF) { 
    $name = $rst2->fields["name"];
    $rst2->close();
}
else {
    $name = $singular_table;
    $rst2->close();
}


if(empty($search_on) OR !eregi("[0-9]", $search_on)) {
    if(!empty($search_on)) {
        $search_on = $con->qstr("%$search_on%", get_magic_quotes_gpc());
    }
    //If you want to make this work for other tables, you should be able to edit utils-database.php with the proper names
    $name_order = implode(', ', array_reverse(table_name($what_table)));

    /**
    * SQL Compatibility Changes 
    * 1. Removed name from group by clause as the group by clause
    *       has contact name already
    * 2. When the relationship involves a contact
    *       Modified the
    *       having firstnames, lastname like $search_on 
    *       SQL clause to
    *       having (firstnames like $search_on) OR (lastname like $search_on)
    * 3. Adjust so order by clause will use 'name' for contacts,
    *       and table_name($what_table) for non-concat'd fields
    **/
    if ($what_table == 'contacts') {
        $name_concat = $con->Concat(implode(", ' ', ", table_name($what_table))) . ' as name';
        $search_name = 'last_name';
    } else {
        $name_concat = implode(", ' ', ", table_name($what_table));
        $search_name = implode(", ' ', ", table_name($what_table));
    }
    
    $sql = "SELECT $name_concat, {$what_table_singular}_id
            FROM $what_table
            WHERE {$what_table_singular}_record_status='a'";

    if(!empty($search_on) && $what_table == 'contacts' ) { 
        $sql.= " AND ((first_names LIKE $search_on) OR ( last_name LIKE $search_on)) ";
    }
    elseif(!empty($search_on)) {
        $sql .= " AND $name_order LIKE $search_on ";
    }

    $sql .= " GROUP BY {$what_table_singular}_id, $name_order
              ORDER BY $search_name";

    //$con->debug=1;
    $rst = $con->execute($sql);
    if(!$rst) {
        db_error_handler($con, $sql);
    }
    elseif(!$rst->EOF) {
        $search_output.= $rst->getmenu2('on_what_id2', '', false);
        $search_output.= " &nbsp; <input type=button class=button value='"._("More Info")."' "
            . "onclick='document.forms[0]." . $what_table_singular
            . "_id.value=document.forms[1].on_what_id2.options[document.forms[1].on_what_id2.selectedIndex].value; document.forms[0].submit();'>";
    }
    else {
        $error_msg= _("There is no $what_table_singular by that name");
    }
}
else {
    $name_concat = $con->Concat(implode(', \' \', ', table_name($what_table)));
    $sql = "SELECT {$what_table_singular}_id, $name_concat AS name
            FROM $what_table
            WHERE {$what_table_singular}_id = $search_on
            AND {$what_table_singular}_record_status='a'";

    $rst = $con->execute($sql);
    if(!$rst) {
        db_error_handler($con, $sql);
    }
    elseif(!$rst->EOF) {
        $search_output.= "<input type=hidden name=on_what_id2 value=$search_on>{$rst->fields['name']}\n";
    }
    else {
        $error_msg= _("There is no $what_table_singular by that ID");
    }
}
if ($error_msg) {
    $relationship_entities=urlencode($relationship_entities);
    Header("Location: new-relationship.php?msg=$error_msg&relationship_entities=$relationship_entities&return_url=$return_url");
}
$page_title = _("Add Relationship for ". $display_name_singular);
start_page($page_title, true, $msg);
$what_url=table_one_url($what_table, '');
?>

<div id="Main">
    <div id="Content">

        <form action="<?php echo $http_site_root . $what_url ?>" method=get target="_blank">
        <input type="hidden" name="<?php echo $what_table_singular; ?>_id">
        </form>
        <form action=new-relationship-3.php method=post <?php if($working_direction == "both") { ?>onsubmit="document.forms[1].working_direction.value = (document.forms[1].relationship_type_id.selectedIndex < (document.forms[1].relationship_type_id.length / 2)) ? 'from' : 'to'; return true;"<?php } ?>>
        <input type="hidden" name="relationship_type_id" value="<?php echo $relationship_type_id; ?>">
        <input type="hidden" name="working_direction" value="<?php echo $working_direction; ?>">
        <input type="hidden" name="real_working_direction" value="<?php echo $working_direction; ?>">
        <input type="hidden" name="on_what_id" value="<?php echo $on_what_id; ?>">
        <input type="hidden" name="return_url" value="<?php echo $return_url ?>">
        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header><?php echo $display_name; ?></td>
            </tr>
            <tr>
                <td class=widget_content_form_element><?php echo $name; ?> <?php echo $text; ?>
                    <?php echo $search_output; ?>
               </td>
            </tr>
            <tr>
                <td class=widget_content_form_element colspan=2>
                    <input class=button type=submit value="<?php echo $page_title; ?>">
<!---                    <input class=button name=return type=submit value="<?php echo $page_title; ?> and Return">--->
                </td>
            </tr>
        </table>
        </form>

    </div>

        <!-- right column //-->
    <div id="Sidebar">

        &nbsp;

    </div>
</div>

<?php

end_page();

/**
 * $Log: new-relationship-2.php,v $
 * Revision 1.27  2006/06/30 02:05:56  vanmer
 * - changed More Info link to work for all pages, by using table_one_url function to generate target to hit
 *
 * Revision 1.26  2006/01/02 23:31:01  vanmer
 * - changed to use centralized dbconnection function
 *
 * Revision 1.25  2005/05/09 01:34:46  vanmer
 * - changed output to occur after start_page, calculations to occur before start_page
 * - changed to redirect to search page when error occurs in search
 *
 * Revision 1.24  2005/01/12 18:18:11  vanmer
 * - added parsing of relationship_entity to provide ablity for multiple entities on original screen
 *
 * Revision 1.23  2005/01/11 21:07:16  neildogg
 * - Null search shows all
 *
 * Revision 1.22  2005/01/11 17:19:33  neildogg
 * - Fairly large update to make it work as it should
 *
 * Revision 1.21  2005/01/10 22:17:29  neildogg
 * - Adding a relationship now works without a relationship name
 *
 * Revision 1.20  2005/01/10 20:40:02  neildogg
 * - Supports parameter passing after the sidebar update
 *
 * Revision 1.19  2005/01/08 19:18:59  braverock
 * - add more portable handling of single field order by, group by
 *
 * Revision 1.18  2005/01/07 21:14:24  braverock
 * - sql portability change for HAVING clause on name search
 *
 * Revision 1.17  2004/12/01 18:24:59  vanmer
 * - changed to allow new-relationship-2 to be called using a type_id and direction (from new new-relationship.php)
 * - changed to display relationship creation as a sentence
 *
 * Revision 1.16  2004/09/29 21:25:40  braverock
 * - removed second two concats to replace with
 *    'group by name' and 'having name' clauses
 *
 * Revision 1.15  2004/09/29 17:33:36  braverock
 * - roll back rev 1.14, I mucked up the code for Contact searches
 * - still a bug in this version where having clause claims no column company_name
 * - bug appears in 'contact s/companies' searches for companies
 *
 * Revision 1.14  2004/09/29 15:20:55  braverock
 * - removed other unecessary uses of concat
 * - removed having clause in favor of simple 'and' in where clause
 * - left 'group by' in case it is still needed by mssql
 *
 * Revision 1.13  2004/09/29 14:55:17  braverock
 * - fix incorrect use of concat when it should have been $what_table_singular.'_name'
 *
 * Revision 1.12  2004/09/29 14:27:06  braverock
 * - add additional db_error_handler clauses to aid debugging
 *
 * revision 1.11 2004-09-29 08:37 braverock
 * - add db_error_handler to help debug non-object error
 *
 * revision 1.10 2004-09-13 06:47 introspectshun
 * - Fixed db incompatibility with HAVING clause (now uses expression rather than alias)
 * - Added GROUP BY clause, as HAVING doesn't work without it on MSSQL
 *
 * revision 1.9 2004-07-28 01:28 neildogg
 * - Can now add multiple relationships with new button
 *
 * revision 1.8 2004-07-28 12:59 neildogg
 * - Added drop down box if added a contact to a company
 *
 * revision 1.7 2004-07-25 05:50 johnfawcett
 * - Removed lang/ - I clobbered an earlier change by Brian
 *
 * revision 1.6 2004-07-25 05:11 johnfawcett
 * - updated gettext to include strings formed by static and database lookups
 * - corrected sql error when * given as search criteria
 *
 * revision 1.5 2004-07-25 08:13 braverock
 * - remove lang file require_once, as it is no longer used
 *
 * Revision 1.4  2004/07/08 19:38:38  neildogg
 * No need to add quotes or %% to ID search
 *
 * Revision 1.3  2004/07/07 21:20:21  neildogg
 * - Added first/last name search\n- Implemented search by ID
 *
 * Revision 1.2  2004/07/05 22:13:27  introspectshun
 * - Include adodb-params.php
 *
 * Revision 1.1  2004/07/01 19:48:10  braverock
 * - add new configurable relationships code
 *   - adapted from patches submitted by Neil Roberts
 */
?>
