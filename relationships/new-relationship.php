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
GetGlobalVar($msg, 'msg');
GetGlobalVar($relationship_entities,'relationship_entities');
GetGlobalVar($relationship_entity, 'relationship_entity');

//retrieve relationship array provided by one.php pages (urlencoded and serialized by relationships/sidebar.php)
$relationship_entities_array=unserialize(urldecode($relationship_entities));

if ($relationship_entity) {
    //Split relationship entity into table and ID (comma separated to start with)
    $relationship_entity_array=explode(",",$relationship_entity);
    $relationship_entity_table=array_shift($relationship_entity_array);
    $relationship_entity_id = array_shift($relationship_entity_array);
} else {
    //otherwise default to the first relationship type available
    $relationship_entity_table=key($relationship_entities_array);
    $relationship_entity_id=current($relationship_entities_array);
}

$con = get_xrms_dbconnection();

$singular_table=make_singular($relationship_entity_table);

//Loop on entities for which a relationship could be created on
foreach ($relationship_entities_array as $rel_table=>$rel_id) {
    $rel_singular_table = make_singular($rel_table);
    $name_to_get = $con->Concat(implode(", ' ' , ", table_name($rel_table)));
    $sql = "SELECT $name_to_get AS name
            FROM $rel_table
            WHERE $rel_singular_table" . "_id = $rel_id";
    $rel_rst=$con->execute($sql);
    
    //retrieve name of entity
    $entity_name=$rel_rst->fields['name'];
    
    //check to see if currently created entry was selected by the user
    if ($rel_id==$relationship_entity_id AND $rel_table==$relationship_entity_table) $rel_selected=' SELECTED';
    else $rel_selected='';
    
    //add option for the entity, table,id as value
    $entity_options.="\n<option VALUE=\"$rel_table,$rel_id\" $rel_selected>".ucfirst($rel_singular_table).": $entity_name\n";    
}
if (count($relationship_entities_array)>1) {
    $entity_select = "<SELECT id='relationship_entity' name='relationship_entity' onchange=\"javascript:restrictByEntity();\">";
    $entity_select.=$entity_options."</SELECT>";
} else {
    //use hidden variable if only one entity is available
    $entity_select = "<input type=hidden name=relationship_entity value=\"$rel_type,$rel_id\">" . ucfirst($rel_singular_table).": $entity_name\n";
}

$directions=array('from','to');
$optionsarray=array();
//loop over the possible directions between relationships
foreach ($directions as $direction) {
    //search for this table in relationships in this direction
    $sql="SELECT *
    FROM relationship_types
    WHERE {$direction}_what_table = " . $con->qstr($relationship_entity_table)
  . " and  relationship_status='a' ORDER BY {$direction}_what_table";
    $rst = $con->execute($sql);
    if ($direction=='from') { $opposite='to'; } else { $opposite='from'; }
    if (!$rst) { db_error_handler($con, $sql); }
    if ($rst->numRows()>0) {
        while (!$rst->EOF) {
            $opposite_table=$rst->fields[$opposite.'_what_table'];
            //make array keyed on text (should be unique), and set to relationship_type,direction
            //keep in array by table this relationship connects to, for sorting purposes
            $optionsarray[$opposite_table][strtolower($rst->fields[$direction.'_what_text']).' '.                    make_singular($rst->fields[$opposite.'_what_table'])]=$rst->fields['relationship_type_id'].','.$direction;
            $rst->movenext();
        }
    }
    
}        
        
    //break down and append all relationship type options, sort by table then type then direction
    $full_optionsarray=array();
    foreach ($optionsarray as $otable=>$optionset) {
        //sort and append each table to the full set of options
        asort($optionset);
        $full_optionsarray=array_merge($full_optionsarray,$optionset);
    }
    
    reset($full_optionsarray);

$options = '';

//options should be look like: <option value="2,from">Retains consultant contact</option>
foreach ($full_optionsarray as $text => $type) {
    $options .= "
                    <option value=\"$type\">$text</option>";
}

$display_name = ucfirst($singular_table);

$page_title = _("Add Relationship for ") . $display_name;

start_page($page_title, true, $msg);
?>
<script language=JavaScript>
<!--
function restrictByEntity() {
    entity=document.getElementById("relationship_entity");
    location.href = 'new-relationship.php?relationship_entities=<?php echo urlencode($relationship_entities); ?>&relationship_entity=' + entity.value + '&return_url=<?php echo $return_url; ?>';
}
//-->
</script>
<div id="Main">
    <div id="Content">
        <form action=new-relationship-2.php method=post>
        <input type="hidden" name="on_what_id" value="<?php echo $on_what_id; ?>">
        <input type="hidden" name="on_what_table" value="<?php echo $on_what_table; ?>">
        <input type="hidden" name="return_url" value="<?php echo $return_url ?>">
        <input type="hidden" name="relationship_entities" value="<?php echo $relationship_entities; ?>">
        <table class=widget cellspacing=1>
            <tr>
                 <td class=widget_content_form_element><?php echo $entity_select; ?>
                    <select name=relationship_type_direction><?php echo $options; ?>

                    </select>
                    <input type=text size=8 name=search_on>
                </td></tr>
            <tr><td class=widget_content_form_element><input type=submit class=button name=relSearch value="<?php echo _("Search"); ?>"></td></tr>
        </table>
        </form>
<?php
/*
 * $Log: new-relationship.php,v $
 * Revision 1.18  2006/07/21 16:25:56  jnhayart
 * modify query for don't show delete relation
 *
 * Revision 1.17  2006/01/02 23:31:01  vanmer
 * - changed to use centralized dbconnection function
 *
 * Revision 1.16  2005/05/09 01:33:50  vanmer
 * - added relationship entities as hidden variable in order to pass back in case of error
 *
 * Revision 1.15  2005/01/12 18:16:59  vanmer
 * - updated to allow multiple intial entities to be created, all from single page
 * - reloads to restrict list of possible relationship_types upon initital entity selection
 *
 * Revision 1.14  2005/01/11 17:19:33  neildogg
 * - Fairly large update to make it work as it should
 *
 * Revision 1.13  2005/01/10 22:59:27  neildogg
 * - Fixed bugs in code relating to dropdown
 *
 * Revision 1.12  2005/01/10 22:17:29  neildogg
 * - Adding a relationship now works without a relationship name
 *
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