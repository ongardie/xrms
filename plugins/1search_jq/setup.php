<?php
/*
 * Search plugin for XRMS 
 * by Stefan Pampel <stefan.pampel@polyformal.de> 
 * (c) 2008 (GNU GPL - see ../../COPYING)
 *
 *  This plugin is an approach to have one search field in the GUI for quick
 *  searches and fast results.
 *
 * - inspired by the 1search plugin from Glenn Powers
 * - uses jquery library with autocomplete plugin
 *   http://bassistance.de/jquery-plugins/jquery-plugin-autocomplete/
 * - see README.txt for Details
 *
 */

function xrms_plugin_init_1search_jq() {
    global $xrms_plugin_hooks;
    $xrms_plugin_hooks['private_sidebar_top']['1search_jq'] = 'one_search_jq';
    $xrms_plugin_hooks['company_sidebar_top']['1search_jq'] = 'one_search_jq';
    $xrms_plugin_hooks['company_some_sidebar_top']['1search_jq'] = 'one_search_jq';
    $xrms_plugin_hooks['contact_sidebar_top']['1search_jq'] = 'one_search_jq';
    $xrms_plugin_hooks['contact_some_sidebar_top']['1search_jq'] = 'one_search_jq';
    $xrms_plugin_hooks['opportunity_sidebar_top']['1search_jq'] = 'one_search_jq';
    $xrms_plugin_hooks['opportunity_some_sidebar_top']['1search_jq'] = 'one_search_jq';
//    $xrms_plugin_hooks['bodytags']['1search_jq'] = 'bodytags';
    $xrms_plugin_hooks['activity_sidebar_top']['1search_jq'] = 'one_search_jq';
}


function one_search_jq() {
    global $http_site_root, $sidebar_rows_top;
    // BOF: custon javascript for search
    $js="
      $().ready(function() {

  function findValueCallback(event, data, formatted) {
    $(\"<li>\").html( !data ? \"No match!\" : \"Selected: \" + formatted).appendTo(\"#result\");
  }
  
  function formatItem(row) {
    return row[0] + \" (<strong>id: \" + row[1] + \"</strong>)\";
  }
  function formatResult(row) {
    return row[0].replace(/(<.+?>)/gi, '');
  }
  
  $(\"#singleBirdRemote\").autocomplete(\"".$http_site_root."/plugins/1search_jq/livesearch_jq.php\", {
      formatItem: function(item) {
      return item[0];
    }
  }).result(function(event, item) {
    location.href = item[1];
  });

  $(\":text, textarea\").result(findValueCallback).next().click(function() {
    $(this).prev().search();
  });


  $(\"#scrollChange\").click(changeScrollHeight);
  
  $(\"#clear\").click(function() {
    $(\":input\").unautocomplete();
  });

});

function changeOptions(){
  var max = parseInt(window.prompt('Please type number of items to display:', jQuery.Autocompleter.defaults.max));
  if (max > 0) {
    $(\"#suggest1\").setOptions({
      max: max
    });
  }
}

function changeScrollHeight() {
    var h = parseInt(window.prompt('Please type new scroll height (number in pixels):', jQuery.Autocompleter.defaults.scrollHeight));
    if(h > 0) {
        $(\"#suggest1\").setOptions({
      scrollHeight: h
    });
    }
}

      ";
    // EOF: custon javascript for search
    $sidebar= "
<script type='text/javascript' src='".$http_site_root."/js/jquery/jquery.js'></script>
<script type='text/javascript' src='".$http_site_root."/js/jquery/jquery.bgiframe.min.js'></script>
<script type='text/javascript' src='".$http_site_root."/js/jquery/jquery.ajaxQueue.js'></script>
<script type='text/javascript' src='".$http_site_root."/js/jquery/jquery.autocomplete.js'></script>
<script type='text/javascript'>
  ".$js."
</script>
<link rel='stylesheet' type='text/css' href='".$http_site_root."/js/jquery/jquery.autocomplete.css' />
<table class=widget cellspacing=1>
  <tr>
    <form autocomplete=\"off\"  >
      <td class=widget_header>
        " . _("Search") .  "
      </td>
  </tr>
  <tr>
    <td class=widget_content colspan=3>
      <input size=40 id=\"singleBirdRemote\" class=\"ac_input\" type=\"text\" autocomplete=\"off\"/>
        <div id=\"LSResult\" style=\"display: none;\"><div id=\"LSShadow\"></div></div>
    </td>
    </form>
  </tr>   
</table>
";
return $sidebar_rows_top.=$sidebar;
}

?>
