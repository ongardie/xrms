<?php

/**
 * Form for creating a new file and editing existing ones
 *
 * $Id: edit-form.php,v 1.2 2005/12/14 05:03:45 daturaarutad Exp $
 */


$page_title = _("Attach File");
start_page($page_title, true, $msg);

$file_entered_at = '';

?>

<div id="Main">
    <div id="Content">

        <!-- FORM will call "self" page -->
        <form enctype="multipart/form-data" action="" method="post">

        <!-- more data to pass around -->
        <input type="hidden" name="attached_to_name" value="<?php echo $attached_to_name ?>">
        <input type="hidden" name="MAX_FILE_SIZE"    value="<?php echo $max_file_size; ?>">
        <input type="hidden" name="on_what_table"    value="<?php echo $on_what_table ?>">
        <input type="hidden" name="on_what_id"       value="<?php echo $on_what_id ?>">
        <input type="hidden" name="return_url"       value="<?php echo $return_url ?>">
        <input type="hidden" name="act"              value="up">

        <table class="widget" cellspacing="1">
            <!-- Table Header -->
            <tr>
                <td class="widget_header" colspan="2">
                    <?php echo _("File Information"); ?>
                </td>
            </tr>
            <!-- Template Name Display -->
            <tr>
                <td class="widget_label_right">
                    <?php echo _("Attached To"); ?>
                </td>
                <td class="widget_content_form_element">
                    <b><?php echo $attached_to_name; ?></b>
                </td>
            </tr>
            <!-- Display Name of File -->
            <tr>
                <td class="widget_label_right">
                    <?php echo _("Summary"); ?>
                </td>
                <td class="widget_content_form_element">
                    <input type="text"
                           name="file_pretty_name"
                           id="file_pretty_name"
                           size="40"
                           value="<?php echo $_POST['file_pretty_name'] ?>" />
                    </td>
            </tr>
            <!-- Description of File -->
            <tr>
                <td class="widget_label_right">
                    <?php echo _("Description"); ?>
                </td>
                <td class="widget_content_form_element">
                    <textarea rows="10" cols="70" name="file_description"><?php echo $_POST['file_description'] ?></textarea>
                </td>
            </tr>
            <!-- UPLOAD Object -->
            <tr>
                <td class="widget_label_right">
                    <?php echo _("Upload"); ?>
                </td>
                <td class="widget_content_form_element">
                    <input type="file" name="file1" />
                </td>
            </tr>
            <!-- Submit Buttons -->
            <tr>
                <td class="widget_content_form_element" colspan="2">
                    <input class="button" type="submit" value="<?php echo _("Upload");?>" />
                </td>
            </tr>
        </table>
        </form>

    </div>

        <!-- right column //-->
    <div id="Sidebar">

        &nbsp;

    </div>

</div>

<script language="JavaScript" type="text/javascript">

function initialize() {
    document.forms[0].file_pretty_name.focus();
}

initialize();

</script>

<?php

end_page();

// ========================================================================
// ========================================================================

/**
 * $Log: edit-form.php,v $
 * Revision 1.2  2005/12/14 05:03:45  daturaarutad
 * change Display Name to Summary
 *
 * Revision 1.1  2005/07/06 17:31:20  jswalter
 *  - initial commit
 *  - pulled from 'edit.php'
 *
 *
 */

?>
