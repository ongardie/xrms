<?php
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

$id = $_GET['id'];

$page_title = _("Bookmarks") .  " - " .  _("Edit Group");
start_page($page_title, true, $msg);

    // We're actually editing the group.
    if (($action == "edit_group") && $id && $form_title) {

    $sql = "SELECT * FROM apb_groups WHERE group_id = '" . $id . "' AND user_id = '" . $APB_SETTINGS['auth_user_id'] . " LIMIT 1";
    $rst = $con->execute($sql);

    $rec = array();
    $rec['group_parent_id'] = $form_group_parent_id;
    $rec['group_title'] = $form_title;

    $upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
    if ($upd) {
        $upd_rst = $con->execute($upd);
        if (!$upd_rst) {
            db_error_handler ($con, $upd);
        }  else { 
            $g = apb_group($id);

            echo "<p>" . _("Group updated!") . "</p>"
                . "<p>" . $g->print_group_path() . "</p>"
                . "<p><a href='" . $back_url . "'>"
                . _("Go Back to Editing") . "</a></p>";
        }
    }
    } else {
    // We're not doing the SQL editing (updating) yet, so display the form page.

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

            <table class=widget>
                <tr>
                    <td class=widget_header>
                        <?php echo $g->print_group_path() ?>
                    </td>
                </tr>

            <p>
            <form action="<?php echo $SCRIPT_NAME ?>?action=edit_group" method="post">
            <input type='hidden' name='back_url' value='<?php echo $HTTP_REFERER ?>'>
            <?php if ($id) { print "<input type='hidden' name='id' value='$id'>\n"; } ?>
            <tr>
            <td>
                <table width='100%'>
                    <tr>
                        <td><?php echo _("Parent Group"); ?>:</td>
                        <td><?php groups_dropdown('form_group_parent_id', $parent_id, '[top level]', $form_group_id) ?></td>
                    </tr>
                    <tr>
                        <td><?php echo _("Title"); ?>:</td>
                        <td><input size="40" name="form_title" value="<?php echo stripslashes($form_title) ?>"></td>
                    </tr>
<!-- Future Feature(s)
                    <tr>
                        <td><?php echo _("Description"); ?>:</td>
                        <td><input size="40" name="form_description" value="<?php echo stripslashes($form_description) ?>"></td>
                    </tr>
                    <tr>
                        <td><?php echo _("Private"); ?>:</td>
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
            error( _("You don't have permission to edit this group") );
        }

    }


end_page();

?>
