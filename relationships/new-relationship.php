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

GetGlobalVar($on_what_id,'on_what_id');

//GetGlobalVar($working_direction = $_POST['working_direction'];
GetGlobalVar($return_url,'return_url');
GetGlobalVar($on_what_table,'on_what_table');
GetGlobalVar($msg, 'msg');

//$relationship_name = $_POST['relationship_name'];
$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

$singular_table=make_singular($on_what_table);

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
    WHERE $direction"."_what_table = " . $con->qstr($on_what_table);
    
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