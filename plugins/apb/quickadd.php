<?php
//####################################################################
// Active PHP Bookmarks - lbstone.com/apb/
//
// Filename: bookmarklet.php
// Author:   L. Brandon Stone (lbstone.com)
//           Nathanial P. Hendler (retards.org)
//
// 2001-09-05 03:00     This is pretty much the original file from
//                      the older version of apb.
//
//####################################################################

include_once('apb.php');

echo "<h2>" . _("Setup QuickAdd") . "</h2>";

if ($APB_SETTINGS['auth_user_id']) {

echo "<p>

<table cellpadding=\"0\" cellspacing=\"0\" width=\"70%\" align=\"center\" border=\"0\">
<tr>
  <td>

  <p>" . _("Drag the following link to your browser's toolbar, or add this link to your browser's favorites. You can then use this link to automatically add any site that you're viewing to APB.") 

    . "  <p align=\"center\"><a href=\"javascript:document.location = '" . $APB_SETTINGS['apb_url'] . "add_bookmark.php?form_title=' + escape(document.title) + '&form_url=' + escape(document.location)\" onClick=\"javascript:alert('" . _("You must drag this link to your browser\'s toolbar or add it to your favorites.") . "'); return false\">" . _("Add to Bookmarks") . "</a>

  </td>
</tr>
</table>";

} else {
    echo "<p>" . _("You must be logged into access this feature.");
}

apb_foot();

?>
