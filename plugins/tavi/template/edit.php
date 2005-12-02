<?php
// $Id: edit.php,v 1.2 2005/12/02 19:40:00 daturaarutad Exp $

require_once(TemplateDir . '/common.php');

// The edit template is passed an associative array with the following
// elements:
//
//   page      => A string containing the name of the wiki page being edited.
//   text      => A string containing the wiki markup of the wiki page.
//   timestamp => Timestamp of last edit to page.
//   nextver   => An integer; the expected version of this document when saved.
//   archive   => An integer.  Will be nonzero if this is not the most recent
//                version of the page.

function template_edit($args)
{
  global $EditRows, $EditCols, $UserName, $PrefsScript, $UseCaptcha;

  template_common_prologue(array('norobots' => 1,
                                 'title'    => TMPL_Editing .' '. $args['page'],
                                 'heading'  => TMPL_Editing .' ',
                                 'headlink' => $args['page'],
                                 'headsufx' => '',
                                 'toolbar'  => 1));
?>
<div id="body">
<form method="post" action="<?php print saveURL($args['page']); ?>">
<div class="form">
<?php 
  if ($UseCaptcha) {
    $formTxt = TMPL_PreCaptcha .
               '<input name="posted_code" value="" type="text" size="20" />';
    echo html_captcha($args['captcha'], $formTxt);
  }
 ?><input type="submit" name="Save" value="<?php echo TMPL_ButtonSave; ?>" />
<input type="submit" name="Preview" value="<?php echo TMPL_ButtonPreview; ?>" />
<?php
  if($UserName != '')
    { print TMPL_YourUsername .' '. html_ref($UserName, $UserName); }
  else
  { echo TMPL_VisitPrefs . "\n"; }
 ?><br />
  <input type="hidden" name="nextver" value="<?php print $args['nextver']; ?>" />
<?php  if($args['archive'])
    {?>
  <input type="hidden" name="archive" value="1" />
<?php  }

global $fckeditor_location_url;
global $fckeditor_location;

include("$fckeditor_location/fckeditor.php") ;
        
$sBasePath = "$http_site_root/plugins/tavi/fckeditor/";

$oFCKeditor = new FCKeditor('document') ;
$oFCKeditor->BasePath	= $fckeditor_location_url ;
$oFCKeditor->Height = 600;
$oFCKeditor->Width = 700;        
$oFCKeditor->Value		= $args['text'];
$oFCKeditor->Create() ;


?>
  <br />
  <?php echo TMPL_SummaryOfChange; ?>
  <input type="text" name="comment" size="40" value="" /><br />
  <?php echo TMPL_AddToCategory; ?>
  <input type="text" name="categories" size="40" value="" />
</div>
</form>
</div>
<?php
  template_common_epilogue(array('twin'      => $args['page'],
                                 'edit'      => '',
                                 'editver'   => '',
                                 'history'   => $args['page'],
                                 'timestamp' => $args['timestamp'],
                                 'nosearch'  => 0));
}
?>
