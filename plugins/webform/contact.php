<?php
/**
 * WebForm Plugin - new-form.php
 *
 * @author Nic Lowe
 *
 * $Id: contact.php,v 1.1 2004/06/26 14:39:30 braverock Exp $
 */

require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'utils-accounting.php');

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
?>

<form enctype='multipart/form-data' action='new-form.php' method='post'>
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
          <input type=text name='first_names' size=40>
          <?php echo $required_indicator; ?>
        </td>
    </tr>
    <tr>
        <td>Last Name
        <td class=widget_content_form_element>
            <input type=text name='last_name' size=40>
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
        <td>Company Name</td>
        <td class=widget_content_form_element>
            <input type=text name='company_name' size=35>
        </td>
    </tr>
    <tr>
        <td>Contact Phone</td>
        <td class=widget_content_form_element>
            <input type=text name='phone' size=35>
        </td>
    </tr>
    <tr>
        <td>Post Code </td>
        <td class=widget_content_form_element>
            <input type="text" name="postal_code" size="8" maxlength="4">
        </td>
    </tr>
    <tr>
        <td>Suburb</td>
        <td  class=widget_content_form_element>
            <input type="text" name="city" size="8" maxlength="100">
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
if ($rst) {
    while (!$rst->EOF) {
        echo ("<option value=".$rst->fields['company_source_id'].">" .$rst->fields['company_source_pretty_name']. "</option>\n");
        $rst->movenext();
    }
    $rst->close()
}
?>
</select></td>
        <?php echo $required_indicator; ?>

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

        <input type=submit value='Submit Form' name="submit">
        <input type=reset value='Reset Form' name="reset">

</form>

<?php
/**
 * $Log: contact.php,v $
 * Revision 1.1  2004/06/26 14:39:30  braverock
 * - Initial Revision of WebForm Plugin by Nic Lowe
 *   - added phpdoc
 *   - standardized on long php tags
 *
 */
?>