/** $Id: phpsysinfo.js,v 1.1 2007/12/10 18:23:20 gpowers Exp $
 * Copyright (c) 2006, 2007 by phpSysInfo
 * http://phpsysinfo.sourceforge.net/
 * 
 * This program is free software; you can redistribute it
 * and/or modify it under the terms of the
 * GNU General Public License version 2 (GPLv2)
 * as published by the Free Software Foundation.
 * See COPYING for details.
 *
 * Thanks to JohnResig on #jquery@irc.freenode.net for helping out
 * debugging this script in Internet Explorer. His help resulted in the two following bugs: 
 * - An error in success() triggers the error() callback (http://dev.jquery.com/ticket/1441)
 * - Exception when accessing XML attribute (http://dev.jquery.com/ticket/1442)
 *
 */
var qsParm = new Array();
function qs() {
  var query = window.location.search.substring(1);
  var parms = query.split('&');
  for (var i=0; i<parms.length; i++) {
    var pos = parms[i].indexOf('=');
    if (pos > 0) {
      var key = parms[i].substring(0,pos);
      var val = parms[i].substring(pos+1);
      qsParm[key] = val;
    }
  }
}


$(document).ready(function() {

  $.ajax({
      url: 'xml.php',       
      dataType: 'xml',
      error: function(){
          alert('Error loading XML document');
      },
      success: function(xml){          
          if(checkForErrors(xml)) {
            displayErrors(xml);
          } 
          else {
            populateVitals(xml);
            populateNetwork(xml);
            populateMemory(xml);
            populateFilesystems(xml);
            populateHardware(xml);
            populateTemp(xml);
            populateVoltage(xml);
            populateHddtemp(xml);          
            displayPage(xml);
            getLanguage();
          }
      }
  }); 
  
  $("#sPci").click(function(){
    $("#pciTable").slideDown("slow");
    $("#sPci").hide();
    $("#hPci").show();
  });
  $("#hPci").click(function(){
    $("#pciTable").slideUp("slow");
    $("#hPci").hide();
    $("#sPci").show();
  });
  
  $("#sIde").click(function(){
    $("#ideTable").slideDown("slow");
    $("#sIde").hide();
    $("#hIde").show();
  });
  $("#hIde").click(function(){
    $("#ideTable").slideUp("slow");
    $("#hIde").hide();
    $("#sIde").show();
  }); 
  
  $("#sScsi").click(function(){
    $("#scsiTable").slideDown("slow");
    $("#sScsi").hide();
    $("#hScsi").show();
  });
  $("#hScsi").click(function(){
    $("#scsiTable").slideUp("slow");
    $("#hScsi").hide();
    $("#sScsi").show();
  }); 
  
  $("#sUsb").click(function(){
    $("#usbTable").slideDown("slow");
    $("#sUsb").hide();
    $("#hUsb").show();
  });
  $("#hUsb").click(function(){
    $("#usbTable").slideUp("slow");
    $("#hUsb").hide();
    $("#sUsb").show();
  });
});

function getLanguage() {
     var getLangUrl;
     qsParm['lang'] = null;
     qs();
     if(qsParm['lang'] != null) {
       getLangUrl = 'language/language.php?lang=' + qsParm['lang'];
     } else {
       getLangUrl = 'language/language.php';
     }
     $.ajax({      
      url: getLangUrl,
      type: 'GET',
      dataType: 'xml',
      timeout: 100000,
      error: function(){
          alert('Error loading language.');
      },
      success: function(xml){          
        changeLanguage(xml);
      }
  });   
}

function changeLanguage(lang) { 
  $("[@lang]").each(function(i) {
    langId = this.lang;
    
    langStr = $("string[@id="+langId+"]",lang);
    if(langStr.length > 0) {
      this.innerHTML = langStr.text();    
    }
  });
  $("[@lang]").removeAttr("lang")
}

function populateVitals(xml) {
  var title;
  $("Vitals",xml).each(function(id) {
    vital = $("Vitals",xml).get(id);
    
    hostname = $("Hostname",vital).text();   
    ip       = $("IPAddr",vital).text();
    kernel   = $("Kernel",vital).text();
    distro   = $("Distro",vital).text();
    icon     = $("Distroicon",vital).text();
    uptime   = uptime($("Uptime",vital).text());
    users    = $("Users",vital).text();
    loadavg  = $("LoadAvg",vital).text();    
    if($("CPULoad",vital).length == 1) {
      cpuload = $("CPULoad",vital).text();
      loadavg = loadavg + createBar(cpuload);
    }
    
    document.title = "System information: " + hostname + " (" + ip + ")";
    title = "<span lang='001'>System information</span>: " + hostname + " (" + ip + ")";
    $("#title").append(title);
    
    $("#vitalsTable").append("<tr><td lang='003'>Hostname</td><td>" + hostname + "</td></tr>");
    $("#vitalsTable").append("<tr><td lang='004'>Listening IP</td><td>" + ip + "</td></tr>");
    $("#vitalsTable").append("<tr><td lang='005'>Kernel Version</td><td>" + kernel + "</td></tr>");
    $("#vitalsTable").append("<tr><td lang='006'>Distro Name</td><td><img src='images/" + icon + "' alt='' height='16' width='16'> " + distro + "</td></tr>");
    $("#vitalsTable").append("<tr><td lang='007'>Uptime</td><td>" + uptime + "</td></tr>");
    $("#vitalsTable").append("<tr><td lang='008'>Current Users</td><td>" + users + "</td></tr>");
    $("#vitalsTable").append("<tr><td lang='009'>Load Averages</td><td>" + loadavg + "</td></tr>");
  });
}

function populateNetwork(xml) {
  var network;
  var errors;
  $("#networkTable").append("<tr><th lang='022'>Interface</th><th lang='023'>Recieved</th><th lang='024'>Transfered</th><th lang='025'>Error/Drops</th></tr>");
  $("Network",xml).each(function(id) {
    network = $("Network",xml).get(id);
    $("NetDevice",network).each(function(did) {
      device = $("NetDevice",network).get(did);
      
      name   = $("Name",device).text();
      rx     = $("RxBytes",device).text();
      tx     = $("TxBytes",device).text();
      errors = $("Errors",device).text();
      drops  = $("Drops",device).text();
      
      $("#networkTable").append("<tr><td>" + name + "</td><td>" + formatBytes(rx/1024) + "</td><td>" + formatBytes(tx/1024) + "</td><td>" + errors + "/" + drops + "</td></tr>");
    });
  });
}

function displayPage(xml) {  
  $("#loader").hide();
  $('.stripeMe tr:nth-child(even)').addClass('odd');  
  $("#container").fadeIn("slow");   
  versioni = $("Generation", xml).attr("version"); 
  $("#version").append(versioni);
}

function populateMemory(xml) {
  $("#memoryTable").append("<tr><th lang='034'>Type</th><th lang='033'>Percent</th><th lang='035'>Free</th><th lang='036'>Used</th><th lang='037'>Total</th></tr>");
  
  $("Memory",xml).each(function(id) {
    vital = $("Memory",xml).get(id);
    
    free    = $("Free",vital).text();   
    used    = $("Used",vital).text();
    total   = $("Total",vital).text();
    percent = $("Percent",vital).text();
    $("#memoryTable").append("<tr><td lang='028'>Physical Memory</td><td>" + createBar(percent) + "</td><td>" + formatBytes(free) + "</td><td>" + formatBytes(used) + "</td><td>" + formatBytes(total) + "</td></tr>");
    
    if($("App", vital).length > 0) {
      app     = $("App", vital).text();
      appp    = $("AppPercent", vital).text();
      buff    = $("Buffers", vital).text();
      buffp   = $("BuffersPercent", vital).text();
      cached  = $("Cached", vital).text();
      cachedp = $("CachedPercent", vital).text();
      
      $("#memoryTable").append("<tr><td> - <span lang='064'>Kernel + applications</span></td><td>" + createBar(appp) + "</td><td> </td><td>" + formatBytes(app) + "</td><td> </td></tr>");
      $("#memoryTable").append("<tr><td> - <span lang='065'>Buffers</span></td><td>" + createBar(buffp) + "</td><td> </td><td>" + formatBytes(buff) + "</td><td> </td></tr>");
      $("#memoryTable").append("<tr><td> - <span lang='066'>Cached</span></td><td>" + createBar(cachedp) + "</td><td> </td><td>" + formatBytes(cached) + "</td><td> </td></tr>");
    }
    
    
  });

  $("Swap",xml).each(function(id) {
    vital = $("Swap",xml).get(id);
    
    free    = $("Free",vital).text();   
    used    = $("Used",vital).text();
    total   = $("Total",vital).text();
    percent = $("Percent",vital).text();
    
    $("#memoryTable").append("<tr><td lang='029'>Disk swap</td><td>" + createBar(percent) + "</td><td>" + formatBytes(free) + "</td><td>" + formatBytes(used) + "</td><td>" + formatBytes(total) + "</td></tr>");
  });
}

function populateFilesystems(xml) {

  var total_usage = 0;
  var total_used = 0;
  var total_free = 0;
  var total_size = 0;
  var inodes_text;
  var mounted_text;
  
  var filesystem;
  $("#filesystemTable").append("<thead><tr><th lang='031'>Mount</th><th lang='034'>Type</th><th lang='032'>Partition</th><th lang='033'>Percent used</th><th lang='035'>Free</th><th lang='036'>Used</th><th lang='037'>Total</th></tr></thead><tfoot></tfoot>");
  $("FileSystem",xml).each(function(id) {
    filesystem = $("FileSystem",xml).get(id);
    $("Mount",filesystem).each(function(mid) {
      mount = $("Mount",filesystem).get(mid);
      $("Device",mount).each(function(did) {
        dev = $("Device",mount).get(did);
        name = $("Name",dev).text();
      });
      mpid    = $("MountPointID",mount).text();
      mpoint  = $("MountPoint",mount).text();
      type    = $("Type",mount).text();
      percent = $("Percent",mount).text();
      free    = $("Free",mount).text();
      used    = $("Used",mount).text();
      size    = $("Size",mount).text();
      inodes  = $("Inodes",mount).text();
  
      if(mpoint != "")	mounted_text = mpoint;
	  else				mounted_text = mpid;
	  
      if(inodes != "")	inodes_text = "<span style='font-style:italic'>&nbsp;(" + inodes + "%)</span>";
      else				inodes_text = "";

      $("#filesystemTable").append("<tr><td val=\"" + mounted_text + "\">" + mounted_text + "</td><td val=\"" + type + "\">" + type + "</td><td val=\"" + name + "\">" + name + "</td><td val=\"" + percent + "\">" + createBar(percent) + inodes_text + "</td><td val=\"" + free + "\">" + formatBytes(free) + "</td><td val=\"" + used + "\">" + formatBytes(used) + "</td><td val=\"" + size + "\">" + formatBytes(size) + "</td></tr>");
	
      total_used += parseInt(used);
      total_free += parseInt(free);
      total_size += parseInt(size);
      total_usage = Math.round( (total_used / total_size) * 100 );
    });

    $("#filesystemTable tfoot").append("<tr style='font-weight : bold'><td>&nbsp;</td><td>&nbsp;</td><td lang='038'>Totals</td><td>" + createBar(total_usage) + "</td><td>" + formatBytes(total_free) + "</td><td>" + formatBytes(total_used) + "</td><td>" + formatBytes(total_size) + "</td></tr>");
  });
  
  var myTextExtraction = function(node)  
  {    
	return $(node).attr("val"); 
  } 

  if($("#filesystemTable tbody tr").length >0)
  {
    $("#filesystemTable").tablesorter({
      textExtraction: myTextExtraction,
      widgets: ['zebra']
    });
  }

}

function populateHardware(xml) {
  var hardware;
  $("Hardware",xml).each(function(id) {
    hardware = $("Hardware",xml).get(id);
    $("CPU",hardware).each(function(id) {
      cpu = $("CPU",hardware).get(id);
      num   = $("Number",cpu).text();
      model = $("Model",cpu).text();
      speed = $("Cpuspeed",cpu).text();
      bus   = $("Busspeed",cpu).text();
      cache = $("Cache",cpu).text();
      bogo  = $("Bogomips",cpu).text();
      
      $("#cpuTable").append("<tr><td lang='011'>Processors</td><td>" + num + "</td></tr>");
      $("#cpuTable").append("<tr><td lang='012'>Model</td><td>" + model + "</td></tr>");
      $("#cpuTable").append("<tr><td lang='013'>CPU Speed</td><td>" + formatHertz(speed) + "</td></tr>");
      $("#cpuTable").append("<tr><td lang='014'>BUS Speed</td><td>" + formatHertz(bus) + "</td></tr>");
      $("#cpuTable").append("<tr><td lang='015'>Cache Size</td><td>" + cache + "</td></tr>");
      $("#cpuTable").append("<tr><td lang='016'>Bogomips</td><td>" + bogo + "</td></tr>");
    });
    popDevices('PCI:', 'pciTable', 'PCI', hardware);
    popDevices('IDE:', 'ideTable', 'IDE', hardware);
    popDevices('SCSI:', 'scsiTable', 'SCSI', hardware);
    popDevices('USB:', 'usbTable', 'USB', hardware);
  });
}

function popDevices(header, table, type, xml) {
  var text = '';
  $(type,xml).each(function(id) {
    alldev = $(type,xml).get(id);
    $("Device",alldev).each(function(id) {
      dev = $("Device",alldev).get(id);
      text = text + $("Name",dev).text() + "<br>";      
    });
  });
  if(text == "") {
    $("#" + table).append("<tr><td><span lang='042'>none</span></td></tr>");
  } else {
    $("#" + table).append("<tr><td>" + text + "</td></tr>");
  }
}

function checkForVoltage(xml) {
  var voltage;
  voltage = $("Voltage",xml).length;
  if(voltage > 0) {
    return true;
  }  
  return false;
}

function checkForTemp(xml) {
  var voltage;
  voltage = $("Temperature",xml).length;
  if(voltage > 0) {
    return true;
  }  
  return false;
}

function checkForHddtemp(xml) {
  var voltage;
  voltage = $("HDDTemp",xml).length;
  if(voltage > 0) {
    return true;
  }  
  return false;
}

function populateVoltage(xml) {
  var voltage, item;
  if(checkForVoltage(xml)) {
    $("Voltage",xml).each(function(id) {
      voltage = $("Voltage",xml).get(id);
      $("Item",voltage).each(function(iid) {
        item = $("Item",voltage).get(iid);
        label = $("Label",item).text();
        value = $("Value",item).text();
        max   = $("Max",item).text();
        min   = $("Min",item).text();
        $("#voltageTable").append("<tr><td>" + label + "</td><td>" + value + " <span lang='062'>V</span></td><td>" + min + " <span lang='062'>V</span></td><td>" + max + " <span lang='062'>V</span></td></tr>");
      });
    });
    $("#voltage").show();
  }
}

function populateTemp(xml) {
  var temp, item;
  if(checkForTemp(xml)) {
    $("Temperature",xml).each(function(id) {
      temp = $("Temperature",xml).get(id);
      $("Item",temp).each(function(iid) {
        item = $("Item",temp).get(iid);
        label = $("Label",item).text();
        value = $("Value",item).text();        
        limit = $("Limit",item).text();
        
        value = value.replace(/\+/g,"");
        limit = limit.replace(/\+/g,"");
        
        $("#tempTable").append("<tr><td>" + label + "</td><td>" + value + " <span lang='060'>C</span></td><td>" + limit + " <span lang='060'>C</span></td></tr>");
      });
    });       
    $("#temp").show();
  }
}

function populateHddtemp(xml) {
  var temp, item;
  if(checkForHddtemp(xml)) {    
    $("HDDTemp",xml).each(function(id) {
      temp = $("HDDTemp",xml).get(id);
      $("Item",temp).each(function(iid) {
        item = $("Item",temp).get(iid);
        label = $("Label",item).text();
        value = $("Value",item).text();        
        model = $("Model",item).text();        
        if(value != 'NA') {
          $("#tempTable").append("<tr><td>" + model + "</td><td>" + value + " <span lang='060'>C</span></td><td> </td></tr>");
        }
      });
    });       
    $("#temp").show();
  }
}

function uptime(sec) {
  txt = '';
  intMin = sec / 60;
  intHours = intMin / 60;
  intDays = Math.floor(intHours/24);
  intHours = Math.floor(intHours-(intDays*24));
  intMin = Math.floor(intMin-(intDays*60*24)-(intHours*60));
  
  if(intDays != 0 ) {
    txt = txt + intDays + " <span lang='048'>days</span> ";
  }
  if(intHours != 0 ) {    
    txt = txt + intHours + " <span lang='049'>hours</span> ";
  }
  txt = txt +  intMin + " <span lang='050'>minutes</span>";
  return txt;
}

function formatBytes(kbytes) {
  
  if(kbytes > 1048576) {
    show = Math.round((kbytes/1048576)*100)/100;
    return show + " <span lang='041'>GB</span>";
  } else if(kbytes > 1024) {
    show = Math.round((kbytes/1024)*100)/100; 
    return show + " <span lang='040'>MB</span>";
  } else {    
    show = Math.round((kbytes)*100)/100;
    return show + " <span lang='039'>KB</span>";
  }
}

function formatHertz(mhertz)
{
  if(mhertz != "" && mhertz < 1000) {
    return mhertz + " Mhz";
  } else if(mhertz != "" && mhertz >=1000) {
    return  Math.round(mhertz/1000*100)/100 + " GHz";
  } else {
    return "";
  }
}

function createBar(percent) {
  h = '<div class="bar" style="float:left; width: ' + percent + 'px "> &nbsp;</div> <div style="float: left">&nbsp; ' + percent + '%</div>';
  
  return h;
}

function checkForErrors(xml) {
  var errors;
  errors = $("Error",xml).length;
  if(errors > 0) {
    return true;
  }  
  return false;
}

function displayErrors(xml) {
  var error;
  var message;  
  $("Error",xml).each(function(id) {
    error = $("Error",xml).get(id);
    message = $("Message",error).text();
    $("#errorlist").append("<p>" + message + "</p>");
  });
  document.title = "phpSysInfo | Huston, we got a problem.";
  
  $("#loader").hide();  
  $("#errors").fadeIn("slow");
}
