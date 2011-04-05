<?php
/*
*
* googlemaps plugin
* by Stefan Pampel <stefan.pampel@polyformal.de> 
* polyformal ( http://www.polyformal.de/ )
* (c) 2007 (GNU GPL - see ../../COPYING)
* 
* $Id: setup.php,v 1.6 2011/04/05 20:09:02 gopherit Exp $
*
* This plugin allows show the location of the selected company in the sidebar
* within google maps.
* 
* There could be some improvements like:
* - show map on request (click) only
* - calculate a route, based an the company_id/address_id of the current user DONE
* - need modified companies/one.php because of trouble with company init 
*/


function xrms_plugin_init_googlemaps () {
    global $xrms_plugin_hooks; $division_id;
   
    $xrms_plugin_hooks['division_sidebar_bottom']['googlemaps'] = 'googlemaps_sidebar_division';
    $xrms_plugin_hooks['company_sidebar_bottom']['googlemaps'] = 'googlemaps_sidebar_company';
}
    
function googlemaps_sidebar_company () {
  return googlemaps_sidebar_rows_bottom();
}

function googlemaps_sidebar_division () {
  return googlemaps_sidebar_rows_bottom();
}
function googlemaps_sidebar_rows_bottom () {
  global $contact_id, $sidebar_rows_bottom, $company_id, $user_id, $division_id, $company_name, $my_company_id, $division_sidebar_bottom;
  // use logged in useres address instead account owner
  $session_user_id = session_check();

	// you'll need your own api_key
  $google_maps_apikey ="ABQIAAAA_XPEGP-AFVnM2VL6tVt25hT2yXp_ZAY8_ufC3CFXhHIE1NvwkxQPjktfc-0qMosjSbFfqeK1xiJYoQ";

	// query the useres address, needed to displsy the 'Route'-Link
	$sql="select * from addresses 
		inner join contacts on contacts.address_id = addresses.address_id
		inner join countries on addresses.country_id=countries.country_id 
		inner join users on users.user_contact_id = contacts.contact_id
		where users.user_id ={$session_user_id}";

	$user_address = get_gm_address($sql);
	$user_html_address = urlencode($user_address);
	
	// query the address, use diffferent query and filter if we see an division
	if ($division_id) {
	  $sql_filter = 'inner join company_division on addresses.address_id=company_division.address_id';
	  $sql_crit = ' and company_division.division_id='.$division_id;
	}
	 else {
	   $sql_filter='inner join companies on addresses.address_id=companies.default_primary_address';
	   $sql_crit='';
	 }
	$sql = "select * from addresses 
		inner join countries on addresses.country_id=countries.country_id 
		".$sql_filter."
		where addresses.on_what_table='companies' and addresses.on_what_id=". $company_id.$sql_crit;
    
	$address = get_gm_address($sql);
  #if no address is given, then don't start the whole process.
  if ($address !=', , , ') {
	$html_address=urlencode($address);
	// assembling the html stuff
			$sidebar_string = '<div id="googlemaps_sidebar">
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
     <a target="_blank" href="http://maps.google.com/maps?daddr='. $html_address .'&geocode=&saddr='.$user_html_address.'&f=d&z=12&om=1">Show Route</a>
      <div id="map" style="width: 100%; height: 300px"></div>
    </form>          

</td>
</tr>
</table>
';
    // return the result
   return $sidebar_rows_bottom .= $sidebar_string;
      }//EOF if
}

function get_gm_address($sql) {
    global $con;
    $rst = $con->execute($sql);
    if ($rst) {
        if (!$rst->EOF) {
            $address_line1 = $rst->fields['line1'];
			$address_line2 = $rst->fields['line2'];
			$address_postal_code = $rst->fields['postal_code'];
			$address_city = $rst->fields['city'];
			$address_country = $rst->fields['country_name'];
			$address=$address_line1 . ", " . $address_postal_code . ", " . $address_city . ", " .$address_country ;
                }
            $rst->close();
    }
    return $address;
}
/*
 * $Log: setup.php,v $
 * Revision 1.6  2011/04/05 20:09:02  gopherit
 * FIXED Bug Artifact #2973756  Removed charset encoding parameter from the URL to enable users with extended character sets to make GET requests to GoogleMaps.
 *
 * Revision 1.5  2010/03/11 02:45:32  gopherit
 * The plugin route URL defaulted to http://maps.google.de/ and German language.  Switched it to point to http://maps.google.com/ and removed the language reference to allow Google Maps to self-determine the user's language.
 *
 * Revision 1.4  2010/03/10 22:59:56  gopherit
 * Fixed: Bug Artifact #2968294: googlemaps Plugin Outputs SQL Query Code in Sidebar.
 *
 * Revision 1.3  2008/10/07 08:57:24  polyformal_sp
 * updated to recent version, fix sql statements to on_what_* layout
 *
 * Revision 1.2  2008/08/29 14:51:45  polyformal_sp
 * midify xing to googlemaps in comments (mistype)
 *
 * Revision 1.1  2008/08/09 14:13:26  randym56
 * Added to core XRMS
 *
 * Revision 1.0  2007/09/21 07:49:09  polyformal
*/
?>
