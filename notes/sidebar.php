<?php
/**
 * Sidebar box for notes
 *
 * $Id: sidebar.php,v 1.18 2005/01/11 13:22:03 braverock Exp $
 */
if ( !defined('IN_XRMS') )
{
  die(_('Hacking attempt'));
  exit;
}

$note_rows = '<div id="note_sidebar">
        <table class=widget cellspacing=1 width="100%">
            <tr>
                <td class=widget_header colspan=4>'
                ._("Notes")
                .'</td>
            </tr>
            <tr>
                <td class=widget_label>'
                ._("Attached To")
                .'</td>
                <td class=widget_label>'
                ._("Date")
                .'</td>
                <td class=widget_label>'
                ._("Owner")
                .'</td>
                <td class=widget_label>&nbsp;</td>
            </tr>'."\n";

//build the notes sql query
if (strlen($on_what_table)>0){
  $where = '';
  if (isset($on_what_id)) {
    $where = "and on_what_id = '$on_what_id'";
  }
  $note_sql = "select note_id, note_description, entered_by, entered_at, username, user_contact_id
            from notes, users
            where notes.entered_by = users.user_id
            and on_what_table = '$on_what_table'
            " . $where . "
            and note_record_status = 'a'
            order by entered_at desc";
  $rst = $con->execute($note_sql);
} else {
  $note_sql = "select note_id, note_description, entered_by, entered_at, on_what_table, on_what_id, username, user_contact_id
            from notes, users
            where notes.entered_by = '$session_user_id'
            and notes.entered_by = users.user_id
            and note_record_status = 'a'
            order by entered_at";
  $rst = $con->SelectLimit($note_sql, 5, 0);
}

//uncomment the debug line to see what's going on with the query
//$con->debug=1;

//
// Note:
//
// 1) derive $note_company_id from if notes.on_what_table = 'companies'
//      from notes.on_what_id
// 2) derive $note_contact_id from if notes.on_what_table = 'contacts'
//      from notes.on_what_id
//

$rst = $con->execute($note_sql);

if (strlen($rst->fields['username']) > 0) {
  while (!$rst->EOF) {
    $attached_to_link ='';
    $on_what_name     ='';
    $note_company_id  ='';
    $note_contact_id  ='';

    if (strlen($rst->fields['on_what_table']) > 0) {
      switch ($rst->fields['on_what_table']) {
        case 'activities':
          $on_what_name = 'activity_title AS on_what_name ';
          break;
        case 'companies':
          $note_company_id    = $rst->fields['on_what_id'];
          break;
        case 'contacts':
          $on_what_name = $con->Concat("last_name","', '","first_names") . " AS on_what_name ";
          $note_contact_id   = $rst->fields['on_what_id'];
          break;
        case 'opportunities':
          $on_what_name = 'opportunity_title AS on_what_name ';
          break;
        case 'cases':
          $on_what_name = 'case_title AS on_what_name ';
          break;
        case 'campaigns':
          $on_what_name = 'campaign_title AS on_what_name ';
          break;
        case 'users':
          $on_what_name = 'username AS on_what_name ';
          break;
      }
      if (!$on_what_name) {
         $on_what_name = make_singular($rst->fields['on_what_table']).'_name as on_what_name ';
      }
      $attached_sql = 'select '
                      . $on_what_name.', '
                      . make_singular($rst->fields['on_what_table']).'_id '
                      . ' from '.$rst->fields['on_what_table']
                      . ' where '
                      . make_singular($rst->fields['on_what_table']).'_id = '. $rst->fields['on_what_id'];
      $attached_rst = $con->SelectLimit($attached_sql, 1, 0);
      if ($attached_rst) {
        $attached_to_link = "<a href=\"$http_site_root/". $rst->fields['on_what_table']
                            .'/one.php?'. make_singular($rst->fields['on_what_table']).'_id='
                            . $rst->fields['on_what_id']
                            .'">'.$attached_rst->fields['on_what_name'].'</a>';
      }
    } // if (strlen($rst->fields['on_what_table']) > 0) ...

    if ($note_contact_id) {
      $return_url = "&return_url=/contacts/one.php?contact_id=" . $note_contact_id;
    } elseif ($note_company_id) {
      $return_url = "&return_url=/companies/one.php?company_id=" . $note_company_id;
    } else {
      $return_url = "&return_url=/private/home.php";
    }

    $note_rows .= "
             <tr>
                 <td class=widget_content>
                 <font class=note_label>
                 $attached_to_link
                 </font>
                 </td>
                 <td class=widget_content>
                 <font class=note_label>"
               . $con->userdate($rst->fields['entered_at'])
               . "</font>
                 </td>\n\t<td class=widget_content>
                 <font class=note_label>"
               . $rst->fields['username']
               . "</font>
                 </td>\n\t<td class=widget_content>
                 <font class=note_label>
                 <a href='" . $http_site_root . "/notes/edit.php?note_id=" . $rst->fields['note_id'] . $return_url . "'>"
               . _("View/Edit")
               . "</a>
                 </font>
                 </td>
             </tr>";
    $note_rows .= "
             <tr>
                 <td class=widget_content colspan=4>
                 <font class=note_label>"
               . nl2br(substr($rst->fields['note_description'],0,255))
               . "</font>
                 </td>
             </tr>";

        // to next row
    $rst->movenext();

  } // while (!$rst->EOF) ..

  $rst->close();

} else {
  $note_rows .= "\n            <tr> <td class=widget_content colspan=4> "
                . _("No attached notes")
                . " </td> </tr>\n";
} // if (strlen($rst->fields['username']) > 0) ...

// put in the new button

if ( strlen( $on_what_table ) > 0 ) {

  if ( !isset($on_what_id) ) {
    $on_what_id = '';
  }

  // $notes_ vars will be set if calling page needs to override form values (see private/home.php)
  if ( isset($notes_on_what_id) ) {
    $on_what_id = $notes_on_what_id;
  }
  if ( isset($notes_return_url) ) {
    $return_url = $notes_return_url;
  } else {
    $return_url = '/'.$on_what_table.'/one.php?'.make_singular($on_what_table).'_id='.$on_what_id;
  }

  // use single quote as string delimiter so that variables stand out with color editor
  $note_rows .= '
            <tr>
            <form action="'.$http_site_root.'/notes/new.php" method="post">
                <td class=widget_content_form_element colspan=4>
                        <input type=hidden name=on_what_table value="'.$on_what_table.'">
                        <input type=hidden name=on_what_id value="'.$on_what_id.'">
                        <input type=hidden name=return_url value="'.$return_url.'">
                        <input type=submit class=button value="'._("New").'">
                </td>
            </form>
            </tr>';
}

//now close the table, we're done
$note_rows .= "        </table>\n</div>";

/**
 * $Log: sidebar.php,v $
 * Revision 1.18  2005/01/11 13:22:03  braverock
 * - removed on_what_string hack, changed to use standard make_singular fn
 *
 * Revision 1.17  2004/10/01 20:04:46  introspectshun
 * - Calling page can now override on_what_id, return_url
 *   - Allows for plugins to use notes table easily
 *
 * Revision 1.16  2004/10/01 19:19:49  introspectshun
 * - If on_what_id isn't set, pull it from the sql statement
 *
 * Revision 1.15  2004/09/28 20:43:28  introspectshun
 * - Added closing <font> tags to <td>s.
 * - Value of $on_what_table is not overwritten in the while loop
 *   - Now "New" button now displays when notes exist in list.
 * - Updated code indenting to make file more manageable
 *
 * Revision 1.14  2004/07/21 18:19:25  neildogg
 * - Stopped over-writing of variables if note was present
 *
 * Revision 1.13  2004/07/21 13:12:50  cpsource
 * - Allow 'new'
 *
 * Revision 1.12  2004/07/21 12:23:07  cpsource
 * - Stubbed out 'new' function, as design is bogus.
 *
 * Revision 1.11  2004/07/14 21:34:44  cpsource
 * - Attempt to fix undefine usage for
 *     $contact_id
 *     $company_id
 *
 * Revision 1.10  2004/07/14 19:04:41  gpowers
 * - added $http_site_root to Edit link
 *   - needed for calls from plugins
 *
 * Revision 1.9  2004/07/14 14:49:27  cpsource
 * - All sidebar.php's now support IN_XRMS security feature.
 *
 * Revision 1.8  2004/06/28 16:23:25  gpowers
 * - removed $http_site_root from return_url
 *   - $http_site_root is added to the Location: header in notes/delete.php
 *     and notes/edit-2.php
 *
 * Revision 1.7  2004/06/21 14:25:00  braverock
 * - localized strings for i18n/internationalization/translation support
 *
 * Revision 1.6  2004/06/12 06:26:27  introspectshun
 * - Now use ADODB Concat & SelectLimit functions.
 * - Updated 'on_what_table' switch with $on_what_name values for opportunites, cases, campaigns and users.
 *
 * Revision 1.5  2004/06/05 15:29:53  braverock
 * - cleaned up table headers
 * - fixed sql error handling
 * - added link to attached record
 *
 * Revision 1.4  2004/04/20 15:20:58  braverock
 * - apply patch to fix return URL on delete
 *   - fixes SF bugs 938049 & 938007
 *   - SF patch 938625 submitted by Glenn Powers
 *
 * Revision 1.3  2004/04/07 19:38:26  maulani
 * - Add CSS2 positioning
 * - Repair HTML to meet validation
 *
 * Revision 1.2  2004/04/07 13:50:53  maulani
 * - Set CSS2 positioning for the home page
 *
 * Revision 1.1  2004/03/07 14:03:05  braverock
 * Initital Checkin of side-bar centralization
 *
 */
?>