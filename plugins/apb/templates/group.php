<p>
<table <? if ($group_width) { echo "width='$group_width'"; } ?> bgcolor="<? echo $group_border_color ?>" cellpadding="0" cellspacing="0" align="center" border="0">
  <tr>
    <td>

    <table width="100%" cellpadding="1" cellspacing="1" border="0">
      <tr>
        <td bgcolor="<? echo $group_bgcolor ?>" >

        <table width="100%" cellpadding="0" cellspacing="0" bgcolor="<? echo $group_color ?>" border="0">
          <tr>
            <td align="center">

            <table width="100%" cellpadding="3" cellspacing="0" border="0">
              <tr>
                <td>
                <font face="Arial, Helvetica, sans-serif" size="2" color="<? echo $group_title_color ?>">
                <b><? echo $group_name ?></b>
                </td><td align="right">
                <? if ($group_edit_icon) { ?>
                <a href="<? echo $group_edit_url ?>"><img
                src="<? echo $dir_images ?>icon_edit.gif" width="13" height="13" border="0"
                alt="Edit this Group"></a>
                <? } ?>
                <? if ($group_expand_icon) { ?>
                <a href="<? echo $group_expand_url ?>"><img
                src="<? echo $dir_images ?>icon_expand.gif" width="13" height="13" border="0"
                alt="Expand this Group"></a>
                <? } ?>
                </td>
              </tr>
            </table>

            </td>
          </tr>
        </table>

        <table width="100%" cellpadding="3" cellspacing="0">
          <tr>
            <td>
            <? echo $group_list ?></td>
          </tr>
        </table>

        </td>
      </tr>
    </table>

    </td>
  </tr>
</table>
</p>
