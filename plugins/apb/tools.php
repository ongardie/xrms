<?
include_once('apb.php');
apb_head();
?>

<h2>Tools</h2>

<? if ($APB_SETTINGS['auth_user_id']) { ?>

<p><table cellpadding="0" cellspacing="0" width="70%">
<tr>
  <td>

  <h3>APB Setup</h3>

  <ul>
    <li><a href="quickadd.php">Setup QuickAdd</a> - Create a button in your browser that
    will automatically add bookmarks to APB.
  </ul>

  <h3>Additional APB Features</h3>

  <ul>
    <li><a href="daily_browser.php">Daily Browser</a> - See what kind of activity your APB is
    getting.
  </ul>

  </td>
</tr>
</table>

<? } else { ?>

<p>You must be logged into access this feature of APB.

<?

}

apb_foot();

?>
