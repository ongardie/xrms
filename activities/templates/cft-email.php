<?php

//begin output
$page_title = _("Email").': '.$activity_title;
start_page($page_title, true, $msg);

//load confGoTo.js
confGoTo_includes();

?>

<script language="JavaScript" type="text/javascript">

function changeAttachment(attachAction) {
   if (!attachAction) {
      document.forms[0].change_attachment.value='true';
      document.forms[0].submit();
   } else if (attachAction=='detach') {
      document.forms[0].change_attachment.value='detach';
      document.forms[0].submit();
   }
}

function logTime() {
    var date = new Date();
    var d = date.getDate();
    var day = (d < 10) ? '0' + d : d;
    var m = date.getMonth() + 1;
    var month = (m < 10) ? '0' + m : m;
    var yy = date.getYear();
    var year = (yy < 1000) ? yy + 1900 : yy;

    var h = date.getHours();
    var hour = (h < 10) ? '0' + h : h;
    var mm = date.getMinutes();
    var minute = (mm < 10) ? '0' + mm : mm;
    var s = date.getSeconds();
    var second = (s < 10) ? '0' + s : s;

    return year + '-' + month + '-' + day + ' ' + hour + ':' + minute + ':' + second;
}
</script>

    <link rel="stylesheet" type="text/css" href="../css/tabcontent.css" />

<script type="text/javascript" src="../js/tabcontent.js">

/***********************************************
* Tab Content script v2.0- © Dynamic Drive DHTML code library (www.dynamicdrive.com)
* This notice MUST stay intact for legal use
* Visit Dynamic Drive at http://www.dynamicdrive.com/ for full source code
***********************************************/

</script>

<div id="Main">
    <div id="Content">

        <?php echo $activity_content_top; ?>

        <form action="edit-2.php" method="post" class="print" name="activity_data">
            <input type=hidden name=return_url value="<?php  echo $return_url; ?>">
            <input type=hidden name=current_activity_status value="<?php  echo $activity_status; ?>">
            <input type=hidden name=activity_status value="<?php  echo $activity_status; ?>"> 
            <input type=hidden name=activity_id value="<?php  echo $activity_id; ?>">
            <input type=hidden name=company_id value="<?php  echo $company_id; ?>">
            <input type=hidden name=contact_id value="<?php  echo $contact_id; ?>">
            <input type=hidden name=on_what_table value="<?php  echo $activity_on_what_table; ?>">
            <input type=hidden name=on_what_id value="<?php  echo $activity_on_what_id; ?>">
            <input type=hidden name=table_name value="<?php echo $table_name ?>">
            <input type=hidden name=table_status_id value="<?php echo $table_status_id ?>">
            <input type=hidden name=old_status value="<?php echo $table_status_id ?>">
            <input type=hidden name=thread_id value="<?php  echo $thread_id; ?>">
            <input type=hidden name=followup_from_id value="<?php  echo $followup_from_id; ?>">
            <input type=hidden name=email_to value="<?php  echo $email_to; ?>">
                
        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=2>
                    <input type=text size=50 name=activity_title value="<?php  echo htmlspecialchars(trim($activity_title)); ?>">
                    <?php echo ($save_and_next) ? "(<input onclick=\"var input = prompt('Jump to', ''); if(input != null && input != '') document.location.href='browse-next.php?activity_id=" . $activity_id . "&pos=' + (input);\" type=button class=button value=" . $_SESSION['pos'] . ">/" . count($_SESSION['next_to_check']) . ")": "" ; ?>                   
                </td>
            </tr>

            <tr>
                <td class=widget_content_form_element colspan=2>
    
                <?php if($print_view) {
                        echo htmlspecialchars(nl2br(trim($activity_description)));
                        echo "<input type=hidden name=activity_description value=\"" . htmlspecialchars(nl2br(trim($activity_description))) . "\">\n";
                      } else { ?>
                      <?php if (get_user_preference($con, $user_id, "html_activity_notes") == 'y') { ?>
                          <input type="hidden" id="FCKeditor1" name="activity_description" value="<?php  echo htmlspecialchars(trim($activity_description)); ?>"
                                style="display:none" />
                               <input type="hidden" id="FCKeditor1___Config" value="" style="display:none" />
                               <iframe id="FCKeditor1___Frame" src="/xrms/include/fckeditor/editor/fckeditor.html?InstanceName=FCKeditor1&amp;Toolbar=Default"
                                    width="100%" height="320" frameborder="0" scrolling="no"></iframe>
                        <?php }  else {?>
                            <textarea rows=20 cols=70 name=activity_description id=the_activity_description><?php  echo htmlspecialchars(trim($activity_description)); ?></textarea>
                    <?php }  ?>
                <?php }  ?>
                </td>
            </tr>
            <?php echo $activity_inline_rows; ?>
            <?php  echo $history_text; ?>
        </table>
    </div>

    <!-- sidebar //-->
    <div id="Sidebar">
    <!-- sidebar top plugins //-->
    <?php echo $sidebar_top_plugin_rows; ?>
       <div id="topsidebar" style="height: 400px">
           <ul id="uppertabs" class="shadetabs">
              <li><a href="#" rel="tDetails">Details</a></li>
              <li><a href="#" rel="tRegarding">Regarding</a></li>
              <li><a href="#" rel="tCompany">Company</a></li>
           </ul>

<div style="border:1px solid gray; padding: 1px">
    <div id="tDetails" class="selected">
        <?php echo $activity_details_sidebar; ?>
    </div>
    
        <div id="tRegarding" class="tabcontent">
            <?php echo $regarding_sidebar; ?>
        </div>
                
        <div id="tCompany" class="tabcontent">           
            <?php echo $company_block; ?>
        </div>

</div>
</div>
    
        <ul id="lowertabs" class="shadetabs">
            <li><a href="#" rel="tContact">Sender</a></li>
            <li><a href="#" rel="tParticipants">Recipients</a></li>
            <li><a href="#" rel="tRelationships">Related</a></li>
            <li><a href="#" rel="tTools">Tools</a></li>
            <?php if (false) echo '<li><a href="#" rel="tFiles">Files</a></li>'; ?>
        </ul>

    <div style="border:1px solid gray; padding: 1px">
 
         <div id="tContact" class="tabcontent">           
            <?php echo $contact_block; ?>
        </div>
        
        <div id="tParticipants" class="selected">        
            <?php echo $participant_block; ?>
        </div>
               
        <div id="tRelationships" class="tabcontent">   
            <?php if ( isset($relationship_link_rows) && $relationship_link_rows ) echo $relationship_link_rows; ?>
        </div>
        
        <div id="tfiles" class="tabcontent">          
            <?php if ($enable_files_sidebar) echo $file_rows; ?>
        </div>
        
        <div id="tTools" class="tabcontent">           
            <?php echo $tools_sidebar; ?>
        </div>
        
        <?php if ($sidebar_plugin_rows) echo $sidebar_plugin_rows; ?>
    </div>
</div>

    <div id="Content">
        <form action=one.php name="OneActivityForm" method=post>
            <input type=hidden name="activity_id" value="<?php echo $activity_id; ?>">
            <input type=hidden name="return_url" value="<?php echo $return_url; ?>">
            <?php
                // output the selectable columns widget
                echo $pager_columns_selects;
                echo $related_activities_widget['content'];
                echo $related_activities_widget['sidebar'];
                echo $related_activities_widget['js'];
            ?>
        </form>
        <?php echo $activity_content_bottom; ?>
        </div>
</div>

<script type="text/javascript">
var uppertabs=new ddtabcontent("uppertabs") //enter ID of Tab Container
uppertabs.setpersist(false) //toogle persistence of the tabs' state
uppertabs.setselectedClassTarget("linkparent") //"link" or "linkparent"
uppertabs.init()
</script>

<script type="text/javascript">
var lowertabs=new ddtabcontent("lowertabs") //enter ID of Tab Container
lowertabs.setpersist(false) //toogle persistence of the tabs' state
lowertabs.setselectedClassTarget("linkparent") //"link" or "linkparent"
lowertabs.init()
</script>
  
<script type="text/javascript">
    var old_description='';
    var old_type='';
    function HideResolutionFields() {
        old_type=document.getElementById('activity_resolution_type_id').value;
        old_description=document.getElementById('resolution_description').value;
        document.getElementById('resolution_type').style.display='none';
        document.getElementById('resolution_reason').style.display='none';
        document.getElementById('activity_resolution_type_id').value='';
        document.getElementById('resolution_description').value='';
        document.getElementById('activity_completed').onclick=ShowResolutionFields;
    }
    function ShowResolutionFields() {
        document.getElementById('resolution_type').style.display='';
        document.getElementById('resolution_reason').style.display='';
        document.getElementById('activity_resolution_type_id').value=old_type;
        document.getElementById('resolution_description').value=old_description;
        document.getElementById('activity_completed').onclick=HideResolutionFields;
    }

    if (!document.getElementById('activity_completed').checked) {
        HideResolutionFields();
    } else {
        document.getElementById('activity_completed').onclick=HideResolutionFields;
    }
</script>
<script type="text/javascript">
    Calendar.setup({
        inputField     :    "f_date_c",      // id of the input field
        ifFormat       :    "%Y-%m-%d %H:%M:%S",       // format of the input field
        showsTime      :    true,            // will display a time selector
        button         :    "f_trigger_c",   // trigger for the calendar (button ID)
        singleClick    :    false,           // double-click mode
        step           :    1,                // show all years in drop-down boxes (instead of every other year as default)
        align          :    "Bl"           // alignment (defaults to "Bl")
    });
</script>
<script type="text/javascript">
    Calendar.setup({
        inputField     :    "f_date_d",      // id of the input field
        ifFormat       :    "%Y-%m-%d %H:%M:%S",       // format of the input field
        showsTime      :    true,            // will display a time selector
        button         :    "f_trigger_d",   // trigger for the calendar (button ID)
        singleClick    :    false,           // double-click mode
        step           :    1,                // show all years in drop-down boxes (instead of every other year as default)
        align          :    "Bl"           // alignment (defaults to "Bl")
    });
</script>