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
$relationship_name = $_POST['relationship_name'];
$working_direction = $_POST['working_direction'];
$on_what_id = $_POST['on_what_id'];
$return_url = $_POST['return_url'];
$search_on = ($_POST['possible_id']) ? $_POST['possible_id'] : $_POST['search_on'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

$sql = "SELECT from_what_table, to_what_table
        FROM relationship_types
        WHERE relationship_name='$relationship_name'";
$rst = $con->execute($sql);

if($working_direction == "from") {
    $opposite_direction = "to";
}
else {
    $opposite_direction = "from";
}

$what_table = $rst->fields[$opposite_direction . '_what_table'];
$what_table_singular = make_singular($what_table);

$rst->close();

$display_name = ucfirst($what_table);
$display_name_singular = ucfirst($what_table_singular);

if($working_direction == "both") {
    $sql = "SELECT from_what_text, relationship_type_id
            FROM relationship_types
            WHERE relationship_name='" . $relationship_name . "'
            AND relationship_status='a'
            UNION
            SELECT to_what_text, relationship_type_id
            FROM relationship_types
            WHERE relationship_name='" . $relationship_name . "'
            AND relationship_status='a'";
}
else {
    $sql = "SELECT " . $working_direction . "_what_text, relationship_type_id
            FROM relationship_types
            WHERE relationship_name='" . $relationship_name . "'
            AND relationship_status='a'";
}
$rst = $con->execute($sql);
if ($rst) {
    $relationship_menu = $rst->getmenu2('relationship_type_id', '', false);
    $rst->close();
} else {
    db_error_handler ($con, $sql);
}

$page_title = _("Add ". $display_name_singular);
start_page($page_title, true, $msg);
?>

<div id="Main">
    <div id="Content">

        <form action="<?php echo $http_site_root . "/" . $what_table . "/one.php"; ?>" method=get target="_blank">
        <input type="hidden" name="<?php echo $what_table_singular; ?>_id">
        </form>
        <form action=new-relationship-3.php method=post <?php if($working_direction == "both") { ?>onsubmit="document.forms[1].working_direction.value = (document.forms[1].relationship_type_id.selectedIndex < (document.forms[1].relationship_type_id.length / 2)) ? 'from' : 'to'; return true;"<?php } ?>>
        <input type="hidden" name="relationship_name" value="<?php echo $relationship_name; ?>">
        <input type="hidden" name="working_direction" value="<?php echo $working_direction; ?>">
        <input type="hidden" name="real_working_direction" value="<?php echo $working_direction; ?>">
        <input type="hidden" name="on_what_id" value="<?php echo $on_what_id; ?>">
        <input type="hidden" name="return_url" value="<?php echo $return_url ?>">
        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header><?php echo $display_name; ?></td>
            </tr>
                <td class=widget_content_form_element><?php echo $relationship_menu; ?> &nbsp;
                <?php
if ($search_on == '')
{
    echo _("Specify a search condition");
}
else
{
    if(!eregi("[0-9]", $search_on)) {
        $search_on = $con->qstr("%$search_on%", get_magic_quotes_gpc());
        //If you want to make this work for other tables, you should be able to edit utils-database.php with the proper names
        $name_order = implode(', ', array_reverse(table_name($what_table)));
        $name_concat = $con->Concat(implode(', \' \', ', table_name($what_table)));

        $sql = "select " . $name_concat . " as name, " . $what_table_singular . "_id
                from " . $what_table . "
                where " . $what_table_singular . "_record_status='a'
                group by " . $name_concat . ", " . $what_table_singular . "_id, " . $name_order . "
                having " . $name_concat . " like " . $search_on . "
                order by " . $name_order;
        $rst = $con->execute($sql);
        if ($rst) {
            if($rst->rowcount()) {
                echo $rst->getmenu2('on_what_id2', '', false);
                echo " &nbsp; <input type=button class=button value='"._("More Info")."' "
                    . "onclick='document.forms[0]." . $what_table_singular
                    . "_id.value=document.forms[1].on_what_id2.options[document.forms[1].on_what_id2.selectedIndex].value; document.forms[0].submit();'>";
            }
            else {
                echo _("There is no ". $what_table_singular . " by that name");
            }
        } else {
            db_error_handler ($con, $sql);
        }
    }
    else {
        $name_concat = $con->Concat(implode(', \' \', ', table_name($what_table)));
        $sql = "select " . $what_table_singular . "_id, " . $name_concat . " as name
            from " . $what_table . "
            where " . $what_table_singular . "_id = " . $search_on . "
            and " .$what_table_singular . "_record_status='a'";
        $rst = $con->execute($sql);
        if ($rst) {
            if($rst->rowcount()) {
            echo "<input type=hidden name=on_what_id2 value=$search_on>" . $rst->fields['name'] . "\n";
            }
            else {
                echo _("There is no ". $what_table_singular . " by that ID");
            }
        } else {
            db_error_handler ($con, $sql);
        }
    }
}
?>
               </td>
            </tr>
            <tr>
                <td class=widget_content_form_element colspan=2>
                    <input class=button type=submit value="<?php echo $page_title; ?>">
                    <input class=button name=return type=submit value="<?php echo $page_title; ?> and Return">
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