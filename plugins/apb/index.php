<?php

//####################################################################
// Active PHP Bookmarks - lbstone.com/apb/
//
// Filename: index.php
// Authors:  L. Brandon Stone (lbstone.com)
//           Nathanial P. Hendler (retards.org)
//           Glenn Powers (glenn@net127.com)
//
//####################################################################

include_once('apb.php');
include_once('options_box.php');

$page_title = _("Bookmarks");
start_page($page_title, true, $msg);

$APB_SETTINGS['allow_edit_mode'] = 1;
$APB_SETTINGS['allow_search_box'] = 1;

$number_of_bookmarks = get_number_of_bookmarks();

if ($number_of_bookmarks == 0)
{
    // If edit mode is on, let's turn it off. (This can happen if a person deletes
    // the last bookmark. [LBS 20020211]
    $edit_mode = 0;
}

if ($number_of_bookmarks == 0) {

    // No need for edit mode when there's nothing to edit. [LBS 20020211]
    $APB_SETTINGS['allow_edit_mode'] = 0;

    ?>

    <p>
    <table cellpadding="0" cellspacing="0" border="0" width="70%">
    <tr>
      <td>
      <p><?php echo _("There are no bookmarks."); ?></p>
      </td>
    </tr>
    </table>

    <?php

    $APB_SETTINGS['allow_search_box'] = 0;
} else {


$directory_view = directory_view();
// $top_groups = top_groups(5,5,14);

echo "

<div id=Main>
  <div id=Content>
    <table class=widget>

            <tr>
                <td class=widget_header>Search</td>
            </tr>
            <tr>
                <td class=widget_content>
                <form method=get action=\"search.php\">
                <input name=\"keywords\" value=\"\" size=\"25\">
                <input type=\"submit\" name=\"submit\" value=\"" . _("Search") . "\">
                </form>
                </td>
            </tr>

    </table>
    <table class=widget>
        <tr>
            <td class=widget_header>" .   _("Groups") . "</td>
        </tr>
        <tr>
            <td class=widget_content>
                " .  $directory_view . "
            </td>
        </tr>
    </table>
  </div>

";

}

?>

  <!-- right column //-->
    <div id="Sidebar">

<?php echo $options_box; ?>

<table class=widget cellspacing=1 width="100%">
<tr>
  <td class=widget_header>
    <?php echo _("QuickAdd"); ?>
  </td>
</tr>

<tr>
  <td class=widget_content>

    <?php echo _("Drag the following link to your browser's toolbar, or add this link to your browser's favorites. You can then use this link to automatically add any site that you're viewing to these bookmarks."); ?> 

    <?php echo "<p align=\"center\"><a href=\"javascript:document.location = '" . $APB_SETTINGS['apb_url'] . "add_bookmark.php?form_title=' + escape(document.title) + '&form_url=' + escape(document.location)\" onClick=\"javascript:alert('" . _("You must drag this link to your browser\'s toolbar or add it to your favorites.") . "'); return false\">" . _("Add to Bookmarks") . "</a>"; ?>

  </td>
</tr>

</table>

  </div>
</div>

<?php end_page(); ?>
