<?php
  /*
  *
  * Mapquest XRMS Plugin v0.1
  *
  * copyright 2004 Glenn Powers <glenn@net127.com>
  *
  */

  function xrms_plugin_init_whereis() {
		  global $xrms_plugin_hooks;
		  $GLOBALS["use_whereis_link"]="y";
  }

  function whereis($line1, $city, $province, $iso_code2, $address_to_display) {
		  //for whereis.com, you need to get street number
		  //do a little parsing guesswork on line1
		  list($streetnumber, $streetname)=explode(" ",$line1,2);
		  //check that street number is a number
		  $sn = eregi_replace("[^0-9]", null, $streetnumber);
		  if(is_numeric($sn)){
				  $streetnumber=$sn;
		  }else{
				  //you got something else - perhaps they didnt put in the street number
				  switch (strtolower($streetnumber)){
						  case "unit":
						  case "level":
						  case "room":
							case "shop":
								  $streetnumber="";
						  		break;
						  default:
						  		
				  }
		  }
			//check the streename var
			$s2=preg_replace("/['0123456789]/","", $streetname);
			$streetname=$s2;

		  $url_line1 = urlencode ($line1);
		  $url_streetnumber = urlencode ($streetnumber);
		  $url_streetname = urlencode ($streetname);
		  $url_city = urlencode ($city);
		  $url_province = urlencode ($province);
		  $url_country = urlencode ($iso_code2);

		  return "<a href=\"http://www.whereis.com.au/whereis/mapping/geocodeAddress.do?streetNumber=".$url_streetnumber."&streetName=".$url_streetname."&suburb=" . $url_city . "&state=" . $url_province . "\">" . $address_to_display . "</a>";//    return "COUNTRY: " . $url_country . "<BR>city: " . $url_city  . "<BR>address_id: " . $address_id;
  }

  ?>