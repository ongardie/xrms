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

      <p><b>This message will disappear once you've added your first bookmark.</b>

      <p>There are no bookmarks in APB right now.  You can
      <a href='quickadd.php'>create a button</a> in your browser that will aid
      in adding bookmarks to APB.  We highly recommend that you do this.
      If you don't do this now, you can always do it later in the
      '<a href='tools.php'>Setup</a>' section.

      <?php if (!$APB_SETTINGS['auth_user_id']) { echo "<p><font color='red'>By the way, you can't really do anything until you're logged in.</font>"; } ?>

      </td>
    </tr>
    </table>

    <?php

    $APB_SETTINGS['allow_search_box'] = 0;
} else {


$directory_view = directory_view();
// $top_groups = top_groups(5,5,14);

?>

<div id=Main>
  <div id=Content>
    <table class=widget>
        <tr>
            <td class=widget_header><?php echo  _("Groups"); ?></td>
        </tr>
        <tr>
            <td class=widget_content>
                <?php echo $directory_view; ?>
            </td>
        </tr>
    </table>
  </div>
  <div id=Sidebar>
    <?php echo $top_groups; ?>
  </div>

<?php

apb_foot();

echo "</div>";

}

end_page();

?>
