<?
//####################################################################
// Active PHP Bookmarks - lbstone.com/apb/
//
// Filename: add_bookmark.php
// Author:   L. Brandon Stone (lbstone.com)
//           Nathanial P. Hendler (retards.org)
//
// 2001-09-05 03:35     Starting on version 1.0  The structure of
//                      this program is very similar to the original
//                      insert_bookmark section of apb (0.1.0 - 0.6.01)
// 2001-09-05 09:05     Appears to be working.
//
// This is pretty straight forward.  If the user hasn't authenticated
// the can't do anything.  If $action is set to insert_bookmark, we
// need to insert a bookmark, which may involve created a new group.
// If $action isn't set, we display the form for them to add a new
// bookmark, prefilling the fields if we're passed $form_* values.
//
//####################################################################

include_once('apb.php');

$action =  $_GET['action'];
$form_id = $_POST['form_id'];
$form_group_id = $_POST['form_group_id'];
$form_group_title = $_POST['form_group_title'];
$form_group_type = $_POST['form_group_type'];
$form_parent_id = $_POST['form_parent_id'];
$form_title = $_POST['form_title'];
$form_url = $_POST['form_url'];
$form_description = $_POST['form_description'];
$form_private = $_POST['form_private'];


$page_title = _("Bookmarks : Add");
start_page($page_title, true, $msg);

    // If we're going to insert, we need to have a URL. [LBS 20020211]
    if ($action == 'insert_bookmark' && $form_url) {

        // We're inserting a new bookmark.
        if ($form_group_type == 'new' && $form_group_title) {

            // INSERT new group
            $query = "
                INSERT INTO apb_groups
                (group_parent_id, group_title, user_id, group_creation_date)
                VALUES
                ('$form_group_parent_id', '$form_group_title', '".$APB_SETTINGS['auth_user_id']."', NOW())
            ";
            $result = mysql_db_query($APB_SETTINGS['apb_database'], $query);


            // Groups have to be unique accross group_title and
            // user_id, so we just do the previous insert blindly
            // and now query the db for the group_id of the group
            // which we'll use in our bookmark insert.  If the last
            // insert failed, it's because the group already
            // existed for that user_id, which doesn't prohibit us
            // from just going ahead and using the existing group
            // for the bookmark insert.

            $query = "
                SELECT group_id
                  FROM apb_groups
                 WHERE group_title = '$form_group_title'
                   AND user_id = '".$APB_SETTINGS['auth_user_id']."'
            ";

            $result = mysql_db_query($APB_SETTINGS['apb_database'], $query);
            $row = mysql_fetch_assoc($result);
            $form_group_id = $row['group_id'];

        }

        // UPDATE bokmark
        if ($form_id) {
            if (1) {
                $query = "
                    UPDATE apb_bookmarks
                       SET group_id = '$form_group_id',
                           bookmark_title = '$form_title',
                           bookmark_url = '$form_url',
                           bookmark_description = '$form_description',
                           bookmark_private = '$form_private'
                     WHERE bookmark_id = $form_id
                       AND user_id = '".$APB_SETTINGS['auth_user_id']."'
                     LIMIT 1
                ";
                $result = mysql_db_query($APB_SETTINGS['apb_database'], $query);
                if ($result) {

                    $b = apb_bookmark($form_id);
                    $g = apb_group($b->group_id());

                    echo "<p>" . _("Bookmark saved!") . "</p>"
                        . "<p>" . $g->print_group_path() . "</p>"
                        . "<p>" . $b->link() . "</p>"
                        . "<p><a href='" . $back_url . "'>"
                        . _("Go Back to Editing") . "</a>";
                } else {
                    error( _("Bookmark edit failed!!") );
                }
            } else {
                error( _("You don't have permission to edit this bookmark.") );
            }

        // INSERT bookmark
        } else {
            $query = "
                INSERT INTO apb_bookmarks
                    (group_id, bookmark_title, bookmark_url, bookmark_description,
                     bookmark_creation_date, bookmark_private, user_id)
                    VALUES
                    ('$form_group_id', '$form_title', '$form_url', '$form_description',
                     now(), '$form_private', '".$APB_SETTINGS['auth_user_id']."')
            ";

            // print "<p>QUERY<pre>$query</pre><p>\n";
            $result = mysql_db_query($APB_SETTINGS['apb_database'], $query);

            if ($result) {

                $b = apb_bookmark(mysql_insert_id());
                $g = apb_group($b->group_id());

                echo "<p>" . _("Bookmark saved!") . "</p>"
                    . "<p>" . $g->print_group_path() . "</p>"
                    . "<p>" . $b->link() . "</p>";

            } else {
                error( _("Bookmark add failed!!") );
            }
        }

    } elseif ($action == 'delete_bookmark') {
        $b = apb_bookmark($bookmark_id);
        if ($b->user_id() == $APB_SETTINGS['auth_user_id']) {
            $query = "
                UPDATE apb_bookmarks
                   SET bookmark_deleted = '1'
                 WHERE bookmark_id = $bookmark_id
                 LIMIT 1
            ";
            debug($query, 3);
            $result = mysql_db_query($APB_SETTINGS['apb_database'], $query);
            print _("Bookmark Deleted") . "<br>\n";

            ?>

            <p><a href='<?php echo $back_url; ?>'>Go Back to Editing</a>

            <?

        } else {
            print _("Can't delete, you don't have permission.") . "<br>\n";
        }

    } else {

        set_magic_quotes_runtime(0); ?>

        <?

        if ($id) {
            $b = apb_bookmark($id);
            $form_group_id = $b->group_id();
            $form_title = $b->title();
            $form_url = $b->url();
            $form_description = $b->description();
            $form_private = $b->private();
            $id_owner_user_id = $b->user_id();
        }

        if (! $id || $id_owner_user_id == $APB_SETTINGS['auth_user_id']) {

            ?>

<div id=Main>
    <div id=Content>
        <table class=widget>
                    <tr>
                      <td class=widget_header>
                        <?php echo _("Add a Bookmark"); ?>
                      </td>
                    </tr>
            <tr>
            <td class=widget_content>

                <?

                // If there are already groups, show the advanced group editor. [LBS 20020211]
                if (get_number_of_groups() > 0)
                {
                    ?>

                    <table cellpadding="10" border="1" cellspacing="0" width='100%'>
                    <tr>
                      <td class=wdiget_content>
                        <table cellpadding="5" cellspacing="0" border="0" width="100%">
                        <tr>

            <form action="<?php echo $SCRIPT_NAME; ?>?action=insert_bookmark" method="post">
            <input type='hidden' name='back_url' value='<?php echo $HTTP_REFERER; ?>'>
            <?php if ($id) { print "<input type='hidden' name='form_id' value='$id'>\n"; } ?>
                            <td><input type="radio" name="form_group_type" value="existing" checked></td>
                            <td><?php echo _("Existing Group"); ?>:</td>
                            <td><?php echo groups_dropdown('form_group_id', $form_group_id); ?></td>
                        </tr>
                        <tr>
                            <td colspan='3'><hr></td>
                        </tr>
                        <tr>
                            <td><input type="radio" name="form_group_type" value="new"></td>
                            <td>New Group:</td>
                            <td><input name="form_group_title" size="20" value="<?php echo $form_group_title; ?>"></td>
                        </tr>
                        <tr>
                            <td>&nbsp;</td>
                            <td><?php echo _("Parent Group"); ?>:</td>
                            <td><?php echo groups_dropdown('form_group_parent_id', '0', '[top level]'); ?></td>
                        </tr>
                        </table>
                      </td>
                    </tr>
                    </table>

                    <?php
                }
                // If there are not any groups yet, show the simple group editor. [LBS 20020211]
                else
                {
                    if (!$form_group_title) { $form_group_title = "My First Group"; }

                    ?>
                    <input type="hidden" name="form_group_type" value="new">
                    <input type="hidden" name="form_group_parent_id" value="0">
                    <table cellpadding="10" border="1" cellspacing="0" width='100%'>
                    <tr>
                      <td>
                        <table width="100%" cellpadding="5" cellspacing="0">
                        <tr>
                            <td align="center">New Group: <input name="form_group_title" size="20" value="<?php echo $form_group_title; ?>"></td>
                        </tr>
                        </table>
                      </td>
                    </tr>
                    </table>

                    <?php
                }

                ?>

                <p>

                <table width='100%'>
                    <tr>
                        <td>Title:</td>
                        <td><input size="40" name="form_title" value="<?php echo stripslashes($form_title); ?>"></td>
                    </tr>
                    <tr>
                        <td><?php echo _("URL"); ?>:</td>
                        <td><input size="40" name="form_url" value="<?php echo $form_url; ?>"></td>
                    </tr>
                    <tr>
                        <td><?php echo _("Description"); ?>:</td>
                        <td><input size="40" name="form_description" value="<?php echo stripslashes($form_description); ?>"></td>
                    </tr>
                    <tr>
                        <td><?php echo _("Private"); ?>:</td>
                        <td>
                            <?php echo _("No"); ?> <input type='radio' name='form_private' value='0' <?php
                                (($form_private == '1') ? '' : ' CHECKED' ); ?> >
                            <?php echo _("Yes"); ?> <input type='radio' name='form_private' value='1' <?php
                                (($form_private == '1') ? ' CHECKED' : '' ); ?> >
                        </td>
                    </tr>
                </table>
            </td>
            </tr>
            </table>

            <p><center><input type="submit" value="<?php echo (($id) ? 'Edit' : 'Add'); ?> Bookmark"></center>

            </form>

         <?php if ($id) { ?>
         <form action="<?php echo $SCRIPT_NAME ?>?action=delete_bookmark&bookmark_id=<?php echo $id ?>" method="post">
         <input type='hidden' name='back_url' value='<?php echo $HTTP_REFERER ?>'>
         <p><input type="submit" value="Delete Bookmark" onClick="return confirm('Are you sure you want to delete this bookmark?')">
         </form>

         <?php } ?>

            <?php
        } else {
            error( _("You don't have permission to edit this bookmark") );
        }

    }


apb_foot();
end_page();

?>
