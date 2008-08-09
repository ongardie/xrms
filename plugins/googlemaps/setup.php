<?php
/*
*
* xing plugin
* by Stefan Pampel <stefan.pampel@polyformal.de> 
* polyformal ( http://www.polyformal.de/ )
* (c) 2007 (GNU GPL - see ../../COPYING)
* 
* $Id: setup.php,v 1.1 2008/08/09 14:13:26 randym56 Exp $
*
* This plugin allows show the location of the selected company in the sidebar
* within google maps.
* 
* There could be some improvements like:
* - show map on request (click) only
* - calculate a route, based an the company_id/address_id of the current user
*/


function xrms_plugin_init_googlemaps () {
    global $xrms_plugin_hooks;
    $xrms_plugin_hooks['company_sidebar_bottom']['googlemaps'] = 'googlemaps';
    }
    
function googlemaps () {
    global $con, $contact_id, $sidebar, $company_id, $company_name, $my_company_id;
	$google_maps_apikey ="ABQIAAAAB0zjRnMEpKe6vamz2SKl4xQu4nSd9P7liTVaIVaMzn0q7SrjURTURy_xHpA5-CI0i3xikWBCNPiuFA";
    $sql = "select * from addresses inner join countries on addresses.country_id=countries.country_id where company_id=". $company_id;
    $rst = $con->execute($sql);
    if ($rst) {
        if (!$rst->EOF) {
            $address_line1 = $rst->fields['line1'];
			$address_line2 = $rst->fields['line2'];
			$address_postal_code = $rst->fields['postal_code'];
			$address_city = $rst->fields['city'];
			$address_country = $rst->fields['country_name'];
			$address=$address_line1 . ", " . $address_postal_code . ", " . $address_city . ", " .$address_country ;
			$html_address=urlencode($address);
			$sidebar_string = '<div id="idphoto_sidebar">
			<table class=widget cellspacing=1 width="100%">
            <tr>
                <td class=widget_header colspan=4>'
                ._("Google Maps")
                .'</td>
            </tr>
            <tr>
                <td>
                   <script src="http://maps.google.com/maps?file=api&amp;v=2.x&amp;key='.$google_maps_apikey.'" type="text/javascript"></script>
    <script type="text/javascript">
    //<![CDATA[

    var map = null;
    var geocoder = null;
    function load() {
      if (GBrowserIsCompatible()) {
        map = new GMap2(document.getElementById("map"));
		map.addControl(new GSmallMapControl());
		map.addControl(new GMapTypeControl());
        //map.setCenter(new GLatLng(37.4419, -122.1419), 13);
        geocoder = new GClientGeocoder();
        showAddress("' . $address . '");
      }
    }

    function showAddress(address) {
      if (geocoder) {
        geocoder.getLatLng(
          address,
          function(point) {
            if (!point) {
              alert(address + " not found");
            } else {
              map.setCenter(point, 13);
              var marker = new GMarker(point);
              map.addOverlay(marker);
              marker.openInfoWindowHtml( \'<b>'.$company_name.'</b> <br>\' + address );
            }
          }
        );
      }
    }
    //]]>

// BEGIN NEW CODE TO AVOID USING BODY TAG FOR LOAD/UNLOAD

function addLoadEvent(func) {
  var oldonload = window.onload;
  if (typeof window.onload != \'function\') {
    window.onload = func;
  } else {
    window.onload = function() {
      if (oldonload) {
        oldonload();
      }
      func();
    }
  }
}

addLoadEvent(load);

// arrange for our onunload handler to \'listen\' for onunload events
if (window.attachEvent) {
        window.attachEvent("onunload", function() {
                GUnload();      // Internet Explorer
        });
} else {

        window.addEventListener("unload", function() {
                GUnload(); // Firefox and standard browsers
        }, false);

}

// END NEW CODE TO AVOID USING BODY TAG FOR LOAD/UNLOAD

    </script>
     <a target="_blank" href="http://maps.google.de/maps?daddr='. $html_address .'&geocode=&saddr=kaesenstra%C3%9Fe+k%C3%B6ln&f=d&hl=de&sll=50.796386,7.206228&sspn=0.009725,0.020084&ie=UTF8&z=12&om=1">Link hierher</a>
      <div id="map" style="width: 100%; height: 300px"></div>
    </form>          

</td>
</tr>
</table>
';
        }
            $rst->close();
    }
    return $sidebar.=$sidebar_string;
}
/*
 * $Log: setup.php,v $
 * Revision 1.1  2008/08/09 14:13:26  randym56
 * Added to core XRMS
 *
 * Revision 1.0  2007/09/21 07:49:09  polyformal
*/
?>
