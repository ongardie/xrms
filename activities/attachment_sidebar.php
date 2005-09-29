<?php
if ( !defined('IN_XRMS') )
{
  die('Hacking attempt');
  exit;
}

/**
 * Activity Association Information Sidebar
 *
 * Include this file anywhere you want to display the relation to another entity
 *
 * @param string $on-what_table with the string identifying the attached table
 * @param integer $on_what_id The on_what_id should be set before including this file
 *
 * @author Aaron van Meerten
 *
 * $Id: attachment_sidebar.php,v 1.5 2005/09/29 14:48:40 vanmer Exp $
 */

$buttons="<input type=button class=button name=change_attachment onclick=\"changeAttachment()\"  value=\""._("Change Attachment")."\">";
if ($on_what_table AND $on_what_id) {
    $attached_to_link = "<a href='$http_site_root" . table_one_url($on_what_table, $on_what_id) . "'>";
    $singular=make_singular($on_what_table);
    $name_field=$con->Concat(implode(", ' ' , ", table_name($on_what_table)));
    $on_what_field=$singular.'_id';
    $sql = "select $name_field as attached_to_name from $on_what_table WHERE $on_what_field = $on_what_id";
    if (!$activity_template_id) {
        $buttons.='<input type=button class=button value="'._("Detach") . '" onclick=changeAttachment(\'detach\')>';
    } else { $buttons=''; }
} else {
    $attached_to_link = "N/A";
    $sql = "select * from companies where 1 = 2";
}

$rst = $con->execute($sql); 

if ($rst) {
    $attached_to_name = $rst->fields['attached_to_name'];
    $attached_to_link .= $attached_to_name . "</a>\n";
    $rst->close();
    if ($attached_to_name <> NULL) {
       $related_block .= "\n" . '<div id="related">
                        <table class="widget" cellspacing="1">
                            <tr>
                                <td class="widget_header">' . _("Attached To") . ' ' . _(ucfirst($singular)) .'</td>
                            </tr>' . "\n";
  } else {
       $related_block.='<div id="related">
                        <table class="widget" cellspacing="1">
                            <tr>
                                <td class="widget_header">'._("Attached To") . ' ' .'</td>
                            </tr>'."\n";
  }
    $related_block.="\n<tr>\n\t<td class=widget_content>$attached_to_link</td>\n</tr>\n";
    $related_block.="\n<tr>
        <td class=widget_content_form_element>
            $buttons
        </td>
    </tr>\n";
    $related_block.="\n\t</table>\n</div>\n";
} else { db_error_handler($con, $sql); }

/**
  * $Log: attachment_sidebar.php,v $
  * Revision 1.5  2005/09/29 14:48:40  vanmer
  * - changed to allow detach of activity from association with another entity
  * - changed to only allow change/detach of activity if activity is not part of a workflow (is a template activity)
  *
  * Revision 1.4  2005/09/25 04:12:23  vanmer
  * - added ability to detach an activity from an on_what_table/on_what_id relationship using Detach button
  * - added case to check for $on_what_id before attempting to query for activity attachmetn
  * - added error handling on sql errors when querying for a name of the activity's attached entity
  *
  * Revision 1.3  2005/08/24 11:03:09  braverock
  * - remove non-printable characters causing parse error from attached to link
  * - quote properties of tags
  *
  * Revision 1.2  2005/08/23 16:42:14  braverock
  * - apply patch for localization problem when activity not attached
  *   - patch supplied by Daniele Baudone (SF: dbaudone)
  *
  * Revision 1.1  2005/08/11 02:32:22  vanmer
  * -Initial revision of a sidebar to display and control association of an activity
  *
**/
?>