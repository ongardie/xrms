<p>
<table <?php if ($group_width) { echo "width='$group_width'"; } ?> bgcolor="<?php echo $group_border_color ?>" cellpadding="0" cellspacing="0" align="center" border="0">
  <tr>
    <td>

    <table width="100%" cellpadding="1" cellspacing="1" border="0">
      <tr>
        <td bgcolor="<?php echo $group_bgcolor ?>" >

        <table width="100%" cellpadding="0" cellspacing="0" bgcolor="<?php echo $group_color ?>" border="0">
          <tr>
            <td align="center">

            <table width="100%" cellpadding="3" cellspacing="0" border="0">
              <tr>
                <td>
                <font face="Arial, Helvetica, sans-serif" size="2" color="<?php echo $group_title_color ?>">
                <b><?php echo $group_name ?></b>
                </td><td align="right">
                <?php if ($group_edit_icon) { ?>
                <a href="<?php echo $group_edit_url ?>"><img
                src="<?php echo $dir_images ?>icon_edit.gif" width="13" height="13" border="0"
                alt="Edit this Group"></a>
                <?php } ?>
                <?php if ($group_expand_icon) { ?>
                <a href="<?php echo $group_expand_url ?>"><img
                src="<?php echo $dir_images ?>icon_expand.gif" width="13" height="13" border="0"
                alt="Expand this Group"></a>
                <?php } ?>
                </td>
              </tr>
            </table>

            </td>
          </tr>
        </table>

        <table width="100%" cellpadding="3" cellspacing="0">
          <tr>
            <td>
            <?php echo $group_list ?></td>
          </tr>
        </table>

        </td>
      </tr>
    </table>

    </td>
  </tr>
</table>
</p>
