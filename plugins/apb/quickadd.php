<?
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
apb_head();

?>

<h2>Setup QuickAdd</h2>

<?php if ($APB_SETTINGS['auth_user_id']) { ?>

<p><table cellpadding="0" cellspacing="0" width="70%" align="center" border="0">
<tr>
  <td>

  <p>Drag the following link to your browser's toolbar, or add this link to your browser's "favorites".
  You can then use this link to <b>automatically add any site that you're viewing</b> to APB.

  <p align="center"><a href="javascript:document.location = '<?php echo $APB_SETTINGS['apb_url'] ?>add_bookmark.php?form_title=' + escape(document.title) + '&form_url=' + escape(document.location)" onClick="javascript:alert('You must drag this link to your browser\'s toolbar or add it to your favorites.'); return false">Add to Bookmarks</a>

  </td>
</tr>
</table>

<?php } else { ?>

<p>You must be logged into access this feature of APB.

<?

}

apb_foot();

?>