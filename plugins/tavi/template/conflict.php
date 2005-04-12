<?php
// $Id: conflict.php,v 1.1 2005/04/12 20:45:13 gpowers Exp $

require_once(TemplateDir . '/common.php');

// The conflict template is passed an associative array with the following
// elements:
//
//   page      => A string containing the name of the wiki page being edited.
//   text      => A string containing the wiki markup of the version that was
//                saved while the user was editing the page.
//   html      => A string containing the XHTML markup of the version of the
//                page that was saved while the user was editing the page.
//   usertext  => A string containing the wiki markup of the text the user
//                tried to save.
//   timestamp => Timestamp of last edit to page.
//   nextver   => An integer; the expected version of this document when saved.

function template_conflict($args)
{
  global $EditRows, $EditCols, $UserName, $PrefsScript;

  template_common_prologue(array('norobots' => 1,
                                 'title'    => TMPL_Editing .' '. $args['page'],
                                 'heading'  => TMPL_Editing .' ',
                                 'headlink' => $args['page'],
                                 'headsufx' => '',
                                 'toolbar'  => 1));
?>
<div id="body">
<p class="warning"><?php print TMPL_WarningOtherEditing; ?></p>
<h1><?php print TMPL_CurrentVersion; ?></h1>
<form method="post" action="<?php print saveURL($args['page']); ?>">
<div class="form">
  <input type="submit" name="Save" value="<?php print TMPL_ButtonSave; ?>" />
  <input type="submit" name="Preview" value="<?php print TMPL_ButtonPreview; ?>" />
<?php
  if($UserName != '')
    { print TMPL_YourUsername .' '. html_ref($UserName, $UserName); }
  else
    { echo TMPL_VisitPrefs . "\n"; }
?><br />
  <input type="hidden" name="nextver" value="<?php print $args['nextver']; ?>" />
  <textarea name="document" rows="<?php
    print $EditRows; ?>" cols="<?php
    print $EditCols; ?>" wrap="virtual"><?php
  print str_replace('<', '&lt;', str_replace('&', '&amp;', $args['text']));
?></textarea><br />
  <?php echo TMPL_SummaryOfChange; ?>
  <input type="text" name="comment" size="40" value="" /><br />
  <?php echo TMPL_AddToCategory; ?> 
  <input type="text" name="categories" size="40" value="" />
<hr />
<h1><?php echo TMPL_YourChanges; ?></h1>
  <textarea name="discard" rows="<?php
    print $EditRows; ?>" cols="<?php
    print $EditCols; ?>" wrap="virtual"><?php
  print str_replace('<', '&lt;', str_replace('&', '&amp;', $args['usertext']));
?></textarea><br />
</div>
</form>
<h1><?php echo TMPL_PreviewCurrentVersion; ?></h1>
<?php
  print $args['html'];
?>
</div>
<?php
  template_common_epilogue(array('twin'      => $args['page'],
                                 'edit'      => '',
                                 'editver'   => 0,
                                 'history'   => $args['page'],
                                 'timestamp' => $args['timestamp'],
                                 'nosearch'  => 0));
}
?>
