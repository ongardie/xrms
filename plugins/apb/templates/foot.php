<?

//####################################################################
// Active PHP Bookmarks - lbstone.com/apb/
//
// Filename:    foot.php
// Authors:     L. Brandon Stone (lbstone.com)
//
// 2003-03-11   Added security check. [LBS]
//
//####################################################################

//////////////////////////////////////////////////////////////////////
// Security check.
//////////////////////////////////////////////////////////////////////

if ($HTTP_COOKIE_VARS["APB_SETTINGS"]["template_path"] ||
    $HTTP_POST_VARS["APB_SETTINGS"]["template_path"] ||
    $HTTP_GET_VARS["APB_SETTINGS"]["template_path"])
{ exit(); }

//////////////////////////////////////////////////////////////////////
// There should be no need to alter this file.  If you want to change
// the look and feel of APB, you should change the foot_design.php
// file.
//////////////////////////////////////////////////////////////////////

if ($edit_mode) {  echo "<h2 class='warning'>Edit Mode</h2>"; }

?>

<p>
<table cellpadding="5" align="center" cellspacing="0" border="0">
<tr>

  <!-- HOME -->
  <td align="center">
    <a href="<?php echo $APB_SETTINGS['apb_url'] ?>"><img src="images/tb_home.gif" border="0"><br><font size='1'>Bookmarks Home</font></a>
  </td>

  <!-- EDIT MODE -->
  <?php if ($APB_SETTINGS['auth_user_id'] && $APB_SETTINGS['allow_edit_mode']) { ?>
  <td align="center">
    <?
        if ($APB_SETTINGS['edit_mode']) {
            print "<a href='".$SCRIPT_NAME."?";
            if ($date) { echo "date=".$date."&"; }
            if ($id) { echo "id=".$id."&"; }
            if ($action) { echo "action=".$action."&"; }
            if ($keywords) { echo "keywords=".$keywords; }
            print "'><img src='images/tb_edit.gif' border='0'><br><font size='1'>Exit Edit Mode</font></a>";

        } else {
            ?>
            <a href="<?php echo $SCRIPT_NAME ?>?edit_mode=1<?php if ($QUERY_STRING) { echo "&".$QUERY_STRING; } ?>"><img src="images/tb_edit.gif" border="0"><br><font size='1'>Enter Edit Mode</font></a>
            <?
        }
    ?>
  </td>
  <?php } ?>

  <!-- ADD BOOKMARK -->
  <?php if ($APB_SETTINGS['auth_user_id']) { ?>
  <td align="center">
    <a href="<?php echo $APB_SETTINGS['apb_url'] ?>add_bookmark.php"><img src="images/tb_new.gif" border="0"><br><font size='1'>Add Bookmark</font></font></a>
  </td>
  <?php } ?>

  <!-- ADD GROUP -->
<!--
  <?php if ($APB_SETTINGS['auth_user_id']) { ?>
  <td align="center">
    <a href="<?php echo $APB_SETTINGS['apb_url'] ?>add_group.php"><img src="images/tb_open.gif" border="0"><br><font size='1'>Add Group</font></font></a>
  </td>
  <?php } ?>
-->

  <!-- SETUP -->
  <?php if ($APB_SETTINGS['auth_user_id']) { ?>
  <td align="center">
    <a href="tools.php"><img src="images/tb_preferences.gif" border="0"><br><font size='1'>Tools</font></a>
  </td>
  <?php } ?>

</tr>
</table>

</center>

<?

// If you want to create your own design for APB, change the foot_design.php file.
include($APB_SETTINGS['template_path'] . "foot_design.php");

?>
