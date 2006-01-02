<?php
/**
 * Export all Companies with Phne,Fax, and Primary address info
 *
 * @author Fontain Consulting Group (France)
 * @author Brian Peterson (modified to only export company info and address)
 *
 * $Id: export-company-address.php,v 1.3 2006/01/02 21:50:29 vanmer Exp $
 */

//include required files
require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb/toexport.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check( 'Admin' );

$sql = " SELECT
  c.company_name AS 'company_name',
  c.phone AS 'company_phone',
  c.phone2 AS 'company_alt_phone',
  c.fax AS 'company_fax',
  a.line1 AS 'address_line1',
  a.line2 AS 'address_line2',
  a.city AS 'city',
  a.province AS 'province',
  a.postal_code AS 'postal_code',
  coun.country_name AS 'country'
FROM companies c, addresses a, countries coun
WHERE
  c.company_record_status = 'a' AND
  c.default_primary_address = a.address_id AND
  a.country_id = coun.country_id
ORDER BY c.company_name";

$con = get_xrms_dbconnection();

$rst = $con->execute($sql);

if ($rst) {

$fp = fopen($xrms_file_root . '/tmp/company-address-export.csv', 'w');

if (($fp)) {
    rs2csvfile($rst, $fp);
    $rst->close();
    fclose($fp);
} else {
    echo '<br><h1>'._("Unable to Open file for writing.").'</h1>';
    $con->close();
    exit;
}
} else {
    db_error_handler ($con, $sql);
}

$con->close();

header("Location: {$http_site_root}/tmp/company-address-export.csv");

/**
 * $Log: export-company-address.php,v $
 * Revision 1.3  2006/01/02 21:50:29  vanmer
 * - changed to use centralized dbconnection function
 *
 * Revision 1.2  2005/09/06 16:04:39  braverock
 * - add Admin ACL restriction to export functions.
 *   credit Bert (SF:camel2004) for the patch
 *
 * Revision 1.1  2004/07/27 13:09:08  braverock
 * - Initial Revision of Company & Address Export requested by Jack Iu
 *
 * Revision 1.5  2004/07/16 13:51:58  braverock
 * - localize strings for i18n translation support
 *   - applies modified patches from Sebastian Becker (hyperpac)
 *
 * Revision 1.4  2004/06/14 22:24:40  introspectshun
 * - Add adodb-params.php include for multi-db compatibility.
 *
 * Revision 1.3  2004/03/15 14:15:07  braverock
 * - added code to export all available contact fields on contact/company export
 *   - new code provided by Olivier Colonna of Fontaine Consulting
 * - add phpdoc
 */
?>