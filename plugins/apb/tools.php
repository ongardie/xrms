<?php

include_once('apb.php');

echo "<h2>" . _("Tools") . "</h2>";

echo "<table cellpadding=\"0\" cellspacing=\"0\" width=\"70%\">
<tr>
  <td>

  <h3>" . _("Bookmark Setup") . "</h3>

  <ul>
    <li><a href=\"quickadd.php\">" . _("Setup QuickAdd") . "</a> - " . _("Create a button in your browser that will automatically add bookmarks.") . "
  </ul>

  <h3>" . _("Additional Features") . "</h3>

  <ul>
    <li><a href=\"daily_browser.php\">" . _("Daily Browser") . "</a> - " . _("See what kind of activity your Bookarks are getting.") . "
  </ul>

  </td>
</tr>
</table>
";

apb_foot();

?>
