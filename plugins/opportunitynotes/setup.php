<?php
/*
 *  OpportunityNotes setup.php
 *
 * Copyright (c) Neil Roberts
 */


function xrms_plugin_init_opportunitynotes() {
    global $xrms_plugin_hooks;
    $xrms_plugin_hooks['opportunity_notes_buttons']['opportunitynotes'] = 'opportunitynotes';
}

function opportunitynotes() {
    global $save_and_next;
    global $activity_id;
    $output = "";
    
    if(date('l', time() + 172800) == "Saturday" or date('l', time() + 172800) == "Sunday") {
        $new_time = date('Y-m-d H:i:s', strtotime("first Monday"));
    }
    else {
        $new_time = date('Y-m-d H:i:s', time() + 172800);
    }
    
    $buttons = array();
    $buttons[] = array('value' => 'Left Message', 'onclick' => "document.forms[0].ends_at.value='" . $new_time . "'; document.forms[0].opportunity_description.value = logTime() + ' Message left by " . $_SESSION['username'] . "\\n' + document.forms[0].opportunity_description.value; document.forms[0].return_url.value = '" . current_page() . "'; document.forms[0].submit();");
    if($save_and_next) {
        $buttons[] = array('type' => 'submit', 'name' => 'saveandnext', 'value' => 'Save + 2 Days', 'onclick' => "document.forms[0].ends_at.value='" . $new_time . "';");
    }
    else {
        $buttons[] = array('value' => 'Save + 2 Days', 'onclick' => "document.forms[0].ends_at.value='" . $new_time . "'; document.forms[0].submit();");
    }
    $buttons[] = array('value' => 'Print', 'onclick' => "window.open('../plugins/opportunitynotes/letter.php?activity_id=" . $activity_id . "', 'letter', 'width=450')");

    foreach($buttons as $button) {
        $output .= " <input class=\"button\" ";
        if(isset($button['type'])) {
            $output .= "type=\"" . $button['type'] . "\" ";
        }
        else {
            $output .= "type=\"button\" ";
        }
        if(isset($button['name'])) {
            $output .= "name=\"" . $button['name'] . "\" ";
        }
        $output .= "value=\"" . $button['value'] . "\" ";
        if(isset($button['onclick'])) {
            $output .= "onclick=\"" . $button['onclick'] . "\" ";
        }
        $output .= "> ";
    }
    
    echo $output;
}

?>
