<?php

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

$page_title = _("Bookmarks");

$id = $_GET['id'];
$id || $id = 0;
$g = apb_group($id);

$page_title .= $g->print_group_path();
start_page($page_title, true, $msg);

$APB_SETTINGS['allow_edit_mode'] = '1';

$id = $_GET['id'];
$id || $id = 0;

?>

<div id="Main">
    <div id="Content">
        <table class=widget>
            <tr>
                <td class=widget_header>Search</td>
            </tr>
            <tr>
                <td class=widget_content>
                <form method='get' action='search.php'>
                <input name='keywords' value='' size='25'>
                <br />
                <input type='submit' name='submit' value='<?php echo _("Search"); ?>'>
                </form>
                </td>
            </tr>

<?php
/*
if ($g->number_of_bookmarks() >= 3) {
    $v = new TopInGroupView();
    $v->group_id = $g->id();
    $v->template = 'topingroup';
    $v->output();
}
*/

if ($g->number_of_child_groups() > 0) {
    echo "
            <tr>
                <td class=widget_header>
                    " .  _("Groups") . "
                </td>
            </tr>
            <tr>
                <td class=widget_content>
";
    $g->print_group_children();
    echo "
                </td>
            </tr>
    ";
}

?>

<?php

if ($g->number_of_bookmarks() > 0) {
    echo " 
            <tr>
                <td class=widget_header> 
                    " .  _("Sites") . "
                </td>
            </tr>
            <tr>
                <td class=widget_content>
";
    $g->print_group_bookmarks();
    echo "
                </td>
            </tr>
    ";
}

?>

                </td>
            </tr>
        </table>
    </div>
    <div id=Sidebar>
    </div>
    <?php apb_foot(); ?>
</div>

<?php
end_page();
?>
