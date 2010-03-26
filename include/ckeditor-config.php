<?php
/* 
 * This file contains the default configuration settings for the CKEditor used
 * for entering HTML Activity Notes (if that is enabled in your installation)
 *
 */

// Defines the location of the CKEditor code
$ckeditor_location = $xrms_file_root . '/js/ckeditor/';
$ckeditor_location_url = $http_site_root . '/js/ckeditor/';

// Configure the CKEditor
$ckeditor_config            = array();
$ckeditor_config['height']  = '150';
// Available skins are 'kama', 'office2003' and 'v2'
$ckeditor_config['skin']    = 'office2003';
$ckeditor_config['toolbar'] = array (
    array ( 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock', '-',
            'Link', 'Unlink', '-',
            'Table', 'HorizontalRule', 'Smiley', 'SpecialChar', '-',
            'Find', 'Replace', 'SelectAll', 'RemoveFormat', '-',
            'SpellChecker', 'Source', 'ShowBlocks', 'Maximize'),
    array ( 'Format', 'Bold', 'Italic', 'Underline', '-',
            'NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', 'Blockquote', '-',
            'Cut', 'Paste', 'PasteText', 'PasteFromWord', '-',
            'Undo', 'Redo')
            // Unused toolbar buttons:
            // 'Copy', 'Strike', 'Subscript', 'Superscript', 'Preview', 'Print', 'Scayt',
            // 'Form', 'Checkbox', 'Radio', 'TextField', 'Textarea', 'Select', 'Button', 'ImageButton', 'HiddenField',
            // 'Anchor', 'Image', 'Flash', 'PageBreak', 'Styles', 'Font', 'FontSize', 'TextColor', 'BGColor', 'About'
);


?>
