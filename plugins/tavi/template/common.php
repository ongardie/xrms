<?php
// $Id: common.php,v 1.3 2005/12/02 19:40:00 daturaarutad Exp $

// This function generates the common prologue and header
// for the various templates.

global $include_directory;
require_once('../../include-locations.inc');


require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');
require_once($include_directory . 'confgoto.php');

$session_user_id = session_check();

if (!check_object_permission_bool($_SESSION['session_user_id'], 'wiki', 'Read')) {
    echo _("You do not have permissions to access this page.");
    exit;
}

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
                  <input type="submit" value="<?php echo _("Search"); ?>" />
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
            <tr>
            <td>
<b><?=$args['twin']?> </b>
            </td>
            </tr>
            <?php echo $sidebar_box; ?>   
            <tr>
            <td>
                <a href="index.php?action=delete&page=<?=urlencode($args['twin'])?>"><?=_("Delete")?></a>
            </td>
            </tr>
            <tr>
            <td>
                <br>
            </td>
            </tr>
            <tr>
            <td>
<b><?php echo _("Documents"); ?></b><br>
            </td>
            </tr>
            <tr>
                <td class=widget_content>                                
<?php                

# Lista dos documentos
global $http_site_root;
global $xrms_db_server,$xrms_db_username,$xrms_db_password,$xrms_db_dbname;

$WKDB = new WikiDB(0,$xrms_db_server,$xrms_db_username,$xrms_db_password,$xrms_db_dbname);

$sql = "select distinct title from tavi_pages";
$rs = $WKDB->query($sql);
if ($rs) {
    $list = array();
    while(($result = $WKDB->result($rs)))
    {
      $list[] = array($result[0]);
      echo "<a href=\"$http_site_root/plugins/tavi/index.php?page=$result[0]\">$result[0]</a><br>";
    }

}
?>                    
                <br><br>
                </td>
            </tr>
            
            <?php echo html_toolbar_top( !empty($path[1])?$path[1]:'' ); ?>
                             
        </table>
        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header>
                    <?php echo _("New"); ?>
                </td>
            </tr>                        
            <tr>
            <td>
                <input id="title" type="text" name="title" value=""> <input onclick="document.location='<?php echo $http_site_root; ?>/plugins/tavi/index.php?action=edit&page=' + document.getElementById('title').value" type="button" name="new" value="<?=_("New")?>">
            </td>
            </tr>
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
