<?php

require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
//require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'utils-accounting.php');




$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
$con->debug = 1;
?>
<script language="JavaScript">
<!--
function MM_findObj(n, d) { //v4.0
  var p,i,x;  if(!d) d=document; if((p=n.indexOf("?"))>0&&parent.frames.length) {
    d=parent.frames[n.substring(p+1)].document; n=n.substring(0,p);}
  if(!(x=d[n])&&d.all) x=d.all[n]; for (i=0;!x&&i<d.forms.length;i++) x=d.forms[i][n];
  for(i=0;!x&&d.layers&&i<d.layers.length;i++) x=MM_findObj(n,d.layers[i].document);
  if(!x && document.getElementById) x=document.getElementById(n); return x;
}

function MM_validateForm() { //v4.0
  var i,p,q,nm,test,num,min,max,errors='',args=MM_validateForm.arguments;
  for (i=0; i<(args.length-2); i+=3) { test=args[i+2]; val=MM_findObj(args[i]);
    if (val) { nm=val.name; if ((val=val.value)!="") {
      if (test.indexOf('isEmail')!=-1) { p=val.indexOf('@');
        if (p<1 || p==(val.length-1)) errors+='- '+nm+' must contain an e-mail address.\n';
      } else if (test!='R') {
        if (isNaN(val)) errors+='- '+nm+' must contain a number.\n';
        if (test.indexOf('inRange') != -1) { p=test.indexOf(':');
          min=test.substring(8,p); max=test.substring(p+1);
          if (val<min || max<val) errors+='- '+nm+' must contain a number between '+min+' and '+max+'.\n';
    } } } else if (test.charAt(0) == 'R') errors += '- '+nm+' is required.\n'; }
  } if (errors) alert('The following error(s) occurred:\n'+errors);
  document.MM_returnValue = (errors == '');
}
//-->
</script>

<form enctype='multipart/form-data' action='<?php echo $http_site_root; ?>/plugins/webform/new-form.php' method='post'>
<input type="hidden" name="user_id" value="1">
<input type="hidden" name="crm_status_id" value="1">
<input type="hidden" name="industry_id" value="1">
<input type="hidden" name="session_user_id" value="1">
<input type="hidden" name="last_modified_by" value="1">
<input type="hidden" name="country_id" value="14">
<table border=0>
    <tr> 
      <td>First Name</td>
      <td class=widget_content_form_element> 
        <input type=text name='first_names' size=35>
        <?php echo $required_indicator; ?>
      </td>
    </tr>
    <tr> 
      <td>Last Name 
      <td class=widget_content_form_element> 
        <input type=text name='last_name' size=35>
        <?php echo $required_indicator; ?>
      </td>
    </tr>
    <tr> 
      <td>Email Address</td>
      <td class=widget_content_form_element> 
        <input type=text name='email' size=35>
        <?php echo $required_indicator; ?>
      </td>
    </tr>
    <tr> 
      <td>Company Name (if applicable)</td>
      <td class=widget_content_form_element> 
        <input type=text name='company_name' size=35>
      </td>
    </tr>
    <tr> 
      <td>Contact Phone</td>
      <td class=widget_content_form_element> 
        <input type=text name='phone' size=15>
        <?php echo $required_indicator; ?>
      </td>
    </tr>
    <tr> 
      <td>Post Code </td>
      <td class=widget_content_form_element> 
        <input type="text" name="postal_code" size="4" maxlength="4">
      </td>
    </tr>
    <tr> 
      <td>Suburb</td>
      <td  class=widget_content_form_element> 
        <input type="text" name="city" size="35" maxlength="100">
        <?php echo $required_indicator; ?>
      </td>
    </tr>
    <tr> 
      <td>State</td>
      <td class=widget_content_form_element> 
        <select name="province">
          <option value="NSW">NSW</option>
          <option value="VIC">VIC</option>
          <option value="QLD">QLD</option>
          <option value="SA">SA</option>
          <option value="QA">WA</option>
          <option value="ACT">ACT</option>
          <option value="NT">NT</option>
          <option value="TAS">TAS</option>
        </select>
        <?php echo $required_indicator; ?>
      </td>
    </tr>
    <tr> 
      <td>How did you hear about us</td>
      <td><select name="company_source_id">
<?php 
$sql = "SELECT company_source_id,company_source_pretty_name FROM company_sources WHERE company_source_record_status ='a' ORDER BY company_source_pretty_name ";
$rst = $con->execute($sql);
while (!$rst->EOF) {
  echo ("<option value=".$rst->fields['company_source_id'].">" .$rst->fields['company_source_pretty_name']. "</option>\n");
  $rst->movenext();
}
$rst->close()
?>
</select><?php echo $required_indicator; ?></td>
        
      
    </tr>
    <tr> 
      <td>Your Message</td>
      <td class=widget_content_form_element> 
        <textarea name='YourMessage' rows=5 cols=40></textarea>
        <?php echo $required_indicator; ?>
      </td>
    </tr>
    <tr> 
      <td>&nbsp; </td>
      <td>&nbsp;</td>
    </tr>
  </table>
                 
                
  <input type=submit value='Submit Form' name="submit" onClick="MM_validateForm('first_names','','R','last_name','','R','email','','RisEmail','phone','','R','city','','R','YourMessage','','R');return document.MM_returnValue">
                <input type=reset value='Reset Form' name="reset">
                 
              </form>
