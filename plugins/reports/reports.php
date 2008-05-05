<?php
/**
 * Custom Reports Administration
 *
 * @author Randy Martinsen <randym56@hotmail.com>
 *
 * $Id: reports.php,v 1.1 2008/05/05 22:19:00 randym56 Exp $
 */

// include common files
require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

$session_user_id = session_check( 'Admin' );
$file_tmp_name = $_FILES['file1']['tmp_name'];

if (isset($_FILES['file1'])) {
	//upload file here then clear variable
	move_uploaded_file($file_tmp_name,$xrms_file_root.'/reports/custom/'.$_FILES['file1']['name']);
	$_FILES['file1'] = NULL;
	}
	
if (isset($_GET['filename'])) {
	unlink($_GET['filename']);
	header ("location: reports.php");
	}	

$page_title = _("Custom Reports Administration");

$reportspath = $xrms_file_root . '/reports/custom/';

start_page($page_title, true, $status_msg);


?>
<div id="Main">
    <div id="Content">

<form method=post name=reports-admin>
    <table class=widget cellspacing=1 width="100%">
    <input type=hidden name=plugin_submit value=true>
<tr><td class=widget_header colspan="3">Manage Custom Reports</td></tr>

<?php

  if ( file_exists($reportspath) ) {
      $fd = opendir( $reportspath );
      $op_report = array();
      while (false !== ($file = readdir($fd))) {
        if ($file != '.' && $file != '..' && $file != 'CVS' && $file != 'index.php') {
			$start1 = strpos(file_get_contents($reportspath.$file),'reportname: ')+12;
			$end1 = strpos(file_get_contents($reportspath.$file),'reportdescrip: ')-$start1-3;
			$start2 = strpos(file_get_contents($reportspath.$file),'reportdescrip: ')+14;
			$end2 = strpos(file_get_contents($reportspath.$file),'author: ')-$start2-3;
			$reportname = substr(file_get_contents($reportspath.$file),$start1,$end1);
			$reportdescrip = substr(file_get_contents($reportspath.$file),$start2,$end2);
            echo "<tr><td><a href=\"reports.php?filename=".$reportspath.$file."\">Delete</a></td><td><a href=\"" . $http_site_root. "/reports/custom/".$file . "\">".
			$reportname ."</a></td><td>" .
			$reportdescrip . "</td></tr>"; //start at position 12 always
        }
      }
    closedir($fd);

    echo '</table>';
  } else {
    echo '<tr><td colspan=3 align="center">'._("Custom Reports directory could not be found: ") . $reportspath . ", or No Custom files exist.</td></tr>\n";
  }
?>
</table></form>

</div>
    <div id="Sidebar">
        <table class=widget cellspacing=1 width="100%">
            <tr>
                <td class=widget_header><?php echo _("Add Custom Report"); ?></td>
            </tr>
        <form enctype="multipart/form-data" action="reports.php" method="post" name="savefile">
            <tr>
                <td>WARNING: Your new report file MUST have the correct headers to work properly.<br>The file must also have a .php extension.  To see an example file with appropriate headers <a href="sample.html" target="_blank">Click Here</a></td>
            </tr>
            <tr>
                <td class=widget_label_left><?php echo _("Report File: "); ?><input type=file name=file1></td>
            </tr>
            <tr>
                <td class=widget_content_form_element><input type=submit class=button value="<?php echo _("Save File"); ?>">&nbsp;
				</td>
            </tr>
        </form>
        </table>



    </div>
</div>
<?php
/**
 * $Log: reports.php,v $
 * Revision 1.1  2008/05/05 22:19:00  randym56
 * Custom reports plugin added to XRMS
 *
 *
 */
?>
