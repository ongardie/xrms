<?php
// $Id: common.php,v 1.1 2005/04/12 20:45:13 gpowers Exp $

// This function generates the common prologue and header
// for the various templates.

require_once('../../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');
require_once($include_directory . 'confgoto.php');

$session_user_id = session_check();

function template_common_prologue($args)
{
$page_title = _("User Documentation Wiki");
  global $WikiName, $HomePage, $WikiLogo, $MetaKeywords, $MetaDescription;
  global $StyleScript, $SeparateTitleWords, $SeparateHeaderWords;

  preg_match("/^(.*)\//", $args['headlink'], $path); // Find startpath of page
  ob_start();                           // Start buffering output.

  if($SeparateTitleWords)
    { $args['title'] = html_split_name($args['title']); }
?>
<?php
    $title .= $args['heading'];
    if($args['headlink'] != '')
    {
?>
<?php
    if($SeparateHeaderWords)
      { $title .= html_split_name($args['headlink']); }
    else
      { $title .= $args['headlink']; }
?></a>
<?php
    }
    $title .= $args['headsufx'];
start_page($page_title);
?>
<div id="Main">
  <div id="Content">
        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header>
                    <?php echo $title; ?>
               </td>
            </tr>
            <tr>
                <td class=widget_content>

<?php
}

function template_common_epilogue($args)
{
  global $FindScript, $pagestore, $sidebar_options;
?>
</td>
</tr>
</table>

<?php
  if($args['history'])
  {
    $sidebar_box .= '<tr><td class=widget_content><a href="'. historyURL($args['history']). '">'.
         TMPL_ViewDocHistory . '</a></td></tr>';
  }
  if($args['twin'] != '')
  {
    if(count($twin = $pagestore->twinpages($args['twin'])))
    {
      $sidebar_box .= "<tr><td class=widget_content>" .  TMPL_TwinPages . '</td></tr>';
      for($i = 0; $i < count($twin); $i++)
        { $sidebar_box .= "<tr><td class=widget_content>" . html_twin($twin[$i][0], $twin[$i][1]) . '</td></tr>'; } 
    }
  }
  if($args['timestamp'])
  {
    $sidebar_box .= "<tr><td class=widget_content>". TMPL_DocLastModified . ':<br /> '. 
         html_time($args['timestamp']) . '</td></tr>';
  }
  if($args['edit'])
  {
    if($args['editver'] == 0)
    {
      $sidebar_box .= '<tr><td class=widget_content><a href="'. editUrl($args['edit']) . '">'.TMPL_EditDocument.'</a></td></tr>'; 
    }
    else if($args['editver'] == -1)
    {
      $sidebar_box .= '<tr><td class=widget_content><a href="' . TMPL_NoEditDocumentn . '</a><br />';
    }
    else
    {
      $sidebar_box .= '<tr><td class=widget_content><a href="' . editUrl($args['edit'], $args['editver']) . '"></td></tr>'.
           TMPL_EditArchiveVersion . '</a><br />';
    }

  }
  if(!$args['nosearch'])
  {
?>


</div>

<div id="Sidebar">
        <table class=widget cellspacing=1>
            <tr>
              <td class=widget_header><?php echo _("Search"); ?></td>  </tr>
            <tr>
              <td class=widget_content>
                <form method="get" action="<?php print $FindScript; ?>">
                  <input type="hidden" name="action" value="find" />
                  <input type="text" name="find" size="20" />
                  <input type="submit" value="<?php echo _("Seach"); ?>" />
                </form>
            </td>
          </tr>
        </table>
        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header>
                    <?php echo _("Options"); ?>
                </td>
            </tr>
                <?php echo html_toolbar_top( !empty($path[1])?$path[1]:'' ); ?>
                <?php echo $sidebar_box; ?>
        </table>
      </div>
<?php
  }
?>
    </div>
  </body>
</html>
<?php

  ob_end_flush();
}
?>
