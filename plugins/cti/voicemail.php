<?php
/**
 * The main page for the Demo plugin
 *
 * @todo create more examples here.
 *
 * $Id: voicemail.php,v 1.3 2006/01/02 23:52:14 vanmer Exp $
 */

// include the common files
require_once('../../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

$session_user_id = session_check();

$msg = $_GET['msg'];

//connect to the database
$con = get_xrms_dbconnection();

/*********************************/
/*** Include the sidebar boxes ***/
//include the Cases sidebar
$case_limit_sql = "and cases.user_id = $session_user_id";
require_once($xrms_file_root."/cases/sidebar.php");

//include the opportunities sidebar
$opportunity_limit_sql = "and opportunities.user_id = $session_user_id \nand status_open_indicator = 'o'";

require_once($xrms_file_root."/opportunities/sidebar.php");

//include the files sidebar
require_once($xrms_file_root."/files/sidebar.php");

//include the notes sidebar
require_once($xrms_file_root."/notes/sidebar.php");

/** End of the sidebar includes **/
/*********************************/

//uncomment the debug line to see what's going on with the query
// $con->debug = 1;


//You would define any SQL you needed from the XRMS database here and execute it...

//close the database connection, as we are done with it.
$con->close();

$page_title = "Voice Mail";
start_page($page_title);

?>

<table border=0 cellpadding=0 cellspacing=0 width="100%">
    <tr>
        <td class=lcol width="75%" valign=top>

<!-- START PLUGIN -->

<?php

$user = $_SESSION['username'];
$context = "default";
$mailbox = "INBOX";
$vm_file_exten = "WAV";

$vmurlprefix="/voicemail";
$vmconfig = "/etc/asterisk/voicemail.conf";

$contents = file($vmconfig);
$vmexten = array();

foreach ($contents as $line) {
preg_match("/^(\d+)([^,]+),($user)(.*)$/i", $line, $findexten);
if ($findexten[1] != "") { $exten = $findexten[1]; };
};

echo "User: " . $user  . "<br>";
echo "Context: ". $context . "<br>";
echo "Extension: ". $exten . "<br>";
echo "Mailbox: ". $mailbox . "<br>";
?>

<br>

<table cellspacing=5>
<tr>
<th align=left>Msg<br>Num</th>
<th align=left>Date</th>
<th align=left>From</th>
<th align=left>Duration</th>
</tr>

<?php
$vmdir="/var/spool/asterisk/voicemail/$context/$exten/$mailbox";

if ($handle = opendir($vmdir)) {
   chdir ($vmdir);
   while (false !== ($filename = readdir($handle))) {
      if (preg_match("/txt/i", $filename)) {
         $filehandle = fopen($filename, "r");
         $contents = fread($filehandle, filesize($filename));
         fclose($filehandle);
         echo "<tr><td>";
// Msg Num
         //preg_match("/^(msg)?(\.txt)/i", $filename, $msgnum);
         preg_match("/^(msg)(\d+)(\.txt)$/", $filename, $msgnum);
         echo "<a href=$vmurlprefix/" . $context . "/" . $exten . "/" . $mailbox . "/msg" . $msgnum[2] . "." . $vm_file_exten . ">" . $msgnum[2] . "</a>";
         echo "</td><td>";
// Date
         preg_match("/(origdate=)([^\n]+)/i",$contents,$date);
         echo $date[2];
         echo "</td><td>";
// From
         preg_match("/(callerid=)([^\n]+)/i",$contents,$callerid);
         preg_match("/(callerchan=)([^\n]+)/i",$contents,$callerchan);
         echo $callerid[2] . " (" . $callerchan[2] . ")";
         echo "</td><td>";
// Duration
         preg_match("/(duration=)([^\n]+)/i",$contents,$duration);
         echo $duration[2];
         echo "</td></tr>\n";
      };
   };

   closedir($handle);
};

?>
</table>


<!-- END PLUGIN -->

        </td>

        <!-- gutter //-->
        <td class=gutter width=1%>
        &nbsp;
        </td>

        <!-- right column //-->
        <td class=rcol width="24%" valign=top>

            <!-- opportunities //-->
            <?php  echo $opportunity_rows; ?>

            <!-- cases //-->
            <?php  echo $case_rows; ?>

            <!-- files //-->
            <?php  echo $file_rows; ?>

            <!-- notes //-->
            <?php  echo $note_rows; ?>

        </td>
    </tr>
</table>

<?php
end_page();
?>
