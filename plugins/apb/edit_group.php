<?
//####################################################################
// Active PHP Bookmarks - lbstone.com/apb/
//
// Filename: edit_group.php
// Author:   L. Brandon Stone (lbstone.com)
//           Nathanial P. Hendler (retards.org)
//
// 2002-01-29 22:01     Edit group created.  This code was snagged
//                      from the edit (add) bookmark code, so it may
//                      be weird in places. [LBS]
//
//####################################################################

include_once('apb.php');
apb_head();

//echo "[".$APB_SETTINGS['auth_user_id']."]";

if ($APB_SETTINGS['auth_user_id']) {

    // We have an authenticated user.

    print "<h2>Edit Group</h2>\n";

    // We're actually editing the group.
    if ($action == "edit_group" && $id && $form_title)
    {
        $query = "
            UPDATE apb_groups
               SET group_parent_id = $form_group_parent_id,
                   group_title = '$form_title'
             WHERE group_id = $id
               AND user_id = ".$APB_SETTINGS['auth_user_id']."
             LIMIT 1
        ";
//        echo "<p><pre>$query</pre><p>\n";
        $result = mysql_db_query($APB_SETTINGS['apb_database'], $query);
        if ($result)
        {
            $g = apb_group($id);

            ?>

            <p>Group updated!

            <p><?= $g->print_group_path() ?>

            <p><a href='<?= $back_url ?>'>Go Back to Editing</a>

            <?
        }
        else
        {
            error("Group edit failed!!");
        }
    }
    // We're not doing the SQL editing (updating) yet, so display the form page.
    else
    {
//        Turned this off for the time being [LBS]
//        set_magic_quotes_runtime(0);

        if ($id) {

            $g = apb_group($id);
            $form_group_id = $g->id();
            $form_title = $g->title();
            $form_description = $g->description();
            $form_private = $g->private();
            $id_owner_user_id = $g->user_id();
            $parent_id = $g->parent_id();
        }

        // Make sure we're authenticated.
        if ($id_owner_user_id == $APB_SETTINGS['auth_user_id']) {

            ?>

            <p><?= $g->print_group_path() ?>

            <p>
            <form action="<?= $SCRIPT_NAME ?>?action=edit_group" method="post">
            <input type='hidden' name='back_url' value='<?= $HTTP_REFERER ?>'>
            <?php if ($id) { print "<input type='hidden' name='id' value='$id'>\n"; } ?>
            <table>
            <tr>
            <td>
                <table width='100%'>
                    <tr>
                        <td>Parent Group:</td>
                        <td><?php groups_dropdown('form_group_parent_id', $parent_id, '[top level]', $form_group_id) ?></td>
                    </tr>
                    <tr>
                        <td>Title:</td>
                        <td><input size="40" name="form_title" value="<?php echo stripslashes($form_title) ?>"></td>
                    </tr>
<!-- Future Feature(s)
                    <tr>
                        <td>Description:</td>
                        <td><input size="40" name="form_description" value="<?php echo stripslashes($form_description) ?>"></td>
                    </tr>
                    <tr>
                        <td>Private:</td>
                        <td>
                            No <input type='radio' name='form_private' value='0' <?=
                                (($form_private == '1') ? '' : ' CHECKED' ) ?> >
                            Yes <input type='radio' name='form_private' value='1' <?=
                                (($form_private == '1') ? ' CHECKED' : '' ) ?> >
                        </td>
                    </tr>
-->
                </table>
            </td>
            </tr>
            </table>

            <p><center><input type="submit" value="Edit Group"></center>
            </form>
            <?
        } else {
            error("You don't have permission to edit this group");
        }

    }

} else {

    print "<b>You must be logged in to edit groups</b><p>\n\n";

}

apb_foot();

?>
