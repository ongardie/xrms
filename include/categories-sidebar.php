<?php
/**
 * Sidebar box for Categories
 *
 * $Id: categories-sidebar.php,v 1.3 2005/08/28 16:39:37 braverock Exp $
 */

 if ( !defined('IN_XRMS') )
{
  die(_('Hacking attempt'));
  exit;
}


$category_rows = "<div id='category_sidebar'>
        <table class=widget cellspacing=1 width=\"100%\">
            <tr>
                <td class=widget_header>" . _("Categories") . "</td>
            </tr>\n";

//build the categories sql query
$categories_sql = "select category_pretty_name
from categories c, category_scopes cs, category_category_scope_map ccsm, entity_category_map ecm
where ecm.on_what_table = '$on_what_table'
and ecm.on_what_id = '$on_what_id'
and ecm.category_id = c.category_id
and cs.category_scope_id = ccsm.category_scope_id
and c.category_id = ccsm.category_id
and cs.on_what_table = '$on_what_table'
and category_record_status = 'a'
order by category_pretty_name";

//uncomment the debug line to see what's going on with the query
//$con->debug=1;

//execute our query
$rst = $con->execute($categories_sql);
$categories = array();

if ($rst) {
    while (!$rst->EOF) {
        array_push($categories, $rst->fields['category_pretty_name']);
        $rst->movenext();
    }
    $rst->close();
} else {
    db_error_handler($con, $categories_sql);
}

if (!empty($categories)) {
    $categories = implode(', ', $categories);
} else {
    $categories = _("No categories");
}

$category_rows .= "
            <tr>
                <td class=widget_content>" . $categories . "</td>
            </tr>
            <tr>
                <td class=widget_content_form_element><input type=button class=button onclick=\"javascript: location.href='categories.php?" . make_singular($on_what_table) . "_id=" . $on_what_id . "';\" value=\"". _("Manage") . "\"></td>
            </tr>
        </table>\n</div>";

/**
 * $Log: categories-sidebar.php,v $
 * Revision 1.3  2005/08/28 16:39:37  braverock
 * - remove unnecessary colspan
 *
 * Revision 1.2  2005/01/11 13:03:32  braverock
 * - removed on_what_string hack, changed to use standard make_singular function
 *
 * Revision 1.1  2004/10/22 20:44:29  introspectshun
 * - Centralize category handling as sidebar
 *
 */
?>