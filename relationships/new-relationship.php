<?php
/**
 * new_relationship.php
 *
 * First page in sequence to create a new relationship between two entities
 *
 * @author Aaron van Meerten
 */


require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();

GetGlobalVar($return_url,'return_url');
GetGlobalVar($relationships,'relationships');
parse_str($relationships);
GetGlobalVar($msg, 'msg');

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

$sql = "SELECT rt2.from_what_table, rt2.to_what_table
        FROM relationship_types AS rt, relationship_types AS rt2
        WHERE rt.relationship_type_id = $relationship_type_id
        AND rt.relationship_name = rt2.relationship_name
        AND rt2.relationship_status = 'a'";
$rst = $con->execute($sql);

if($working_direction == "from") {
    $opposite_direction = "to";
}
else {
    $opposite_direction = "from";
}

if($working_direction == "both") {
    $working_table = $rst->fields['from_what_table'];
    $what_table = false;
}
else {
    $working_table = $rst->fields[$working_direction . '_what_table'];
    $what_table = $rst->fields[$opposite_direction . '_what_table'];
}
$display_name = ucfirst(make_singular($working_table));

if($working_table == "companies" and $what_table == "contacts") {
    $sql = "(SELECT 'Enter other contact' AS name,
            0 AS contact_id)
            UNION
            (SELECT " .
            $con->Concat("first_names", "' '", "last_name") . " AS name,
            contact_id
            FROM contacts
            WHERE company_id=" . $on_what_id . "
            AND contact_record_status='a'
            ORDER BY last_name)";

        //Create query to get the name of the entity, either contact first/last name or _name
        $name_order = implode(', ', array_reverse(table_name($on_what_table)));
        $name_concat = $con->Concat(implode(', \' \', ', table_name($on_what_table)));    
        $namesql = "SELECT $name_concat as name FROM $on_what_table WHERE $singular_table" . "_id=$on_what_id";

    $namerst = $con->execute($namesql);
    if (!$namerst) { db_error_handler($con, $namesql); }
    elseif ($namerst->numRows()>0) { 
        $entityName = $namerst->fields["name"];
    } else { $entityName = $singular_table; }

$directions=array('from','to');
$optionsarray=array();
//loop over the possible directions between relationships
foreach ($directions as $direction) {
    //search for this table in relationships in this direction
    $sql="SELECT *
    FROM relationship_types
    WHERE $direction"."_what_table = " . $con->qstr($on_what_table)
    ." AND relationship_status='a'";
    
    $rst = $con->execute($sql);
    if ($direction=='from') { $opposite='to'; } else { $opposite='from'; }
    if (!$rst) { db_error_handler($con, $sql); }
    if ($rst->numRows()>0) {
        while (!$rst->EOF) {
            //make array keyed on text (should be unique), and set to relationship_type,direction
            $optionsarray[strtolower($rst->fields[$direction.'_what_text']).' '.                    make_singular($rst->fields[$opposite.'_what_table'])]=$rst->fields['relationship_type_id'].','.$direction;
            $rst->movenext();
        }
    }
    
}
        $options='';        

        ksort($optionsarray);
        reset($optionsarray);
        
        //options should be look like: <option value="2,from">Retains consultant contact</option>
        foreach ($optionsarray as $text=>$type) {
            $options.='<option value="'. $type. '">'.$text."</option>";
        }

$display_name = ucfirst($singular_table);

$page_title = _("Add Relationship for ") . $display_name;

start_page($page_title, true, $msg);
?>
<div id="Main">
    <div id="Content">

        <form action=new-relationship-2.php method=post>
        <input type="hidden" name="relationship_type_id" value="<?php echo $relationship_type_id; ?>">
        <input type="hidden" name="on_what_id" value="<?php echo $on_what_id; ?>">
        <input type="hidden" name="on_what_table" value="<?php echo $on_what_table; ?>">
        <input type="hidden" name="return_url" value="<?php echo $return_url ?>">
        <table class=widget cellspacing=1>
            <tr><td class=widget_content_form_element><?php echo $display_name . ': ' .$entityName; ?> <select name=relationship_type_direction><?php echo $options; ?></select>
                <input type=text size=8 name=search_on>
            </td></tr>
            <tr><td class=widget_content_form_element><input type=submit class=button name=relSearch value="<?php echo _("Search"); ?>"></td></tr>
        </table>
        </form>
<?php
/*
 * $Log: new-relationship.php,v $
 * Revision 1.11  2005/01/10 20:40:02  neildogg
 * - Supports parameter passing after the sidebar update
 *
 * Revision 1.10  2004/12/30 20:29:20  vanmer
 * - added restriction to only show active relationship types
 *
 * Revision 1.9  2004/12/09 17:42:18  vanmer
 * - fixed unclosed tag to allow options to display properly in internet explorer.
 *
 * Revision 1.8  2004/12/01 18:33:41  vanmer
 * - New revision of the new-relationship.php page to more generally create relationships between entities
 * - Starts the creation process by selecting the relationship_type and direction
 * - adds search terms to allow restriction of the list of the other side of the relationship
 *
 *
 */
?>