<?php
// $Id: preview.php,v 1.1 2005/04/12 20:45:10 gpowers Exp $

require(TemplateDir . '/preview.php');

// Preview what a page will look like when it is saved.
function action_preview()
{
  global $ParseEngine, $archive, $UseCaptcha;
  global $page, $document, $nextver, $pagestore;

  $document = str_replace("\r", "", $document);
  $pg = $pagestore->page($page);
  $pg->read();

  if ($UseCaptcha) {
    // Restore/start session 
    session_name("taviCaptcha");
    session_start();
    
    if ($_SESSION['captcha_phrase']) {
      $captcha = $_SESSION['captcha_image'];
    } else {
      echo "<strong>Something went wrong. No captcha information</strong>\n";
      // Should we generate a new captcha here???
      exit(1);
    }
  } else {
    $captcha='';
  }
    
  template_preview(array('page'      => $page,
                         'text'      => $document,
                         'html'      => parseText($document,
                                                  $ParseEngine, $page),
                         'timestamp' => $pg->time,
                         'nextver'   => $nextver,
                         'archive'   => $archive,
                         'captcha'   => $captcha));
}
?>
