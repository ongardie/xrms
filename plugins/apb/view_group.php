<?

//####################################################################
// Active PHP Bookmarks - lbstone.com/apb/
//
// Filename: view_group.php
// Authors:  L. Brandon Stone (lbstone.com)
//           Nathanial P. Hendler (retards.org)
//
// 2001-10-29 14:14     Starting on version 1.0 (NPH)
//####################################################################

//####################################################################
// Initialization.
//####################################################################

include_once('apb.php');
apb_head();

$APB_SETTINGS['allow_edit_mode'] = '1';
$APB_SETTINGS['allow_search_box'] = 1;

$id = $_GET['id'];
$id || $id = 0;

?>
</center>
<p><table cellpadding='0' cellspacing='0' border='0' width="100%">
<tr><td>
<?

$g = apb_group($id);
$g->print_group_path();
print "<p>\n";


if ($g->number_of_bookmarks() >= 3) {
    $v = new TopInGroupView();
    $v->group_id = $g->id();
//    $v->since_n_interval = "14";
    $v->template = 'topingroup';
    $v->output();
}

if ($g->number_of_child_groups() > 0) {
    print "<b>Groups</b><p>\n";
    $g->print_group_children();
    print "<p>\n";
}

if ($g->number_of_bookmarks() > 0) {
    print "<b>Site Listings</b><p>\n";
    $g->print_group_bookmarks();
}


?>
</td></tr>
</table>

<center>

<?
apb_foot();
?>
