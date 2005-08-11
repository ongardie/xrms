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
 * $Id: attachment_sidebar.php,v 1.1 2005/08/11 02:32:22 vanmer Exp $
 */

if ($on_what_table) {
    $attached_to_link = "<a href='$http_site_root" . table_one_url($on_what_table, $on_what_id) . "'>";
    $singular=make_singular($on_what_table);
    $name_field=$con->Concat(implode(", ' ' , ", table_name($on_what_table)));
    $on_what_field=$singular.'_id';
    $sql = "select $name_field as attached_to_name from $on_what_table WHERE $on_what_field = $on_what_id";
} else {
    $attached_to_link = "N/A";
    $sql = "select * from companies where 1 = 2";
}

$rst = $con->execute($sql);

if ($rst) {
    $attached_to_name = $rst->fields['attached_to_name'];
    $attached_to_link .= $attached_to_name . "</a>";
    $rst->close();
    $related_block.='<div id=related><table class=widget cellspacing=1><tr><td class=widget_header>'._("Attached To") . ' ' ._(ucfirst($singular)).'</td></tr>';
    $related_block.="<tr><td class=widget_content>$attached_to_link</td></tr>\n";
    $related_block.="<tr><td class=widget_content_form_element><input type=button class=button name=change_attachment onclick=\"changeAttachment()\"  value=\""._("Change Attachment")."\"></td></tr>";
    $related_block.="</table></div>";
}

/**
  * $Log: attachment_sidebar.php,v $
  * Revision 1.1  2005/08/11 02:32:22  vanmer
  * -Initial revision of a sidebar to display and control association of an activity
  *
**/
?>